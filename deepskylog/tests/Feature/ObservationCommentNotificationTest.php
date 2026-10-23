<?php

use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\MessageBlock;
use App\Models\Observation;
use App\Models\ObservingList;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

// Issue #965: a comment on an observation tells its observer, as a comment on
// an observing list tells the owner of the list.

function commentedObservation(User $owner): Observation
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

it('sends the observer a message when another observer comments on the observation', function () {
    Mail::fake();
    $owner = User::factory()->create(['username' => 'observer', 'sendMail' => true]);
    $commenter = User::factory()->create(['username' => 'commenter']);
    $observation = commentedObservation($owner);

    $this->actingAs($commenter)
        ->post(route('observation.comments.store'), ['observation_id' => $observation->id, 'body' => 'Did you see the <b>dust lane</b>?'])
        ->assertSessionHas('success');

    $message = Message::where('receiver', 'observer')->sole();
    expect($message->sender)->toBe('commenter')
        ->and($message->subject)->toContain('M 65')
        ->and($message->message)->toContain('Did you see the dust lane?')
        ->and($message->message)->toContain(route('observations.detail', ['observer' => $owner->slug, 'date' => '2026-03-01', 'object' => 'm-65']));
    expect(Message::getNumberOfUnreadMails('observer'))->toBe(1);
    Mail::assertQueued(MessageReceived::class, fn (MessageReceived $mail) => $mail->hasTo($owner->email));
});

it('writes the message in the language of the observer', function () {
    $owner = User::factory()->create(['username' => 'observer', 'language' => 'nl']);
    $commenter = User::factory()->create(['language' => 'en']);
    $observation = commentedObservation($owner);

    $this->actingAs($commenter)
        ->post(route('observation.comments.store'), ['observation_id' => $observation->id, 'body' => 'Mooi']);

    expect(Message::where('receiver', 'observer')->sole()->subject)->toBe('Nieuwe reactie op je waarneming van M 65');
});

it('does not mail an observer who turned the mails off', function () {
    Mail::fake();
    $owner = User::factory()->create(['sendMail' => false]);
    $observation = commentedObservation($owner);

    $this->actingAs(User::factory()->create())
        ->post(route('observation.comments.store'), ['observation_id' => $observation->id, 'body' => 'Nice']);

    expect(Message::where('receiver', $owner->username)->count())->toBe(1);
    Mail::assertNothingQueued();
});

it('does not send a message when the observer comments on their own observation', function () {
    $owner = User::factory()->create();
    $observation = commentedObservation($owner);

    $this->actingAs($owner)
        ->post(route('observation.comments.store'), ['observation_id' => $observation->id, 'body' => 'A note to myself'])
        ->assertSessionHas('success');

    expect(Message::count())->toBe(0);
});

it('does not send a message for a comment of a blocked observer', function () {
    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    MessageBlock::create(['user_id' => $owner->id, 'blocked_user_id' => $commenter->id]);
    $observation = commentedObservation($owner);

    $this->actingAs($commenter)
        ->post(route('observation.comments.store'), ['observation_id' => $observation->id, 'body' => 'Hello again'])
        ->assertSessionHas('success');

    expect(Message::count())->toBe(0);
});

it('does not send a message for a comment of a blocked observer on an observing list', function () {
    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    MessageBlock::create(['user_id' => $owner->id, 'blocked_user_id' => $commenter->id]);
    $list = ObservingList::create(['owner_user_id' => $owner->id, 'name' => 'Spring Galaxies', 'public' => true]);

    $this->actingAs($commenter)
        ->post(route('observing-list.comments.store', ['list' => $list]), ['body' => 'Hello again'])
        ->assertSessionHas('success');

    expect(Message::count())->toBe(0);
});
