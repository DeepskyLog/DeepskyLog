<?php

use App\Models\Team;
use App\Models\User;
use Laravel\Jetstream\Mail\TeamInvitation;

it('escapes the team name in the invitation mail', function () {
    $owner = User::factory()->create();
    $team = Team::forceCreate(['user_id' => $owner->id, 'name' => '<a href="https://evil.test">Club</a>', 'personal_team' => false]);
    $invitation = $team->teamInvitations()->create(['email' => 'new@example.test', 'role' => 'editor']);

    $html = (new TeamInvitation($invitation))->render();

    expect($html)->not->toContain('<a href="https://evil.test">')
        // The Markdown conversion turns &quot; back into ", harmless with < and > escaped.
        ->and($html)->toContain('&lt;a href="https://evil.test"&gt;Club&lt;/a&gt;');
});
