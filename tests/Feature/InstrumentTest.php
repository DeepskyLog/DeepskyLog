<?php
/**
 * Tests for creating, deleting, and adapting instruments.
 *
 * PHP Version 7
 *
 * @category Test
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Instrument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Tests for creating, deleting, and adapting instruments.
 *
 * @category Test
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class InstrumentTest extends TestCase
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
     * Checks whether a guest user can see the list with instruments.
     *
     * @test
     *
     * @return None
     */
    public function listInstrumentsNotLoggedIn()
    {
        $response = $this->get('/instrument');
        // Code 302 is the code for redirecting
        $response->assertStatus(302);
        // Check if we are redirected to the login page
        $response->assertRedirect('/login');
    }

    /**
     * Checks whether a real user can see the list with instruments.
     *
     * @test
     *
     * @return None
     */
    public function listEmptyInstrumentsLoggedIn()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $response = $this->get('/instrument');
        // Code 200 is the code for a working page
        $response->assertStatus(200);
        // Check if we see the correct page
        $response->assertSee(
            'Instruments of ' . $this->_user->name
        );
    }

    /**
     * Checks whether a real user can see the list with instruments.
     *
     * @test
     *
     * @return None
     */
    public function listInstrumentsLoggedIn()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $instrument =Instrument::factory()->create(
            ['user_id' => $this->_user->id]
        );

        $response = $this->get('/instrument');
        // Code 200 is the code for a working page
        $response->assertStatus(200);

        // Check if we see the correct page
        $response->assertSee('Instruments of ' . $this->_user->name);

        $response->assertViewIs('layout.instrument.view');

        $this->assertEquals($this->_user->instruments->first()->id, $instrument->id);
        $this->assertEquals(
            $this->_user->instruments->first()->name,
            $instrument->name
        );
        $this->assertEquals(
            $this->_user->instruments->first()->brand,
            $instrument->brand
        );
        $this->assertEquals(
            $this->_user->instruments->first()->focalLength,
            floatval($instrument->focalLength)
        );
        $this->assertEquals(
            $this->_user->instruments->first()->type,
            $instrument->type
        );
        $this->assertEquals(
            $this->_user->instruments->first()->apparentFOV,
            $instrument->apparentFOV
        );
        $this->assertEquals(
            $this->_user->instruments->first()->maxFocalLength,
            $instrument->maxFocalLength
        );
        $this->assertEquals(
            $this->_user->instruments->first()->active,
            $instrument->active
        );
        $this->assertEquals(
            $this->_user->instruments->first()->user_id,
            $instrument->user_id
        );
    }

    /**
     * Checks whether a verified user can create a new instrument.
     *
     * @test
     *
     * @return None
     */
    public function aUserCanCreateAnInstrument()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => 457,
            'type' => 3,
            'fd' => 4.5,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $this->post('instrument', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

        // Then there should be a new instrument in the database
        $this->assertDatabaseHas('instruments', $attributes);
    }

    /**
     * Checks whether an instrument has at least 6 characters.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldHaveALongEnoughName()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test1',
            'diameter' => 457,
            'type' => 3,
            'fd' => 4.5,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('instrument', $attributes);
    }

    /**
     * Checks whether an instrument has a diamter.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldHaveADiameter()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => null,
            'type' => 3,
            'fd' => 4.5,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('instrument', $attributes);
    }

    /**
     * Checks whether an instrument has an fd or a fixedMagnification.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldHaveAnFdOrFixedMagnification()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => 457,
            'type' => 3,
            'fd' => null,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('instrument', $attributes);
    }

    /**
     * Checks whether an instrument has a diamter.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldHaveADiameterLargerThan0()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => -3,
            'type' => 3,
            'fd' => 4.5,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('instrument', $attributes);
    }

    /**
     * Checks whether an instrument has a numerical diameter.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldHaveANumericalDiameter()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => 'test',
            'type' => 3,
            'fd' => 4.5,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('instrument', $attributes);
    }

    /**
     * Checks whether an instrument has an fd larger than 1.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldHaveAnFdLargerThan1()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => 457,
            'type' => 3,
            'fd' => 0.5,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('instrument', $attributes);
    }

    /**
     * Checks whether an instrument has a numerical fd.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldHaveANumericalFd()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => 457,
            'type' => 3,
            'fd' => 'test',
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('instrument', $attributes);
    }

    /**
     * Checks whether an instrument has a fixedMagnification larger than 0.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldHaveAnFixedMagnificationLargerThan1()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => 457,
            'type' => 3,
            'fd' => null,
            'fixedMagnification' => -0.3,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('instrument', $attributes);
    }

    /**
     * Checks whether an instrument has a numerical fixedMagnification.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldHaveANumericalFixedMagnification()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => 457,
            'type' => 3,
            'fd' => null,
            'fixedMagnification' => 'test',
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('instrument', $attributes);
    }

    /**
     * Checks whether an instrument has a type.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldHaveAType()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => 457,
            'type' => null,
            'fd' => 4.5,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->post('instrument', $attributes);
    }

    /**
     * Checks whether that an instrument needs at least 6 characters after update.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldHaveALongEnoughNameAfterUpdate()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $instrument =Instrument::factory()->create(
            ['user_id' => $this->_user->id]
        );

        $response = $this->actingAs($this->_user)->put(
            '/instrument/' . $instrument->id,
            [
                'name' => 'Test',
                'diameter' => 457,
                'type' => 4,
                'fd' => 4.5,
                'fixedMagnification' => null,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Checks whether that an instrument has all required parameters when updating.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldHaveADiameterAfterUpdate()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $instrument =Instrument::factory()->create(
            ['user_id' => $this->_user->id]
        );

        $response = $this->actingAs($this->_user)->patch(
            '/instrument/' . $instrument->id,
            [
                'name' => 'Test instrument',
                'diameter' => null,
                'type' => 4,
                'fd' => 4.5,
                'fixedMagnification' => null,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['diameter']);
    }

    /**
     * Checks whether that an instrument has all required parameters when updating.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldHaveAllRequiredParametersAfterUpdate()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $instrument =Instrument::factory()->create(
            ['user_id' => $this->_user->id]
        );

        $response = $this->actingAs($this->_user)->patch(
            '/instrument/' . $instrument->id,
            [
                'name' => 'Test instrument',
                'diameter' => null,
                'type' => 4,
                'fd' => 4.5,
                'fixedMagnification' => null,
                'active' => 1,
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['diameter']);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => -3,
            'type' => 4,
            'fd' => 4.5,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $response = $this->actingAs($this->_user)->put(
            '/instrument/' . $instrument->id,
            $attributes
        );
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['diameter']);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => 457,
            'type' => null,
            'fd' => 4.5,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $response = $this->actingAs($this->_user)->put(
            '/instrument/' . $instrument->id,
            $attributes
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['type']);

        $attributes = [
            'name' => 'Test instrument',
            'diameter' => 457,
            'type' => 4,
            'fd' => -1.2,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $response = $this->actingAs($this->_user)->put(
            '/instrument/' . $instrument->id,
            $attributes
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['fd']);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => 457,
            'type' => 4,
            'fd' => null,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $response = $this->actingAs($this->_user)->put(
            '/instrument/' . $instrument->id,
            $attributes
        );
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['fd']);
        $response->assertSessionHasErrors(['fixedMagnification']);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => 457,
            'type' => 4,
            'fd' => null,
            'fixedMagnification' => -4.3,
            'active' => 1,
        ];

        $response = $this->actingAs($this->_user)->put(
            '/instrument/' . $instrument->id,
            $attributes
        );
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['fixedMagnification']);
    }

    /**
     * Checks whether an instrument can be updated by the owner of the instrument.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldBeUpdateable()
    {
        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // Get a new instrument from the factory
        $instrument =Instrument::factory()->create(['user_id' => $this->_user->id]);

        // Then there should be a new instrument in the database
        $attributes = [
            'name' => $instrument->name,
            'diameter' => $instrument->diameter,
            'type' => $instrument->type,
            'fd' => $instrument->fd,
            'fixedMagnification' => $instrument->fixedMagnification,
            'active' => $instrument->active,
        ];

        $this->assertDatabaseHas('instruments', $attributes);

        // Adapt the name and the factor
        $newAttributes = [
            'user_id' => $instrument->user_id,
            'name' => 'My test instrument',
            'diameter' => 457.2,
            'type' => 3,
            'fd' => 4.5,
            'fixedMagnification' => null,
            'active' => $instrument->active,
        ];

        $this->put('instrument/' . $instrument->id, $newAttributes);

        // Then there should be an updated instrument in the database
        $this->assertDatabaseHas('instruments', $newAttributes);
    }

    /**
     * Ensure that an instrument can not be updated by another user.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldNotBeUpdateableByOtherUser()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => 457,
            'type' => 4,
            'fd' => 4.5,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $this->post('instrument', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

        // Then there should be a new instrument in the database
        $this->assertDatabaseHas('instruments', $attributes);

        $instrument = \App\Models\Instrument::firstOrFail();

        $newUser = User::factory()->create();
        $this->actingAs($newUser);

        // Adapt the name and the diameter
        $newAttributes = [
            'name' => 'New test instrument',
            'diameter' => 342,
            'type' => 4,
            'fd' => 4.5,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $this->expectException(AuthorizationException::class);

        $this->put('/instrument/' . $instrument->id, $newAttributes);
    }

    /**
     * Ensure that an instrument can be updated by an admin.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldBeUpdateableByAdmin()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => 457,
            'type' => 4,
            'fd' => 4.5,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $this->post('instrument', $attributes);

        // Also check if the user_id is correct
        $attributes['user_id'] = $this->_user->id;

        // Then there should be a new instrument in the database
        $this->assertDatabaseHas('instruments', $attributes);

        $instrument = \App\Models\Instrument::firstOrFail();

        $newUser = User::factory()->create(['type' => 'admin']);

        $this->actingAs($newUser);

        // Adapt the name and the diameter
        $newAttributes = [
            'name' => 'New test instrument',
            'diameter' => 352,
            'type' => 4,
            'fd' => 4.5,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $this->put('/instrument/' . $instrument->id, $newAttributes);

        // Then there should be an updated instrument in the database
        $this->assertDatabaseHas('instruments', $newAttributes);
    }

    /**
     * Checks whether that an instrument can be deleted by the owner.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldBeDeleteable()
    {
        // TODO: Only make it possible to delete the instrument if there are
        // no observations!
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $instrument =Instrument::factory()->create(['user_id' => $this->_user->id]);

        // Then there should be a new instrument in the database
        $this->assertDatabaseHas(
            'instruments',
            [
                'name' => $instrument->name,
                'diameter' => $instrument->diameter,
                'type' => $instrument->type,
                'fd' => $instrument->fd,
                'fixedMagnification' => $instrument->fixedMagnification,
                'active' => $instrument->active,
                'user_id' => $instrument->user_id,
            ]
        );

        $this->assertEquals(1, \App\Models\Instrument::count());

        $response = $this->delete('/instrument/' . $instrument->id);

        $response->assertStatus(302);

        // Then there shouldn't be an instrument in the database anymore
        $this->assertDatabaseMissing(
            'instruments',
            [
                'name' => $instrument->name,
                'diameter' => $instrument->diameter,
                'type' => $instrument->type,
                'fd' => $instrument->fd,
                'fixedMagnification' => $instrument->fixedMagnification,
                'active' => $instrument->active,
                'user_id' => $instrument->user_id,
            ]
        );
        $this->assertEquals(0, \App\Models\Instrument::count());
    }

    /**
     * Ensure that an instrument can not be deleted by another user.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldNotBeDeleteableByOtherUser()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $instrument =Instrument::factory()->create(['user_id' => $this->_user->id]);

        // Then there should be a new instrument in the database
        $this->assertDatabaseHas(
            'instruments',
            [
                'name' => $instrument->name,
                'diameter' => $instrument->diameter,
                'type' => $instrument->type,
                'fd' => $instrument->fd,
                'fixedMagnification' => $instrument->fixedMagnification,
                'active' => $instrument->active,
                'user_id' => $instrument->user_id,
            ]
        );

        $newUser = User::factory()->create();
        $this->actingAs($newUser);

        $this->expectException(AuthorizationException::class);

        // Try to delete the instrument
        $this->delete('/instrument/' . $instrument->id);
    }

    /**
     * Ensure that an instrument can be deleted by an admin.
     *
     * @test
     *
     * @return None
     */
    public function anInstrumentShouldBeDeleteableByAdmin()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory
        $this->actingAs($this->_user);

        $instrument =Instrument::factory()->create(['user_id' => $this->_user->id]);

        $attributes = [
            'name' => $instrument->name,
            'diameter' => $instrument->diameter,
            'type' => $instrument->type,
            'fd' => $instrument->fd,
            'fixedMagnification' => $instrument->fixedMagnification,
            'active' => $instrument->active,
            'user_id' => $instrument->user_id,
        ];

        // Then there should be a new instrument in the database
        $this->assertDatabaseHas(
            'instruments',
            $attributes
        );

        $newUser = User::factory()->create(['type' => 'admin']);

        $this->actingAs($newUser);

        $this->delete('/instrument/' . $instrument->id);

        // Then there should not be an instrument in the database anymore
        $this->assertDatabaseMissing('instruments', $attributes);
    }

    /**
     * Checks whether a guest is not allowed to create a new instrument.
     *
     * @test
     *
     * @return None
     */
    public function guestsMayNotCreateAnInstrument()
    {
        $this->withoutExceptionHandling();

        $this->assertGuest();

        // When they hit the endpoint in /instrument to create a new instrument while
        // passing the necessary data
        $attributes = [
            'name' => 'New test instrument',
            'diameter' => 352,
            'type' => 4,
            'fd' => 4.5,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $this->expectException(\Illuminate\Auth\AuthenticationException::class);

        $this->post('/instrument', $attributes);
    }

    /**
     * Unverified users are not allowed to create a new instrument.
     *
     * @test
     *
     * @return None
     */
    public function unverifiedUsersMayNotCreateAnInstrument()
    {
        // Given I am a user who is logged in and not verified
        // Act as a new user created by the factory
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user);

        // When they hit the endpoint in /instrument to create a new instrument while
        // passing the necessary data
        $attributes = [
            'name' => 'New test instrument',
            'diameter' => 352,
            'type' => 4,
            'fd' => 4.5,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $this->post('/instrument', $attributes);

        $this->assertDatabaseMissing('instruments', $attributes);
    }

    /**
     * Ensure that the create instrument page is not accessible for guests.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsNotAccessibleForGuests()
    {
        $response = $this->get('/instrument/create');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Ensure that the create instrument page is not accessible for unverified users.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsNotAccessibleForUnverifiedUsers()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)->get('/instrument/create');

        $response->assertStatus(302);
        $response->assertRedirect('/email/verify');
    }

    /**
     * Ensure that the create instrument page is accessible for real users.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsAccessibleForUser()
    {
        $response = $this->actingAs($this->_user)->get('/instrument/create');

        $response->assertStatus(200);
    }

    /**
     * Ensure that the create instrument page is accessible for administrators.
     *
     * @test
     *
     * @return None
     */
    public function createPageIsAccessibleForAdmin()
    {
        $user = User::factory()->create(['type' => 'admin']);
        $response = $this->actingAs($user)->get('/instrument/create');

        $response->assertStatus(200);
    }

    /**
     * Ensure that the update instrument page contains the correct values.
     *
     * @test
     *
     * @return None
     */
    public function updateInstrumentPageContainsCorrectValues()
    {
        $instrument =Instrument::factory()->create(
            ['user_id' => $this->_user->id]
        );

        $response = $this->actingAs($this->_user)->get(
            '/instrument/' . $instrument->id . '/edit'
        );

        $response->assertStatus(200);
        $response->assertSee($instrument->name);
        $response->assertSee($instrument->diameter);
    }

    /**
     * Ensure that we can upload a picture.
     *
     * @test
     */
    public function testCreateInstrumentFileUploaded()
    {
        // Will put the fake image in
        Storage::fake('public');

        $this->actingAs($this->_user)->post(
            'instrument',
            [
                'name' => 'New test instrument',
                'diameter' => 352,
                'type' => 4,
                'fd' => 4.5,
                'fixedMagnification' => null,
                'active' => 1,
                'picture' => UploadedFile::fake()->image('instrument.png'),
            ]
        );

        $instrument = \App\Models\Instrument::firstOrFail();

        Storage::disk('public')->assertExists(
            $instrument->id . '/' . $instrument->id . '.png'
        );
    }

    /**
     * Ensure that the owner of an instrument can see the change instrument button.
     *
     * @test
     *
     */
    public function testShowInstrumentDetailWithChangeButton()
    {
        $instrument =Instrument::factory()->create(
            ['user_id' => $this->_user->id]
        );

        $response = $this->actingAs($this->_user)->get(
            '/instrument/' . $instrument->id
        );

        $response->assertStatus(200);
        $response->assertSee($instrument->name);
        $response->assertSee($instrument->diameter);
        $response->assertSee($this->_user->name);
        $response->assertSee('Edit ' . $instrument->name);
    }

    /**
     * Ensure that a different user than the owner of an instrument cannot
     * see the change instrument button.
     *
     * @test
     *
     */
    public function testShowInstrumentDetailWithoutChangeButton()
    {
        $newUser = User::factory()->create();
        $instrument =Instrument::factory()->create(['user_id' => $newUser->id]);

        $response = $this->actingAs($this->_user)->get(
            '/instrument/' . $instrument->id
        );

        $response->assertStatus(200);
        $response->assertSee($instrument->name);
        $response->assertSee($instrument->diameter);
        $response->assertSee($this->_user->name);
        $response->assertDontSee('Edit ' . $instrument->name);
    }

    /**
     * Ensure that an admin can always see the change instrument button.
     *
     * @test
     *
     */
    public function testAdminAlwaysSeesChangeButton()
    {
        $admin = User::factory()->create(['type' => 'admin']);
        $instrument =Instrument::factory()->create(
            ['user_id' => $this->_user->id]
        );

        $response = $this->actingAs($admin)->get('/instrument/' . $instrument->id);

        $response->assertStatus(200);
        $response->assertSee($instrument->name);
        $response->assertSee($instrument->diameter);
        $response->assertSee($this->_user->name);
        $response->assertSee('Edit ' . $instrument->name);
    }

    /**
     * Ensure that a guest user can not see the change instrument button.
     *
     * @test
     *
     */
    public function testGuestNeverSeesChangeButton()
    {
        $instrument =Instrument::factory()->create(
            ['user_id' => $this->_user->id]
        );

        $response = $this->get('/instrument/' . $instrument->id);

        $response->assertStatus(200);
        $response->assertSee($instrument->name);
        $response->assertSee($instrument->diameter);
        $response->assertSee($this->_user->name);
        $response->assertDontSee('Edit ' . $instrument->name);
    }

    /**
     * Ensure that only an admin can see the admin page with all the instruments.
     *
     * @test
     *
     */
    public function testOnlyAdminCanSeeOverviewOfAllInstruments()
    {
        User::factory(50)->create();
        $instrument = Instrument::factory(500)->create();

        // Check as guest
        $response = $this->get('/instrument/admin');

        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // Check as normal user
        $response = $this->actingAs($this->_user)->get('/instrument/admin');

        $response->assertStatus(401);

        // Check as admin
        $admin = User::factory()->create(['type' => 'admin']);
        $response = $this->actingAs($admin)->get('/instrument/admin');

        $response->assertStatus(200);
        $response->assertSee('All instruments');
    }

    /**
     * Ensure that logged in users can see the Json information of an instrument.
     *
     * @test
     *
     */
    public function testJsonInformationForInstrument()
    {
        $instrument =Instrument::factory()->create(
            ['user_id' => $this->_user->id]
        );

        // Only for logged in users!
        $response = $this->get('/getInstrumentJson/' . $instrument->id);
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // Test for logged in user
        $response = $this->actingAs($this->_user)->get(
            '/getInstrumentJson/' . $instrument->id
        );

        $this->assertEquals($response['name'], $instrument->name);
        $this->assertEquals($response['id'], $instrument->id);
        $this->assertEquals($response['user_id'], $instrument->user_id);
        $this->assertEquals($response['type'], $instrument->type);
        $this->assertEquals($response['diameter'], $instrument->diameter);
        $this->assertEquals($response['fd'], $instrument->fd);
        $this->assertEquals(
            $response['fixedMagnification'],
            $instrument->fixedMagnification
        );
        $this->assertEquals($response['active'], $instrument->active);
    }

    /**
     * Ensure that we get an image of an instrument.
     *
     * @test
     *
     */
    public function testGetInstrumentImage()
    {
        // Will put the fake image in
        Storage::fake('public');

        $instrument =Instrument::factory()->create(
            ['user_id' => $this->_user->id]
        );

        // Check the image, if no image is uploaded
        $this->actingAs($this->_user)->get(
            'instrument/' . $instrument->id . '/getImage'
        );

        Storage::disk('public')->assertExists(
            $instrument->id . '/' . $instrument->id . '.png'
        );

        // Check the image if we have uploaded an image
        $this->actingAs($this->_user)->post(
            'instrument',
            [
                'name' => 'New test instrument',
                'diameter' => 352,
                'type' => 4,
                'fd' => 4.5,
                'fixedMagnification' => null,
                'active' => 1,
                'picture' => UploadedFile::fake()->image('instrument.png'),
            ]
        );

        $instrument2 = DB::table('instruments')->latest('id')->first();

        Storage::disk('public')->assertExists(
            $instrument2->id . '/' . $instrument2->id . '.png'
        );
    }

    /**
     * Ensure that we can delete an image of an instrument.
     *
     * @test
     *
     */
    public function testDeleteInstrumentImage()
    {
        // Will put the fake image in
        Storage::fake('public');

        // Check if we can delete the image if we have uploaded an image
        $this->actingAs($this->_user)->post(
            'instrument',
            [
                'name' => 'New test instrument',
                'diameter' => 352,
                'type' => 4,
                'fd' => 4.5,
                'fixedMagnification' => null,
                'active' => 1,
                'picture' => UploadedFile::fake()->image('instrument.png'),
            ]
        );

        $instrument = DB::table('instruments')->latest('id')->first();

        $this->actingAs($this->_user)->post(
            'instrument/' . $instrument->id . '/deleteImage'
        );

        Storage::disk('public')->assertMissing(
            $instrument->id . '/' . $instrument->id . '.png'
        );

        // Check if another user cannot delete the image if we have uploaded an image
        $this->actingAs($this->_user)->post(
            'instrument',
            [
                'name' => 'New test instrument',
                'diameter' => 352,
                'type' => 4,
                'fd' => 4.5,
                'fixedMagnification' => null,
                'active' => 1,
                'picture' => UploadedFile::fake()->image('instrument.png'),
            ]
        );

        $instrument = DB::table('instruments')->latest('id')->first();

        $user = User::factory()->create();

        $this->actingAs($user)->post(
            'instruments/' . $instrument->id . '/deleteImage'
        );

        Storage::disk('public')->assertExists(
            $instrument->id . '/' . $instrument->id . '.png'
        );
    }

    /**
     * Ensure that the autocomplete works for select2.
     *
     * @test
     *
     */
    public function testAutocompleteForInstrument()
    {
        $instrument =Instrument::factory()->create(
            ['user_id' => $this->_user->id, 'name' => 'DeepskyLog test instrument']
        );

        $instrument2 =Instrument::factory()->create(
            ['user_id' => $this->_user->id, 'name' => 'Other test instrument']
        );

        // Only for logged in users!
        $response = $this->get('/instrument/autocomplete?q=Deep');
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // Test for logged in user
        $response = $this->actingAs($this->_user)->get(
            '/instrument/autocomplete?q=Deep'
        );

        $this->assertEquals($instrument->id, $response[0]['id']);
        $this->assertEquals($instrument->name, $response[0]['name']);

        $response = $this->actingAs($this->_user)->get(
            '/instrument/autocomplete?q=test'
        );

        $this->assertEquals($instrument->id, $response[0]['id']);
        $this->assertEquals($instrument->name, $response[0]['name']);

        $this->assertEquals($instrument2->id, $response[1]['id']);
        $this->assertEquals($instrument2->name, $response[1]['name']);
    }

    /**
     * Ensure that the diameter and focal length are shown in imperial units if
     * requested.
     *
     * @test
     *
     */
    public function testImperialUnitsDisplayInstrument()
    {
        $user = User::factory()->create(['showInches' => 1]);
        $instrument =Instrument::factory()->create(
            [
                'user_id' => $user->id,
                'diameter' => 457,
                'fd' => 4.5,
            ]
        );

        $response = $this->actingAs($user)->get('/instrument/' . $instrument->id);

        $response->assertStatus(200);
        $response->assertSee($instrument->name);
        $response->assertSee(round($instrument->diameter / 25.4, 2));
        $response->assertSee($user->name);
        $response->assertSee(
            round($instrument->diameter * $instrument->fd / 25.4, 2)
        );
    }

    /**
     * Ensure that the diameter and focal length are shown in imperial units if
     * requested.
     *
     * @test
     *
     */
    public function testImperialUnitsAddInstrument()
    {
        $user = User::factory()->create(['showInches' => 1]);

        // When they hit the endpoint in /instrument to create a new instrument
        // while passing the necessary data
        $attributes = [
            'name' => 'Test instrument',
            'diameter' => 18,
            'type' => 4,
            'fd' => 4.5,
            'fixedMagnification' => null,
            'active' => 1,
        ];

        $this->actingAs($user)->post('instrument', $attributes);

        $instrument = \App\Models\Instrument::firstOrFail();

        $response = $this->actingAs($user)->get('/instrument/' . $instrument->id);

        $response->assertStatus(200);
        $response->assertSee('Test instrument');
        $response->assertSee(18);
        $response->assertSee($user->name);
        $response->assertSee(
            round(18 * 4.5, 2)
        );

        $attributes['diameter'] *= 25.4;
        $this->assertDatabaseHas('instruments', $attributes);
    }

    /**
     * Tests the detail page.
     * The owner should only see the used eyepieces, filters, lenses and
     * locations for the instrument.
     *
     * @test
     *
     */
    public function testShowInstrumentDetailAsOwner()
    {
        $instrument = Instrument::factory()->create();

        // As guest
        $this->assertGuest();
        $response = $this->get(
            '/instrument/' . $instrument->id
        );
        $response->assertStatus(200);
        $response->assertDontSee('Used eyepieces');
        $response->assertDontSee('Used filters');
        $response->assertDontSee('Used lenses');
        $response->assertDontSee('Observed in the following locations');
        $response->assertSee('First light');
        $response->assertSee('Last used on');

        $response = $this->actingAs($this->_user)->get(
            '/instrument/' . $instrument->id
        );

        $response->assertStatus(200);
        $response->assertSee('Used eyepieces');
        $response->assertSee('Used filters');
        $response->assertSee('Used lenses');
        $response->assertSee('Observed in the following locations');
        $response->assertSee('First light');
        $response->assertSee('Last used on');

        // As other user
        $otherUser = User::factory()->create();
        $response = $this->actingAs($otherUser)->get(
            '/instrument/' . $instrument->id
        );
        $response->assertStatus(200);
        $response->assertDontSee('Used eyepieces');
        $response->assertDontSee('Used filters');
        $response->assertDontSee('Used lenses');
        $response->assertDontSee('Observed in the following locations');
        $response->assertSee('First light');
        $response->assertSee('Last used on');

        // As admin
        $admin = User::factory()->create(['type' => 'admin']);
        $response = $this->actingAs($admin)->get(
            '/instrument/' . $instrument->id
        );
        $response->assertStatus(200);
        $response->assertDontSee('Used eyepieces');
        $response->assertDontSee('Used filters');
        $response->assertDontSee('Used lenses');
        $response->assertDontSee('Observed in the following locations');
        $response->assertSee('First light');
        $response->assertSee('Last used on');
    }
}
