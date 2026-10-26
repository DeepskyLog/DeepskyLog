<?php

use App\Models\Location;
use App\Models\Message;
use App\Models\Observation;
use App\Models\ObservingGoal;
use App\Models\User;
use App\Services\ObserverBlog;
use App\Services\ObserverYear;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

function yearObservation(User $observer, string $object, string $date, array $attributes = []): Observation
{
    return Observation::query()->create(array_merge([
        'observer_username' => $observer->username,
        'observer_user_id'  => $observer->id,
        'observed_on'       => $date,
        'observed_time'     => '22:00:00',
        'language'          => 'en',
        'description'       => '',
        'target_type'       => 'deepsky',
        'target_table'      => 'objects',
        'target_key'        => $object,
        'target_name'       => $object,
        'has_drawing'       => false,
    ], $attributes));
}

function yearCatalog(string $catalog, int $count): void
{
    foreach (range(1, $count) as $index) {
        DB::table('objectnames')->insert([
            'objectname' => $catalog . ' ' . $index,
            'catalog'    => $catalog,
            'catindex'   => (string) $index,
            'altname'    => $catalog . ' ' . $index,
            'slug'       => strtolower($catalog) . '-' . $index,
            'timestamp'  => now(),
        ]);
    }
}

beforeEach(function () {
    Carbon::setTestNow('2026-10-26 12:00:00');
    $this->observer = User::factory()->create(['name' => 'Year Watcher', 'username' => 'yearwatcher', 'slug' => 'year-watcher', 'language' => 'en']);
    $this->location = Location::query()->create([
        'user_id'           => $this->observer->id,
        'name'              => 'Year Site',
        'longitude'         => 4.5,
        'latitude'          => 50.5,
        'timezone'          => 'UTC',
        'limitingMagnitude' => -999,
        'skyBackground'     => -999,
        'elevation'         => 120,
        'country'           => 'Belgium',
        'active'            => true,
        'observer'          => '',
        'observations'      => 0,
        'slug'              => 'year-site',
    ]);
    yearCatalog('M', 4);
});

afterEach(fn () => Carbon::setTestNow());

it('sums up the observations of a year', function () {
    $site = ['location_id' => $this->location->id];
    // Seen before 2025: not new in 2025.
    yearObservation($this->observer, 'M 1', '2024-03-01', $site);

    // One night: the evening of 10 February and the morning after.
    yearObservation($this->observer, 'M 1', '2025-02-10', $site);
    yearObservation($this->observer, 'M 2', '2025-02-10', $site);
    yearObservation($this->observer, 'M 3', '2025-02-11', $site + ['observed_time' => '02:00:00']);
    // Another night, with a drawing and an object that was not seen.
    yearObservation($this->observer, 'M 2', '2025-08-20', $site + ['has_drawing' => true]);
    $notSeen = yearObservation($this->observer, 'M 4', '2025-08-20', $site);
    DB::table('deepsky_observation_details')->insert(['observation_id' => $notSeen->id, 'visibility' => Observation::VISIBILITY_NOT_SEEN]);

    $summary = (new ObserverYear($this->observer, 2025))->summary();

    expect($summary['totals'])->toBe(['observations' => 5, 'nights' => 2, 'objects' => 3, 'drawings' => 1, 'sessions' => 0])
        ->and($summary['previous']['observations'])->toBe(1)
        ->and($summary['months']['observations'][1])->toBe(3)
        ->and($summary['months']['observations'][7])->toBe(2)
        ->and($summary['months']['nights'][1])->toBe(1)
        ->and($summary['new_objects']['count'])->toBe(2)
        ->and(collect($summary['new_objects']['shown'])->pluck('name')->all())->toBe(['M 2', 'M 3'])
        ->and($summary['best_night']['date'])->toBe('2025-02-10')
        ->and($summary['best_night']['objects'])->toBe(3)
        ->and($summary['catalogs'])->toBe([['catalog' => 'M', 'gained' => 2, 'seen' => 3, 'total' => DB::table('objectnames')->where('catalog', 'M')->distinct()->count('objectname')]])
        ->and($summary['locations'][0])->toBe(['name' => 'Year Site', 'count' => 5])
        ->and($summary['drawing_ids'])->toHaveCount(1);
});

it('shows only finished years with observations', function () {
    yearObservation($this->observer, 'M 1', '2025-02-10');
    yearObservation($this->observer, 'M 2', '2026-02-10');

    expect(ObserverYear::years($this->observer)->all())->toBe([2025]);

    $this->get(route('observer.year', ['observer' => 'year-watcher', 'year' => 2025]))
        ->assertOk()
        ->assertSee('The year 2025 of Year Watcher')
        ->assertSee('og:title', false);
    $this->get(route('observer.year', ['observer' => 'year-watcher', 'year' => 2026]))->assertNotFound();
    $this->get(route('observer.year', ['observer' => 'year-watcher', 'year' => 2024]))->assertNotFound();

    $this->get(route('observer.show', ['observer' => 'year-watcher']))
        ->assertOk()
        ->assertSee('The year 2025 in review');
});

it('shows the private goals of the year only to the observer', function () {
    yearObservation($this->observer, 'M 1', '2025-02-10');
    $goal = ['user_id' => $this->observer->id, 'catalog' => 'M', 'source_label' => 'M', 'target_count' => 1, 'mode' => 'observed', 'starts_on' => '2025-01-01', 'ends_on' => '2025-12-31'];
    ObservingGoal::query()->create($goal + ['name' => 'Public goal']);
    ObservingGoal::query()->create($goal + ['name' => 'Secret goal', 'public' => false]);

    $url = route('observer.year', ['observer' => 'year-watcher', 'year' => 2025]);
    $this->get($url)->assertOk()->assertSee('Public goal')->assertDontSee('Secret goal');
    $this->actingAs($this->observer)->get($url)->assertOk()->assertSee('Secret goal');
});

it('puts the finished years in the blog and the feed', function () {
    yearObservation($this->observer, 'M 1', '2025-02-10');

    $entry = (new ObserverBlog($this->observer))->page()['entries']->firstWhere('kind', 'year');
    expect($entry['year'])->toBe(2025)
        ->and($entry['date']->toDateString())->toBe('2025-12-31')
        ->and($entry['summary']['totals']['observations'])->toBe(1);

    $this->get(route('observer.blog', ['observer' => 'year-watcher']))
        ->assertOk()
        ->assertSee('Year in review')
        ->assertSee(ObserverYear::url($this->observer, 2025), false);

    $this->get(route('feed.observer', ['observer' => 'year-watcher']))
        ->assertOk()
        ->assertSee('The year 2025 of Year Watcher', false);
});

it('sends the year in review once as a DeepskyLog message', function () {
    yearObservation($this->observer, 'M 1', '2025-02-10');
    $quiet = User::factory()->create(['username' => 'quietyear', 'year_review' => false]);
    yearObservation($quiet, 'M 1', '2025-02-10');
    // Nothing observed in 2025.
    User::factory()->create(['username' => 'idleyear']);

    $this->artisan('observers:send-year-review')->assertSuccessful();
    expect(Message::count())->toBe(0);

    $this->artisan('observers:send-year-review --apply')->assertSuccessful();
    $messages = Message::all();
    expect($messages)->toHaveCount(1)
        ->and($messages->first()->receiver)->toBe('yearwatcher')
        ->and($messages->first()->subject)->toBe('Your year 2025 on DeepskyLog')
        ->and($messages->first()->message)->toContain(ObserverYear::url($this->observer, 2025))
        ->and($this->observer->fresh()->year_review_sent_year)->toBe(2025);

    $this->artisan('observers:send-year-review --apply')->assertSuccessful();
    expect(Message::count())->toBe(1);

    // The current year has not ended.
    $this->artisan('observers:send-year-review --year=2026')->assertFailed();
});
