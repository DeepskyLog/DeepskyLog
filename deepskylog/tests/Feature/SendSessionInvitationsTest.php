<?php

use App\Http\Controllers\SessionController;
use App\Jobs\SendSessionInvitations;
use App\Models\ObservationSession;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Auth;

it('does not include the creator when dispatching session invitations via controller store', function () {
    Bus::fake();

    // Create a dummy user as creator
    $creator = User::factory()->create(['username' => 'creatoruser']);
    Auth::login($creator);

    // Prepare dummy request data
    $request = new \Illuminate\Http\Request([], [
        'name' => 'Test Session',
        'observer' => $creator->username,
        'active' => 1,
    ]);

    // Call controller store method
    $controller = new SessionController();
    $response = $controller->store($request);

    // The BuildSessionCopies job may be dispatched; ensure SendSessionInvitations was dispatched
    Bus::assertDispatched(SendSessionInvitations::class, function ($job) use ($creator) {
        // The creator should not be in recipients
        return is_array($job->recipients) && ! in_array($creator->username, $job->recipients, true);
    });
});
