<?php

use App\Jobs\ComputeContrastReserveForObject;
use App\Models\Eyepiece;
use App\Models\Instrument;
use App\Models\Location;
use App\Models\User;
use App\Models\UserObjectMetric;
use App\Services\ContrastReserveCalculator;
use Database\Factories\InstrumentFactory;
use Database\Factories\LensFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function insertContrastReserveObject(string $name = 'M 31'): void
{
    DB::table('objects')->insert([
        'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'type' => 'GALXY', 'con' => 'AND',
        'ra' => 0.712313, 'decl' => 41.26917, 'mag' => 8.4, 'subr' => 12.0, 'pa' => 35, 'urano' => 0,
        'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 600.0, 'diam2' => 240.0,
        'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

/**
 * @return array{User, Instrument, Location, Lens}
 */
function contrastReserveEquipment(array $instrument = []): array
{
    $user = User::factory()->create();
    $instrument = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'aperture_mm' => 200, 'focal_length_mm' => 1200, ...$instrument]);
    $location = LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'skyBackground' => 21.0]);
    $lens = LensFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'factor' => 2.0]);
    foreach ([24.0, 12.0] as $focal) {
        Eyepiece::query()->create([
            'user_id' => $user->id, 'make_id' => DB::table('eyepiece_makes')->value('id'),
            'type_id' => DB::table('eyepiece_types')->value('id'), 'name' => "Eyepiece {$focal}", 'focal_length_mm' => $focal,
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
    $user = User::factory()->create();
    $instrument = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);

    expect(array_keys(ContrastReserveCalculator::magnifications($instrument, $user)))->toBe(ContrastReserveCalculator::GENERIC_MAGNIFICATIONS);
});

it('stores the same values as the preview calculates', function () {
    insertContrastReserveObject();
    [$user, $instrument, $location, $lens] = contrastReserveEquipment();

    (new ComputeContrastReserveForObject($user->id, $instrument->id, $location->id, 'M 31', $lens->id))->handle();

    $metric = UserObjectMetric::where('object_name', 'M 31')->sole();
    $expected = ContrastReserveCalculator::calculate(ContrastReserveCalculator::target(600.0, 240.0, 8.4), $instrument, $location, $user, 2.0);

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

    $user->forceFill(['stdtelescope' => $instrument->id, 'stdlocation' => $location->id, 'stdlens' => $lens->id])->save();
    \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\EnqueueComputeCRForUser::class, 1);

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
