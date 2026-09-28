<?php

use Illuminate\Support\Facades\DB;

// Missing magnitudes, diameters and position angles are stored with the sentinels of the
// legacy data (mag and subr 99.9, pa 999, SBObj -999), by the create wizard and the edit form alike.

function insertMeasurementTestObject(string $name, string $slug): void
{
    DB::table('deepskytypes')->insertOrIgnore(['code' => 'GALXY', 'name' => 'Galaxy']);
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => $slug,
        'type' => 'GALXY',
        'con' => 'LEO',
        'ra' => 11.3155,
        'decl' => 13.0922,
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
        'SBObj' => 12.9,
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
    DB::table('objectnames')->insertOrIgnore([
        'objectname' => $name,
        'catalog' => explode(' ', $name)[0],
        'catindex' => explode(' ', $name)[1],
        'altname' => $name,
        'slug' => $slug,
        'timestamp' => now(),
    ]);
}

function updateMeasurementTestObject($test, array $fields)
{
    $admin = $test->createUserAndAssignToTeam('Administrators');

    return $test->actingAs($admin)->put(route('object.update', ['slug' => 'm-65']), array_merge([
        'name' => 'M 65',
        'ra' => '11.3155',
        'decl' => '13.0922',
        'type' => 'GALXY',
    ], $fields));
}

function storeMeasurementTestObject($test, array $fields)
{
    DB::table('deepskytypes')->insertOrIgnore(['code' => 'GALXY', 'name' => 'Galaxy']);
    $admin = $test->createUserAndAssignToTeam('Administrators');

    return $test->actingAs($admin)->post(route('object.store'), array_merge([
        'name' => 'Wdm 1',
        'ra' => '11 18 55.8',
        'decl' => '13 05 32',
        'type' => 'GALXY',
    ], $fields));
}

it('stores the sentinels for missing values when an object is created', function () {
    storeMeasurementTestObject($this, [])->assertSessionHasNoErrors();

    $object = DB::table('objects')->where('name', 'Wdm 1')->first();
    expect((float) $object->mag)->toBe(99.9)
        ->and((float) $object->subr)->toBe(99.9)
        ->and((int) $object->pa)->toBe(999)
        ->and((float) $object->SBObj)->toBe(-999.0)
        ->and((float) $object->diam1)->toBe(0.0);
});

it('keeps a magnitude and position angle of 0 when an object is created', function () {
    storeMeasurementTestObject($this, ['mag' => '0', 'pa' => '0', 'diam1' => '2'])->assertSessionHasNoErrors();

    $object = DB::table('objects')->where('name', 'Wdm 1')->first();
    expect((float) $object->mag)->toBe(0.0)
        ->and((int) $object->pa)->toBe(0)
        ->and((float) $object->diam1)->toBe(120.0)
        ->and((float) $object->SBObj)->toEqualWithDelta(2.5 * log10(M_PI * 120 * 120 / 4), 0.001);
});

it('stores the sentinels for missing values when an object is edited', function () {
    insertMeasurementTestObject('M 65', 'm-65');

    updateMeasurementTestObject($this, [])->assertSessionHasNoErrors();

    $object = DB::table('objects')->where('name', 'M 65')->first();
    expect((float) $object->mag)->toBe(99.9)
        ->and((float) $object->subr)->toBe(99.9)
        ->and((int) $object->pa)->toBe(999)
        ->and((float) $object->SBObj)->toBe(-999.0);
});

it('calculates SBObj of an edited object without a surface brightness', function () {
    insertMeasurementTestObject('M 65', 'm-65');

    updateMeasurementTestObject($this, ['mag' => '9.3', 'diam1' => '10', 'diam2' => '3'])->assertSessionHasNoErrors();

    $object = DB::table('objects')->where('name', 'M 65')->first();
    expect((float) $object->subr)->toBe(99.9)
        ->and((float) $object->SBObj)->toEqualWithDelta(9.3 + 2.5 * log10(M_PI * 600 * 180 / 4), 0.001);
});

it('keeps a magnitude and position angle of 0 when an object is edited', function () {
    insertMeasurementTestObject('M 65', 'm-65');

    updateMeasurementTestObject($this, ['mag' => '0', 'pa' => '0', 'diam1' => '10'])->assertSessionHasNoErrors();

    $object = DB::table('objects')->where('name', 'M 65')->first();
    expect((float) $object->mag)->toBe(0.0)
        ->and((int) $object->pa)->toBe(0)
        ->and((float) $object->SBObj)->not->toBe(-999.0);
});

it('rejects an unknown object type when an object is edited', function () {
    insertMeasurementTestObject('M 65', 'm-65');

    updateMeasurementTestObject($this, ['type' => 'NOPE'])->assertSessionHasErrors('type');

    expect(DB::table('objects')->where('name', 'M 65')->value('type'))->toBe('GALXY');
});

it('finds an existing object by its alias in the create wizard', function () {
    insertMeasurementTestObject('M 65', 'm-65');
    DB::table('objectnames')->insert([
        'objectname' => 'M 65',
        'catalog' => 'NGC',
        'catindex' => '3623',
        'altname' => 'NGC 3623',
        'slug' => 'ngc-3623',
        'timestamp' => now(),
    ]);
    $admin = $this->createUserAndAssignToTeam('Administrators');

    $this->actingAs($admin)->get(route('object.nameSearch', ['catalog' => 'NGC', 'number' => '3623']))
        ->assertOk()
        ->assertSee('M 65')
        ->assertSee('(NGC 3623)', false)
        ->assertSee(route('object.show', 'm-65'), false);
});
