<?php

use App\Models\ObjectName;
use Illuminate\Support\Facades\DB;

function insertAliasTestObject(string $name, string $slug): void
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

function aliasCatalog(string $altname): array
{
    $row = DB::table('objectnames')->where('altname', $altname)->first();

    return [$row->catalog, $row->catindex];
}

it('splits a name into catalog and catindex at the first space', function (string $name, string $catalog, string $catindex) {
    expect(ObjectName::splitCatalogDesignation($name))->toBe([$catalog, $catindex]);
})->with([
    ['H400 12', 'H400', '12'],
    ['vdB-Ha 5', 'vdB-Ha', '5'],
    ['H N 122', 'H', 'N 122'],
    ['NGC 3623', 'NGC', '3623'],
    ['3C 273', '3C', '273'],
    ['Merope', '', 'Merope'],
    ['1 Ari', '', '1 Ari'],
]);

it('stores the catalog and catindex of new aliases when an object is edited', function () {
    insertAliasTestObject('M 65', 'm-65');
    $admin = $this->createUserAndAssignToTeam('Administrators');

    $this->actingAs($admin)->put(route('object.update', ['slug' => 'm-65']), [
        'name' => 'M 65',
        'ra' => '11.3155',
        'decl' => '13.0922',
        'type' => 'GALXY',
        'mag' => '9.3',
        'subr' => '12.4',
        'diam1' => '10',
        'diam2' => '3',
        'alternative_names' => 'M 65, H400 12, vdB-Ha 5',
        // The test database has no H400 or vdB-Ha names yet
        'name_catalogs' => [
            ['name' => 'H400 12', 'choice' => 'new'],
            ['name' => 'vdB-Ha 5', 'choice' => 'new'],
        ],
    ])->assertSessionHasNoErrors();

    expect(aliasCatalog('M 65'))->toBe(['M', '65'])
        ->and(aliasCatalog('H400 12'))->toBe(['H400', '12'])
        ->and(aliasCatalog('vdB-Ha 5'))->toBe(['vdB-Ha', '5']);
});

it('keeps the catalog and catindex of aliases that are not changed', function () {
    insertAliasTestObject('M 65', 'm-65');
    DB::table('objectnames')->insert([
        'objectname' => 'M 65',
        'catalog' => '',
        'catindex' => 'HIP 55183',
        'altname' => 'HIP 55183',
        'slug' => 'hip-55183',
        'timestamp' => now(),
    ]);
    $admin = $this->createUserAndAssignToTeam('Administrators');

    $this->actingAs($admin)->put(route('object.update', ['slug' => 'm-65']), [
        'name' => 'M 65',
        'ra' => '11.3155',
        'decl' => '13.0922',
        'type' => 'GALXY',
        'mag' => '9.3',
        'subr' => '12.4',
        'diam1' => '10',
        'diam2' => '3',
        'alternative_names' => 'HIP 55183',
    ])->assertSessionHasNoErrors();

    expect(aliasCatalog('HIP 55183'))->toBe(['', 'HIP 55183'])
        ->and(aliasCatalog('M 65'))->toBe(['M', '65']);
});
