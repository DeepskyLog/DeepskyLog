<?php

use App\Models\Observation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function insertStatisticsTestObject(string $name, string $type): void
{
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => str($name)->slug()->toString(),
        'type' => $type,
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
        'timestamp' => now(),
    ]);
}

function insertStatisticsTestObjectname(string $objectName, string $slug): void
{
    DB::table('objectnames')->insert([
        'objectname' => $objectName,
        'slug' => $slug,
        'catalog' => strtok($objectName, ' '),
        'catindex' => trim((string) str($objectName)->after(' ')),
        'altname' => $objectName,
        'timestamp' => now(),
    ]);
}

function insertStatisticsTestLocation(int $userId, string $name, string $country): int
{
    return (int) DB::table('locations')->insertGetId([
        'name' => $name,
        'longitude' => 4.35,
        'latitude' => 50.85,
        'timezone' => 'UTC',
        'limitingMagnitude' => -999,
        'skyBackground' => -999,
        'elevation' => 0,
        'country' => $country,
        'active' => true,
        'user_id' => $userId,
        'observer' => 'test-observer',
        'observations' => 0,
        'picture' => null,
        'slug' => str($name)->slug()->toString(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('renders database statistics and filters them by country', function () {
    insertStatisticsTestObject('M 31', 'GX');
    insertStatisticsTestObject('M 40', 'DS');
    insertStatisticsTestObject('STF 123', 'DS');
    insertStatisticsTestObjectname('M 31', 'm-31');
    insertStatisticsTestObjectname('M 40', 'm-40');

    $user = User::factory()->create();
    $belgiumId = insertStatisticsTestLocation($user->id, 'Brussels', 'Belgium');
    $franceId = insertStatisticsTestLocation($user->id, 'Paris', 'France');

    Observation::query()->insert([
        [
            'observer_username' => 'observer-a',
            'location_id' => $belgiumId,
            'observed_on' => now()->toDateString(),
            'target_type' => 'deepsky',
            'target_table' => 'objectnames',
            'target_key' => 'm-31',
            'target_name' => 'M 31',
            'has_drawing' => false,
            'language' => 'en',
            'legacy_source' => null,
            'legacy_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'observer_username' => 'observer-b',
            'location_id' => $belgiumId,
            'observed_on' => now()->toDateString(),
            'target_type' => 'deepsky',
            'target_table' => 'objectnames',
            'target_key' => 'm-40',
            'target_name' => 'M 40',
            'has_drawing' => false,
            'language' => 'en',
            'legacy_source' => null,
            'legacy_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'observer_username' => 'observer-c',
            'location_id' => $belgiumId,
            'observed_on' => now()->toDateString(),
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'stf-123',
            'target_name' => 'STF 123',
            'has_drawing' => false,
            'language' => 'en',
            'legacy_source' => 'deepsky',
            'legacy_id' => 1234,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'observer_username' => 'observer-d',
            'location_id' => $belgiumId,
            'observed_on' => now()->toDateString(),
            'target_type' => 'planet',
            'target_table' => 'planets',
            'target_key' => 'jupiter',
            'target_name' => 'Jupiter',
            'has_drawing' => false,
            'language' => 'en',
            'legacy_source' => null,
            'legacy_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'observer_username' => 'observer-e',
            'location_id' => $franceId,
            'observed_on' => now()->toDateString(),
            'target_type' => 'comet',
            'target_table' => 'cometobjects',
            'target_key' => '42',
            'target_name' => 'C/2023 A1',
            'has_drawing' => true,
            'language' => 'en',
            'legacy_source' => null,
            'legacy_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'observer_username' => 'observer-f',
            'location_id' => $franceId,
            'observed_on' => now()->toDateString(),
            'target_type' => 'moon',
            'target_table' => 'moons',
            'target_key' => 'moon',
            'target_name' => 'Moon',
            'has_drawing' => true,
            'language' => 'en',
            'legacy_source' => null,
            'legacy_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->get(route('statistics.index'));

    $response->assertOk();
    $response->assertViewHas('selectedCountry', null);
    $response->assertViewHas('countryOptions', function (array $options) {
        return array_key_exists('Belgium', $options) && array_key_exists('France', $options);
    });
    $response->assertViewHas('summaryRows', function (array $rows) {
        $observations = collect($rows)->firstWhere('key', 'observations');

        return $observations['values']['total'] === 6
            && $observations['values']['deepsky'] === 1
            && $observations['values']['comets'] === 1
            && $observations['values']['double'] === 2
            && $observations['values']['planets'] === 1
            && $observations['values']['sun'] === 0
            && $observations['values']['moon'] === 1;
    });

    $filteredResponse = $this->get(route('statistics.index', ['country' => 'Belgium']));

    $filteredResponse->assertOk();
    $filteredResponse->assertViewHas('selectedCountry', 'Belgium');
    $filteredResponse->assertViewHas('summaryRows', function (array $rows) {
        $observations = collect($rows)->firstWhere('key', 'observations');
        $drawings = collect($rows)->firstWhere('key', 'drawings');

        return $observations['values']['total'] === 4
            && $observations['values']['deepsky'] === 1
            && $observations['values']['comets'] === 0
            && $observations['values']['double'] === 2
            && $observations['values']['planets'] === 1
            && $observations['values']['moon'] === 0
            && $drawings['values']['total'] === 0;
    });
});