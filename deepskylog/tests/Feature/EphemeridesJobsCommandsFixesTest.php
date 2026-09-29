<?php

use App\Http\Controllers\NearbyExportController;
use App\Models\ObservationSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

function fixesTestMetric(User $user, string $object): void
{
    DB::table('user_object_metrics')->insert([
        'user_id' => $user->id,
        'instrument_id' => 1,
        'location_id' => 1,
        'object_name' => $object,
        'contrast_reserve' => 0.5,
        'optimum_detection_magnification' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('stops the best magnification backfill at the limit', function () {
    $user = User::factory()->create();
    foreach (['M 31', 'M 32', 'M 33', 'M 34', 'M 35'] as $object) {
        fixesTestMetric($user, $object);
    }

    $this->artisan('metrics:backfill-best-mag', ['--limit' => 2, '--chunk' => 1, '--dry-run' => true])
        ->expectsOutputToContain('Processed 2 rows')
        ->assertSuccessful();
});

it('gives sessions whose name has no letters or digits a slug from the id', function () {
    $user = User::factory()->create();
    $attributes = [
        'observerid' => $user->username,
        'locationid' => 1,
        'active' => 1,
        'begindate' => '2026-03-14 20:00:00',
        'enddate' => '2026-03-15 04:00:00',
    ];
    $symbols = ObservationSession::create($attributes + ['id' => 4801, 'name' => '???']);
    $entities = ObservationSession::create($attributes + ['id' => 4802, 'name' => 'M31 &amp; M32']);
    DB::table('observation_sessions')->whereIn('id', [4801, 4802])->update(['slug' => '']);

    $this->artisan('sessions:backfill-slugs')->assertSuccessful();

    expect($symbols->fresh()->slug)->toBe('session-4801')
        ->and($entities->fresh()->slug)->toBe('m31-m32');

    // A second run has nothing left to do
    $this->artisan('sessions:backfill-slugs')
        ->expectsOutputToContain('Found 0 session(s) without slugs.')
        ->assertSuccessful();
});

it('requires a login to generate an atlas page', function () {
    $this->post(route('downloads.dsl-atlas.generate'), ['type' => 'detail'])
        ->assertRedirect(route('login'));
});

it('uses the object coordinates for a nearby export with empty coordinates', function () {
    DB::table('objects')->insert([
        'name' => 'M 31', 'slug' => 'm-31', 'type' => 'GALXY', 'con' => 'AND',
        'ra' => 0.712313, 'decl' => 41.26917, 'mag' => 3.4, 'subr' => 0.0, 'pa' => 35,
        'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 190.0, 'diam2' => 60.0,
        'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL' => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);

    $resolve = new ReflectionMethod(NearbyExportController::class, 'resolveCenterAndTarget');
    [$ra, $dec] = $resolve->invoke(new NearbyExportController(), Request::create('/x', 'GET', ['ra' => '', 'dec' => '']), 'm-31');

    expect($ra)->toEqualWithDelta(0.712313 * 15.0, 1e-6)
        ->and($dec)->toEqualWithDelta(41.26917, 1e-6);

    [$ra] = $resolve->invoke(new NearbyExportController(), Request::create('/x', 'GET', ['ra' => '', 'dec' => '']), null);
    expect($ra)->toBeNull();
});
