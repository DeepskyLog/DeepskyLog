<?php

use App\Http\Livewire\MagnificationPicker;
use App\Models\Eyepiece;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use App\Services\Import\CsvObservationImporter;
use App\Services\Import\OalObservationImporter;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function editFixesM65(): void
{
    DB::table('objects')->insert([
        'name' => 'M 65', 'slug' => 'm-65', 'type' => 'GALXY', 'con' => 'LEO', 'ra' => 11.3155, 'decl' => 13.0922,
        'mag' => 9.3, 'subr' => 12.4, 'pa' => 174, 'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '',
        'diam1' => 600.0, 'diam2' => 180.0, 'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '',
        'psa' => '', 'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0', 'DeepskyHunter' => '0',
        'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function editFixesLocation(User $owner, string $timezone = 'Europe/Brussels'): Location
{
    return Location::query()->create([
        'user_id' => $owner->id, 'name' => 'Edit Fixes Site', 'longitude' => 4.5, 'latitude' => 50.5,
        'timezone' => $timezone, 'limitingMagnitude' => -999, 'skyBackground' => -999, 'elevation' => 120,
        'country' => 'Belgium', 'active' => true, 'observer' => '', 'observations' => 0, 'slug' => 'edit-fixes-site',
    ]);
}

function editFixesInstrument(User $owner, array $extra = []): Instrument
{
    return Instrument::query()->create(array_merge([
        'user_id' => $owner->id, 'make_id' => DB::table('instrument_makes')->value('id'), 'name' => 'Edit Fixes Scope',
        'aperture_mm' => 250, 'instrument_type_id' => DB::table('instrument_types')->value('id'),
        'mount_type_id' => DB::table('mount_types')->value('id'), 'focal_length_mm' => 1200, 'active' => true, 'observer' => '',
    ], $extra));
}

function editFixesEyepiece(User $owner): Eyepiece
{
    return Eyepiece::query()->create([
        'user_id' => $owner->id, 'make_id' => DB::table('eyepiece_makes')->value('id'),
        'type_id' => DB::table('eyepiece_types')->value('id'), 'name' => 'Edit Fixes Eyepiece', 'focal_length_mm' => 10,
        'apparentFOV' => 82, 'active' => true, 'observer' => '', 'observations' => 0, 'slug' => 'edit-fixes-eyepiece',
    ]);
}

function editFixesObservation(User $observer, array $extra = []): Observation
{
    return Observation::query()->create(array_merge([
        'observer_username' => $observer->username, 'observer_user_id' => $observer->id, 'observed_on' => '2026-03-01',
        'language' => 'en', 'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'M 65',
        'target_name' => 'M 65', 'has_drawing' => false, 'description' => 'Seen.',
    ], $extra));
}

function editFixesCsvRow(string $date, string $time, string $description): string
{
    return "M 65;Observer;{$date};{$time};;Edit Fixes Scope;;;;3;;;;en;{$description}\n";
}

it('updates the legacy date and time when a migrated observation is edited', function () {
    $user = User::factory()->create(['username' => 'legacyedit', 'slug' => 'legacy-edit']);
    editFixesM65();
    $observation = editFixesObservation($user, [
        'observed_on' => '2010-02-03', 'observed_time' => '21:30:00', 'legacy_date_int' => 20100203,
        'legacy_time_int' => 2130, 'legacy_source' => 'deepsky', 'legacy_id' => 42,
    ]);

    $this->actingAs($user)->post(route('observation.store'), [
        'instrument_id' => InstrumentFactory::new()->create(['observer' => '', 'user_id' => $user->id])->id,
        'description' => 'Seen.',
        'edit_unified_id' => $observation->id, 'target_table' => 'objects', 'target_slug' => 'm-65',
        'observed_on' => '2010-02-05', 'observed_time' => '22:45',
    ])->assertSessionHasNoErrors();

    $observation->refresh();
    expect($observation->legacy_date_int)->toBe(20100205)
        ->and($observation->legacy_time_int)->toBe(2245)
        ->and($observation->date)->toBe('20100205')
        ->and($observation->time)->toBe(2245);

    $this->actingAs($user)->post(route('observation.store'), [
        'instrument_id' => InstrumentFactory::new()->create(['observer' => '', 'user_id' => $user->id])->id,
        'description' => 'Seen.',
        'edit_unified_id' => $observation->id, 'target_table' => 'objects', 'target_slug' => 'm-65',
        'observed_on' => '2010-02-06',
    ])->assertSessionHasNoErrors();

    $observation->refresh();
    expect($observation->legacy_date_int)->toBe(20100206)
        ->and($observation->legacy_time_int)->toBe(-9999)
        ->and($observation->time)->toBeNull();
});

it('does not touch the legacy columns of observations made in this version', function () {
    $user = User::factory()->create(['username' => 'newedit', 'slug' => 'new-edit']);
    editFixesM65();
    $observation = editFixesObservation($user, ['observed_time' => '21:30:00']);

    $this->actingAs($user)->post(route('observation.store'), [
        'instrument_id' => InstrumentFactory::new()->create(['observer' => '', 'user_id' => $user->id])->id,
        'description' => 'Seen.',
        'edit_unified_id' => $observation->id, 'target_table' => 'objects', 'target_slug' => 'm-65',
        'observed_on' => '2026-03-02', 'observed_time' => '22:00',
    ])->assertSessionHasNoErrors();

    $observation->refresh();
    expect($observation->legacy_date_int)->toBeNull()
        ->and($observation->legacy_time_int)->toBeNull();
});

it('only lets a csv row without a time replace an observation without a time', function () {
    $user = User::factory()->create(['username' => 'csvdup']);
    editFixesM65();
    editFixesInstrument($user);
    $timed = editFixesObservation($user, ['observed_time' => '21:30:00', 'description' => 'timed']);

    $result = (new CsvObservationImporter($user))->import(editFixesCsvRow('01-03-2026', '', 'no time'));

    expect($result->imported)->toBe(1)
        ->and($timed->fresh()->description)->toBe('timed')
        ->and(Observation::where('observer_user_id', $user->id)->count())->toBe(2);

    // Importing it again updates the observation without a time.
    (new CsvObservationImporter($user))->import(editFixesCsvRow('01-03-2026', '', 'no time again'));
    expect(Observation::where('observer_user_id', $user->id)->count())->toBe(2)
        ->and(Observation::whereNull('observed_time')->value('description'))->toBe('no time again');
});

it('keeps the drawing and does not duplicate migrated observations when re-importing', function () {
    $user = User::factory()->create(['username' => 'csvlegacy']);
    editFixesM65();
    editFixesInstrument($user);
    $legacy = editFixesObservation($user, [
        'observed_time' => '21:30:00', 'legacy_date_int' => 20260301, 'legacy_time_int' => 2130,
        'legacy_source' => 'deepsky', 'legacy_id' => 7, 'has_drawing' => true, 'description' => 'old',
    ]);
    $legacyWithoutTime = editFixesObservation($user, [
        'observed_on' => '2026-03-02', 'observed_time' => '23:59:00', 'legacy_date_int' => 20260302,
        'legacy_time_int' => -9999, 'legacy_source' => 'deepsky', 'legacy_id' => 8, 'description' => 'old',
    ]);

    $result = (new CsvObservationImporter($user))->import(
        editFixesCsvRow('01-03-2026', '21:30', 'new') . editFixesCsvRow('02-03-2026', '', 'new')
    );

    expect($result->imported)->toBe(2)
        ->and(Observation::where('observer_user_id', $user->id)->count())->toBe(2);

    $legacy->refresh();
    expect($legacy->description)->toBe('new')
        ->and($legacy->has_drawing)->toBeTrue()
        ->and($legacy->legacy_source)->toBe('deepsky');

    $legacyWithoutTime->refresh();
    expect($legacyWithoutTime->description)->toBe('new')
        ->and($legacyWithoutTime->time)->toBeNull();
});

it('keeps the transparency when an oal file updates an observation', function () {
    $user = User::factory()->create(['username' => 'oaltransp']);
    editFixesM65();
    $observation = editFixesObservation($user, ['observed_time' => '21:30:00', 'transparency' => 2, 'has_drawing' => true]);

    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<oal:observations xmlns:oal="http://groups.google.com/group/openastronomylog" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="2.1" xsi:schemaLocation="http://groups.google.com/group/openastronomylog oal21.xsd">
  <observers><observer id="o1"><name>A</name><surname>B</surname></observer></observers>
  <sites/><sessions/>
  <targets><target id="t1" xsi:type="oal:deepSkyGX"><datasource>DeepskyLog</datasource><name>M 65</name></target></targets>
  <scopes/><eyepieces/><lenses/><filters/><imagers/>
  <observation id="obs1"><observer>o1</observer><target>t1</target><begin>2026-03-01T22:30:00+01:00</begin><result lang="en"><description>from oal</description></result></observation>
</oal:observations>
XML;

    $result = app()->call(fn() => new OalObservationImporter($user))->import($xml);

    expect($result->errors)->toBe(0)
        ->and(Observation::where('observer_user_id', $user->id)->count())->toBe(1);
    $observation->refresh();
    expect($observation->description)->toBe('from oal')
        ->and($observation->transparency)->toBe(2)
        ->and($observation->has_drawing)->toBeTrue();
});

it('writes the oal begin in local time with the right offset and reads it back in UT', function () {
    $user = User::factory()->create(['username' => 'oaltz', 'slug' => 'oal-tz']);
    $importer = User::factory()->create(['username' => 'oaltzimport']);
    editFixesM65();
    $location = editFixesLocation($user);
    editFixesObservation($user, ['observed_on' => '2026-07-01', 'observed_time' => '21:15:30', 'location_id' => $location->id]);
    editFixesObservation($user, ['observed_on' => '2026-07-02', 'observed_time' => '23:59:00', 'legacy_time_int' => -9999, 'location_id' => $location->id]);

    $this->actingAs($user);
    $content = $this->get(route('observations.search.oal', ['q' => '%']))->assertOk()->getContent();

    // 21:15:30 UT is 23:15:30 in Brussels in summer; an observation without a
    // time is written as midnight UT on its date.
    expect($content)->toContain('<begin>2026-07-01T23:15:30+02:00</begin>')
        ->and($content)->toContain('<begin>2026-07-02T00:00:00+00:00</begin>');

    // Re-importing into the same account matches both observations.
    $again = app()->call(fn() => new OalObservationImporter($user))->import($content);
    expect($again->errors)->toBe(0)
        ->and(Observation::where('observer_user_id', $user->id)->count())->toBe(2);

    app()->call(fn() => new OalObservationImporter($importer))->import($content);
    $imported = Observation::where('observer_user_id', $importer->id)->whereDate('observed_on', '2026-07-01')->first();
    expect($imported->observed_time)->toBe('21:15:30');
});

it('exports an observation without a time with an empty time in the csv', function () {
    $user = User::factory()->create(['name' => 'Csv Observer', 'username' => 'csvnotime']);
    editFixesM65();
    editFixesObservation($user, ['observed_time' => '23:59:00', 'legacy_time_int' => -9999]);

    Livewire::test(\App\Livewire\ObservationSearchTable::class, ['objectName' => 'M 65'])
        ->call('exportToCsv')
        ->assertFileDownloaded('export.csv', "\xEF\xBB\xBF\"M 65\";\"Csv Observer\";01-03-2026;;;;;;;;0;0;;en;Seen.;;\n");
});

it('keeps the stored magnification in the picker and computes it otherwise', function () {
    $user = User::factory()->create();
    $instrument = editFixesInstrument($user);
    $eyepiece = editFixesEyepiece($user);

    Livewire::test(MagnificationPicker::class, ['instrument_id' => $instrument->id, 'eyepiece_id' => $eyepiece->id, 'manual_magnification' => '150'])
        ->assertSet('manual_magnification', '150')
        ->assertSet('computed_magnification', 120);

    Livewire::test(MagnificationPicker::class, ['instrument_id' => $instrument->id, 'eyepiece_id' => $eyepiece->id])
        ->assertSet('manual_magnification', '120');
});

it('uses the fixed magnification of an instrument', function () {
    $user = User::factory()->create();
    $binoculars = editFixesInstrument($user, ['name' => 'Binoculars', 'fixedMagnification' => 15, 'focal_length_mm' => null]);
    $eyepiece = editFixesEyepiece($user);
    $lens = Lens::query()->create([
        'user_id' => $user->id, 'make_id' => DB::table('lens_makes')->value('id'), 'name' => 'Barlow', 'factor' => 2.0,
        'active' => true, 'observer' => '', 'observations' => 0, 'slug' => 'edit-fixes-barlow',
    ]);

    expect($binoculars->magnificationFor(null))->toBe(15)
        ->and($binoculars->magnificationFor($eyepiece))->toBe(15)
        ->and($binoculars->magnificationFor(null, $lens))->toBe(30)
        ->and(editFixesInstrument($user, ['name' => 'No eyepiece'])->magnificationFor(null))->toBeNull();

    Livewire::test(MagnificationPicker::class, ['instrument_id' => $binoculars->id])
        ->assertSet('manual_magnification', '15');

    $this->actingAs($user)->getJson('/api/magnification?instrument_id=' . $binoculars->id)
        ->assertJson(['magnification' => 15]);
});

it('stores the magnification with the observation and prefills it when editing', function () {
    $user = User::factory()->create(['username' => 'maguser', 'slug' => 'mag-user']);
    editFixesM65();

    $this->actingAs($user)->post(route('observation.store'), [
        'instrument_id' => InstrumentFactory::new()->create(['observer' => '', 'user_id' => $user->id])->id,
        'description' => 'Seen.',
        'target_table' => 'objects', 'target_slug' => 'm-65', 'observed_on' => '2026-03-01', 'observed_time' => '21:00',
        'magnification' => '150x',
    ])->assertSessionHasNoErrors();

    $observation = Observation::latest('id')->first();
    expect(DB::table('deepsky_observation_details')->where('observation_id', $observation->id)->value('magnification'))->toBe('150')
        ->and($observation->magnification)->toBe('150');

    $this->actingAs($user)->get('/observation/mag-user/2026-03-01/m-65/1/edit')->assertRedirect();
    expect(session()->getOldInput('magnification'))->toBe('150');
});
