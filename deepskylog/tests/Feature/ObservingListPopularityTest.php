<?php

use App\Livewire\ObjectObservingLists;
use App\Models\ObservingList;
use App\Models\ObservingListSubscription;
use App\Models\User;
use Livewire\Livewire;

// The most popular observing lists: ordered on likes and subscribers
// together, then on subscribers, then on comments (ObservingList::scopeOrderByPopularity()).

function popularList(User $owner, string $name, int $likes = 0, int $subscribers = 0, int $comments = 0): ObservingList
{
    $list = ObservingList::create(['owner_user_id' => $owner->id, 'name' => $name, 'public' => true]);
    $list->forceFill(['likes_count' => $likes, 'comments_count' => $comments])->save();
    $list->items()->create(['object_name' => 'M 31', 'added_by_user_id' => $owner->id]);
    foreach (User::factory()->count($subscribers)->create() as $subscriber) {
        ObservingListSubscription::create(['observing_list_id' => $list->id, 'user_id' => $subscriber->id]);
    }

    return $list;
}

beforeEach(function () {
    $this->owner = User::factory()->create();
    popularList($this->owner, 'Only comments', comments: 5);
    popularList($this->owner, 'Three likes', likes: 3);
    popularList($this->owner, 'Two subscribers', subscribers: 2);
    popularList($this->owner, 'One like one subscriber', likes: 1, subscribers: 1);
    popularList($this->owner, 'Two likes two subscribers', likes: 2, subscribers: 2);
});

it('orders the lists on their likes and subscribers, then on subscribers and comments', function () {
    expect(ObservingList::orderByPopularity()->pluck('name')->all())->toBe([
        'Two likes two subscribers',
        'Three likes',
        // Equal to the above: more subscribers first
        'Two subscribers',
        'One like one subscriber',
        'Only comments',
    ]);
});

it('counts the subscribers on the popular tab of the discover page', function () {
    $this->get(route('observing-lists.discover', ['sort' => 'popular']))
        ->assertOk()
        ->assertSeeInOrder(['Two likes two subscribers', 'Three likes', 'Two subscribers', 'One like one subscriber', 'Only comments'])
        ->assertSee(__('Subscribers'));

    // The newest tab shows the counts as well
    $this->get(route('observing-lists.discover'))
        ->assertOk()
        ->assertSee(__('Subscribers'));
});

it('orders the public lists on the object page the same way', function () {
    Livewire::test(ObjectObservingLists::class, ['objectName' => 'M 31'])
        ->assertSeeInOrder(['Two likes two subscribers', 'Three likes', 'Two subscribers', 'One like one subscriber', 'Only comments'])
        ->assertSee(__('Subscribers'));
});
