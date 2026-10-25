<?php

use App\Models\Observation;
use App\Models\User;

it('offers expand all on the observation search pages', function (string $url) {
    $response = $this->get($url);

    $response->assertOk();
    $response->assertSee('Expand all');
    $response->assertSee("Alpine.store('pgDetails')", false);
    $response->assertDontSee('toggleCompare', false);
})->with([
    'search' => fn () => route('observations.search'),
    'advanced results' => fn () => route('search.observations.advanced.results', ['languages' => ['en']]),
]);

it('lets every detail row follow expand all', function () {
    $user = User::factory()->create(['username' => 'expand-observer', 'slug' => 'expand-observer']);

    Observation::query()->insert([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-03',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'language' => 'en',
    ]);

    $response = $this->get(route('observations.search', ['q' => 'NGC 2392']));

    $response->assertOk();
    $response->assertSee('x-on:pg-details-expanded-observation-search-table.window', false);
    $response->assertSee('$store.pgDetails', false);
});
