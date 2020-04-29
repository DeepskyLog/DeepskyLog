<?php
/**
 * Tests for creating, deleting, and adapting lenses.
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
 * Tests for creating, deleting, and adapting lenses.
 *
 * @category Test
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class LensTest extends TestCase
{
    use RefreshDatabase;

    private $_user;

    /**
     * Set up the user.
     */
    public function setUp(): void
    {
        parent::setup();

        $this->_user = factory('App\User')->create();
    }

    /**
     * Checks whether a guest user can see the list with lenses.
     *
     * @test
     *
     * @return None
     */
    public function listLensesNotLoggedIn()
    {
        $response = $this->get('/lens');
        // Code 302 is the code for redirecting
        $response->assertStatus(302);
        // Check if we are redirected to the login page
        $response->assertRedirect('/login');
    }

    /**
     * Checks whether a real user can see the list with lenses.
     *
     * @test
     *
     * @return None
     */
    public function listLensesLoggedIn()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $response = $this->get('/lens');
        // Code 200 is the code for a working page
        $response->assertStatus(200);
        // Check if we see the correct page
        $response->assertSee('Lenses of');
    }

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
        $this->actingAs($this->_user);

        // When they hit the endpoint in /lens to create a new lens
        // while passing the necessary data
        $attributes = [
            'name' => 'Test lens',
            'factor' => 2.0,
        ];

        $this->post('lens', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

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
        $this->actingAs($this->_user);

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
    public function aLensShouldHaveAPositiveFactor()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

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
        $this->actingAs($this->_user);

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
     * Checks whether that a lens needs at least 6 characters after update.
     *
     * @test
     *
     * @return None
     */
    public function aLensShouldHaveALongEnoughNameAfterUpdate()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $lens = factory('App\Lens')->create(['user_id' => $this->_user->id]);

        $response = $this->actingAs($this->_user)->put(
            '/lens/'.$lens->id,
            ['name' => 'test', 'factor' => 1.3]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);
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
        $this->actingAs($this->_user);

        // Get a new filter from the factory
        $lens = factory('App\Lens')->create(['user_id' => $this->_user->id]);

        // Then there should be a new lens in the database
        $attributes = [
            'user_id' => $lens->user_id,
            'name' => $lens->name,
            'factor' => $lens->factor,
            'active' => $lens->active,
        ];

        // Then there should be a new lens in the database
        $this->assertDatabaseHas('lens', $attributes);

        $lens = \App\Lens::firstOrFail();

        // Adapt the name and the factor
        $newAttributes = [
            'user_id' => $this->_user->id,
            'name' => 'My updated lens',
            'factor' => 2.5,
        ];

        $this->put('/lens/'.$lens->id, $newAttributes);

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
        $this->actingAs($this->_user);

        // When they hit the endpoint in /lens to create a new lens
        // while passing the necessary data
        $attributes = [
            'name' => 'My new lens',
            'factor' => 2.0,
        ];

        $this->post('lens', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

        // Then there should be a new lens in the database
        $this->assertDatabaseHas('lens', $attributes);

        $lens = \App\Lens::firstOrFail();

        $newUser = factory('App\User')->create();
        $this->actingAs($newUser);

        // Adapt the name and the factor
        $newAttributes = [
            'user_id' => $newUser->id,
            'name' => 'My updated lens',
            'factor' => 2.5,
        ];

        $this->expectException(AuthorizationException::class);

        $this->put('/lens/'.$lens->id, $newAttributes);
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
        $this->actingAs($this->_user);

        // When they hit the endpoint in /lens to create a new lens
        // while passing the necessary data
        $attributes = [
            'name' => 'My new lens',
            'factor' => 2.0,
        ];

        $this->post('lens', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

        // Then there should be a new lens in the database
        $this->assertDatabaseHas('lens', $attributes);

        $lens = \App\Lens::firstOrFail();

        $newUser = factory('App\User')->create();
        $newUser->type = 'admin';

        $this->actingAs($newUser);

        // Adapt the name and the factor
        $newAttributes = [
            'name' => 'My updated lens',
            'factor' => 2.5,
        ];

        $this->put('/lens/'.$lens->id, $newAttributes);

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
        $this->actingAs($this->_user);

        $lens = factory('App\Lens')->create(['user_id' => $this->_user->id]);

        // Then there should be a new filter in the database
        $this->assertDatabaseHas(
            'lens',
            [
                'name' => $lens->name,
                'factor' => $lens->factor,
                'active' => $lens->active,
                'user_id' => $lens->user_id,
            ]
        );

        $this->assertEquals(1, \App\Lens::count());

        $response = $this->delete('/lens/'.$lens->id);

        $response->assertStatus(302);

        // Then there shouldn't be a filter in the database anymore
        $this->assertDatabaseMissing(
            'lens',
            [
                'name' => $lens->name,
                'factor' => $lens->factor,
                'active' => $lens->active,
                'user_id' => $lens->user_id,
            ]
        );
        $this->assertEquals(0, \App\Lens::count());
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
        $this->actingAs($this->_user);

        // When they hit the endpoint in /lens to create a new lens
        // while passing the necessary data
        $attributes = [
            'name' => 'My new lens',
            'factor' => 2.0,
        ];

        $this->post('lens', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

        // Then there should be a new lens in the database
        $this->assertDatabaseHas('lens', $attributes);

        $lens = \App\Lens::firstOrFail();

        $newUser = factory('App\User')->create();
        $this->actingAs($newUser);

        $this->expectException(AuthorizationException::class);

        // Try to delete the lens
        $this->delete('/lens/'.$lens->id);
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
        $this->actingAs($this->_user);

        // When they hit the endpoint in /lens to create a new lens
        // while passing the necessary data
        $attributes = [
            'name' => 'My new lens',
            'factor' => 2.0,
        ];

        $this->post('lens', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

        // Then there should be a new lens in the database
        $this->assertDatabaseHas('lens', $attributes);

        $lens = \App\Lens::firstOrFail();

        $newUser = factory('App\User')->create();
        $newUser->type = 'admin';

        $this->actingAs($newUser);

        $this->delete('/lens/'.$lens->id);

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
            'factor' => 2.0,
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
        $user = factory('App\User')->create(['email_verified_at' => null]);

        $this->actingAs($user);

        // When they hit the endpoint in /lens to create a new lens while
        // passing the necessary data
        $attributes = [
            'user_id' => $user->id,
            'name' => 'Test lens for unverified user',
            'factor' => 2.5,
        ];

        $this->post('/lens', $attributes);

        $this->assertDatabaseMissing('lens', $attributes);
    }

    /**
     * Ensure that the create lens page is not accessible for guests.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsNotAccessibleForGuests()
    {
        $response = $this->get('/lens/create');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Ensure that the create lens page is not accessible for unverified users.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsNotAccessibleForUnverifiedUsers()
    {
        $user = factory('App\User')->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)->get('/lens/create');

        $response->assertStatus(302);
        $response->assertRedirect('/email/verify');
    }

    /**
     * Ensure that the create lens page is accessible for real users.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsAccessibleForUser()
    {
        $response = $this->actingAs($this->_user)->get('/lens/create');

        $response->assertStatus(200);
    }

    /**
     * Ensure that the create lens page is not accessible for adminstrators.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsAccessibleForAdmin()
    {
        $user = factory('App\User')->create(['type' => 'admin']);
        $response = $this->actingAs($user)->get('/lens/create');

        $response->assertStatus(200);
    }

    /**
     * Ensure that the update lens page contains the correct values.
     *
     * @test
     *
     * @return None
     */
    public function updateLensPageContainsCorrectValues()
    {
        $lens = factory('App\Lens')->create(['user_id' => $this->_user->id]);

        $response = $this->actingAs($this->_user)->get(
            '/lens/'.$lens->id.'/edit'
        );

        $response->assertStatus(200);
        $response->assertSee($lens->name);
        $response->assertSee($lens->factor);
    }

    /**
     * Ensure that we can upload a picture.
     *
     * @test
     *
     * @return void
     */
    public function testCreateLensFileUploaded()
    {
        // Will put the fake image in
        Storage::fake('public');

        $this->actingAs($this->_user)->post(
            'lens',
            [
                'name' => 'Test lens',
                'factor' => 3.3,
                'picture' => UploadedFile::fake()->image('lens.png'),
            ]
        );

        $lens = \App\Lens::firstOrFail();

        Storage::disk('public')->assertExists(
            $lens->id.'/'.$lens->id.'.png'
        );
    }

    /**
     * Ensure that the owner of a lens can see the change lens button.
     *
     * @test
     *
     * @return void
     */
    public function testShowFilterDetailWithChangeButton()
    {
        $lens = factory('App\Lens')->create(['user_id' => $this->_user->id]);

        $response = $this->actingAs($this->_user)->get('/lens/'.$lens->id);

        $response->assertStatus(200);
        $response->assertSee($lens->name);
        $response->assertSee($this->_user->name);
        $response->assertSee('Edit '.$lens->name);
        $response->assertSee($lens->factor);
    }

    /**
     * Ensure that a different user than the owner of a lens cannot
     * see the change lens button.
     *
     * @test
     *
     * @return void
     */
    public function testShowFilterDetailWithoutChangeButton()
    {
        $newUser = factory('App\User')->create();
        $lens = factory('App\Lens')->create(['user_id' => $newUser->id]);

        $response = $this->actingAs($this->_user)->get('/lens/'.$lens->id);

        $response->assertStatus(200);
        $response->assertSee($lens->name);
        $response->assertSee($this->_user->name);
        $response->assertDontSee('Edit '.$lens->name);
        $response->assertSee($lens->factor);
    }

    /**
     * Ensure that an admin can always see the change lens button.
     *
     * @test
     *
     * @return void
     */
    public function testAdminAlwaysSeesChangeButton()
    {
        $admin = factory('App\User')->create(['type' => 'admin']);
        $lens = factory('App\Lens')->create(['user_id' => $this->_user->id]);

        $response = $this->actingAs($admin)->get('/lens/'.$lens->id);

        $response->assertStatus(200);
        $response->assertSee($lens->name);
        $response->assertSee($this->_user->name);
        $response->assertSee($lens->factor);
        $response->assertSee('Edit '.$lens->name);
    }

    /**
     * Ensure that a guest user can not see the change lens button.
     *
     * @test
     *
     * @return void
     */
    public function testGuestNeverSeesChangeButton()
    {
        $lens = factory('App\Lens')->create(['user_id' => $this->_user->id]);

        $response = $this->get('/lens/'.$lens->id);

        $response->assertStatus(200);
        $response->assertSee($lens->name);
        $response->assertSee($lens->factor);
        $response->assertSee($this->_user->name);
        $response->assertDontSee('Edit '.$lens->name);
    }

    /**
     * Ensure that only an admin can see the admin page with all the lenses.
     *
     * @test
     *
     * @return void
     */
    public function testOnlyAdminCanSeeOverviewOfAllLenses()
    {
        factory('App\User', 50)->create();
        $lens = factory('App\Lens', 500)->create();

        // Check as guest
        $response = $this->get('/lens/admin');

        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // Check as normal user
        $response = $this->actingAs($this->_user)->get('/lens/admin');

        $response->assertStatus(401);

        // Check as admin
        $admin = factory('App\User')->create(['type' => 'admin']);
        $response = $this->actingAs($admin)->get('/lens/admin');

        $response->assertStatus(200);
        $response->assertSee('All lenses');
    }

    /**
     * Ensure that logged in users can see the Json information of a lens.
     *
     * @test
     *
     * @return void
     */
    public function testJsonInformationForLens()
    {
        $lens = factory('App\Lens')->create(['user_id' => $this->_user->id]);

        // Only for logged in users!
        $response = $this->get('/getLensJson/'.$lens->id);
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // Test for logged in user
        $response = $this->actingAs($this->_user)->get(
            '/getLensJson/'.$lens->id
        );

        $this->assertEquals($response['name'], $lens->name);
        $this->assertEquals($response['id'], $lens->id);
        $this->assertEquals($response['user_id'], $lens->user_id);
        $this->assertEquals($response['factor'], $lens->factor);
        $this->assertEquals($response['active'], $lens->active);
    }

    /**
     * Ensure that we get an image of a lens.
     *
     * @test
     */
    public function testGetLensImage()
    {
        // Will put the fake image in
        Storage::fake('public');

        $lens = factory('App\Lens')->create(['user_id' => $this->_user->id]);

        // Check the image, if no image is uploaded
        $this->actingAs($this->_user)->get('lens/'.$lens->id.'/getImage');

        Storage::disk('public')->assertExists(
            $lens->id.'/'.$lens->id.'.png'
        );

        // Check the image if we have uploaded an image
        $this->actingAs($this->_user)->post(
            'lens',
            [
                'name' => 'Test lens',
                'factor' => 3.2,
                'picture' => UploadedFile::fake()->image('lens.png'),
            ]
        );

        $lens2 = DB::table('lens')->latest('id')->first();

        Storage::disk('public')->assertExists(
            $lens2->id.'/'.$lens2->id.'.png'
        );
    }

    /**
     * Ensure that we can delete an image of a lens.
     *
     * @test
     */
    public function testDeleteFilterImage()
    {
        // Will put the fake image in
        Storage::fake('public');

        // Check if we can delete the image if we have uploaded an image
        $this->actingAs($this->_user)->post(
            'lens',
            [
                'name' => 'Test lens',
                'factor' => 3.1,
                'picture' => UploadedFile::fake()->image('lens.png'),
            ]
        );

        $lens = DB::table('lens')->latest('id')->first();

        $this->actingAs($this->_user)->post(
            'lens/'.$lens->id.'/deleteImage'
        );

        Storage::disk('public')->assertMissing(
            $lens->id.'/'.$lens->id.'.png'
        );

        // Check if another user cannot delete the image if we have uploaded an image
        $this->actingAs($this->_user)->post(
            'lens',
            [
                'name' => 'Test lens',
                'factor' => 3.1,
                'picture' => UploadedFile::fake()->image('lens.png'),
            ]
        );

        $lens = DB::table('lens')->latest('id')->first();

        $user = factory('App\User')->create();

        $this->actingAs($user)->post(
            'lens/'.$lens->id.'/deleteImage'
        );

        Storage::disk('public')->assertExists(
            $lens->id.'/'.$lens->id.'.png'
        );
    }

    /**
     * Ensure that the autocomplete works for select2.
     *
     * @test
     *
     * @return void
     */
    public function testAutocompleteForLens()
    {
        $lens = factory('App\Lens')->create(
            ['user_id' => $this->_user->id, 'name' => 'DeepskyLog test lens']
        );

        $lens2 = factory('App\Lens')->create(
            ['user_id' => $this->_user->id, 'name' => 'Other test lens']
        );

        // Only for logged in users!
        $response = $this->get('/lens/autocomplete?q=Deep');
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // Test for logged in user
        $response = $this->actingAs($this->_user)->get(
            '/lens/autocomplete?q=Deep'
        );

        $this->assertEquals($lens->id, $response[0]['id']);
        $this->assertEquals($lens->name, $response[0]['name']);

        $response = $this->actingAs($this->_user)->get(
            '/lens/autocomplete?q=test'
        );

        $this->assertEquals($lens->id, $response[0]['id']);
        $this->assertEquals($lens->name, $response[0]['name']);

        $this->assertEquals($lens2->id, $response[1]['id']);
        $this->assertEquals($lens2->name, $response[1]['name']);
    }
}
