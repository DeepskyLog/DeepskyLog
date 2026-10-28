<?php

use App\Http\Livewire\QuickSearch;
use App\Models\Observation;
use App\Models\User;
use App\Services\ObservationSearchQuery;
use Livewire\Livewire;

function searchNameMatchObservation(User $user, string $name, string $table = 'objects'): Observation
{
    return Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-01',
        'target_type' => $table === 'objects' ? 'deepsky' : 'comet',
        'target_table' => $table,
        'target_key' => $name,
        'target_name' => $name,
        'has_drawing' => false,
        'legacy_source' => null,
    ]);
}

function searchNameMatches(string $search): array
{
    return app(ObservationSearchQuery::class)->observations($search)
        ->orderBy('target_name')
        ->pluck('target_name')
        ->all();
}

beforeEach(function () {
    $user = User::factory()->create(['username' => 'alice', 'slug' => 'alice']);
    foreach (['M 2', 'M 2A', 'M 27', 'M 29', 'M 2-9', 'NGC 7000'] as $name) {
        searchNameMatchObservation($user, $name);
    }
    searchNameMatchObservation($user, 'C/2020 F3 (NEOWISE)', 'cometobjects');
});

it('matches a number in the observation search name whole', function () {
    expect(searchNameMatches('M 2'))->toBe(['M 2', 'M 2A'])
        ->and(searchNameMatches('m2'))->toBe(['M 2', 'M 2A'])
        ->and(searchNameMatches('M 2-9'))->toBe(['M 2-9'])
        ->and(searchNameMatches('NGC 700'))->toBe([]);
});

it('still matches the start of a name that does not end in a number', function () {
    expect(searchNameMatches('C/2020 F3'))->toBe(['C/2020 F3 (NEOWISE)'])
        ->and(searchNameMatches('NGC'))->toBe(['NGC 7000']);
});

it('treats * and % in the observation search name as wildcards', function () {
    expect(searchNameMatches('M 2*'))->toBe(['M 2', 'M 2-9', 'M 27', 'M 29', 'M 2A'])
        ->and(searchNameMatches('*7*'))->toBe(['M 27', 'NGC 7000'])
        ->and(searchNameMatches('%'))->toHaveCount(7);
});

it('offers new observation in the quick search only to logged-in observers', function () {
    Livewire::test(QuickSearch::class, ['observationActions' => true])
        ->assertSee(__('Search observations'))
        ->assertDontSee(__('New observation'));

    Livewire::actingAs(User::first())
        ->test(QuickSearch::class, ['observationActions' => true])
        ->assertSee(__('New observation'));
});

it('searches observations for the quick search name', function () {
    Livewire::test(QuickSearch::class, ['observationActions' => true])
        ->set('query', 'M 2')
        ->call('searchObservations')
        ->assertRedirect(route('observations.search', ['q' => 'M 2']));
});

it('starts a new observation for the quick search name', function () {
    Livewire::test(QuickSearch::class, ['observationActions' => true])
        ->set('query', 'M 2')
        ->call('newObservation')
        ->assertRedirect(route('login'));

    Livewire::actingAs(User::first())
        ->test(QuickSearch::class, ['observationActions' => true])
        ->set('query', 'M 2')
        ->call('newObservation')
        ->assertRedirect(route('observation.create', ['slug' => 'M 2']));
});
