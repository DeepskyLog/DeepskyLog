<?php

use App\Models\Observation;
use App\Models\User;
use App\Services\Import\CsvObservationImporter;
use App\Services\Import\OalObservationImporter;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;

function insertHorizonTestObject(string $name, string $slug, float $ra, float $decl): void
{
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => $slug,
        'type' => 'GALXY',
        'con' => 'OCT',
        'ra' => $ra,
        'decl' => $decl,
        'mag' => 9.3,
        'subr' => 12.4,
        'pa' => 0,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 60.0,
        'diam2' => 60.0,
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
}

function horizonTestOal(string $target, string $begin): string
{
    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<oal:observations xmlns:oal="http://groups.google.com/group/openastronomylog" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="2.1" xsi:schemaLocation="http://groups.google.com/group/openastronomylog oal21.xsd">
  <observers><observer id="o1"><name>A</name><surname>B</surname></observer></observers>
  <sites><site id="s1"><name>Leuven</name><longitude unit="deg">4.70</longitude><latitude unit="deg">50.88</latitude><timezone>60</timezone></site></sites>
  <sessions/>
  <targets><target id="t1" xsi:type="oal:deepSkyGX"><datasource>DeepskyLog</datasource><name>{$target}</name></target></targets>
  <scopes/><eyepieces/><lenses/><filters/><imagers/>
  <observation id="obs1"><observer>o1</observer><site>s1</site><target>t1</target><begin>{$begin}</begin><result lang="en"><description>from oal</description></result></observation>
</oal:observations>
XML;
}

beforeEach(function () {
    // Never above the horizon of Leuven, and always above it.
    insertHorizonTestObject('Southern Test Galaxy', 'southern-test-galaxy', 6.0, -80.0);
    insertHorizonTestObject('Northern Test Galaxy', 'northern-test-galaxy', 6.0, 85.0);
    $this->user = User::factory()->create();
    $this->location = LocationFactory::new()->create([
        'user_id' => $this->user->id,
        'observer' => $this->user->username,
        'name' => 'Leuven',
        'latitude' => 50.88,
        'longitude' => 4.70,
        'timezone' => 'Europe/Brussels',
    ]);
});

it('refuses an observation of an object below the horizon at the time given', function () {
    $this->actingAs($this->user)->post(route('observation.store'), [
        'target_table' => 'objects',
        'target_slug' => 'southern-test-galaxy',
        'observed_on' => '2026-03-01',
        'observed_time' => '22:00',
        'location_id' => $this->location->id,
    ])->assertSessionHasErrors('below_horizon');

    expect(Observation::count())->toBe(0);
});

it('refuses an observation without a time of an object that never rises there', function () {
    $response = $this->actingAs($this->user)->post(route('observation.store'), [
        'target_table' => 'objects',
        'target_slug' => 'southern-test-galaxy',
        'observed_on' => '2026-03-01',
        'location_id' => $this->location->id,
    ]);

    $response->assertSessionHasErrors('below_horizon');
    expect(session('errors')->first('below_horizon'))->toContain('does not rise above the horizon of Leuven')
        ->and(Observation::count())->toBe(0);
});

it('saves an observation below the horizon when the observer confirms it', function () {
    $this->actingAs($this->user)->post(route('observation.store'), [
        'target_table' => 'objects',
        'target_slug' => 'southern-test-galaxy',
        'observed_on' => '2026-03-01',
        'observed_time' => '22:00',
        'location_id' => $this->location->id,
        'confirm_below_horizon' => '1',
    ])->assertSessionHasNoErrors();

    expect(Observation::count())->toBe(1);
});

it('saves an observation of an object above the horizon', function () {
    $this->actingAs($this->user)->post(route('observation.store'), [
        'target_table' => 'objects',
        'target_slug' => 'northern-test-galaxy',
        'observed_on' => '2026-03-01',
        'observed_time' => '22:00',
        'location_id' => $this->location->id,
    ])->assertSessionHasNoErrors();

    expect(Observation::count())->toBe(1);
});

it('does not check an observation without a location', function () {
    $this->actingAs($this->user)->post(route('observation.store'), [
        'target_table' => 'objects',
        'target_slug' => 'southern-test-galaxy',
        'observed_on' => '2026-03-01',
        'observed_time' => '22:00',
    ])->assertSessionHasNoErrors();

    expect(Observation::count())->toBe(1);
});

it('skips a csv row of an object below the horizon, and imports it when asked', function () {
    $row = "Southern Test Galaxy;A;01-03-2026;22:00;Leuven;;;;;;;;;en;faint\n";

    $result = (new CsvObservationImporter($this->user))->import($row);
    expect($result->imported)->toBe(0)
        ->and($result->skipped)->toBe(1)
        ->and(collect($result->log)->where('type', 'error')->pluck('message')->implode(' '))
        ->toContain("'Southern Test Galaxy' was")->toContain('below the horizon of Leuven on 2026-03-01 at 22:00 UT')
        ->and(Observation::count())->toBe(0);

    $result = (new CsvObservationImporter($this->user))->importBelowHorizon()->import($row);
    expect($result->imported)->toBe(1)
        ->and($result->errors)->toBe(0)
        ->and(collect($result->log)->where('type', 'warning')->pluck('message')->implode(' '))->toContain('Imported anyway')
        ->and(Observation::count())->toBe(1);
});

it('imports a csv row of an object above the horizon', function () {
    $result = (new CsvObservationImporter($this->user))->import("Northern Test Galaxy;A;01-03-2026;22:00;Leuven;;;;;;;;;en;bright\n");

    expect($result->imported)->toBe(1)->and($result->errors)->toBe(0);
});

it('skips an oal observation of an object below the horizon', function () {
    $result = app()->call(fn () => new OalObservationImporter($this->user))
        ->import(horizonTestOal('Southern Test Galaxy', '2026-03-01T22:00:00+01:00'));

    expect($result->imported)->toBe(0)
        ->and($result->skipped)->toBe(1)
        ->and(Observation::count())->toBe(0);
});

it('checks an oal observation at midnight UT as one without a time', function () {
    // At midnight UT the galaxy is below the horizon, but it rises that day.
    insertHorizonTestObject('Evening Test Galaxy', 'evening-test-galaxy', 12.0, 0.0);

    $result = app()->call(fn () => new OalObservationImporter($this->user))
        ->import(horizonTestOal('Evening Test Galaxy', '2026-09-01T00:00:00Z'));

    expect($result->imported)->toBe(1)->and($result->errors)->toBe(0);
});

it('passes the import below the horizon option from the import form', function () {
    $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('obs.csv', "Southern Test Galaxy;A;01-03-2026;22:00;Leuven;;;;;;;;;en;faint\n");

    $this->actingAs($this->user)->post(route('observation.import.process'), [
        'format' => 'csv',
        'import_file' => $file,
        'import_below_horizon' => '1',
    ])->assertOk();

    expect(Observation::count())->toBe(1);
});

it('shows the altitude of the object at the time of the observation on its detail page', function () {
    Observation::create([
        'observer_username' => $this->user->username, 'observer_user_id' => $this->user->id,
        'observed_on' => '2026-03-01', 'observed_time' => '22:00:00', 'location_id' => $this->location->id,
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'Northern Test Galaxy', 'target_name' => 'Northern Test Galaxy',
    ]);

    // At declination 85° the galaxy stands between 45.9° and 55.9° above Leuven.
    $this->get(route('observations.detail', ['observer' => $this->user->slug, 'date' => '2026-03-01', 'object' => 'northern-test-galaxy']))
        ->assertOk()
        ->assertViewHas('objectAltitude', fn ($a) => $a['at_time'] === true && $a['degrees'] > 45.8 && $a['degrees'] < 56.0)
        ->assertSee('Altitude of the object during observation');
});

it('shows the highest altitude of the object on the date of an observation without a time', function () {
    Observation::create([
        'observer_username' => $this->user->username, 'observer_user_id' => $this->user->id,
        'observed_on' => '2026-03-01', 'location_id' => $this->location->id,
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'Northern Test Galaxy', 'target_name' => 'Northern Test Galaxy',
    ]);

    $this->get(route('observations.detail', ['observer' => $this->user->slug, 'date' => '2026-03-01', 'object' => 'northern-test-galaxy']))
        ->assertOk()
        ->assertViewHas('objectAltitude', ['degrees' => 55.9, 'at_time' => false])
        ->assertSee('Highest altitude of the object on this date')
        ->assertSee('55.9');
});

it('shows no altitude of the object for an observation without a location', function () {
    Observation::create([
        'observer_username' => $this->user->username, 'observer_user_id' => $this->user->id,
        'observed_on' => '2026-03-01', 'observed_time' => '22:00:00',
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'Northern Test Galaxy', 'target_name' => 'Northern Test Galaxy',
    ]);

    $this->get(route('observations.detail', ['observer' => $this->user->slug, 'date' => '2026-03-01', 'object' => 'northern-test-galaxy']))
        ->assertOk()
        ->assertViewHas('objectAltitude', null)
        ->assertDontSee('Altitude of the object during observation');
});
