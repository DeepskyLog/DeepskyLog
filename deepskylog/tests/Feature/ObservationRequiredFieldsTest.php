<?php

use App\Models\Instrument;
use App\Models\Observation;
use App\Models\User;
use App\Services\Import\CsvObservationImporter;
use App\Services\Import\OalObservationImporter;
use App\Support\Html;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\DB;

function requiredFieldsM65(): void
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

function requiredFieldsOal(string $scopes, string $scopeRef, string $description): string
{
    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<oal:observations xmlns:oal="http://groups.google.com/group/openastronomylog" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="2.1" xsi:schemaLocation="http://groups.google.com/group/openastronomylog oal21.xsd">
  <observers><observer id="o1"><name>A</name><surname>B</surname></observer></observers>
  <sites/><sessions/>
  <targets><target id="t1" xsi:type="oal:deepSkyGX"><datasource>DeepskyLog</datasource><name>M 65</name></target></targets>
  <scopes>{$scopes}</scopes><eyepieces/><lenses/><filters/><imagers/>
  <observation id="obs1"><observer>o1</observer><target>t1</target><begin>2026-03-01T22:30:00+01:00</begin>{$scopeRef}<result lang="en"><description>{$description}</description></result></observation>
</oal:observations>
XML;
}

beforeEach(function () {
    requiredFieldsM65();
    $this->user = User::factory()->create();
    $this->instrument = InstrumentFactory::new()->create(['observer' => '', 'user_id' => $this->user->id, 'name' => 'Test Scope']);
});

it('sees no text in empty editor markup', function () {
    expect(Html::isBlank(null))->toBeTrue()
        ->and(Html::isBlank(''))->toBeTrue()
        ->and(Html::isBlank('<p>&nbsp;</p>'))->toBeTrue()
        ->and(Html::isBlank('&lt;p&gt; &lt;/p&gt;'))->toBeTrue()
        ->and(Html::isBlank('<p>Faint glow</p>'))->toBeFalse();
});

it('requires an instrument and a description on the observation form', function () {
    $payload = ['target_table' => 'objects', 'target_slug' => 'm-65', 'observed_on' => '2026-03-01'];

    $this->actingAs($this->user)->post(route('observation.store'), $payload + ['description' => '<p>&nbsp;</p>'])
        ->assertSessionHasErrors(['instrument_id', 'description']);
    expect(Observation::count())->toBe(0);

    $this->actingAs($this->user)->post(route('observation.store'), $payload + [
        'instrument_id' => $this->instrument->id, 'description' => '<p>Faint glow</p>',
    ])->assertSessionHasNoErrors();
    expect(Observation::count())->toBe(1);
});

it('skips csv rows without an instrument or a description', function () {
    $result = (new CsvObservationImporter($this->user))->import(
        "M 65;A;01-03-2026;22:00;;;;;;;;;;en;Faint glow\n"
        . "M 65;A;02-03-2026;22:00;;Test Scope;;;;;;;;en;\n"
        . "M 65;A;03-03-2026;22:00;;Test Scope;;;;;;;;en;Faint glow\n"
    );

    $errors = collect($result->log)->where('type', 'error')->pluck('message')->implode(' ');
    expect($result->imported)->toBe(1)
        ->and($result->skipped)->toBe(2)
        ->and($errors)->toContain('Row 1: Instrument is empty')->toContain('Row 2: Description is empty')
        ->and(Observation::value('instrument_id'))->toBe($this->instrument->id);
});

it('imports an oal observation without a scope with the naked-eye instrument', function () {
    $result = app()->call(fn () => new OalObservationImporter($this->user))->import(requiredFieldsOal('', '', 'Faint glow'));

    $nakedEye = Instrument::where('user_id', $this->user->id)->where('instrument_type_id', 1)->first();
    expect($result->imported)->toBe(1)
        ->and($nakedEye->name)->toBe('Naked eye')
        ->and(Observation::value('instrument_id'))->toBe($nakedEye->id);

    // A second import uses the same instrument.
    app()->call(fn () => new OalObservationImporter($this->user))->import(requiredFieldsOal('', '', 'Faint glow again'));
    expect(Instrument::where('user_id', $this->user->id)->where('instrument_type_id', 1)->count())->toBe(1);
});

it('skips an oal observation without a description before creating its equipment', function () {
    $scopes = '<scope id="sc1" xsi:type="oal:scopeType"><model>Big Dob</model><aperture>400</aperture><focalLength>1800</focalLength></scope>';

    $result = app()->call(fn () => new OalObservationImporter($this->user))
        ->import(requiredFieldsOal($scopes, '<scope>sc1</scope>', ''));

    expect($result->imported)->toBe(0)->and($result->skipped)->toBe(1)
        ->and(collect($result->log)->where('type', 'error')->pluck('message')->implode(' '))->toContain('Description is empty')
        ->and(Instrument::where('user_id', $this->user->id)->count())->toBe(1);
});
