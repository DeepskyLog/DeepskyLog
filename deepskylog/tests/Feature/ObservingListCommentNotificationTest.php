<?php

use App\Models\Message;
use App\Models\ObservingList;
use App\Models\User;

it('sends an internal message to the list owner when another user comments', function () {
    $owner = User::factory()->create(['username' => 'listowner']);
    $commenter = User::factory()->create(['username' => 'commenter']);

    $list = ObservingList::create([
        'owner_user_id' => $owner->id,
        'name' => 'Spring Galaxies',
        'description' => 'A compact spring list',
        'public' => true,
    ]);

    $response = $this->actingAs($commenter)->post(route('observing-list.comments.store', ['list' => $list]), [
        'body' => 'Great list, thanks for sharing.',
    ]);

    $response->assertSessionHas('success');

    $message = Message::where('receiver', $owner->username)
        ->where('sender', $commenter->username)
        ->first();

    expect($message)->not->toBeNull();
    expect($message->subject)->toContain('Spring Galaxies');

    $listUrl = route('observing-list.show', ['list' => $list]);
    expect($message->message)->toContain($listUrl);
});

it('does not send a message when the owner comments on their own list', function () {
    $owner = User::factory()->create(['username' => 'listowner']);

    $list = ObservingList::create([
        'owner_user_id' => $owner->id,
        'name' => 'Summer Nebulae',
        'description' => null,
        'public' => false,
    ]);

    $response = $this->actingAs($owner)->post(route('observing-list.comments.store', ['list' => $list]), [
        'body' => 'My own note.',
    ]);

    $response->assertSessionHas('success');

    $count = Message::where('receiver', $owner->username)->count();
    expect($count)->toBe(0);
});

it('shows the comments of a public list to a guest without a delete button', function () {
    $owner = User::factory()->create();
    $list = ObservingList::create(['owner_user_id' => $owner->id, 'name' => 'Weekend list', 'public' => true]);
    \App\Models\ObservingListComment::create(['observing_list_id' => $list->id, 'user_id' => $owner->id, 'body' => 'Clear skies all weekend']);

    $this->get(route('observing-list.show', ['list' => $list]))
        ->assertOk()
        ->assertSee('Clear skies all weekend')
        ->assertDontSee(route('observing-list.comments.destroy', [$list, 1]), false);
});

it('sorts popular lists with the same number of likes on the number of comments', function () {
    $owner = User::factory()->create();
    $make = fn (string $name, int $likes, int $comments) => ObservingList::create(['owner_user_id' => $owner->id, 'name' => $name, 'public' => true])
        ->forceFill(['likes_count' => $likes, 'comments_count' => $comments])->save();
    $make('Few comments', 3, 1);
    $make('Most liked', 5, 0);
    $make('Many comments', 3, 4);
    $make('Not liked', 0, 9);

    $this->get(route('observing-lists.discover', ['sort' => 'popular']))
        ->assertOk()
        ->assertSeeInOrder(['Most liked', 'Many comments', 'Few comments', 'Not liked']);
});
