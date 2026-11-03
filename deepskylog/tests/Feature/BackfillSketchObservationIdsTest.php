<?php

use App\Models\Observation;
use App\Models\User;
use Database\Seeders\BackfillSketchObservationIdsSeeder;
use Illuminate\Support\Facades\DB;

function backfillObservation(User $observer, string $source, int $legacyId, bool $drawing = true): Observation
{
    return Observation::create([
        'observer_username' => $observer->username,
        'observer_user_id'  => $observer->id,
        'observed_on'       => '2020-03-01',
        'target_type'       => $source === 'comet' ? 'comet' : 'deepsky',
        'target_table'      => $source === 'comet' ? 'cometobjects' : 'objects',
        'target_key'        => 'M 65',
        'target_name'       => 'M 65',
        'description'       => 'Seen',
        'has_drawing'       => $drawing,
        'legacy_source'     => $source,
        'legacy_id'         => $legacyId,
    ]);
}

function backfillSketch(int $observationId, User $observer, string $table = 'sketch_of_the_week'): int
{
    return DB::table($table)->insertGetId([
        'observation_id' => $observationId,
        'user_id'        => $observer->id,
        'date'           => '2020-03-08',
    ]);
}

function sketchTarget(int $sketchId, string $table = 'sketch_of_the_week'): int
{
    return (int) DB::table($table)->where('id', $sketchId)->value('observation_id');
}

it('translates old ids even when they are also the new id of another drawing', function () {
    $ann = User::factory()->create();
    $bob = User::factory()->create();

    $first  = backfillObservation($bob, 'deepsky', 500);           // new id 1, a drawing by Bob
    $annDs  = backfillObservation($ann, 'deepsky', $first->id);    // Ann's drawing had the old id 1
    $comet  = backfillObservation($ann, 'comet', 7);

    $deepskySketch = backfillSketch($first->id, $ann);  // old id 1: Ann's drawing, not Bob's
    $cometSketch   = backfillSketch(-7, $ann);

    $this->seed(BackfillSketchObservationIdsSeeder::class);

    expect(sketchTarget($deepskySketch))->toBe($annDs->id)
        ->and(sketchTarget($cometSketch))->toBe($comet->id);

    // A second run changes nothing.
    $this->seed(BackfillSketchObservationIdsSeeder::class);

    expect(sketchTarget($deepskySketch))->toBe($annDs->id)
        ->and(sketchTarget($cometSketch))->toBe($comet->id);
});

it('keeps translated sketches and repairs the ones the old fix migration broke', function () {
    $ann = User::factory()->create();
    $bob = User::factory()->create();

    $annComet = backfillObservation($ann, 'comet', 30);
    $bobDs    = backfillObservation($bob, 'deepsky', $annComet->id, drawing: false);
    $annDs    = backfillObservation($ann, 'deepsky', 900);

    $right  = backfillSketch($annDs->id, $ann);
    $broken = backfillSketch($bobDs->id, $ann);  // was $annComet->id, "corrected" through legacy_id

    $this->seed(BackfillSketchObservationIdsSeeder::class);

    expect(sketchTarget($right))->toBe($annDs->id)
        ->and(sketchTarget($broken))->toBe($annComet->id);
});

it('leaves a sketch without a drawing of its observer as it is', function () {
    $ann = User::factory()->create();
    $bob = User::factory()->create();

    $bobDs  = backfillObservation($bob, 'deepsky', 400);
    $sketch = backfillSketch($bobDs->id, $ann, 'sketch_of_the_month');

    $this->seed(BackfillSketchObservationIdsSeeder::class);

    expect(sketchTarget($sketch, 'sketch_of_the_month'))->toBe($bobDs->id);
});

it('prefers the old id to undoing the old fix migration when both are drawings of the observer', function () {
    $ann = User::factory()->create();
    $bob = User::factory()->create();

    $annLater = backfillObservation($ann, 'deepsky', 800);              // what undoing would give
    $annOld   = backfillObservation($ann, 'deepsky', 3);                // old id 3 is what the sketch held
    $bobDs    = backfillObservation($bob, 'deepsky', $annLater->id);    // new id 3, Bob's
    expect($bobDs->id)->toBe(3);

    $sketch = backfillSketch($bobDs->id, $ann);

    $this->seed(BackfillSketchObservationIdsSeeder::class);

    expect(sketchTarget($sketch))->toBe($annOld->id);
});
