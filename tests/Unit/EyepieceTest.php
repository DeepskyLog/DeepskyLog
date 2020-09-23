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

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Eyepiece;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Tests for creating, deleting, and adapting filters.
 *
 * @category Test
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class EyepieceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Checks whether an eyepiece can show the correct generic name.
     *
     * @test
     *
     * @return None
     */
    public function checkGenericName()
    {
        User::factory()->create();
        $eyepieces = Eyepiece::factory(5)->create(['user_id' => 1]);

        foreach ($eyepieces as $eyepiece) {
            $this->assertStringContainsString(
                $eyepiece->brand,
                $eyepiece->genericname
            );
            $this->assertStringContainsString(
                $eyepiece->type,
                $eyepiece->genericname
            );
            $this->assertStringContainsString(
                $eyepiece->focalLength,
                $eyepiece->genericname
            );
            $this->assertStringContainsString(
                'mm',
                $eyepiece->genericname
            );

            if ($eyepiece->maxFocalLength) {
                $this->assertStringContainsString(
                    $eyepiece->maxFocalLength,
                    $eyepiece->genericname
                );
                $this->assertStringContainsString('-', $eyepiece->genericname);
            }
        }
    }
}
