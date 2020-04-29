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
 * Tests for creating, deleting, and adapting locations.
 *
 * @category Test
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class LocationTest extends TestCase
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
     * Checks whether a guest user can see the list with locations.
     *
     * @test
     *
     * @return None
     */
    public function listLocationsNotLoggedIn()
    {
        $response = $this->get('/location');
        // Code 302 is the code for redirecting
        $response->assertStatus(302);
        // Check if we are redirected to the login page
        $response->assertRedirect('/login');
    }

    /**
     * Checks whether a real user can see the list with locations.
     *
     * @test
     *
     * @return None
     */
    public function listEmptyLocationsLoggedIn()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $response = $this->get('/location');
        // Code 200 is the code for a working page
        $response->assertStatus(200);
        // Check if we see the correct page
        $response->assertSee(
            'Locations of '.$this->_user->name
        );
    }

    /**
     * Checks whether a real user can see the list with locations.
     *
     * @test
     *
     * @return None
     */
    public function listLocationsLoggedIn()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $location = factory('App\Location')->create(
            ['user_id' => $this->_user->id]
        );

        $response = $this->get('/location');
        // Code 200 is the code for a working page
        $response->assertStatus(200);

        // Check if we see the correct page
        $response->assertSee('Locations of '.$this->_user->name);

        $response->assertViewIs('layout.location.view');

        $this->assertEquals($this->_user->locations->first()->id, $location->id);
        $this->assertEquals(
            $this->_user->locations->first()->name, $location->name
        );
        $this->assertEquals(
            $this->_user->locations->first()->longitude, $location->longitude
        );
        $this->assertEquals(
            $this->_user->locations->first()->latitude, $location->latitude
        );
        $this->assertEquals(
            $this->_user->locations->first()->elevation, $location->elevation
        );
        $this->assertEquals(
            $this->_user->locations->first()->country,
            $location->country
        );
        $this->assertEquals(
            $this->_user->locations->first()->timezone,
            $location->timezone
        );
        $this->assertEquals(
            $this->_user->locations->first()->limitingMagnitude,
            $location->limitingMagnitude
        );
        $this->assertEquals(
            $this->_user->locations->first()->skyBackground,
            $location->skyBackground
        );
        $this->assertEquals(
            $this->_user->locations->first()->bortle,
            $location->bortle
        );
        $this->assertEquals(
            $this->_user->locations->first()->active, $location->active
        );
        $this->assertEquals(
            $this->_user->locations->first()->user_id, $location->user_id
        );
    }

    /**
     * Checks whether a verified user can create a new location.
     *
     * @test
     *
     * @return None
     */
    public function aUserCanCreateAnLocation()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => 12.2215,
            'latitude' => -14.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->post('location', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;
        $attributes['limitingMagnitude'] = $attributes['lm'];
        unset($attributes['lm']);

        // Then there should be a new location in the database
        $this->assertDatabaseHas('locations', $attributes);
    }

    /**
     * Checks whether an location has at least 6 characters.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveALongEnoughName()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test',
            'longitude' => 12.2215,
            'latitude' => -14.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether an location has a longitude.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveALongitude()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test',
            'latitude' => -14.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether an location has a latitude.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveALatitude()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => 4.125,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether an location has an elevation.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAnElevation()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => 4.125,
            'latitude' => -14.2158,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether an location has a country.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveACountry()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => 4.125,
            'latitude' => -14.2158,
            'elevation' => 1254,
            'timezone' => 'Europe/Madrid',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether an location has a timezone.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveATimezone()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => 4.125,
            'latitude' => -14.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid longitude.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidLongitude()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => 184.125,
            'latitude' => -14.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid longitude.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidLongitude2()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => -184.125,
            'latitude' => -14.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid longitude.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidLongitude3()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => 'bla',
            'latitude' => -14.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid latitude.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidLatitude()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => -18.125,
            'latitude' => -95.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid latitude.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidLatitude2()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => -84.125,
            'latitude' => 94.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid latitude.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidLatitude3()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => -84.125,
            'latitude' => 'bla',
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid elevation.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidElevation()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => -84.125,
            'latitude' => 4.2158,
            'elevation' => 11234,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid elevation.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidElevation2()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => -84.125,
            'latitude' => 4.2158,
            'elevation' => -834,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid elevation.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidElevation3()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => -84.125,
            'latitude' => 4.2158,
            'elevation' => 'Bla',
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid timezone.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidTimezone()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => -84.125,
            'latitude' => 4.2158,
            'elevation' => 1234,
            'country' => 'ES',
            'timezone' => 'Europe/bla',
            'lm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid limiting magnitude.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidLimitingMagnitude()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => 14.125,
            'latitude' => -14.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => -6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid limiting magnitude.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidLimitingMagnitude2()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => 184.125,
            'latitude' => -14.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => 9.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid limiting magnitude.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidLimitingMagnitude3()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => 184.125,
            'latitude' => -14.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'lm' => 'TEST',
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid sqm.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidSqm()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => 14.125,
            'latitude' => -14.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'sqm' => 6.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid sqm.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidSqm2()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => 184.125,
            'latitude' => -14.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'sqm' => 29.4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid sqm.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidSqm3()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => 184.125,
            'latitude' => -14.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'sqm' => 'TEST',
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid bortle.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidBortle()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => 14.125,
            'latitude' => -14.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'bortle' => -4,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid bortle.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidBortle2()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => 184.125,
            'latitude' => -14.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'bortle' => 10,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether a location has a valid bortle.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldHaveAValidBortle3()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'Test location',
            'longitude' => 184.125,
            'latitude' => -14.2158,
            'elevation' => 1254,
            'country' => 'ES',
            'timezone' => 'Europe/Madrid',
            'bortle' => 'TEST',
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('location', $attributes);
    }

    /**
     * Checks whether that an location has all parameters correct.
     *
     * @test
     *
     * @return None
     */
    public function anLocationShouldHaveAllParametersCorrectAfterUpdate()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $location = factory('App\Location')->create(
            ['user_id' => $this->_user->id]
        );

        // Name not long enough
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'lm' => 6.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);

        // Longitude too low
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -184.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'lm' => 6.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['longitude']);

        // Longitude too high
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => 184.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'lm' => 6.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['longitude']);

        // Longitude not available
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => null,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'lm' => 6.4,
                'active' => 1,
            ]
        );

        // Latitude too low
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -14.125,
                'latitude' => -94.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'lm' => 6.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['latitude']);

        // Latitude too high
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => 14.125,
                'latitude' => 94.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'lm' => 6.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['latitude']);

        // Latitude not available
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => 1.215,
                'latitude' => null,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'lm' => 6.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['latitude']);

        // Elevation too high
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 11234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'lm' => 6.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['elevation']);

        // Elevation to low
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => -1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'lm' => 6.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['elevation']);

        // Elevation not numeric
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 'test',
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'lm' => 6.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['elevation']);

        // Elevation not given
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => null,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'lm' => 6.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['elevation']);

        // Country not given
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => null,
                'timezone' => 'Europe/Madrid',
                'lm' => 6.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['country']);

        // Timezone not given
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => null,
                'lm' => 6.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['timezone']);

        // Timezone not valid
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/blah',
                'lm' => 6.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['timezone']);

        // Limiting magnitude not given
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'lm' => null,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['lm']);

        // Limiting magnitude is too low
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'lm' => -6.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['lm']);

        // Limiting magnitude is too high
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'lm' => 16.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['lm']);

        // Limiting magnitude is a string
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'lm' => 'test',
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['lm']);

        // sqm is a string
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'sqm' => 'test',
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['sqm']);

        // sqm is too high
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'sqm' => 26.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['sqm']);

        // sqm is too low
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'sqm' => 6.4,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['sqm']);

        // sqm is not given
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'sqm' => null,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['sqm']);

        // Bortle is a string
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'bortle' => 'test',
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['bortle']);

        // bortle is too high
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'bortle' => 11,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['bortle']);

        // bortle is too low
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'bortle' => 0,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['bortle']);

        // Bortle is null
        $response = $this->actingAs($this->_user)->put(
            '/location/'.$location->id,
            [
                'name' => 'Test location',
                'longitude' => -84.125,
                'latitude' => 4.2158,
                'elevation' => 1234,
                'country' => 'ES',
                'timezone' => 'Europe/Madrid',
                'bortle' => null,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['bortle']);
    }

    /**
     * Checks whether a location can be updated by the owner of the location.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldBeUpdateable()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // Get a new location from the factory
        $location = factory('App\Location')->create(['user_id' => $this->_user->id]);

        // Then there should be a new location in the database
        $attributes = [
            'name' => $location->name,
            'longitude' => $location->longitude,
            'latitude' => $location->latitude,
            'elevation' => $location->elevation,
            'country' => $location->country,
            'timezone' => $location->timezone,
            'limitingMagnitude' => $location->limitingMagnitude,
            'skyBackground' => $location->skyBackground,
            'bortle' => $location->bortle,
            'active' => $location->active,
        ];

        $this->assertDatabaseHas('locations', $attributes);

        // Adapt the settings
        $newAttributes = [
            'user_id' => $location->user_id,
            'name' => 'My test location',
            'longitude' => 12.21,
            'latitude' => 50.12,
            'elevation' => 75,
            'country' => 'BE',
            'timezone' => 'Europe/Brussels',
            'bortle' => 5,
            'active' => $location->active,
        ];

        $this->put('location/'.$location->id, $newAttributes);

        // Then there should be an updated location in the database
        $this->assertDatabaseHas('locations', $newAttributes);
    }

    /**
     * Ensure that a location can not be updated by another user.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldNotBeUpdateableByOtherUser()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'My test location',
            'longitude' => 12.21,
            'latitude' => 50.12,
            'elevation' => 75,
            'country' => 'BE',
            'timezone' => 'Europe/Brussels',
            'bortle' => 5,
            'active' => 1,
        ];

        $this->post('location', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

        // Then there should be a new location in the database
        $this->assertDatabaseHas('locations', $attributes);

        $location = \App\Location::firstOrFail();

        $newUser = factory('App\User')->create();
        $this->actingAs($newUser);

        // Adapt the name and the diameter
        $newAttributes = [
            'name' => 'My new test location',
            'longitude' => 12.21,
            'latitude' => 50.12,
            'elevation' => 75,
            'country' => 'BE',
            'timezone' => 'Europe/Brussels',
            'bortle' => 3,
            'active' => 1,
        ];

        $this->expectException(AuthorizationException::class);

        $this->put('/location/'.$location->id, $newAttributes);
    }

    /**
     * Ensure that a location can be updated by an admin.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldBeUpdateableByAdmin()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /location to create a new location
        // while passing the necessary data
        $attributes = [
            'name' => 'My test location',
            'longitude' => 12.21,
            'latitude' => 50.12,
            'elevation' => 75,
            'country' => 'BE',
            'timezone' => 'Europe/Brussels',
            'bortle' => 5,
            'active' => 1,
        ];

        $this->post('location', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

        // Then there should be a new location in the database
        $this->assertDatabaseHas('locations', $attributes);

        $location = \App\Location::firstOrFail();

        $newUser = factory('App\User')->create(['type' => 'admin']);

        $this->actingAs($newUser);

        // Adapt the name and the diameter
        $newAttributes = [
            'name' => 'My new test location',
            'longitude' => 12.21,
            'latitude' => 50.12,
            'elevation' => 75,
            'country' => 'BE',
            'timezone' => 'Europe/Brussels',
            'bortle' => 3,
            'active' => 1,
        ];

        $this->put('/location/'.$location->id, $newAttributes);

        // Then there should be an updated location in the database
        $this->assertDatabaseHas('locations', $newAttributes);
    }

    /**
     * Checks whether that a location can be deleted by the owner.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldBeDeleteable()
    {
        // TODO: Only make it possible to delete the location if there are
        // no observations!
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $location = factory('App\Location')->create(['user_id' => $this->_user->id]);

        // Then there should be a new location in the database
        $this->assertDatabaseHas(
            'locations',
            [
                'name' => $location->name,
                'longitude' => $location->longitude,
                'latitude' => $location->latitude,
                'elevation' => $location->elevation,
                'country' => $location->country,
                'timezone' => $location->timezone,
                'limitingMagnitude' => $location->limitingMagnitude,
                'skyBackground' => $location->skyBackground,
                'bortle' => $location->bortle,
                'active' => $location->active,
            ]
        );

        $this->assertEquals(1, \App\Location::count());

        $response = $this->delete('/location/'.$location->id);

        $response->assertStatus(302);

        // Then there shouldn't be an location in the database anymore
        $this->assertDatabaseMissing(
            'locations',
            [
                'name' => $location->name,
                'longitude' => $location->longitude,
                'latitude' => $location->latitude,
                'elevation' => $location->elevation,
                'country' => $location->country,
                'timezone' => $location->timezone,
                'limitingMagnitude' => $location->limitingMagnitude,
                'skyBackground' => $location->skyBackground,
                'bortle' => $location->bortle,
                'active' => $location->active,
            ]
        );
        $this->assertEquals(0, \App\Location::count());
    }

    /**
     * Ensure that a location can not be deleted by another user.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldNotBeDeleteableByOtherUser()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $location = factory('App\Location')->create(['user_id' => $this->_user->id]);

        // Then there should be a new location in the database
        $this->assertDatabaseHas(
            'locations',
            [
                'name' => $location->name,
                'longitude' => $location->longitude,
                'latitude' => $location->latitude,
                'elevation' => $location->elevation,
                'country' => $location->country,
                'timezone' => $location->timezone,
                'limitingMagnitude' => $location->limitingMagnitude,
                'skyBackground' => $location->skyBackground,
                'bortle' => $location->bortle,
                'active' => $location->active,
            ]
        );

        $newUser = factory('App\User')->create();
        $this->actingAs($newUser);

        $this->expectException(AuthorizationException::class);

        // Try to delete the location
        $this->delete('/location/'.$location->id);
    }

    /**
     * Ensure that a location can be deleted by an admin.
     *
     * @test
     *
     * @return None
     */
    public function aLocationShouldBeDeleteableByAdmin()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $location = factory('App\Location')->create(['user_id' => $this->_user->id]);

        $attributes = [
            'name' => $location->name,
            'longitude' => $location->longitude,
            'latitude' => $location->latitude,
            'elevation' => $location->elevation,
            'country' => $location->country,
            'timezone' => $location->timezone,
            'limitingMagnitude' => $location->limitingMagnitude,
            'skyBackground' => $location->skyBackground,
            'bortle' => $location->bortle,
            'active' => $location->active,
        ];

        // Then there should be a new location in the database
        $this->assertDatabaseHas(
            'locations', $attributes
        );

        $newUser = factory('App\User')->create(['type' => 'admin']);

        $this->actingAs($newUser);

        $this->delete('/location/'.$location->id);

        // Then there should not be an location in the database anymore
        $this->assertDatabaseMissing('locations', $attributes);
    }

    /**
     * Checks whether a guest is not allowed to create a new location.
     *
     * @test
     *
     * @return None
     */
    public function guestsMayNotCreateALocation()
    {
        $this->withoutExceptionHandling();

        $this->assertGuest();

        // When they hit the endpoint in /location to create a new location while
        // passing the necessary data
        $attributes = [
            'name' => 'My new test location',
            'longitude' => 12.21,
            'latitude' => 50.12,
            'elevation' => 75,
            'country' => 'BE',
            'timezone' => 'Europe/Brussels',
            'bortle' => 3,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Auth\AuthenticationException::class);

        $this->post('/location', $attributes);
    }

    /**
     * Unverified users are not allowed to create a new location.
     *
     * @test
     *
     * @return None
     */
    public function unverifiedUsersMayNotCreateAnLocation()
    {
        // Given I am a user who is logged in and not verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create(['email_verified_at' => null]);

        $this->actingAs($user);

        // When they hit the endpoint in /location to create a new location while
        // passing the necessary data
        $attributes = [
            'name' => 'My new test location',
            'longitude' => 12.21,
            'latitude' => 50.12,
            'elevation' => 75,
            'country' => 'BE',
            'timezone' => 'Europe/Brussels',
            'bortle' => 3,
            'active' => 1,
        ];

        $this->post('/location', $attributes);

        $this->assertDatabaseMissing('locations', $attributes);
    }

    /**
     * Ensure that the create location page is not accessible for guests.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsNotAccessibleForGuests()
    {
        $response = $this->get('/location/create');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Ensure that the create location page is not accessible for unverified users.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsNotAccessibleForUnverifiedUsers()
    {
        $user = factory('App\User')->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)->get('/location/create');

        $response->assertStatus(302);
        $response->assertRedirect('/email/verify');
    }

    /**
     * Ensure that the create location page is accessible for real users.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsAccessibleForUser()
    {
        $response = $this->actingAs($this->_user)->get('/location/create');

        $response->assertStatus(200);
    }

    /**
     * Ensure that the create location page is accessible for administrators.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsAccessibleForAdmin()
    {
        $user = factory('App\User')->create(['type' => 'admin']);
        $response = $this->actingAs($user)->get('/location/create');

        $response->assertStatus(200);
    }

    /**
     * Ensure that the update location page contains the correct values.
     *
     * @test
     *
     * @return None
     */
    public function updateLocationPageContainsCorrectValues()
    {
        $location = factory('App\Location')->create(
            ['user_id' => $this->_user->id]
        );

        $response = $this->actingAs($this->_user)->get(
            '/location/'.$location->id.'/edit'
        );

        $response->assertStatus(200);
        $response->assertSee($location->name);
    }

    /**
     * Ensure that we can upload a picture.
     *
     * @test
     */
    public function testCreateLocationFileUploaded()
    {
        // Will put the fake image in
        Storage::fake('public');

        $this->actingAs($this->_user)->post(
            'location',
            [
                'name' => 'My new test location',
                'longitude' => 12.21,
                'latitude' => 50.12,
                'elevation' => 75,
                'country' => 'BE',
                'timezone' => 'Europe/Brussels',
                'bortle' => 3,
                'active' => 1,
                'picture' => UploadedFile::fake()->image('location.png'),
            ]
        );

        $location = \App\Location::firstOrFail();

        Storage::disk('public')->assertExists(
            $location->id.'/'.$location->id.'.png'
        );
    }

    /**
     * Ensure that the owner of a location can see the change location button.
     *
     * @test
     *
     * @return void
     */
    public function testShowLocationDetailWithChangeButton()
    {
        $location = factory('App\Location')->create(
            ['user_id' => $this->_user->id]
        );

        $response = $this->actingAs($this->_user)->get(
            '/location/'.$location->id
        );

        $response->assertStatus(200);
        $response->assertSee($location->name);
        $response->assertSee($location->elevation);
        $response->assertSee($this->_user->name);
        $response->assertSee('Edit '.$location->name);
    }

    /**
     * Ensure that a different user than the owner of a location cannot
     * see the change location button.
     *
     * @test
     *
     * @return void
     */
    public function testShowLocationDetailWithoutChangeButton()
    {
        $newUser = factory('App\User')->create();
        $location = factory('App\Location')->create(['user_id' => $newUser->id]);

        $response = $this->actingAs($this->_user)->get(
            '/location/'.$location->id
        );

        $response->assertStatus(200);
        $response->assertSee($location->name);
        $response->assertSee($location->elevation);
        $response->assertSee($this->_user->name);
        $response->assertDontSee('Edit '.$location->name);
    }

    /**
     * Ensure that an admin can always see the change location button.
     *
     * @test
     *
     * @return void
     */
    public function testAdminAlwaysSeesChangeButton()
    {
        $admin = factory('App\User')->create(['type' => 'admin']);
        $location = factory('App\Location')->create(
            ['user_id' => $this->_user->id]
        );

        $response = $this->actingAs($admin)->get('/location/'.$location->id);

        $response->assertStatus(200);
        $response->assertSee($location->name);
        $response->assertSee($location->elevation);
        $response->assertSee($this->_user->name);
        $response->assertSee('Edit '.$location->name);
    }

    /**
     * Ensure that a guest user can not see the change location button.
     *
     * @test
     *
     * @return void
     */
    public function testGuestNeverSeesChangeButton()
    {
        $location = factory('App\Location')->create(
            ['user_id' => $this->_user->id]
        );

        $response = $this->get('/location/'.$location->id);

        $response->assertStatus(200);
        $response->assertSee($location->name);
        $response->assertSee($location->elevation);
        $response->assertSee($this->_user->name);
        $response->assertDontSee('Edit '.$location->name);
    }

    /**
     * Ensure that only an admin can see the admin page with all the locations.
     *
     * @test
     *
     * @return void
     */
    public function testOnlyAdminCanSeeOverviewOfAllLocations()
    {
        factory('App\User', 50)->create();
        $location = factory('App\Location', 500)->create();

        // Check as guest
        $response = $this->get('/location/admin');

        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // Check as normal user
        $response = $this->actingAs($this->_user)->get('/location/admin');

        $response->assertStatus(401);

        // Check as admin
        $admin = factory('App\User')->create(['type' => 'admin']);
        $response = $this->actingAs($admin)->get('/location/admin');

        $response->assertStatus(200);
        $response->assertSee('All locations');
    }

    /**
     * Ensure that logged in users can see the Json information of an location.
     *
     * @test
     *
     * @return void
     */
    public function testJsonInformationForLocation()
    {
        $location = factory('App\Location')->create(
            ['user_id' => $this->_user->id]
        );

        // Only for logged in users!
        $response = $this->get('/getLocationJson/'.$location->id);
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // Test for logged in user
        $response = $this->actingAs($this->_user)->get(
            '/getLocationJson/'.$location->id
        );

        $this->assertEquals($response['name'], $location->name);
        $this->assertEquals($response['id'], $location->id);
        $this->assertEquals($response['user_id'], $location->user_id);
        $this->assertEquals($response['longitude'], $location->longitude);
        $this->assertEquals($response['latitude'], $location->latitude);
        $this->assertEquals($response['elevation'], $location->elevation);
        $this->assertEquals(
            $response['country'], $location->country
        );
        $this->assertEquals($response['timezone'], $location->timezone);
        $this->assertEquals(
            $response['limitingMagnitude'], $location->limitingMagnitude
        );
        $this->assertEquals($response['skyBackground'], $location->skyBackground);
        $this->assertEquals($response['bortle'], $location->bortle);
        $this->assertEquals($response['active'], $location->active);
    }

    /**
     * Ensure that we get an image of an location.
     *
     * @test
     *
     * @return void
     */
    public function testGetLocationImage()
    {
        // Will put the fake image in
        Storage::fake('public');

        $location = factory('App\Location')->create(
            ['user_id' => $this->_user->id]
        );

        // Check the image, if no image is uploaded
        $this->actingAs($this->_user)->get(
            'location/'.$location->id.'/getImage'
        );

        Storage::disk('public')->assertExists(
            $location->id.'/'.$location->id.'.png'
        );

        // Check the image if we have uploaded an image
        $this->actingAs($this->_user)->post(
            'location',
            [
                'name' => 'My new test location',
                'longitude' => 12.21,
                'latitude' => 50.12,
                'elevation' => 75,
                'country' => 'BE',
                'timezone' => 'Europe/Brussels',
                'bortle' => 3,
                'active' => 1,
                'picture' => UploadedFile::fake()->image('location.png'),
            ]
        );

        $location2 = DB::table('locations')->latest('id')->first();

        Storage::disk('public')->assertExists(
            $location2->id.'/'.$location2->id.'.png'
        );
    }

    /**
     * Ensure that we can delete an image of an location.
     *
     * @test
     *
     * @return void
     */
    public function testDeleteLocationImage()
    {
        // Will put the fake image in
        Storage::fake('public');

        // Check if we can delete the image if we have uploaded an image
        $this->actingAs($this->_user)->post(
            'location',
            [
                'name' => 'My new test location',
                'longitude' => 12.21,
                'latitude' => 50.12,
                'elevation' => 75,
                'country' => 'BE',
                'timezone' => 'Europe/Brussels',
                'bortle' => 3,
                'active' => 1,
                'picture' => UploadedFile::fake()->image('location.png'),
            ]
        );

        $location = DB::table('locations')->latest('id')->first();

        $this->actingAs($this->_user)->post(
            'location/'.$location->id.'/deleteImage'
        );

        Storage::disk('public')->assertMissing(
            $location->id.'/'.$location->id.'.png'
        );

        // Check if another user cannot delete the image if we have uploaded an image
        $this->actingAs($this->_user)->post(
            'location',
            [
                'name' => 'My new test location',
                'longitude' => 12.21,
                'latitude' => 50.12,
                'elevation' => 75,
                'country' => 'BE',
                'timezone' => 'Europe/Brussels',
                'bortle' => 3,
                'active' => 1,
                'picture' => UploadedFile::fake()->image('location.png'),
            ]
        );

        $location = DB::table('locations')->latest('id')->first();

        $user = factory('App\User')->create();

        $this->actingAs($user)->post(
            'locations/'.$location->id.'/deleteImage'
        );

        Storage::disk('public')->assertExists(
            $location->id.'/'.$location->id.'.png'
        );
    }

    /**
     * Ensure that the autocomplete works for select2.
     *
     * @test
     *
     * @return void
     */
    public function testAutocompleteForLocation()
    {
        $location = factory('App\Location')->create(
            ['user_id' => $this->_user->id, 'name' => 'DeepskyLog test location']
        );

        $location2 = factory('App\Location')->create(
            ['user_id' => $this->_user->id, 'name' => 'Other test location']
        );

        // Only for logged in users!
        $response = $this->get('/location/autocomplete?q=Deep');
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // Test for logged in user
        $response = $this->actingAs($this->_user)->get(
            '/location/autocomplete?q=Deep'
        );

        $this->assertEquals($location->id, $response[0]['id']);
        $this->assertEquals($location->name, $response[0]['name']);

        $response = $this->actingAs($this->_user)->get(
            '/location/autocomplete?q=test'
        );

        $this->assertEquals($location->id, $response[0]['id']);
        $this->assertEquals($location->name, $response[0]['name']);

        $this->assertEquals($location2->id, $response[1]['id']);
        $this->assertEquals($location2->name, $response[1]['name']);
    }

    /**
     * Tests the detail page.
     * The owner should only see the used instruments, eyepieces, filters, and lenses
     * for the location.
     *
     * @test
     *
     * @return void
     */
    public function testShowLocationDetailAsOwner()
    {
        $location = factory('App\Location')->create();

        // As guest
        $this->assertGuest();
        $response = $this->get(
            '/location/'.$location->id
        );
        $response->assertStatus(200);
        $response->assertDontSee('Used eyepieces');
        $response->assertDontSee('Used filters');
        $response->assertDontSee('Used lenses');
        $response->assertDontSee('Used instruments');
        $response->assertDontSee('First observation');
        $response->assertDontSee('Last observation');

        $response = $this->actingAs($this->_user)->get(
            '/location/'.$location->id
        );

        $response->assertStatus(200);
        $response->assertSee('Used eyepieces');
        $response->assertSee('Used filters');
        $response->assertSee('Used lenses');
        $response->assertSee('Used instruments');
        $response->assertSee('First observation');
        $response->assertSee('Last observation');

        // As other user
        $otherUser = factory('App\User')->create();
        $response = $this->actingAs($otherUser)->get(
            '/location/'.$location->id
        );
        $response->assertStatus(200);
        $response->assertDontSee('Used eyepieces');
        $response->assertDontSee('Used filters');
        $response->assertDontSee('Used lenses');
        $response->assertDontSee('Used instruments');
        $response->assertDontSee('First observation');
        $response->assertDontSee('Last observation');

        // As admin
        $admin = factory('App\User')->create(['type' => 'admin']);
        $response = $this->actingAs($admin)->get(
            '/location/'.$location->id
        );
        $response->assertStatus(200);
        $response->assertDontSee('Used eyepieces');
        $response->assertDontSee('Used filters');
        $response->assertDontSee('Used lenses');
        $response->assertDontSee('Used instruments');
        $response->assertDontSee('First observation');
        $response->assertDontSee('Last observation');
    }
}
