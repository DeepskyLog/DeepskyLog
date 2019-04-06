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
        $this->actingAs(factory('App\User')->create());

        // When they hit the endpoint in /lens to create a new lens
        // while passing the necessary data
        $attributes = [
            'name' => 'Test lens',
            'factor' => 2.0
        ];

        $this->post('/lens', $attributes);

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

        // When they hit the endpoint in /lens to create a new lens while
        // passing the necessary data
        $attributes = [
            'name' => 'Test lens',
            'factor' => 2.0
        ];

        $this->post('/lens', $attributes)->assertRedirect('/');

        // Can work with changes in web.php:
        // Route::middleware('auth')->post('lens', ...)
    }
}
