<?php

use App\Http\Controllers\LikersController;
use App\Models\ObjectFindingNote;
use App\Models\ObjectFindingNoteLike;
use App\Models\Observation;
use App\Models\ObservationLike;
use App\Models\ObservationSession;
use App\Models\ObservingList;
use App\Models\ObservingListLike;
use App\Models\User;

/** Who liked something: only for logged-in observers, never for what they may not see. */
function likersObservation(User $owner): Observation
{
    return Observation::create([
        'observer_username' => $owner->username,
        'observer_user_id' => $owner->id,
        'observed_on' => '2026-03-01',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'M 65',
        'target_name' => 'M 65',
        'description' => 'Seen',
    ]);
}

function likersSession(User $owner, int $active): ObservationSession
{
    return ObservationSession::create([
        'observerid' => $owner->username,
        'name' => 'Night out',
        'active' => $active,
        'begindate' => now()->toDateTimeString(),
        'enddate' => now()->toDateTimeString(),
        'locationid' => 0,
    ]);
}

it('lists who liked an observation, newest first, with links to their pages', function () {
    $owner = User::factory()->create();
    $first = User::factory()->create(['name' => 'Anna']);
    $second = User::factory()->create(['name' => 'Bert &amp; Co']);
    $observation = likersObservation($owner);

    ObservationLike::create(['user_id' => $first->id, 'observation_type' => 'observation', 'observation_id' => $observation->id])
        ->forceFill(['created_at' => now()->subDay()])->save();
    ObservationLike::create(['user_id' => $second->id, 'observation_type' => 'observation', 'observation_id' => $observation->id]);

    $this->actingAs($owner)
        ->getJson(route('likes.likers', ['kind' => 'observation', 'id' => $observation->id]))
        ->assertOk()
        ->assertExactJson([
            'total' => 2,
            'more' => 0,
            'likers' => [
                ['name' => 'Bert & Co', 'url' => route('observer.show', $second->slug)],
                ['name' => 'Anna', 'url' => route('observer.show', $first->slug)],
            ],
        ]);
});

it('does not show the likers to guests', function () {
    $observation = likersObservation(User::factory()->create());

    $this->getJson(route('likes.likers', ['kind' => 'observation', 'id' => $observation->id]))
        ->assertUnauthorized();
});

it('counts the likers beyond the limit', function () {
    $list = ObservingList::create(['owner_user_id' => User::factory()->create()->id, 'name' => 'Popular', 'public' => true]);
    User::factory()->count(LikersController::LIMIT + 3)->create()
        ->each(fn (User $u) => ObservingListLike::create(['observing_list_id' => $list->id, 'user_id' => $u->id]));

    $this->actingAs(User::factory()->create())
        ->getJson(route('likes.likers', ['kind' => 'observing-list', 'id' => $list->id]))
        ->assertOk()
        ->assertJsonPath('total', LikersController::LIMIT + 3)
        ->assertJsonPath('more', 3)
        ->assertJsonCount(LikersController::LIMIT, 'likers');
});

it('hides the likers of a private list from others than its owner', function () {
    $owner = User::factory()->create();
    $list = ObservingList::create(['owner_user_id' => $owner->id, 'name' => 'Mine', 'public' => false]);

    $this->actingAs(User::factory()->create())
        ->getJson(route('likes.likers', ['kind' => 'observing-list', 'id' => $list->id]))
        ->assertNotFound();
    $this->actingAs($owner)
        ->getJson(route('likes.likers', ['kind' => 'observing-list', 'id' => $list->id]))
        ->assertOk()->assertJsonPath('total', 0);
});

it('hides the likers of a draft session from others than its observer', function () {
    $owner = User::factory()->create();
    $liker = User::factory()->create();
    $draft = likersSession($owner, 0);
    $active = likersSession($owner, 1);
    ObservationLike::create(['user_id' => $liker->id, 'observation_type' => 'session', 'observation_id' => $active->id]);

    $this->actingAs($liker)
        ->getJson(route('likes.likers', ['kind' => 'session', 'id' => $draft->id]))
        ->assertNotFound();
    $this->actingAs($owner)
        ->getJson(route('likes.likers', ['kind' => 'session', 'id' => $draft->id]))
        ->assertOk();
    $this->actingAs($liker)
        ->getJson(route('likes.likers', ['kind' => 'session', 'id' => $active->id]))
        ->assertOk()->assertJsonPath('likers.0.name', $liker->name);
});

it('lists who liked a finding note', function () {
    $author = User::factory()->create();
    $liker = User::factory()->create();
    $note = ObjectFindingNote::create(['object_name' => 'M 42', 'user_id' => $author->id, 'text' => 'Easy']);
    ObjectFindingNoteLike::create(['object_finding_note_id' => $note->id, 'user_id' => $liker->id]);

    $this->actingAs($author)
        ->getJson(route('likes.likers', ['kind' => 'finding-note', 'id' => $note->id]))
        ->assertOk()->assertJsonPath('likers.0.name', $liker->name);
});

it('answers 404 for an unknown kind or object', function () {
    $this->actingAs(User::factory()->create());

    $this->getJson('/likes/comment/1')->assertNotFound();
    $this->getJson(route('likes.likers', ['kind' => 'observation', 'id' => 999999]))->assertNotFound();
});

it('shows the likers button on the observation page to logged-in observers only', function () {
    $owner = User::factory()->create();
    $observation = likersObservation($owner);
    ObservationLike::create(['user_id' => User::factory()->create()->id, 'observation_type' => 'observation', 'observation_id' => $observation->id]);
    $params = ['observer' => $owner->slug, 'date' => '2026-03-01', 'object' => 'm-65'];
    $url = route('likes.likers', ['kind' => 'observation', 'id' => $observation->id]);

    $this->get(route('observations.detail', $params))->assertOk()->assertDontSee($url, false);
    $this->actingAs($owner)->get(route('observations.detail', $params))->assertOk()->assertSee($url, false);
});
