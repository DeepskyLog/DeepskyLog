<?php

use App\Models\ObservingList;
use App\Models\ObservingListPin;
use App\Models\ObservingListSubscription;
use App\Models\User;
use App\Services\AtTheEyepiece;
use App\Services\ObjectSources;

// "Working on" (ObservingListPin): a personal mark on one's own lists and the
// public lists one subscribed to, shown first.

function pinList(User $owner, string $name, bool $public = true): ObservingList
{
    $list = ObservingList::create(['owner_user_id' => $owner->id, 'name' => $name, 'public' => $public]);
    $list->items()->create(['object_name' => 'M 31', 'added_by_user_id' => $owner->id]);

    return $list;
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->other = User::factory()->create();
});

it('marks an own list as worked on and removes the mark', function () {
    $list = pinList($this->user, 'Herschel 400');

    $this->actingAs($this->user)->post(route('observing-list.pin', $list))->assertRedirect();
    expect($list->isPinnedBy($this->user))->toBeTrue();

    // A second mark changes nothing
    $this->actingAs($this->user)->post(route('observing-list.pin', $list))->assertRedirect();
    expect(ObservingListPin::count())->toBe(1);

    $this->actingAs($this->user)->post(route('observing-list.unpin', $list))->assertRedirect();
    expect($list->isPinnedBy($this->user))->toBeFalse();
});

it('marks a subscribed public list, but not a list of another observer without a subscription', function () {
    $list = pinList($this->other, 'Arp galaxies');

    $this->actingAs($this->user)->post(route('observing-list.pin', $list))->assertForbidden();

    ObservingListSubscription::create(['observing_list_id' => $list->id, 'user_id' => $this->user->id]);
    $this->actingAs($this->user)->post(route('observing-list.pin', $list))->assertRedirect();
    expect($list->isPinnedBy($this->user))->toBeTrue();
    // It does not make the list more popular
    expect($list->subscriptions()->count())->toBe(1);
});

it('allows at most the maximum number of lists', function () {
    foreach (range(1, ObservingListPin::MAX_PER_USER) as $i) {
        expect(ObservingListPin::pin($this->user, pinList($this->user, "List {$i}")))->toBeTrue();
    }
    $extra = pinList($this->user, 'One too many');

    $this->actingAs($this->user)->post(route('observing-list.pin', $extra))->assertSessionHas('error');
    expect($extra->isPinnedBy($this->user))->toBeFalse();
    expect(ObservingListPin::where('user_id', $this->user->id)->count())->toBe(ObservingListPin::MAX_PER_USER);
});

it('forgets the mark when unsubscribing, when the list becomes private and when it is deleted', function () {
    $list = pinList($this->other, 'Shared');
    ObservingListSubscription::create(['observing_list_id' => $list->id, 'user_id' => $this->user->id]);
    ObservingListPin::pin($this->user, $list);

    $this->actingAs($this->user)->post(route('observing-list.unsubscribe', $list));
    expect($list->isPinnedBy($this->user))->toBeFalse();

    ObservingListSubscription::create(['observing_list_id' => $list->id, 'user_id' => $this->user->id]);
    ObservingListPin::pin($this->user, $list);
    ObservingListPin::pin($this->other, $list);
    $list->update(['public' => false]);
    expect($list->isPinnedBy($this->user))->toBeFalse()
        ->and($list->isPinnedBy($this->other))->toBeTrue();

    $list->delete();
    expect(ObservingListPin::count())->toBe(0);
});

it('shows the lists worked on first on the page of one\'s lists', function () {
    pinList($this->user, 'Not worked on');
    $pinned = pinList($this->user, 'Galaxy trios');
    ObservingListPin::pin($this->user, $pinned);

    $this->actingAs($this->user)->get(route('observing-lists.index'))
        ->assertOk()
        ->assertSeeInOrder([__('Working on'), 'Galaxy trios', __('My lists'), 'Not worked on']);
});

it('puts the lists worked on after the active list at the eyepiece', function () {
    $a = pinList($this->user, 'A');
    $b = pinList($this->user, 'B');
    $c = pinList($this->user, 'C');
    $a->forceFill(['updated_at' => now()])->save();
    $b->forceFill(['updated_at' => now()->subDay()])->save();
    $c->forceFill(['updated_at' => now()->subDays(2)])->save();
    ObservingListPin::pin($this->user, $c);
    $this->user->forceFill(['active_observing_list_id' => $b->id])->save();

    expect(AtTheEyepiece::lists($this->user->fresh())['other']->pluck('name')->all())->toBe(['B', 'C', 'A']);
});

it('puts the lists worked on first among the sources of goals', function () {
    pinList($this->user, 'Alpha');
    ObservingListPin::pin($this->user, pinList($this->user, 'Zeta'));

    $labels = collect(ObjectSources::options($this->user))->where('type', 'list')->pluck('label')->values()->all();
    expect($labels)->toBe(['Zeta', 'Alpha']);
});

it('orders option lists with the worked-on lists first', function () {
    $list = pinList($this->user, 'Pinned');
    ObservingListPin::pin($this->user, $list);

    expect(ObservingListPin::first([999 => 'x', $list->id => 'Pinned'], $this->user->id))
        ->toBe([$list->id => 'Pinned', 999 => 'x']);
});

it('keeps a note of the observer on a list they work on', function () {
    $list = pinList($this->user, 'Messier');
    ObservingListPin::pin($this->user, $list);

    $this->actingAs($this->user)
        ->post(route('observing-list.pin-note', $list), ['note' => "  Sketch them all\r\nwith the 8\"  "])
        ->assertSessionHas('success');
    expect(ObservingListPin::notesFor($this->user->id))->toBe([$list->id => "Sketch them all\nwith the 8\""]);

    // Shown to the observer on /observing-lists, the list page and at the eyepiece
    $this->actingAs($this->user)->get(route('observing-lists.index'))->assertSee('Sketch them all');
    $this->actingAs($this->user)->get(route('observing-list.show', $list))->assertSee('Sketch them all');
    expect(AtTheEyepiece::lists($this->user->fresh())['notes'])->toHaveKey($list->id);

    // Found by the search of /observing-lists
    $this->actingAs($this->user)->get(route('observing-lists.index', ['search' => 'sketch']))
        ->assertViewHas('pinnedLists', fn ($lists) => $lists->pluck('id')->all() === [$list->id]);

    // Not to anyone else
    $this->actingAs($this->other)->get(route('observing-list.show', $list))->assertDontSee('Sketch them all');

    // Too long, then emptied
    $this->actingAs($this->user)->post(route('observing-list.pin-note', $list), ['note' => str_repeat('x', ObservingListPin::NOTE_LENGTH + 1)])
        ->assertSessionHasErrors('note');
    $this->actingAs($this->user)->post(route('observing-list.pin-note', $list), ['note' => ' '])->assertRedirect();
    expect(ObservingListPin::notesFor($this->user->id))->toBe([]);
});

it('refuses a note on a list the observer does not work on', function () {
    $list = pinList($this->user, 'Caldwell');

    $this->actingAs($this->user)->post(route('observing-list.pin-note', $list), ['note' => 'binoculars'])->assertNotFound();
    expect(ObservingListPin::count())->toBe(0);
});

it('keeps one general note on what the observer is working on', function () {
    $this->actingAs($this->user)
        ->post(route('observing-lists.working-on-note'), ['note' => "Sketch every Messier object\r\n"])
        ->assertSessionHas('success');
    expect(ObservingListPin::generalNoteFor($this->user->id))->toBe('Sketch every Messier object');

    // Replaced, not added
    $this->actingAs($this->user)->post(route('observing-lists.working-on-note'), ['note' => 'Sketch every Caldwell object']);
    expect(DB::table('user_preferences')->where('user_id', $this->user->id)->where('key', ObservingListPin::GENERAL_NOTE_PREFERENCE)->count())->toBe(1);

    $this->actingAs($this->user)->get(route('observing-lists.index'))->assertSee('Sketch every Caldwell object');
    $this->actingAs($this->other)->get(route('observing-lists.index'))->assertDontSee('Sketch every Caldwell object');
    expect(AtTheEyepiece::lists($this->user->fresh())['workingOnNote'])->toBe('Sketch every Caldwell object');

    $this->actingAs($this->user)->post(route('observing-lists.working-on-note'), ['note' => str_repeat('x', ObservingListPin::GENERAL_NOTE_LENGTH + 1)])
        ->assertSessionHasErrors('note');
    $this->actingAs($this->user)->post(route('observing-lists.working-on-note'), ['note' => '']);
    expect(ObservingListPin::generalNoteFor($this->user->id))->toBeNull();
});
