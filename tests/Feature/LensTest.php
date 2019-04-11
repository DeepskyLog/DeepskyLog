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
use Illuminate\Auth\Access\AuthorizationException;
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
     * Checks whether that a lens needs at least 6 characters.
     *
     * @test
     *
     * @return None
     */
    public function aLensShouldHaveALongEnoughName()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /lens to create a new lens
        // while passing the necessary data
        $attributes = [
            'name' => 'Test1',
            'factor' => 2.0,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('lens', $attributes);
    }

    /**
     * Checks whether that a lens needs a positive factor.
     *
     * @test
     *
     * @return None
     */
    public function aLensShouldHaveAPositiveFactorSmallerThan10()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /lens to create a new lens
        // while passing the necessary data
        $attributes = [
            'name' => 'My new lens',
            'factor' => -2.0,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('lens', $attributes);
    }

    /**
     * Checks whether that a lens needs a factor that is smaller than 10.
     *
     * @test
     *
     * @return None
     */
    public function aLensShouldHaveAFactorSmallerThan10()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /lens to create a new lens
        // while passing the necessary data
        $attributes = [
            'name' => 'My new lens',
            'factor' => 12.0,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('lens', $attributes);
    }

    /**
     * Checks whether that a lens can be updated by the owner of the lens.
     *
     * @test
     *
     * @return None
     */
    public function aLensShouldBeUpdateable()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /lens to create a new lens
        // while passing the necessary data
        $attributes = [
            'name' => 'My new lens',
            'factor' => 2.0,
        ];

        $this->post('lens', $attributes);

        // Also check if the user_id is correct
        $attributes['observer_id'] = $user->id;

        // Then there should be a new lens in the database
        $this->assertDatabaseHas('lens', $attributes);

        $lens = \App\Lens::firstOrFail();

        // Adapt the name and the factor
        $newAttributes = [
            'observer_id' => $user->id,
            'name' => 'My updated lens',
            'factor' => 2.5,
        ];

        $this->put('/lens/' . $lens->id, $newAttributes);

        // Then there should be an updated lens in the database
        $this->assertDatabaseHas('lens', $newAttributes);
    }

    /**
     * Ensure that a lens can not be updated by another user.
     *
     * @test
     *
     * @return None
     */
    public function aLensShouldNotBeUpdateableByOtherUser()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /lens to create a new lens
        // while passing the necessary data
        $attributes = [
            'name' => 'My new lens',
            'factor' => 2.0,
        ];

        $this->post('lens', $attributes);

        // Also check if the user_id is correct
        $attributes['observer_id'] = $user->id;

        // Then there should be a new lens in the database
        $this->assertDatabaseHas('lens', $attributes);

        $lens = \App\Lens::firstOrFail();

        $newUser = factory('App\User')->create();
        $this->actingAs($newUser);

        // Adapt the name and the factor
        $newAttributes = [
            'observer_id' => $newUser->id,
            'name' => 'My updated lens',
            'factor' => 2.5,
        ];

        $this->expectException(AuthorizationException::class);

        $this->put('/lens/' . $lens->id, $newAttributes);
    }

    /**
     * Ensure that a lens can be updated by an admin.
     *
     * @test
     *
     * @return None
     */
    public function aLensShouldBeUpdateableByAdmin()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /lens to create a new lens
        // while passing the necessary data
        $attributes = [
            'name' => 'My new lens',
            'factor' => 2.0,
        ];

        $this->post('lens', $attributes);

        // Also check if the user_id is correct
        $attributes['observer_id'] = $user->id;

        // Then there should be a new lens in the database
        $this->assertDatabaseHas('lens', $attributes);

        $lens = \App\Lens::firstOrFail();

        $newUser = factory('App\User')->create();
        $newUser->type = "admin";

        $this->actingAs($newUser);

        // Adapt the name and the factor
        $newAttributes = [
            'name' => 'My updated lens',
            'factor' => 2.5,
        ];

        $this->put('/lens/' . $lens->id, $newAttributes);

         // Then there should be an updated lens in the database
         $this->assertDatabaseHas('lens', $newAttributes);

    }

    /**
     * Checks whether that a lens can be deleted by the owner of the lens.
     *
     * @test
     *
     * @return None
     */
    public function aLensShouldBeDeleteable()
    {
        // TODO: Only make it possible to delete the lens if there are no observations!
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /lens to create a new lens
        // while passing the necessary data
        $attributes = [
            'name' => 'My new lens',
            'factor' => 2.0,
        ];

        $this->post('lens', $attributes);

        // Also check if the user_id is correct
        $attributes['observer_id'] = $user->id;

        // Then there should be a new lens in the database
        $this->assertDatabaseHas('lens', $attributes);

        $lens = \App\Lens::firstOrFail();

        $this->delete('/lens/' . $lens->id);

        // Then there shouldn't be a lens in the database anymore
        $this->assertDatabaseMissing('lens', $attributes);
    }

    /**
     * Ensure that a lens can not be deleted by another user.
     *
     * @test
     *
     * @return None
     */
    public function aLensShouldNotBeDeleteableByOtherUser()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /lens to create a new lens
        // while passing the necessary data
        $attributes = [
            'name' => 'My new lens',
            'factor' => 2.0,
        ];

        $this->post('lens', $attributes);

        // Also check if the user_id is correct
        $attributes['observer_id'] = $user->id;

        // Then there should be a new lens in the database
        $this->assertDatabaseHas('lens', $attributes);

        $lens = \App\Lens::firstOrFail();

        $newUser = factory('App\User')->create();
        $this->actingAs($newUser);

        $this->expectException(AuthorizationException::class);

        // Try to delete the lens
        $this->delete('/lens/' . $lens->id);
    }

    /**
     * Ensure that a lens can be deleted by an admin.
     *
     * @test
     *
     * @return None
     */
    public function aLensShouldBeDeleteableByAdmin()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /lens to create a new lens
        // while passing the necessary data
        $attributes = [
            'name' => 'My new lens',
            'factor' => 2.0,
        ];

        $this->post('lens', $attributes);

        // Also check if the user_id is correct
        $attributes['observer_id'] = $user->id;

        // Then there should be a new lens in the database
        $this->assertDatabaseHas('lens', $attributes);

        $lens = \App\Lens::firstOrFail();

        $newUser = factory('App\User')->create();
        $newUser->type = "admin";

        $this->actingAs($newUser);

        $this->delete('/lens/' . $lens->id);

         // Then there should not be a lens in the database anymore
         $this->assertDatabaseMissing('lens', $attributes);

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
