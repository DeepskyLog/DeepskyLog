<?php

use App\Models\Observation;
use Illuminate\Support\Facades\DB;

function insertPopularTestObject(string $name, string $slug): void
{
    DB::table('objects')->insert([
        'name' => $name,
        'type' => 'GX',
        'con' => 'AND',
        'ra' => 0,
        'decl' => 0,
        'mag' => 4.0,
        'subr' => 0,
        'pa' => null,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 10,
        'diam2' => 5,
        'datasource' => null,
        'taki' => '',
        'SBObj' => 0,
        'description' => '',
        'psa' => '',
        'torresB' => '',
        'torresBC' => '',
        'torresC' => '',
        'milleniumbase' => '',
        'DSLDL' => '0',
        'DSLDP' => '0',
        'DSLLL' => '0',
        'DSLLP' => '0',
        'DSLOL' => '0',
        'DSLOP' => '0',
        'DeepskyHunter' => '0',
        'Interstellarum' => '0',
        'slug' => $slug,
        'timestamp' => now(),
    ]);

    DB::table('objectnames')->insert([
        'objectname' => $name,
        'slug' => $slug,
        'catalog' => strtok($name, ' '),
        'catindex' => trim((string) str($name)->after(' ')),
        'altname' => $name,
        'timestamp' => now(),
    ]);
}

function insertPopularTestComet(int $id, string $name, string $slug): void
{
    DB::table('cometobjects')->insert([
        'id' => $id,
        'name' => $name,
        'icqname' => '',
        'slug' => $slug,
        'timestamp' => now(),
    ]);
}

it('renders popular objects ordered by observation count', function () {
    insertPopularTestObject('M 31', 'm-31');
    insertPopularTestObject('NGC 7000', 'ngc-7000');

    Observation::query()->insert([
        [
            'observer_username' => 'observer-a',
            'observed_on' => '2026-05-20',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'm-31',
            'target_name' => 'M 31',
            'has_drawing' => false,
            'language' => 'en',
            'legacy_source' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'observer_username' => 'observer-b',
            'observed_on' => '2026-05-21',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'm-31',
            'target_name' => 'M 31',
            'has_drawing' => true,
            'language' => 'en',
            'legacy_source' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'observer_username' => 'observer-c',
            'observed_on' => '2026-05-22',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'ngc-7000',
            'target_name' => 'NGC 7000',
            'has_drawing' => true,
            'language' => 'en',
            'legacy_source' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->get(route('objects.popular'));

    $response->assertOk();
    $response->assertSee('Popular objects');
    $response->assertSeeInOrder(['M 31', 'NGC 7000'], false);
    $response->assertSee(route('object.show', ['slug' => 'm-31']), false);
    $response->assertSee(route('observations.object', ['slug' => 'm-31']), false);
    $response->assertSee('2 observations');
});

it('switches popular objects to drawing counts', function () {
    insertPopularTestObject('M 31', 'm-31');
    insertPopularTestObject('NGC 7000', 'ngc-7000');

    Observation::query()->insert([
        [
            'observer_username' => 'observer-a',
            'observed_on' => '2026-05-20',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'm-31',
            'target_name' => 'M 31',
            'has_drawing' => false,
            'language' => 'en',
            'legacy_source' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'observer_username' => 'observer-b',
            'observed_on' => '2026-05-21',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'm-31',
            'target_name' => 'M 31',
            'has_drawing' => true,
            'language' => 'en',
            'legacy_source' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'observer_username' => 'observer-c',
            'observed_on' => '2026-05-22',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'ngc-7000',
            'target_name' => 'NGC 7000',
            'has_drawing' => true,
            'language' => 'en',
            'legacy_source' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'observer_username' => 'observer-d',
            'observed_on' => '2026-05-23',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'ngc-7000',
            'target_name' => 'NGC 7000',
            'has_drawing' => true,
            'language' => 'en',
            'legacy_source' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->get(route('objects.popular', ['mode' => 'drawings']));

    $response->assertOk();
    $response->assertSeeInOrder(['NGC 7000', 'M 31'], false);
    $response->assertSee(route('observations.drawings.show', ['slug' => 'ngc-7000']), false);
    $response->assertSee('2 drawings');
    $response->assertDontSee('3 drawings');
});

it('uses comet object routes for popular comet objects', function () {
    insertPopularTestComet(42, 'C/2023 A1', 'c-2023-a1');

    Observation::query()->insert([
        [
            'observer_username' => 'observer-comet-a',
            'observed_on' => '2026-05-20',
            'target_type' => 'comet',
            'target_table' => 'cometobjects',
            'target_key' => '42',
            'target_name' => 'C/2023 A1',
            'has_drawing' => true,
            'language' => 'en',
            'legacy_source' => 'comet',
            'legacy_id' => 4201,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'observer_username' => 'observer-comet-b',
            'observed_on' => '2026-05-21',
            'target_type' => 'comet',
            'target_table' => 'cometobjects',
            'target_key' => '42',
            'target_name' => 'C/2023 A1',
            'has_drawing' => false,
            'language' => 'en',
            'legacy_source' => 'comet',
            'legacy_id' => 4202,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->get(route('objects.popular'));

    $response->assertOk();
    $response->assertSee(route('object.show', ['slug' => 'c-2023-a1']), false);
    $response->assertSee(route('observations.comet.object', ['object' => 'c-2023-a1']), false);
});