<?php

use App\Livewire\EyepieceLadder;
use App\Livewire\WhatsNewEquipment;
use App\Models\Instrument;
use App\Models\InstrumentType;
use App\Models\MountType;
use App\Models\User;
use App\Services\EquipmentGain;
use App\Services\EyepieceGaps;
use Database\Factories\EyepieceFactory;
use Database\Factories\InstrumentFactory;
use Database\Factories\InstrumentSetFactory;
use Database\Factories\LensFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

function eyepieceGapsObject(string $name, float $mag, float $diam, string $type = 'PLNNB'): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'AND',
        'ra'            => 1.0, 'decl' => 40.0, 'mag' => $mag, 'subr' => 0.0, 'pa' => 999,
        'urano'         => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => $diam, 'diam2' => $diam,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL'         => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function eyepieceGapsEyepiece(User $user, float $focalLength, array $attributes = []): void
{
    EyepieceFactory::new()->create([
        'user_id'     => $user->id, 'observer' => $user->username, 'name' => $focalLength.' mm', 'focal_length_mm' => $focalLength,
        'apparentFOV' => 50, 'field_stop_mm' => null, 'max_focal_length_mm' => -1, 'active' => true, ...$attributes,
    ]);
}

/**
 * An observer at a suburban site (SQM 20) with a 200 mm f/6 Dobsonian and
 * eyepieces of 25 and 10 mm (48× and 120×, exit pupils 4.2 and 1.7 mm).
 *
 * @return array{0: User, 1: Instrument}
 */
function eyepieceGapsObserver(): array
{
    $team = test()->getTeamFromString('Observers');
    $user = User::factory()->create();
    $user->teams()->attach($team);
    $user->switchTeam($team);
    $dobson = InstrumentFactory::new()->create([
        'user_id'            => $user->id, 'observer' => $user->username, 'name' => 'Dobson', 'aperture_mm' => 200, 'focal_length_mm' => 1200,
        'fixedMagnification' => 0, 'active' => true, 'lens_id' => null,
        'instrument_type_id' => InstrumentType::firstOrCreate(['name' => 'Reflector'])->id,
        'mount_type_id'      => MountType::firstOrCreate(['name' => 'Alt/Azimuth'])->id,
    ]);
    eyepieceGapsEyepiece($user, 25);
    eyepieceGapsEyepiece($user, 10);
    $location = LocationFactory::new()->create([
        'user_id'  => $user->id, 'observer' => $user->username, 'name' => 'Backyard', 'latitude' => 50.8, 'longitude' => 4.7,
        'timezone' => 'Europe/Brussels', 'limitingMagnitude' => -999, 'skyBackground' => 20.0, 'active' => true,
    ]);
    $user->forceFill(['stdtelescope' => $dobson->id, 'stdlocation' => $location->id])->save();

    return [$user->fresh(), $dobson];
}

it('makes the ladder of the eyepieces with the lenses', function () {
    [$user, $dobson] = eyepieceGapsObserver();
    eyepieceGapsEyepiece($user, 8, ['name' => 'Zoom', 'max_focal_length_mm' => 24, 'apparentFOV' => 60]);
    LensFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Barlow', 'factor' => 2, 'active' => true]);

    $steps = (new EyepieceGaps($user, $dobson))->steps();

    expect(array_map(fn ($step) => [$step['magnification'], $step['max_magnification']], $steps))
        ->toBe([[48, null], [50, 150], [96, null], [100, 300], [120, null], [240, null]])
        ->and(round($steps[0]['exit_pupil'], 2))->toBe(4.17)
        ->and(round($steps[0]['field'], 2))->toBe(1.04)
        ->and(round($steps[1]['min_exit_pupil'], 2))->toBe(1.33)
        ->and($steps[2]['combinations'][0]['lens']->name)->toBe('Barlow');
});

it('finds the gaps and suggests the eyepieces that fill them', function () {
    [$user, $dobson] = eyepieceGapsObserver();

    $gaps = (new EyepieceGaps($user, $dobson))->gaps();

    expect(array_column($gaps, 'kind'))->toBe(['low', 'between', 'between', 'high'])
        // a 2" eyepiece of 36 mm gives an exit pupil of 6 mm and a field of 2°
        ->and($gaps[0]['suggestions'][0])->toMatchArray(['focal_length' => 36.0, 'magnification' => 33, 'exit_pupil' => 6.0])
        ->and(round($gaps[0]['field'], 2))->toBe(1.04)
        ->and($gaps[1])->toMatchArray(['from' => 4.2, 'to' => 1.7, 'magnification' => 48, 'to_magnification' => 120])
        ->and(array_column($gaps[1]['suggestions'], 'focal_length'))->toBe([18.0, 14.0])
        ->and(array_column($gaps[2]['suggestions'], 'focal_length'))->toBe([7.5, 5.5])
        ->and($gaps[3])->toMatchArray(['from' => 1.7, 'magnification' => 120])
        ->and($gaps[3]['suggestions'][0])->toMatchArray(['focal_length' => 4.0, 'magnification' => 300]);
});

it('finds no gaps in a complete ladder', function () {
    [$user, $dobson] = eyepieceGapsObserver();
    foreach ([[36, 46], [18, null], [14, null], [7, null], [5, null], [4, null]] as [$focalLength, $fieldStop]) {
        eyepieceGapsEyepiece($user, $focalLength, ['field_stop_mm' => $fieldStop, 'apparentFOV' => 68]);
    }

    expect((new EyepieceGaps($user, $dobson))->gaps())->toBe([]);
});

it('suggests a whole ladder without eyepieces', function () {
    [$user, $dobson] = eyepieceGapsObserver();
    DB::table('eyepieces')->where('user_id', $user->id)->update(['active' => false]);

    $gaps = (new EyepieceGaps($user, $dobson))->gaps();

    expect(array_column($gaps, 'kind'))->toBe(['low', 'between', 'high'])
        ->and(array_column($gaps[1]['suggestions'], 'focal_length'))->toBe([18.0, 8.5]);
});

it('does not apply to binoculars', function () {
    [$user, $dobson] = eyepieceGapsObserver();

    expect(EyepieceGaps::applies($dobson))->toBeTrue()
        ->and(EyepieceGaps::applies(new Instrument(['aperture_mm' => 50, 'focal_length_mm' => 0, 'fixedMagnification' => 10])))->toBeFalse();
});

it('counts the objects a planned eyepiece adds to the ladder', function () {
    [$user, $dobson] = eyepieceGapsObserver();
    // a small planetary nebula only seen at a high magnification, and one seen with both
    eyepieceGapsObject('Tiny Nebula', 13.6, 8);
    eyepieceGapsObject('Bright Nebula', 9.0, 30);

    $gain   = EquipmentGain::forPlannedEyepiece($user, $dobson, ['focal_length' => 4, 'apparent_fov' => 68], EquipmentGain::location($user, null));
    $result = $gain->compute();

    expect($gain->unavailable())->toBeNull()
        ->and(array_column($result['objects'], 'name'))->toBe(['Tiny Nebula'])
        ->and($result['objects'][0]['magnification'])->toBe(300)
        ->and($result['compared'])->toBe([$dobson->fullName().' with your eyepieces'])
        ->and($gain->unseenTotal($result))->toBe(1)
        ->and($gain->equipmentName())->toBe('4 mm 68° eyepiece');
});

it('shows the ladder with the gaps on the own instrument page', function () {
    [$user, $dobson] = eyepieceGapsObserver();
    eyepieceGapsObject('Tiny Nebula', 13.6, 8);

    $this->actingAs($user)->get('/instrument/'.$user->slug.'/'.$dobson->slug)
        ->assertOk()
        ->assertSee('Eyepiece ladder')
        ->assertSee('Nothing between 4.2 mm and 1.7 mm exit pupil (48× to 120×).');

    Livewire::actingAs($user)
        ->test(EyepieceLadder::class, ['instrumentId' => $dobson->id])
        ->assertSee('36 mm eyepiece')
        ->assertSee(route('instrument.what-if-eyepiece', [$user->slug, $dobson->slug, 'focal_length' => 4.0, 'apparent_field' => 68.0]))
        ->assertSee('one object you have not seen');

    $other = User::factory()->create();
    $this->actingAs($other)->get('/instrument/'.$user->slug.'/'.$dobson->slug)
        ->assertOk()
        ->assertDontSee('Eyepiece ladder');
});

it('shows what a planned eyepiece adds on its own page', function () {
    [$user, $dobson] = eyepieceGapsObserver();
    eyepieceGapsObject('Tiny Nebula', 13.6, 8);

    $this->actingAs($user)->get(route('instrument.what-if-eyepiece', [$user->slug, $dobson->slug, 'focal_length' => 4, 'apparent_field' => 68]))
        ->assertOk()
        ->assertSee('Tiny Nebula');

    Livewire::actingAs($user)
        ->withQueryParams(['focal_length' => '4'])
        ->test(WhatsNewEquipment::class, ['kind' => EquipmentGain::PLANNED_EYEPIECE, 'equipmentId' => $dobson->id])
        ->assertSee('Tiny Nebula')
        ->set('focalLength', '0.2')
        ->call('calculate')
        ->assertHasErrors('focalLength');

    $other = User::factory()->create();
    $this->actingAs($other)->get(route('instrument.what-if-eyepiece', [$user->slug, $dobson->slug]))->assertNotFound();
});

it('uses only the eyepieces and lenses of a chosen instrument set', function () {
    [$user, $dobson] = eyepieceGapsObserver();
    LensFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Barlow', 'factor' => 2, 'active' => true]);
    $set = InstrumentSetFactory::new()->create(['user_id' => $user->id, 'name' => 'Travel kit']);
    $set->instruments()->attach($dobson->id);
    $set->eyepieces()->attach($user->eyepieces()->where('focal_length_mm', 25)->value('id'));
    // another observer's set is not used
    $foreign = InstrumentSetFactory::new()->create();

    expect(EyepieceGaps::set($user, $set->id)?->id)->toBe($set->id)
        ->and(EyepieceGaps::set($user, $foreign->id))->toBeNull()
        ->and(array_column((new EyepieceGaps($user, $dobson))->steps(), 'magnification'))->toBe([48, 96, 120, 240])
        ->and(array_column((new EyepieceGaps($user, $dobson, $set))->steps(), 'magnification'))->toBe([48]);

    eyepieceGapsObject('Tiny Nebula', 13.6, 8);
    $gain   = EquipmentGain::forPlannedEyepiece($user, $dobson, ['focal_length' => 4, 'apparent_fov' => 68, 'instrument_set' => $set->id], EquipmentGain::location($user, null));
    $result = $gain->compute();
    expect($result['compared'])->toBe([$dobson->fullName().' with the eyepieces of Travel kit'])
        ->and(array_column($result['objects'], 'name'))->toBe(['Tiny Nebula']);

    Livewire::actingAs($user)
        ->test(EyepieceLadder::class, ['instrumentId' => $dobson->id])
        ->assertSee('240×')
        ->set('instrumentSet', (string) $set->id)
        ->assertDontSee('240×')
        ->assertSee('set='.$set->id);

    Livewire::actingAs($user)
        ->withQueryParams(['focal_length' => '4', 'set' => (string) $set->id])
        ->test(WhatsNewEquipment::class, ['kind' => EquipmentGain::PLANNED_EYEPIECE, 'equipmentId' => $dobson->id])
        ->assertSet('planned.instrument_set', $set->id)
        ->assertSee('with the eyepieces of Travel kit');
});
