<?php
/**
 * Seeder for the Lens table of the database.
 * Fills the database with random lenses.
 *
 * PHP Version 7
 *
 * @category Database
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

use Illuminate\Database\Seeder;

/**
 * Seeder for the Lens table of the database.
 * Fills the database with random lenses.
 *
 * @category Database
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class LensTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Lens::class, 150)->create();
    }
}
