<?php

use App\Models\Observation;
use App\Models\User;
use App\Services\Observations\ReadObservations;
use Illuminate\Support\Facades\DB;

// The observations an observer has not read yet, with the mark the old site
// keeps for its "Latest observations" (user_preferences legacy_last_read_observation).

function unreadTestObservation(User $observer, string $object): Observation
{
    return Observation::create([
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

function storedReadMark(User $user): ?string
{
    return DB::table('user_preferences')->where('user_id', $user->id)->where('key', ReadObservations::PREFERENCE)->value('value');
}

it('starts an observer without a mark with everything read', function () {
    $user = User::factory()->create();
    $last = unreadTestObservation(User::factory()->create(), 'M 31');

    expect(ReadObservations::mark($user))->toBe($last->id)
        ->and(storedReadMark($user))->toBe((string) $last->id)
        ->and(ReadObservations::unreadCount($user))->toBe(0);
});

it('counts the observations above the mark, not the observer\'s own', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $read = unreadTestObservation($other, 'M 31');
    ReadObservations::mark($user);

    unreadTestObservation($other, 'M 33');
    unreadTestObservation($other, 'M 42');
    unreadTestObservation($user, 'M 57');

    expect(ReadObservations::unreadCount($user))->toBe(2)
        ->and(ReadObservations::mark($user))->toBe($read->id);
});

it('lists only the unread observations with ?new=1 and marks them', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    unreadTestObservation($other, 'NGC 7000');
    ReadObservations::mark($user);
    $new = unreadTestObservation($other, 'NGC 891');

    $this->actingAs($user)->get('/observations?new=1')
        ->assertOk()
        ->assertSee('NGC 891')
        ->assertDontSee('NGC 7000')
        ->assertSee('Mark all read')
        ->assertSee('Mark this one and the older ones as read');

    $this->actingAs($user)->from('/observations?new=1')
        ->post(route('observations.mark-read'), ['up_to' => $new->id])
        ->assertRedirect('/observations?new=1');

    expect(ReadObservations::unreadCount($user))->toBe(0);
    $this->actingAs($user)->get('/observations?new=1')->assertSee('No new observations.');
});

it('marks only up to the posted id, so observations added meanwhile stay unread', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    unreadTestObservation($other, 'M 1');
    ReadObservations::mark($user);
    $shown = unreadTestObservation($other, 'M 2');
    $later = unreadTestObservation($other, 'M 3');

    $this->actingAs($user)->post(route('observations.mark-read'), ['up_to' => $shown->id]);

    expect(ReadObservations::mark($user))->toBe($shown->id)
        ->and(ReadObservations::unreadCount($user))->toBe(1);

    // Never back, never past the newest observation
    $this->actingAs($user)->post(route('observations.mark-read'), ['up_to' => 1]);
    expect(ReadObservations::mark($user))->toBe($shown->id);
    $this->actingAs($user)->post(route('observations.mark-read'), ['up_to' => $later->id + 1000]);
    expect(ReadObservations::mark($user))->toBe($later->id);
});

it('does not mark anything through a GET or for a guest', function () {
    $this->post(route('observations.mark-read'), ['up_to' => 5])->assertRedirect(route('login'));
    $this->get('/observations?new=1')->assertRedirect(route('login'));

    $user = User::factory()->create();
    unreadTestObservation(User::factory()->create(), 'M 1');
    ReadObservations::mark($user);
    unreadTestObservation(User::factory()->create(), 'M 2');

    $this->actingAs($user)->get('/observations?markAsRead=All&up_to=99999')->assertOk();
    expect(ReadObservations::unreadCount($user))->toBe(1);
});
