<?php

/**
 * Seeder for the database.
 * Fills the database with random values.
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
 * Seeder for the database.
 * Fills the database with random values.
 *
 * @category Database
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(
            [UsersTableSeeder::class, LensTableSeeder::class,
            MessagesTableSeeder::class]
        );
    }
}
