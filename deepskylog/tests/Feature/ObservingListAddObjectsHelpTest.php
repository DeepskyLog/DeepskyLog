<?php

use App\Models\ObservingList;
use App\Models\User;
use App\Services\ActiveObservingListService;

/*
 * How an observer fills a new observing list (#378): the "Add object" field
 * on the list page, the explanation an empty list shows its owner, and the
 * option of the create form to make the new list the active one.
 */

function olahList(User $owner): ObservingList
{
    return ObservingList::create(['owner_user_id' => $owner->id, 'name' => 'Empty list', 'public' => true]);
}

it('explains an empty list to its owner, not to other observers', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $list = olahList($owner);

    $this->actingAs($owner)->get(route('observing-list.show', $list))
        ->assertOk()
        ->assertSee('How to add objects to this list')
        ->assertSee('Add object')
        ->assertSee(route('search.advanced'), false)
        ->assertSee('open-list-import', false);

    $this->actingAs($other)->get(route('observing-list.show', $list))
        ->assertOk()
        ->assertDontSee('How to add objects to this list')
        ->assertDontSee('Add object')
        ->assertSee('This list has no objects yet.');

    $this->get(route('observing-list.show', $list))
        ->assertOk()
        ->assertDontSee('How to add objects to this list');
});

it('offers to make the empty list active until it is', function () {
    $owner = User::factory()->create();
    $list = olahList($owner);

    $this->actingAs($owner)->get(route('observing-list.show', $list))
        ->assertSee('Make this my active list')
        ->assertDontSee('This is your active list.');

    app(ActiveObservingListService::class)->setActiveList($owner, $list);

    $this->actingAs($owner->fresh())->get(route('observing-list.show', $list))
        ->assertSee('This is your active list.')
        ->assertDontSee('Make this my active list');
});

it('drops the explanation once the list has objects, but keeps the add field', function () {
    $owner = User::factory()->create();
    $list = olahList($owner);
    $list->items()->create(['object_name' => 'M 31', 'source_mode' => 'manual', 'added_by_user_id' => $owner->id]);

    $this->actingAs($owner)->get(route('observing-list.show', $list))
        ->assertOk()
        ->assertDontSee('How to add objects to this list')
        ->assertSee('Add object');
});

it('makes a new list the active one when asked', function () {
    $owner = $this->createUserAndAssignToTeam('Observers');

    $this->actingAs($owner)->post(route('observing-list.store'), ['name' => 'Galaxies', 'make_active' => '1'])
        ->assertSessionHasNoErrors();
    $galaxies = ObservingList::where('name', 'Galaxies')->firstOrFail();
    expect($owner->fresh()->active_observing_list_id)->toBe($galaxies->id);

    // Without the option the active list stays as it was.
    $this->actingAs($owner)->post(route('observing-list.store'), ['name' => 'Nebulae', 'make_active' => '0'])
        ->assertSessionHasNoErrors();
    expect($owner->fresh()->active_observing_list_id)->toBe($galaxies->id);
});

it('checks the active-list option of the create form by default', function () {
    $owner = $this->createUserAndAssignToTeam('Observers');

    $this->actingAs($owner)->get(route('observing-list.create'))
        ->assertOk()
        ->assertSee('Make this my active list');
});
