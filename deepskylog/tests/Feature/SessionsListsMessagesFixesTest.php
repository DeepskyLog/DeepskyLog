<?php

use App\Livewire\CreateSession;
use App\Models\Location;
use App\Models\Message;
use App\Models\ObservationSession;
use App\Models\ObservingList;
use App\Models\ObservingListComment;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

function slmLocation(User $owner): Location
{
    return Location::query()->create([
        'user_id' => $owner->id, 'name' => 'Test Site', 'longitude' => 4.5, 'latitude' => 50.5,
        'timezone' => 'Europe/Brussels', 'limitingMagnitude' => -999, 'skyBackground' => -999, 'elevation' => 120,
        'country' => 'Belgium', 'active' => true, 'observer' => '', 'observations' => 0, 'slug' => 'slm-site',
    ]);
}

function slmSession(User $owner, Location $location, string $name, int $active = 1): ObservationSession
{
    return ObservationSession::query()->create([
        'name' => $name, 'observerid' => $owner->username, 'begindate' => '2026-03-01 00:00:00',
        'enddate' => '2026-03-01 23:59:59', 'locationid' => $location->id, 'weather' => '', 'equipment' => '',
        'comments' => 'Clear skies', 'language' => 'en', 'active' => $active,
    ]);
}

it('does not delete a comment through the URL of another list', function () {
    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    $ownList = ObservingList::create(['owner_user_id' => $owner->id, 'name' => 'Own list', 'public' => true]);
    $otherList = ObservingList::create(['owner_user_id' => $commenter->id, 'name' => 'Other list', 'public' => true]);
    $comment = $otherList->addComment($commenter, 'Nice list');

    $this->actingAs($owner)
        ->delete(route('observing-list.comments.destroy', [$ownList, $comment]))
        ->assertNotFound();

    expect(ObservingListComment::find($comment->id))->not->toBeNull();
});

it('keeps list items in the order they were added', function () {
    $owner = User::factory()->create();
    $list = ObservingList::create(['owner_user_id' => $owner->id, 'name' => 'Ordered', 'public' => true]);

    foreach (['NGC 7000', 'M 31', 'IC 434'] as $name) {
        $list->items()->create(['object_name' => $name, 'added_by_user_id' => $owner->id]);
    }

    expect($list->items()->orderBy('sort_order')->pluck('object_name')->all())
        ->toBe(['NGC 7000', 'M 31', 'IC 434']);
});

it('gives a new session a fresh id, so it does not inherit a deleted session', function () {
    $owner = User::factory()->create(['username' => 'freshid']);
    $location = slmLocation($owner);
    $old = slmSession($owner, $location, 'Old night');
    $oldId = $old->id;
    $old->delete();

    expect(slmSession($owner, $location, 'New night')->id)->toBeGreaterThan($oldId);
});

it('forgets the cached session translations when a session is saved', function () {
    $owner = User::factory()->create(['username' => 'cacheowner']);
    $session = slmSession($owner, slmLocation($owner), 'Cached night');
    Cache::put('session_preview:' . $session->id . ':nl', 'Heldere hemel', now()->addDay());
    Cache::put('session_comments:' . $session->id . ':fr', 'Ciel clair', now()->addDay());

    $session->update(['comments' => 'Hazy']);

    expect(Cache::has('session_preview:' . $session->id . ':nl'))->toBeFalse()
        ->and(Cache::has('session_comments:' . $session->id . ':fr'))->toBeFalse();
});

it('makes session slugs unique per observer, from the decoded name', function () {
    $alice = User::factory()->create(['username' => 'slugalice']);
    $bob = User::factory()->create(['username' => 'slugbob']);
    $location = slmLocation($alice);

    $first = slmSession($alice, $location, 'M31 &amp; M32');
    $second = slmSession($alice, $location, 'M31 &amp; M32');
    $bobs = slmSession($bob, $location, 'M31 &amp; M32');

    expect($first->slug)->toBe('m31-m32')
        ->and($second->slug)->toBe('m31-m32-2')
        ->and($bobs->slug)->toBe('m31-m32');
});

it('shows all drafts after "Show more"', function () {
    $owner = User::factory()->create(['username' => 'draftowner']);
    $location = slmLocation($owner);
    foreach (range(1, 12) as $i) {
        slmSession($owner, $location, "Draft number {$i}", 0);
    }

    $this->actingAs($owner)->get(route('session.user', [$owner->slug]))
        ->assertDontSee('Draft number 1<', false)
        ->assertSee('show_drafts=1', false);

    $this->actingAs($owner)->get(route('session.user', [$owner->slug]) . '?show_drafts=1')
        ->assertSee('Draft number 1<', false)
        ->assertDontSee('show_drafts=1', false);
});

it('does not open or save the session of somebody else through adapt_from', function () {
    $owner = User::factory()->create(['username' => 'adaptowner']);
    $intruder = User::factory()->create(['username' => 'adaptintruder']);
    $session = slmSession($owner, slmLocation($owner), 'Private night');

    $this->actingAs($intruder)
        ->get(route('session.create', ['adapt_from' => $session->id]))
        ->assertForbidden();

    $this->actingAs($intruder);
    Livewire::test(CreateSession::class, ['session' => $session])->assertForbidden();

    expect($session->fresh()->name)->toBe('Private night');
});

it('only lets administrators broadcast', function () {
    $expert = $this->createUserAndAssignToTeam('Database Experts');

    $this->actingAs($expert)
        ->post(route('messages.broadcast'), ['subject' => 'Hi', 'message' => 'Hello all'])
        ->assertForbidden();

    expect(Message::where('receiver', 'all')->exists())->toBeFalse();
});

it('shows the broadcast form only to administrators', function () {
    $admin = $this->createUserAndAssignToTeam('Administrators');
    $broadcastForm = 'action="' . route('messages.broadcast') . '"';

    $this->actingAs($admin)
        ->get(route('messages.create', ['receiver' => 'all']))
        ->assertOk()
        ->assertSee($broadcastForm, false)
        ->assertSee('name="receiver" value="all"', false);

    $observer = User::factory()->create();
    $this->actingAs($observer)
        ->get(route('messages.create', ['receiver' => 'all']))
        ->assertOk()
        ->assertDontSee($broadcastForm, false)
        ->assertSee('action="' . route('messages.store') . '"', false);
});

it('does not count your own broadcasts as unread', function () {
    $admin = User::factory()->create(['username' => 'bcadmin']);
    Message::create(['sender' => 'bcadmin', 'receiver' => 'all', 'subject' => 'News', 'message' => 'News', 'date' => now()]);

    expect(Message::getNumberOfUnreadMails('bcadmin'))->toBe(0)
        ->and(Message::getNumberOfUnreadMails('someoneelse'))->toBe(1);
});

it('answers an unauthorized list export with 403', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $list = ObservingList::create(['owner_user_id' => $owner->id, 'name' => 'Secret list', 'public' => false]);

    $this->actingAs($other)
        ->get(route('observing-list.export.argo', $list))
        ->assertForbidden();
});

it('clears the active list after unsubscribing and when the owner makes it private', function () {
    $owner = User::factory()->create();
    $subscriber = User::factory()->create();
    $list = ObservingList::create(['owner_user_id' => $owner->id, 'name' => 'Shared list', 'public' => true]);
    $list->subscribers()->attach($subscriber->id);

    $this->actingAs($subscriber)->post(route('observing-list.set-active', $list));
    expect($subscriber->fresh()->active_observing_list_id)->toBe($list->id);

    $this->actingAs($subscriber)->post(route('observing-list.unsubscribe', $list));
    expect($subscriber->fresh()->active_observing_list_id)->toBeNull();

    $list->subscribers()->attach($subscriber->id);
    $this->actingAs($subscriber)->post(route('observing-list.set-active', $list));
    $owner->forceFill(['active_observing_list_id' => $list->id])->save();

    $list->update(['public' => false]);

    expect($subscriber->fresh()->active_observing_list_id)->toBeNull()
        ->and($owner->fresh()->active_observing_list_id)->toBe($list->id)
        ->and($list->isSubscribedBy($subscriber))->toBeFalse()
        ->and($subscriber->can('setAsActive', $list->fresh()))->toBeFalse();
});

it('shows a private list only to its owner', function () {
    $owner = User::factory()->create();
    $subscriber = User::factory()->create();
    $list = ObservingList::create(['owner_user_id' => $owner->id, 'name' => 'Going private', 'public' => true]);
    $list->subscribers()->attach($subscriber->id);

    $list->update(['public' => false]);

    expect($owner->can('view', $list))->toBeTrue()
        ->and($subscriber->can('view', $list))->toBeFalse();
    $this->actingAs($subscriber)->get(route('observing-list.show', $list))->assertForbidden();
});
