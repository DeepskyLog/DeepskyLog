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

it('keeps the unread count of a user up to date while it is cached', function () {
    $alice = $this->alice->username;
    $personal = createTestMessage($this->bob->username, $alice);
    createTestMessage('DeepskyLog', 'all');

    expect(Message::getNumberOfUnreadMails($alice))->toBe(2);

    // Served from the cache: no query on the messages tables.
    DB::enableQueryLog();
    expect(Message::getNumberOfUnreadMails($alice))->toBe(2)
        ->and(collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'messages'))->all())->toBe([]);
    DB::disableQueryLog();

    // A new personal message, a new broadcast, reading and deleting all change it.
    $second = createTestMessage($this->bob->username, $alice);
    expect(Message::getNumberOfUnreadMails($alice))->toBe(3);
    createTestMessage('DeepskyLog', 'all');
    expect(Message::getNumberOfUnreadMails($alice))->toBe(4)
        ->and(Message::getNumberOfUnreadMails($this->bob->username))->toBe(2);

    $this->actingAs($this->alice)->get(route('messages.show', $personal->id))->assertOk();
    expect(Message::getNumberOfUnreadMails($alice))->toBe(3);

    $this->actingAs($this->alice)->post(route('messages.destroy', $second->id));
    expect(Message::getNumberOfUnreadMails($alice))->toBe(2);

    $this->actingAs($this->alice)->post(route('messages.markAllRead'));
    expect(Message::getNumberOfUnreadMails($alice))->toBe(0);
});

it('does not count a broadcast for the administrator who sent it', function () {
    createTestMessage($this->alice->username, 'all');

    expect(Message::getNumberOfUnreadMails($this->alice->username))->toBe(0)
        ->and(Message::getNumberOfUnreadMails($this->bob->username))->toBe(1);
});

it('starts a new message with a link to the page it is about', function () {
    $about = url('/observations/someone/2026-01-01/m31');

    $this->actingAs($this->alice)
        ->get(route('messages.create', ['to' => $this->bob->username, 'about' => $about]))
        ->assertOk()
        ->assertSee(e('<a href="'.e($about).'">'), false);
});

it('ignores an about link to another site', function () {
    $this->actingAs($this->alice)
        ->get(route('messages.create', ['to' => $this->bob->username, 'about' => 'https://evil.example/phish']))
        ->assertOk()
        ->assertDontSee('evil.example', false);
});

it('makes the relative links of a message absolute in the notification mail', function () {
    $base = 'https://www.deepskylog.org/messages/create';
    $html = '<p><a href="../observation/wim/2026-05-12/copernicus?x=1&amp;y=2">obs</a> '
        .'<a href="/object/m31">m31</a> <a href="https://example.org/a">ext</a> '
        .'<a href="mailto:a@b.c">mail</a></p>';

    expect(App\Mail\MessageReceived::absoluteLinks($html, $base))->toBe(
        '<p><a href="https://www.deepskylog.org/observation/wim/2026-05-12/copernicus?x=1&amp;y=2">obs</a> '
        .'<a href="https://www.deepskylog.org/object/m31">m31</a> <a href="https://example.org/a">ext</a> '
        .'<a href="mailto:a@b.c">mail</a></p>'
    );
});
