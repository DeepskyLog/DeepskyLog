<?php

use App\Models\Observation;
use App\Models\User;
use App\Services\Observations\ObservationListView;
use App\Services\Observations\ReadObservations;
use Illuminate\Support\Facades\DB;

// The observation listings as cards or as a compact list, one row per
// observation (ObservationListView, ?view=list|cards), as the old site's
// "list" of the latest observations.

function listViewObservation(User $observer, string $object, ?int $visibility = null, bool $drawing = false): Observation
{
    $observation = Observation::create([
        'observer_username' => $observer->username,
        'observer_user_id' => $observer->id,
        'observed_on' => '2026-03-01',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $object,
        'target_name' => $object,
        'description' => 'A description only the cards show',
        'has_drawing' => $drawing,
    ]);

    if ($visibility !== null) {
        DB::table('deepsky_observation_details')->insert(['observation_id' => $observation->id, 'visibility' => $visibility]);
    }

    return $observation;
}

it('shows the observations as cards by default and as a list with ?view=list', function () {
    $observer = User::factory()->create(['name' => 'Alice Observer']);
    listViewObservation($observer, 'NGC 891', 4, true);
    listViewObservation($observer, 'NGC 7000', Observation::VISIBILITY_NOT_SEEN);

    $this->get('/observations')
        ->assertOk()
        ->assertSee('A description only the cards show')
        ->assertDontSee('data-observation-list', false);

    $this->get('/observations?view=list')
        ->assertOk()
        ->assertSee('data-observation-list', false)
        ->assertSee('NGC 891')
        ->assertSee('NGC 7000')
        ->assertSee('Alice Observer')
        ->assertSee('Not seen')
        ->assertSee('title="Averted vision required to percept object"', false)
        ->assertSee('✏️')
        ->assertDontSee('A description only the cards show');
});

it('keeps the list view in the links of the tabs, the sorting and the pages', function () {
    $observer = User::factory()->create();
    foreach (range(1, 21) as $n) {
        listViewObservation($observer, "NGC $n");
    }

    $this->get('/observations?view=list')
        ->assertOk()
        ->assertSee('view=list&amp;obs_type=deepsky', false)
        ->assertSee('view=list&amp;sort=id', false)
        ->assertSee('view=list&amp;deepsky=2', false);
});

it('remembers the choice of a logged-in observer, not of a guest', function () {
    $user = User::factory()->create();
    listViewObservation(User::factory()->create(), 'M 31');

    $this->actingAs($user)->get('/observations?view=list')->assertOk()->assertSee('data-observation-list', false);
    expect(DB::table('user_preferences')->where('user_id', $user->id)->where('key', ObservationListView::PREFERENCE)->value('value'))->toBe('list');

    $this->actingAs($user)->get('/observations')->assertOk()->assertSee('data-observation-list', false);

    $this->actingAs($user)->get('/observations?view=cards')->assertOk()->assertDontSee('data-observation-list', false);
    $this->actingAs($user)->get('/observations')->assertOk()->assertDontSee('data-observation-list', false);
});

it('lists the new observations with their marker and the mark per row when sorted by id', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    listViewObservation($other, 'NGC 7000');
    ReadObservations::mark($user);
    $new = listViewObservation($other, 'NGC 891');

    $this->actingAs($user)->get('/observations?new=1&view=list')
        ->assertOk()
        ->assertSee('data-observation-list', false)
        ->assertSee('NGC 891')
        ->assertDontSee('NGC 7000')
        ->assertSee('Mark all read')
        ->assertSee('name="up_to" value="'.$new->id.'"', false)
        ->assertSee('Mark this one and the older ones as read');
});

it('offers no list on the drawings pages', function () {
    listViewObservation(User::factory()->create(), 'M 51', 3, true);

    $this->get('/drawings?view=list')->assertOk()->assertDontSee('data-observation-list', false);
});
