<?php
/**
 * Tests for creating, deleting, and adapting messages.
 *
 * PHP Version 7
 *
 * @category Test
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;
use Cmgmyr\Messenger\Models\Thread;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Tests for creating, deleting, and adapting messages.
 *
 * @category Test
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class MessagesTest extends TestCase
{
    use RefreshDatabase;

    private $_user;

    /**
     * Set up the user.
     *
     */
    public function setUp(): void
    {
        parent::setup();

        $this->_user = User::factory()->create();
    }

    /**
     * Checks whether a guest user can see the messages.
     *
     * @test
     *
     * @return None
     */
    public function listMessagesNotLoggedIn()
    {
        $response = $this->get('/messages');
        // Code 302 is the code for redirecting
        $response->assertStatus(302);
        // Check if we are redirected to the login page
        $response->assertRedirect('/login');
    }

    /**
     * Checks whether a real user can see the list with messages.
     *
     * @test
     *
     * @return None
     */
    public function listEmptyMessagesLoggedIn()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $response = $this->get('/messages');
        // Code 200 is the code for a working page
        $response->assertStatus(200);
        // Check if we see the correct page
        $response->assertSee('New messages');
        $response->assertSee('Old messages');
        $response->assertSee('Start new conversation');
    }

    /**
     * Checks whether a real user can see the list with messages.
     *
     * @test
     *
     * @return None
     */
    public function listMessagesLoggedIn()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $faker = \Faker\Factory::create();

        $thread = Thread::create(
            [
                'subject' => $faker->sentence($nbWords = 6, $variableNbWords = true),
            ]
        );

        $message = Message::create(
            [
                'thread_id' => $thread->id,
                'user_id' => $this->_user->id,
                'body' => $faker->paragraph(
                    $nbSentences = 3,
                    $variableNbSentences = true
                ),
            ]
        );

        // Sender
        $participant = Participant::create(
            [
                'thread_id' => $thread->id,
                'user_id' => $this->_user->id,
                'last_read' => new Carbon,
            ]
        );

        // Add recipient
        $newUser = User::factory()->create();
        $thread->addParticipant($newUser->id);

        $response = $this->get('/messages');
        // Code 200 is the code for a working page
        $response->assertStatus(200);

        // Check if we see the correct page
        $response->assertSee('New messages');

        $response->assertViewIs('layout.messages.index');

        // $this->assertEquals($this->_user->instruments->first()->id, $instrument->id);
        // $this->assertEquals(
        //     $this->_user->instruments->first()->name,
        //     $instrument->name
        // );
        // $this->assertEquals(
        //     $this->_user->instruments->first()->brand,
        //     $instrument->brand
        // );
        // $this->assertEquals(
        //     $this->_user->instruments->first()->focalLength,
        //     floatval($instrument->focalLength)
        // );
        // $this->assertEquals(
        //     $this->_user->instruments->first()->type,
        //     $instrument->type
        // );
        // $this->assertEquals(
        //     $this->_user->instruments->first()->apparentFOV,
        //     $instrument->apparentFOV
        // );
        // $this->assertEquals(
        //     $this->_user->instruments->first()->maxFocalLength,
        //     $instrument->maxFocalLength
        // );
        // $this->assertEquals(
        //     $this->_user->instruments->first()->active,
        //     $instrument->active
        // );
        // $this->assertEquals(
        //     $this->_user->instruments->first()->user_id,
        //     $instrument->user_id
        // );
    }
}
