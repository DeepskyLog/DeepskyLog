<?php

/**
 * Seeder for the target table of the database.
 * Fills the database with the deepsky objects and comets from the old database.
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
use App\ObjectOld;
use App\CometObjectOld;
use App\Target;

/**
 * Seeder for the target table of the database.
 * Fills the database with the deepsky objects and comets from the old database.
 *
 * @category Database
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class TargetTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Import the object data
        $objectData = ObjectOld::all();

        foreach ($objectData as $oldObject) {
            if ($oldObject->timestamp == '') {
                $date = date('Y-m-d H:i:s');
            } else {
                list($year, $month, $day, $hour, $minute, $second)
                       = sscanf($oldObject->timestamp, '%4d%2d%2d%2d%2d%d');
                $date = date(
                    'Y-m-d H:i:s',
                    mktime($hour, $minute, $second, $month, $day, $year)
                );
            }

            $type = $oldObject->type;

            if ($type == 'LMCDN' || $type == 'SMCDN') {
                $type = 'GXADN';
            }

            if ($type == 'GXAGC' || $type == 'LMCGC' || $type == 'SMCGC') {
                $type = 'GLOCL';
            }

            if ($type == 'GACAN' || $type == 'LMCCN' || $type == 'SMCCN') {
                $type = 'CLANB';
            }

            if ($type == 'LMCOC' || $type == 'SMCOC' || $type == 'AA8STAR') {
                $type = 'OPNCL';
            }

            if ($type == 'AA1STAR') {
                $type = 'DS';
            }

            Target::create(
                [
                    'name' => $oldObject->name,
                    'type' => $type,
                    'con' => $oldObject->con,
                    'ra' => $oldObject->ra,
                    'decl' => $oldObject->decl,
                    'mag' => $oldObject->mag,
                    'subr' => $oldObject->subr,
                    'diam1' => $oldObject->diam1,
                    'diam2' => $oldObject->diam2,
                    'pa' => $oldObject->pa,
                    'SBObj' => $oldObject->SBObj,
                    'datasource' => $oldObject->datasource,
                    'description' => $oldObject->description,
                    'urano' => $oldObject->urano,
                    'urano_new' => $oldObject->urano_new,
                    'sky' => $oldObject->sky,
                    'millenium' => $oldObject->millenium,
                    'taki' => $oldObject->taki,
                    'psa' => $oldObject->psa,
                    'torresB' => $oldObject->torresB,
                    'torresBC' => $oldObject->torresBC,
                    'torresC' => $oldObject->torresC,
                    'milleniumbase' => $oldObject->milleniumbase,
                    'DSLDL' => $oldObject->DSLDL,
                    'DSLDP' => $oldObject->DSLDP,
                    'DSLLL' => $oldObject->DSLLL,
                    'DSLLP' => $oldObject->DSLLP,
                    'DSLOL' => $oldObject->DSLOL,
                    'DSLOP' => $oldObject->DSLOP,
                    'DeepskyHunter' => $oldObject->DeepskyHunter,
                    'Interstellarum' => $oldObject->Interstellarum,
                    'created_at' => $date
                ]
            );
        }

        // Import the cometobject data
        $cometData = CometObjectOld::all();

        foreach ($cometData as $comet) {
            if ($comet->timestamp == '') {
                $date = date('Y-m-d H:i:s');
            } else {
                list($year, $month, $day, $hour, $minute, $second)
                       = sscanf($comet->timestamp, '%4d%2d%2d%2d%2d%d');
                $date = date(
                    'Y-m-d H:i:s',
                    mktime($hour, $minute, $second, $month, $day, $year)
                );
            }

            Target::create(
                [
                    'name' => $comet->name,
                    'type' => 'COMET',
                    'icqname' => $comet->icqname,
                    'created_at' => $date
                ]
            );
        }
    }
}
