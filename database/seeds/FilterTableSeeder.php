<?php
/**
 * Seeder for the Filter table of the database.
 * Fills the database with the filters from the old database.
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
use App\FilterOld;
use App\Filter;
use App\User;

/**
 * Seeder for the Filter table of the database.
 * Fills the database with the filters from the old database.
 *
 * @category Database
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class FilterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return None
     */
    public function run()
    {
        $filterData = FilterOld::all();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('filters')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ($filterData as $filter) {
            $observer = User::where('username', $filter->observer)->pluck('id');
            if (sizeof($observer) > 0) {
                if ($filter->timestamp == '') {
                    $date = date('Y-m-d H:i:s');
                } else {
                    list($year, $month, $day, $hour, $minute, $second)
                        = sscanf($filter->timestamp, '%4d%2d%2d%2d%2d%d');
                    $date = date(
                        'Y-m-d H:i:s',
                        mktime($hour, $minute, $second, $month, $day, $year)
                    );
                }

                $newFilter = Filter::create(
                    [
                        'id' => $filter->id,
                        'name' => html_entity_decode($filter->name),
                        'type' => $filter->type,
                        'observer_id' => $observer[0],
                        'active' => $filter->filteractive,
                        'created_at' => $date
                    ]
                );

                if ($filter->wratten != 0) {
                    $newFilter->wratten = $filter->wratten;
                }

                if ($filter->color != 0) {
                    $newFilter->color = $filter->color;
                }

                if ($filter->schott != 0) {
                    $newFilter->schott = $filter->schott;
                }

            }
        }
    }
}
