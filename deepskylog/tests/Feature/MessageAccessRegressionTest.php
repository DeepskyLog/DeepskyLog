<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->alice = $this->createUserAndAssignToTeam('Observers');
    $this->bob = User::factory()->create();
});

it('lets every user delete a broadcast message from their own inbox', function () {
    $message = Message::create(['sender' => 'DeepskyLog', 'receiver' => 'all', 'subject' => 'News', 'message' => 'Clear skies!', 'date' => now()]);

    $this->actingAs($this->alice)->post(route('messages.destroy', $message->id))->assertRedirect();

    expect(DB::table('messages_deleted')->where('id', $message->id)->where('receiver', $this->alice->username)->exists())->toBeTrue();
});

it('lets every user reply to a broadcast message', function () {
    $message = Message::create(['sender' => 'DeepskyLog', 'receiver' => 'all', 'subject' => 'News', 'message' => 'Clear skies!', 'date' => now()]);

    $this->actingAs($this->alice)->get(route('messages.replyData', $message->id))->assertOk();
});

it('does not let a non-administrator send a message to all', function () {
    $this->actingAs($this->alice)
        ->post(route('messages.store'), ['receiver' => 'all', 'subject' => 'Spam', 'message' => 'Hello everyone'])
        ->assertSessionHasErrors('receiver');

    expect(Message::where('receiver', 'all')->exists())->toBeFalse();
});

it('does not store a message to a user that does not exist', function () {
    $this->actingAs($this->alice)
        ->post(route('messages.store'), ['receiver' => 'nobody-with-this-name', 'message' => 'Hello'])
        ->assertSessionHasErrors('receiver');

    expect(Message::count())->toBe(0);
});

it('stores a message to an existing user', function () {
    $this->actingAs($this->alice)
        ->post(route('messages.store'), ['receiver' => $this->bob->username, 'message' => 'Hello'])
        ->assertSessionHasNoErrors();

    expect(Message::where('receiver', $this->bob->username)->exists())->toBeTrue();
});
