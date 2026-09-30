<?php

use App\Models\Observation;
use Illuminate\Support\Facades\DB;

function insertDifferentObjectKeyObservation(?string $targetKey, ?string $targetName): int
{
    return (int) DB::table('observations')->insertGetId([
        'observer_username' => 'key-observer', 'observed_on' => '2024-01-01',
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => $targetKey, 'target_name' => $targetName,
        'has_drawing' => false, 'language' => 'en', 'created_at' => now(), 'updated_at' => now(),
    ]);
}

it('keeps the target an observation counts for in different_object_key', function () {
    $key = fn (int $id) => DB::table('observations')->where('id', $id)->value('different_object_key');

    expect($key(insertDifferentObjectKeyObservation(' M 31 ', 'Andromeda')))->toBe('M 31')
        ->and($key(insertDifferentObjectKeyObservation('', ' NGC 7000 ')))->toBe('ngc 7000')
        ->and($key(insertDifferentObjectKeyObservation(null, 'IC 434')))->toBe('ic 434')
        ->and($key(insertDifferentObjectKeyObservation(null, null)))->toBeNull();

    $id = insertDifferentObjectKeyObservation(null, 'IC 434');
    DB::table('observations')->where('id', $id)->update(['target_key' => 'IC 434']);
    expect($key($id))->toBe('IC 434');
});

it('counts different objects on the generated key', function () {
    insertDifferentObjectKeyObservation('M 31', 'M 31');
    insertDifferentObjectKeyObservation(' M 31', 'M 31');
    insertDifferentObjectKeyObservation(null, 'M 32');
    $notSeen = insertDifferentObjectKeyObservation('M 33', 'M 33');
    DB::table('deepsky_observation_details')->insert(['observation_id' => $notSeen, 'visibility' => Observation::VISIBILITY_NOT_SEEN]);

    expect(Observation::countDifferentObjects(DB::table('observations')))->toBe(2);
});
