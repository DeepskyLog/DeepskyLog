<?php
// accomplishments.php
global $inIndex;
if ((!isset($inIndex))||(!$inIndex)) {
    include "../../redirect.php";
}

/**
Returns the accomplishments (badges) of an observer.

They are the achievements of the new application, which awards them every six
hours (update:achievements); the view legacy_accomplishments of the new DB
shows them in the columns of the old accomplishments table, with every tier up
to the highest one reached. This site does not calculate or store them any
more.
*/
class Accomplishments
{
    /** Returns 1 if the observer has an accomplishment.

    @param $observerId The observer for which the accomplishments should be returned from the database.
    @return integer[] [ messierBronze, messierSilver, messierGold, ... ]
    */
    public function getAllAccomplishments($observerId)
    {
        global $objDatabase;
        $recordArray = $objDatabase->selectRecordsetArray("select * from accomplishments where observer = \"". $observerId . "\";");
        return $recordArray[0];
    }

    /** Returns 1 if the observer has 1, 10, 25, 50, 100, 250, 500, 1000, 2500 or 5000 comet observations.

    @param $observerId The observer for which the comet observations should be returned from the database.
    @return integer[] [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
    */
    public function getCometObservationsAccomplishments($observerId)
    {
        global $objDatabase;
        $recordArray = $objDatabase->selectRecordsetArray("select CometObservationsNewbie as '0', CometObservationsRookie as '1', CometObservationsBeginner as '2', CometObservationsTalented as '3', CometObservationsSkilled as '4', CometObservationsIntermediate as '5', CometObservationsExperienced as '6', CometObservationsAdvanced as '7', CometObservationsSenior as '8', CometObservationsExpert as '9' from accomplishments where observer = \"". $observerId . "\";");
        return $recordArray[0];
    }

    /** Returns 1 if the observer has observed 1, 10, 25, 50, 100, 250, 500, 1000, 2500 or 5000 different comets.

    @param $observerId The observer for which the different comet observations should be returned from the database.
    @return integer[] [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
    */
    public function getCometsObservedAccomplishments($observerId)
    {
        global $objDatabase;
        $recordArray = $objDatabase->selectRecordsetArray("select CometsObservedNewbie as '0', CometsObservedRookie as '1', CometsObservedBeginner as '2', CometsObservedTalented as '3', CometsObservedSkilled as '4', CometsObservedIntermediate as '5', CometsObservedExperienced as '6', CometsObservedAdvanced as '7', CometsObservedSenior as '8', CometsObservedExpert as '9' from accomplishments where observer = \"". $observerId . "\";");
        return $recordArray[0];
    }

    /** Returns 1 if the observer has 1, 10, 25, 50, 100, 250, 500, 1000, 2500 or 5000 comet drawings.

    @param $observerId The observer for which the comet drawings should be returned from the database.
    @return integer[] [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
    */
    public function getCometDrawingsAccomplishments($observerId)
    {
        global $objDatabase;
        $recordArray = $objDatabase->selectRecordsetArray("select CometDrawingsNewbie as '0', CometDrawingsRookie as '1', CometDrawingsBeginner as '2', CometDrawingsTalented as '3', CometDrawingsSkilled as '4', CometDrawingsIntermediate as '5', CometDrawingsExperienced as '6', CometDrawingsAdvanced as '7', CometDrawingsSenior as '8', CometDrawingsExpert as '9' from accomplishments where observer = \"". $observerId . "\";");
        return $recordArray[0];
    }

    /** The new application recalculates the deepsky accomplishments.
      @param $observerId The observer whose observations changed.
    */
    public function recalculateDeepsky($observerId)
    {
    }

    /** The new application recalculates the comet accomplishments.
      @param $observerId The observer whose observations changed.
    */
    public function recalculateComets($observerId)
    {
    }
}
