<?php

use App\Models\InstrumentType;
use App\Models\MountType;
use App\Models\Observation;
use App\Models\SuspectObservation;
use App\Models\User;
use App\Services\Observations\ApertureRecords;
use App\Services\Observations\SeenByAperture;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// How many observers saw an object, per aperture class
// (App\Services\Observations\SeenByAperture).

function seenByApertureObject(string $name, float $mag, string $type = 'GALXY'): void
{
    DB::table('objects')->insert([
        'name' => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'CVN',
        'ra' => 13.4980, 'decl' => 47.1953, 'mag' => $mag, 'subr' => 13.0, 'pa' => 0,
        'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 300.0, 'diam2' => 200.0,
        'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL' => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function seenByApertureObservation(User $user, float $apertureMm, string $object, bool $seen = true, string $type = 'Reflector', array $attributes = []): Observation
{
    $instrumentId = InstrumentFactory::new()->create([
        'user_id' => $user->id, 'observer' => $user->username, 'name' => $apertureMm.' mm '.$type, 'aperture_mm' => $apertureMm,
        'focal_length_mm' => $apertureMm * 5, 'instrument_type_id' => InstrumentType::firstOrCreate(['name' => $type])->id,
        'mount_type_id' => MountType::firstOrCreate(['name' => 'Alt/Azimuth'])->id,
    ])->id;
    $observation = Observation::create($attributes + [
        'observer_username' => $user->username, 'observer_user_id' => $user->id,
        'observed_on' => '2024-01-01', 'observed_time' => '22:00:00', 'language' => 'en', 'description' => 'Looked',
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => $object, 'target_name' => $object,
        'instrument_id' => $instrumentId,
    ]);
    if (! $seen) {
        DB::table('deepsky_observation_details')->insert(['observation_id' => $observation->id, 'visibility' => Observation::VISIBILITY_NOT_SEEN]);
    }

    return $observation;
}

function seenByApertureClass(?array $result, string $class): ?array
{
    return collect($result['classes'] ?? [])->firstWhere('class', $class);
}

it('counts per aperture class the observers who saw an object and those who did not', function () {
    seenByApertureObject('M 51', 8.4);
    [$a, $b, $c, $d] = User::factory()->count(4)->create()->all();

    // An observer counts once per class, as seen when any of their observations saw it
    seenByApertureObservation($a, 80, 'M 51', false);
    seenByApertureObservation($a, 90, 'M 51', true);
    seenByApertureObservation($b, 100, 'M 51', false);
    seenByApertureObservation($c, 60, 'M 51', true);
    // A doubted observation does not count
    $suspect = seenByApertureObservation($d, 70, 'M 51', true);
    SuspectObservation::create(['observation_id' => $suspect->id, 'reason' => SuspectObservation::REASON_BELOW_HORIZON, 'details' => []]);
    // Two observers in the 8" class: too few for a percentage
    seenByApertureObservation($a, 203, 'M 51', true);
    seenByApertureObservation($b, 200, 'M 51', true);
    // The naked eye is a class of its own
    seenByApertureObservation($c, 30, 'M 51', false, ApertureRecords::NAKED_EYE_TYPE);

    $result = (new SeenByAperture)->forObject('M 51');
    $small = seenByApertureClass($result, 'small');

    expect(collect($result['classes'])->pluck('class')->all())->toBe([SeenByAperture::NAKED_EYE, 'small', '8-inch'])
        ->and($small['observers'])->toBe(3)
        ->and($small['seen'])->toBe(2)
        ->and($small['percentage'])->toBe(66.7)
        ->and($small['category'])->not->toBeNull()
        ->and(seenByApertureClass($result, '8-inch')['percentage'])->toBeNull()
        ->and(seenByApertureClass($result, SeenByAperture::NAKED_EYE)['seen'])->toBe(0)
        // no prediction for the naked eye
        ->and(seenByApertureClass($result, SeenByAperture::NAKED_EYE)['category'])->toBeNull()
        ->and($result['sqm'])->toBe(SeenByAperture::DEFAULT_SQM)
        ->and($result['sqm_from_observations'])->toBeFalse();
});

it('keeps a not-seen observation of an object too faint for the aperture, but not a seen one', function () {
    // A planetary nebula with the magnitude of its central star
    seenByApertureObject('NGC 6543', 17.0, 'PLNNB');
    [$a, $b, $c] = User::factory()->count(3)->create()->all();

    seenByApertureObservation($a, 80, 'NGC 6543', false);
    seenByApertureObservation($b, 80, 'NGC 6543', false);
    seenByApertureObservation($c, 80, 'NGC 6543', false);
    $result = (new SeenByAperture)->forObject('NGC 6543');
    expect(seenByApertureClass($result, 'small')['observers'])->toBe(3);

    seenByApertureObservation(User::factory()->create(), 80, 'NGC 6543', true);
    cache()->flush();
    $result = (new SeenByAperture)->forObject('NGC 6543');
    expect(seenByApertureClass($result, 'small')['observers'])->toBe(3)
        ->and(seenByApertureClass($result, 'small')['seen'])->toBe(0);
});

it('predicts under the median sky of the observations', function () {
    seenByApertureObject('M 51', 8.4);
    foreach ([19.0, 20.0, 21.5] as $sqm) {
        seenByApertureObservation(User::factory()->create(), 150, 'M 51', true, attributes: ['sqm' => $sqm]);
    }

    $result = (new SeenByAperture)->forObject('M 51');

    expect($result['sqm'])->toBe(20.0)
        ->and($result['sqm_from_observations'])->toBeTrue()
        ->and(seenByApertureClass($result, '5-6')['contrast_reserve'])->toBeFloat();
});

it('shows the classes on the object page, marking the viewer\'s telescope', function () {
    seenByApertureObject('M 51', 8.4);
    foreach ([true, true, false] as $seen) {
        seenByApertureObservation(User::factory()->create(), 200, 'M 51', $seen);
    }
    $viewer = User::factory()->create();
    $viewer->forceFill(['stdtelescope' => seenByApertureObservation($viewer, 203, 'NGC 1')->instrument_id])->save();

    $this->actingAs($viewer)
        ->blade('<x-seen-by-aperture object-name="M 51" />')
        ->assertSee('Seen by how many, per aperture')
        ->assertSee('67%')
        ->assertSee('2 of 3')
        ->assertSee('yours');

    $this->blade('<x-seen-by-aperture object-name="NGC 1" />')->assertDontSee('Seen by how many, per aperture');
});
