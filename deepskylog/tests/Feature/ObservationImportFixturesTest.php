<?php

use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use App\Services\Import\CsvObservationImporter;
use App\Services\Import\ImportResult;
use App\Services\Import\OalObservationImporter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/*
 * Runs the example files of tests/Fixtures/ObservationImport (described in its
 * README.md) through the CSV and OAL importers.
 */

function importFixture(string $path): string
{
    return file_get_contents(base_path('tests/Fixtures/ObservationImport/'.$path));
}

function importFixtureObject(string $name, string $slug, string $type, float $ra, float $decl): void
{
    DB::table('objects')->insert([
        'name' => $name, 'slug' => $slug, 'type' => $type, 'con' => 'AND', 'ra' => $ra, 'decl' => $decl,
        'mag' => 5.0, 'subr' => 12.0, 'pa' => 0, 'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '',
        'diam1' => 60.0, 'diam2' => 60.0, 'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '',
        'psa' => '', 'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0', 'DeepskyHunter' => '0',
        'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

/**
 * The Solar System targets of the solar-system and all-types files, with the
 * orbital elements of 12P/Pons-Brooks (MPC), so its horizon check is real.
 */
function importFixtureSolarSystem(): void
{
    $now = now();
    foreach (['Sun' => 'star', 'Earth' => 'planet', 'Jupiter' => 'planet'] as $planet => $type) {
        DB::table('planets')->insert(['name' => $planet, 'slug' => strtolower($planet), 'body_type' => $type, 'created_at' => $now, 'updated_at' => $now]);
    }
    DB::table('asteroids')->insert(['name' => 'Vesta', 'slug' => 'vesta', 'designation' => '4', 'body_type' => 'asteroid', 'created_at' => $now, 'updated_at' => $now]);
    $planetIds = DB::table('planets')->pluck('id', 'name');
    DB::table('moons')->insert([
        ['name' => 'Moon', 'slug' => 'moon', 'planet_id' => $planetIds['Earth'], 'body_type' => 'moon', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'Io', 'slug' => 'io', 'planet_id' => $planetIds['Jupiter'], 'body_type' => 'moon', 'created_at' => $now, 'updated_at' => $now],
    ]);
    foreach (['Moon' => 'other', 'Copernicus' => 'crater', 'Tycho' => 'crater'] as $feature => $type) {
        DB::table('lunar_features')->insert(['name' => $feature, 'slug' => strtolower($feature), 'feature_type' => $type, 'created_at' => $now, 'updated_at' => $now]);
    }
    DB::table('cometobjects')->insert(['name' => '12P/Pons-Brooks', 'slug' => '12ppons-brooks', 'icqname' => '', 'timestamp' => $now]);
    DB::table('comets_orbital_elements')->insert([
        'name' => '12P/Pons-Brooks', 'epoch' => 2461307.5, 'q' => 0.781885, 'e' => 0.954746, 'i' => 74.1494,
        'w' => 199.0345, 'node' => 255.8673, 'Tp' => 20240421.2023, 'H' => 5, 'n' => 13.5, 'ref' => 'MPC191593',
    ]);
}

/**
 * @return list<string>
 */
function importMessages(ImportResult $result, string $type): array
{
    return array_values(array_map(
        fn (array $entry) => $entry['message'],
        array_filter($result->log, fn (array $entry) => $entry['type'] === $type),
    ));
}

beforeEach(function () {
    importFixtureObject('M 31', 'm-31', 'GALXY', 0.7123, 41.2692);
    importFixtureObject('M 13', 'm-13', 'GLOCL', 16.6949, 36.4613);
    importFixtureObject('M 42', 'm-42', 'BRTNB', 5.5881, -5.3911);
    importFixtureObject('M 45', 'm-45', 'OPNCL', 3.7833, 24.1167);
    importFixtureObject('M 81', 'm-81', 'GALXY', 9.9259, 69.0653);
    importFixtureObject('NGC 104', 'ngc-104', 'GLOCL', 0.4014, -72.0814);
    importFixtureSolarSystem();

    // The equipment and location the CSV files name (see README.md).
    $this->user = User::factory()->create(['username' => 'example-observer']);
    $owner = ['user_id' => $this->user->id, 'observer' => $this->user->username, 'active' => true];

    Location::query()->create($owner + [
        'name' => 'Example Site', 'slug' => 'example-site', 'latitude' => 50.88, 'longitude' => 4.70, 'elevation' => 30,
        'timezone' => 'Europe/Brussels', 'limitingMagnitude' => -999, 'skyBackground' => -999, 'country' => 'Belgium',
        'observations' => 0,
    ]);
    Instrument::query()->create($owner + [
        'name' => 'Example Dobson 250', 'make_id' => DB::table('instrument_makes')->value('id'), 'aperture_mm' => 250,
        'focal_length_mm' => 1200, 'instrument_type_id' => 4, 'mount_type_id' => DB::table('mount_types')->value('id'),
    ]);
    Instrument::query()->create($owner + [
        'name' => 'Naked eye', 'make_id' => DB::table('instrument_makes')->value('id'), 'aperture_mm' => 7,
        'focal_length_mm' => 1, 'fixedMagnification' => 1, 'instrument_type_id' => 1,
        'mount_type_id' => DB::table('mount_types')->value('id'),
    ]);
    Eyepiece::query()->create($owner + [
        'name' => 'Example 10mm', 'slug' => 'example-10mm', 'make_id' => DB::table('eyepiece_makes')->value('id'),
        'type_id' => DB::table('eyepiece_types')->value('id'), 'focal_length_mm' => 10, 'apparentFOV' => 82,
        'observations' => 0,
    ]);
    Filter::query()->create($owner + [
        'name' => 'Example UHC', 'make_id' => DB::table('filter_makes')->value('id'),
        'type_id' => DB::table('filter_types')->value('id'), 'color_id' => DB::table('filter_colors')->value('id'),
    ]);
    Lens::query()->create($owner + [
        'name' => 'Example Barlow 2x', 'slug' => 'example-barlow-2x', 'make_id' => DB::table('lens_makes')->value('id'),
        'factor' => 2.0, 'observations' => 0,
    ]);
});

function runImportFixture(User $user, string $file, bool $belowHorizon = false): ImportResult
{
    $importer = str_starts_with($file, 'csv/') ? new CsvObservationImporter($user) : new OalObservationImporter($user);

    return $importer->importBelowHorizon($belowHorizon)->import(importFixture($file));
}

/*
 * Per file: the observations imported, the rows skipped, and the errors and
 * warnings expected, each as a part of the message, in order.
 */
function importFixtureExpectations(): array
{
    return [
        'csv/valid-observations.csv' => ['csv/valid-observations.csv', 6, 0, [], []],
        'csv/valid-export-format.csv' => ['csv/valid-export-format.csv', 2, 0, [], []],
        'csv/invalid-unknown-object.csv' => ['csv/invalid-unknown-object.csv', 0, 1, ["Row 1: Object 'Example Unknown Nebula 123' not found in the database, skipping row."], []],
        'csv/invalid-rows.csv' => ['csv/invalid-rows.csv', 0, 12, [
            'Object name is empty',
            'Row 2: Instrument is empty',
            'Row 3: Description is empty',
            'Row 4: Description is empty',
            "Cannot parse date '2026/09/12'",
            "Row 6: Location 'Example Unknown Site' not found",
            "Row 7: Instrument 'Example Unknown Scope' not found",
            "Row 8: Eyepiece 'Example Unknown Eyepiece' not found",
            "Row 9: Filter 'Example Unknown Filter' not found",
            "Row 10: Lens 'Example Unknown Lens' not found",
            "Row 11: 'M 42' was 44.4° below the horizon of Example Site",
            "Row 12: 'NGC 104' does not rise above the horizon of Example Site",
        ], []],
        'csv/mixed.csv' => ['csv/mixed.csv', 2, 2, [
            "Row 2: Instrument 'Example Unknown Scope' not found",
            "Cannot parse date '31-13-2026'",
        ], []],
        'csv/below-horizon.csv' => ['csv/below-horizon.csv', 0, 2, [
            "Row 1: 'M 42' was 44.4° below the horizon",
            "Row 2: 'NGC 104' does not rise above the horizon",
        ], []],
        'csv/valid-solar-system.csv' => ['csv/valid-solar-system.csv', 6, 0, [], []],
        'csv/valid-all-types.csv' => ['csv/valid-all-types.csv', 8, 0, [], []],
        'csv/invalid-solar-system.csv' => ['csv/invalid-solar-system.csv', 0, 4, [
            "Row 1: Object 'C/2099 Z1 (Example)' not found in the database",
            "Row 2: Object 'Example Crater' not found in the database",
            "Row 3: 'Moon' was 19.2° below the horizon of Example Site on 2026-09-25 at 06:00 UT.",
            "Row 4: '12P/Pons-Brooks' was 7.6° below the horizon of Example Site on 2024-03-20 at 23:30 UT.",
        ], []],
        'oal/valid-observations.xml' => ['oal/valid-observations.xml', 5, 0, [], []],
        'oal/valid-solar-system.xml' => ['oal/valid-solar-system.xml', 5, 0, [], []],
        'oal/valid-all-types.xml' => ['oal/valid-all-types.xml', 8, 0, [], []],
        'oal/invalid-solar-system.xml' => ['oal/invalid-solar-system.xml', 0, 3, [
            "Observation 1: Target 'Example Crater' is not in the DeepskyLog database, and the file does not define it as a deep-sky object, skipping.",
            "Observation 2: 'Moon' was 19.2° below the horizon of Example Site on 2026-09-25 at 06:00 UT.",
            "Observation 3: '12P/Pons-Brooks' was 7.6° below the horizon of Example Site on 2024-03-20 at 23:30 UT.",
        ], []],
        'oal/valid-latin1.xml' => ['oal/valid-latin1.xml', 1, 0, [], []],
        'oal/valid-new-targets.xml' => ['oal/valid-new-targets.xml', 3, 0, [], []],
        'oal/invalid-unknown-targets.xml' => ['oal/invalid-unknown-targets.xml', 0, 4, [
            "Observation 1: Target 'Example Unknown Nebula 123' is not in the DeepskyLog database, and the file gives no valid position for it, skipping.",
            "Observation 2: Target 'Example Unknown Object 5' is not in the DeepskyLog database, and its type in the file (oal:deepskyna) has no DeepskyLog object type, skipping.",
            "Observation 3: Target 'C/2099 Z1 (Example)' is not in the DeepskyLog database, and the file does not define it as a deep-sky object, skipping.",
            "Observation 4: 'Example Southern Galaxy' was 56.7° below the horizon of Example Site",
        ], []],
        'oal/warning-no-observations.xml' => ['oal/warning-no-observations.xml', 0, 0, [], ['No <observation> elements found']],
        'oal/invalid-not-well-formed.xml' => ['oal/invalid-not-well-formed.xml', 0, 0, [
            'OAL 2.1 schema validation error: line 19: Opening and ending tag mismatch',
            'OAL 2.1 schema validation error: line 20: Premature end of data',
        ], []],
        'oal/invalid-schema-missing-target.xml' => ['oal/invalid-schema-missing-target.xml', 0, 0, [
            "OAL 2.1 schema validation error: line 14: Element 'begin': This element is not expected. Expected is one of ( session, target ).",
        ], []],
        'oal/invalid-schema-date-only.xml' => ['oal/invalid-schema-date-only.xml', 0, 0, [
            "OAL 2.1 schema validation error: line 18: Element 'begin': '2026-09-12' is not a valid value of the atomic type 'xs:dateTime'.",
        ], []],
        'oal/invalid-schema-unknown-reference.xml' => ['oal/invalid-schema-unknown-reference.xml', 0, 0, [
            "No match found for key-sequence ['scope-does-not-exist'] of keyref",
        ], []],
        'oal/invalid-observations.xml' => ['oal/invalid-observations.xml', 0, 4, [
            'Observation 1: Description is empty',
            "Observation 2: 'M 42' was 44.4° below the horizon",
            "Observation 3: 'NGC 104' does not rise above the horizon",
            'Observation 4: The scope has no model or vendor',
        ], []],
        'oal/below-horizon.xml' => ['oal/below-horizon.xml', 0, 2, [
            "Observation 1: 'M 42' was 44.4° below the horizon",
            "Observation 2: 'NGC 104' does not rise above the horizon",
        ], []],
    ];
}

dataset('import fixtures', fn () => importFixtureExpectations());

it('imports the example file with the expected outcome', function (string $file, int $imported, int $skipped, array $errors, array $warnings) {
    $result = runImportFixture($this->user, $file);

    expect($result->imported)->toBe($imported)
        ->and($result->skipped)->toBe($skipped)
        ->and(Observation::query()->where('observer_user_id', $this->user->id)->count())->toBe($imported);

    foreach ([[$errors, 'error'], [$warnings, 'warning']] as [$expected, $type]) {
        $messages = importMessages($result, $type);
        expect($messages)->toHaveCount(count($expected));
        foreach ($expected as $i => $part) {
            expect($messages[$i])->toContain($part);
        }
    }
})->with('import fixtures');

it('covers every example file', function () {
    $files = collect(glob(base_path('tests/Fixtures/ObservationImport/*/*.{csv,xml}'), GLOB_BRACE))
        ->map(fn (string $path) => basename(dirname($path)).'/'.basename($path))
        ->sort()->values()->all();
    $covered = collect(importFixtureExpectations())->keys()->sort()->values()->all();

    expect($covered)->toBe($files);
});

it('stores the fields of the valid csv file', function () {
    runImportFixture($this->user, 'csv/valid-observations.csv');

    $observations = Observation::query()->orderBy('id')->get();
    $site = Location::query()->where('name', 'Example Site')->value('id');
    $dobson = Instrument::query()->where('name', 'Example Dobson 250')->value('id');

    [$m31, $m13, $m42, $m81, $m45, $m31NoTime] = $observations->all();
    expect($m31->target_name)->toBe('M 31')
        ->and($m31->observed_on->format('Y-m-d'))->toBe('2026-09-12')
        ->and($m31->observed_time)->toBe('21:30:00')
        ->and($m31->location_id)->toBe($site)
        ->and($m31->instrument_id)->toBe($dobson)
        ->and($m31->eyepiece_id)->toBe(Eyepiece::query()->value('id'))
        ->and($m31->seeing)->toBe(2)
        ->and((float) $m31->limiting_magnitude)->toBe(6.2)
        ->and((float) $m31->sqm)->toBe(20.85)
        ->and(DB::table('deepsky_observation_details')->where('observation_id', $m31->id)->value('visibility'))->toBe(2);
    expect($m13->lens_id)->toBe(Lens::query()->value('id'));
    // Written as "M042" and "M81".
    expect($m42->target_name)->toBe('M 42')
        ->and($m42->target_key)->toBe('M 42')
        ->and($m42->observed_on->format('Y-m-d'))->toBe('2026-01-15')
        ->and($m42->filter_id)->toBe(Filter::query()->value('id'))
        ->and($m42->language)->toBe('nl')
        ->and($m42->description)->toContain('<strong>zeer uitgebreid</strong>');
    expect($m81->target_name)->toBe('M 81')
        ->and($m81->observed_time)->toBe('20:15:30');
    expect($m45->instrument_id)->toBe(Instrument::query()->where('name', 'Naked eye')->value('id'));
    expect($m31NoTime->target_name)->toBe('M 31')
        ->and($m31NoTime->observed_time)->toBeNull()
        ->and($m31NoTime->location_id)->toBeNull()
        ->and($m31NoTime->instrument_id)->toBe($dobson);
});

it('reads the quoted fields and the byte order mark of a csv export', function () {
    runImportFixture($this->user, 'csv/valid-export-format.csv');

    $observation = Observation::query()->orderBy('id')->first();
    expect($observation->target_name)->toBe('M 31')
        ->and($observation->description)->toBe('Bright core; a "dust lane" with averted vision.');
});

it('stores the fields of the valid oal file and creates the missing site and scope', function () {
    $result = runImportFixture($this->user, 'oal/valid-observations.xml');

    expect(array_keys($result->newLocations))->toBe(['Example Remote Site'])
        ->and(array_keys($result->newInstruments))->toBe(['Example Refractor 102'])
        ->and($result->newEyepieces)->toBe([])
        ->and($result->newLenses)->toBe([])
        ->and($result->newFilters)->toBe([]);

    [$m31, $m13, $m42, $m45, $m81] = Observation::query()->orderBy('id')->get()->all();
    // 23:30 at +02:00 is 21:30 UT.
    expect($m31->observed_time)->toBe('21:30:00')
        ->and($m31->eyepiece_id)->toBe(Eyepiece::query()->value('id'))
        ->and((float) $m31->sqm)->toBe(20.85)
        ->and($m31->seeing)->toBe(2);
    // Written as "m13" and "M81".
    expect($m13->target_name)->toBe('M 13')
        ->and($m13->lens_id)->toBe(Lens::query()->value('id'));
    expect($m42->filter_id)->toBe(Filter::query()->value('id'))
        ->and($m42->language)->toBe('nl')
        ->and($m42->description)->toContain('zeer uitgebreid');
    expect($m45->instrument_id)->toBe(Instrument::query()->where('name', 'Naked eye')->value('id'));
    expect($m81->target_name)->toBe('M 81')
        ->and($m81->location_id)->toBe(Location::query()->where('name', 'Example Remote Site')->value('id'));
});

it('adds a target the oal file defines but the database does not have', function () {
    $result = runImportFixture($this->user, 'oal/valid-new-targets.xml');

    expect($result->newObjects)->toBe(['Example Nebula 123' => 'example-nebula-123']);

    $object = DB::table('objects')->where('name', 'Example Nebula 123')->first();
    expect($object->type)->toBe('BRTNB')
        ->and((float) $object->ra)->toEqualWithDelta(83.82 / 15, 1e-6)
        ->and((float) $object->decl)->toEqualWithDelta(-5.39, 1e-6)
        ->and($object->con)->toBe('ORI')
        // Diameters in arcseconds, the major axis first; subr in mag/arcmin².
        ->and((float) $object->diam1)->toBe(600.0)
        ->and((float) $object->diam2)->toBe(300.0)
        ->and((float) $object->mag)->toBe(8.5)
        ->and((float) $object->subr)->toEqualWithDelta(12.11, 0.01)
        ->and((int) $object->pa)->toBe(45)
        ->and($object->datasource)->toBe('OAL import by example-observer')
        ->and(DB::table('objectnames')->where('objectname', 'Example Nebula 123')->exists())->toBeTrue()
        ->and(DB::table('search_index')->where('name', 'Example Nebula 123')->exists())->toBeTrue();

    // Both observations of the new target point to the one object; the third
    // target is found through its alias M31.
    $observations = Observation::query()->orderBy('id')->get();
    expect($observations->pluck('target_key')->all())->toBe(['Example Nebula 123', 'Example Nebula 123', 'M 31'])
        ->and(DB::table('objects')->where('name', 'like', 'Example%')->count())->toBe(1);
});

it('adds no object for an observation that is skipped', function () {
    runImportFixture($this->user, 'oal/invalid-unknown-targets.xml');

    expect(DB::table('objects')->where('name', 'like', 'Example%')->exists())->toBeFalse()
        ->and(DB::table('objectnames')->where('objectname', 'like', 'Example%')->exists())->toBeFalse()
        ->and(DB::table('search_index')->where('name', 'like', 'Example%')->exists())->toBeFalse();
});

it('stores comets, moons and lunar features under their own table', function (string $file, array $expected) {
    runImportFixture($this->user, $file);

    $comet = (string) DB::table('cometobjects')->where('name', '12P/Pons-Brooks')->value('id');
    $targets = Observation::query()->orderBy('id')->get()
        ->map(fn (Observation $o) => [$o->target_table, $o->target_key, $o->target_type, $o->target_name])
        ->all();

    expect($targets)->toBe(array_map(
        fn (array $target) => $target[0] === 'cometobjects' ? ['cometobjects', $comet, 'comet', '12P/Pons-Brooks'] : $target,
        $expected,
    ));
})->with([
    'csv' => ['csv/valid-solar-system.csv', [
        ['cometobjects'],
        // A name only: "Moon" is the lunar feature, as on the observation form.
        ['lunar_features', 'moon', 'lunar', 'Moon'],
        ['lunar_features', 'copernicus', 'lunar', 'Copernicus'],
        ['lunar_features', 'tycho', 'lunar', 'Tycho'],
        ['moons', 'io', 'lunar', 'Io'],
        ['cometobjects'],
    ]],
    'oal' => ['oal/valid-solar-system.xml', [
        ['cometobjects'],
        // oal:MoonTargetType: the Moon of the moons table.
        ['moons', 'moon', 'lunar', 'Moon'],
        ['lunar_features', 'copernicus', 'lunar', 'Copernicus'],
        ['lunar_features', 'tycho', 'lunar', 'Tycho'],
        ['moons', 'io', 'lunar', 'Io'],
    ]],
    'csv, all types' => ['csv/valid-all-types.csv', $allTypes = [
        ['objects', 'M 31', 'deepsky', 'M 31'],
        ['cometobjects'],
        ['planets', 'sun', 'planet', 'Sun'],
        ['planets', 'jupiter', 'planet', 'Jupiter'],
        ['lunar_features', 'moon', 'lunar', 'Moon'],
        ['lunar_features', 'copernicus', 'lunar', 'Copernicus'],
        ['moons', 'io', 'lunar', 'Io'],
        ['asteroids', 'vesta', 'solarsystem', 'Vesta'],
    ]],
    'oal, all types' => ['oal/valid-all-types.xml', array_replace($allTypes, [4 => ['moons', 'moon', 'lunar', 'Moon']])],
]);

it('reads an oal file in latin-1', function () {
    runImportFixture($this->user, 'oal/valid-latin1.xml');

    expect(Observation::query()->value('description'))->toBe('Très belle galaxie, bras spiraux à peine visibles. Noyau étoilé.');
});

it('updates the observations instead of duplicating them when a valid file is imported again', function (string $file, int $count) {
    runImportFixture($this->user, $file);
    $again = runImportFixture($this->user, $file);

    expect($again->imported)->toBe($count)
        ->and(collect($again->importedObservations)->every(fn (array $row) => $row['updated']))->toBeTrue()
        ->and(Observation::query()->count())->toBe($count);
})->with([
    ['csv/valid-observations.csv', 6],
    ['oal/valid-observations.xml', 5],
    ['oal/valid-new-targets.xml', 3],
    ['csv/valid-solar-system.csv', 6],
    ['oal/valid-solar-system.xml', 5],
    ['csv/valid-all-types.csv', 8],
    ['oal/valid-all-types.xml', 8],
]);

it('imports the observations below the horizon with a warning when asked to', function (string $file) {
    $result = runImportFixture($this->user, $file, belowHorizon: true);

    expect($result->imported)->toBe(2)
        ->and($result->errors)->toBe(0)
        ->and(importMessages($result, 'warning'))->toHaveCount(2)
        ->and(importMessages($result, 'warning')[0])->toContain('Imported anyway.');
})->with(['csv/below-horizon.csv', 'oal/below-horizon.xml']);

it('accepts the example file as an upload', function (string $file) {
    $format = str_starts_with($file, 'csv/') ? 'csv' : 'oal';
    $upload = UploadedFile::fake()->createWithContent(basename($file), importFixture($file));

    $this->actingAs($this->user)
        ->post(route('observation.import.process'), ['format' => $format, 'import_file' => $upload])
        ->assertOk()
        ->assertViewIs('observations.import-result')
        ->assertSee('Import Results');
})->with(array_keys(importFixtureExpectations()));
