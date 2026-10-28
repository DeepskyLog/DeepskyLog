<?php

use App\Jobs\SendSessionInvitations;
use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\MessageBlock;
use App\Models\ObservationSession;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

function featureMessage(string $sender, string $receiver, string $subject = 'Subject', string $text = 'Clear skies!'): Message
{
    return Message::create(['sender' => $sender, 'receiver' => $receiver, 'subject' => $subject, 'message' => $text, 'date' => now()]);
}

function makeAdministrator(User $user): User
{
    $team = Team::find(Team::ADMINISTRATORS);
    $user->teams()->attach($team);
    $user->switchTeam($team);

    return $user;
}

beforeEach(function () {
    $this->alice = $this->createUserAndAssignToTeam('Observers');
    $this->bob = User::factory()->create(['name' => 'Bob Builder']);
    $this->carol = User::factory()->create(['name' => 'Carol Comet']);
});

it('searches the subject, the text and the name of the other observer', function () {
    featureMessage($this->bob->username, $this->alice->username, 'Messier marathon', 'Shall we?');
    featureMessage($this->carol->username, $this->alice->username, 'Clouds', 'Nothing to see tonight');
    featureMessage($this->alice->username, $this->carol->username, 'Answer', 'Too bad about the weather');

    $search = fn (string $q) => $this->actingAs($this->alice)->get(route('messages.index', ['q' => $q]));

    $search('marathon')->assertSee('Messier marathon')->assertDontSee('Clouds');
    $search('tonight')->assertSee('Clouds')->assertDontSee('Messier marathon');
    // The name of the sender of a received message, of the receiver of a sent one.
    $search('Bob Builder')->assertSee('Messier marathon')->assertDontSee('Clouds');
    $search('Carol')->assertSee('Clouds')->assertSee('Answer')->assertDontSee('Messier marathon');
    $search('nothing like this')->assertSee('No messages found for');
});

it('shows only the received or only the sent messages', function () {
    featureMessage($this->bob->username, $this->alice->username, 'Incoming');
    featureMessage($this->alice->username, $this->bob->username, 'Outgoing');

    $this->actingAs($this->alice)->get(route('messages.index', ['box' => 'received']))
        ->assertSee('Incoming')->assertDontSee('Outgoing');
    $this->actingAs($this->alice)->get(route('messages.index', ['box' => 'sent']))
        ->assertSee('Outgoing')->assertDontSee('Incoming');
    $this->actingAs($this->alice)->get(route('messages.index'))
        ->assertSee('Outgoing')->assertSee('Incoming');
});

it('does not keep the unread filter in the filter form, so it can be switched off', function () {
    $html = $this->actingAs($this->alice)->get(route('messages.index', ['unread' => 1, 'sort' => 'subject']))->getContent();

    expect($html)->not->toContain('type="hidden" name="unread"')
        ->and($html)->toContain('type="hidden" name="sort" value="subject"');
});

it('links a reply to the message it answers, both ways', function () {
    $original = featureMessage($this->bob->username, $this->alice->username, 'Question');

    $this->actingAs($this->alice)->post(route('messages.store'), [
        'receiver' => $this->bob->username, 'subject' => 'Re: Question', 'message' => '<p>Answer</p>', 'reply_to_id' => $original->id,
    ])->assertRedirect(route('messages.index'));

    $reply = Message::where('subject', 'Re: Question')->first();
    expect($reply->reply_to_id)->toBe($original->id);

    $this->actingAs($this->bob)->get(route('messages.show', $reply->id))
        ->assertSee(__('In reply to'))->assertSee(route('messages.show', $original->id));
    $this->actingAs($this->bob)->get(route('messages.show', $original->id))
        ->assertSee(__('Replies'))->assertSee(route('messages.show', $reply->id));
});

it('does not link a reply to a message the sender can not read', function () {
    $private = featureMessage($this->bob->username, $this->carol->username, 'Private');

    $this->actingAs($this->alice)->post(route('messages.store'), [
        'receiver' => $this->bob->username, 'message' => '<p>Hi</p>', 'reply_to_id' => $private->id,
    ]);

    expect(Message::where('sender', $this->alice->username)->value('reply_to_id'))->toBeNull();
});

it('passes the message replied to from the link on to the form', function () {
    $original = featureMessage($this->bob->username, $this->alice->username);

    $this->actingAs($this->alice)->get(route('messages.create', ['reply_to' => $original->id, 'to' => $this->bob->username]))
        ->assertSee('name="reply_to_id" value="'.$original->id.'"', false);
});

it('links the notification mail to the message', function () {
    Mail::fake();
    $this->bob->forceFill(['sendMail' => true])->save();

    $this->actingAs($this->alice)->post(route('messages.store'), ['receiver' => $this->bob->username, 'message' => '<p>Hi</p>']);

    $url = route('messages.show', Message::where('sender', $this->alice->username)->value('id'));
    Mail::assertQueued(MessageReceived::class, fn ($mail) => $mail->url === $url);

    expect((new MessageReceived('Alice', 'Hi', '<p>Hi</p>', 'Bob', $url))->render())->toContain('href="'.$url.'"');
});

it('links the broadcast mail to the broadcast', function () {
    Mail::fake();
    $admin = makeAdministrator(User::factory()->create());
    $this->bob->forceFill(['sendMail' => true])->save();

    $this->actingAs($admin)->post(route('messages.broadcast'), ['subject' => 'News', 'message' => '<p>News</p>']);

    $url = route('messages.show', Message::where('receiver', 'all')->value('id'));
    Mail::assertQueued(MessageReceived::class, fn ($mail) => $mail->hasTo($this->bob->email) && $mail->url === $url);
});

it('lets an observer block another observer, who can then no longer send messages', function () {
    $message = featureMessage($this->bob->username, $this->alice->username);

    $this->actingAs($this->alice)->get(route('messages.show', $message->id))->assertSee(__('Block :name', ['name' => 'Bob Builder']));
    $this->actingAs($this->alice)->post(route('messages.block'), ['user_id' => $this->bob->id])->assertRedirect();
    expect(MessageBlock::blocks($this->alice, $this->bob))->toBeTrue();

    $this->actingAs($this->bob)
        ->post(route('messages.store'), ['receiver' => $this->alice->username, 'message' => '<p>Again</p>'])
        ->assertSessionHasErrors('receiver');
    expect(Message::where('sender', $this->bob->username)->count())->toBe(1);

    // The blocked observer is listed in the inbox, and can be unblocked.
    $this->actingAs($this->alice)->get(route('messages.index'))->assertSee(__('Blocked observers'))->assertSee('Bob Builder');
    $this->actingAs($this->alice)->post(route('messages.unblock', $this->bob->id))->assertRedirect();
    $this->actingAs($this->bob)
        ->post(route('messages.store'), ['receiver' => $this->alice->username, 'message' => '<p>Sorry</p>'])
        ->assertSessionHasNoErrors();
});

it('does not let an observer block an administrator or themselves', function () {
    $admin = makeAdministrator(User::factory()->create());
    $message = featureMessage($admin->username, $this->alice->username);

    $this->actingAs($this->alice)->get(route('messages.show', $message->id))->assertDontSee(__('Block :name', ['name' => $admin->name]));
    $this->actingAs($this->alice)->post(route('messages.block'), ['user_id' => $admin->id])->assertStatus(422);
    $this->actingAs($this->alice)->post(route('messages.block'), ['user_id' => $this->alice->id])->assertStatus(422);

    expect(MessageBlock::count())->toBe(0);
});

it('does not send a session invitation to an observer who blocked the sender', function () {
    MessageBlock::create(['user_id' => $this->bob->id, 'blocked_user_id' => $this->alice->id]);
    $session = ObservationSession::query()->create([
        'id' => 1, 'name' => 'Night', 'observerid' => $this->alice->username,
        'begindate' => '2026-10-20 00:00:00', 'enddate' => '2026-10-20 23:59:59',
        'locationid' => 0, 'weather' => '', 'equipment' => '', 'language' => 'en', 'active' => 1,
    ]);

    (new SendSessionInvitations($session->id, [$this->bob->username, $this->carol->username], $this->alice->username))->handle();

    expect(Message::where('receiver', $this->bob->username)->exists())->toBeFalse()
        ->and(Message::where('receiver', $this->carol->username)->exists())->toBeTrue();
});

it('deletes, marks read and marks unread the selected messages', function () {
    $first = featureMessage($this->bob->username, $this->alice->username, 'Andromeda note');
    $second = featureMessage($this->carol->username, $this->alice->username, 'Betelgeuse note');
    $sent = featureMessage($this->alice->username, $this->bob->username, 'Cygnus note');
    $other = featureMessage($this->bob->username, $this->carol->username, 'Not for Alice');

    $bulk = fn (string $action, array $ids) => $this->actingAs($this->alice)
        ->from(route('messages.index'))
        ->post(route('messages.bulk'), ['action' => $action, 'ids' => $ids]);

    $bulk('read', [$first->id, $second->id])->assertRedirect(route('messages.index'));
    expect(Message::getNumberOfUnreadMails($this->alice->username))->toBe(0);

    $bulk('unread', [$first->id]);
    expect(Message::getNumberOfUnreadMails($this->alice->username))->toBe(1);

    // A message of others is skipped.
    $bulk('delete', [$second->id, $sent->id, $other->id]);
    expect(DB::table('messages_deleted')->where('receiver', $this->alice->username)->pluck('id')->all())
        ->toEqualCanonicalizing([$second->id, $sent->id]);

    $this->actingAs($this->alice)->get(route('messages.index'))
        ->assertSee('Andromeda note')->assertDontSee('Betelgeuse note')->assertDontSee('Cygnus note');
});

it('marks a message as unread again', function () {
    $message = featureMessage($this->bob->username, $this->alice->username);

    $this->actingAs($this->alice)->get(route('messages.show', $message->id))->assertSee(__('Mark as unread'));
    expect(Message::getNumberOfUnreadMails($this->alice->username))->toBe(0);

    $this->actingAs($this->alice)->post(route('messages.markUnread', $message->id))->assertRedirect(route('messages.index'));
    expect(Message::getNumberOfUnreadMails($this->alice->username))->toBe(1);

    // Not a message somebody else received.
    $this->actingAs($this->carol)->post(route('messages.markUnread', $message->id))->assertForbidden();
});

it('shows the result of an action', function () {
    $this->actingAs($this->alice)
        ->followingRedirects()
        ->post(route('messages.store'), ['receiver' => $this->bob->username, 'message' => '<p>Hi</p>'])
        ->assertSee(__('Message sent'));
});

it('sends a message to several observers at once', function () {
    Mail::fake();
    $this->bob->forceFill(['sendMail' => true])->save();
    $this->carol->forceFill(['sendMail' => true])->save();

    $this->actingAs($this->alice)->post(route('messages.store'), [
        'receivers' => json_encode([$this->bob->username, $this->carol->username]),
        'subject' => 'Star party', 'message' => '<p>Saturday at the club?</p>',
    ])->assertRedirect(route('messages.index'))->assertSessionHasNoErrors();

    $copies = Message::where('subject', 'Star party')->get();
    expect($copies->pluck('receiver')->all())->toEqualCanonicalizing([$this->bob->username, $this->carol->username])
        ->and($copies->pluck('date')->unique())->toHaveCount(1);
    Mail::assertQueued(MessageReceived::class, 2);
    Mail::assertQueued(MessageReceived::class, fn ($mail) => $mail->hasTo($this->carol->email) && $mail->url === route('messages.show', $copies->firstWhere('receiver', $this->carol->username)->id));

    // Each receiver gets it in their inbox; the sender sees one sent message with both names.
    $this->actingAs($this->bob)->get(route('messages.index'))->assertSee('Star party');
    $this->actingAs($this->carol)->get(route('messages.index'))->assertSee('Star party');
    $inbox = $this->actingAs($this->alice)->get(route('messages.index'))->assertSee('Bob Builder')->assertSee('Carol Comet')->getContent();
    expect(substr_count($inbox, 'Star party'))->toBe(1);
});

it('accepts the receivers as an array and ignores doubles', function () {
    $this->actingAs($this->alice)->post(route('messages.store'), [
        'receivers' => [$this->bob->username, strtoupper($this->bob->username), $this->carol->username],
        'message' => '<p>Hi</p>',
    ])->assertSessionHasNoErrors();

    expect(Message::where('sender', $this->alice->username)->count())->toBe(2);
});

it('sends nothing when one of the receivers does not exist, or there are too many', function () {
    $this->actingAs($this->alice)
        ->post(route('messages.store'), ['receivers' => json_encode([$this->bob->username, 'nobody-here']), 'message' => '<p>Hi</p>'])
        ->assertSessionHasErrors('receiver');

    $many = User::factory()->count(App\Http\Controllers\MessagesController::MAX_RECEIVERS + 1)->create()->pluck('username')->all();
    $this->actingAs($this->alice)
        ->post(route('messages.store'), ['receivers' => json_encode($many), 'message' => '<p>Hi</p>'])
        ->assertSessionHasErrors('receiver');

    $this->actingAs($this->alice)
        ->post(route('messages.store'), ['receivers' => json_encode(['all']), 'message' => '<p>Hi</p>'])
        ->assertSessionHasErrors('receiver');

    expect(Message::count())->toBe(0);
});

it('names the receivers who blocked the sender, and sends nothing', function () {
    MessageBlock::create(['user_id' => $this->carol->id, 'blocked_user_id' => $this->alice->id]);

    $this->actingAs($this->alice)
        ->post(route('messages.store'), ['receivers' => json_encode([$this->bob->username, $this->carol->username]), 'message' => '<p>Hi</p>'])
        ->assertSessionHasErrors(['receiver' => __('These observers do not accept messages from you: :names', ['names' => 'Carol Comet'])]);

    expect(Message::count())->toBe(0);
});

it('shows all receivers on the message page, with a reply to all', function () {
    $this->actingAs($this->alice)->post(route('messages.store'), [
        'receivers' => json_encode([$this->bob->username, $this->carol->username]), 'subject' => 'Star party', 'message' => '<p>Hi</p>',
    ]);
    $bobsCopy = Message::where('receiver', $this->bob->username)->first();

    $page = $this->actingAs($this->bob)->get(route('messages.show', $bobsCopy->id))
        ->assertSee('Bob Builder')->assertSee('Carol Comet')
        ->assertSee(e(route('messages.create', ['reply_to' => $bobsCopy->id, 'to' => $this->alice->username])), false)
        ->assertSee(e(route('messages.create', ['reply_to' => $bobsCopy->id, 'to' => $this->alice->username.','.$this->carol->username])), false);

    // The sender replies to both receivers.
    $this->actingAs($this->alice)->get(route('messages.show', $bobsCopy->id))
        ->assertSee(e(route('messages.create', ['reply_to' => $bobsCopy->id, 'to' => $this->bob->username.','.$this->carol->username])), false)
        ->assertDontSee(__('Reply to all'));
});

it('starts the form with the receivers of the link', function () {
    $html = $this->actingAs($this->alice)
        ->get(route('messages.create', ['to' => $this->bob->username.','.$this->carol->username]))
        ->assertOk()->getContent();

    // @js() writes the list as JSON.parse('[\u0022bob\u0022,…]').
    expect($html)->toContain('receivers: '.Illuminate\Support\Js::from([$this->bob->username, $this->carol->username]));
});

it('shows in the inbox that a received message went to several observers', function () {
    $this->actingAs($this->alice)->post(route('messages.store'), [
        'receivers' => json_encode([$this->bob->username, $this->carol->username]), 'subject' => 'Star party', 'message' => '<p>Hi</p>',
    ]);
    featureMessage($this->alice->username, $this->bob->username, 'Just for Bob');

    $inbox = $this->actingAs($this->bob)->get(route('messages.index'))->assertOk()->getContent();

    expect($inbox)->toContain(e(__('Sent to :count observers', ['count' => 2])).': Bob Builder, Carol Comet')
        ->and(substr_count($inbox, e(__('Sent to :count observers', ['count' => 2]))))->toBe(2); // tooltip and screen-reader text, once
});
