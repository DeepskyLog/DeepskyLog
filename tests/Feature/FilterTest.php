<?php
/**
 * Tests for creating, deleting, and adapting filter.
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
 * Tests for creating, deleting, and adapting filters.
 *
 * @category Test
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class FilterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Checks whether a verified user can create a new filter.
     *
     * @test
     *
     * @return None
     */
    public function aUserCanCreateAFilter()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /filter to create a new filter
        // while passing the necessary data
        $attributes = [
            'name' => 'Test filter',
            'type' => 3,
        ];

        $this->post('filter', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $user->id;

        // Then there should be a new filter in the database
        $this->assertDatabaseHas('filters', $attributes);
    }

    /**
     * Checks whether that a filter needs at least 6 characters.
     *
     * @test
     *
     * @return None
     */
    public function aFilterShouldHaveALongEnoughName()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /filter to create a new filter
        // while passing the necessary data
        $attributes = [
            'name' => 'Test1',
            'type' => 4,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('filter', $attributes);
    }

    /**
     * Checks whether that a filter can be updated by the owner of the filter.
     *
     * @test
     *
     * @return None
     */
    public function aFilterShouldBeUpdateable()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /filter to create a new filter
        // while passing the necessary data
        $attributes = [
            'name' => 'My new filter',
            'type' => 3,
        ];

        $this->post('filter', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $user->id;

        // Then there should be a new filter in the database
        $this->assertDatabaseHas('filters', $attributes);

        $filter = \App\Filter::firstOrFail();

        // Adapt the name and the factor
        $newAttributes = [
            'user_id' => $user->id,
            'name' => 'My updated filter',
            'type' => 2,
        ];

        $this->put('/filter/' . $filter->id, $newAttributes);

        // Then there should be an updated filter in the database
        $this->assertDatabaseHas('filters', $newAttributes);
    }

    /**
     * Ensure that a filter can not be updated by another user.
     *
     * @test
     *
     * @return None
     */
    public function aFilterShouldNotBeUpdateableByOtherUser()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /filter to create a new filter
        // while passing the necessary data
        $attributes = [
            'name' => 'My new filter',
            'type' => 2,
        ];

        $this->post('filter', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $user->id;

        // Then there should be a new filter in the database
        $this->assertDatabaseHas('filters', $attributes);

        $filter = \App\Filter::firstOrFail();

        $newUser = factory('App\User')->create();
        $this->actingAs($newUser);

        // Adapt the name and the factor
        $newAttributes = [
            'user_id' => $newUser->id,
            'name' => 'My updated filter',
            'type' => 6,
        ];

        $this->expectException(AuthorizationException::class);

        $this->put('/filter/' . $filter->id, $newAttributes);
    }

    /**
     * Ensure that a filter can be updated by an admin.
     *
     * @test
     *
     * @return None
     */
    public function aFilterShouldBeUpdateableByAdmin()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /filter to create a new filter
        // while passing the necessary data
        $attributes = [
            'name' => 'My new filter',
            'type' => 2,
        ];

        $this->post('filter', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $user->id;

        // Then there should be a new filter in the database
        $this->assertDatabaseHas('filters', $attributes);

        $filter = \App\Filter::firstOrFail();

        $newUser = factory('App\User')->create();
        $newUser->type = "admin";

        $this->actingAs($newUser);

        // Adapt the name and the factor
        $newAttributes = [
            'name' => 'My updated filter',
            'type' => 3,
        ];

        $this->put('/filter/' . $filter->id, $newAttributes);

         // Then there should be an updated filter in the database
         $this->assertDatabaseHas('filters', $newAttributes);

    }

    /**
     * Checks whether that a filter can be deleted by the owner of the filter.
     *
     * @test
     *
     * @return None
     */
    public function aFilterShouldBeDeleteable()
    {
        // TODO: Only make it possible to delete the filter if there are no observations!
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /filter to create a new filter
        // while passing the necessary data
        $attributes = [
            'name' => 'My new filter',
            'type' => 2,
        ];

        $this->post('filter', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $user->id;

        // Then there should be a new filter in the database
        $this->assertDatabaseHas('filters', $attributes);

        $filter = \App\Filter::firstOrFail();

        $this->delete('/filter/' . $filter->id);

        // Then there shouldn't be a filter in the database anymore
        $this->assertDatabaseMissing('filters', $attributes);
    }

    /**
     * Ensure that a filter can not be deleted by another user.
     *
     * @test
     *
     * @return None
     */
    public function aFilterShouldNotBeDeleteableByOtherUser()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /filter to create a new filter
        // while passing the necessary data
        $attributes = [
            'name' => 'My new filter',
            'type' => 3,
        ];

        $this->post('filter', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $user->id;

        // Then there should be a new filter in the database
        $this->assertDatabaseHas('filters', $attributes);

        $filter = \App\Filter::firstOrFail();

        $newUser = factory('App\User')->create();
        $this->actingAs($newUser);

        $this->expectException(AuthorizationException::class);

        // Try to delete the filter
        $this->delete('/filter/' . $filter->id);
    }

    /**
     * Ensure that a filter can be deleted by an admin.
     *
     * @test
     *
     * @return None
     */
    public function aFilterShouldBeDeleteableByAdmin()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // When they hit the endpoint in /filter to create a new filter
        // while passing the necessary data
        $attributes = [
            'name' => 'My new filter',
            'type' => 2,
        ];

        $this->post('filter', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $user->id;

        // Then there should be a new filter in the database
        $this->assertDatabaseHas('filters', $attributes);

        $filter = \App\Filter::firstOrFail();

        $newUser = factory('App\User')->create();
        $newUser->type = "admin";

        $this->actingAs($newUser);

        $this->delete('/filter/' . $filter->id);

         // Then there should not be a filter in the database anymore
         $this->assertDatabaseMissing('filters', $attributes);

    }

    /**
     * Checks whether a guest is not allowed to create a new filter.
     *
     * @test
     *
     * @return None
     */
    public function guestsMayNotCreateAFilter()
    {
        $this->withoutExceptionHandling();

        $this->assertGuest();

        // When they hit the endpoint in /filter to create a new filter while
        // passing the necessary data
        $attributes = [
            'name' => 'Test filter',
            'factor' => 2.0
        ];

        $this->expectException(\Illuminate\Auth\AuthenticationException::class);

        $this->post('/filter', $attributes);
    }

    /**
     * Unverified users are not allowed to create a new filter.
     *
     * @test
     *
     * @return None
     */
    public function unverifiedUsersMayNotCreateAFilter()
    {
        //$this->withoutExceptionHandling();

        // Given I am a user who is logged in and not verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create();

        $user->email_verified_at = null;

        $this->actingAs($user);

        // When they hit the endpoint in /filter to create a new filter while
        // passing the necessary data
        $attributes = [
            'user_id' => $user->id,
            'name' => 'Test filter for unverified user',
            'type' => 2
        ];

        $this->post('/filter', $attributes);

        $this->assertDatabaseMissing('filters', $attributes);
    }
}
