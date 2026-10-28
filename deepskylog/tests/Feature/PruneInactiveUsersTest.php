<?php

use App\Mail\InactiveAccountWarning;
use App\Models\Message;
use App\Models\User;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    $this->travelTo(now()->setTime(3, 30));
    // The first account owns the system teams and is never deleted
    $this->first = User::factory()->create(['created_at' => now()->subYears(10)]);
});

function registeredMonthsAgo(int $months): User
{
    return User::factory()->create(['created_at' => now()->subMonths($months)]);
}

function prune(string $options = '--apply'): void
{
    test()->artisan('users:prune-inactive '.$options)->assertSuccessful();
}

/**
 * The daily runs that matter for an account warned today: the first warning
 * now, the final warning 24 days later, the deletion 8 days after that.
 */
function pruneThroughWarnings(string $options = '--apply'): void
{
    prune($options);
    test()->travel(24)->days();
    prune($options);
    test()->travel(8)->days();
    prune($options);
}

it('sends nothing and deletes nothing without --apply', function () {
    $user = registeredMonthsAgo(30);

    pruneThroughWarnings('');

    Mail::assertNothingSent();
    expect(User::find($user->id))->not->toBeNull();
});

it('warns a month and a week before deleting an account that recorded nothing', function () {
    $user = registeredMonthsAgo(30);
    $start = now()->copy();

    prune();
    Mail::assertSent(InactiveAccountWarning::class, 1);
    Mail::assertSent(InactiveAccountWarning::class, fn ($mail) => $mail->hasTo($user->email)
        && $mail->deletionDate->isSameDay($start->copy()->addMonth()));
    $user->refresh();
    expect($user->inactivity_deletion_at->isSameDay($start->copy()->addMonth()))->toBeTrue();

    // Nothing more until a week before the deletion
    $this->travel(10)->days();
    prune();
    Mail::assertSent(InactiveAccountWarning::class, 1);

    $this->travelTo($user->inactivity_deletion_at->copy()->subWeek());
    prune();
    Mail::assertSent(InactiveAccountWarning::class, 2);
    expect(User::find($user->id))->not->toBeNull();

    $this->travelTo($user->inactivity_deletion_at->copy()->subHour());
    prune();
    expect(User::find($user->id))->not->toBeNull();

    $this->travelTo($user->inactivity_deletion_at->copy()->addMinute());
    prune();
    expect(User::find($user->id))->toBeNull()
        ->and(User::find($this->first->id))->not->toBeNull();
    Mail::assertSent(InactiveAccountWarning::class, 2);
});

it('deletes a recent account 24 months after registration', function () {
    $user = User::factory()->create(['created_at' => now()->subMonths(23)->subDay()]);

    prune();
    // At least 24 months after registration, and a month after the warning
    $deletion = $user->fresh()->inactivity_deletion_at;
    expect($deletion->gte($user->created_at->copy()->addMonths(24)))->toBeTrue()
        ->and($deletion->isSameDay(now()->addMonth()))->toBeTrue();

    $this->travelTo($deletion->copy()->subWeek());
    prune();
    $this->travelTo($deletion->copy()->addDay());
    prune();

    expect(User::find($user->id))->toBeNull();
    Mail::assertSent(InactiveAccountWarning::class, 2);
});

it('does not warn an account younger than 23 months', function () {
    registeredMonthsAgo(22);

    prune();

    Mail::assertNothingSent();
});

it('gives the final warning a week of notice when a run was missed', function () {
    $user = registeredMonthsAgo(30);
    prune();
    $deletion = $user->fresh()->inactivity_deletion_at;

    // No run in the last week before the deletion date
    $this->travelTo($deletion->copy()->addDay());
    prune();

    Mail::assertSent(InactiveAccountWarning::class, 2);
    expect(User::find($user->id))->not->toBeNull()
        ->and($user->fresh()->inactivity_deletion_at->isSameDay(now()->addWeek()))->toBeTrue();
});

it('keeps a warned account that records something, and clears the warning', function () {
    $user = registeredMonthsAgo(30);
    prune();

    InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);
    $this->travel(40)->days();
    prune();

    $user->refresh();
    expect($user)->not->toBeNull()
        ->and($user->inactivity_deletion_at)->toBeNull()
        ->and($user->inactivity_final_warning_at)->toBeNull();
    Mail::assertSent(InactiveAccountWarning::class, 1);
});

it('keeps an account with an observation', function () {
    $user = registeredMonthsAgo(30);
    DB::table('observations')->insert([
        'observer_username' => $user->username,
        'target_name' => 'M 31',
        'observed_on' => '2020-01-01',
    ]);

    pruneThroughWarnings();

    Mail::assertNothingSent();
    expect(User::find($user->id))->not->toBeNull();
});

it('keeps an account that sent a message', function () {
    $user = registeredMonthsAgo(30);
    Message::create(['sender' => $user->username, 'receiver' => $this->first->username, 'subject' => 'Hi', 'message' => '-', 'date' => now()]);

    pruneThroughWarnings();

    expect(User::find($user->id))->not->toBeNull();
});

it('keeps administrators and database experts', function () {
    $admin = $this->createUserAndAssignToTeam('Administrators');
    $admin->forceFill(['created_at' => now()->subYears(5)])->save();
    $expert = registeredMonthsAgo(30);
    $this->addUserToTeam($expert, 'Database Experts');

    pruneThroughWarnings();

    Mail::assertNothingSent();
    expect(User::find($admin->id))->not->toBeNull()
        ->and(User::find($expert->id))->not->toBeNull();
});

it('deletes the inbox of a deleted account', function () {
    $user = registeredMonthsAgo(30);
    $message = Message::create(['sender' => $this->first->username, 'receiver' => $user->username, 'subject' => 'Hi', 'message' => '-', 'date' => now()]);
    $broadcast = Message::create(['sender' => $this->first->username, 'receiver' => 'all', 'subject' => 'News', 'message' => '-', 'date' => now()]);
    DB::table('messages_read')->insert(['id' => $broadcast->id, 'receiver' => $user->username]);

    pruneThroughWarnings();

    expect(User::find($user->id))->toBeNull()
        ->and(Message::find($message->id))->toBeNull()
        ->and(Message::find($broadcast->id))->not->toBeNull()
        ->and(DB::table('messages_read')->where('receiver', $user->username)->exists())->toBeFalse();
});

it('honours --months', function () {
    $user = registeredMonthsAgo(13);

    pruneThroughWarnings('--apply --months=12');

    expect(User::find($user->id))->toBeNull();
});

it('sends the warning in the language of the observer', function () {
    $user = User::factory()->create(['created_at' => now()->subMonths(30), 'language' => 'nl']);

    prune();

    Mail::assertSent(InactiveAccountWarning::class, fn ($mail) => $mail->locale === 'nl');
    $html = (new InactiveAccountWarning($user, now()->addMonth()))->locale('nl')->render();
    expect($html)->toContain('wordt verwijderd op')->toContain(e($user->username));
});
