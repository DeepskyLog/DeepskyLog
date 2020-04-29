<?php
/**
 * Tests for creating, deleting, and adapting eyepieces.
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
 * Tests for creating, deleting, and adapting eyepieces.
 *
 * @category Test
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class EyepieceTest extends TestCase
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
     * Checks whether a guest user can see the list with eyepieces.
     *
     * @test
     *
     * @return None
     */
    public function listEyepieceNotLoggedIn()
    {
        $response = $this->get('/eyepiece');
        // Code 302 is the code for redirecting
        $response->assertStatus(302);
        // Check if we are redirected to the login page
        $response->assertRedirect('/login');
    }

    /**
     * Checks whether a real user can see the list with eyepieces.
     *
     * @test
     *
     * @return None
     */
    public function listEmptyEyepiecesLoggedIn()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $response = $this->get('/eyepiece');
        // Code 200 is the code for a working page
        $response->assertStatus(200);
        // Check if we see the correct page
        $response->assertSee(
            'Eyepieces of '.$this->_user->name
        );
    }

    /**
     * Checks whether a real user can see the list with eyepieces.
     *
     * @test
     *
     * @return None
     */
    public function listEyepiecesLoggedIn()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $eyepiece = factory('App\Eyepiece')->create(['user_id' => $this->_user->id]);

        $response = $this->get('/eyepiece');
        // Code 200 is the code for a working page
        $response->assertStatus(200);

        // Check if we see the correct page
        $response->assertSee('Eyepieces of '.$this->_user->name);

        $response->assertViewIs('layout.eyepiece.view');

        $this->assertEquals($this->_user->eyepieces->first()->id, $eyepiece->id);
        $this->assertEquals($this->_user->eyepieces->first()->name, $eyepiece->name);
        $this->assertEquals(
            $this->_user->eyepieces->first()->brand,
            $eyepiece->brand
        );
        $this->assertEquals(
            $this->_user->eyepieces->first()->focalLength,
            floatval($eyepiece->focalLength)
        );
        $this->assertEquals($this->_user->eyepieces->first()->type, $eyepiece->type);
        $this->assertEquals(
            $this->_user->eyepieces->first()->apparentFOV,
            $eyepiece->apparentFOV
        );
        $this->assertEquals(
            $this->_user->eyepieces->first()->maxFocalLength,
            $eyepiece->maxFocalLength
        );
        $this->assertEquals(
            $this->_user->eyepieces->first()->active,
            $eyepiece->active
        );
        $this->assertEquals(
            $this->_user->eyepieces->first()->user_id,
            $eyepiece->user_id
        );
    }

    /**
     * Checks whether a verified user can create a new eyepiece.
     *
     * @test
     *
     * @return None
     */
    public function aUserCanCreateAnEyepiece()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 26.3,
            'type' => 'Ethos',
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $this->post('eyepiece', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

        // Then there should be a new eyepiece in the database
        $this->assertDatabaseHas('eyepieces', $attributes);

        // Check brand and type after adding a new eyepiece with unknown brand!
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'New Brand',
            'focalLength' => 26.3,
            'type' => 'Ethos2',
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $this->post('eyepiece', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

        $this->assertDatabaseHas('eyepiece_brands', ['brand' => 'New Brand']);
        $this->assertDatabaseHas(
            'eyepiece_types',
            ['brand' => 'New Brand', 'type' => 'Ethos2']
        );
    }

    /**
     * Checks whether an eyepiece has at least 6 characters.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldHaveALongEnoughName()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test1',
            'brand' => 'Televue',
            'focalLength' => 26.3,
            'type' => 'Ethos',
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('eyepiece', $attributes);
    }

    /**
     * Checks whether an eyepiece has a brand.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldHaveABrand()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => null,
            'focalLength' => 26.3,
            'type' => 'Ethos',
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('eyepiece', $attributes);
    }

    /**
     * Checks whether an eyepiece has a focalLength.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldHaveAFocalLength()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => null,
            'type' => 'Ethos',
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('eyepiece', $attributes);
    }

    /**
     * Checks whether an eyepiece has a focalLength.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldHaveAFocalLengthLargerThan1()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 0.12,
            'type' => 'Ethos',
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('eyepiece', $attributes);
    }

    /**
     * Checks whether an eyepiece has a focalLength smaller than 100.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldHaveAFocalLengthSmallerThan100()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 123.5,
            'type' => 'Ethos',
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('eyepiece', $attributes);
    }

    /**
     * Checks whether an eyepiece has a numerical focalLength.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldHaveANumericalFocalLength()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 'test',
            'type' => 'Ethos',
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('eyepiece', $attributes);
    }

    /**
     * Checks whether an eyepiece has a type.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldHaveAType()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 28.4,
            'type' => null,
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('eyepiece', $attributes);
    }

    /**
     * Checks whether an eyepiece has an apparent FOV.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldHaveAnApparentFov()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 27.2,
            'type' => 'Ethos',
            'apparentFOV' => null,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('eyepiece', $attributes);
    }

    /**
     * Checks whether an eyepiece has an apparent FOV larger than 20.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldHaveAnApparentFovLargerThan20()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 27.2,
            'type' => 'Ethos',
            'apparentFOV' => 15.3,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('eyepiece', $attributes);
    }

    /**
     * Checks whether an eyepiece has an apparent FOV smaller than 150.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldHaveAnApparentFovSmallerThan150()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 27.2,
            'type' => 'Ethos',
            'apparentFOV' => 185.2,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('eyepiece', $attributes);
    }

    /**
     * Checks whether an eyepiece has a numeric apparent FOV.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldHaveANumericApparentFov()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 27.2,
            'type' => 'Ethos',
            'apparentFOV' => null,
            'maxFocalLength' => 'test',
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('eyepiece', $attributes);
    }

    /**
     * Checks whether an eyepiece has a max focal length smaller than 100.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldHaveAMaxFocalLengthSmallerThan100()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 27.2,
            'type' => 'Ethos',
            'apparentFOV' => 80,
            'maxFocalLength' => 152.2,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('eyepiece', $attributes);
    }

    /**
     * Checks whether an eyepiece has a max focal length larger than 1.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldHaveAMaxFocalLengthLargerThan1()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 27.2,
            'type' => 'Ethos',
            'apparentFOV' => 52,
            'maxFocalLength' => 0.5,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('eyepiece', $attributes);
    }

    /**
     * Checks whether that an eyepiece needs at least 6 characters after update.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldHaveALongEnoughNameAfterUpdate()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $eyepiece = factory('App\Eyepiece')->create(['user_id' => $this->_user->id]);

        $response = $this->actingAs($this->_user)->put(
            '/eyepiece/'.$eyepiece->id,
            [
                'name' => 'test',
                'brand' => 'Televue',
                'focalLength' => 26.3,
                'type' => 'Ethos',
                'apparentFOV' => 107,
                'maxFocalLength' => null,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Checks whether that an eyepiece has all required parameters when updating.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldHaveABrandAfterUpdate()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $eyepiece = factory('App\Eyepiece')->create(['user_id' => $this->_user->id]);

        $response = $this->actingAs($this->_user)->patch(
            '/eyepiece/'.$eyepiece->id,
            [
                'name' => 'Test eyepiece',
                'brand' => null,
                'focalLength' => 26.3,
                'type' => 'Ethos',
                'apparentFOV' => 84,
                'maxFocalLength' => null,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['brand']);
    }

    /**
     * Checks whether that an eyepiece has all required parameters when updating.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldHaveAFocalLengthAfterUpdate()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $eyepiece = factory('App\Eyepiece')->create(['user_id' => $this->_user->id]);

        $response = $this->actingAs($this->_user)->patch(
            '/eyepiece/'.$eyepiece->id,
            [
                'name' => 'test eyepiece',
                'brand' => 'Televue',
                'type' => 'Ethos',
                'focalLength' => 0.26,
                'apparentFOV' => 107,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['focalLength']);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 123.5,
            'type' => 'Ethos',
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $response = $this->actingAs($this->_user)->put(
            '/eyepiece/'.$eyepiece->id,
            $attributes
        );
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['focalLength']);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 'test',
            'type' => 'Ethos',
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $response = $this->actingAs($this->_user)->put(
            '/eyepiece/'.$eyepiece->id,
            $attributes
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['focalLength']);

        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => null,
            'type' => 'Ethos',
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $response = $this->actingAs($this->_user)->put(
            '/eyepiece/'.$eyepiece->id,
            $attributes
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['focalLength']);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 28.4,
            'type' => null,
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $response = $this->actingAs($this->_user)->put(
            '/eyepiece/'.$eyepiece->id,
            $attributes
        );
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['type']);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 27.2,
            'type' => 'Ethos',
            'apparentFOV' => null,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $response = $this->actingAs($this->_user)->put(
            '/eyepiece/'.$eyepiece->id,
            $attributes
        );
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['apparentFOV']);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 27.2,
            'type' => 'Ethos',
            'apparentFOV' => 15.3,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $response = $this->actingAs($this->_user)->put(
            '/eyepiece/'.$eyepiece->id,
            $attributes
        );
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['apparentFOV']);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 27.2,
            'type' => 'Ethos',
            'apparentFOV' => 185.2,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $response = $this->actingAs($this->_user)->put(
            '/eyepiece/'.$eyepiece->id,
            $attributes
        );
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['apparentFOV']);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 27.2,
            'type' => 'Ethos',
            'apparentFOV' => null,
            'maxFocalLength' => 'test',
            'active' => 1,
        ];

        $response = $this->actingAs($this->_user)->put(
            '/eyepiece/'.$eyepiece->id,
            $attributes
        );
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['maxFocalLength']);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 27.2,
            'type' => 'Ethos',
            'apparentFOV' => null,
            'maxFocalLength' => 152.2,
            'active' => 1,
        ];

        $response = $this->actingAs($this->_user)->put(
            '/eyepiece/'.$eyepiece->id,
            $attributes
        );
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['maxFocalLength']);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 27.2,
            'type' => 'Ethos',
            'apparentFOV' => null,
            'maxFocalLength' => 0.5,
            'active' => 1,
        ];

        $response = $this->actingAs($this->_user)->put(
            '/eyepiece/'.$eyepiece->id,
            $attributes
        );
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['maxFocalLength']);
    }

    /**
     * Checks whether an eyepiece can be updated by the owner of the eyepiece.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldBeUpdateable()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // Get a new eyepiece from the factory
        $eyepiece = factory('App\Eyepiece')->create(['user_id' => $this->_user->id]);

        // Then there should be a new eyepiece in the database
        $attributes = [
            'user_id' => $eyepiece->user_id,
            'name' => $eyepiece->name,
            'brand' => $eyepiece->brand,
            'focalLength' => $eyepiece->focalLength,
            'type' => $eyepiece->type,
            'apparentFOV' => $eyepiece->apparentFOV,
            'maxFocalLength' => $eyepiece->maxFocalLength,
            'user_id' => $eyepiece->user_id,
            'active' => $eyepiece->active,
        ];

        $this->assertDatabaseHas('eyepieces', $attributes);

        // Adapt the name and the factor
        $newAttributes = [
            'user_id' => $eyepiece->user_id,
            'name' => 'Updated eyepiece',
            'brand' => 'Televue',
            'focalLength' => 31,
            'type' => 'Nagler',
            'apparentFOV' => 82,
            'maxFocalLength' => $eyepiece->maxFocalLength,
            'user_id' => $eyepiece->user_id,
            'active' => $eyepiece->active,
        ];

        $this->put('eyepiece/'.$eyepiece->id, $newAttributes);

        // Then there should be an updated eyepiece in the database
        $this->assertDatabaseHas('eyepieces', $newAttributes);
    }

    /**
     * Ensure that an eyepiece can not be updated by another user.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldNotBeUpdateableByOtherUser()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 26.3,
            'type' => 'Ethos',
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $this->post('eyepiece', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

        // Then there should be a new eyepiece in the database
        $this->assertDatabaseHas('eyepieces', $attributes);

        $eyepiece = \App\Eyepiece::firstOrFail();

        $newUser = factory('App\User')->create();
        $this->actingAs($newUser);

        // Adapt the name and the factor
        $newAttributes = [
            'user_id' => $newUser->id,
            'name' => 'Updated eyepiece',
            'brand' => 'Televue',
            'focalLength' => 31,
            'type' => 'Nagler',
            'apparentFOV' => 82,
            'maxFocalLength' => $eyepiece->maxFocalLength,
            'user_id' => $eyepiece->user_id,
            'active' => $eyepiece->active,
        ];

        $this->expectException(AuthorizationException::class);

        $this->put('/eyepiece/'.$eyepiece->id, $newAttributes);
    }

    /**
     * Ensure that an eyepiece can be updated by an admin.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldBeUpdateableByAdmin()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece
        // while passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 26.3,
            'type' => 'Ethos',
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $this->post('eyepiece', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

        // Then there should be a new eyepiece in the database
        $this->assertDatabaseHas('eyepieces', $attributes);

        $eyepiece = \App\Eyepiece::firstOrFail();

        $newUser = factory('App\User')->create(['type' => 'admin']);

        $this->actingAs($newUser);

        // Adapt the name and the factor
        $newAttributes = [
            'name' => 'Updated eyepiece',
            'brand' => 'Televue',
            'focalLength' => 31,
            'type' => 'Nagler',
            'apparentFOV' => 82,
            'maxFocalLength' => $eyepiece->maxFocalLength,
            'user_id' => $eyepiece->user_id,
            'active' => $eyepiece->active,
        ];

        $this->put('/eyepiece/'.$eyepiece->id, $newAttributes);

        // Then there should be an updated eyepiece in the database
        $this->assertDatabaseHas('eyepieces', $newAttributes);
    }

    /**
     * Checks whether that an eyepiece can be deleted by the owner.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldBeDeleteable()
    {
        // TODO: Only make it possible to delete the eyepiece if there are
        // no observations!
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $eyepiece = factory('App\Eyepiece')->create(['user_id' => $this->_user->id]);

        // Then there should be a new eyepiece in the database
        $this->assertDatabaseHas(
            'eyepieces',
            [
                'name' => $eyepiece->name,
                'type' => $eyepiece->type,
                'brand' => $eyepiece->brand,
                'focalLength' => $eyepiece->focalLength,
                'apparentFOV' => $eyepiece->apparentFOV,
                'user_id' => $eyepiece->user_id,
            ]
        );

        $this->assertEquals(1, \App\Eyepiece::count());

        $response = $this->delete('/eyepiece/'.$eyepiece->id);

        $response->assertStatus(302);

        // Then there shouldn't be an eyepiece in the database anymore
        $this->assertDatabaseMissing(
            'eyepieces',
            [
                'name' => $eyepiece->name,
                'type' => $eyepiece->type,
                'brand' => $eyepiece->brand,
                'focalLength' => $eyepiece->focalLength,
                'apparentFOV' => $eyepiece->apparentFOV,
                'user_id' => $eyepiece->user_id,
            ]
        );
        $this->assertEquals(0, \App\Eyepiece::count());
    }

    /**
     * Ensure that an eyepiece can not be deleted by another user.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldNotBeDeleteableByOtherUser()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $eyepiece = factory('App\Eyepiece')->create(['user_id' => $this->_user->id]);

        // Then there should be a new eyepiece in the database
        $this->assertDatabaseHas(
            'eyepieces',
            [
                'name' => $eyepiece->name,
                'type' => $eyepiece->type,
                'brand' => $eyepiece->brand,
                'focalLength' => $eyepiece->focalLength,
                'apparentFOV' => $eyepiece->apparentFOV,
                'user_id' => $eyepiece->user_id,
            ]
        );

        $newUser = factory('App\User')->create();
        $this->actingAs($newUser);

        $this->expectException(AuthorizationException::class);

        // Try to delete the eyepiece
        $this->delete('/eyepiece/'.$eyepiece->id);
    }

    /**
     * Ensure that an eyepiece can be deleted by an admin.
     *
     * @test
     *
     * @return None
     */
    public function anEyepieceShouldBeDeleteableByAdmin()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $eyepiece = factory('App\Eyepiece')->create(['user_id' => $this->_user->id]);

        $attributes = [
            'name' => $eyepiece->name,
            'type' => $eyepiece->type,
            'brand' => $eyepiece->brand,
            'focalLength' => $eyepiece->focalLength,
            'apparentFOV' => $eyepiece->apparentFOV,
            'user_id' => $eyepiece->user_id,
        ];

        // Then there should be a new eyepiece in the database
        $this->assertDatabaseHas(
            'eyepieces',
            $attributes
        );

        $newUser = factory('App\User')->create(['type' => 'admin']);

        $this->actingAs($newUser);

        $this->delete('/eyepiece/'.$eyepiece->id);

        // Then there should not be an eyepiece in the database anymore
        $this->assertDatabaseMissing('eyepieces', $attributes);
    }

    /**
     * Checks whether a guest is not allowed to create a new eyepiece.
     *
     * @test
     *
     * @return None
     */
    public function guestsMayNotCreateAnEyepiece()
    {
        $this->withoutExceptionHandling();

        $this->assertGuest();

        // When they hit the endpoint in /eyepiece to create a new eyepiece while
        // passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 26.3,
            'type' => 'Ethos',
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Auth\AuthenticationException::class);

        $this->post('/eyepiece', $attributes);
    }

    /**
     * Unverified users are not allowed to create a new eyepiece.
     *
     * @test
     *
     * @return None
     */
    public function unverifiedUsersMayNotCreateAnEyepiece()
    {
        // Given I am a user who is logged in and not verified
        // Act as a new user created by the factory
        $user = factory('App\User')->create(['email_verified_at' => null]);

        $this->actingAs($user);

        // When they hit the endpoint in /eyepiece to create a new eyepiece while
        // passing the necessary data
        $attributes = [
            'name' => 'Test eyepiece',
            'brand' => 'Televue',
            'focalLength' => 26.3,
            'type' => 'Ethos',
            'apparentFOV' => 107,
            'maxFocalLength' => null,
            'active' => 1,
        ];

        $this->post('/eyepiece', $attributes);

        $this->assertDatabaseMissing('eyepieces', $attributes);
    }

    /**
     * Ensure that the create eyepiece page is not accessible for guests.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsNotAccessibleForGuests()
    {
        $response = $this->get('/eyepiece/create');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Ensure that the create eyepiece page is not accessible for unverified users.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsNotAccessibleForUnverifiedUsers()
    {
        $user = factory('App\User')->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)->get('/eyepiece/create');

        $response->assertStatus(302);
        $response->assertRedirect('/email/verify');
    }

    /**
     * Ensure that the create eyepiece page is accessible for real users.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsAccessibleForUser()
    {
        $response = $this->actingAs($this->_user)->get('/eyepiece/create');

        $response->assertStatus(200);
    }

    /**
     * Ensure that the create eyepiece page is accessible for administrators.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsAccessibleForAdmin()
    {
        $user = factory('App\User')->create(['type' => 'admin']);
        $response = $this->actingAs($user)->get('/eyepiece/create');

        $response->assertStatus(200);
    }

    /**
     * Ensure that the update eyepiece page contains the correct values.
     *
     * @test
     *
     * @return None
     */
    public function updateEyepiecePageContainsCorrectValues()
    {
        $eyepiece = factory('App\Eyepiece')->create(['user_id' => $this->_user->id]);

        $response = $this->actingAs($this->_user)->get(
            '/eyepiece/'.$eyepiece->id.'/edit'
        );

        $response->assertStatus(200);
        $response->assertSee($eyepiece->name);
        $response->assertSee($eyepiece->focalLength);
        $response->assertSee($eyepiece->apparentFOV);
    }

    /**
     * Ensure that we can upload a picture.
     *
     * @test
     */
    public function testCreateEyepieceFileUploaded()
    {
        // Will put the fake image in
        Storage::fake('public');

        $this->actingAs($this->_user)->post(
            'eyepiece',
            [
                'name' => 'Test eyepiece',
                'brand' => 'Televue',
                'focalLength' => 26.3,
                'type' => 'Ethos',
                'apparentFOV' => 107,
                'maxFocalLength' => null,
                'active' => 1,
                'picture' => UploadedFile::fake()->image('eyepiece.png'),
            ]
        );

        $eyepiece = \App\Eyepiece::firstOrFail();

        Storage::disk('public')->assertExists(
            $eyepiece->id.'/'.$eyepiece->id.'.png'
        );
    }

    /**
     * Ensure that the owner of an eyepiece can see the change eyepiece button.
     *
     * @test
     */
    public function testShowEyepieceDetailWithChangeButton()
    {
        $eyepiece = factory('App\Eyepiece')->create(['user_id' => $this->_user->id]);

        $response = $this->actingAs($this->_user)->get('/eyepiece/'.$eyepiece->id);

        $response->assertStatus(200);
        $response->assertSee($eyepiece->name);
        $response->assertSee($eyepiece->brand);
        $response->assertSee($eyepiece->type);
        $response->assertSee($eyepiece->focalLength);
        $response->assertSee($eyepiece->apparentFOV);
        $response->assertSee($eyepiece->name);
        $response->assertSee($this->_user->name);
        $response->assertSee('Edit '.$eyepiece->name);
    }

    /**
     * Ensure that a different user than the owner of an eyepiece cannot
     * see the change eyepiece button.
     *
     * @test
     */
    public function testShowEyepieceDetailWithoutChangeButton()
    {
        $newUser = factory('App\User')->create();
        $eyepiece = factory('App\Eyepiece')->create(['user_id' => $newUser->id]);

        $response = $this->actingAs($this->_user)->get('/eyepiece/'.$eyepiece->id);

        $response->assertStatus(200);
        $response->assertSee($eyepiece->name);
        $response->assertSee($eyepiece->brand);
        $response->assertSee($eyepiece->type);
        $response->assertSee($eyepiece->focalLength);
        $response->assertSee($eyepiece->apparentFOV);
        $response->assertSee($eyepiece->name);
        $response->assertSee($this->_user->name);
        $response->assertDontSee('Edit '.$eyepiece->name);
    }

    /**
     * Ensure that an admin can always see the change eyepiece button.
     *
     * @test
     */
    public function testAdminAlwaysSeesChangeButton()
    {
        $admin = factory('App\User')->create(['type' => 'admin']);
        $eyepiece = factory('App\Eyepiece')->create(['user_id' => $this->_user->id]);

        $response = $this->actingAs($admin)->get('/eyepiece/'.$eyepiece->id);

        $response->assertStatus(200);
        $response->assertSee($eyepiece->name);
        $response->assertSee($eyepiece->brand);
        $response->assertSee($eyepiece->type);
        $response->assertSee($eyepiece->focalLength);
        $response->assertSee($eyepiece->apparentFOV);
        $response->assertSee($eyepiece->name);
        $response->assertSee($this->_user->name);
        $response->assertSee('Edit '.$eyepiece->name);
    }

    /**
     * Ensure that a guest user can not see the change eyepiece button.
     *
     * @test
     */
    public function testGuestNeverSeesChangeButton()
    {
        $eyepiece = factory('App\Eyepiece')->create(['user_id' => $this->_user->id]);

        $response = $this->get('/eyepiece/'.$eyepiece->id);

        $response->assertStatus(200);
        $response->assertSee($eyepiece->name);
        $response->assertSee($eyepiece->brand);
        $response->assertSee($eyepiece->type);
        $response->assertSee($eyepiece->focalLength);
        $response->assertSee($eyepiece->apparentFOV);
        $response->assertSee($eyepiece->name);
        $response->assertSee($this->_user->name);
        $response->assertDontSee('Edit '.$eyepiece->name);
    }

    /**
     * Ensure that only an admin can see the admin page with all the eyepieces.
     *
     * @test
     */
    public function testOnlyAdminCanSeeOverviewOfAllEyepieces()
    {
        factory('App\User', 50)->create();
        $eyepiece = factory('App\Eyepiece', 500)->create();

        // Check as guest
        $response = $this->get('/eyepiece/admin');

        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // Check as normal user
        $response = $this->actingAs($this->_user)->get('/eyepiece/admin');

        $response->assertStatus(401);

        // Check as admin
        $admin = factory('App\User')->create(['type' => 'admin']);
        $response = $this->actingAs($admin)->get('/eyepiece/admin');

        $response->assertStatus(200);
        $response->assertSee('All eyepieces');
    }

    /**
     * Ensure that logged in users can see the Json information of an eyepiece.
     *
     * @test
     */
    public function testJsonInformationForEyepiece()
    {
        $eyepiece = factory('App\Eyepiece')->create(['user_id' => $this->_user->id]);

        // Only for logged in users!
        $response = $this->get('/getEyepieceJson/'.$eyepiece->id);
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // Test for logged in user
        $response = $this->actingAs($this->_user)->get(
            '/getEyepieceJson/'.$eyepiece->id
        );

        $this->assertEquals($response['name'], $eyepiece->name);
        $this->assertEquals($response['id'], $eyepiece->id);
        $this->assertEquals($response['user_id'], $eyepiece->user_id);
        $this->assertEquals($response['type'], $eyepiece->type);
        $this->assertEquals($response['brand'], $eyepiece->brand);
        $this->assertEquals($response['focalLength'], $eyepiece->focalLength);
        $this->assertEquals($response['apparentFOV'], $eyepiece->apparentFOV);
        $this->assertEquals($response['active'], $eyepiece->active);
    }

    /**
     * Ensure that we get an image of an eyepiece.
     *
     * @test
     */
    public function testGetEyepieceImage()
    {
        // Will put the fake image in
        Storage::fake('public');

        $eyepiece = factory('App\Eyepiece')->create(['user_id' => $this->_user->id]);

        // Check the image, if no image is uploaded
        $this->actingAs($this->_user)->get(
            'eyepiece/'.$eyepiece->id.'/getImage'
        );

        Storage::disk('public')->assertExists(
            $eyepiece->id.'/'.$eyepiece->id.'.png'
        );

        // Check the image if we have uploaded an image
        $this->actingAs($this->_user)->post(
            'eyepiece',
            [
                'name' => 'Test eyepiece',
                'focalLength' => 13,
                'apparentFOV' => 72,
                'brand' => 'Televue',
                'type' => 'Ethos',
                'picture' => UploadedFile::fake()->image('eyepiece.png'),
            ]
        );

        $eyepiece2 = DB::table('eyepieces')->latest('id')->first();

        Storage::disk('public')->assertExists(
            $eyepiece2->id.'/'.$eyepiece2->id.'.png'
        );
    }

    /**
     * Ensure that we can delete an image of an eyepiece.
     *
     * @test
     */
    public function testDeleteEyepieceImage()
    {
        // Will put the fake image in
        Storage::fake('public');

        // Check if we can delete the image if we have uploaded an image
        $this->actingAs($this->_user)->post(
            'eyepiece',
            [
                'name' => 'Test eyepiece',
                'focalLength' => 13,
                'apparentFOV' => 72,
                'brand' => 'Televue',
                'type' => 'Ethos',
                'picture' => UploadedFile::fake()->image('eyepiece.png'),
            ]
        );

        $eyepiece = DB::table('eyepieces')->latest('id')->first();

        $this->actingAs($this->_user)->post(
            'eyepiece/'.$eyepiece->id.'/deleteImage'
        );

        Storage::disk('public')->assertMissing(
            $eyepiece->id.'/'.$eyepiece->id.'.png'
        );

        // Check if another user cannot delete the image if we have uploaded an image
        $this->actingAs($this->_user)->post(
            'eyepiece',
            [
                'name' => 'Test eyepiece',
                'focalLength' => 13,
                'apparentFOV' => 72,
                'brand' => 'Televue',
                'type' => 'Ethos',
                'picture' => UploadedFile::fake()->image('eyepiece.png'),
            ]
        );

        $eyepiece = DB::table('eyepieces')->latest('id')->first();

        $user = factory('App\User')->create();

        $this->actingAs($user)->post(
            'eyepieces/'.$eyepiece->id.'/deleteImage'
        );

        Storage::disk('public')->assertExists(
            $eyepiece->id.'/'.$eyepiece->id.'.png'
        );
    }

    /**
     * Ensure that the autocomplete works for select2.
     *
     * @test
     */
    public function testAutocompleteForEyepiece()
    {
        $eyepiece = factory('App\Eyepiece')->create(
            ['user_id' => $this->_user->id, 'name' => 'DeepskyLog test eyepiece']
        );

        $eyepiece2 = factory('App\Eyepiece')->create(
            ['user_id' => $this->_user->id, 'name' => 'Other test eyepiece']
        );

        // Only for logged in users!
        $response = $this->get('/eyepiece/autocomplete?q=Deep');
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // Test for logged in user
        $response = $this->actingAs($this->_user)->get(
            '/eyepiece/autocomplete?q=Deep'
        );

        $this->assertEquals($eyepiece->id, $response[0]['id']);
        $this->assertEquals($eyepiece->name, $response[0]['name']);

        $response = $this->actingAs($this->_user)->get(
            '/eyepiece/autocomplete?q=test'
        );

        $this->assertEquals($eyepiece->id, $response[0]['id']);
        $this->assertEquals($eyepiece->name, $response[0]['name']);

        $this->assertEquals($eyepiece2->id, $response[1]['id']);
        $this->assertEquals($eyepiece2->name, $response[1]['name']);
    }
}
