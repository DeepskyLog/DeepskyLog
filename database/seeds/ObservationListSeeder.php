<?php
/**
 * Seeder for the observation list table of the database.
 * Fills the database with the observation lists from the old database.
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
 * Seeder for the observation list table of the database.
 * Fills the database with the observation lists from the old database.
 *
 * @category Database
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class ObservationListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Import the object data
        $objectData = \App\Models\ObservationListOld::select('observerid', 'listname', 'public')->distinct()->get();

        $cnt   = 0;
        foreach ($objectData as $data) {
            $observerid = \App\Models\User::where('username', html_entity_decode($data->observerid))->first()->id;
            $timestamp  = \App\Models\ObservationListOld::where('observerid', $data->observerid)->where('listname', $data->listname)->pluck('timestamp')[0];
            if ($timestamp == '') {
                $timestamp = '20210316112000';
            }
            [$year, $month, $day, $hour, $minute]
                  = sscanf($timestamp, '%4d%2d%2d%2d%2d');
            $date = date(
                'Y-m-d H:i:s',
                mktime($hour, $minute, 0, $month, $day, $year)
            );

            if ($data->listname == '') {
                $listname = 'List ' . $cnt;
            } else {
                $listname = $data->listname;
            }

            $list = \App\Models\ObservationList::create(
                [
                    'name'             => html_entity_decode($listname),
                    'user_id'          => $observerid,
                    'discoverable'     => $data->public,
                    'created_at'       => $date,
                    'updated_at'       => $date,
                ]
            );

            $list->save();
            $cnt++;
        }
        dump('Imported ' . $cnt . ' observing lists');
    }
}
