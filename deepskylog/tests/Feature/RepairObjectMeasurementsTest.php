<?php

use App\Models\User;
use App\Models\UserObjectMetric;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function measuredObject(string $name, string $type, float $mag, float $diam1, float $diam2): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'AND',
        'ra'            => 0.8, 'decl' => 30.0, 'mag' => $mag, 'subr' => 99.9, 'pa' => 999, 'urano' => 0,
        'urano_new'     => 0, 'sky' => 0, 'millenium' => '', 'diam1' => $diam1, 'diam2' => $diam2,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP'         => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function measurements(string $name): array
{
    $row = DB::table('objects')->where('name', $name)->first();

    return [(float) $row->mag, (float) $row->diam1, (float) $row->diam2];
}

beforeEach(function () {
    measuredObject('PGC 2892', 'GALXY', 16.2, 0.3, 0.2);
    measuredObject('IC 1108', 'GALXY', 8.0, 2.0, 2.0);
    measuredObject('G 119', 'GXAGC', 15.0, 2.0, 2.0);
    measuredObject('NGC 7331', 'GALXY', 9.5, 630.0, 228.0);
    measuredObject('M 24', 'OPNCL', 11.1, 5400.0, 2400.0);
    measuredObject('M 43', 'EMINB', 9.0, 1200.0, 1200.0);
});

it('changes nothing without --apply', function () {
    $this->artisan('objects:repair-measurements')
        ->expectsOutputToContain('3 objects to repair.')
        ->assertSuccessful();

    expect(measurements('PGC 2892'))->toBe([16.2, 0.3, 0.2]);
});

it('repairs galaxy sizes stored in arcminutes and the corrected objects', function () {
    $user = User::factory()->create();
    DB::table('user_object_metrics')->insert(['user_id' => $user->id, 'instrument_id' => 1, 'location_id' => 1, 'object_name' => 'PGC 2892', 'contrast_reserve' => 0.3]);

    $this->artisan('objects:repair-measurements', ['--apply' => true])->assertSuccessful();

    expect(measurements('PGC 2892'))->toBe([16.2, 18.0, 12.0])
        ->and(measurements('M 24'))->toBe([4.6, 5400.0, 2400.0])
        ->and(measurements('M 43'))->toBe([9.0, 360.0, 180.0])
        // too bright with the size in arcminutes as well: probably the magnitude is wrong
        ->and(measurements('IC 1108'))->toBe([8.0, 2.0, 2.0])
        // a globular cluster in M 31 is that small
        ->and(measurements('G 119'))->toBe([15.0, 2.0, 2.0])
        ->and(measurements('NGC 7331'))->toBe([9.5, 630.0, 228.0])
        ->and(UserObjectMetric::where('object_name', 'PGC 2892')->exists())->toBeFalse();

    $this->artisan('objects:repair-measurements')->expectsOutputToContain('0 objects to repair.')->assertSuccessful();
});
