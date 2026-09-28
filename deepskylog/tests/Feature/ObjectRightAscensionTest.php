<?php

use App\Http\Controllers\ObjectController;
use App\Models\DeepskyObject;
use App\Services\EphemeridesBuilder;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use Illuminate\Support\Facades\DB;

// objects.ra is in decimal hours; these guard the places that mixed it up with degrees.

function insertRaTestObject(string $name, string $slug, float $ra, float $decl): void
{
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => $slug,
        'type' => 'GALXY',
        'con' => '',
        'ra' => $ra,
        'decl' => $decl,
        'mag' => 9.3,
        'subr' => 12.4,
        'pa' => 174,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 600.0,
        'diam2' => 180.0,
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

function updateRaTestObject($test, string $ra, string $decl = '13.0922')
{
    $admin = $test->createUserAndAssignToTeam('Administrators');

    return $test->actingAs($admin)->put(route('object.update', ['slug' => 'm-65']), [
        'name' => 'M 65',
        'ra' => $ra,
        'decl' => $decl,
        'type' => 'GALXY',
        'mag' => '9.3',
        'subr' => '12.4',
        'diam1' => '10',
        'diam2' => '3',
    ]);
}

beforeEach(function () {
    insertRaTestObject('M 65', 'm-65', 11.3155, 13.0922);
});

it('stores the right ascension of an edited object in hours', function (string $input) {
    updateRaTestObject($this, $input)->assertSessionHasNoErrors();

    $object = DB::table('objects')->where('name', 'M 65')->first();
    expect((float) $object->ra)->toEqualWithDelta(11.3155, 0.0001)
        ->and($object->con)->toBe('LEO');
})->with([
    'decimal hours' => '11.3155',
    'sexagesimal hours' => '11 18 55.8',
    'decimal degrees' => '169.7325',
]);

it('rejects a right ascension that is not a number', function () {
    updateRaTestObject($this, 'abc')->assertSessionHasErrors('ra');

    expect((float) DB::table('objects')->where('name', 'M 65')->value('ra'))->toBe(11.3155);
});

it('computes the ephemerides of an object in the first hours of right ascension from its hours', function () {
    insertRaTestObject('NGC 224', 'ngc-224', 0.7123, 41.2692);
    $object = DeepskyObject::where('name', 'NGC 224')->first();
    $location = (object) ['elevation' => 0.0];

    $result = EphemeridesBuilder::compute($object, Carbon::parse('2026-09-28 22:00:00'), $location, new GeographicalCoordinates(4.7, 50.9));

    expect($result['raDeg'])->toEqualWithDelta(10.6845, 0.0001)
        ->and($result['decDeg'])->toEqualWithDelta(41.2692, 0.0001);
});

it('reads the RA_d column of SIMBAD as degrees and the RA column as sexagesimal hours', function () {
    $method = new ReflectionMethod(ObjectController::class, 'simbadRaHours');

    expect($method->invoke(null, 'RA_d', '10.6847083'))->toEqualWithDelta(0.712314, 0.000001)
        ->and($method->invoke(null, 'RA_d', '169.7329519'))->toEqualWithDelta(11.315530, 0.000001)
        ->and($method->invoke(null, 'RA', '00 42 44.3'))->toEqualWithDelta(0.712306, 0.000001)
        ->and($method->invoke(null, 'RA', '0.7'))->toBeNull()
        ->and($method->invoke(null, 'RA_d', ''))->toBeNull();
});

it('repairs objects whose right ascension was stored in degrees', function () {
    DB::table('objects')->where('name', 'M 65')->update(['ra' => 169.731]);
    DB::table('search_index')->insert([
        'name' => 'M 65',
        'source_table' => 'objects',
        'source_pk' => 'm-65',
        'ra' => 169.731,
        'decl' => 13.0903,
    ]);

    (require database_path('migrations/2026_09_28_000003_repair_object_ra_stored_in_degrees.php'))->up();

    expect((float) DB::table('objects')->where('name', 'M 65')->value('ra'))->toEqualWithDelta(11.3154, 0.0001)
        ->and((float) DB::table('search_index')->where('source_pk', 'm-65')->value('ra'))->toEqualWithDelta(11.3154, 0.0001);
});

it('repairs the listed objects below 1.6 h whose right ascension was stored in degrees, unless already corrected', function (float $stored, float $expected) {
    insertRaTestObject('Hickson 12', 'hickson-12', $stored, -4.67056);
    DB::table('search_index')->insert([
        'name' => 'Hickson 12',
        'source_table' => 'objects',
        'source_pk' => 'hickson-12',
        'ra' => $stored,
        'decl' => -4.67056,
    ]);

    (require database_path('migrations/2026_09_28_000003_repair_object_ra_stored_in_degrees.php'))->up();

    expect((float) DB::table('objects')->where('name', 'Hickson 12')->value('ra'))->toEqualWithDelta($expected, 0.0001)
        ->and((float) DB::table('search_index')->where('source_pk', 'hickson-12')->value('ra'))->toEqualWithDelta($expected, 0.0001);
})->with([
    'stored in degrees' => [21.8916, 1.45944],
    'already in hours' => [1.45936, 1.45936],
]);
