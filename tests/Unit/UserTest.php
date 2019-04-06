<?php
/**
 * Tests for creating, deleting, and adapting users.
 *
 * PHP Version 7
 *
 * @category Test
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Tests for creating, deleting, and adapting users.
 *
 * PHP Version 7
 *
 * @category Test
 * @package  DeepskyLog
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
        $user = factory(App\User::class)->create();
        dd(count($user));

        $user->lenses->create(
            [
                'name' => 'Test lens',
                'factor' => 2.0
            ]
        );

        $this->assertEquals('Test lens', $user->lens->name);
        $this->assertEquals(2.0, $user->lens->factor);
    }
}
