<?php

/**
 * Seeder for the targetpartof table of the database.
 *
 * PHP Version 7
 *
 * @category Database
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

use App\Models\ObjectPartofOld;
use App\Models\TargetName;
use App\Models\TargetPartOf;
use Illuminate\Database\Seeder;

/**
 * Seeder for the targetpartof table of the database.
 *
 * @category Database
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class TargetPartOfTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     */
    public function run()
    {
        // Import the objectpart data
        $objectData = ObjectPartofOld::all();

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

            $target = TargetName::where('altname', '=', $oldObject->objectname);
            $partof = TargetName::where('altname', '=', $oldObject->partofname);

            if ($target->count() && $partof->count()) {
                TargetPartOf::firstOrCreate(
                    [
                        'target_id' => $target->first()->target_id,
                        'partof_id' => $partof->first()->target_id,
                        'created_at' => $date,
                    ]
                );
            }
        }
    }
}
