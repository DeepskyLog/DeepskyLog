<?php

use App\Livewire\EyepieceTable;
use App\Models\Eyepiece;
use App\Models\EyepieceMake;
use App\Models\EyepieceType;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\LensMake;
use App\Models\Observation;
use App\Models\User;
use Database\Factories\EyepieceFactory;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

function insertRankObservation(string $username, string $targetKey, ?int $visibility = null, string $type = 'deepsky'): void
{
    $id = DB::table('observations')->insertGetId([
        'observer_username' => $username,
        'observed_on' => now()->toDateString(),
        'target_type' => $type,
        'target_table' => $type === 'comet' ? 'cometobjects' : 'objects',
        'target_key' => $targetKey,
        'target_name' => $targetKey,
        'has_drawing' => false,
        'language' => 'en',
        'legacy_source' => 'deepsky',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    if ($visibility !== null) {
        DB::table('deepsky_observation_details')->insert(['observation_id' => $id, 'visibility' => $visibility]);
    }
}

it('moves the types of a deleted eyepiece make to the new make', function () {
    $admin = $this->createUserAndAssignToTeam('Administrators');
    $old = EyepieceMake::create(['name' => 'Old make']);
    $new = EyepieceMake::create(['name' => 'New make']);
    $moved = EyepieceType::create(['name' => 'Nagler', 'eyepiece_makes_id' => $old->id]);
    $merged = EyepieceType::create(['name' => 'Delos', 'eyepiece_makes_id' => $old->id]);
    $existing = EyepieceType::create(['name' => 'Delos', 'eyepiece_makes_id' => $new->id]);
    $nagler = EyepieceFactory::new()->create(['make_id' => $old->id, 'type_id' => $moved->id]);
    $delos = EyepieceFactory::new()->create(['make_id' => $old->id, 'type_id' => $merged->id]);

    $this->actingAs($admin)
        ->post(route('eyepiecemake.destroy'), ['id' => $old->id, 'new_make' => $new->id])
        ->assertRedirect(route('eyepiece.indexAdmin'));

    expect(EyepieceMake::find($old->id))->toBeNull()
        ->and($moved->fresh()->eyepiece_makes_id)->toBe($new->id)
        ->and(EyepieceType::find($merged->id))->toBeNull()
        ->and($nagler->fresh()->make_id)->toBe($new->id)
        ->and($nagler->fresh()->type_id)->toBe($moved->id)
        ->and($delos->fresh()->type_id)->toBe($existing->id);
});

it('validates deleting and renaming a make', function () {
    $admin = $this->createUserAndAssignToTeam('Administrators');
    $make = LensMake::create(['name' => 'A lens make']);
    $other = LensMake::create(['name' => 'Another lens make']);

    $this->actingAs($admin)
        ->post(route('lensmake.destroy'), ['id' => $make->id])
        ->assertSessionHasErrors('new_make');
    $this->post(route('lensmake.destroy'), ['id' => $make->id, 'new_make' => $make->id])
        ->assertSessionHasErrors('new_make');
    $this->post(route('lensmake.store'), ['id' => $make->id, 'lens_make' => $other->name])
        ->assertSessionHasErrors('lens_make');

    expect(LensMake::find($make->id)->name)->toBe('A lens make');
});

it('gives equipment of the same user with the same name different slugs', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $first = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Dobson', 'slug' => null]);
    $second = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Dobson', 'slug' => null]);
    $foreign = InstrumentFactory::new()->create(['user_id' => $other->id, 'observer' => $other->username, 'name' => 'Dobson', 'slug' => null]);

    expect($first->slug)->toBe('dobson')
        ->and($second->slug)->toBe('dobson-2')
        ->and($foreign->slug)->toBe('dobson');
});

it('answers 404 for equipment that does not exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/eyepiece/'.$user->slug.'/does-not-exist/edit')->assertNotFound();
    $this->get('/instrument/no-such-user/does-not-exist')->assertNotFound();
});

it('ranks observers by all their observations, the same way on /observers and the observer page', function () {
    $alice = User::factory()->create(['name' => 'Alice', 'username' => 'alice-rank']);
    $bob = User::factory()->create(['name' => 'Bob', 'username' => 'bob-rank']);
    $carol = User::factory()->create(['name' => 'Carol', 'username' => 'carol-rank']);
    $dave = User::factory()->create(['name' => 'Dave', 'username' => 'dave-rank']);
    $erin = User::factory()->create(['name' => 'Erin', 'username' => 'erin-rank']);

    foreach (['M 1', 'M 2', 'M 2'] as $object) {
        insertRankObservation($alice->username, $object);
    }
    insertRankObservation($alice->username, 'M 3', Observation::VISIBILITY_NOT_SEEN);
    foreach (['M 1', 'M 2'] as $object) {
        insertRankObservation($bob->username, $object);
        insertRankObservation($carol->username, $object);
    }
    // Comet observations count for the rank as well
    foreach (['12', '12', '34'] as $comet) {
        insertRankObservation($erin->username, $comet, type: 'comet');
    }

    expect($alice->getRank())->toBe(1)
        ->and($erin->getRank())->toBe(2)
        ->and($bob->getRank())->toBe(3)
        ->and($carol->getRank())->toBe(3)
        ->and($dave->getRank())->toBe(0)
        ->and(User::rankedObserverCount())->toBe(4)
        ->and(User::topTenObserverUsernames())->toContain($erin->username)
        ->and(Observation::countDifferentObjects(Observation::where('observer_username', $alice->username)))->toBe(2);

    $rows = fn($search) => collect($this->get(route('observers.index', ['observer' => $search]))->assertOk()->viewData('observerRows')->items())
        ->map(fn($row) => [$row['username'], $row['rank'], $row['observations_count'], $row['different_objects_count']])->all();

    expect($rows('Carol'))->toBe([[$carol->username, 3, 2, 2]])
        ->and($rows('Erin'))->toBe([[$erin->username, 2, 3, 2]]);
});

it('ranks observers per category on the observer page', function () {
    $alice = User::factory()->create(['name' => 'Alice', 'username' => 'alice-category']);
    $bob = User::factory()->create(['name' => 'Bob', 'username' => 'bob-category']);
    $carol = User::factory()->create(['name' => 'Carol', 'username' => 'carol-category']);

    DB::table('objects')->insert(collect(['name' => 'STF 1', 'slug' => 'stf-1', 'type' => 'DS', 'con' => 'AND', 'ra' => 0, 'decl' => 0, 'mag' => 4.0, 'subr' => 0, 'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'diam1' => 0, 'diam2' => 0, 'SBObj' => 0, 'timestamp' => now()])
        ->merge(array_fill_keys(['millenium', 'taki', 'description', 'psa', 'torresB', 'torresBC', 'torresC', 'milleniumbase'], ''))
        ->merge(array_fill_keys(['DSLDL', 'DSLDP', 'DSLLL', 'DSLLP', 'DSLOL', 'DSLOP', 'DeepskyHunter', 'Interstellarum'], '0'))
        ->all());

    foreach (['M 1', 'M 2', 'M 3'] as $object) {
        insertRankObservation($bob->username, $object);
    }
    // Alice has more deep-sky observations, but two are of a double star
    foreach (['M 1', 'M 2', 'STF 1', 'STF 1'] as $object) {
        insertRankObservation($alice->username, $object);
    }
    insertRankObservation($carol->username, 'STF 1');
    insertRankObservation($carol->username, '12', type: 'comet');

    $ranks = fn(User $user) => collect($user->getCategoryRanks())->map(fn($rank) => [$rank['rank'], $rank['observers']])->all();

    expect($ranks($bob))->toMatchArray(['deepsky' => [1, 2], 'double' => [0, 2], 'comet' => [0, 1], 'planets' => [0, 0]])
        ->and($ranks($alice))->toMatchArray(['deepsky' => [2, 2], 'double' => [1, 2], 'comet' => [0, 1]])
        ->and($ranks($carol))->toMatchArray(['deepsky' => [0, 2], 'double' => [2, 2], 'comet' => [1, 1]]);

    $this->get(route('observer.show', $carol->slug))->assertOk()->assertViewHas('categoryRanks');
});

it('does not show 60 minutes in the field of view', function () {
    $instrument = new Instrument(['focal_length_mm' => 1000, 'aperture_mm' => 200]);
    $eyepiece = new Eyepiece(['focal_length_mm' => 10, 'apparentFOV' => 99.97, 'field_stop_mm' => 0]);

    expect($instrument->field_of_view($eyepiece))->toBe("1° 00'")
        ->and($instrument->exit_pupil($eyepiece, new Lens(['factor' => 0])))->toBe('2mm');
});

it('makes only an own eyepiece the default', function () {
    $user = User::factory()->create();
    $own = EyepieceFactory::new()->create(['user_id' => $user->id]);
    $foreign = EyepieceFactory::new()->create();

    $this->actingAs($user);
    (new EyepieceTable)->clickToMakeDefault($foreign->id);
    expect($user->fresh()->stdeyepiece)->toBeNull();

    (new EyepieceTable)->clickToMakeDefault($own->id);
    expect($user->fresh()->stdeyepiece)->toBe($own->id);
});

it('forgets the cached observer statistics when an observation is saved or deleted', function () {
    $alice = User::factory()->create(['username' => 'alice-stats']);
    $bob = User::factory()->create(['username' => 'bob-stats']);
    $cache = fn() => [Cache::has('observer_stats:'.$alice->id), Cache::has('observer_stats:'.$bob->id)];
    $fill = function () use ($alice, $bob) {
        Cache::put('observer_stats:'.$alice->id, ['x' => 1], now()->addHour());
        Cache::put('observer_stats:'.$bob->id, ['x' => 1], now()->addHour());
    };

    $fill();
    // Only the username is known, as for many migrated observations
    $observation = Observation::create([
        'observer_username' => $alice->username,
        'observed_on' => now()->toDateString(),
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'M 1',
        'target_name' => 'M 1',
        'language' => 'en',
    ]);
    expect($cache())->toBe([false, true]);

    // Moving the observation to another observer clears both
    $fill();
    $observation->update(['observer_username' => $bob->username]);
    expect($cache())->toBe([false, false]);

    $fill();
    $observation->delete();
    expect($cache())->toBe([true, false]);
});
