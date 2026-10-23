<?php

use App\Models\DeepskyObject;
use App\Services\StarChartService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function insertCapTestObject(string $name, float $ra, float $decl, ?float $mag): void
{
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => Str::slug($name),
        'type' => 'GX',
        'con' => 'VIR',
        // RA in hours, as the legacy table stores it.
        'ra' => $ra,
        'decl' => $decl,
        'mag' => $mag,
        'subr' => 0.0,
        'pa' => 0,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 1.0,
        'diam2' => 1.0,
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
        'timestamp' => now(),
    ]);
}

// Issue #685: in a crowded field the 200-object cap of a non-atlas chart
// dropped bright galaxies, because the query had no order.
it('keeps the brightest objects and the target when a crowded chart is capped', function () {
    for ($i = 0; $i < 250; $i++) {
        insertCapTestObject(sprintf('Faint %03d', $i), 12.505 + ($i % 25) * 0.0004, 12.1 + intdiv($i, 25) * 0.02, $i % 2 === 0 ? 15.0 : 99.9);
    }
    // Inserted last and north of the others, so neither comes first in an
    // unordered scan by row or by the declination index.
    insertCapTestObject('Bright galaxy', 12.51, 12.29, 8.0);
    insertCapTestObject('Faint target', 12.512, 12.295, 16.5);

    /** @var DeepskyObject $target */
    $target = DeepskyObject::query()->where('name', 'Faint target')->firstOrFail();
    $chart = app(StarChartService::class)->build($target, 12.51 * 15.0, 12.2, 60, 20.0);

    $names = $chart['objects']->pluck('name')->filter()->values();
    expect($names)->toContain('Bright galaxy')
        ->and($names)->toContain('Faint target');
});
