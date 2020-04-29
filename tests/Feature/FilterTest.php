<?php
/**
 * Tests for creating, deleting, and adapting filter.
 *
 * PHP Version 7
 *
 * @category Test
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace Tests\Feature;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tests for creating, deleting, and adapting filters.
 *
 * @category Test
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class FilterTest extends TestCase
{
    use RefreshDatabase;

    private $_user;

    /**
     * Set up the user.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setup();

        $this->_user = factory('App\User')->create();
    }

    /**
     * Checks whether a guest user can see the list with filters.
     *
     * @test
     *
     * @return None
     */
    public function listFilterNotLoggedIn()
    {
        $response = $this->get('/filter');
        // Code 302 is the code for redirecting
        $response->assertStatus(302);
        // Check if we are redirected to the login page
        $response->assertRedirect('/login');
    }

    /**
     * Checks whether a real user can see the list with filters.
     *
     * @test
     *
     * @return None
     */
    public function listFiltersLoggedIn()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $response = $this->get('/filter');
        // Code 200 is the code for a working page
        $response->assertStatus(200);
        // Check if we see the correct page
        $response->assertSee('Filters of');
    }

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
        $this->actingAs($this->_user);

        // When they hit the endpoint in /filter to create a new filter
        // while passing the necessary data
        $attributes = [
            'name' => 'Test filter',
            'type' => 3,
        ];

        $this->post('filter', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

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
        $this->actingAs($this->_user);

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
     * Checks whether that a filter needs at least 6 characters after update.
     *
     * @test
     *
     * @return None
     */
    public function aFilterShouldHaveALongEnoughNameAfterUpdate()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $filter = factory('App\Filter')->create(['user_id' => $this->_user->id]);

        $response = $this->actingAs($this->_user)->put(
            '/filter/'.$filter->id,
            ['name' => 'test', 'type' => 3]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);
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
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // Get a new filter from the factory
        $filter = factory('App\Filter')->create(['user_id' => $this->_user->id]);

        // Then there should be a new filter in the database
        $attributes = [
            'user_id' => $filter->user_id,
            'name' => $filter->name,
            'type' => $filter->type,
            'color' => $filter->color,
            'wratten' => $filter->wratten,
            'schott' => $filter->schott,
            'active' => $filter->active,
        ];

        $this->assertDatabaseHas('filters', $attributes);

        // Adapt the name and the factor
        $newAttributes = [
            'user_id' => $filter->user_id,
            'name' => 'My updated filter',
            'type' => 2,
            'color' => $filter->color,
            'wratten' => $filter->wratten,
            'schott' => $filter->schott,
        ];

        $this->put('filter/'.$filter->id, $newAttributes);

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
        $this->actingAs($this->_user);

        // When they hit the endpoint in /filter to create a new filter
        // while passing the necessary data
        $attributes = [
            'name' => 'My new filter',
            'type' => 2,
        ];

        $this->post('filter', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

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

        $this->put('/filter/'.$filter->id, $newAttributes);
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
        $this->actingAs($this->_user);

        // When they hit the endpoint in /filter to create a new filter
        // while passing the necessary data
        $attributes = [
            'name' => 'My new filter',
            'type' => 2,
        ];

        $this->post('filter', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

        // Then there should be a new filter in the database
        $this->assertDatabaseHas('filters', $attributes);

        $filter = \App\Filter::firstOrFail();

        $newUser = factory('App\User')->create(['type' => 'admin']);

        $this->actingAs($newUser);

        // Adapt the name and the factor
        $newAttributes = [
            'name' => 'My updated filter',
            'type' => 3,
        ];

        $this->put('/filter/'.$filter->id, $newAttributes);

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
        // TODO: Only make it possible to delete the filter if there are
        // no observations!
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /filter to create a new filter
        // while passing the necessary data
        $filter = factory('App\Filter')->create(['user_id' => $this->_user->id]);

        // Then there should be a new filter in the database
        $this->assertDatabaseHas(
            'filters',
            [
                'name' => $filter->name,
                'type' => $filter->type,
                'color' => $filter->color,
                'user_id' => $filter->user_id,
            ]
        );

        $this->assertEquals(1, \App\Filter::count());

        $response = $this->delete('/filter/'.$filter->id);

        $response->assertStatus(302);

        // Then there shouldn't be a filter in the database anymore
        $this->assertDatabaseMissing(
            'filters',
            [
                'name' => $filter->name,
                'type' => $filter->type,
                'color' => $filter->color,
                'user_id' => $filter->user_id,
            ]
        );
        $this->assertEquals(0, \App\Filter::count());
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
        $this->actingAs($this->_user);

        // When they hit the endpoint in /filter to create a new filter
        // while passing the necessary data
        $attributes = [
            'name' => 'My new filter',
            'type' => 3,
        ];

        $this->post('filter', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

        // Then there should be a new filter in the database
        $this->assertDatabaseHas('filters', $attributes);

        $filter = \App\Filter::firstOrFail();

        $newUser = factory('App\User')->create();
        $this->actingAs($newUser);

        $this->expectException(AuthorizationException::class);

        // Try to delete the filter
        $this->delete('/filter/'.$filter->id);
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
        $this->actingAs($this->_user);

        // When they hit the endpoint in /filter to create a new filter
        // while passing the necessary data
        $attributes = [
            'name' => 'My new filter',
            'type' => 2,
        ];

        $this->post('filter', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

        // Then there should be a new filter in the database
        $this->assertDatabaseHas('filters', $attributes);

        $filter = \App\Filter::firstOrFail();

        $newUser = factory('App\User')->create(['type' => 'admin']);

        $this->actingAs($newUser);

        $this->delete('/filter/'.$filter->id);

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
            'factor' => 2.0,
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
        // Given I am a user who is logged in and not verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create(['email_verified_at' => null]);

        $this->actingAs($user);

        // When they hit the endpoint in /filter to create a new filter while
        // passing the necessary data
        $attributes = [
            'user_id' => $user->id,
            'name' => 'Test filter for unverified user',
            'type' => 2,
        ];

        $this->post('/filter', $attributes);

        $this->assertDatabaseMissing('filters', $attributes);
    }

    /**
     * Ensure that the create filter page is not accessible for guests.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsNotAccessibleForGuests()
    {
        $response = $this->get('/filter/create');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Ensure that the create filter page is not accessible for unverified users.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsNotAccessibleForUnverifiedUsers()
    {
        $user = factory('App\User')->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)->get('/filter/create');

        $response->assertStatus(302);
        $response->assertRedirect('/email/verify');
    }

    /**
     * Ensure that the create filter page is accessible for real users.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsAccessibleForUser()
    {
        $response = $this->actingAs($this->_user)->get('/filter/create');

        $response->assertStatus(200);
    }

    /**
     * Ensure that the create filter page is not accessible for adminstrators.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsAccessibleForAdmin()
    {
        $user = factory('App\User')->create(['type' => 'admin']);
        $response = $this->actingAs($user)->get('/filter/create');

        $response->assertStatus(200);
    }

    /**
     * Ensure that the update filter page contains the correct values.
     *
     * @test
     *
     * @return None
     */
    public function updateFilterPageContainsCorrectValues()
    {
        $filter = factory('App\Filter')->create(['user_id' => $this->_user->id]);

        $response = $this->actingAs($this->_user)->get(
            '/filter/'.$filter->id.'/edit'
        );

        $response->assertStatus(200);
        $response->assertSee($filter->name);
    }

    /**
     * Ensure that we can upload a picture.
     *
     * @test
     */
    public function testCreateFilterFileUploaded()
    {
        // Will put the fake image in
        Storage::fake('public');

        $this->actingAs($this->_user)->post(
            'filter',
            [
                'name' => 'Test filter',
                'type' => 3,
                'picture' => UploadedFile::fake()->image('filter.png'),
            ]
        );

        $filter = \App\Filter::firstOrFail();

        Storage::disk('public')->assertExists(
            $filter->id.'/'.$filter->id.'.png'
        );
    }

    /**
     * Ensure that the owner of a filter can see the change filter button.
     *
     * @test
     *
     * @return void
     */
    public function testShowFilterDetailWithChangeButton()
    {
        $filter = factory('App\Filter')->create(['user_id' => $this->_user->id]);

        $response = $this->actingAs($this->_user)->get('/filter/'.$filter->id);

        $response->assertStatus(200);
        $response->assertSee($filter->name);
        $response->assertSee($this->_user->name);
        $response->assertSee('Edit '.$filter->name);
    }

    /**
     * Ensure that a different user than the owner of a filter cannot
     * see the change filter button.
     *
     * @test
     *
     * @return void
     */
    public function testShowFilterDetailWithoutChangeButton()
    {
        $newUser = factory('App\User')->create();
        $filter = factory('App\Filter')->create(['user_id' => $newUser->id]);

        $response = $this->actingAs($this->_user)->get('/filter/'.$filter->id);

        $response->assertStatus(200);
        $response->assertSee($filter->name);
        $response->assertSee($this->_user->name);
        $response->assertDontSee('Edit '.$filter->name);
    }

    /**
     * Ensure that an admin can always see the change filter button.
     *
     * @test
     *
     * @return void
     */
    public function testAdminAlwaysSeesChangeButton()
    {
        $admin = factory('App\User')->create(['type' => 'admin']);
        $filter = factory('App\Filter')->create(['user_id' => $this->_user->id]);

        $response = $this->actingAs($admin)->get('/filter/'.$filter->id);

        $response->assertStatus(200);
        $response->assertSee($filter->name);
        $response->assertSee($this->_user->name);
        $response->assertSee('Edit '.$filter->name);
    }

    /**
     * Ensure that a guest user can not see the change filter button.
     *
     * @test
     *
     * @return void
     */
    public function testGuestNeverSeesChangeButton()
    {
        $filter = factory('App\Filter')->create(['user_id' => $this->_user->id]);

        $response = $this->get('/filter/'.$filter->id);

        $response->assertStatus(200);
        $response->assertSee($filter->name);
        $response->assertSee($this->_user->name);
        $response->assertDontSee('Edit '.$filter->name);
    }

    /**
     * Ensure that only an admin can see the admin page with all the filters.
     *
     * @test
     *
     * @return void
     */
    public function testOnlyAdminCanSeeOverviewOfAllFilters()
    {
        factory('App\User', 50)->create();
        $filter = factory('App\Filter', 500)->create();

        // Check as guest
        $response = $this->get('/filter/admin');

        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // Check as normal user
        $response = $this->actingAs($this->_user)->get('/filter/admin');

        $response->assertStatus(401);

        // Check as admin
        $admin = factory('App\User')->create(['type' => 'admin']);
        $response = $this->actingAs($admin)->get('/filter/admin');

        $response->assertStatus(200);
        $response->assertSee('All filters');
    }

    /**
     * Ensure that logged in users can see the Json information of a filter.
     *
     * @test
     *
     * @return void
     */
    public function testJsonInformationForFilter()
    {
        $filter = factory('App\Filter')->create(['user_id' => $this->_user->id]);

        // Only for logged in users!
        $response = $this->get('/getFilterJson/'.$filter->id);
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // Test for logged in user
        $response = $this->actingAs($this->_user)->get(
            '/getFilterJson/'.$filter->id
        );

        $this->assertEquals($response['name'], $filter->name);
        $this->assertEquals($response['id'], $filter->id);
        $this->assertEquals($response['user_id'], $filter->user_id);
        $this->assertEquals($response['type'], $filter->type);
        $this->assertEquals($response['color'], $filter->color);
        $this->assertEquals($response['wratten'], $filter->wratten);
        $this->assertEquals($response['schott'], $filter->schott);
        $this->assertEquals($response['active'], $filter->active);
    }

    /**
     * Ensure that we get an image of a filter.
     *
     * @test
     */
    public function testGetFilterImage()
    {
        // Will put the fake image in
        Storage::fake('public');

        $filter = factory('App\Filter')->create(['user_id' => $this->_user->id]);

        // Check the image, if no image is uploaded
        $this->actingAs($this->_user)->get('filter/'.$filter->id.'/getImage');

        Storage::disk('public')->assertExists(
            $filter->id.'/'.$filter->id.'.png'
        );

        // Check the image if we have uploaded an image
        $this->actingAs($this->_user)->post(
            'filter',
            [
                'name' => 'Test filter',
                'type' => 3,
                'picture' => UploadedFile::fake()->image('filter.png'),
            ]
        );

        $filter2 = DB::table('filters')->latest('id')->first();

        Storage::disk('public')->assertExists(
            $filter2->id.'/'.$filter2->id.'.png'
        );
    }

    /**
     * Ensure that we can delete an image of a filter.
     *
     * @test
     */
    public function testDeleteFilterImage()
    {
        // Will put the fake image in
        Storage::fake('public');

        // Check if we can delete the image if we have uploaded an image
        $this->actingAs($this->_user)->post(
            'filter',
            [
                'name' => 'Test filter',
                'type' => 3,
                'picture' => UploadedFile::fake()->image('filter.png'),
            ]
        );

        $filter = DB::table('filters')->latest('id')->first();

        $this->actingAs($this->_user)->post(
            'filter/'.$filter->id.'/deleteImage'
        );

        Storage::disk('public')->assertMissing(
            $filter->id.'/'.$filter->id.'.png'
        );

        // Check if another user cannot delete the image if we have uploaded an image
        $this->actingAs($this->_user)->post(
            'filter',
            [
                'name' => 'Test filter',
                'type' => 3,
                'picture' => UploadedFile::fake()->image('filter.png'),
            ]
        );

        $filter = DB::table('filters')->latest('id')->first();

        $user = factory('App\User')->create();

        $this->actingAs($user)->post(
            'filter/'.$filter->id.'/deleteImage'
        );

        Storage::disk('public')->assertExists(
            $filter->id.'/'.$filter->id.'.png'
        );
    }

    /**
     * Ensure that the autocomplete works for select2.
     *
     * @test
     *
     * @return void
     */
    public function testAutocompleteForFilter()
    {
        $filter = factory('App\Filter')->create(
            ['user_id' => $this->_user->id, 'name' => 'DeepskyLog test filter']
        );

        $filter2 = factory('App\Filter')->create(
            ['user_id' => $this->_user->id, 'name' => 'Other test filter']
        );

        // Only for logged in users!
        $response = $this->get('/filter/autocomplete?q=Deep');
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // Test for logged in user
        $response = $this->actingAs($this->_user)->get(
            '/filter/autocomplete?q=Deep'
        );

        $this->assertEquals($filter->id, $response[0]['id']);
        $this->assertEquals($filter->name, $response[0]['name']);

        $response = $this->actingAs($this->_user)->get(
            '/filter/autocomplete?q=test'
        );

        $this->assertEquals($filter->id, $response[0]['id']);
        $this->assertEquals($filter->name, $response[0]['name']);

        $this->assertEquals($filter2->id, $response[1]['id']);
        $this->assertEquals($filter2->name, $response[1]['name']);
    }
}
