<?php

use Illuminate\Support\Facades\DB;

function catalogueRow(string $name): void
{
    DB::table('objects')->insert([
        'name' => $name,
        'type' => 'ASTER',
        'ra' => 5.0,
        'decl' => 40.0,
        'mag' => 8.0,
        'milleniumbase' => '',
        'timestamp' => '2026-01-01 00:00:00',
    ]);
}

function aliasRow(string $objectName, string $alias): int
{
    return (int) DB::table('objectnames')->insertGetId([
        'objectname' => $objectName,
        'catalog' => '',
        'catindex' => $alias,
        'altname' => $alias,
        'timestamp' => '2026-01-01 00:00:00',
    ]);
}

it('leaves the catalogue alone without --apply', function () {
    catalogueRow('T&ouml;pler 2');

    $this->artisan('objects:fix-html-entities')
        ->expectsOutputToContain('Would rename "T&ouml;pler 2" -> "Töpler 2"')
        ->assertSuccessful();

    expect(DB::table('objects')->where('name', 'T&ouml;pler 2')->exists())->toBeTrue();
});

it('decodes an entity in an object name', function () {
    catalogueRow('T&ouml;pler 2');

    $this->artisan('objects:fix-html-entities', ['--apply' => true])->assertSuccessful();

    expect(DB::table('objects')->pluck('name')->all())->toBe(['Töpler 2']);
});

it('carries every reference to a renamed object across with it', function () {
    catalogueRow('T&ouml;pler 2');
    $aliasId = aliasRow('T&ouml;pler 2', 'Topler 2');

    DB::table('observations')->insert([
        'target_table' => 'objects',
        'target_key' => 'T&ouml;pler 2',
        'target_name' => 'T&ouml;pler 2',
        'observer_username' => 'someone',
        'observed_on' => '2026-01-01',
    ]);

    $this->artisan('objects:fix-html-entities', ['--apply' => true])->assertSuccessful();

    // Nothing may be left pointing at a name the catalogue no longer holds.
    // Only the rows created here are checked -- migrations seed the alias
    // table with Messier names that have nothing to do with this rename.
    expect(DB::table('objectnames')->where('id', $aliasId)->value('objectname'))->toBe('Töpler 2');
    expect(DB::table('objectnames')->where('objectname', 'T&ouml;pler 2')->exists())->toBeFalse();
    expect(DB::table('observations')->pluck('target_name')->all())->toBe(['Töpler 2']);
    expect(DB::table('observations')->pluck('target_key')->all())->toBe(['Töpler 2']);
});

it('decodes the entities in the alias names as well', function () {
    catalogueRow('NGC 3310');
    $id = aliasRow('NGC 3310', 'Bow &amp; Arrow');

    $this->artisan('objects:fix-html-entities', ['--apply' => true])->assertSuccessful();

    $alias = DB::table('objectnames')->where('id', $id)->first();

    expect($alias->altname)->toBe('Bow & Arrow');
    expect($alias->catindex)->toBe('Bow & Arrow');
});

it('refuses to merge an object onto a name the catalogue already holds', function () {
    catalogueRow('T&ouml;pler 2');
    catalogueRow('Töpler 2');

    $this->artisan('objects:fix-html-entities', ['--apply' => true])
        ->expectsOutputToContain('is already in the catalogue')
        ->assertSuccessful();

    expect(DB::table('objects')->where('name', 'T&ouml;pler 2')->exists())->toBeTrue();
});

it('changes nothing on a second run', function () {
    catalogueRow('T&ouml;pler 2');
    aliasRow('T&ouml;pler 2', 'Bow &amp; Arrow');

    $this->artisan('objects:fix-html-entities', ['--apply' => true])->assertSuccessful();

    $this->artisan('objects:fix-html-entities', ['--apply' => true])
        ->expectsOutputToContain('Objects renamed: 0')
        ->expectsOutputToContain('Alias names corrected: 0')
        ->expectsOutputToContain('No encoded names left in the catalogue.')
        ->assertSuccessful();
});

it('leaves a name that only looks like it carries an entity', function () {
    // An ampersand is not an entity, and the index and the charts show it
    // exactly as the catalogue spells it.
    catalogueRow('Bow & Arrow');

    $this->artisan('objects:fix-html-entities', ['--apply' => true])->assertSuccessful();

    expect(DB::table('objects')->pluck('name')->all())->toBe(['Bow & Arrow']);
});
