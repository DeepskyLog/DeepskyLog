<?php

use App\Livewire\AdvancedObjectSearchTable;
use App\Livewire\MessierMarathonPlanner;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\MessierMarathon;
use App\Services\NightWindow;
use Carbon\Carbon;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

// The Messier marathon planner: the nights of March and April near the new
// Moon and the order to see the Messier objects in (MessierMarathon), made an
// observing list on /observing-lists/messier-marathon.

/** The new Moons of the 2027 season. */
const MM_NEW_MOON_MARCH = '2027-03-08';
const MM_NEW_MOON_APRIL = '2027-04-06';

/**
 * A Messier object (RA in hours), with its name in the M catalog unless
 * $catalogName is false.
 */
function mmObject(int $number, float $ra, float $decl, bool $catalogName = true): void
{
    $name = 'M '.$number;
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => 'GALXY', 'con' => 'AND',
        'ra'            => $ra, 'decl' => $decl, 'mag' => 8.0, 'subr' => 13.0, 'pa' => 0, 'urano' => 0,
        'urano_new'     => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 300.0, 'diam2' => 200.0,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP'         => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
    DB::table('search_index')->insertOrIgnore([
        'name'         => $name, 'name_normalized' => mb_strtolower($name), 'source_table' => 'objects', 'source_pk' => Str::slug($name),
        'display_name' => $name, 'source_type' => 'GALXY', 'ra' => $ra, 'decl' => $decl, 'name_unique' => true,
        'created_at'   => now(), 'updated_at' => now(),
    ]);
    if ($catalogName) {
        DB::table('objectnames')->insertOrIgnore([
            'objectname' => $name, 'catalog' => 'M', 'catindex' => (string) $number, 'altname' => $name,
            'slug'       => Str::slug($name), 'timestamp' => now(),
        ]);
    }
}

/**
 * Messier objects all over the sky; M 65 only by its name (a migration adds
 * its catalog name, which an SQL import may lose), and M 111, which is not
 * Messier's.
 */
function mmSky(): void
{
    DB::table('objectnames')->where('objectname', 'M 65')->delete();
    mmObject(77, 2.711, -0.013);
    mmObject(74, 1.611, 15.78);
    mmObject(31, 0.712, 41.27);
    mmObject(42, 5.588, -5.39);
    mmObject(44, 8.67, 19.67);
    mmObject(81, 9.926, 69.07);
    mmObject(65, 11.315, 13.09, false);
    mmObject(87, 12.514, 12.39);
    mmObject(13, 16.695, 36.46);
    mmObject(7, 17.9, -34.8);
    mmObject(30, 21.673, -23.18);
    mmObject(111, 1.1111, 1.11111);
}

function mmObserver(): User
{
    // in the Observer role, which makes observing lists
    $team = test()->getTeamFromString('Observers');
    $user = User::factory()->create();
    $user->teams()->attach($team);
    $user->switchTeam($team);
    $location = LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Leuven',
        'latitude'                                        => 50.85, 'longitude' => 4.35, 'timezone' => 'Europe/Brussels', 'active' => true]);
    $user->forceFill(['stdlocation' => $location->id])->save();

    return $user->fresh();
}

function mmLeuven(int $minutes = MessierMarathon::MINUTES_PER_OBJECT): MessierMarathon
{
    return new MessierMarathon(50.85, 4.35, 0.0, 'Europe/Brussels', MessierMarathon::MIN_ALTITUDE, $minutes);
}

beforeEach(fn () => Carbon::setTestNow('2026-10-26 20:00:00'));
afterEach(fn () => Carbon::setTestNow());

it('plans the marathon for the coming seasons', function () {
    $keys = fn (string $date) => array_column(MessierMarathon::seasons($date), 'key');

    expect($keys('2026-10-26'))->toBe(['2026-autumn', '2027-spring', '2027-autumn'])
        ->and($keys('2026-12-01'))->toBe(['2027-spring', '2027-autumn', '2028-spring'])
        ->and($keys('2027-04-30'))->toBe(['2027-spring', '2027-autumn', '2028-spring'])
        ->and($keys('2027-05-01'))->toBe(['2027-autumn', '2028-spring', '2028-autumn']);
});

it('finds the Messier objects by their catalog name or their name', function () {
    mmSky();

    expect(array_column(MessierMarathon::objects(), 'name'))->toBe(['M 7', 'M 13', 'M 30', 'M 31', 'M 42', 'M 44', 'M 65', 'M 74', 'M 77', 'M 81', 'M 87']);
});

it('runs the night between the ends of nautical twilight', function () {
    $dark     = NightWindow::for(50.85, 4.35, MM_NEW_MOON_APRIL, 'Europe/Brussels');
    $nautical = NightWindow::for(50.85, 4.35, MM_NEW_MOON_APRIL, 'Europe/Brussels', ['nautical']);

    expect($nautical->twilight)->toBe('nautical')
        ->and($nautical->start)->toBeLessThan($dark->start)
        ->and($nautical->end)->toBeGreaterThan($dark->end)
        ->and(mmLeuven()->window(MM_NEW_MOON_APRIL)->start)->toBe($nautical->start);
});

it('gives the order to see the objects in, each within its window', function () {
    mmSky();
    $plan  = mmLeuven()->plan(MM_NEW_MOON_MARCH);
    $order = array_column($plan['objects'], 'name');

    // From Leuven M 7 and M 30 never get 5° high in the dark or the twilight
    expect($order)->toHaveCount(9)
        ->and(array_column($plan['missed'], 'name'))->toBe(['M 7', 'M 30'])
        ->and(array_column($plan['missed'], 'reason'))->toBe(['never', 'never'])
        // the evening objects in the west first, M 13 rising in the east last
        ->and(array_slice($order, 0, 2))->toEqualCanonicalizing(['M 74', 'M 77'])
        ->and(end($order))->toBe('M 13')
        ->and(array_search('M 87', $order))->toBeGreaterThan(array_search('M 44', $order));

    $previous = null;
    foreach ($plan['objects'] as $object) {
        expect($object['at'])->toBeGreaterThanOrEqual($object['from'])
            ->and($object['at'])->toBeLessThanOrEqual($object['until'])
            ->and($object['altitude'])->toBeGreaterThanOrEqual(MessierMarathon::MIN_ALTITUDE - 0.1)
            ->and($object['twilight'])->toBeFalse();
        if ($previous !== null) {
            expect($object['at'] - $previous)->toBeGreaterThanOrEqual(MessierMarathon::MINUTES_PER_OBJECT * 60);
        }
        $previous = $object['at'];
    }

    // An object that just rose is planned when it stands higher, M 81 near
    // its transit rather than at the start of the night
    $m81 = collect($plan['objects'])->firstWhere('name', 'M 81');
    expect($m81['at'])->toBeGreaterThan($plan['start'] + 3600);
});

it('marks objects only high enough in the twilight', function () {
    mmSky();
    // On the first of April M 77 sets by the end of nautical twilight in
    // Leuven: it is seen in the twilight, at the darkest moment it can be.
    $plan = mmLeuven()->plan('2027-04-01');
    $m77  = collect($plan['objects'])->firstWhere('name', 'M 77');

    expect($m77['twilight'])->toBeTrue()
        ->and($m77['at'])->toBeLessThan($plan['start'])
        ->and($m77['at'])->toBe($m77['until'])
        ->and($plan['objects'][0]['name'])->toBe('M 77')
        ->and(collect($plan['objects'])->firstWhere('name', 'M 31')['twilight'])->toBeFalse();
});

it('keeps the objects in the twilight apart', function () {
    // Three objects at the place of M 77, which then sets in the twilight
    mmObject(1, 2.711, -0.013);
    mmObject(2, 2.711, -0.013);
    mmObject(3, 2.711, -0.013);

    $objects = mmLeuven()->plan('2027-04-01')['objects'];

    expect(array_column($objects, 'twilight'))->toBe([true, true, true])
        ->and($objects[1]['at'] - $objects[0]['at'])->toBe(MessierMarathon::MINUTES_PER_OBJECT * 60)
        ->and($objects[2]['at'] - $objects[1]['at'])->toBe(MessierMarathon::MINUTES_PER_OBJECT * 60)
        ->and($objects[2]['at'])->toBe($objects[2]['until']);
});

it('misses the objects there is no time for', function () {
    // Three objects at the place of M 77, which at the end of March sets
    // minutes after the start of the night: at a quarter of an hour each,
    // only one is seen.
    mmObject(1, 2.711, -0.013);
    mmObject(2, 2.711, -0.013);
    mmObject(3, 2.711, -0.013);

    $plan = mmLeuven(15)->plan('2027-03-28');

    expect(array_column($plan['objects'], 'name'))->toBe(['M 1'])
        ->and(array_column($plan['missed'], 'name'))->toBe(['M 2', 'M 3'])
        ->and(array_column($plan['missed'], 'reason'))->toBe(['time', 'time']);
});

it('gives the best nights near the new Moon', function () {
    mmSky();
    $nights = mmLeuven()->nights(2027);
    $best   = array_values(array_filter($nights, fn (array $night) => $night['best']));

    expect($nights)->toHaveCount(61)
        ->and($nights[0]['night'])->toBe('2027-03-01')
        ->and(end($nights)['night'])->toBe('2027-04-30')
        ->and($best)->toHaveCount(MessierMarathon::BEST_NIGHTS);

    foreach ($best as $night) {
        expect($night['near_new_moon'])->toBeTrue()
            ->and($night['moon']['light'])->toBeLessThanOrEqual(MessierMarathon::MAX_MOON_LIGHT);
    }

    // The nights of new Moon have no Moon light; the full Moon is up all night.
    $byNight = collect($nights)->keyBy('night');
    expect($byNight[MM_NEW_MOON_MARCH]['moon']['light'])->toBe(0.0)
        ->and($byNight[MM_NEW_MOON_MARCH]['near_new_moon'])->toBeTrue()
        ->and($byNight['2027-03-22']['near_new_moon'])->toBeFalse();
});

it('makes the observing list of the marathon', function () {
    mmSky();
    $user = mmObserver();
    $this->actingAs($user);

    $this->get(route('observing-list.messier-marathon'))->assertOk()->assertSee('Messier marathon planner');

    Livewire::test(MessierMarathonPlanner::class)
        ->assertSet('season', '2026-autumn')
        ->assertSee('October and November 2026')
        ->assertSee('Best')
        ->set('season', '2027-spring')
        ->call('plan', MM_NEW_MOON_MARCH)
        ->assertSee('M 81')
        ->assertSee('Leuven')
        ->set('name', 'My marathon')
        ->call('create')
        ->assertRedirect();

    $list  = ObservingList::where('owner_user_id', $user->id)->firstOrFail();
    $items = $list->items()->orderBy('sort_order')->get();

    expect($list->name)->toBe('My marathon')
        ->and($list->description)->toContain('Messier marathon in the night of March 8, 2027 from Leuven')
        ->and($items)->toHaveCount(11)
        ->and($items->pluck('object_name')->slice(-2)->values()->all())->toBe(['M 7', 'M 30'])
        ->and($items->last()->item_description)->toBe('Not high enough in this night.')
        ->and($items->last()->planned_at)->toBeNull()
        ->and($list->planned_timezone)->toBe('Europe/Brussels')
        ->and($list->hasPlannedTimes())->toBeTrue();

    // The objects of the night have their time and window, in the order of
    // the list.
    $planned = $items->slice(0, 9)->values();
    foreach ($planned as $index => $item) {
        expect($item->item_description)->toBeNull()
            ->and($item->planned_at->between($item->planned_from, $item->planned_until))->toBeTrue();
        if ($index > 0) {
            expect($item->planned_at->gt($planned[$index - 1]->planned_at))->toBeTrue();
        }
    }

    // The list page shows the times in the time zone of the location, in
    // their order, and the months of the seasonal planner.
    $table = Livewire::withoutLazyLoading()->test(AdvancedObjectSearchTable::class, ['filters' => ['observing_lists' => [(string) $list->id], 'observing_lists_mode' => 'in']]);
    $time  = $planned[0]->planned_at->setTimezone('Europe/Brussels')->format('H:i');
    $html  = $table->html();
    expect($html)->toContain('Time (Europe/Brussels)')
        ->and($html)->toContain('Months')
        ->and($html)->toContain('>'.$time.'<')
        ->and(strpos($html, '>M 81<'))->toBeLessThan(strpos($html, '>M 13<'))
        ->and(strpos($html, '>M 77<'))->toBeLessThan(strpos($html, '>M 81<'));
});

it('plans the autumn marathon', function () {
    mmSky();
    $nights = mmLeuven()->nights(2026, 'autumn');

    expect($nights)->toHaveCount(61)
        ->and($nights[0]['night'])->toBe('2026-10-01')
        ->and(end($nights)['night'])->toBe('2026-11-30')
        ->and(array_filter($nights, fn (array $night) => $night['best']))->toHaveCount(MessierMarathon::BEST_NIGHTS);
});

it('shows no times for a list without them', function () {
    mmSky();
    $user = mmObserver();
    $this->actingAs($user);
    $list = ObservingList::createWithObjects($user, 'Plain list', ['M 31', 'M 13']);

    $html = Livewire::withoutLazyLoading()->test(AdvancedObjectSearchTable::class, ['filters' => ['observing_lists' => [(string) $list->id], 'observing_lists_mode' => 'in']])->html();
    expect($html)->not->toContain('Time (')
        ->and($html)->toContain('>M 31<');
});

it('validates the answers of the planner', function () {
    mmSky();
    $this->actingAs(mmObserver());

    Livewire::test(MessierMarathonPlanner::class)
        ->set('minutes', 50)
        ->set('minAltitude', -5)
        ->call('create')
        ->assertHasErrors(['minutes', 'minAltitude']);

    expect(ObservingList::count())->toBe(0);
});

it('needs a login', function () {
    $this->get(route('observing-list.messier-marathon'))->assertRedirect(route('login'));
});
