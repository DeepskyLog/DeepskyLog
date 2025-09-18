<?php

namespace Tests\Feature;

use App\Models\ObservationSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowUserSessionsDraftsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sessions_page_shows_draft_sessions()
    {
        // Create a user
        $user = User::factory()->create(['username' => 'tester']);

        // Create an active session and an inactive (draft) session for this user
        ObservationSession::create([
            'observerid' => $user->username,
            'name' => 'Active Session',
            'active' => 1,
        ]);

        $draft = ObservationSession::create([
            'observerid' => $user->username,
            'name' => 'Draft Session Alpha',
            'active' => 0,
        ]);

        // Act as the user and request the sessions page for that user
        $response = $this->actingAs($user)->get(route('session.user', [$user->slug ?? $user->username]));

        $response->assertStatus(200);

        // The page should contain the 'Draft sessions' header and the draft session name
        $response->assertSee(__('Draft sessions'), false);
        $response->assertSee('Draft Session Alpha');
    }
}
