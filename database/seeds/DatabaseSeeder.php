<?php

/**
 * Seeder for the database.
 * Fills the database with random values.
 *
 * PHP Version 7
 *
 * @category Database
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
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call(
            [UsersTableSeeder::class, LensTableSeeder::class,
                FilterTableSeeder::class, EyepieceTableSeeder::class,
                EyepieceBrandSeeder::class, EyepieceTypeSeeder::class,
                MessagesTableSeeder::class, InstrumentTableSeeder::class,
                LocationTableSeeder::class, TargetTableSeeder::class,
                TargetNameTableSeeder::class, TargetPartOfTableSeeder::class, ]
        );
    }
}
