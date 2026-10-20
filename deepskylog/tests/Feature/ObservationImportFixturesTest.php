<?php

use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\Support\ObservationImportFixtures as Fixtures;

/*
 * Runs the example files of tests/Fixtures/ObservationImport (described in its
 * README.md) through the CSV and OAL importers.
 */

beforeEach(function () {
    Fixtures::seedTargets();
    $this->user = Fixtures::createObserver(['username' => 'example-observer']);
});

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
        'csv/valid-same-moment.csv' => ['csv/valid-same-moment.csv', 4, 0, [], []],
        'csv/invalid-solar-system.csv' => ['csv/invalid-solar-system.csv', 0, 4, [
            "Row 1: Object 'C/2099 Z1 (Example)' not found in the database",
            "Row 2: Object 'Example Crater' not found in the database",
            "Row 3: 'Moon' was 19.2° below the horizon of Example Site on 2026-09-25 at 06:00 UT.",
            "Row 4: '12P/Pons-Brooks' was 7.6° below the horizon of Example Site on 2024-03-20 at 23:30 UT.",
        ], []],
        'oal/valid-observations.xml' => ['oal/valid-observations.xml', 5, 0, [], []],
        'oal/valid-solar-system.xml' => ['oal/valid-solar-system.xml', 5, 0, [], []],
        'oal/valid-all-types.xml' => ['oal/valid-all-types.xml', 8, 0, [], []],
        'oal/valid-same-moment.xml' => ['oal/valid-same-moment.xml', 4, 0, [], []],
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
    $result = Fixtures::import($this->user, $file);

    expect($result->imported)->toBe($imported)
        ->and($result->skipped)->toBe($skipped)
        ->and(Observation::query()->where('observer_user_id', $this->user->id)->count())->toBe($imported);

    foreach ([[$errors, 'error'], [$warnings, 'warning']] as [$expected, $type]) {
        $messages = Fixtures::messages($result, $type);
        expect($messages)->toHaveCount(count($expected));
        foreach ($expected as $i => $part) {
            expect($messages[$i])->toContain($part);
        }
    }
})->with('import fixtures');

it('covers every example file', function () {
    $covered = collect(importFixtureExpectations())->keys()->sort()->values()->all();

    expect($covered)->toBe(Fixtures::files());
});

it('stores the fields of the valid csv file', function () {
    Fixtures::import($this->user, 'csv/valid-observations.csv');

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
    Fixtures::import($this->user, 'csv/valid-export-format.csv');

    $observation = Observation::query()->orderBy('id')->first();
    expect($observation->target_name)->toBe('M 31')
        ->and($observation->description)->toBe('Bright core; a "dust lane" with averted vision.');
});

it('stores the fields of the valid oal file and creates the missing site and scope', function () {
    $result = Fixtures::import($this->user, 'oal/valid-observations.xml');

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
        ->and($m81->location_id)->toBe(Location::query()->where('name', 'Example Remote Site')->value('id'))
        // <timezone>60</timezone> at 46° N, 7.5° E
        ->and(Location::query()->where('name', 'Example Remote Site')->value('timezone'))->toBe('Europe/Zurich');
});

it('adds a target the oal file defines but the database does not have', function () {
    $result = Fixtures::import($this->user, 'oal/valid-new-targets.xml');

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
    Fixtures::import($this->user, 'oal/invalid-unknown-targets.xml');

    expect(DB::table('objects')->where('name', 'like', 'Example%')->exists())->toBeFalse()
        ->and(DB::table('objectnames')->where('objectname', 'like', 'Example%')->exists())->toBeFalse()
        ->and(DB::table('search_index')->where('name', 'like', 'Example%')->exists())->toBeFalse();
});

it('stores comets, moons and lunar features under their own table', function (string $file, array $expected) {
    Fixtures::import($this->user, $file);

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
        ['moons', 'moon', 'lunar', 'Moon'],
        ['lunar_features', 'copernicus', 'lunar', 'Copernicus'],
        ['lunar_features', 'tycho', 'lunar', 'Tycho'],
        ['moons', 'io', 'lunar', 'Io'],
        ['cometobjects'],
    ]],
    'oal' => ['oal/valid-solar-system.xml', [
        ['cometobjects'],
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
        ['moons', 'moon', 'lunar', 'Moon'],
        ['lunar_features', 'copernicus', 'lunar', 'Copernicus'],
        ['moons', 'io', 'lunar', 'Io'],
        ['asteroids', 'vesta', 'solarsystem', 'Vesta'],
    ]],
    'oal, all types' => ['oal/valid-all-types.xml', $allTypes],
]);

it('reads an oal file in latin-1', function () {
    Fixtures::import($this->user, 'oal/valid-latin1.xml');

    expect(Observation::query()->value('description'))->toBe('Très belle galaxie, bras spiraux à peine visibles. Noyau étoilé.');
});

it('adds nothing when a file is imported again', function (string $file, bool $belowHorizon) {
    $first = Fixtures::import($this->user, $file, $belowHorizon);
    $count = Observation::query()->count();
    $objects = DB::table('objects')->count();
    $again = Fixtures::import($this->user, $file, $belowHorizon);

    expect($count)->toBe($first->imported)
        ->and($again->imported)->toBe($first->imported)
        ->and(collect($again->importedObservations)->every(fn (array $row) => $row['updated']))->toBeTrue()
        ->and(Observation::query()->count())->toBe($count)
        // A target the first import added is found the second time.
        ->and(DB::table('objects')->count())->toBe($objects);
})->with(fn () => collect(array_keys(importFixtureExpectations()))
    ->filter(fn (string $file) => str_contains($file, '/valid-'))
    ->map(fn (string $file) => [$file, false])
    ->merge([['csv/below-horizon.csv', true], ['oal/below-horizon.xml', true]])
    ->mapWithKeys(fn (array $case) => [$case[0].($case[1] ? ', below the horizon too' : '') => $case])
    ->all());

it('keeps observations of the same moment with another instrument apart', function (string $file) {
    Fixtures::import($this->user, $file);

    $instruments = Observation::query()->orderBy('id')->get()
        ->map(fn (Observation $o) => Instrument::query()->whereKey($o->instrument_id)->value('name'))
        ->all();
    expect($instruments)->toBe(['Example Dobson 250', 'Naked eye', 'Example Dobson 250', 'Naked eye']);
})->with(['csv/valid-same-moment.csv', 'oal/valid-same-moment.xml']);

it('gives an observation without an instrument the instrument of the file', function (string $file) {
    $owner = ['observer_username' => $this->user->username, 'observer_user_id' => $this->user->id, 'language' => 'en',
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'M 31', 'target_name' => 'M 31',
        'has_drawing' => false, 'observed_on' => '2026-09-12', 'observed_time' => '21:30:00'];
    $withoutInstrument = Observation::query()->create($owner + ['description' => 'Migrated, without an instrument.']);
    $binoculars = Instrument::query()->create([
        'user_id' => $this->user->id, 'observer' => $this->user->username, 'active' => true, 'name' => 'Example Binoculars',
        'make_id' => DB::table('instrument_makes')->value('id'), 'aperture_mm' => 50, 'fixedMagnification' => 10,
        'instrument_type_id' => 2, 'mount_type_id' => DB::table('mount_types')->value('id'),
    ]);
    $otherInstrument = Observation::query()->create($owner + ['instrument_id' => $binoculars->id, 'description' => 'Through binoculars.']);

    Fixtures::import($this->user, $file);

    // The file has M 31 at that moment through the Dobson and with the naked
    // eye: the first fills in the observation without an instrument, the
    // second is new, and the one through binoculars is left alone.
    expect($withoutInstrument->fresh()->instrument_id)->toBe(Instrument::query()->where('user_id', $this->user->id)->where('name', 'Example Dobson 250')->value('id'))
        ->and($otherInstrument->fresh()->description)->toBe('Through binoculars.')
        ->and(Observation::query()->where('observed_on', '2026-09-12')->count())->toBe(3);
})->with(['csv/valid-same-moment.csv', 'oal/valid-same-moment.xml']);

it('adds nothing when the same observations come from a csv and an oal file', function (string $first, string $second) {
    Fixtures::import($this->user, $first);
    $again = Fixtures::import($this->user, $second);

    // The two files hold the same eight observations, with the times in UT
    // (CSV) and in UT or local time with an offset (OAL).
    expect($again->imported)->toBe(8)
        ->and(collect($again->importedObservations)->every(fn (array $row) => $row['updated']))->toBeTrue()
        ->and(Observation::query()->count())->toBe(8);
})->with([
    'csv, then oal' => ['csv/valid-all-types.csv', 'oal/valid-all-types.xml'],
    'oal, then csv' => ['oal/valid-all-types.xml', 'csv/valid-all-types.csv'],
]);

it('imports the observations below the horizon with a warning when asked to', function (string $file) {
    $result = Fixtures::import($this->user, $file, belowHorizon: true);

    expect($result->imported)->toBe(2)
        ->and($result->errors)->toBe(0)
        ->and(Fixtures::messages($result, 'warning'))->toHaveCount(2)
        ->and(Fixtures::messages($result, 'warning')[0])->toContain('Imported anyway.');
})->with(['csv/below-horizon.csv', 'oal/below-horizon.xml']);

it('accepts the example file as an upload', function (string $file) {
    $format = str_starts_with($file, 'csv/') ? 'csv' : 'oal';
    $upload = UploadedFile::fake()->createWithContent(basename($file), Fixtures::content($file));

    $this->actingAs($this->user)
        ->post(route('observation.import.process'), ['format' => $format, 'import_file' => $upload])
        ->assertOk()
        ->assertViewIs('observations.import-result')
        ->assertSee('Import Results');
})->with(array_keys(importFixtureExpectations()));
