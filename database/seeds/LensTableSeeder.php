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
use App\LensOld;
use App\Lens;
use App\User;

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
//        factory(App\Lens::class, 15000)->create();
        $lensData = LensOld::all();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('lens')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ($lensData as $lens) {
            $observer = User::where('username', $lens->observer)->pluck("id");
            if (sizeof($observer) > 0) {

                if ($lens->timestamp == '') {
                    $date = date('Y-m-d H:i:s');
                } else {
                    list($year, $month, $day, $hour, $minute, $second)
                        = sscanf($lens->timestamp, '%4d%2d%2d%2d%2d%d');
                    $date = date(
                        'Y-m-d H:i:s',
                        mktime($hour, $minute, $second, $month, $day, $year)
                    );
                }

                Lens::create(
                    [
                        'id' => $lens->id,
                        'name' => html_entity_decode($lens->name),
                        'factor' => $lens->factor,
                        'observer_id' => $observer[0],
                        'active' => $lens->lensactive,
                        'created_at' => $date
                    ]
                );
            }
        }
    }
}
