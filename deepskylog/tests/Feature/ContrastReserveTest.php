<?php

use App\Jobs\ComputeContrastReserveForObject;
use App\Models\Eyepiece;
use App\Models\Instrument;
use App\Models\Location;
use App\Models\User;
use App\Models\UserObjectMetric;
use App\Services\AtmosphericExtinction;
use App\Services\ContrastReserveCalculator;
use Database\Factories\InstrumentFactory;
use Database\Factories\LensFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function insertContrastReserveObject(string $name = 'M 31'): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'type' => 'GALXY', 'con' => 'AND',
        'ra'            => 0.712313, 'decl' => 41.26917, 'mag' => 8.4, 'subr' => 12.0, 'pa' => 35, 'urano' => 0,
        'urano_new'     => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 600.0, 'diam2' => 240.0,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP'         => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

/**
 * @return array{User, Instrument, Location, Lens}
 */
function contrastReserveEquipment(array $instrument = []): array
{
    $user       = User::factory()->create();
    $instrument = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'aperture_mm' => 200, 'focal_length_mm' => 1200, ...$instrument]);
    $location   = LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'skyBackground' => 21.0, 'latitude' => 51.0]);
    $lens       = LensFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'factor' => 2.0]);
    foreach ([24.0, 12.0] as $focal) {
        Eyepiece::query()->create([
            'user_id'     => $user->id, 'make_id' => DB::table('eyepiece_makes')->value('id'),
            'type_id'     => DB::table('eyepiece_types')->value('id'), 'name' => "Eyepiece {$focal}", 'focal_length_mm' => $focal,
            'apparentFOV' => 68, 'active' => true, 'observer' => '', 'observations' => 0, 'slug' => "eyepiece-{$focal}-{$user->id}",
        ]);
    }

    return [$user, $instrument, $location, $lens];
}

it('uses one scale of categories', function () {
    expect(ContrastReserveCalculator::category(1.2))->toBe('very_easy')
        ->and(ContrastReserveCalculator::category(0.6))->toBe('easy')
        ->and(ContrastReserveCalculator::category(0.4))->toBe('quite_difficult')
        ->and(ContrastReserveCalculator::category(0.2))->toBe('difficult')
        ->and(ContrastReserveCalculator::category(0.0))->toBe('questionable')
        ->and(ContrastReserveCalculator::category(-0.5))->toBe('not_visible')
        ->and(ContrastReserveCalculator::category(null))->toBeNull();
});

it('takes the magnifications from the eyepieces, with the lens', function () {
    [$user, $instrument] = contrastReserveEquipment();

    expect(array_keys(ContrastReserveCalculator::magnifications($instrument, $user)))->toEqualCanonicalizing([50, 100])
        ->and(array_keys(ContrastReserveCalculator::magnifications($instrument, $user, 2.0)))->toEqualCanonicalizing([100, 200]);
});

it('does not apply the lens to a fixed magnification', function () {
    [$user, $instrument] = contrastReserveEquipment(['fixedMagnification' => 10, 'aperture_mm' => 50]);

    expect(ContrastReserveCalculator::magnifications($instrument, $user, 2.0))->toBe([10 => []]);
});

it('falls back to generic magnifications without eyepieces', function () {
    $user       = User::factory()->create();
    $instrument = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);

    expect(array_keys(ContrastReserveCalculator::magnifications($instrument, $user)))->toBe(ContrastReserveCalculator::GENERIC_MAGNIFICATIONS);
});

it('stores the same values as the preview calculates', function () {
    insertContrastReserveObject();
    [$user, $instrument, $location, $lens] = contrastReserveEquipment();

    (new ComputeContrastReserveForObject($user->id, $instrument->id, $location->id, 'M 31', $lens->id))->handle();

    $metric   = UserObjectMetric::where('object_name', 'M 31')->sole();
    $expected = ContrastReserveCalculator::calculate(ContrastReserveCalculator::target(600.0, 240.0, 8.4), $instrument, $location, $user, 2.0,
        altitude: ContrastReserveCalculator::highestAltitude(41.26917, $location));

    expect($metric->lens_id)->toBe($lens->id)
        ->and((float) $metric->contrast_reserve)->toEqualWithDelta($expected['contrast_reserve'], 1e-6)
        ->and($metric->contrast_reserve_category)->toBe($expected['category'])
        ->and($metric->optimum_detection_magnification)->toBe($expected['magnification'])
        ->and($expected['magnification'])->toBeIn([100, 200]);
});

it('stores the values of metrics:compute-cr under the lens', function () {
    insertContrastReserveObject();
    [$user, $instrument, $location, $lens] = contrastReserveEquipment();

    $this->artisan('metrics:compute-cr', [
        'user_id' => $user->id, '--instrument_id' => $instrument->id, '--location_id' => $location->id, '--lens_id' => $lens->id,
    ])->assertSuccessful();

    expect(UserObjectMetric::where('object_name', 'M 31')->pluck('lens_id')->all())->toBe([$lens->id]);
});

it('replaces the stored values when metrics:compute-cr runs again', function () {
    insertContrastReserveObject();
    insertContrastReserveObject('M 32');
    [$user, $instrument, $location] = contrastReserveEquipment();
    UserObjectMetric::create([
        'user_id' => $user->id, 'instrument_id' => $instrument->id, 'location_id' => $location->id,
        'lens_id' => null, 'object_name' => 'M 31', 'contrast_reserve' => 99.0,
    ]);

    $args = ['user_id' => $user->id, '--instrument_id' => $instrument->id, '--location_id' => $location->id];
    $this->artisan('metrics:compute-cr', $args)->assertSuccessful();
    $this->artisan('metrics:compute-cr', $args + ['--queued' => true])->assertSuccessful();

    $metrics = UserObjectMetric::whereNull('lens_id')->orderBy('object_name')->get();
    expect($metrics->pluck('object_name')->all())->toBe(['M 31', 'M 32'])
        ->and((float) $metrics->first()->contrast_reserve)->not->toBe(99.0);
});

it('queues the metrics per chunk of objects when the standard equipment changes', function () {
    insertContrastReserveObject();
    insertContrastReserveObject('M 32');
    [$user, $instrument, $location, $lens] = contrastReserveEquipment();
    \Illuminate\Support\Facades\Queue::fake();

    $instrument->forceFill(['lens_id' => $lens->id])->saveQuietly();
    $user->forceFill(['stdtelescope' => $instrument->id, 'stdlocation' => $location->id])->save();
    \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\EnqueueComputeCRForUser::class, fn ($job) => $job->lensId === $lens->id);

    (new \App\Jobs\EnqueueComputeCRForUser($user->id, $instrument->id, $location->id, $lens->id))->handle();
    \Illuminate\Support\Facades\Queue::assertPushed(
        \App\Jobs\ComputeContrastReserveForObjectsBatch::class,
        fn ($job) => $job->recompute && $job->objectNames === ['M 31', 'M 32'] && $job->lensId === $lens->id
    );
    \Illuminate\Support\Facades\Queue::assertNotPushed(ComputeContrastReserveForObject::class);
});

it('lets the queue retry a failing contrast reserve job', function () {
    insertContrastReserveObject();
    [$user, $instrument, $location] = contrastReserveEquipment();
    Schema::drop('user_object_metrics');

    (new ComputeContrastReserveForObject($user->id, $instrument->id, $location->id, 'M 31'))->handle();
})->throws(\Illuminate\Database\QueryException::class);

it('recomputes the metrics with the new lens when the lens of the standard telescope changes', function () {
    [$user, $instrument, $location, $lens] = contrastReserveEquipment();
    $user->forceFill(['stdtelescope' => $instrument->id, 'stdlocation' => $location->id])->saveQuietly();
    \Illuminate\Support\Facades\Queue::fake();

    $instrument->update(['lens_id' => $lens->id]);

    \Illuminate\Support\Facades\Queue::assertPushed(
        \App\Jobs\EnqueueComputeCRForUser::class,
        fn ($job) => $job->userId === $user->id && $job->instrumentId === $instrument->id && $job->lensId === $lens->id
    );
    expect($user->fresh()->standardLensId())->toBe($lens->id);
});

it('chooses the optimum detection magnification among the magnifications whose field holds the whole object', function () {
    // A small, bright object: the best contrast reserve, at whatever field.
    $small   = ContrastReserveCalculator::target(60, 60, 9.0);
    $sbSmall = $small->calculateSBObj();
    $all     = $small->calculateBestMagnification($sbSmall, 21.5, 400, [50, 100, 300]);
    expect(ContrastReserveCalculator::bestMagnification($small, $sbSmall, 21.5, 400, [50, 100, 300]))->toBe((int) $all);

    // A large object (3°): the library alone takes a magnification at which
    // only a part of it is seen; no field holds it, so the widest is used.
    $large   = ContrastReserveCalculator::target(10800, 3600, 3.4);
    $sbLarge = $large->calculateSBObj();
    expect((int) $large->calculateBestMagnification($sbLarge, 21.5, 400, [50, 100, 300]))->toBeGreaterThan(50)
        ->and(ContrastReserveCalculator::bestMagnification($large, $sbLarge, 21.5, 400, [50, 100, 300]))->toBe(50)
        // A known field counts: a 100° eyepiece at 30x holds 3.3°.
        ->and(ContrastReserveCalculator::bestMagnification($large, $sbLarge, 21.5, 400, [30, 50, 300], [30 => 100 / 30]))->toBe(30);
});

it('takes the field of view at each magnification from the widest eyepiece giving it', function () {
    [, $instrument] = contrastReserveEquipment();
    $wide           = new Eyepiece(['focal_length_mm' => 24, 'apparentFOV' => 82]);
    $narrow         = new Eyepiece(['focal_length_mm' => 24, 'apparentFOV' => 52]);
    $stop           = new Eyepiece(['focal_length_mm' => 12, 'apparentFOV' => 60, 'field_stop_mm' => 12.0]);

    $magnifications = ContrastReserveCalculator::eyepieceMagnifications($instrument, [$narrow, $wide, $stop]);
    $fields         = ContrastReserveCalculator::fields($instrument, $magnifications);

    expect(array_keys($magnifications))->toBe([50, 100])
        ->and($fields[50])->toBe(82 / 50)
        // From the field stop: 12 / 1200 rad.
        ->and(round($fields[100], 3))->toBe(round(12 / 1200 * 57.2958, 3));
});

it('computes the air mass and the extinction against the zenith', function () {
    expect(AtmosphericExtinction::airmass(90.0))->toEqualWithDelta(1.0, 1e-3)
        ->and(AtmosphericExtinction::airmass(30.0))->toEqualWithDelta(2.0, 0.01)
        ->and(AtmosphericExtinction::magnitudes(90.0))->toEqualWithDelta(0.0, 1e-3)
        ->and(AtmosphericExtinction::magnitudes(30.0))->toEqualWithDelta(0.25, 0.005)
        ->and(AtmosphericExtinction::magnitudes(0.0))->toBe(INF)
        ->and(AtmosphericExtinction::highestAltitude(41.0, 51.0))->toEqualWithDelta(80.0, 1e-9)
        ->and(AtmosphericExtinction::highestAltitude(-30.0, 51.0))->toEqualWithDelta(9.0, 1e-9)
        ->and(AtmosphericExtinction::highestAltitude(-50.0, 51.0))->toBeLessThan(0.0);
});

it('dims the object by the extinction at its highest altitude', function () {
    [$user, $instrument, $location] = contrastReserveEquipment();
    $target = ContrastReserveCalculator::target(600.0, 240.0, 8.4);

    $zenith = ContrastReserveCalculator::calculate($target, $instrument, $location, $user);
    $high   = ContrastReserveCalculator::calculate($target, $instrument, $location, $user, altitude: 80.0);
    $low    = ContrastReserveCalculator::calculate($target, $instrument, $location, $user, altitude: 9.0);
    $below  = ContrastReserveCalculator::calculate($target, $instrument, $location, $user, altitude: -5.0);

    expect($zenith['extinction'])->toBeNull()
        ->and($high['extinction'])->toBeLessThan(0.01)
        ->and($low['extinction'])->toBeGreaterThan(1.0)
        ->and($high['contrast_reserve'])->toBeLessThan($zenith['contrast_reserve'])
        ->and($low['contrast_reserve'])->toBeLessThan($high['contrast_reserve'] - 0.3)
        ->and($below)->toMatchArray(['contrast_reserve' => null, 'category' => 'not_visible', 'magnification' => null])
        // the caller's target keeps its magnitude
        ->and($target->getMagnitude())->toBe(8.4);

    $point = ContrastReserveCalculator::pointSource(10.0, $instrument, $location, $user);
    $lowPoint = ContrastReserveCalculator::pointSource(10.0, $instrument, $location, $user, altitude: 9.0);
    expect($lowPoint['contrast_reserve'])->toEqualWithDelta($point['contrast_reserve'] - 0.4 * $lowPoint['extinction'], 1e-9)
        ->and(ContrastReserveCalculator::pointSource(10.0, $instrument, $location, $user, altitude: -5.0)['category'])->toBe('not_visible');
});

it('stores an object that never rises at the location as not visible', function () {
    insertContrastReserveObject();
    DB::table('objects')->where('name', 'M 31')->update(['decl' => -70.0]);
    [$user, $instrument, $location] = contrastReserveEquipment();

    (new ComputeContrastReserveForObject($user->id, $instrument->id, $location->id, 'M 31'))->handle();

    $metric = UserObjectMetric::where('object_name', 'M 31')->sole();
    expect($metric->contrast_reserve)->toBeNull()
        ->and($metric->contrast_reserve_category)->toBe('not_visible');
});

it('drops the stored metrics when the latitude of the location changes', function () {
    insertContrastReserveObject();
    [$user, $instrument, $location] = contrastReserveEquipment();
    (new ComputeContrastReserveForObject($user->id, $instrument->id, $location->id, 'M 31'))->handle();
    expect(UserObjectMetric::count())->toBe(1);

    $location->update(['latitude' => (float) $location->latitude + 5.0]);

    expect(UserObjectMetric::count())->toBe(0);
});
