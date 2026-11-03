<?php

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\Observation;
use App\Models\User;
use App\Models\VariableStarEstimate;
use App\Services\Observations\AavsoExport;
use Carbon\CarbonImmutable;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

// Magnitude estimates of variable stars, novae and supernovae on the
// observation form, exported in the AAVSO Visual File Format.

function variableStarObject(string $name, string $type): void
{
    DB::table('objects')->insert([
        'name' => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'CYG', 'ra' => 21.7, 'decl' => 43.6,
        'mag' => 8.0, 'subr' => 99.9, 'pa' => 0, 'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '',
        'diam1' => 0, 'diam2' => 0, 'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '',
        'psa' => '', 'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0', 'DeepskyHunter' => '0',
        'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function variableStarPost(User $user, string $slug, array $extra = []): array
{
    return array_merge([
        'target_table' => 'objects',
        'target_slug' => $slug,
        'observed_on' => '2026-09-01',
        'observed_time' => '21:30',
        'instrument_id' => InstrumentFactory::new()->create(['observer' => '', 'user_id' => $user->id])->id,
        'description' => '<p>Between 110 and 113.</p>',
        'language' => 'en',
    ], $extra);
}

beforeEach(function () {
    Queue::fake();
    variableStarObject('SS Cyg', 'AA1STAR');
    variableStarObject('NGC 7000', 'EMINB');
});

it('writes an estimate in the aavso visual file format', function () {
    $line = AavsoExport::line([
        'star' => 'SS Cyg',
        'jd' => AavsoExport::julianDay(CarbonImmutable::parse('2008-08-20 14:57:42', 'UTC')),
        'magnitude' => 11.06,
        'fainter' => true,
        'comment_codes' => 'BZ',
        'comparison1' => '110',
        'comparison2' => null,
        'chart' => '070613',
    ]);

    expect($line)->toBe('SS Cyg,2454699.1234,<11.1,BZ,110,na,070613,na')
        ->and(VariableStarEstimate::normalizeCommentCodes(['z', 'B', 'Q', 'B']))->toBe('BZ');
});

it('stores the estimate of a variable star with the observation and prefills it when editing', function () {
    $user = User::factory()->create(['username' => 'varobs', 'slug' => 'varobs']);

    $this->actingAs($user)->get(route('observation.create', ['slug' => 'ss-cyg']))
        ->assertOk()
        ->assertSee('variable_magnitude', false);
    $this->actingAs($user)->get(route('observation.create', ['slug' => 'ngc-7000']))
        ->assertOk()
        ->assertDontSee('variable_magnitude', false);

    $this->actingAs($user)->post(route('observation.store'), variableStarPost($user, 'ss-cyg', [
        'variable_magnitude' => '11.2',
        'variable_comparison1' => '110',
        'variable_comparison2' => ' 113 ',
        'variable_chart' => 'x12345abc',
        'variable_comment_codes' => ['Z', 'B'],
    ]))->assertSessionHasNoErrors();

    $observation = Observation::sole();
    expect(VariableStarEstimate::find($observation->id)->only(['magnitude', 'fainter', 'comparison1', 'comparison2', 'chart', 'comment_codes']))
        ->toBe(['magnitude' => 11.2, 'fainter' => false, 'comparison1' => '110', 'comparison2' => '113', 'chart' => 'X12345ABC', 'comment_codes' => 'BZ']);

    $this->actingAs($user)->followingRedirects()->get('/observation/varobs/2026-09-01/ss-cyg/1')
        ->assertOk()
        ->assertSee('Magnitude estimate')
        ->assertSee('110, 113')
        ->assertSee(route('observations.search.aavso'));

    $this->actingAs($user)->get('/observation/varobs/2026-09-01/ss-cyg/1/edit')
        ->assertSessionHasInput('variable_magnitude', 11.2)
        ->assertSessionHasInput('variable_comment_codes', ['B', 'Z']);

    // Without a magnitude the estimate goes
    $this->actingAs($user)->post(route('observation.store'), variableStarPost($user, 'ss-cyg', [
        'edit_unified_id' => $observation->id,
    ]))->assertSessionHasNoErrors();

    expect(VariableStarEstimate::count())->toBe(0);
});

it('stores no estimate for an object that is no variable star', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('observation.store'), variableStarPost($user, 'ngc-7000', [
        'variable_magnitude' => '5.0',
    ]))->assertSessionHasNoErrors();

    expect(VariableStarEstimate::count())->toBe(0);
});

it('exports the own estimates of the search for the aavso', function () {
    $user = User::factory()->create(['username' => 'varobs', 'slug' => 'varobs', 'aavso_code' => 'VOB']);
    $other = User::factory()->create(['username' => 'other', 'slug' => 'other', 'aavso_code' => 'OTH']);

    $this->actingAs($user)->post(route('observation.store'), variableStarPost($user, 'ss-cyg', [
        'variable_magnitude' => '11.2',
        'variable_comparison1' => '110',
        'variable_comparison2' => '113',
        'variable_comment_codes' => ['Z'],
    ]))->assertSessionHasNoErrors();
    $this->actingAs($user)->post(route('observation.store'), variableStarPost($user, 'ss-cyg', [
        'observed_on' => '2026-09-02',
        'observed_time' => '',
        'variable_magnitude' => '9.0',
    ]))->assertSessionHasNoErrors();
    $this->actingAs($other)->post(route('observation.store'), variableStarPost($other, 'ss-cyg', [
        'variable_magnitude' => '11.4',
    ]))->assertSessionHasNoErrors();

    $response = $this->actingAs($user)->get(route('observations.search.aavso', ['q' => 'SS Cyg']));

    // 2026-09-01 21:30 UT; the estimate without a time is left out
    $response->assertOk();
    expect($response->streamedContent())->toBe(implode("\n", [
        '#TYPE=Visual',
        '#OBSCODE=VOB',
        '#SOFTWARE=DeepskyLog (https://www.deepskylog.org)',
        '#DELIM=,',
        '#DATE=JD',
        '#NAME,DATE,MAG,COMMENTCODE,COMP1,COMP2,CHART,NOTES',
        'SS Cyg,2461285.3958,11.2,Z,110,113,na,na',
    ])."\n");
});

it('asks for the aavso observer code before exporting', function () {
    $user = User::factory()->create(['aavso_code' => null]);

    $this->actingAs($user)
        ->from(route('observations.search'))
        ->get(route('observations.search.aavso'))
        ->assertRedirect(route('observations.search'))
        ->assertSessionHas('error');
});

it('lets only logged in observers export for the aavso', function () {
    $this->get(route('observations.search.aavso'))->assertRedirect(route('login'));
});

it('saves the aavso observer code of the profile in capitals', function () {
    $user = User::factory()->create();

    app(UpdateUserProfileInformation::class)->update($user, [
        'name' => $user->name,
        'email' => $user->email,
        'country' => 'BE',
        'about' => '',
        'fstOffset' => 0,
        'copyrightSelection' => '',
        'aavso_code' => ' dmw ',
    ]);

    expect($user->fresh()->aavso_code)->toBe('DMW');
});

it('draws the light curve of the estimates in deepskylog on the object page', function () {
    $user = User::factory()->create(['username' => 'varobs', 'slug' => 'varobs', 'name' => 'Var Observer']);
    $other = User::factory()->create(['username' => 'other', 'slug' => 'other', 'name' => '</script><b>x']);

    $this->actingAs($user)->post(route('observation.store'), variableStarPost($user, 'ss-cyg', [
        'variable_magnitude' => '11.2',
    ]))->assertSessionHasNoErrors();

    // One estimate makes no curve
    $this->blade('<x-variable-star-light-curve object-name="SS Cyg" slug="ss-cyg" />')->assertDontSee('Light curve');

    $this->actingAs($user)->post(route('observation.store'), variableStarPost($user, 'ss-cyg', [
        'observed_on' => '2026-09-03',
        'observed_time' => '',
        'variable_magnitude' => '12.0',
        'variable_fainter' => '1',
    ]))->assertSessionHasNoErrors();

    $points = App\Services\Observations\VariableStarLightCurve::forObject('SS Cyg');
    expect(array_map(fn ($p) => [$p['at'], $p['magnitude'], $p['fainter'], $p['observer'], $p['time_known']], $points))->toBe([
        [CarbonImmutable::parse('2026-09-01 21:30', 'UTC')->getTimestamp(), 11.2, false, 'Var Observer', true],
        [CarbonImmutable::parse('2026-09-03', 'UTC')->getTimestamp(), 12.0, true, 'Var Observer', false],
    ])->and($points[0]['url'])->toContain('/observation/varobs/2026-09-01/ss-cyg');

    $this->actingAs($other)->post(route('observation.store'), variableStarPost($other, 'ss-cyg', [
        'observed_on' => '2026-09-05',
        'variable_magnitude' => '11.8',
    ]))->assertSessionHasNoErrors();

    // An observer's name cannot end the script
    $this->blade('<x-variable-star-light-curve object-name="SS Cyg" slug="ss-cyg" />')
        ->assertDontSee('</script><b>', false)
        ->assertSee('Light curve')
        ->assertSee(route('observation.create', ['slug' => 'ss-cyg']), false);

    $chart = (new App\Charts\VariableStarLightCurveChart())->build('SS Cyg', $points)->toJsonEncodedString();
    expect($chart)->toContain('"reversed":true')->toContain('"observer":"Var Observer"');
});
