<?php

use App\Livewire\Profile\OwnEye;
use App\Models\InstrumentType;
use App\Models\MountType;
use App\Models\Observation;
use App\Models\User;
use App\Services\DetectionThreshold;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

// "Your own eye": the observer's own detection threshold (App\Services\DetectionThreshold).

/**
 * Points seen as an observer with the threshold and slope would see them:
 * at each reserve of the grid, $per observations of which the fraction
 * 1 / (1 + e^-(slope·(reserve - threshold))) is seen.
 *
 * @return list<array{reserve: float, seen: float}>
 */
function eyePoints(float $threshold, float $slope = 3.0, int $per = 20, float $from = -1.0, float $to = 1.5): array
{
    $points = [];
    for ($reserve = $from; $reserve <= $to + 1e-9; $reserve += 0.1) {
        $seen = (int) round($per / (1 + exp(-$slope * ($reserve - $threshold))));
        for ($i = 0; $i < $per; $i++) {
            $points[] = ['reserve' => round($reserve, 2), 'seen' => $i < $seen ? 1.0 : 0.0];
        }
    }

    return $points;
}

function eyeObserver(): User
{
    $user = User::factory()->create();
    InstrumentFactory::new()->create([
        'user_id' => $user->id, 'observer' => $user->username, 'name' => 'Dobson', 'aperture_mm' => 300, 'focal_length_mm' => 1500,
        'obstruction_perc' => 0, 'fixedMagnification' => null,
        'instrument_type_id' => InstrumentType::firstOrCreate(['name' => 'Reflector'])->id,
        'mount_type_id' => MountType::firstOrCreate(['name' => 'Alt/Azimuth'])->id,
    ]);
    LocationFactory::new()->create([
        'user_id' => $user->id, 'observer' => $user->username, 'name' => 'Backyard', 'latitude' => 50.8, 'longitude' => 4.7,
        'timezone' => 'Europe/Brussels', 'skyBackground' => 20.0, 'limitingMagnitude' => -999,
    ]);

    return $user;
}

function eyeObservation(User $user, string $object, ?int $visibility = null): Observation
{
    $observation = Observation::create([
        'observer_username' => $user->username, 'observer_user_id' => $user->id,
        'instrument_id' => DB::table('instruments')->where('user_id', $user->id)->value('id'),
        'location_id' => DB::table('locations')->where('user_id', $user->id)->value('id'),
        'observed_on' => '2026-01-15', 'observed_time' => '22:00:00', 'description' => 'Seen',
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => $object, 'target_name' => $object,
    ]);
    if ($visibility !== null) {
        DB::table('deepsky_observation_details')->insert(['observation_id' => $observation->id, 'visibility' => $visibility]);
    }

    return $observation;
}

it('finds the reserve at which the observer sees half the objects', function () {
    foreach ([-0.3, 0.0, 0.25] as $threshold) {
        $fit = DetectionThreshold::fit(eyePoints($threshold, from: $threshold - 1.2, to: $threshold + 1.2));

        expect($fit['threshold'])->toEqualWithDelta($threshold, 0.05)
            ->and($fit['slope'])->toEqualWithDelta(3.0, 0.6)
            ->and($fit['reliable'])->toBeTrue()
            ->and($fit['reason'])->toBeNull();
    }
});

it('does not depend on how many of the objects not seen were recorded', function () {
    // only one in five of the objects not seen recorded
    $kept = 0;
    $points = array_values(array_filter(eyePoints(0.2, from: -1.0, to: 1.4), function (array $p) use (&$kept) {
        return $p['seen'] === 1.0 || $kept++ % 5 === 0;
    }));

    expect(DetectionThreshold::fit($points)['threshold'])->toEqualWithDelta(0.2, 0.1);
});

it('needs enough observations not seen, very questionable ones counting half', function () {
    $seen = array_fill(0, DetectionThreshold::MIN_SEEN, ['reserve' => 0.5, 'seen' => 1.0]);
    $notSeen = array_fill(0, DetectionThreshold::MIN_NOT_SEEN - 1, ['reserve' => -0.5, 'seen' => 0.0]);
    $questionable = [['reserve' => 0.0, 'seen' => 0.5]];

    expect(DetectionThreshold::fit([...$seen, ...$notSeen, ...$questionable]))
        ->threshold->toBeNull()
        ->reason->toBe('Not enough observations yet.')
        ->questionable->toBe(1)
        ->and(DetectionThreshold::fit([...$seen, ...$notSeen, ...$questionable, ...$questionable])['threshold'])->not->toBeNull();
});

it('finds no threshold when the reserve does not tell seen from not seen', function () {
    $points = [];
    foreach (range(0, 59) as $i) {
        $points[] = ['reserve' => -1.0 + ($i % 20) * 0.1, 'seen' => $i % 3 === 0 ? 0.0 : 1.0];
    }

    expect(DetectionThreshold::fit($points))->threshold->toBeNull()->reliable->toBeFalse();
});

it('puts the observations in bins of reserve', function () {
    $bins = DetectionThreshold::bins([
        ['reserve' => -0.1, 'seen' => 0.0], ['reserve' => -0.2, 'seen' => 0.5], ['reserve' => 0.3, 'seen' => 1.0],
    ]);

    expect($bins)->toBe([
        ['from' => -0.25, 'to' => 0.0, 'seen' => 0.5, 'not_seen' => 1.5],
        ['from' => 0.25, 'to' => 0.5, 'seen' => 1.0, 'not_seen' => 0.0],
    ]);
});

it('offsets the reserves only when chosen and reliable, within MAX_OFFSET', function () {
    $user = User::factory()->create();
    $user->forceFill(['detection_threshold_fit' => ['threshold' => -0.15, 'reliable' => true]]);
    expect(DetectionThreshold::offsetFor($user))->toBe(0.0);

    $user->use_detection_threshold = true;
    expect(DetectionThreshold::offsetFor($user))->toBe(0.15);

    $user->detection_threshold_fit = ['threshold' => 1.2, 'reliable' => true];
    expect(DetectionThreshold::offsetFor($user))->toBe(-DetectionThreshold::MAX_OFFSET);

    $user->detection_threshold_fit = ['threshold' => -0.15, 'reliable' => false];
    expect(DetectionThreshold::offsetFor($user))->toBe(0.0)
        ->and(DetectionThreshold::offsetFor(null))->toBe(0.0);
});

it('fits from the observations and refits when they change', function () {
    $user = eyeObserver();
    eyeObservation($user, 'NGC 1', 3);
    eyeObservation($user, 'NGC 2', Observation::VISIBILITY_NOT_SEEN);

    expect(DetectionThreshold::stale($user))->toBeTrue();
    $fit = DetectionThreshold::compute($user);

    expect($fit['threshold'])->toBeNull()
        ->and($fit['reason'])->toBe('Not enough observations yet.')
        // counted without calculating the reserves
        ->and($fit)->toMatchArray(['seen' => 1, 'not_seen' => 1, 'questionable' => 0])
        ->and(DetectionThreshold::stale($user->fresh()))->toBeFalse();

    eyeObservation($user, 'NGC 3', Observation::VISIBILITY_NOT_SEEN);
    expect(DetectionThreshold::stale($user->fresh()))->toBeTrue();
});

it('shows the threshold on the profile page and saves the choice to use it', function () {
    $user = eyeObserver();
    $points = eyePoints(-0.2, from: -1.4, to: 1.0);
    $user->forceFill([
        'detection_threshold_fit' => [
            ...DetectionThreshold::fit($points),
            'bins' => DetectionThreshold::bins($points),
            'signature' => DetectionThreshold::signature($user),
            'fitted_at' => now()->toIso8601String(),
        ],
    ])->save();
    $this->actingAs($user);

    Livewire::test(OwnEye::class)
        ->call('refresh')
        ->assertSee('You see half of the objects with a contrast reserve of −0.20')
        ->assertSee('You see fainter objects than the model expects.')
        ->assertSee('<polyline', false)
        ->set('useThreshold', true)
        ->assertSee('There, +0.20 is added to every contrast reserve.');

    expect($user->fresh()->use_detection_threshold)->toBeTrue()
        ->and(DetectionThreshold::offsetFor($user->fresh()))->toBe(0.2);
});

it('asks for observations not seen when there are too few', function () {
    $user = eyeObserver();
    eyeObservation($user, 'NGC 1', 3);
    $this->actingAs($user);

    Livewire::test(OwnEye::class)
        ->call('refresh')
        ->assertSee('Record the objects you looked for but did not see');

    expect(DetectionThreshold::stored($user->fresh()))->not->toBeNull();
});
