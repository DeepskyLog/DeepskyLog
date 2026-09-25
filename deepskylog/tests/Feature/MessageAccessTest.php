<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function createTestMessage(string $sender, string $receiver): Message
{
    return Message::create([
        'sender' => $sender,
        'receiver' => $receiver,
        'subject' => "From {$sender} to {$receiver}",
        'message' => 'Clear skies!',
        'date' => now(),
    ]);
}

beforeEach(function () {
    $this->alice = $this->createUserAndAssignToTeam('Observers');
    $this->bob = User::factory()->create();
});

it('lets every user open a broadcast message and marks it read for them', function () {
    $message = createTestMessage('DeepskyLog', 'all');

    $this->actingAs($this->alice)->get(route('messages.show', $message->id))->assertOk();

    expect(DB::table('messages_read')->where('id', $message->id)->where('receiver', $this->alice->username)->exists())->toBeTrue();
});

it('lets the receiver and the sender open a personal message, but nobody else', function () {
    $message = createTestMessage($this->bob->username, $this->alice->username);
    $outsider = User::factory()->create();

    $this->actingAs($this->alice)->get(route('messages.show', $message->id))->assertOk();
    $this->actingAs($this->bob)->get(route('messages.show', $message->id))->assertOk();
    $this->actingAs($outsider)->get(route('messages.show', $message->id))->assertForbidden();

    // Opening a sent message does not mark it read for the sender.
    expect(DB::table('messages_read')->where('id', $message->id)->where('receiver', $this->bob->username)->exists())->toBeFalse();
});

it('starts a newly registered user without the existing broadcast messages', function () {
    $this->getTeamFromString('Observers');
    createTestMessage('DeepskyLog', 'all');
    createTestMessage('DeepskyLog', 'all');

    $user = app(\App\Actions\Fortify\CreateNewUser::class)->create([
        'name' => 'New Observer',
        'username' => 'new-observer',
        'email' => 'new-observer@example.com',
        'password' => 'correct-horse-battery-staple-42',
        'password_confirmation' => 'correct-horse-battery-staple-42',
        'terms' => true,
    ]);

    expect(Message::getNumberOfUnreadMails($user->username))->toBe(0);

    // Broadcasts sent after registration still reach the new user.
    createTestMessage('DeepskyLog', 'all');

    expect(Message::getNumberOfUnreadMails($user->username))->toBe(1);
});

it('does not remove the existing broadcasts from other users', function () {
    createTestMessage('DeepskyLog', 'all');

    Message::dismissExistingBroadcastsFor('someone-new');

    expect(Message::getNumberOfUnreadMails($this->alice->username))->toBe(1);
});
