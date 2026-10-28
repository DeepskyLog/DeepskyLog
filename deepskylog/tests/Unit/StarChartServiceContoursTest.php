<?php

use App\Models\DeepskyObject;
use App\Services\StarChartService;
use Illuminate\Support\Facades\DB;

it('renders migrated object contours on the star chart svg', function () {
    DB::table('objects')->insert([
        'name' => 'M 31',
        'slug' => 'm-31',
        'type' => 'GX',
        'con' => 'AND',
        'ra' => 0.712313,
        'decl' => 41.26917,
        'mag' => 3.4,
        'subr' => 0.0,
        'pa' => 35,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 190.0,
        'diam2' => 60.0,
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

    DB::table('object_contours')->insert([
        [
            'object_name' => 'M 31',
            'contour_index' => 0,
            'point_index' => 0,
            'ra_degrees' => 10.55,
            'decl_degrees' => 41.20,
        ],
        [
            'object_name' => 'M 31',
            'contour_index' => 0,
            'point_index' => 1,
            'ra_degrees' => 10.75,
            'decl_degrees' => 41.25,
        ],
        [
            'object_name' => 'M 31',
            'contour_index' => 0,
            'point_index' => 2,
            'ra_degrees' => 10.88,
            'decl_degrees' => 41.35,
        ],
    ]);

    /** @var DeepskyObject $target */
    $target = DeepskyObject::query()->where('name', 'M 31')->firstOrFail();
    $chart = app(StarChartService::class)->build($target, 10.684708, 41.268750, 30, 10.0);

    expect($chart['svg'])->toContain('data-contour="M 31"');
});
