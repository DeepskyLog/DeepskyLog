<?php

use App\Jobs\SendSessionInvitations;
use App\Livewire\CreateSession;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;

it('does not include the creator when dispatching session invitations when creating a session', function () {
    Bus::fake();

    $creator = User::factory()->create(['username' => 'creatoruser']);
    // The job is only dispatched when somebody other than the creator is
    // invited, so the session needs a second participant to be worth asserting on.
    $invitee = User::factory()->create(['username' => 'inviteduser']);
    $location = Location::query()->create([
        'user_id' => $creator->id,
        'name' => 'Test Site',
        'longitude' => 4.0,
        'latitude' => 50.0,
        'timezone' => 'UTC',
        'limitingMagnitude' => -999,
        'skyBackground' => -999,
        'elevation' => 0,
        'country' => 'Belgium',
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'test-site',
    ]);

    $this->actingAs($creator);

    Livewire::test(CreateSession::class)
        ->set('name', 'Test Session')
        ->set('locationid', $location->id)
        ->set('begindate', '2026-01-01')
        ->set('enddate', '2026-01-02')
        ->set('otherObservers', [$invitee->username])
        ->call('save')
        ->assertHasNoErrors();

    Bus::assertDispatched(SendSessionInvitations::class, function ($job) use ($creator, $invitee) {
        return ! in_array($creator->username, $job->recipients, true)
            && in_array($invitee->username, $job->recipients, true);
    });
});
