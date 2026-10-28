<?php

use App\Models\ObservationLike;
use App\Models\User;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Database\Seeders\GroupSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Jetstream\Http\Livewire\DeleteUserForm;
use Livewire\Livewire;

function insertAccountObservation(User $user, string $target, ?string $drawing = null): int
{
    return (int) DB::table('observations')->insertGetId([
        'observer_user_id' => $user->id, 'observer_username' => $user->username, 'observed_on' => '2026-01-01',
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => $target, 'target_name' => $target,
        'has_drawing' => $drawing !== null, 'drawing_filename' => $drawing, 'language' => 'en',
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

function insertAccountList(User $owner, string $name): int
{
    return (int) DB::table('observing_lists')->insertGetId([
        'owner_user_id' => $owner->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'public' => true,
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

beforeEach(function () {
    $this->seed(GroupSeeder::class);
    Storage::fake('public');
});

it('lets observers delete their account with everything they recorded', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Storage::disk('public')->put('observations/sketch.jpg', 'x');
    $observation = insertAccountObservation($user, 'M 31', 'observations/sketch.jpg');
    $kept = insertAccountObservation($other, 'M 31');
    DB::table('observation_likes')->insert(['user_id' => $other->id, 'observation_type' => ObservationLike::TYPE_OBSERVATION,
        'observation_id' => $observation, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('observation_likes')->insert(['user_id' => $user->id, 'observation_type' => ObservationLike::TYPE_OBSERVATION,
        'observation_id' => $kept, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('sketch_of_the_week')->insert(['observation_id' => $observation, 'user_id' => $user->id, 'date' => '2026-01-05']);

    $session = (int) DB::table('observation_sessions')->insertGetId(['name' => 'Night', 'observerid' => $user->username,
        'begindate' => '2026-01-01 20:00:00', 'enddate' => '2026-01-02 02:00:00', 'locationid' => 0, 'active' => 1]);
    $othersSession = (int) DB::table('observation_sessions')->insertGetId(['name' => 'Together', 'observerid' => $other->username,
        'begindate' => '2026-01-01 20:00:00', 'enddate' => '2026-01-02 02:00:00', 'locationid' => 0, 'active' => 1]);
    DB::table('sessionObservers')->insert([['sessionid' => $session, 'observer' => $other->username], ['sessionid' => $othersSession, 'observer' => $user->username]]);

    InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);
    LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);

    $ownList = insertAccountList($user, 'Mine');
    $othersList = insertAccountList($other, 'Theirs');
    DB::table('observing_list_items')->insert(['observing_list_id' => $othersList, 'object_name' => 'M 33', 'added_by_user_id' => $user->id,
        'source_observation_id' => $observation, 'created_at' => now(), 'updated_at' => now()]);

    $this->actingAs($user);
    Livewire::test(DeleteUserForm::class)
        ->set('password', 'password')
        ->call('deleteUser');

    expect(User::find($user->id))->toBeNull()
        ->and(DB::table('observations')->where('observer_username', $user->username)->exists())->toBeFalse()
        ->and(DB::table('observations')->where('id', $kept)->exists())->toBeTrue()
        ->and(DB::table('observation_likes')->count())->toBe(0)
        ->and(DB::table('sketch_of_the_week')->count())->toBe(0)
        ->and(DB::table('observation_sessions')->pluck('id')->all())->toBe([$othersSession])
        ->and(DB::table('sessionObservers')->count())->toBe(0)
        ->and(DB::table('instruments')->where('user_id', $user->id)->exists())->toBeFalse()
        ->and(DB::table('locations')->where('user_id', $user->id)->exists())->toBeFalse()
        ->and(DB::table('observing_lists')->pluck('id')->all())->toBe([$othersList]);

    // An item added to the list of another observer stays, as added by its owner
    $item = DB::table('observing_list_items')->where('observing_list_id', $othersList)->first();
    expect((int) $item->added_by_user_id)->toBe($other->id)
        ->and($item->source_observation_id)->toBeNull();

    Storage::disk('public')->assertMissing('observations/sketch.jpg');
    $this->assertGuest();
});

it('does not delete the account with a wrong password', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    Livewire::test(DeleteUserForm::class)
        ->set('password', 'wrong-password')
        ->call('deleteUser')
        ->assertHasErrors(['password']);

    expect(User::find($user->id))->not->toBeNull();
});

it('shows the delete form to observers but not to the first user', function () {
    $first = User::factory()->create()->fresh();
    $user = User::factory()->create()->fresh();

    $this->actingAs($user)->get('/user/profile')->assertOk()->assertSeeLivewire(DeleteUserForm::class);
    $this->actingAs($first)->get('/user/profile')->assertOk()->assertDontSeeLivewire(DeleteUserForm::class);
});
