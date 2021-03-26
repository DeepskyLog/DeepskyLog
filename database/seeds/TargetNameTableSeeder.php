<?php

/**
 * Seeder for the target table of the database.
 * Fills the database with the deepsky objects and comets from the old database.
 *
 * PHP Version 7
 *
 * @category Database
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

use App\Models\TargetName;
use App\Models\ObjectNamesOld;
use Illuminate\Database\Seeder;

/**
 * Seeder for the target table of the database.
 * Fills the database with the deepsky objects and comets from the old database.
 *
 * @category Database
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class TargetNameTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Add all names for the comets, moon, planets, sun
        foreach (\App\Models\TargetType::where('observation_type', '!=', 'ds')->where('observation_type', '!=', 'double')->get() as $type) {
            foreach (\App\Models\Target::where('target_type', $type->id)->get() as $target) {
                \App\Models\TargetName::create(
                    [
                        'target_id' => $target->id,
                        'catalog'   => '',
                        'catindex'  => $target->target_name,
                        'altname'   => $target->target_name,
                    ]
                );
            }
        }

        // Import the object data
        $objectData = ObjectNamesOld::all();

        foreach ($objectData as $oldObject) {
            if ($oldObject->timestamp == '') {
                $date = date('Y-m-d H:i:s');
            } else {
                [$year, $month, $day, $hour, $minute, $second]
                      = sscanf($oldObject->timestamp, '%4d%2d%2d%2d%2d%d');
                $date = date(
                    'Y-m-d H:i:s',
                    mktime($hour, $minute, $second, $month, $day, $year)
                );
            }

            if (\App\Models\Target::where(
                'target_name->en',
                $oldObject->objectname
            )->count()
            ) {
                $target = TargetName::firstOrCreate(
                    [
                        'target_id' => \App\Models\Target::where(
                            'target_name->en',
                            $oldObject->objectname
                        )->first()->id,
                        'altname' => $oldObject->altname,
                    ]
                );
                $target->catalog  = $oldObject->catalog;
                $target->catindex = $oldObject->catindex;

                $target->created_at = $date;
                $target->save();
            }
        }
    }
}
