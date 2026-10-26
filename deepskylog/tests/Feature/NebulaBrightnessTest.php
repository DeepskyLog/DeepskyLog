<?php

use App\Models\DeepskyObject;
use App\Services\NebulaBrightness;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A nebula with its names; the diameters in arcseconds, mag 99.9 when unknown.
 */
function nebulaBrightnessObject(string $name, string $type, float $ra, float $decl, float $diam1, float $diam2, array $names = [], float $mag = 99.9): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'ORI',
        'ra'            => $ra, 'decl' => $decl, 'mag' => $mag, 'subr' => 99.9, 'pa' => 999,
        'urano'         => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => $diam1, 'diam2' => $diam2,
        'datasource'    => null, 'taki' => '', 'SBObj' => -999, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL'         => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
    foreach ($names as $altname) {
        [$catalog, $index] = explode(' ', $altname, 2);
        DB::table('objectnames')->insert([
            'objectname' => $name, 'catalog' => $catalog, 'catindex' => $index, 'altname' => $altname, 'slug' => Str::slug($altname), 'timestamp' => now(),
        ]);
    }
}

it('turns a surface brightness and a size into a magnitude', function () {
    // 60' x 10' at 22.3 mag/arcsec²
    expect(NebulaBrightness::magnitude(22.3, 3600, 600))->toBe(6.7)
        ->and(NebulaBrightness::magnitude(22.3, 1200, 0))->toBe(NebulaBrightness::magnitude(22.3, 1200, 1200))
        ->and(NebulaBrightness::magnitude(22.3, 0, 0))->toBeNull();
});

it('estimates IC 434 from its Lynds class, Lynds before Sharpless', function () {
    nebulaBrightnessObject('IC 434', 'HII', 5.6833, -2.4533, 3600, 600, ['IC 434', 'LBN 953', 'Sh 2-277']);

    $estimate = NebulaBrightness::plan()['estimates']['IC 434'];

    expect($estimate['source'])->toBe('LBN 953, class 1')
        ->and($estimate['mag'])->toBe(6.7)
        ->and($estimate['size_from_catalogue'])->toBeFalse()
        ->and($estimate['changed'])->toBeTrue();
});

it('takes the brightest class of several names and the Sharpless class without a Lynds name', function () {
    // LBN 953 is class 1, LBN 115 class 6 but far away; Sh 2-276 is Barnard's Loop, class 2
    nebulaBrightnessObject('Test nebula', 'EMINB', 5.6920, -2.309, 600, 600, ['LBN 115', 'LBN 953']);
    nebulaBrightnessObject('Sh 2-276', 'BRTNB', 5.4578, -3.965, 36000, 36000, ['Sh 2-276']);

    $plan = NebulaBrightness::plan();

    expect($plan['estimates']['Test nebula']['source'])->toBe('LBN 953, class 1')
        ->and($plan['estimates']['Sh 2-276']['source'])->toBe('Sh 2-276, class 2')
        ->and($plan['estimates']['Sh 2-276']['sb'])->toBe(24.3)
        ->and($plan['rejected'])->toHaveCount(1)
        ->and($plan['rejected'][0]['source'])->toBe('LBN 115');
});

it('leaves measured magnitudes, dark nebulae and objects without a catalogue name alone', function () {
    nebulaBrightnessObject('M 42', 'HII', 5.588, -5.39, 5400, 3600, ['LBN 974'], 4.0);
    nebulaBrightnessObject('B 33', 'DRKNB', 5.683, -2.458, 360, 240, ['LBN 953']);
    nebulaBrightnessObject('RCW 1', 'HII', 5.683, -2.458, 360, 240, ['RCW 1']);

    expect(NebulaBrightness::plan()['estimates'])->toBe([]);
});

it('takes the size of the catalogue when the object has none', function () {
    nebulaBrightnessObject('IC 434', 'HII', 5.6833, -2.4533, 0, 0, ['LBN 953']);

    $estimate = NebulaBrightness::plan()['estimates']['IC 434'];

    // LBN 953 is 90' x 30'
    expect($estimate['size_from_catalogue'])->toBeTrue()
        ->and($estimate['diam1'])->toBe(5400.0)
        ->and($estimate['diam2'])->toBe(1800.0)
        ->and($estimate['mag'])->toBe(NebulaBrightness::magnitude(22.3, 5400, 1800));
});

it('writes the estimates with --apply, forgets their metrics and is safe to run again', function () {
    nebulaBrightnessObject('IC 434', 'HII', 5.6833, -2.4533, 3600, 600, ['LBN 953']);
    DB::table('user_object_metrics')->insert(['user_id' => 1, 'object_name' => 'IC 434', 'created_at' => now(), 'updated_at' => now()]);

    $this->artisan('objects:estimate-nebula-magnitudes')->assertSuccessful();
    expect((float) DB::table('objects')->where('name', 'IC 434')->value('mag'))->toBe(99.9);

    $this->artisan('objects:estimate-nebula-magnitudes', ['--apply' => true])->assertSuccessful();
    $object = DB::table('objects')->where('name', 'IC 434')->first();
    expect((float) $object->mag)->toBe(6.7)
        ->and($object->mag_source)->toBe('LBN 953, class 1')
        ->and((float) $object->SBObj)->toEqualWithDelta(22.3, 0.05)
        ->and(DB::table('user_object_metrics')->where('object_name', 'IC 434')->exists())->toBeFalse();

    expect(NebulaBrightness::plan()['estimates']['IC 434']['changed'])->toBeFalse();
});

it('drops the source when the magnitude is edited', function () {
    nebulaBrightnessObject('IC 434', 'HII', 5.6833, -2.4533, 3600, 600, ['LBN 953']);
    $this->artisan('objects:estimate-nebula-magnitudes', ['--apply' => true])->assertSuccessful();

    $object = DeepskyObject::where('name', 'IC 434')->firstOrFail();
    expect($object->hasEstimatedMagnitude())->toBeTrue();

    $object->description = 'Contains the Horsehead';
    $object->save();
    expect($object->fresh()->hasEstimatedMagnitude())->toBeTrue();

    $object->mag = 7.3;
    $object->save();
    expect($object->fresh()->mag_source)->toBeNull();
});

it('moves the nebulae at a wrong position and replaces the wrong names, once', function () {
    // Sh 2-36 stored south of the equator, Sh 2-115 with the LBN number of another nebula
    nebulaBrightnessObject('Sh 2-36', 'HII', 16.0933, -0.3833, 2700, 2700, ['Sh 2-36', 'LBN 45']);
    nebulaBrightnessObject('Sh 2-115', 'BRTNB', 20.575, 46.8667, 1800, 1800, ['Sh 2-115', 'LBN 115']);
    DB::table('user_object_metrics')->insert(['user_id' => 1, 'object_name' => 'Sh 2-36', 'created_at' => now(), 'updated_at' => now()]);

    $this->artisan('objects:repair-nebula-positions')->assertSuccessful();
    expect((float) DB::table('objects')->where('name', 'Sh 2-36')->value('decl'))->toBe(-0.3833);

    $this->artisan('objects:repair-nebula-positions', ['--apply' => true])->assertSuccessful();

    $object = DB::table('objects')->where('name', 'Sh 2-36')->first();
    expect((float) $object->decl)->toBe(0.3833)
        ->and($object->con)->toBe('SER')
        ->and(DB::table('user_object_metrics')->where('object_name', 'Sh 2-36')->exists())->toBeFalse()
        ->and(DB::table('objectnames')->where('objectname', 'Sh 2-115')->pluck('altname')->sort()->values()->all())->toBe(['LBN 357', 'Sh 2-115'])
        ->and(DB::table('search_index')->where('source_pk', 'sh-2-115')->where('name', 'LBN 357')->exists())->toBeTrue()
        ->and(DB::table('search_index')->where('name', 'LBN 115')->exists())->toBeFalse();

    $this->artisan('objects:repair-nebula-positions')->expectsOutput('Nothing to repair.')->assertSuccessful();

    // The repaired names are no longer refused; LBN 357 is class 2
    $plan = NebulaBrightness::plan();
    expect($plan['rejected'])->toBe([])
        ->and($plan['estimates']['Sh 2-115']['source'])->toBe('LBN 357, class 2');
});
