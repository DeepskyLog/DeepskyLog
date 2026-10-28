<?php

use App\Models\Instrument;
use Database\Factories\InstrumentFactory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Insert an observation the way the old site does: with SQL, without the
 * model events, and a row in legacy_observation_changes.
 */
function insertLegacySiteObservation(User $user, int $instrumentId, string $object = 'NGC 7000'): int
{
    $id = DB::table('observations')->insertGetId([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'instrument_id' => $instrumentId,
        'observed_on' => '2026-10-20',
        'description' => 'Seen on the old site',
        'language' => 'en',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $object,
        'target_name' => $object,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    recordLegacySiteChange($id, 'created');

    return $id;
}

function recordLegacySiteChange(int $id, string $action): void
{
    $original = $action === 'created' ? null : json_encode((array) DB::table('observations')->where('id', $id)->first());

    DB::table('legacy_observation_changes')->insert([
        'observation_id' => $id,
        'action' => $action,
        'original' => $original,
        'created_at' => now(),
    ]);
}

function updateLegacySiteObservation(int $id, array $values): void
{
    recordLegacySiteChange($id, 'updated');
    DB::table('observations')->where('id', $id)->update($values);
}

function legacyInstrumentCount(Instrument $instrument): int
{
    return (int) DB::table('instruments')->where('id', $instrument->id)->value('observations');
}

it('counts a new observation of the old site once, however many fields it saved after it', function () {
    $user = User::factory()->create(['username' => 'legacy-observer']);
    $instrument = InstrumentFactory::new()->create(['observer' => '', 'observations' => 0]);

    $id = insertLegacySiteObservation($user, $instrument->id);
    updateLegacySiteObservation($id, ['seeing' => 2]);
    updateLegacySiteObservation($id, ['sqm' => 21.2]);

    $this->artisan('observations:apply-legacy-changes')->assertSuccessful();
    expect(legacyInstrumentCount($instrument))->toBe(0)
        ->and(DB::table('legacy_observation_changes')->count())->toBe(3);

    $this->artisan('observations:apply-legacy-changes --apply')->assertSuccessful();

    expect(legacyInstrumentCount($instrument))->toBe(1)
        ->and(DB::table('object_observation_stats')->where('object_name', 'NGC 7000')->value('observations'))->toBe(1)
        ->and(DB::table('legacy_observation_changes')->count())->toBe(0);
});

it('moves the counts when the old site changes the instrument and the object', function () {
    $user = User::factory()->create(['username' => 'legacy-observer']);
    $first = InstrumentFactory::new()->create(['observer' => '', 'observations' => 0]);
    $second = InstrumentFactory::new()->create(['observer' => '', 'observations' => 0]);

    $id = insertLegacySiteObservation($user, $first->id);
    $this->artisan('observations:apply-legacy-changes --apply')->assertSuccessful();

    updateLegacySiteObservation($id, ['instrument_id' => $second->id]);
    updateLegacySiteObservation($id, ['target_name' => 'NGC 7001', 'target_key' => 'NGC 7001']);
    $this->artisan('observations:apply-legacy-changes --apply')->assertSuccessful();

    expect(legacyInstrumentCount($first))->toBe(0)
        ->and(legacyInstrumentCount($second))->toBe(1)
        ->and(DB::table('object_observation_stats')->where('object_name', 'NGC 7000')->exists())->toBeFalse()
        ->and(DB::table('object_observation_stats')->where('object_name', 'NGC 7001')->value('observations'))->toBe(1);
});

it('removes the counts, likes and comments of an observation the old site deleted', function () {
    $user = User::factory()->create(['username' => 'legacy-observer']);
    $instrument = InstrumentFactory::new()->create(['observer' => '', 'observations' => 0]);

    $id = insertLegacySiteObservation($user, $instrument->id);
    $this->artisan('observations:apply-legacy-changes --apply')->assertSuccessful();

    DB::table('observation_likes')->insert(['user_id' => $user->id, 'observation_type' => 'observation', 'observation_id' => $id, 'created_at' => now(), 'updated_at' => now()]);
    recordLegacySiteChange($id, 'deleted');
    DB::table('observations')->where('id', $id)->delete();
    $this->artisan('observations:apply-legacy-changes --apply')->assertSuccessful();

    expect(legacyInstrumentCount($instrument))->toBe(0)
        ->and(DB::table('observation_likes')->where('observation_id', $id)->count())->toBe(0)
        ->and(DB::table('object_observation_stats')->where('object_name', 'NGC 7000')->exists())->toBeFalse();
});

it('counts nothing for an observation the old site added and deleted before the run', function () {
    $user = User::factory()->create(['username' => 'legacy-observer']);
    $instrument = InstrumentFactory::new()->create(['observer' => '', 'observations' => 3]);

    $id = insertLegacySiteObservation($user, $instrument->id);
    recordLegacySiteChange($id, 'deleted');
    DB::table('observations')->where('id', $id)->delete();
    $this->artisan('observations:apply-legacy-changes --apply')->assertSuccessful();

    expect(legacyInstrumentCount($instrument))->toBe(3);
});
