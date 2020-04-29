<?php
/**
 * Seeder for the Lens table of the database.
 * Fills the database with the lenses from the old database.
 *
 * PHP Version 7
 *
 * @category Database
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

use App\Lens;
use App\LensOld;
use App\User;
use Illuminate\Database\Seeder;

/**
 * Seeder for the Lens table of the database.
 * Fills the database with the lenses from the old database.
 *
 * @category Database
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class LensTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return None
     */
    public function run()
    {
        $lensData = LensOld::all();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('lens')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ($lensData as $lens) {
            $observer = User::where('username', $lens->observer)->pluck('id');
            if (count($observer) > 0) {
                if ($lens->timestamp == '') {
                    $date = date('Y-m-d H:i:s');
                } else {
                    [$year, $month, $day, $hour, $minute, $second]
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
                        'user_id' => $observer[0],
                        'active' => $lens->lensactive,
                        'created_at' => $date,
                    ]
                );
            }
        }
    }
}
