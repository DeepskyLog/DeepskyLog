<?php
/**
 * Tests for creating, deleting, and adapting lenses.
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

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Tests for creating, deleting, and adapting lenses.
 *
 * @category Test
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class LensTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Checks whether a verified user can create a new lens.
     *
     * @test
     *
     * @return None
     */
    public function aUserCanCreateALens()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /lens to create a new lens
        // while passing the necessary data
        $attributes = [
            'name' => 'Test lens',
            'factor' => 2.0,
        ];

        $this->post('lens', $attributes);

        // Also check if the user_id is correct
        $attributes['observer_id'] = $user->id;

        // Then there should be a new lens in the database
        $this->assertDatabaseHas('lens', $attributes);
    }

    /**
     * Checks whether a guest is not allowed to create a new lens.
     *
     * @test
     *
     * @return None
     */
    public function guestsMayNotCreateALens()
    {
        $this->withoutExceptionHandling();

        $this->assertGuest();

        // When they hit the endpoint in /lens to create a new lens while
        // passing the necessary data
        $attributes = [
            'name' => 'Test lens',
            'factor' => 2.0
        ];

        $this->expectException(\Illuminate\Auth\AuthenticationException::class);

        $this->post('/lens', $attributes);
    }

    /**
     * Unverified users are not allowed to create a new lens.
     *
     * @test
     *
     * @return None
     */
    public function unverifiedUsersMayNotCreateALens()
    {
        //$this->withoutExceptionHandling();

        // Given I am a user who is logged in and not verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();

        $user->email_verified_at = null;

        $this->actingAs($user);

        // When they hit the endpoint in /lens to create a new lens while
        // passing the necessary data
        $attributes = [
            'observer_id' => $user->id,
            'name' => 'Test lens for unverified user',
            'factor' => 2.5
        ];

        $this->post('/lens', $attributes);

        $this->assertDatabaseMissing('lens', $attributes);
    }
}
