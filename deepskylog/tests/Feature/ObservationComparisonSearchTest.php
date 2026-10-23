<?php

use App\Livewire\AdvancedObjectSearch;
use App\Livewire\AdvancedObjectSearchTable;
use App\Models\Observation;
use App\Models\SearchIndex;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

// The "seen with one instrument or location, not yet with another" filter of
// the advanced object search (App\Services\ObservationComparison).

function comparisonObject(string $name): void
{
    DB::table('search_index')->insert([
        'name' => $name,
        'name_normalized' => mb_strtolower($name),
        'source_table' => 'objects',
        'source_pk' => Str::slug($name),
        'display_name' => $name,
        'source_type' => 'GALXY',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function comparisonObservation(string $username, string $object, array $attributes = [], bool $seen = true): int
{
    $id = (int) DB::table('observations')->insertGetId($attributes + [
        'observer_username' => $username, 'observed_on' => '2024-01-01',
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => $object, 'target_name' => $object,
        'has_drawing' => false, 'language' => 'en', 'created_at' => now(), 'updated_at' => now(),
    ]);
    if (!$seen) {
        DB::table('deepsky_observation_details')->insert(['observation_id' => $id, 'visibility' => Observation::VISIBILITY_NOT_SEEN]);
    }

    return $id;
}

/**
 * The names the results table lists for these filters.
 */
function comparisonResults(array $filters): array
{
    SearchIndex::markUniqueNames();
    $table = new AdvancedObjectSearchTable();
    $table->mount($filters);

    return $table->datasource()->orderBy('search_index.name')->pluck('search_index.name')->all();
}

beforeEach(function () {
    foreach (['M 31', 'M 33', 'M 51', 'M 81', 'M 101'] as $name) {
        comparisonObject($name);
    }
    $this->user = User::factory()->create(['username' => 'comparer']);
});

it('finds the objects seen with one instrument but not yet with another', function () {
    comparisonObservation('comparer', 'M 31', ['instrument_id' => 1]);
    comparisonObservation('comparer', 'M 33', ['instrument_id' => 1]);
    comparisonObservation('comparer', 'M 33', ['instrument_id' => 2]);
    // Tried with the second, but not seen
    comparisonObservation('comparer', 'M 51', ['instrument_id' => 1]);
    comparisonObservation('comparer', 'M 51', ['instrument_id' => 2], seen: false);
    // Not seen with the first
    comparisonObservation('comparer', 'M 81', ['instrument_id' => 1], seen: false);
    // Someone else's observation
    comparisonObservation('other', 'M 101', ['instrument_id' => 1]);

    $this->actingAs($this->user);

    expect(comparisonResults(['compare_by' => 'instrument', 'compare_seen' => '1', 'compare_not_seen' => '2']))
        ->toBe(['M 31', 'M 51'])
        ->and(comparisonResults(['compare_by' => 'instrument', 'compare_seen' => '2', 'compare_not_seen' => '1']))
        ->toBe([]);
});

it('finds the objects seen at one location but not yet at another', function () {
    comparisonObservation('comparer', 'M 31', ['location_id' => 7]);
    comparisonObservation('comparer', 'M 33', ['location_id' => 7]);
    comparisonObservation('comparer', 'M 33', ['location_id' => 8]);

    $this->actingAs($this->user);

    expect(comparisonResults(['compare_by' => 'location', 'compare_seen' => '7', 'compare_not_seen' => '8']))
        ->toBe(['M 31']);
});

it('compares the sky of the observation, else of its location', function () {
    $location = fn (string $name, array $sky = []) => (int) DB::table('locations')->insertGetId($sky + [
        'name' => $name, 'longitude' => 4.35, 'latitude' => 50.85, 'timezone' => 'UTC',
        'limitingMagnitude' => -999, 'skyBackground' => -999, 'elevation' => 0, 'country' => 'Belgium',
        'active' => true, 'user_id' => $this->user->id, 'observer' => 'comparer', 'observations' => 0, 'slug' => Str::slug($name),
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $poor = $location('Town', ['skyBackground' => 19.5]);
    $dark = $location('Ardennes', ['limitingMagnitude' => 6.8]);
    $unknown = $location('Somewhere');

    // Only seen under a poor sky
    comparisonObservation('comparer', 'M 31', ['location_id' => $poor]);
    // Seen under both
    comparisonObservation('comparer', 'M 33', ['location_id' => $poor]);
    comparisonObservation('comparer', 'M 33', ['location_id' => $dark]);
    // At the poor location, but the observation's own SQM was dark
    comparisonObservation('comparer', 'M 51', ['location_id' => $poor, 'sqm' => 21.4]);
    // Only seen under a dark sky (by the observation's limiting magnitude)
    comparisonObservation('comparer', 'M 81', ['location_id' => $poor, 'limiting_magnitude' => 6.9]);
    // The sky is unknown
    comparisonObservation('comparer', 'M 101', ['location_id' => $unknown]);

    $this->actingAs($this->user);

    expect(comparisonResults(['compare_by' => 'sky', 'compare_sqm' => '21']))
        ->toBe(['M 31'])
        ->and(comparisonResults(['compare_by' => 'sky', 'compare_sqm' => '21', 'compare_sky_direction' => 'dark_not_poor']))
        ->toBe(['M 51', 'M 81']);
});

it('matches nothing for a guest or an incomplete comparison', function () {
    comparisonObservation('comparer', 'M 31', ['instrument_id' => 1]);

    expect(comparisonResults(['compare_by' => 'instrument', 'compare_seen' => '1', 'compare_not_seen' => '2']))->toBe([]);

    $this->actingAs($this->user);

    expect(comparisonResults(['compare_by' => 'instrument', 'compare_seen' => '1']))->toBe([])
        ->and(comparisonResults(['compare_by' => 'sky', 'compare_sqm' => '25']))->toBe([])
        ->and(comparisonResults(['compare_by' => 'instrument', 'compare_seen' => '1', 'compare_not_seen' => '2']))->toBe(['M 31']);
});

it('offers the filter only to observers who are logged in', function () {
    expect(Livewire::test(AdvancedObjectSearch::class)->get('availableFilterTypes'))
        ->not->toHaveKey('compareObservations');

    $this->actingAs($this->user);

    expect(Livewire::test(AdvancedObjectSearch::class)->get('availableFilterTypes'))
        ->toHaveKey('compareObservations');
});

it('puts the comparison in the query string of the results', function () {
    $this->actingAs($this->user);

    Livewire::test(AdvancedObjectSearch::class)
        ->call('addFilter', 'compareObservations')
        ->set('compareSeen', '1')
        ->set('compareNotSeen', '2')
        ->call('search')
        ->assertRedirect(route('search.advanced.results', [
            'compare_by' => 'instrument', 'compare_seen' => '1', 'compare_not_seen' => '2',
        ]));
});

it('refuses an incomplete comparison', function () {
    $this->actingAs($this->user);

    Livewire::test(AdvancedObjectSearch::class)
        ->call('addFilter', 'compareObservations')
        ->set('compareSeen', '1')
        ->call('search')
        ->assertNoRedirect()
        ->assertSet('errorMessage', __('Choose both what you saw the objects with and what you did not see them with yet.'))
        ->set('compareNotSeen', '1')
        ->call('search')
        ->assertSet('errorMessage', __('Choose two different ones to compare.'))
        ->set('compareBy', 'sky')
        ->set('compareSqm', '9')
        ->call('search')
        ->assertSet('errorMessage', __('Enter an SQM between :min and :max.', ['min' => 10, 'max' => 22]));
});

it('restores the comparison of a saved search', function () {
    $this->actingAs($this->user);

    $component = Livewire::test(AdvancedObjectSearch::class, ['filters' => [
        'compare_by' => 'sky', 'compare_sqm' => '20.5', 'compare_sky_direction' => 'dark_not_poor',
    ]]);

    expect($component->get('activeFilters'))->toContain('compareObservations')
        ->and($component->get('compareBy'))->toBe('sky')
        ->and($component->get('compareSqm'))->toBe('20.5')
        ->and($component->get('compareSkyDirection'))->toBe('dark_not_poor');
});
