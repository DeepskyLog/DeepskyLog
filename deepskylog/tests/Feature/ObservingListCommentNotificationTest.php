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
