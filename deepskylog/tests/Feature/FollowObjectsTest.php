<?php

use App\Models\Message;
use App\Models\ObjectFollow;
use App\Models\Observation;
use App\Models\User;
use App\Models\UserFollow;
use App\Services\ObjectMerger;
use App\Services\PushNotifications;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Following objects: the Follow button of the object page, the objects in
// the "Following" view of the observations, the personal RSS feed and the
// daily digest (with a push notification).

function foObject(string $name): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => 'EMINB', 'con' => 'MON',
        'ra'            => 6.65, 'decl' => 8.73, 'mag' => 9.0, 'subr' => 13.0, 'pa' => 0, 'urano' => 0,
        'urano_new'     => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 120.0, 'diam2' => 60.0,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP'         => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function foComet(string $name): int
{
    return DB::table('cometobjects')->insertGetId(['name' => $name, 'slug' => Str::slug($name), 'icqname' => '', 'timestamp' => now()]);
}

function foObservation(User $observer, string $table, string $key, array $attributes = []): Observation
{
    return Observation::create($attributes + [
        'observer_username' => $observer->username,
        'observer_user_id' => $observer->id,
        'observed_on' => '2026-03-01',
        'target_type' => ObjectFollow::TABLES[$table],
        'target_table' => $table,
        'target_key' => $key,
        'target_name' => $key,
        'description' => 'Seen',
    ]);
}

function foFollow(User $user, string $table, string $key): ObjectFollow
{
    return ObjectFollow::create(['user_id' => $user->id, 'target_type' => ObjectFollow::TABLES[$table], 'target_table' => $table, 'target_key' => $key]);
}

beforeEach(fn () => foObject('NGC 2261'));

it('follows and unfollows an object', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('object.follow'), ['table' => 'objects', 'key' => 'NGC 2261'])->assertSessionHas('success');
    $this->actingAs($user)->post(route('object.follow'), ['table' => 'objects', 'key' => 'NGC 2261']);
    expect(ObjectFollow::where('user_id', $user->id)->sole()->target_type)->toBe('deepsky')
        ->and(ObjectFollow::isFollowedBy($user, 'objects', 'NGC 2261'))->toBeTrue();

    $this->actingAs($user)->post(route('object.unfollow'), ['table' => 'objects', 'key' => 'NGC 2261'])->assertSessionHas('success');
    expect(ObjectFollow::count())->toBe(0);
});

it('refuses objects that do not exist and guests', function () {
    $user = User::factory()->create();

    $this->post(route('object.follow'), ['table' => 'objects', 'key' => 'NGC 2261'])->assertRedirect(route('login'));
    $this->actingAs($user)->post(route('object.follow'), ['table' => 'objects', 'key' => 'NGC 99999'])->assertNotFound();
    $this->actingAs($user)->post(route('object.follow'), ['table' => 'users', 'key' => '1'])->assertSessionHasErrors('table');
    expect(ObjectFollow::count())->toBe(0);
});

it('follows a comet by its id', function () {
    $user = User::factory()->create();
    $id = foComet('C/2026 A1 (Test)');

    $this->actingAs($user)->post(route('object.follow'), ['table' => 'cometobjects', 'key' => (string) $id])->assertSessionHas('success');

    expect(ObjectFollow::sole()->only(['target_type', 'target_key']))->toBe(['target_type' => 'comet', 'target_key' => (string) $id])
        ->and(ObjectFollow::listFor($user)->all())->toBe([['name' => 'C/2026 A1 (Test)', 'slug' => 'c2026-a1-test']]);
});

it('shows the follow button of the object page', function () {
    $user = User::factory()->create();
    $target = ['table' => 'objects', 'key' => 'NGC 2261'];
    $render = fn () => Blade::render('<x-follow-object :target="$target" />', ['target' => $target]);

    expect($render())->toBe('');

    $this->actingAs($user);
    expect($render())->toContain(route('object.follow'))->not->toContain(route('object.unfollow'));

    foFollow($user, 'objects', 'NGC 2261');
    expect($render())->toContain(route('object.unfollow'));
});

it('shows the observations of followed objects by others in the following view and the feed', function () {
    foObject('M 65');
    $user = User::factory()->create();
    $other = User::factory()->create();
    foFollow($user, 'objects', 'NGC 2261');
    foObservation($other, 'objects', 'NGC 2261');
    foObservation($other, 'objects', 'M 65');
    // One's own observations of a followed object are no news
    foObservation($user, 'objects', 'NGC 2261', ['description' => 'Mine']);

    $response = $this->actingAs($user)->get('/observations?following=1')->assertOk();
    expect($response->viewData('deepsky')->pluck('observer_username')->all())->toBe([$other->username]);

    $this->get(route('feed.following', ['token' => $user->followFeedToken()]))
        ->assertOk()
        ->assertSee('NGC 2261', false)
        ->assertDontSee('M 65', false);
});

it('combines followed observers and objects', function () {
    foObject('M 65');
    $user = User::factory()->create();
    $followed = User::factory()->create();
    $other = User::factory()->create();
    UserFollow::create(['follower_id' => $user->id, 'followed_id' => $followed->id]);
    foFollow($user, 'objects', 'NGC 2261');
    foObservation($followed, 'objects', 'M 65');
    foObservation($other, 'objects', 'NGC 2261');
    foObservation($other, 'objects', 'M 65');

    expect($user->followedObservations()->count())->toBe(2)
        ->and($user->followedObserverObservations()->count())->toBe(1)
        ->and($user->followedObjectObservations()->count())->toBe(1);
});

it('sends the new observations and drawings of followed objects in the digest, with a push notification', function () {
    $pushed = new class extends PushNotifications {
        public array $sent = [];

        public function deliver(array $subscriptions, string $title, string $body, string $url, ?string $tag = null, int $ttl = self::DEFAULT_TTL): int
        {
            $this->sent[] = compact('title', 'body', 'url', 'tag');

            return 0;
        }
    };
    app()->instance(PushNotifications::class, $pushed);

    $user = User::factory()->create(['follow_digest' => true, 'sendMail' => false]);
    $other = User::factory()->create();
    foFollow($user, 'objects', 'NGC 2261');
    foObservation($other, 'objects', 'NGC 2261', ['has_drawing' => true]);
    foObservation($other, 'objects', 'NGC 2261');
    foObservation($user, 'objects', 'NGC 2261');
    $this->travel(1)->seconds();

    $this->artisan('follows:send-digest --apply')->assertSuccessful();

    $message = Message::where('receiver', $user->username)->sole();
    expect($message->message)->toContain('NGC 2261')
        ->and($message->message)->toContain('2 new observations, 1 with a drawing')
        ->and($message->message)->toContain(route('object.show', ['slug' => 'ngc-2261']));
    expect($pushed->sent)->toHaveCount(1)
        ->and($pushed->sent[0]['body'])->toBe('2 new observations')
        ->and($pushed->sent[0]['tag'])->toBe('follow-digest');
});

it('moves the follows of a renamed or merged object and forgets those of a deleted one', function () {
    foObject('Hubble Variable Nebula');
    $a = User::factory()->create();
    $b = User::factory()->create();
    foFollow($a, 'objects', 'Hubble Variable Nebula');
    foFollow($b, 'objects', 'Hubble Variable Nebula');
    foFollow($b, 'objects', 'NGC 2261');

    ObjectMerger::merge('Hubble Variable Nebula', 'NGC 2261');

    expect(ObjectFollow::where('target_key', 'NGC 2261')->pluck('user_id')->sort()->values()->all())->toBe([$a->id, $b->id])
        ->and(ObjectFollow::count())->toBe(2);

    foObject('NGC 9999');
    foFollow($a, 'objects', 'NGC 9999');
    ObjectMerger::delete('NGC 9999');
    expect(ObjectFollow::where('target_key', 'NGC 9999')->count())->toBe(0);
});

it('lists the followed objects on the observer\'s own page and removes them with the account', function () {
    $user = User::factory()->create();
    foFollow($user, 'objects', 'NGC 2261');

    $this->actingAs($user)->get(route('observer.show', ['observer' => $user->slug]))
        ->assertOk()
        ->assertSee('Objects you follow')
        ->assertSee(route('object.show', ['slug' => 'ngc-2261']), false);

    app(\Laravel\Jetstream\Contracts\DeletesUsers::class)->delete($user);
    expect(ObjectFollow::count())->toBe(0);
});
