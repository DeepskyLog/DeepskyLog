<?php

use Illuminate\Support\Facades\DB;

// A new name of a catalog DeepskyLog does not have yet starts a new catalog
// only when the edit form says so; otherwise it is a name without a catalog
// (#526). Before, a typo such as NCG 891 silently became a catalog.

function insertCatalogTestObject(string $name, array $altNames = []): void
{
    DB::table('deepskytypes')->insertOrIgnore(['code' => 'GALXY', 'name' => 'Galaxy']);
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => \Illuminate\Support\Str::slug($name),
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
    foreach ([$name, ...$altNames] as $altName) {
        [$catalog, $catindex] = \App\Models\ObjectName::splitCatalogDesignation($altName);
        DB::table('objectnames')->insertOrIgnore([
            'objectname' => $name,
            'catalog' => $catalog,
            'catindex' => $catindex,
            'altname' => $altName,
            'slug' => \App\Models\ObjectName::slugFor($altName),
            'timestamp' => now(),
        ]);
    }
}

function updateCatalogTestObject($test, string $alternativeNames, array $extra = [])
{
    $admin = $test->createUserAndAssignToTeam('Administrators');

    return $test->actingAs($admin)->put(route('object.update', ['slug' => 'm-65']), array_merge([
        'name' => 'M 65',
        'ra' => '11.3155',
        'decl' => '13.0922',
        'type' => 'GALXY',
        'alternative_names' => $alternativeNames,
    ], $extra));
}

function catalogOfName(string $altName): ?array
{
    $row = DB::table('objectnames')->where('altname', $altName)->first();

    return $row ? [$row->catalog, $row->catindex] : null;
}

beforeEach(function () {
    insertCatalogTestObject('M 65', ['NGC 3623']);
});

it('refuses a new name of an unknown catalog without a choice', function () {
    updateCatalogTestObject($this, 'M 65, NGC 3623, NCG 3623')
        ->assertSessionHasErrors('alternative_names');

    expect(catalogOfName('NCG 3623'))->toBeNull()
        ->and(catalogOfName('NGC 3623'))->toBe(['NGC', '3623']);
});

it('adds a new catalog when the form says so', function () {
    updateCatalogTestObject($this, 'M 65, NGC 3623, Wdm 7', [
        'name_catalogs' => [['name' => 'Wdm 7', 'choice' => 'new']],
    ])->assertSessionHasNoErrors();

    expect(catalogOfName('Wdm 7'))->toBe(['Wdm', '7']);
});

it('stores a name without a catalog when the form says so', function () {
    updateCatalogTestObject($this, 'M 65, NGC 3623, Hamburger Galaxy', [
        'name_catalogs' => [['name' => 'Hamburger Galaxy', 'choice' => 'none']],
    ])->assertSessionHasNoErrors();

    expect(catalogOfName('Hamburger Galaxy'))->toBe(['', 'Hamburger Galaxy']);
});

it('asks about a catalog no object has a name in', function () {
    updateCatalogTestObject($this, 'M 65, NGC 3623, ugc 6328')
        ->assertSessionHasErrors('alternative_names');
});

it('gives a name of an existing catalog the spelling of the catalog', function () {
    insertCatalogTestObject('M 66', ['UGC 6346']);
    updateCatalogTestObject($this, 'M 65, NGC 3623, ugc 6328')->assertSessionHasNoErrors();

    expect(catalogOfName('ugc 6328'))->toBe(['UGC', '6328']);
});

it('keeps the names the object already has', function () {
    DB::table('objectnames')->insert([
        'objectname' => 'M 65', 'catalog' => 'Leo', 'catindex' => 'Triplet member',
        'altname' => 'Leo Triplet member', 'slug' => 'leo-triplet-member', 'timestamp' => now(),
    ]);

    updateCatalogTestObject($this, 'M 65, NGC 3623, Leo Triplet member')->assertSessionHasNoErrors();

    expect(catalogOfName('Leo Triplet member'))->toBe(['Leo', 'Triplet member']);
});

it('asks about a renamed object of an unknown catalog', function () {
    $admin = $this->createUserAndAssignToTeam('Administrators');

    $this->actingAs($admin)->put(route('object.update', ['slug' => 'm-65']), [
        'name' => 'Leo Galaxy',
        'ra' => '11.3155',
        'decl' => '13.0922',
        'type' => 'GALXY',
    ])->assertSessionHasErrors('alternative_names');

    expect(DB::table('objects')->where('name', 'M 65')->exists())->toBeTrue();
});

it('gives the edit page the catalogs DeepskyLog has', function () {
    $admin = $this->createUserAndAssignToTeam('Administrators');

    $this->actingAs($admin)->get(route('object.edit', ['slug' => 'm-65']))
        ->assertOk()
        ->assertSee('newCatalogs(', false)
        ->assertSee('ngc', false);
});
