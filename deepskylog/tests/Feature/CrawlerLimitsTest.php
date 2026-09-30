<?php

use App\Livewire\NearbyObjectsTable;
use App\Livewire\ObservationSearchTable;
use App\Models\Observation;
use App\Models\User;
use App\Models\UserTableSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

// Rate limits and export limits against crawlers and bots.

function registerThrottleProbeRoute(): void
{
    Route::middleware('web')->get('/__throttle-probe', fn () => 'ok');
}

it('limits guests to 60 page requests a minute per IP address', function () {
    registerThrottleProbeRoute();

    for ($i = 0; $i < 60; $i++) {
        $this->get('/__throttle-probe')->assertOk();
    }

    $this->get('/__throttle-probe')->assertStatus(429);
});

it('counts Livewire requests apart from page requests', function () {
    registerThrottleProbeRoute();

    for ($i = 0; $i < 60; $i++) {
        $this->get('/__throttle-probe')->assertOk();
    }

    $this->get('/__throttle-probe', ['X-Livewire' => 'true'])->assertOk();
});

it('gives logged-in users a higher limit', function () {
    registerThrottleProbeRoute();
    $this->actingAs(User::factory()->create());

    for ($i = 0; $i < 61; $i++) {
        $this->get('/__throttle-probe')->assertOk();
    }
});

it('limits guests to three exports a minute', function () {
    for ($i = 0; $i < 3; $i++) {
        $this->get(route('search.stxt', ['name_text' => '2392']))->assertOk();
    }

    $this->get(route('search.stxt', ['name_text' => '2392']))->assertStatus(429);
});

it('sends guests to the login page for the PDF exports', function () {
    $this->get(route('object.nearby.chart.pdf', ['slug' => 'm-31']))->assertRedirect(route('login'));
    $this->get(route('object.nearby.names.pdf', ['slug' => 'm-31']))->assertRedirect(route('login'));
    $this->get(route('search.names.pdf', ['q' => 'M 31']))->assertRedirect(route('login'));
    $this->get(route('observations.search.pdf', ['q' => 'M 31']))->assertRedirect(route('login'));
});

it('refuses a search PDF of more than 1000 objects', function () {
    $rows = [];
    for ($i = 1; $i <= 1001; $i++) {
        $rows[] = [
            'name' => "NGC {$i}",
            'name_normalized' => "ngc {$i}",
            'source_table' => 'objects',
            'source_pk' => "ngc-{$i}",
            'display_name' => "NGC {$i}",
            'source_type' => 'object',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    foreach (array_chunk($rows, 200) as $chunk) {
        DB::table('search_index')->insert($chunk);
    }

    $response = $this->actingAs(User::factory()->create())
        ->from(route('search.advanced'))
        ->get(route('search.names.pdf', ['name_text' => 'NGC']));

    $response->assertRedirect(route('search.advanced'));
    $response->assertSessionHas('error', 'This search matches more than 1,000 objects, the most that can be exported in this format. Please narrow down your search.');
});

it('keeps the nearby radius within the radius selector', function () {
    expect(NearbyObjectsTable::clampRadius(99999))->toBe(NearbyObjectsTable::MAX_RADIUS_ARCMIN)
        ->and(NearbyObjectsTable::clampRadius(-5))->toBe(1)
        ->and(NearbyObjectsTable::clampRadius('30'))->toBe(30);
});

it('stores a clamped nearby radius in the user settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    // Not rendered: the nearby query uses MariaDB functions SQLite lacks.
    $table = new NearbyObjectsTable();
    $table->updateRadius(3600);

    expect($table->radiusArcMin)->toBe(NearbyObjectsTable::MAX_RADIUS_ARCMIN);

    $settings = UserTableSetting::where('user_id', $user->id)
        ->where('table_name', 'nearby-objects-table')
        ->value('settings');

    expect($settings['radiusArcMin'])->toBe(NearbyObjectsTable::MAX_RADIUS_ARCMIN);
});

it('refuses a PowerGrid export of more than 5000 rows', function () {
    $user = User::factory()->create();
    $row = [
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-01',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 7000',
        'target_name' => 'NGC 7000',
        'has_drawing' => false,
        'legacy_source' => null,
    ];
    foreach (array_chunk(array_fill(0, 5001, $row), 500) as $chunk) {
        Observation::query()->insert($chunk);
    }

    Livewire::test(ObservationSearchTable::class, ['objectName' => 'NGC 7000'])
        ->call('exportToCsv')
        ->assertNoFileDownloaded()
        ->assertDispatched('wireui:notification');
});
