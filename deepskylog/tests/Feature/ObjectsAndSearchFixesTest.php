<?php

use App\Models\Observation;
use App\Models\User;
use App\Services\AdminObjectCheckService;
use Illuminate\Support\Facades\DB;

// Objects and search (functionality review 2026-09-29).

function fixesObjectRow(string $name, float $ra, float $decl, string $con = 'AND'): array
{
    return [
        'name' => $name,
        'slug' => \Illuminate\Support\Str::slug($name),
        'type' => 'GALXY',
        'con' => $con,
        'ra' => $ra,
        'decl' => $decl,
        'mag' => 9.0,
        'subr' => 13.0,
        'pa' => 999,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 0.0,
        'diam2' => 0.0,
        'datasource' => null,
        'taki' => '',
        'SBObj' => 0.0,
        'description' => '',
        'psa' => '',
        'torresB' => '',
        'torresBC' => '',
        'torresC' => '',
        'milleniumbase' => '0',
        'DSLDL' => '0',
        'DSLDP' => '0',
        'DSLLL' => '0',
        'DSLLP' => '0',
        'DSLOL' => '0',
        'DSLOP' => '0',
        'DeepskyHunter' => '0',
        'Interstellarum' => '0',
        'timestamp' => '2026-01-01 00:00:00',
    ];
}

it('checks the constellations of all objects on the queue', function () {
    Illuminate\Support\Facades\Storage::fake('local');

    $rows = [];
    for ($i = 0; $i < 500; $i++) {
        $rows[] = fixesObjectRow(sprintf('A %03d', $i), 0.712, 41.269);
    }
    foreach (array_chunk($rows, 100) as $chunk) {
        DB::table('objects')->insert($chunk);
    }
    // Sorts after the 500 objects of the sample
    DB::table('objects')->insert(fixesObjectRow('Z 1', 0.712, 41.269, 'ORI'));

    $service = app(AdminObjectCheckService::class);

    expect($service->constellationCheckStatus())->toBeNull()
        ->and($service->exportConstellationMismatches())->toBeNull();

    // The sync queue of the tests runs the job right away
    expect($service->queueConstellationCheck(false))->toBeTrue()
        ->and($service->constellationCheckStatus())
        ->toMatchArray(['state' => 'complete', 'repair' => false, 'total' => 501, 'checked' => 501, 'skipped' => 0, 'mismatch_count' => 1, 'repaired' => 0])
        ->and($service->constellationCheckStatus()['examples'])->toHaveCount(1)
        ->and($service->exportConstellationMismatches())->toContain('"Z 1","ORI"')
        ->and(DB::table('objects')->where('name', 'Z 1')->value('con'))->toBe('ORI');

    expect($service->queueConstellationCheck(true))->toBeTrue()
        ->and($service->constellationCheckStatus())->toMatchArray(['state' => 'complete', 'repair' => true, 'mismatch_count' => 1, 'repaired' => 1])
        ->and(DB::table('objects')->where('name', 'Z 1')->value('con'))->toBe('AND');
});

it('does not queue a second constellation check while one is in progress', function () {
    Illuminate\Support\Facades\Queue::fake();
    $service = app(AdminObjectCheckService::class);

    expect($service->queueConstellationCheck(false))->toBeTrue()
        ->and($service->constellationCheckInProgress())->toBeTrue()
        ->and($service->queueConstellationCheck(true))->toBeFalse();

    Illuminate\Support\Facades\Queue::assertPushed(App\Jobs\CheckObjectConstellations::class, 1);

    // A check that stopped reporting is taken to have died with its worker
    $this->travel(2)->hours();
    expect($service->constellationCheckInProgress())->toBeFalse()
        ->and($service->queueConstellationCheck(false))->toBeTrue();

    (new App\Jobs\CheckObjectConstellations)->failed(new RuntimeException('killed'));
    expect($service->constellationCheckStatus()['state'])->toBe('failed');
});

it('finds nearby objects across 0 h and with the RA range widened by 1/cos(dec)', function () {
    DB::table('objects')->insert([
        fixesObjectRow('Across Zero', 23.99, 10.0),
        // 0.1 h = 1.5° of RA, 0.26° on the sky at dec 80°
        fixesObjectRow('Near Pole', 5.1, 80.0),
        fixesObjectRow('Far Away', 12.0, 10.0),
    ]);

    $user = $this->createUserAndAssignToTeam('Administrators');

    $this->actingAs($user)
        ->get(route('object.coordsSearch', ['name' => 'X', 'ra' => '0.01', 'decl' => '10']))
        ->assertOk()
        ->assertSee('Across Zero')
        ->assertDontSee('Far Away');

    $this->actingAs($user)
        ->get(route('object.coordsSearch', ['name' => 'X', 'ra' => '5.0', 'decl' => '80']))
        ->assertOk()
        ->assertSee('Near Pole');
});

it('does not delete an object with observations without an object to move them to', function () {
    DB::table('objects')->insert(fixesObjectRow('M 31', 0.712, 41.269));
    $observer = User::factory()->create();
    Observation::create([
        'observer_username' => $observer->username,
        'observer_user_id' => $observer->id,
        'observed_on' => '2026-03-01',
        'description' => 'Seen',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'M 31',
        'target_name' => 'Andromeda Galaxy',
    ]);

    $admin = $this->createUserAndAssignToTeam('Administrators');

    $this->actingAs($admin)->delete(route('object.destroy', ['slug' => 'm-31']))->assertSessionHas('error');

    expect(DB::table('objects')->where('name', 'M 31')->exists())->toBeTrue();
});

it('counts the observations of the object to delete by their target key', function () {
    $observer = User::factory()->create();
    Observation::create([
        'observer_username' => $observer->username,
        'observer_user_id' => $observer->id,
        'observed_on' => '2026-03-01',
        'description' => 'Seen',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'M 31',
        'target_name' => 'Andromeda Galaxy',
    ]);

    \Livewire\Livewire::test(\App\Livewire\DeleteObjectModal::class, ['objectSlug' => 'm-31', 'objectName' => 'M 31'])
        ->call('openModal')
        ->assertSet('observationsCount', 1);
});

it('shows the comet observations of an observer at /cometobservations/{observer}', function () {
    $observer = User::factory()->create();

    $this->get(route('observations.comet.object', ['object' => $observer->slug]))
        ->assertOk()
        ->assertSee($observer->name);
});

it('exports search results for Argo Navis, SkySafari and AstroPlanner without leaving a temporary file', function () {
    DB::table('search_index')->insert([
        'name' => 'NGC 2392',
        'name_normalized' => 'ngc 2392',
        'source_table' => 'objects',
        'source_pk' => 'ngc-2392',
        'display_name' => 'NGC 2392',
        'source_type' => 'object',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect($this->get(route('search.argo', ['name_text' => '2392']))->assertOk()->streamedContent())
        ->toStartWith('DSL NGC 2392|')
        ->and($this->get(route('search.skylist', ['name_text' => '2392']))->assertOk()->streamedContent())
        ->toContain('CatalogNumber=NGC 2392');

    $before = glob(sys_get_temp_dir() . '/dsl_apd_*') ?: [];
    $apd = $this->get(route('search.apd', ['name_text' => '2392']))->assertOk()->streamedContent();

    expect($apd)->toStartWith('SQLite format 3')
        ->and(array_diff(glob(sys_get_temp_dir() . '/dsl_apd_*') ?: [], $before))->toBe([]);
});
