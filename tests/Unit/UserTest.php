<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     *
     * A basic unit test example.
     *
     * @return void
     */
    public function a_user_can_have_a_lens()
    {
        $user = factory('App\User')->create();

        $user->lenses->create([
            'name' => 'Test lens',
            'factor' => 2.0
        ]);

        $this->assertEquals('Test lens', $user->lens->name);
        $this->assertEquals(2.0, $user->lens->factor);
    }
}
