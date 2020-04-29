<?php

/**
 * Seeder for the instrument table of the database.
 * Fills the database with the instruments from the old database.
 *
 * PHP Version 7
 *
 * @category Database
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

use App\Instrument;
use App\InstrumentOld;
use App\User;
use Illuminate\Database\Seeder;

/**
 * Seeder for the Instrument table of the database.
 * Fills the database with the instruments from the old database.
 *
 * @category Database
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class InstrumentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $instrumentData = InstrumentOld::all();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('instruments')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ($instrumentData as $instrument) {
            $observer = User::where('username', $instrument->observer)->pluck('id');
            if (count($observer) > 0) {
                if ($instrument->timestamp == '') {
                    $date = date('Y-m-d H:i:s');
                } else {
                    [$year, $month, $day, $hour, $minute, $second]
                        = sscanf($instrument->timestamp, '%4d%2d%2d%2d%2d%d');
                    $date = date(
                        'Y-m-d H:i:s',
                        mktime($hour, $minute, $second, $month, $day, $year)
                    );
                }

                $type = $instrument->type;

                if ($type === -1) {
                    $type = 5;
                }

                $fm = $instrument->fixedMagnification;

                if ($instrument->fixedMagnification === 0) {
                    $fm = null;
                }

                $fd = $instrument->fd;

                if ($instrument->fd === 0.0) {
                    $fd = null;
                }

                $newInstrument = Instrument::create(
                    [
                        'id' => $instrument->id,
                        'name' => html_entity_decode($instrument->name),
                        'diameter' => $instrument->diameter,
                        'fd' => $fd,
                        'type' => $instrument->type,
                        'fixedMagnification' => $fm,
                        'user_id' => $observer[0],
                        'active' => $instrument->instrumentactive,
                        'created_at' => $date,
                    ]
                );
            }
        }
    }
}
