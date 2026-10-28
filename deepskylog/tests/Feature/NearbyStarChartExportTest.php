<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

it('exports a nearby star chart pdf for an object', function () {
    $objectPayload = [
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
    ];

    DB::table('objects')->insert($objectPayload);

    DB::table('stars')->insert([
        [
            'source_key' => sha1('star-1'),
            'source_table' => 'stars8',
            'type' => 'AA1STAR',
            'constellation_code' => 'AND',
            'bayer' => null,
            'bayer_suffix' => null,
            'flamsteed' => null,
            'name' => 'SOA123',
            'ra_mas' => null,
            'dec_mas' => null,
            'ra_hours' => 0.711000,
            'ra_degrees' => 10.665000,
            'decl_degrees' => 41.280000,
            'vmag_int' => 650,
            'vmag' => 6.50,
            'spectral_type' => 'A0',
        ],
        [
            'source_key' => sha1('star-2'),
            'source_table' => 'stars9',
            'type' => null,
            'constellation_code' => null,
            'bayer' => null,
            'bayer_suffix' => null,
            'flamsteed' => null,
            'name' => null,
            'ra_mas' => null,
            'dec_mas' => null,
            'ra_hours' => 0.715000,
            'ra_degrees' => 10.725000,
            'decl_degrees' => 41.220000,
            'vmag_int' => 920,
            'vmag' => 9.20,
            'spectral_type' => null,
        ],
    ]);

    $response = $this->actingAs(User::factory()->create())->get(route('object.nearby.chart.pdf', [
        'slug' => 'm-31',
        'ra' => 10.684708,
        'dec' => 41.268750,
        'radius' => 30,
    ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    expect($response->streamedContent())->toStartWith('%PDF');
});