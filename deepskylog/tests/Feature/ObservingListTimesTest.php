<?php

use App\Livewire\ObservingListTimesPanel;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\ObservingListTimes;
use Carbon\Carbon;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

// The times of an observing list for a night (ObservingListTimes): proposed
// for the chosen hours on the list page, saved in the list or as a new list,
// and typed for one object on its edit page.

/** A night of new Moon in Leuven. */
const LT_NIGHT = '2026-11-09';

function ltObject(string $name, float $ra, float $decl): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => 'GALXY', 'con' => 'AND',
        'ra'            => $ra, 'decl' => $decl, 'mag' => 8.0, 'subr' => 13.0, 'pa' => 0, 'urano' => 0,
        'urano_new'     => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 300.0, 'diam2' => 200.0,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP'         => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function ltObserver(): User
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

/**
 * A list with M 31 (high all evening in November), M 42 (rising in the
 * evening), M 7 (never 20° high in Leuven), Jupiter and an unknown object.
 */
function ltList(User $owner, bool $public = false): ObservingList
{
    ltObject('M 31', 0.712, 41.27);
    ltObject('M 42', 5.588, -5.39);
    ltObject('M 7', 17.9, -34.8);
    $planetId = DB::table('planets')->insertGetId(['name' => 'Jupiter', 'slug' => 'jupiter']);
    DB::table('search_index')->insert([
        'name'         => 'Jupiter', 'name_normalized' => 'jupiter', 'source_table' => 'planets', 'source_pk' => (string) $planetId,
        'display_name' => 'Jupiter', 'source_type' => 'planet', 'name_unique' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);

    return ObservingList::createWithObjects($owner, 'Autumn', ['M 7', 'M 42', 'Jupiter', 'Nothing Known', 'M 31'], null, $public);
}

beforeEach(fn () => Carbon::setTestNow('2026-10-26 20:00:00'));
afterEach(fn () => Carbon::setTestNow());

it('proposes a time for each object high enough in the dark hours', function () {
    $owner    = ltObserver();
    $list     = ltList($owner);
    $proposal = ObservingListTimes::propose($list, $owner->standardLocation, LT_NIGHT, null, null);

    expect(array_column($proposal['objects'], 'name'))->toEqualCanonicalizing(['M 31', 'M 42', 'Jupiter'])
        ->and($proposal['missed'])->toEqualCanonicalizing([['name' => 'M 7', 'reason' => 'never'], ['name' => 'Nothing Known', 'reason' => 'unknown']])
        ->and($proposal['timezone'])->toBe('Europe/Brussels');

    $previous = null;
    foreach ($proposal['objects'] as $object) {
        expect($object['at'])->toBeGreaterThanOrEqual($object['from'])
            ->and($object['at'])->toBeLessThanOrEqual($object['until'])
            ->and($object['altitude'])->toBeGreaterThanOrEqual(ObservingListTimes::MIN_ALTITUDE - 0.1);
        if ($previous !== null) {
            expect($object['at'] - $previous)->toBeGreaterThanOrEqual(ObservingListTimes::MINUTES_PER_OBJECT * 60);
        }
        $previous = $object['at'];
    }
});

it('proposes times in the hours chosen, with time between the objects', function () {
    $owner    = ltObserver();
    $list     = ltList($owner);
    $proposal = ObservingListTimes::propose($list, $owner->standardLocation, LT_NIGHT, '20:00', '01:30', 20, 10, 5);

    expect(Carbon::createFromTimestamp($proposal['start'], 'Europe/Brussels')->format('Y-m-d H:i'))->toBe('2026-11-09 20:00')
        ->and(Carbon::createFromTimestamp($proposal['end'], 'Europe/Brussels')->format('Y-m-d H:i'))->toBe('2026-11-10 01:30');

    $times = array_column($proposal['objects'], 'at');
    foreach (array_slice($times, 1) as $index => $at) {
        expect($at - $times[$index])->toBeGreaterThanOrEqual(15 * 60);
    }
    foreach ($times as $at) {
        expect($at)->toBeGreaterThanOrEqual($proposal['start'])->and($at)->toBeLessThanOrEqual($proposal['end']);
    }
});

it('saves the times in the list, in their order', function () {
    $owner    = ltObserver();
    $list     = ltList($owner);
    $proposal = ObservingListTimes::propose($list, $owner->standardLocation, LT_NIGHT, null, null);
    ObservingListTimes::save($list, $owner->standardLocation, $proposal);

    $list->refresh();
    $items = $list->items()->orderBy('sort_order')->get();
    $timed = array_column($proposal['objects'], 'name');

    expect($list->planned_night->toDateString())->toBe(LT_NIGHT)
        ->and($list->planned_timezone)->toBe('Europe/Brussels')
        ->and($list->planned_location_id)->toBe($owner->standardLocation->id)
        ->and($items->pluck('object_name')->slice(0, 3)->values()->all())->toBe($timed)
        // the others keep the order they had
        ->and($items->pluck('object_name')->slice(3)->values()->all())->toBe(['M 7', 'Nothing Known'])
        ->and($items->first()->planned_at->getTimestamp())->toBe($proposal['objects'][0]['at'])
        ->and($items->last()->planned_at)->toBeNull();

    ObservingListTimes::clear($list);
    expect($list->fresh()->hasPlannedTimes())->toBeFalse()
        ->and($list->fresh()->planned_night)->toBeNull();
});

it('lets the owner save the times and others save them as a new list', function () {
    $owner = ltObserver();
    $list  = ltList($owner, public: true);

    $this->actingAs($owner);
    Livewire::test(ObservingListTimesPanel::class, ['listId' => $list->id])
        ->assertSet('night', '2026-10-26')
        ->set('night', LT_NIGHT)
        ->call('propose')
        ->assertSet('showProposal', true)
        ->assertSee('Jupiter')
        ->assertSee('Position unknown:')
        ->call('save')
        ->assertRedirect(route('observing-list.show', $list));
    expect($list->fresh()->hasPlannedTimes())->toBeTrue();

    // Another observer plans the public list for another night in a list of
    // their own; the list itself keeps its times.
    $other = ltObserver();
    $this->actingAs($other);
    Livewire::test(ObservingListTimesPanel::class, ['listId' => $list->id])
        ->set('night', '2026-11-10')
        ->set('gap', 3)
        ->call('propose')
        ->call('save')
        ->assertForbidden();

    Livewire::test(ObservingListTimesPanel::class, ['listId' => $list->id])
        ->set('night', '2026-11-10')
        ->set('newName', 'My autumn night')
        ->call('saveAsNew')
        ->assertRedirect();

    $copy = ObservingList::where('owner_user_id', $other->id)->firstOrFail();
    expect($copy->name)->toBe('My autumn night')
        ->and($copy->planned_night->toDateString())->toBe('2026-11-10')
        ->and($copy->items()->count())->toBe(5)
        ->and($copy->items()->whereNotNull('planned_at')->count())->toBe(3)
        ->and($list->fresh()->planned_night->toDateString())->toBe(LT_NIGHT);
});

it('validates the answers of the panel', function () {
    $owner = ltObserver();
    $list  = ltList($owner);
    $this->actingAs($owner);

    Livewire::test(ObservingListTimesPanel::class, ['listId' => $list->id])
        ->set('from', '21:00')
        ->set('minutes', 0)
        ->set('gap', 99)
        ->call('propose')
        ->assertHasErrors(['until', 'minutes', 'gap'])
        ->assertSet('showProposal', false);
});

it('gives an object a time on its edit page', function () {
    $owner = ltObserver();
    $list  = ltList($owner);
    $item  = $list->items()->where('object_name', 'M 31')->firstOrFail();
    $this->actingAs($owner);

    // (the edit page itself uses CHAR_LENGTH, which SQLite does not know)
    // The first time gives the list its night; Brussels is UTC+1 in November.
    $this->patch(route('observing-list.items.update', [$list, $item->id]), ['item_description' => 'Dust lanes', 'planned_time' => '22:30', 'planned_night' => LT_NIGHT])
        ->assertRedirect(route('observing-list.show', $list));
    expect($list->fresh()->planned_night->toDateString())->toBe(LT_NIGHT)
        ->and($item->fresh()->planned_at->toDateTimeString())->toBe('2026-11-09 21:30:00')
        ->and($item->fresh()->item_description)->toBe('Dust lanes');

    // A time before noon is in the morning after
    $other = $list->items()->where('object_name', 'M 42')->firstOrFail();
    $this->patch(route('observing-list.items.update', [$list, $other->id]), ['planned_time' => '01:15']);
    expect($other->fresh()->planned_at->toDateTimeString())->toBe('2026-11-10 00:15:00');

    $this->patch(route('observing-list.items.update', [$list, $item->id]), ['item_description' => 'Dust lanes', 'planned_time' => '']);
    expect($item->fresh()->planned_at)->toBeNull();
});
