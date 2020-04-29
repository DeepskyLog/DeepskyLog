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

use App\CometObjectOld;
use App\ObjectOld;
use App\Target;
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
                [$year, $month, $day, $hour, $minute, $second]
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

            $mag = $oldObject->mag;
            if ($mag > 80) {
                $mag = null;
            }
            $subr = $oldObject->subr;
            if ($subr > 80) {
                $subr = null;
            }

            Target::create(
                [
                    'name' => $oldObject->name,
                    'type' => $type,
                    'con' => $oldObject->con,
                    'ra' => $oldObject->ra,
                    'decl' => $oldObject->decl,
                    'mag' => $mag,
                    'subr' => $subr,
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
                    'created_at' => $date,
                ]
            );
        }

        // Import the cometobject data
        $cometData = CometObjectOld::all();

        foreach ($cometData as $comet) {
            if ($comet->timestamp == '') {
                $date = date('Y-m-d H:i:s');
            } else {
                [$year, $month, $day, $hour, $minute, $second]
                       = sscanf($comet->timestamp, '%4d%2d%2d%2d%2d%d');
                $date = date(
                    'Y-m-d H:i:s',
                    mktime($hour, $minute, $second, $month, $day, $year)
                );
            }

            Target::create(
                [
                    'name' => html_entity_decode($comet->name),
                    'type' => 'COMET',
                    'created_at' => $date,
                ]
            );
        }
    }
}
