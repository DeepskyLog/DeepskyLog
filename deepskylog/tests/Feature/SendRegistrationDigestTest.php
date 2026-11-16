<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    $this->first = User::factory()->create(['registration_reported_at' => now()]);
    $team = $this->getTeamFromString('Administrators');
    $this->admin = User::factory()->create(['registration_reported_at' => now()]);
    $this->admin->teams()->attach($team);
    $this->admin->switchTeam($team);
    $this->expert = User::factory()->create(['registration_reported_at' => now()]);
    $this->expert->teams()->attach($this->getTeamFromString('Database Experts'));
});

it('sends nothing without --apply', function () {
    User::factory()->create();

    $this->artisan('users:send-registration-digest')->assertSuccessful();

    expect(Message::count())->toBe(0);
});

it('sends the administrators one message with the new accounts, once', function () {
    $new = User::factory()->create(['name' => 'Jan Janssens', 'country' => 'BE']);
    $other = User::factory()->create();

    $this->artisan('users:send-registration-digest --apply')->assertSuccessful();

    $messages = Message::all();
    expect($messages)->toHaveCount(1)
        ->and($messages[0]->receiver)->toBe($this->admin->username)
        ->and($messages[0]->subject)->toContain('2 new observers')
        ->and($messages[0]->message)->toContain($new->username)
        ->and($messages[0]->message)->toContain('Jan Janssens')
        ->and($messages[0]->message)->toContain(route('observer.show', $other->slug))
        ->and($new->fresh()->registration_reported_at)->not->toBeNull();

    $this->artisan('users:send-registration-digest --apply')->assertSuccessful();
    expect(Message::count())->toBe(1);

    User::factory()->create();
    $this->artisan('users:send-registration-digest --apply')->assertSuccessful();
    expect(Message::count())->toBe(2)
        ->and(Message::latest('id')->first()->subject)->toContain('A new observer');
});

it('sends nothing when nobody registered', function () {
    $this->artisan('users:send-registration-digest --apply')->assertSuccessful();

    expect(Message::count())->toBe(0);
});
