<?php

use App\Http\Controllers\SessionController;
use App\Jobs\SendSessionInvitations;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;

it('does not include the creator when dispatching session invitations via controller store', function () {
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

    Auth::login($creator);

    // store() validates the request body, so this has to be a real POST --
    // Laravel reads a GET request's input from the query string instead.
    $request = Request::create('/session', 'POST', [
        'name' => 'Test Session',
        'observer' => $creator->username,
        'locationid' => $location->id,
        'begindate' => '2026-01-01',
        'enddate' => '2026-01-02',
        'otherObservers' => $invitee->username,
        'active' => 1,
    ]);

    (new SessionController)->store($request);

    Bus::assertDispatched(SendSessionInvitations::class, function ($job) use ($creator, $invitee) {
        return ! in_array($creator->username, $job->recipients, true)
            && in_array($invitee->username, $job->recipients, true);
    });
});
