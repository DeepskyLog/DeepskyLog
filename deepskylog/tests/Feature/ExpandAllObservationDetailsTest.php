<?php

use App\Livewire\ObservationSearchTable;
use App\Models\Observation;
use App\Models\User;
use Livewire\Livewire;

it('offers expand all on the observation search pages', function (string $url) {
    $response = $this->get($url);

    $response->assertOk();
    $response->assertSee('Expand all');
    $response->assertSee("Livewire.dispatch('expand-all-details'", false);
    $response->assertDontSee('toggleCompare', false);
})->with([
    'search' => fn () => route('observations.search'),
    'advanced results' => fn () => route('search.observations.advanced.results', ['languages' => ['en']]),
]);

it('lets every detail row follow expand all', function () {
    $user = User::factory()->create(['username' => 'expand-observer', 'slug' => 'expand-observer']);

    $insert = fn (string $name) => Observation::query()->insertGetId([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-03',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $name,
        'target_name' => $name,
        'language' => 'en',
    ]);
    $first = $insert('NGC 2392');
    $second = $insert('NGC 7662');

    Livewire::withoutLazyLoading();
    $table = Livewire::test(ObservationSearchTable::class);

    // Expand all opens every row; a row closed by hand stays closed
    $table->dispatch('expand-all-details', table: 'observation-search-table', expanded: true)
        ->call('toggleDetail', (string) $first);

    expect($table->instance()->isDetailOpen($first))->toBeFalse()
        ->and($table->instance()->isDetailOpen($second))->toBeTrue();

    // Collapse all closes them all, and another table's button does nothing
    $table->dispatch('expand-all-details', table: 'observation-search-table', expanded: false)
        ->dispatch('expand-all-details', table: 'another-table', expanded: true);

    expect($table->instance()->isDetailOpen($first))->toBeFalse()
        ->and($table->instance()->isDetailOpen($second))->toBeFalse();
});
