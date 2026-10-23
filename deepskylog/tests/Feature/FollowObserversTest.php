<?php

use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\MessageBlock;
use App\Models\Observation;
use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Support\Facades\Mail;

// Issue #966: following observers, the "Following" view of the observations,
// the personal RSS feed and the daily digest.

function followedObservation(User $observer, string $object, array $attributes = []): Observation
{
    return Observation::create($attributes + [
        'observer_username' => $observer->username,
        'observer_user_id' => $observer->id,
        'observed_on' => '2026-03-01',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $object,
        'target_name' => $object,
        'description' => 'Seen',
    ]);
}

it('follows and unfollows an observer', function () {
    $user = User::factory()->create();
    $observer = User::factory()->create();

    $this->actingAs($user)->post(route('observer.follow', ['observer' => $observer->slug]))->assertSessionHas('success');
    expect($user->isFollowing($observer))->toBeTrue()
        ->and($observer->followers()->count())->toBe(1);

    // Following twice keeps one row
    $this->actingAs($user)->post(route('observer.follow', ['observer' => $observer->slug]));
    expect(UserFollow::count())->toBe(1);

    $this->actingAs($user)->post(route('observer.unfollow', ['observer' => $observer->slug]))->assertSessionHas('success');
    expect($user->isFollowing($observer))->toBeFalse();
});

it('refuses following yourself or an observer who blocked you', function () {
    $user = User::factory()->create();
    $blocker = User::factory()->create();
    MessageBlock::create(['user_id' => $blocker->id, 'blocked_user_id' => $user->id]);

    $this->actingAs($user)->post(route('observer.follow', ['observer' => $user->slug]))->assertForbidden();
    $this->actingAs($user)->post(route('observer.follow', ['observer' => $blocker->slug]))->assertForbidden();
    expect(UserFollow::count())->toBe(0);
});

it('stops a follow when the followed observer blocks the follower', function () {
    $user = User::factory()->create();
    $observer = User::factory()->create();
    UserFollow::create(['follower_id' => $user->id, 'followed_id' => $observer->id]);

    $this->actingAs($observer)->post(route('messages.block'), ['user_id' => $user->id]);

    expect(UserFollow::count())->toBe(0);
});

it('requires a login to follow', function () {
    $observer = User::factory()->create();

    $this->post(route('observer.follow', ['observer' => $observer->slug]))->assertRedirect(route('login'));
});

it('shows the follow button and the number of followers on the observer page', function () {
    $user = User::factory()->create();
    $observer = User::factory()->create();
    UserFollow::create(['follower_id' => User::factory()->create()->id, 'followed_id' => $observer->id]);

    $this->actingAs($user)->get(route('observer.show', ['observer' => $observer->slug]))
        ->assertOk()
        ->assertSee(route('observer.follow', ['observer' => $observer->slug]), false)
        ->assertSee('1 follower');
});

it('shows observers their followers on their own page', function () {
    $observer = User::factory()->create();
    $fan = User::factory()->create(['name' => 'Ann Follower']);
    UserFollow::create(['follower_id' => $fan->id, 'followed_id' => $observer->id]);

    $this->actingAs($observer)->get(route('observer.show', ['observer' => $observer->slug]))
        ->assertOk()
        ->assertSee('Ann Follower')
        ->assertDontSee(route('observer.follow', ['observer' => $observer->slug]), false);
});

it('only shows the observations of followed observers in the following view', function () {
    $user = User::factory()->create();
    $followed = User::factory()->create();
    $other = User::factory()->create();
    UserFollow::create(['follower_id' => $user->id, 'followed_id' => $followed->id]);
    followedObservation($followed, 'M 65');
    followedObservation($other, 'M 66');

    $response = $this->actingAs($user)->get('/observations?following=1')->assertOk();
    expect($response->viewData('deepsky')->pluck('target_name')->all())->toBe(['M 65']);

    $this->actingAs($user)->get('/observations')->assertOk()->assertViewHas('deepsky', fn ($page) => $page->total() === 2);
});

it('sends guests of the following view to the login page', function () {
    $this->get('/observations?following=1')->assertRedirect(route('login'));
});

it('serves the personal feed only with a valid token', function () {
    $user = User::factory()->create();
    $followed = User::factory()->create();
    $other = User::factory()->create();
    UserFollow::create(['follower_id' => $user->id, 'followed_id' => $followed->id]);
    followedObservation($followed, 'M 65');
    followedObservation($other, 'M 66');

    $token = $user->followFeedToken();
    $this->get(route('feed.following', ['token' => $token]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
        ->assertSee('M 65', false)
        ->assertDontSee('M 66', false);

    $this->get(route('feed.following', ['token' => 'wrong']))->assertNotFound();

    // A new token makes the old address stop working
    $this->actingAs($user)->post(route('feed.following.regenerate'))->assertSessionHas('success');
    $this->get(route('feed.following', ['token' => $token]))->assertNotFound();
    $this->get(route('feed.following', ['token' => $user->fresh()->follow_feed_token]))->assertOk();
});

it('does not show the feed token in the API', function () {
    $user = User::factory()->create();
    $user->followFeedToken();

    expect($user->fresh()->toArray())->not->toHaveKey('follow_feed_token');
});

it('sends a daily digest to followers who asked for it', function () {
    Mail::fake();
    $user = User::factory()->create(['follow_digest' => true, 'sendMail' => true]);
    $quiet = User::factory()->create(['follow_digest' => false]);
    $followed = User::factory()->create(['name' => 'Jane Sketcher']);
    foreach ([$user, $quiet] as $follower) {
        UserFollow::create(['follower_id' => $follower->id, 'followed_id' => $followed->id]);
    }
    followedObservation($followed, 'M 65');
    followedObservation($followed, 'M 66');
    // Older than the last day: not in the first digest
    followedObservation($followed, 'M 67')->forceFill(['created_at' => now()->subDays(3)])->save();
    // Observations of the second the digest is made in go into the next one
    $this->travel(1)->seconds();

    $this->artisan('follows:send-digest --apply')->assertSuccessful();

    $message = Message::where('receiver', $user->username)->sole();
    expect($message->message)->toContain('Jane Sketcher')
        ->and($message->message)->toContain('2 new observations');
    expect(Message::where('receiver', $quiet->username)->count())->toBe(0);
    Mail::assertQueued(MessageReceived::class, fn (MessageReceived $mail) => $mail->hasTo($user->email));

    // A second run on the same day sends nothing
    followedObservation($followed, 'M 104');
    $this->artisan('follows:send-digest --apply')->assertSuccessful();
    expect(Message::where('receiver', $user->username)->count())->toBe(1);

    // The next day's digest only has what is new since
    $this->travel(1)->days();
    $this->artisan('follows:send-digest --apply')->assertSuccessful();
    $latest = Message::where('receiver', $user->username)->orderByDesc('id')->first();
    expect(Message::where('receiver', $user->username)->count())->toBe(2)
        ->and($latest->message)->toContain('1 new observation');
});

it('sends no digest when there is nothing new', function () {
    $user = User::factory()->create(['follow_digest' => true]);
    UserFollow::create(['follower_id' => $user->id, 'followed_id' => User::factory()->create()->id]);

    $this->artisan('follows:send-digest --apply')->assertSuccessful();

    expect(Message::count())->toBe(0)
        ->and($user->fresh()->follow_digest_sent_at)->not->toBeNull();
});

it('does not send anything in a dry run', function () {
    $user = User::factory()->create(['follow_digest' => true]);
    $followed = User::factory()->create();
    UserFollow::create(['follower_id' => $user->id, 'followed_id' => $followed->id]);
    followedObservation($followed, 'M 65');
    $this->travel(1)->seconds();

    $this->artisan('follows:send-digest')->expectsOutputToContain('Digests to send: 1')->assertSuccessful();

    expect(Message::count())->toBe(0);
});

it('removes the follows of a deleted account in both directions', function () {
    $user = User::factory()->create();
    $a = User::factory()->create();
    $b = User::factory()->create();
    UserFollow::create(['follower_id' => $user->id, 'followed_id' => $a->id]);
    UserFollow::create(['follower_id' => $b->id, 'followed_id' => $user->id]);

    app(\Laravel\Jetstream\Contracts\DeletesUsers::class)->delete($user);

    expect(UserFollow::count())->toBe(0);
});
