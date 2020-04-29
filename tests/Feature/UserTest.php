<?php
/**
 * Tests for creating, deleting, and adapting users.
 *
 * PHP Version 7
 *
 * @category Test
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for creating, deleting, and adapting users.
 *
 * PHP Version 7
 *
 * @category Test
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Checks whether the user can have a lens.
     *
     * @test
     *
     * @return void
     */
    public function aUserCanHaveALens()
    {
        $this->withoutExceptionHandling();

        $user = factory('App\User')->create();
        $this->actingAs($user);

        $this->assertTrue($this->isAuthenticated());

        // Create a new lens
        $lens = new \App\Lens;
        $lens->name = 'Tested lens';
        $lens->factor = 1.43;
        $lens->user_id = $user->id;

        $lens->save();

        $this->assertEquals('Tested lens', $user->lenses->first()->name);
        $this->assertEquals(1.43, $user->lenses->first()->factor);
    }

    // TODO: Rewrite charts to use https://dev.to/arielmejiadev/use-laravel-charts-in-laravel-5bbm
}
