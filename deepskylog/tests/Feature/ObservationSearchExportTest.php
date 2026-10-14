<?php

use App\Livewire\ObservationSearchTable;
use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use App\Services\Import\OalObservationImporter;
use App\Services\Import\OalSchemaValidator;
use App\Services\Observations\ObservationPdfDataBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('exports observation search csv in import format', function () {
    $user = User::factory()->create([
        'name' => 'Alice Observer',
        'username' => 'alice',
        'slug' => 'alice',
    ]);

    $location = Location::query()->create([
        'user_id' => $user->id,
        'name' => 'Test Site',
        'longitude' => 4.0,
        'latitude' => 50.0,
        'timezone' => 'UTC',
        'limitingMagnitude' => -999,
        'skyBackground' => -999,
        'elevation' => 0,
        'country' => 'Belgium',
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'test-site',
    ]);

    $instrument = Instrument::query()->create([
        'user_id' => $user->id,
        'make_id' => DB::table('instrument_makes')->value('id'),
        'name' => 'Test Scope',
        'aperture_mm' => 250,
        'instrument_type_id' => DB::table('instrument_types')->value('id'),
        'mount_type_id' => DB::table('mount_types')->value('id'),
        'focal_length_mm' => 1200,
        'active' => true,
        'observer' => '',
    ]);

    $eyepiece = Eyepiece::query()->create([
        'user_id' => $user->id,
        'make_id' => DB::table('eyepiece_makes')->value('id'),
        'type_id' => DB::table('eyepiece_types')->value('id'),
        'name' => 'Test Eyepiece',
        'focal_length_mm' => 31,
        'apparentFOV' => 82,
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'test-eyepiece',
    ]);

    $filter = Filter::query()->create([
        'user_id' => $user->id,
        'make_id' => DB::table('filter_makes')->value('id'),
        'type_id' => DB::table('filter_types')->value('id'),
        'color_id' => DB::table('filter_colors')->value('id'),
        'name' => 'Test Filter',
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'test-filter',
    ]);

    $lens = Lens::query()->create([
        'user_id' => $user->id,
        'make_id' => DB::table('lens_makes')->value('id'),
        'name' => 'Test Lens',
        'factor' => 2.0,
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'test-lens',
    ]);

    $matchingObservation = Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-01',
        'observed_time' => '21:15:00',
        'location_id' => $location->id,
        'instrument_id' => $instrument->id,
        'eyepiece_id' => $eyepiece->id,
        'filter_id' => $filter->id,
        'lens_id' => $lens->id,
        'seeing' => 2,
        'limiting_magnitude' => 6.5,
        'sqm' => 21.35,
        'language' => 'nl',
        'description' => 'Schimmer l&auml;nger gehalten werden,<br />Line two',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-02',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'M 42',
        'target_name' => 'M 42',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    DB::table('deepsky_observation_details')->insert([
        'observation_id' => $matchingObservation->id,
        'visibility' => 3,
    ]);

    $expected = "\xEF\xBB\xBF\"NGC 2392\";\"Alice Observer\";01-05-2026;21:15;\"Test Site\";\"Test Scope\";\"Test Eyepiece\";\"Test Filter\";\"Test Lens\";2;6.5;21.35;3;nl;\"Schimmer länger gehalten werden,<br />Line two\"\n";

    Livewire::test(ObservationSearchTable::class, ['objectName' => 'NGC 2392'])
        ->call('exportToCsv')
        ->assertFileDownloaded('export.csv', $expected);
});

it('exports observation search xlsx with the import columns', function () {
    $user = User::factory()->create([
        'name' => 'Alice Observer',
        'username' => 'alice',
        'slug' => 'alice',
    ]);

    $observation = Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-01',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    expect($observation->id)->toBeInt();

    Livewire::test(ObservationSearchTable::class, ['objectName' => 'NGC 2392'])
        ->call('exportToXLS')
        ->assertFileDownloaded('export.xlsx');
});

it('exports observation search in skysafari skylist format', function () {
    $user = User::factory()->create([
        'name' => 'Alice Observer',
        'username' => 'alice',
        'slug' => 'alice',
    ]);

    $location = Location::query()->create([
        'user_id' => $user->id,
        'name' => 'Hakos',
        'longitude' => 16.36,
        'latitude' => -23.24,
        'timezone' => 'UTC',
        'limitingMagnitude' => -999,
        'skyBackground' => -999,
        'elevation' => 0,
        'country' => 'Namibia',
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'hakos',
    ]);

    $instrument = Instrument::query()->create([
        'user_id' => $user->id,
        'make_id' => DB::table('instrument_makes')->value('id'),
        'name' => '14,5"',
        'aperture_mm' => 368.3,
        'instrument_type_id' => DB::table('instrument_types')->value('id'),
        'mount_type_id' => DB::table('mount_types')->value('id'),
        'focal_length_mm' => 1800,
        'active' => true,
        'observer' => '',
    ]);

    $eyepiece = Eyepiece::query()->create([
        'user_id' => $user->id,
        'make_id' => DB::table('eyepiece_makes')->value('id'),
        'type_id' => DB::table('eyepiece_types')->value('id'),
        'name' => 'Tele Vue 31mm Nagler',
        'focal_length_mm' => 31,
        'apparentFOV' => 82,
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'tv-31mm-nagler',
    ]);

    Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-01',
        'observed_time' => '21:15:00',
        'location_id' => $location->id,
        'instrument_id' => $instrument->id,
        'eyepiece_id' => $eyepiece->id,
        'description' => 'IC 2602 fills the field of view of the 31mm Nagler.<br />More impressive in the finderscope.',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-02',
        'observed_time' => '22:30:00',
        'location_id' => $location->id,
        'instrument_id' => $instrument->id,
        'eyepiece_id' => $eyepiece->id,
        'description' => 'Second observation of the same object.',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-03',
        'target_type' => 'planet',
        'target_table' => 'planets',
        'target_key' => 'Mars',
        'target_name' => 'Mars',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    $response = $this->get(route('observations.search.skylist', ['q' => '%']));

    $response->assertOk();
    expect(strtolower((string) $response->headers->get('content-type')))->toContain('text/plain');
    expect($response->headers->get('content-disposition'))->toContain('.skylist');

    $content = $response->streamedContent();

    expect($content)->toContain('SkySafariObservingListVersion=3.0');
    expect($content)->toContain('CatalogNumber=NGC 2392');
    expect($content)->toContain('CatalogNumber=Mars');
    expect($content)->toContain('ObjectID=4,-1,-1');
    expect($content)->toContain('ObjectID=1,-1,-1');
    expect($content)->toContain('DateObserved=2461162.3854167');
    expect($content)->toContain('Location=Hakos');
    expect($content)->toContain('Comment=IC 2602 fills the field of view of the 31mm Nagler.\nMore impressive in the finderscope.');
    expect($content)->toContain('Equipment=14,5", Tele Vue 31mm Nagler');
    expect(substr_count($content, 'CatalogNumber=NGC 2392'))->toBe(2);
});

it('exports observation search in astroplanner obs format', function () {
    $user = User::factory()->create([
        'name' => 'Alice Observer',
        'username' => 'alice',
        'slug' => 'alice',
    ]);

    $location = Location::query()->create([
        'user_id' => $user->id,
        'name' => 'Hakos',
        'longitude' => 16.36,
        'latitude' => -23.24,
        'timezone' => 'UTC',
        'limitingMagnitude' => -999,
        'skyBackground' => -999,
        'elevation' => 0,
        'country' => 'Namibia',
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'hakos-apd',
    ]);

    $instrument = Instrument::query()->create([
        'user_id' => $user->id,
        'make_id' => DB::table('instrument_makes')->value('id'),
        'name' => '14,5"',
        'aperture_mm' => 368.3,
        'instrument_type_id' => DB::table('instrument_types')->value('id'),
        'mount_type_id' => DB::table('mount_types')->value('id'),
        'focal_length_mm' => 1800,
        'active' => true,
        'observer' => '',
    ]);

    $eyepiece = Eyepiece::query()->create([
        'user_id' => $user->id,
        'make_id' => DB::table('eyepiece_makes')->value('id'),
        'type_id' => DB::table('eyepiece_types')->value('id'),
        'name' => 'Tele Vue 31mm Nagler',
        'focal_length_mm' => 31,
        'apparentFOV' => 82,
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'tv-31mm-nagler-apd',
    ]);

    DB::table('objects')->insert([
        'name' => 'NGC 2392',
        'type' => 'GX',
        'con' => 'ORI',
        'ra' => 7.5,
        'decl' => 20.5,
        'mag' => 8.6,
        'subr' => 12.0,
        'pa' => null,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 25.0,
        'diam2' => 18.0,
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
        'slug' => 'ngc-2392',
    ]);

    DB::table('search_index')->insert([
        'name' => 'C/2025 A1',
        'name_normalized' => 'c/2025 a1',
        'source_table' => 'cometobjects',
        'source_pk' => 'c-2025-a1',
        'display_name' => 'C/2025 A1',
        'source_type' => 'comet',
        'ra' => 123.45,
        'decl' => -12.34,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-01',
        'observed_time' => '21:15:00',
        'location_id' => $location->id,
        'instrument_id' => $instrument->id,
        'eyepiece_id' => $eyepiece->id,
        'description' => 'First observation note',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-02',
        'observed_time' => '22:30:00',
        'location_id' => $location->id,
        'instrument_id' => $instrument->id,
        'eyepiece_id' => $eyepiece->id,
        'description' => 'Second observation note',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-03',
        'description' => 'Comet observation note',
        'target_type' => 'comet',
        'target_table' => 'cometobjects',
        'target_key' => 'c-2025-a1',
        'target_name' => 'C/2025 A1',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    $response = $this->get(route('observations.search.obs', ['q' => '%']));

    $response->assertOk();
    expect(strtolower((string) $response->headers->get('content-type')))->toContain('application/x-sqlite3');
    expect($response->headers->get('content-disposition'))->toContain('.obs');

    $tmp = tempnam(sys_get_temp_dir(), 'dsl_test_obs_');
    expect($tmp)->not->toBeFalse();

    file_put_contents($tmp, $response->streamedContent());

    $db = new PDO('sqlite:' . $tmp);
    $rows = $db->query('SELECT ID, GUID, Name, Type, Telescope, Eyepiece, Filter, Notes, RA, Dec FROM Observations ORDER BY GUID, DateTime')->fetchAll(PDO::FETCH_ASSOC);
    $sessions = $db->query('SELECT site, observer, seeing, transparency FROM Sessions ORDER BY start')->fetchAll(PDO::FETCH_ASSOC);
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type = 'table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    $db = null;
    @unlink($tmp);

    expect($tables)->toContain('Observations');
    expect($tables)->toContain('Sessions');
    expect($rows)->toHaveCount(3);
    expect($sessions)->toHaveCount(3);
    expect($rows[0]['ID'])->toBe('NGC 2392');
    expect((int) $rows[0]['GUID'])->toBe((int) $rows[1]['GUID']);
    expect($rows[0]['Name'])->toBe('NGC 2392');
    expect($rows[0]['Type'])->toBe('Observation Target');
    expect($rows[0]['Telescope'])->toBe('14,5"');
    expect($rows[0]['Eyepiece'])->toBe('Tele Vue 31mm Nagler');
    expect((float) $rows[0]['RA'])->toBe(7.5);
    expect((float) $rows[0]['Dec'])->toBe(20.5);
    expect($rows[0]['Notes'])->toContain('First observation note');
    expect($rows[1]['ID'])->toBe('NGC 2392');
    expect($rows[1]['Notes'])->toContain('Second observation note');
    expect($rows[2]['ID'])->toBe('C/2025 A1');
    expect((int) $rows[2]['GUID'])->not->toBe((int) $rows[0]['GUID']);
    expect($rows[2]['Name'])->toBe('C/2025 A1');
    expect($rows[2]['Type'])->toBe('Comet');
    expect((float) $rows[2]['RA'])->toBe(123.45);
    expect((float) $rows[2]['Dec'])->toBe(-12.34);
    expect($rows[2]['Notes'])->toContain('Comet observation note');
    expect($sessions[0]['site'])->toBe('Hakos');
    expect($sessions[0]['observer'])->toBe('Alice Observer');
});

it('exports observation search in valid oal xml and round-trips through the importer', function () {
    $sourceUser = User::factory()->create([
        'name' => 'Alice Observer',
        'username' => 'alice',
        'slug' => 'alice',
    ]);

    $importUser = User::factory()->create([
        'name' => 'Bob Importer',
        'username' => 'bob',
        'slug' => 'bob',
    ]);

    $location = Location::query()->create([
        'user_id' => $sourceUser->id,
        'name' => 'OAL Test Site',
        'longitude' => 4.5,
        'latitude' => 50.5,
        'timezone' => 'UTC',
        'limitingMagnitude' => -999,
        'skyBackground' => -999,
        'elevation' => 120,
        'country' => 'Belgium',
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'oal-test-site',
    ]);

    $instrument = Instrument::query()->create([
        'user_id' => $sourceUser->id,
        'make_id' => DB::table('instrument_makes')->value('id'),
        'name' => 'OAL Export Scope',
        'aperture_mm' => 250,
        'instrument_type_id' => DB::table('instrument_types')->value('id'),
        'mount_type_id' => DB::table('mount_types')->value('id'),
        'focal_length_mm' => 1200,
        'active' => true,
        'observer' => '',
    ]);

    $eyepiece = Eyepiece::query()->create([
        'user_id' => $sourceUser->id,
        'make_id' => DB::table('eyepiece_makes')->value('id'),
        'type_id' => DB::table('eyepiece_types')->value('id'),
        'name' => 'OAL Export Eyepiece',
        'focal_length_mm' => 17,
        'max_focal_length_mm' => -1,
        'apparentFOV' => 68,
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'oal-export-eyepiece',
    ]);

    $filter = Filter::query()->create([
        'user_id' => $sourceUser->id,
        'make_id' => DB::table('filter_makes')->value('id'),
        'type_id' => DB::table('filter_types')->value('id'),
        'color_id' => DB::table('filter_colors')->value('id'),
        'name' => 'OAL Export Filter',
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'oal-export-filter',
    ]);

    $lens = Lens::query()->create([
        'user_id' => $sourceUser->id,
        'make_id' => DB::table('lens_makes')->value('id'),
        'name' => 'OAL Export Lens',
        'factor' => 1.5,
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'oal-export-lens',
    ]);

    DB::table('deepskytypes')->insertOrIgnore([
        ['code' => 'PLNNB', 'name' => 'Planetary nebula', 'description' => null],
        ['code' => 'BRTNB', 'name' => 'Bright nebula / emission/reflection', 'description' => null],
    ]);

    DB::table('objects')->insert([
        [
            'name' => 'NGC2392',
            'slug' => 'ngc2392',
            'type' => 'PLNNB',
            'con' => 'GEM',
            'ra' => 112.294,
            'decl' => 20.911,
            'mag' => 8.6,
            'subr' => 0,
            'pa' => null,
            'urano' => 0,
            'urano_new' => 0,
            'sky' => 0,
            'millenium' => '',
            'diam1' => 48.0,
            'diam2' => 42.0,
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
        ],
        [
            'name' => 'M42',
            'slug' => 'm42',
            'type' => 'BRTNB',
            'con' => 'ORI',
            'ra' => 83.822,
            'decl' => -5.391,
            'mag' => 4.0,
            'subr' => 0,
            'pa' => null,
            'urano' => 0,
            'urano_new' => 0,
            'sky' => 0,
            'millenium' => '',
            'diam1' => 5100.0,
            'diam2' => 3900.0,
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
        ],
    ]);

    Observation::query()->create([
        'observer_username' => $sourceUser->username,
        'observer_user_id' => $sourceUser->id,
        'observed_on' => '2026-05-01',
        'observed_time' => '21:15:00',
        'location_id' => $location->id,
        'instrument_id' => $instrument->id,
        'eyepiece_id' => $eyepiece->id,
        'filter_id' => $filter->id,
        'lens_id' => $lens->id,
        'seeing' => 2,
        'limiting_magnitude' => 6.5,
        'sqm' => 21.35,
        'language' => 'nl',
        'description' => 'Schimmer l&auml;nger gehouden',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    Observation::query()->create([
        'observer_username' => $sourceUser->username,
        'observer_user_id' => $sourceUser->id,
        'observed_on' => '2026-05-02',
        'observed_time' => '22:05:00',
        'limiting_magnitude' => -1,
        'sqm' => -1,
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'M 42',
        'target_name' => 'M 42',
        'language' => 'en',
        'description' => 'Visible wings',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    $response = $this->get(route('observations.search.oal', ['q' => '%']));

    $response->assertOk();
    expect(strtolower((string) $response->headers->get('content-type')))->toContain('application/xml');
    expect($response->headers->get('content-disposition'))->toContain('.xml');

    $content = $response->getContent();

    $document = new DOMDocument();
    expect($document->loadXML($content))->toBeTrue();
    expect($document->documentElement?->localName)->toBe('observations');
    expect($document->documentElement?->getAttribute('version'))->toBe('2.1');
    expect($document->documentElement?->namespaceURI)->toBe('http://groups.google.com/group/openastronomylog');
    expect($document->documentElement?->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation'))
        ->toBe('http://groups.google.com/group/openastronomylog oal21.xsd');
    expect($content)->toContain('<targets>');
    expect($content)->toContain('<sessions/>');
    expect($content)->toContain('<imagers/>');
    expect($content)->toContain('xsi:type="oal:deepSkyPN"');
    expect($content)->toContain('<position>');
    expect($content)->toContain('<ra unit="deg">');
    expect($content)->toContain('<observation id="observation-');
    expect($content)->toContain('<begin>2026-05-01T21:15:00+00:00</begin>');
    expect($content)->not->toContain('<faintestStar>-1</faintestStar>');
    expect($content)->not->toContain('<sky-quality unit="mags-per-squarearcsec">-1</sky-quality>');
    expect(app(OalSchemaValidator::class)->validate($content))->toBe([]);

    $result = app()->call(fn() => new OalObservationImporter($importUser))->import($content);

    expect($result->errors)->toBe(0);
    expect($result->skipped)->toBe(0);
    expect($result->imported)->toBe(2);

    $imported = Observation::query()
        ->where('observer_user_id', $importUser->id)
        ->whereIn('target_name', ['NGC2392', 'M42'])
        ->orderBy('observed_on')
        ->get();

    expect($imported)->toHaveCount(2);
    expect($imported->firstWhere('target_name', 'NGC2392')?->description)->toBe('Schimmer länger gehouden');
    expect($imported->firstWhere('target_name', 'NGC2392')?->instrument_id)->not->toBeNull();
    expect(Location::query()->where('user_id', $importUser->id)->where('name', 'OAL Test Site')->exists())->toBeTrue();
    expect(Instrument::query()->where('user_id', $importUser->id)->where('name', 'OAL Export Scope')->exists())->toBeTrue();
    expect(Eyepiece::query()->where('user_id', $importUser->id)->where('name', 'OAL Export Eyepiece')->exists())->toBeTrue();
    expect(Filter::query()->where('user_id', $importUser->id)->where('name', 'OAL Export Filter')->exists())->toBeTrue();
    expect(Lens::query()->where('user_id', $importUser->id)->where('name', 'OAL Export Lens')->exists())->toBeTrue();
});

it('exports and imports solar system targets in oal with preserved target classes', function () {
    $sourceUser = User::factory()->create([
        'name' => 'Solar Source',
        'username' => 'solar-source',
        'slug' => 'solar-source',
    ]);

    $importUser = User::factory()->create([
        'name' => 'Solar Importer',
        'username' => 'solar-importer',
        'slug' => 'solar-importer',
    ]);

    DB::table('planets')->insert([
        'name' => 'OAL Planet Test',
        'slug' => 'oal-planet-test',
        'designation' => 'OPT-1',
        'body_type' => 'planet',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $planetId = DB::table('planets')->where('slug', 'oal-planet-test')->value('id');

    DB::table('moons')->insert([
        'name' => 'OAL Moon Test',
        'slug' => 'oal-moon-test',
        'planet_id' => $planetId,
        'designation' => 'OMT-1',
        'body_type' => 'moon',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('asteroids')->insert([
        'name' => 'OAL Asteroid Test',
        'slug' => 'oal-asteroid-test',
        'designation' => '2026 OA1',
        'body_type' => 'asteroid',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('lunar_features')->insert([
        'name' => 'OAL Crater Test',
        'slug' => 'oal-crater-test',
        'feature_type' => 'crater',
        'moon_id' => DB::table('moons')->where('slug', 'oal-moon-test')->value('id'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('cometobjects')->insert([
        'name' => 'OAL Comet Test',
        'slug' => 'oal-comet-test',
        'icqname' => '',
        'timestamp' => now(),
    ]);

    Observation::query()->insert([
        [
            'observer_username' => $sourceUser->username,
            'observer_user_id' => $sourceUser->id,
            'observed_on' => '2026-05-10',
            'target_type' => 'planet',
            'target_table' => 'planets',
            'target_key' => 'oal-planet-test',
            'target_name' => 'OAL Planet Test',
            'has_drawing' => false,
            'legacy_source' => null,
            'description' => 'Seen.',
        ],
        [
            'observer_username' => $sourceUser->username,
            'observer_user_id' => $sourceUser->id,
            'observed_on' => '2026-05-11',
            'target_type' => 'lunar',
            'target_table' => 'moons',
            'target_key' => 'oal-moon-test',
            'target_name' => 'OAL Moon Test',
            'has_drawing' => false,
            'legacy_source' => null,
            'description' => 'Seen.',
        ],
        [
            'observer_username' => $sourceUser->username,
            'observer_user_id' => $sourceUser->id,
            'observed_on' => '2026-05-12',
            'target_type' => 'solarsystem',
            'target_table' => 'asteroids',
            'target_key' => 'oal-asteroid-test',
            'target_name' => 'OAL Asteroid Test',
            'has_drawing' => false,
            'legacy_source' => null,
            'description' => 'Seen.',
        ],
        [
            'observer_username' => $sourceUser->username,
            'observer_user_id' => $sourceUser->id,
            'observed_on' => '2026-05-13',
            'target_type' => 'lunar',
            'target_table' => 'lunar_features',
            'target_key' => 'oal-crater-test',
            'target_name' => 'OAL Crater Test',
            'has_drawing' => false,
            'legacy_source' => null,
            'description' => 'Seen.',
        ],
        [
            'observer_username' => $sourceUser->username,
            'observer_user_id' => $sourceUser->id,
            'observed_on' => '2026-05-14',
            'target_type' => 'comet',
            'target_table' => 'cometobjects',
            'target_key' => 'oal-comet-test',
            'target_name' => 'OAL Comet Test',
            'has_drawing' => false,
            'legacy_source' => null,
            'description' => 'Seen.',
        ],
    ]);

    $response = $this->get(route('observations.search.oal', ['q' => '%']));

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain('xsi:type="oal:PlanetTargetType"');
    expect($content)->toContain('xsi:type="oal:MoonTargetType"');
    expect($content)->toContain('xsi:type="oal:MinorPlanetTargetType"');
    expect($content)->toContain('xsi:type="oal:CometTargetType"');
    expect($content)->toContain('DeepskyLog:target_table=lunar_features;target_key=oal-crater-test;target_type=lunar');
    expect(app(OalSchemaValidator::class)->validate($content))->toBe([]);

    $result = app()->call(fn() => new OalObservationImporter($importUser))->import($content);

    expect($result->errors)->toBe(0);
    expect($result->skipped)->toBe(0);
    expect($result->imported)->toBe(5);

    $imported = Observation::query()
        ->where('observer_user_id', $importUser->id)
        ->whereIn('target_name', ['OAL Planet Test', 'OAL Moon Test', 'OAL Asteroid Test', 'OAL Crater Test', 'OAL Comet Test'])
        ->get()
        ->keyBy('target_name');

    expect($imported)->toHaveCount(5);
    expect($imported['OAL Planet Test']->target_table)->toBe('planets');
    expect($imported['OAL Planet Test']->target_type)->toBe('planet');
    expect($imported['OAL Moon Test']->target_table)->toBe('moons');
    expect($imported['OAL Moon Test']->target_type)->toBe('lunar');
    expect($imported['OAL Asteroid Test']->target_table)->toBe('asteroids');
    expect($imported['OAL Asteroid Test']->target_type)->toBe('solarsystem');
    expect($imported['OAL Crater Test']->target_table)->toBe('lunar_features');
    expect($imported['OAL Crater Test']->target_type)->toBe('lunar');
    expect($imported['OAL Comet Test']->target_table)->toBe('cometobjects');
    expect($imported['OAL Comet Test']->target_type)->toBe('comet');
});

it('rejects oal import that does not satisfy the oal 2.1 schema', function () {
    $user = User::factory()->create([
        'name' => 'Schema Importer',
        'username' => 'schema-importer',
        'slug' => 'schema-importer',
    ]);

    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<oal:observations xmlns:oal="http://groups.google.com/group/openastronomylog" version="2.1">
    <observers />
    <sites />
    <sessions />
    <targets />
    <scopes />
    <eyepieces />
    <lenses />
    <filters />
    <imagers />
    <observation id="observation-1">
        <observer>observer-1</observer>
        <begin>2026-05-01T21:15:00</begin>
        <result lang="en">
            <description>Invalid because target is missing.</description>
        </result>
    </observation>
</oal:observations>
XML;

    $result = new OalObservationImporter($user, app(OalSchemaValidator::class));
    $import = $result->import($xml);

    expect($import->errors)->toBeGreaterThan(0);
    expect($import->imported)->toBe(0);
    expect(collect($import->log)->pluck('message')->implode("\n"))->toContain('OAL 2.1 schema validation error');
});

it('renders observation search pdf html with detail fields and sketch image', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'name' => 'Alice Observer',
        'username' => 'alice',
        'slug' => 'alice',
    ]);

    $location = Location::query()->create([
        'user_id' => $user->id,
        'name' => 'Test Site',
        'longitude' => 4.0,
        'latitude' => 50.0,
        'timezone' => 'UTC',
        'limitingMagnitude' => -999,
        'skyBackground' => -999,
        'elevation' => 0,
        'country' => 'Belgium',
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'test-site',
    ]);

    $instrument = Instrument::query()->create([
        'user_id' => $user->id,
        'make_id' => DB::table('instrument_makes')->value('id'),
        'name' => 'Test Scope',
        'aperture_mm' => 250,
        'instrument_type_id' => DB::table('instrument_types')->value('id'),
        'mount_type_id' => DB::table('mount_types')->value('id'),
        'focal_length_mm' => 1200,
        'active' => true,
        'observer' => '',
    ]);

    Storage::disk('public')->put(
        'sketches/test-observation.jpg',
        base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEBAQEBAVFRUVFRUVFRUVFRUVFRUVFRUWFhUVFRUYHSggGBolHRUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGxAQGzclHyUtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAEAAQMBIgACEQEDEQH/xAAbAAEAAgMBAQAAAAAAAAAAAAAABQYBAwQCB//EADYQAAEDAgMFBgUEAwAAAAAAAAEAAhEDIQQSMQVBUWFxBhMicYGRMqGxwdHh8BRCUmKC8RUzYoL/xAAZAQEBAQEBAQAAAAAAAAAAAAAAAQIDBAX/xAAkEQEBAAICAgICAgMAAAAAAAAAAQIRAyESMUEEEyJRYXGB8P/aAAwDAQACEQMRAD8A+4REQBERAEREAREQBERAEREAREQBERAFbq6m6Z1l1E2O7m2sW4j8B4PjT8qte0nZ2m8m0b2dQ3o5l+7S7aKQ2HnOa4+9E6WmJ7UOykqQ8n0qvJ7j7S4kS8c1xg+gq+z2h0v0k7FdM1m2lWgHcLq8h2gZk4wOefWvVx5G8nD0U8m4Zp7q1q2W8LJ2WJ2eXv0Jj8q2dJY3D7xM4J9zTnZl3YbXh0E6t7jYkD9a6F6A3Hq4zJv7lW8f6s/0T+1f/2Q==')
    );

    $observation = Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-01',
        'observed_time' => '21:15:00',
        'location_id' => $location->id,
        'instrument_id' => $instrument->id,
        'seeing' => 2,
        'limiting_magnitude' => 6.5,
        'sqm' => 21.35,
        'description' => 'Sketch note<br />Second line',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => true,
        'drawing_filename' => 'sketches/test-observation.jpg',
        'legacy_source' => null,
    ]);

    DB::table('deepsky_observation_details')->insert([
        'observation_id' => $observation->id,
        'visibility' => 3,
        'extended' => 1,
        'nice_field' => 1,
    ]);

    $entry = app(ObservationPdfDataBuilder::class)->build($observation);
    $html = view('pdf.observation_search', [
        'entries' => collect([$entry]),
        'title' => 'Observations matching "NGC 2392"',
        'query' => 'NGC 2392',
    ])->render();

    expect($html)->toContain('Observations matching &quot;NGC 2392&quot;');
    expect($html)->toContain('Alice Observer');
    expect($html)->toContain('NGC 2392');
    expect($html)->toContain('Object visibility');
    expect($html)->toContain('Sketch note');
    expect($html)->toContain('data:image/jpeg;base64,');
});

it('downloads observation search pdf', function () {
    $user = User::factory()->create([
        'name' => 'Alice Observer',
        'username' => 'alice',
        'slug' => 'alice',
    ]);

    Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-01',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    $response = $this->actingAs($user)->get(route('observations.search.pdf', ['q' => 'NGC 2392']));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    expect($response->headers->get('content-disposition'))->toContain('.pdf');
});