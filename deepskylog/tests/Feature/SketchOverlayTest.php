<?php

use App\Models\MountType;
use App\Models\Observation;
use App\Models\User;
use App\Services\Observations\SketchOverlay;
use Database\Factories\EyepieceFactory;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// The drawing of an observation laid over the DSS image (SketchOverlay): the
// field of the eyepiece, and the turn and mirroring of the eyepiece view
// (FieldViewRenderer) undone.

function soObject(string $name, float $ra, float $decl): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => 'GALXY', 'con' => 'AND',
        'ra'            => $ra, 'decl' => $decl, 'mag' => 8.0, 'subr' => 13.0, 'pa' => 0, 'urano' => 0,
        'urano_new'     => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 300.0, 'diam2' => 200.0,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP'         => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

/** @param array<string, mixed> $instrument */
function soObservation(array $instrument = [], bool $drawing = true): Observation
{
    soObject('M 31', 0.712, 41.27);
    $observer = User::factory()->create();
    $location = LocationFactory::new()->create(['user_id' => $observer->id, 'observer' => $observer->username, 'name' => 'Leuven',
        'latitude'                                           => 50.85, 'longitude' => 4.35, 'timezone' => 'Europe/Brussels']);
    $telescope = InstrumentFactory::new()->create(['user_id' => $observer->id, 'observer' => '', 'name' => 'Dobson',
        'aperture_mm'                                         => 300, 'focal_length_mm' => 1500, 'fixedMagnification' => null] + $instrument);
    $eyepiece = EyepieceFactory::new()->create(['user_id' => $observer->id, 'focal_length_mm' => 25, 'apparentFOV' => 50, 'field_stop_mm' => null]);

    return Observation::create([
        'observer_username' => $observer->username,
        'observer_user_id'  => $observer->id,
        'observed_on'       => '2026-10-26',
        'observed_time'     => '21:00:00',
        'target_type'       => 'deepsky',
        'target_table'      => 'objects',
        'target_key'        => 'M 31',
        'target_name'       => 'M 31',
        'location_id'       => $location->id,
        'instrument_id'     => $telescope->id,
        'eyepiece_id'       => $eyepiece->id,
        'description'       => 'Seen',
        'has_drawing'       => $drawing,
        'drawing_filename'  => $drawing ? 'sketches/m31.jpg' : null,
    ]);
}

it('turns the direction of a point to the top as the field views do', function () {
    // North is up already, east (to the left) is turned up by turning a quarter clockwise
    expect(SketchOverlay::rotationTowards(10.0, 0.0, 10.0, 20.0))->toEqualWithDelta(0.0, 1e-6)
        ->and(SketchOverlay::rotationTowards(10.0, 0.0, 20.0, 0.0))->toEqualWithDelta(-90.0, 1e-6)
        ->and(SketchOverlay::rotationTowards(10.0, 0.0, 0.0, 0.0))->toEqualWithDelta(90.0, 1e-6)
        ->and(SketchOverlay::rotationTowards(10.0, 0.0, 10.0, 0.0))->toBeNull();
});

it('places north and west of the sketch pad as the telescope view does', function () {
    // North up: north at the top, west to the right (east left, as on the sky)
    expect(SketchOverlay::compass(0.0, false, false))->toEqual(['north' => [0.0, -1.0], 'west' => [1.0, 0.0]])
        // Mirrored left-right: west to the left
        ->and(SketchOverlay::compass(0.0, false, true)['west'])->toEqual([-1.0, 0.0])
        // Mirrored top-bottom: north at the bottom
        ->and(SketchOverlay::compass(0.0, true, false)['north'])->toEqual([0.0, 1.0])
        // Turned a quarter counter-clockwise: north to the left, west up
        ->and(SketchOverlay::compass(90.0, false, false))->toEqual(['north' => [-1.0, 0.0], 'west' => [0.0, -1.0]]);
});

it('lays a drawing made with an alt-azimuth telescope zenith up, with the field of the eyepiece', function () {
    $observation = soObservation(['flip_image' => true, 'flop_image' => false,
        'mount_type_id'                        => MountType::firstOrCreate(['name' => 'Alt/Azimuth'])->id]);

    $overlay = SketchOverlay::for($observation);

    // 1500 mm / 25 mm = 60x, 50° / 60 = 0.83°
    expect($overlay['field_deg'])->toEqualWithDelta(50 / 60, 1e-3)
        ->and($overlay['field_estimated'])->toBeFalse()
        ->and($overlay['up'])->toBe('zenith')
        ->and($overlay['flip'])->toBeTrue()
        ->and($overlay['flop'])->toBeFalse()
        ->and($overlay['ra'])->toEqualWithDelta(0.712 * 15, 1e-6)
        ->and($overlay['rotation_deg'])->not->toBe(0.0);
});

it('takes the field of a sketch of the sketch pad inside its N and W', function () {
    $observation = soObservation();
    expect(SketchOverlay::for($observation)['field_fraction'])->toBe(1.0);

    $observation->forceFill(['drawing_filename' => \App\Services\AtTheEyepiece::DRAWING_PREFIX.'abc.jpg'])->save();
    expect(SketchOverlay::for($observation)['field_fraction'])->toBe(\App\Services\AtTheEyepiece::SKETCH_FIELD);
});

it('lays a drawing made with an equatorial telescope north up', function () {
    $observation = soObservation(['mount_type_id' => MountType::firstOrCreate(['name' => 'Equatorial'])->id]);

    $overlay = SketchOverlay::for($observation);

    expect($overlay['up'])->toBe('north')
        ->and($overlay['rotation_deg'])->toBe(0.0);
});

it('has no overlay without a drawing', function () {
    expect(SketchOverlay::for(soObservation([], false)))->toBeNull();
});

it('offers the comparison with the photo under the drawing', function () {
    \Illuminate\Database\Eloquent\Model::preventLazyLoading();
    $observation = soObservation();
    // A second observation that day: the page reads them as a collection
    $observation->replicate()->fill(['observed_time' => '22:00:00', 'has_drawing' => false])->save();

    $this->get($observation->detailUrl())->assertOk()
        ->assertSee(__('Compare with the photo'))
        ->assertSee('sketch-overlay-data', false);
});
