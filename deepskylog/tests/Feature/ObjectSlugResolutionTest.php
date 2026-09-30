<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

function insertSlugTestObject(string $name, string $slug): void
{
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => $slug,
        'type' => 'GALXY',
        'con' => 'CAS',
        'ra' => 1.0,
        'decl' => 60.0,
        'mag' => 12.0,
        'subr' => 13.0,
        'pa' => 0,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 60.0,
        'diam2' => 30.0,
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
    DB::table('objectnames')->insert([
        'objectname' => $name,
        'catalog' => strtok($name, ' '),
        'catindex' => trim((string) strstr($name, ' ')),
        'altname' => $name,
        'slug' => $slug,
        'timestamp' => now(),
    ]);
}

/**
 * The object the slug resolves to, read from the resolution cache that
 * ObjectController::show() fills before it renders the page (the page itself
 * uses MariaDB functions that the SQLite test database does not have).
 */
function objectShownFor(string $url): ?string
{
    $slug = substr($url, strlen('/object/'));
    test()->get($url);

    return Cache::get('object:resolve:v1:' . sha1(mb_strtolower($slug)))['pk'] ?? null;
}

it('finds an object by its name without spaces', function () {
    insertSlugTestObject('NGC 7000', 'ngc-7000');

    expect(objectShownFor('/object/ngc7000'))->toBe('NGC 7000')
        ->and(objectShownFor('/object/NGC7000'))->toBe('NGC 7000');
});

it('prefers the name that matches with only spaces or only dashes removed', function () {
    insertSlugTestObject('IC 187-1', 'ic-187-1');
    insertSlugTestObject('IC 1871', 'ic-1871');

    // Both are IC1871 with spaces and dashes removed.
    expect(objectShownFor('/object/ic1871'))->toBe('IC 1871')
        ->and(objectShownFor('/object/ic187-1'))->toBe('IC 187-1');
});

it('falls back to the name with both spaces and dashes removed', function () {
    insertSlugTestObject('Sh 2-155', 'sh-2-155');

    expect(objectShownFor('/object/sh2155'))->toBe('Sh 2-155');
});

it('returns 404 for an unknown object', function () {
    insertSlugTestObject('NGC 7000', 'ngc-7000');

    $this->get('/object/no-such-object-xyz')->assertNotFound();
});
