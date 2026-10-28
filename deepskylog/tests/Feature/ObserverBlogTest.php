<?php

use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservationSession;
use App\Models\ObservingList;
use App\Models\SketchOfTheMonth;
use App\Models\SketchOfTheWeek;
use App\Models\User;
use App\Services\ObserverBlog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

function blogLocation(User $owner): Location
{
    return Location::query()->create([
        'user_id'           => $owner->id,
        'name'              => 'Blog Site',
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
        'slug'              => 'blog-site-' . $owner->id,
    ]);
}

function blogObservation(User $owner, Location $location, string $date, string $time, string $object, string $description = ''): Observation
{
    return Observation::query()->create([
        'observer_username' => $owner->username,
        'observer_user_id'  => $owner->id,
        'observed_on'       => $date,
        'observed_time'     => $time,
        'location_id'       => $location->id,
        'language'          => 'en',
        'description'       => $description,
        'target_type'       => 'deepsky',
        'target_table'      => 'objects',
        'target_key'        => $object,
        'target_name'       => $object,
        'has_drawing'       => false,
    ]);
}

function blogSession(User $owner, Location $location, string $name, string $begin, string $end, array $attributes = []): ObservationSession
{
    return ObservationSession::query()->create(array_merge([
        'name'       => $name,
        'observerid' => $owner->username,
        'begindate'  => $begin,
        'enddate'    => $end,
        'locationid' => $location->id,
        'language'   => 'en',
        'active'     => 1,
    ], $attributes));
}

function linkToSession(ObservationSession $session, Observation $observation): void
{
    DB::table('sessionObservations')->insertOrIgnore(['sessionid' => $session->id, 'observationid' => $observation->id]);
}

/**
 * The observation ids of all pages of the blog, with the number of pages.
 *
 * @return array{0: list<int>, 1: int}
 */
function walkBlog(User $owner): array
{
    $blog   = new ObserverBlog($owner);
    $ids    = [];
    $pages  = 0;
    $before = null;
    do {
        $page = $blog->page($before ? Carbon::parse($before) : null);
        $pages++;
        foreach ($page['entries'] as $entry) {
            foreach ($entry['observations'] ?? [] as $observation) {
                $ids[] = $observation->id;
            }
        }
        $before = $page['next'];
    } while ($before !== null && $pages < 100);

    return [$ids, $pages];
}

beforeEach(function () {
    $this->owner    = User::factory()->create(['name' => 'Blog Owner', 'username' => 'blogger', 'slug' => 'blog-owner']);
    $this->location = blogLocation($this->owner);
});

it('shows the sessions with their text, the other observations per night and the public lists', function () {
    $session = blogSession($this->owner, $this->location, 'Frosty night at the dark site', '2024-01-12 19:30:00', '2024-01-13 00:45:00', [
        'comments' => '<p>Steady transparency all night.</p>',
        'weather'  => '<p>Clear and frosty</p>',
    ]);
    linkToSession($session, blogObservation($this->owner, $this->location, '2024-01-12', '21:15:00', 'M 42', 'Bright core with extended wings'));
    blogObservation($this->owner, $this->location, '2024-02-03', '22:00:00', 'NGC 2392', 'Eskimo with a bright central star');

    blogSession($this->owner, $this->location, 'Draft session nobody sees', '2024-01-20 19:30:00', '2024-01-20 23:00:00', ['active' => 0]);
    ObservingList::query()->create(['owner_user_id' => $this->owner->id, 'name' => 'Winter planetaries', 'description' => 'For cold nights', 'public' => true]);
    ObservingList::query()->create(['owner_user_id' => $this->owner->id, 'name' => 'My secret list', 'public' => false]);

    $this->get(route('observer.blog', ['observer' => 'blog-owner']))
        ->assertOk()
        ->assertSee('Frosty night at the dark site')
        ->assertSee('Steady transparency all night.')
        ->assertSee('Clear and frosty')
        ->assertSee('Bright core with extended wings')
        ->assertSee('Eskimo with a bright central star')
        ->assertSee('Winter planetaries')
        ->assertDontSee('Draft session nobody sees')
        ->assertDontSee('My secret list')
        ->assertSeeInOrder(['Winter planetaries', 'Eskimo with a bright central star', 'Frosty night at the dark site']);
});

it('puts an observation of the morning in the night before', function () {
    blogObservation($this->owner, $this->location, '2024-03-10', '22:00:00', 'M 81');
    $morning = blogObservation($this->owner, $this->location, '2024-03-11', '02:30:00', 'M 82');

    // The review of 2024 aside
    $entries = (new ObserverBlog($this->owner))->page()['entries']->where('kind', '!=', 'year')->values();

    expect($entries)->toHaveCount(1)
        ->and($entries[0]['kind'])->toBe('night')
        ->and($entries[0]['date']->toDateString())->toBe('2024-03-10')
        ->and($entries[0]['observations']->pluck('id')->all())->toContain($morning->id);
});

it('shows an observation once, in its own session of that day', function () {
    $other     = User::factory()->create(['username' => 'companion', 'slug' => 'companion']);
    $own       = blogSession($this->owner, $this->location, 'Our evening, my notes', '2024-05-04 20:00:00', '2024-05-05 02:00:00');
    $theirs    = blogSession($other, $this->location, 'Our evening, their notes', '2024-05-04 20:00:00', '2024-05-05 02:00:00');
    $wrongYear = blogSession($this->owner, $this->location, 'A session of another year', '2023-08-12 21:00:00', '2023-08-13 03:00:00');

    $observation = blogObservation($this->owner, $this->location, '2024-05-04', '22:00:00', 'M 13');
    foreach ([$own, $theirs, $wrongYear] as $session) {
        linkToSession($session, $observation);
    }

    $entries  = (new ObserverBlog($this->owner))->page()['entries'];
    $sessions = $entries->where('kind', 'session')->keyBy(fn ($entry) => $entry['session']->id);

    expect($sessions->keys()->all())->toEqualCanonicalizing([$own->id, $wrongYear->id])
        ->and($sessions[$own->id]['observations']->pluck('id')->all())->toBe([$observation->id])
        ->and($sessions[$wrongYear->id]['observations'])->toBeEmpty();
});

it('shows a session of another observer that holds an observation of the owner', function () {
    $other       = User::factory()->create(['username' => 'host', 'slug' => 'host', 'name' => 'Star Party Host']);
    $session     = blogSession($other, $this->location, 'Star party at the host', '2024-06-01 21:00:00', '2024-06-02 03:00:00');
    $observation = blogObservation($this->owner, $this->location, '2024-06-01', '23:00:00', 'M 57');
    linkToSession($session, $observation);

    $this->get(route('observer.blog', ['observer' => 'blog-owner']))
        ->assertOk()
        ->assertSee('Star party at the host')
        ->assertSee('Star Party Host');

    [$ids] = walkBlog($this->owner);
    expect($ids)->toBe([$observation->id]);
});

it('pages through all observations without losing or repeating one', function () {
    $expected = [];
    $start    = Carbon::parse('2023-01-01');
    for ($day = 0; $day < 3 * ObserverBlog::PER_KIND; $day++) {
        $date       = $start->copy()->addDays($day * 2)->toDateString();
        $expected[] = blogObservation($this->owner, $this->location, $date, '21:00:00', 'NGC ' . ($day + 1))->id;
        // The morning after: the same night
        $expected[] = blogObservation($this->owner, $this->location, $start->copy()->addDays($day * 2 + 1)->toDateString(), '03:00:00', 'IC ' . ($day + 1))->id;
    }
    for ($i = 0; $i < ObserverBlog::PER_KIND + 3; $i++) {
        $session     = blogSession($this->owner, $this->location, 'Session ' . $i, $start->copy()->addDays($i * 5)->setTime(20, 0)->toDateTimeString(), $start->copy()->addDays($i * 5)->setTime(23, 0)->toDateTimeString());
        $observation = blogObservation($this->owner, $this->location, $start->copy()->addDays($i * 5)->toDateString(), '22:00:00', 'M ' . ($i + 1));
        linkToSession($session, $observation);
        $expected[] = $observation->id;
    }

    [$ids, $pages] = walkBlog($this->owner);

    expect($pages)->toBeGreaterThan(1)
        ->and($ids)->toEqualCanonicalizing($expected)
        ->and(count($ids))->toBe(count(array_unique($ids)));
});

it('shows when a drawing was chosen as sketch of the week or month, from that day on', function () {
    $drawing = blogObservation($this->owner, $this->location, '2024-04-06', '22:00:00', 'NGC 4565', 'Edge-on with a dust lane');
    $drawing->forceFill(['has_drawing' => true, 'drawing_filename' => 'drawings/ngc4565.jpg'])->save();
    $later = blogObservation($this->owner, $this->location, '2024-04-07', '22:00:00', 'NGC 4631');
    $later->forceFill(['has_drawing' => true, 'drawing_filename' => 'drawings/ngc4631.jpg'])->save();

    SketchOfTheWeek::query()->forceCreate(['observation_id' => $drawing->id, 'user_id' => $this->owner->id, 'date' => '2024-04-14']);
    SketchOfTheMonth::query()->forceCreate(['observation_id' => $drawing->id, 'user_id' => $this->owner->id, 'date' => '2024-05-08']);
    // Chosen for a later Sunday: hidden until then
    SketchOfTheWeek::query()->forceCreate(['observation_id' => $later->id, 'user_id' => $this->owner->id, 'date' => now()->addWeeks(2)->toDateString()]);

    $sketches = (new ObserverBlog($this->owner))->page()['entries']->where('kind', 'sketch')->values();

    expect($sketches)->toHaveCount(2)
        ->and($sketches[0]['sketch_of'])->toBe('month')
        ->and($sketches[0]['date']->toDateString())->toBe('2024-05-08')
        ->and($sketches[1]['sketch_of'])->toBe('week')
        ->and($sketches[1]['observation']->id)->toBe($drawing->id);

    $this->get(route('observer.blog', ['observer' => 'blog-owner']))
        ->assertOk()
        ->assertSee('NGC 4565 was chosen as sketch of the month')
        ->assertSee('NGC 4565 was chosen as sketch of the week')
        ->assertDontSee('NGC 4631 was chosen');

    $feed = $this->get(route('feed.observer', ['observer' => 'blog-owner']))->assertOk()->getContent();
    expect(simplexml_load_string($feed))->not->toBeFalse()
        ->and($feed)->toContain('NGC 4565 by Blog Owner chosen as sketch of the week')
        ->and($feed)->toContain('NGC 4565 by Blog Owner chosen as sketch of the month')
        ->and($feed)->not->toContain('NGC 4631 by Blog Owner chosen');
});

it('refuses a malformed page date', function () {
    $this->get(route('observer.blog', ['observer' => 'blog-owner', 'before' => '2024-13-45']))->assertNotFound();
});

it('has an rss feed with the sessions, observations and public lists of the observer', function () {
    $session = blogSession($this->owner, $this->location, 'Frosty night at the dark site', '2024-01-12 19:30:00', '2024-01-13 00:45:00', [
        'comments' => '<p>Steady transparency all night.</p>',
    ]);
    linkToSession($session, blogObservation($this->owner, $this->location, '2024-01-12', '21:15:00', 'M 42', 'Bright core'));
    ObservingList::query()->create(['owner_user_id' => $this->owner->id, 'name' => 'Winter planetaries', 'public' => true]);
    ObservingList::query()->create(['owner_user_id' => $this->owner->id, 'name' => 'My secret list', 'public' => false]);

    $response = $this->get(route('feed.observer', ['observer' => 'blog-owner']))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');

    $xml    = simplexml_load_string($response->getContent());
    $titles = [];
    foreach ($xml->channel->item as $item) {
        $titles[] = (string) $item->title;
    }

    expect($xml)->not->toBeFalse()
        ->and((string) $xml->channel->link)->toBe(route('observer.blog', ['observer' => 'blog-owner']))
        ->and($titles)->toContain('Frosty night at the dark site')
        ->and($titles)->toContain('M 42 – Blog Owner')
        ->and(implode(' ', $titles))->toContain('Winter planetaries')
        ->and(implode(' ', $titles))->not->toContain('My secret list')
        ->and($response->getContent())->toContain('Steady transparency all night.');
});
