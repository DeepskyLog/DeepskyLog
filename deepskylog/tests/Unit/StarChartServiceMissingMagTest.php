<?php

use App\Models\DeepskyObject;
use App\Services\StarChartService;
use Illuminate\Support\Facades\DB;

it('includes objects with mag 99.9 on the star chart', function () {
    DB::table('objects')->insert([
        'name' => 'MissingMag 99.9',
        'slug' => 'missing-mag-99-9',
        'type' => 'GX',
        'con' => 'AND',
        // Store RA in hours (values <= 24) to match legacy table semantics.
        'ra' => 0.712313,
        'decl' => 41.268750,
        'mag' => 99.9,
        'subr' => 0.0,
        'pa' => 0,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 5.0,
        'diam2' => 5.0,
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

    /** @var DeepskyObject $target */
    $target = DeepskyObject::query()->where('name', 'MissingMag 99.9')->firstOrFail();
    $chart = app(StarChartService::class)->build($target, 10.684708, 41.268750, 30, 10.0);

    $names = $chart['objects']->pluck('name')->filter()->values();
    expect($names->contains('MissingMag 99.9'))->toBeTrue();
});
