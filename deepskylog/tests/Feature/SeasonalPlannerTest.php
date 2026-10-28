<?php

use App\Livewire\AdvancedObjectSearchTable;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\SeasonalVisibility;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// The seasonal planner of an observing list: the months each object is well
// placed from the observer's location (SeasonalVisibility), and the filter on
// the list page that keeps the objects well placed in one month.

function seasonalObject(string $name, float $raHours, float $decl): void
{
    $slug = Str::slug($name);
    DB::table('objects')->insert([
        'name' => $name, 'slug' => $slug, 'type' => 'GALXY', 'con' => 'AND', 'ra' => $raHours, 'decl' => $decl,
        'mag' => 9.3, 'subr' => 12.4, 'pa' => 174, 'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '',
        'diam1' => 600.0, 'diam2' => 180.0, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0', 'DSLDP' => '0',
        'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0', 'DeepskyHunter' => '0', 'Interstellarum' => '0',
        'timestamp' => '2026-01-01 00:00:00',
    ]);
    seasonalIndexRow($name, 'objects', $slug, 'GALXY', $raHours);
}

function seasonalIndexRow(string $name, string $table, string $pk, string $type, ?float $ra = null): void
{
    DB::table('search_index')->insert([
        'name' => $name, 'name_normalized' => mb_strtolower($name), 'source_table' => $table, 'source_pk' => $pk,
        'display_name' => $name, 'source_type' => $type, 'ra' => $ra, 'name_unique' => true,
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

/**
 * An observer at 51° N with a list holding M 31 (autumn), M 42 (winter),
 * M 7 (at most 4° high there, in summer) and Jupiter.
 */
function seasonalListOwner(): array
{
    $owner = User::factory()->create();
    $locationId = DB::table('locations')->insertGetId([
        'name' => 'Leuven', 'longitude' => 4.7, 'latitude' => 50.9, 'timezone' => 'Europe/Brussels',
        'limitingMagnitude' => -999, 'skyBackground' => -999, 'elevation' => 0, 'country' => 'Belgium', 'active' => true,
        'user_id' => $owner->id, 'observer' => $owner->username, 'observations' => 0, 'slug' => 'leuven',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $owner->forceFill(['stdlocation' => $locationId])->save();

    seasonalObject('M 31', 0.712, 41.27);
    seasonalObject('M 42', 5.588, -5.39);
    seasonalObject('M 7', 17.9, -34.8);
    DB::table('planets')->insert(['name' => 'Jupiter']);
    seasonalIndexRow('Jupiter', 'planets', '1', 'planet', 7.0);

    $list = ObservingList::create(['owner_user_id' => $owner->id, 'name' => 'Seasons', 'public' => true]);
    foreach (['M 31', 'M 42', 'M 7', 'Jupiter'] as $name) {
        $list->items()->create(['object_name' => $name, 'source_mode' => 'manual', 'added_by_user_id' => $owner->id]);
    }

    return [$owner->fresh(), $list];
}

function seasonalTableNames(ObservingList $list, int $month): array
{
    $table = new AdvancedObjectSearchTable();
    $table->mount(['observing_lists' => [(string) $list->id], 'observing_lists_mode' => 'in', 'well_placed_month' => $month]);

    return $table->datasource()->get()->pluck('display_name')->sort()->values()->all();
}

beforeEach(fn () => Carbon::setTestNow('2026-10-26 20:00:00'));
afterEach(fn () => Carbon::setTestNow());

it('gives the months an object is well placed', function () {
    $seasons = SeasonalVisibility::at(50.9, 4.7, 2026, 10, 'Europe/Brussels');

    expect($seasons->wellPlacedMonths(0.712 * 15, 41.27))->toBe([1, 2, 7, 8, 9, 10, 11, 12])
        ->and($seasons->wellPlacedMonths(5.588 * 15, -5.39))->toBe([1, 2, 10, 11, 12])
        // M 7 never gets 30° high: it counts when it transits in the dark.
        ->and($seasons->isLow(-34.8))->toBeTrue()
        ->and($seasons->wellPlacedMonths(17.9 * 15, -34.8))->toBe([6, 7])
        // NGC 6397 never rises.
        ->and($seasons->isLow(-53.67))->toBeFalse()
        ->and($seasons->wellPlacedMonths(17.68 * 15, -53.67))->toBe([])
        // Polaris stands 50° high all night, every night.
        ->and($seasons->wellPlacedMonths(37.95, 89.26))->toBe(range(1, 12))
        ->and($seasons->isWellPlaced(0.712 * 15, 41.27, 10))->toBeTrue()
        ->and($seasons->isWellPlaced(0.712 * 15, 41.27, 4))->toBeFalse();
});

it('counts the objects just above the altitude at their highest as well', function () {
    $seasons = SeasonalVisibility::at(50.9, 4.7, 2026, 10, 'Europe/Brussels');

    // Highest at 29.6°, 30.2° and 33.7°: the first two never stay an hour
    // above 30°, so they count when they transit in the dark, like M 42.
    expect($seasons->isLow(-9.5))->toBeTrue()
        ->and($seasons->isLow(-8.9))->toBeTrue()
        ->and($seasons->isLow(-5.39))->toBeFalse()
        ->and($seasons->wellPlacedMonths(90.0, -9.5))->toBe([1, 2, 10, 11, 12])
        ->and($seasons->wellPlacedMonths(90.0, -8.9))->toBe([1, 2, 10, 11, 12]);
});

it('has no hours in the months without dark hours', function () {
    $seasons = SeasonalVisibility::at(70.0, 20.0, 2026, 6);

    expect($seasons->hasDarkHours(6))->toBeFalse()
        ->and($seasons->hours(37.95, 89.26)[6])->toBeNull()
        ->and($seasons->hasDarkHours(12))->toBeTrue()
        ->and($seasons->hours(37.95, 89.26)[12])->toBeGreaterThan(10.0);
});

it('keeps the objects of the list that are well placed in the month', function () {
    [$owner, $list] = seasonalListOwner();
    $this->actingAs($owner);

    expect(seasonalTableNames($list, 0))->toBe(['Jupiter', 'M 31', 'M 42', 'M 7'])
        ->and(seasonalTableNames($list, 10))->toBe(['M 31', 'M 42'])
        ->and(seasonalTableNames($list, 8))->toBe(['M 31'])
        ->and(seasonalTableNames($list, 7))->toBe(['M 31', 'M 7'])
        ->and(seasonalTableNames($list, 5))->toBe([]);
});

it('shows a cell per month for the deep-sky objects only', function () {
    [$owner, $list] = seasonalListOwner();
    $this->actingAs($owner);

    $table = new AdvancedObjectSearchTable();
    $table->mount(['observing_lists' => [(string) $list->id], 'observing_lists_mode' => 'in']);
    $rows = $table->datasource()->get()->keyBy('display_name');
    $fields = $table->fields()->fields;

    $m31 = $fields['seasons']($rows['M 31']);
    expect(substr_count($m31, 'title="'))->toBe(12)
        ->and(substr_count($m31, 'ring-blue-400'))->toBe(1)
        ->and($m31)->toContain('October: ')
        ->and($fields['seasons']($rows['Jupiter']))->not->toContain('October')
        ->and($fields['seasons_plain']($rows['M 42']))->toBe('Jan, Feb, Oct, Nov, Dec')
        ->and($fields['seasons_plain']($rows['M 7']))->toBe('Jun, Jul')
        ->and(substr_count($fields['seasons']($rows['M 7']), 'bg-yellow-600'))->toBe(2)
        ->and($fields['seasons']($rows['M 7']))->toContain('June: highest at 4° in the dark hours')
        ->and(collect($table->columns())->pluck('field'))->toContain('seasons');

    // The advanced search shows no months: it has no list.
    $search = new AdvancedObjectSearchTable();
    $search->mount([]);
    expect(collect($search->columns())->pluck('field'))->not->toContain('seasons');
});

it('offers the month filter on the list page', function () {
    [$owner, $list] = seasonalListOwner();

    $this->actingAs($owner)->get(route('observing-list.show', $list))
        ->assertOk()
        ->assertSee('Well placed in')
        ->assertSee(route('observing-list.show', ['list' => $list, 'month' => 10]), false)
        ->assertSee('from Leuven');

    $this->actingAs($owner)->get(route('observing-list.show', ['list' => $list, 'month' => 10]))
        ->assertOk()
        ->assertSee('<option value="10" selected', false)
        ->assertSee('Show all objects')
        ->assertDontSee("What's up this month", false);
});
