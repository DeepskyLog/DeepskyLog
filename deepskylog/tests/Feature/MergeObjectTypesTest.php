<?php

use App\Services\ObjectSearchIndex;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// objects:merge-types gives the objects of the rarely used object types
// another type and removes those types (#409).

function mergeTypesObject(string $name, string $type): void
{
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => Str::slug($name),
        'type' => $type,
        'con' => 'ORI',
        'ra' => 5.6,
        'decl' => -5.4,
        'mag' => 4.0,
        'subr' => 99.9,
        'pa' => 999,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 0.0,
        'diam2' => 0.0,
        'datasource' => null,
        'taki' => '',
        'SBObj' => -999,
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
    [$catalog, $index] = App\Models\ObjectName::splitCatalogDesignation($name);
    DB::table('objectnames')->insert([
        'objectname' => $name,
        'catalog' => $catalog,
        'catindex' => $index,
        'altname' => $name,
        'slug' => Str::slug($name),
        'timestamp' => now(),
    ]);
    ObjectSearchIndex::syncObject($name);
}

function mergeTypesSetUp(): void
{
    foreach (['CLANB', 'ENRNN', 'ENSTR', 'RNHII', 'STNEB', 'HII', 'EMINB', 'REFNB', 'OPNCL'] as $code) {
        DB::table('deepskytypes')->insertOrIgnore(['code' => $code, 'name' => $code]);
    }
    mergeTypesObject('M 42', 'ENRNN');
    mergeTypesObject('M 17', 'CLANB');
    mergeTypesObject('NGC 2264', 'CLANB');
    mergeTypesObject('IC 4592', 'ENSTR');
    mergeTypesObject('NGC 2048', 'ENSTR');
    mergeTypesObject('NGC 2024', 'RNHII');
    mergeTypesObject('Gum 46', 'STNEB');
    mergeTypesObject('Hickson 752', 'ENSTR');
}

function mergeTypesOf(string $name): ?string
{
    return DB::table('objects')->where('name', $name)->value('type');
}

it('changes nothing without --apply', function () {
    mergeTypesSetUp();

    $this->artisan('objects:merge-types')
        ->expectsOutputToContain('Objects given another type: 7')
        ->assertSuccessful();

    expect(mergeTypesOf('M 42'))->toBe('ENRNN')
        ->and(mergeTypesOf('Hickson 752'))->toBe('ENSTR')
        ->and(DB::table('deepskytypes')->where('code', 'ENRNN')->exists())->toBeTrue();
});

it('gives the objects their new type, also in the search index', function () {
    mergeTypesSetUp();

    $this->artisan('objects:merge-types', ['--apply' => true])->assertSuccessful();

    expect(mergeTypesOf('M 42'))->toBe('HII')
        ->and(mergeTypesOf('M 17'))->toBe('HII')
        ->and(mergeTypesOf('NGC 2264'))->toBe('OPNCL')
        ->and(mergeTypesOf('IC 4592'))->toBe('REFNB')
        ->and(mergeTypesOf('NGC 2048'))->toBe('EMINB')
        ->and(mergeTypesOf('NGC 2024'))->toBe('HII')
        ->and(mergeTypesOf('Gum 46'))->toBe('EMINB')
        ->and(DB::table('search_index')->where('source_pk', 'm-42')->where('source_type', '!=', 'alias')->value('source_type'))->toBe('HII')
        ->and(DB::table('objects')->whereIn('type', array_keys(App\Console\Commands\MergeObjectTypes::REMOVED_TYPES))->exists())->toBeFalse();
});

it('removes the types', function () {
    mergeTypesSetUp();

    $this->artisan('objects:merge-types', ['--apply' => true])->assertSuccessful();

    expect(DB::table('deepskytypes')->whereIn('code', ['CLANB', 'ENRNN', 'ENSTR', 'RNHII', 'STNEB'])->exists())->toBeFalse()
        ->and(DB::table('target_types')->whereIn('id', ['CLANB', 'ENRNN', 'ENSTR', 'RNHII', 'STNEB'])->exists())->toBeFalse()
        ->and(DB::table('deepskytypes')->where('code', 'HII')->exists())->toBeTrue();
});

it('deletes the test object with its names and metrics', function () {
    mergeTypesSetUp();
    DB::table('user_object_metrics')->insert(['user_id' => 1, 'object_name' => 'Hickson 752', 'created_at' => now(), 'updated_at' => now()]);

    $this->artisan('objects:merge-types', ['--apply' => true])->assertSuccessful();

    expect(DB::table('objects')->where('name', 'Hickson 752')->exists())->toBeFalse()
        ->and(DB::table('objectnames')->where('objectname', 'Hickson 752')->exists())->toBeFalse()
        ->and(DB::table('search_index')->where('source_pk', 'hickson-752')->exists())->toBeFalse()
        ->and(DB::table('user_object_metrics')->where('object_name', 'Hickson 752')->exists())->toBeFalse();
});

it('keeps a test object with observations', function () {
    mergeTypesSetUp();
    DB::table('observations')->insert([
        'observer_username' => 'testobserver',
        'target_table' => 'objects',
        'target_key' => 'Hickson 752',
        'target_name' => 'Hickson 752',
        'observed_on' => '2009-05-11',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->artisan('objects:merge-types', ['--apply' => true])
        ->expectsOutputToContain('Hickson 752 has 1 observation(s) and is not deleted.')
        ->assertSuccessful();

    expect(DB::table('objects')->where('name', 'Hickson 752')->exists())->toBeTrue();
});
