<?php

use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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

it('counts a broadcast as unread at once, while the unread counts are cached', function () {
    $admin = User::factory()->create();
    $team = \App\Models\Team::find(\App\Models\Team::ADMINISTRATORS);
    $admin->teams()->attach($team);
    $admin->switchTeam($team);

    expect(Message::getNumberOfUnreadMails($this->alice->username))->toBe(0);

    $this->actingAs($admin)
        ->post(route('messages.broadcast'), ['subject' => 'News', 'message' => '<p>Clear skies!</p>'])
        ->assertRedirect(route('messages.index'));

    expect(Message::getNumberOfUnreadMails($this->alice->username))->toBe(1)
        ->and(Message::getNumberOfUnreadMails($admin->username))->toBe(0);
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

it('queues the notification mails, in the language of each recipient', function () {
    Mail::fake();
    $admin = User::factory()->create();
    $team = \App\Models\Team::find(\App\Models\Team::ADMINISTRATORS);
    $admin->teams()->attach($team);
    $admin->switchTeam($team);
    $dutch = User::factory()->create(['sendMail' => true, 'language' => 'nl']);
    $english = User::factory()->create(['sendMail' => true, 'language' => 'en']);
    $this->bob->forceFill(['sendMail' => false])->save();

    $this->actingAs($this->alice)
        ->post(route('messages.store'), ['receiver' => $dutch->username, 'subject' => 'Hi', 'message' => '<p>Hello</p>'])
        ->assertRedirect(route('messages.index'));
    Mail::assertQueued(MessageReceived::class, fn ($mail) => $mail->hasTo($dutch->email) && $mail->locale === 'nl');

    $this->actingAs($admin)->post(route('messages.broadcast'), ['subject' => 'News', 'message' => '<p>Clear skies!</p>']);

    Mail::assertNothingSent();
    Mail::assertQueued(MessageReceived::class, fn ($mail) => $mail->hasTo($english->email) && $mail->locale === 'en');
    Mail::assertQueued(MessageReceived::class, fn ($mail) => $mail->hasTo($dutch->email) && $mail->locale === 'nl' && $mail->sender === 'DeepskyLog');
    Mail::assertNotQueued(MessageReceived::class, fn ($mail) => $mail->hasTo($this->bob->email));
});

it('renders the notification mail in the language it is sent in', function () {
    $html = (new MessageReceived('Alice', 'Hi', '<p>Hello</p>', 'Bob'))->locale('nl')->render();

    expect($html)->toContain('U heeft een nieuw bericht ontvangen van Alice.');
});

it('escapes the name of the sender in the notification mail', function () {
    $html = (new MessageReceived('<a href="https://evil.test">Alice</a> & Bob', 'Hi', '<p>Hello</p>', 'Carol'))->render();

    expect($html)->not->toContain('<a href="https://evil.test">')
        ->and($html)->toContain('&lt;a href=&quot;https://evil.test&quot;&gt;Alice&lt;/a&gt; &amp; Bob');
});

it('shows why a message was not sent, and keeps what was filled in', function () {
    $this->actingAs($this->alice)
        ->from(route('messages.create'))
        ->followingRedirects()
        ->post(route('messages.store'), ['receiver' => '', 'subject' => 'Kept subject', 'message' => ''])
        ->assertOk()
        ->assertSee(__('validation.required', ['attribute' => 'receiver']))
        ->assertSee(__('validation.required', ['attribute' => 'message']))
        ->assertSee('Kept subject');

    $this->actingAs($this->alice)
        ->from(route('messages.create'))
        ->followingRedirects()
        ->post(route('messages.store'), ['receiver' => $this->bob->username, 'subject' => 'Hi', 'message' => ''])
        ->assertOk()
        ->assertSee(__('validation.required', ['attribute' => 'message']))
        ->assertSee('receivers: '.Illuminate\Support\Js::from([$this->bob->username]), false)
        ->assertSee(e($this->bob->name), false);
});

it('shows the receiver of a message, and replies to a sent message to its receiver', function () {
    $sent = createTestMessage($this->alice->username, $this->bob->username);

    $this->actingAs($this->alice)->get(route('messages.show', $sent->id))
        ->assertOk()
        ->assertSee(e($this->bob->name), false)
        ->assertSee(e(route('messages.create', ['reply_to' => $sent->id, 'to' => $this->bob->username])), false)
        ->assertDontSee('to='.urlencode($this->alice->username), false);

    $this->actingAs($this->alice)->getJson(route('messages.replyData', $sent->id))
        ->assertOk()
        ->assertJsonPath('sender', $this->alice->username);

    // The receiver still replies to the sender.
    $this->actingAs($this->bob)->get(route('messages.show', $sent->id))
        ->assertSee(e(route('messages.create', ['reply_to' => $sent->id, 'to' => $this->alice->username])), false);

    // Others still can't read it.
    $carol = User::factory()->create();
    $this->actingAs($carol)->getJson(route('messages.replyData', $sent->id))->assertForbidden();
});

it('does not offer a reply to a broadcast the user sent', function () {
    $broadcast = createTestMessage($this->alice->username, 'all');

    $this->actingAs($this->alice)->get(route('messages.show', $broadcast->id))
        ->assertOk()
        ->assertSee(__('All'))
        ->assertDontSee('reply_to=', false);
});

it('shows a broadcast as sent by DeepskyLog, without a reply to the administrator', function () {
    $admin = User::factory()->create(['name' => 'Admin Person']);
    $broadcast = createTestMessage($admin->username, 'all');

    $this->actingAs($this->alice)->get(route('messages.show', $broadcast->id))
        ->assertOk()
        ->assertSee('DeepskyLog')
        ->assertDontSee('Admin Person')
        ->assertDontSee('reply_to=', false);

    $this->actingAs($this->alice)->get(route('messages.index'))
        ->assertOk()
        ->assertSee('DeepskyLog')
        ->assertDontSee('Admin Person');
});

it('quotes a reply with a line per paragraph, line break and list item', function () {
    $message = Message::create([
        'sender' => $this->bob->username, 'receiver' => $this->alice->username, 'subject' => 'S', 'date' => now(),
        'message' => '<p>Line one</p><p>Line &amp; two<br>Line three</p><ul><li>Item</li></ul>',
    ]);

    $this->actingAs($this->alice)->getJson(route('messages.replyData', $message->id))
        ->assertOk()
        ->assertJsonPath('message', "> Line one\n> Line & two\n> Line three\n> Item")
        ->assertJsonPath('message_html', '<blockquote><p>Line one</p><p>Line &amp; two<br />Line three</p><ul><li>Item</li></ul></blockquote>');
});

it('does not open a message the user deleted', function () {
    $message = createTestMessage($this->bob->username, 'all');

    $this->actingAs($this->alice)->post(route('messages.destroy', $message->id));

    $this->actingAs($this->alice)->get(route('messages.show', $message->id))->assertNotFound();
    $this->actingAs($this->bob)->get(route('messages.show', $message->id))->assertOk();
});

it('asks an unverified observer to verify the address before writing a message', function () {
    $unverified = User::factory()->unverified()->create();

    $this->actingAs($unverified)->get(route('messages.create'))->assertRedirect(route('verification.notice'));
});

it('does not query once per sent message in the inbox', function () {
    $queries = function () {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($this->alice)->get(route('messages.index'))->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    createTestMessage($this->alice->username, $this->bob->username);
    $queries(); // fills the caches of the first request
    $few = $queries();
    foreach (range(1, 20) as $i) {
        Message::create(['sender' => $this->alice->username, 'receiver' => $this->bob->username, 'subject' => "S$i", 'message' => "M$i", 'date' => now()->subMinutes($i)]);
    }

    expect($queries())->toBe($few);
});

it('keeps the script of the message page inside the page', function () {
    $message = createTestMessage($this->bob->username, $this->alice->username);
    $html = $this->actingAs($this->alice)->get(route('messages.show', $message->id))->getContent();

    expect(strpos($html, 'var deleteMsg'))->toBeLessThan(strrpos($html, '</html>'));
});
