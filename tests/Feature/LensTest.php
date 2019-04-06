<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LensTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function a_user_can_create_a_lens()
    {
        $this->withoutExceptionHandling();

        // Given I am a user who is logged in and verified
        // Act as a new user created by the factory

        $this->actingAs(factory('App\User')->create());

        // When they hit the endpoint in /lens to create a new lens while passing the necessary data
        $attributes = [
            'name' => 'Test lens',
            'factor' => 2.0
        ];

        $this->post('/lens', $attributes);

        // Then there should be a new lens in the database
        $this->assertDatabaseHas('lens', $attributes);
    }

    /**
     * @test
     */
    public function guests_may_not_create_a_lens()
    {
        $this->withoutExceptionHandling();

        // When they hit the endpoint in /lens to create a new lens while passing the necessary data
        $attributes = [
            'name' => 'Test lens',
            'factor' => 2.0
        ];

        $this->post('/lens', $attributes)->assertRedirect('/');

        // Can work with changes in web.php:
        // Route::middleware('auth')->post('lens', ...)
    }


    /**
     * A basic feature test example.
     *
     */
    //public function testExample()
    //{
        //factory('App\Lens')->create();

    //    $response = $this->get('/');

    //    $response->assertStatus(200);
    //}
}
