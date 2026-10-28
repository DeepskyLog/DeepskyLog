<?php

use App\Livewire\ObjectObservingLists;
use App\Livewire\ObservingListToggle;
use App\Models\ObservingList;
use App\Models\ObservingListItem;
use App\Models\User;
use App\Services\ActiveObservingListService;
use Livewire\Livewire;

function oolList(User $owner, string $name, bool $public = true, array $objects = ['M 31'], int $likes = 0): ObservingList
{
    $list = ObservingList::create(['owner_user_id' => $owner->id, 'name' => $name, 'public' => $public]);
    $list->forceFill(['likes_count' => $likes])->save();
    foreach ($objects as $object) {
        ObservingListItem::create([
            'observing_list_id' => $list->id, 'object_name' => $object, 'added_by_user_id' => $owner->id,
        ]);
    }

    return $list;
}

it('shows guests the public lists that contain the object', function () {
    $owner = User::factory()->create(['name' => 'List Owner']);
    oolList($owner, 'Autumn galaxies');
    oolList($owner, 'Secret targets', false);
    oolList($owner, 'Globulars', true, ['M 13']);

    Livewire::test(ObjectObservingLists::class, ['objectName' => 'M 31'])
        ->assertSee('Autumn galaxies')
        ->assertSee('List Owner')
        ->assertDontSee('Secret targets')
        ->assertDontSee('Globulars');
});

it('shows the observer their own private lists apart from the public ones', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    oolList($me, 'My private plan', false);
    oolList($me, 'My public plan');
    oolList($other, 'Their public list');
    oolList($other, 'Their private list', false);

    Livewire::actingAs($me)
        ->test(ObjectObservingLists::class, ['objectName' => 'M 31'])
        ->assertSeeInOrder(['My lists', 'My public plan', 'My private plan', 'Public lists', 'Their public list'])
        ->assertDontSee('Their private list');
});

it('shows nothing when no list contains the object', function () {
    $owner = User::factory()->create();
    oolList($owner, 'Globulars', true, ['M 13']);

    Livewire::test(ObjectObservingLists::class, ['objectName' => 'M 31'])
        ->assertDontSee('In observing lists');
});

it('shows the most popular public lists first and links to the others', function () {
    $owner = User::factory()->create();
    foreach (range(1, ObjectObservingLists::MAX_PUBLIC + 2) as $i) {
        oolList($owner, sprintf('List %02d', $i), true, ['M 31'], $i === 3 ? 50 : 0);
    }

    $lists = Livewire::test(ObjectObservingLists::class, ['objectName' => 'M 31'])
        ->assertSeeInOrder(['List 03', 'List 12'])
        ->assertSee('All 12 public lists')
        ->assertSee(route('observing-lists.discover', ['q' => 'M 31', 'sort' => 'popular']));

    // Equal likes: the newest list first, so the oldest without likes drops out.
    $lists->assertDontSee('List 01');
});

it('shows the active list after the toggle adds the object to it', function () {
    $me = User::factory()->create();
    $list = oolList($me, 'Tonight', false, []);
    app(ActiveObservingListService::class)->setActiveList($me, $list);

    $lists = Livewire::actingAs($me)->test(ObjectObservingLists::class, ['objectName' => 'M 31'])
        ->assertDontSee('Tonight');

    Livewire::actingAs($me)->test(ObservingListToggle::class, ['objectName' => 'M 31'])
        ->call('toggle')
        ->assertDispatched('observing-list-item-toggled');

    $lists->dispatch('observing-list-item-toggled')->assertSee('Tonight');
});

it('shows the active list first, at most ten own lists and links to the others', function () {
    $me = User::factory()->create();
    $lists = [];
    foreach (range(1, ObjectObservingLists::MAX_OWN + 2) as $i) {
        $lists[$i] = oolList($me, sprintf('Mine %02d', $i), false);
    }
    app(ActiveObservingListService::class)->setActiveList($me, $lists[1]);

    Livewire::actingAs($me)
        ->test(ObjectObservingLists::class, ['objectName' => 'M 31'])
        ->assertSeeInOrder(['Mine 01', 'Mine 12', 'Mine 04'])
        ->assertDontSee('Mine 02')
        ->assertSee('All 12 of my lists')
        ->assertSee(route('observing-lists.index', ['q' => 'M 31']));
});

it('names a list without a name', function () {
    $me = User::factory()->create();
    // Only imported lists of the old site have no name.
    $list = oolList($me, 'Unnamed', false);
    ObservingList::whereKey($list->id)->update(['name' => '']);

    Livewire::actingAs($me)
        ->test(ObjectObservingLists::class, ['objectName' => 'M 31'])
        ->assertSee('(no name)');
});

it('finds the own lists that contain an object', function () {
    $me = User::factory()->create();
    oolList($me, 'Autumn galaxies');
    oolList($me, 'Globulars', true, ['M 13']);

    $this->actingAs($me)->get(route('observing-lists.index', ['q' => 'M 31']))
        ->assertOk()
        ->assertSee('Autumn galaxies')
        ->assertDontSee('Globulars');
});
