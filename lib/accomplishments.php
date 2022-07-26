<?php
// accomplishments.php
global $inIndex;
if ((!isset($inIndex))||(!$inIndex)) {
    include "../../redirect.php";
}
require_once "observations.php";

/**
Collects all functions needed to calculate and retrieve the accomplishments of an observer.
*/
class Accomplishments
{
    /** Calculates the number of different objects of a certain catalog the observer has seen and
  returns an array consisting of $ranking elements. For messier, $ranking should be 3 and the
  result is [ bronze, silver, gold ]

  @param $observer The observer for which to calculate the number of objects seen.
  @param $catalog The catalog to use. If the catalog is
  + drawings: The number of drawings by an observer is calculated.
  + cometObservations: The number of observations in the comets module.
  + cometsObserved: The number of comets observed.
  + cometDrawings: The number of comet drawings.
  + openClusters: The number of open clusters seen or drawn.
  + globularClusters: The number of globular clusters seen or drawn.
  + planetaryNebulae: The number of planetary nebulae seen or drawn.
  + galaxies: The number of galaxies seen or drawn.
  + nebulae: The number of nebulae seen or drawn.
  + differentObjects: The number of different objects seen or drawn
  @param $drawings True if the drawings should be calculated.
  @param $max The maximum number of elements to take into account.
  @return integer[] [ bronze, silver, gold ]
  */
  public function calculateAccomplishments($observer, $catalog, $drawings = false, $max = 0)
  {
      global $objObservation, $objObserver, $objCometObservation, $objDatabase;
      $objObservation = new Observations();

      $extra = "";
      if ($drawings) {
          $extra = " and observations.hasDrawing = 1";
      }

      switch ($catalog) {
      case "drawings":
      $total = $objObservation->getDsDrawingsCountFromObserver($observer);
      break;
      case "cometObservations":
      $total = $objObserver->getNumberOfCometObservations($observer);
      break;
      case "cometsObserved":
      $total = $objCometObservation->getNumberOfObjects($observer);
      break;
      case "cometDrawings":
      $total = $objCometObservation->getCometDrawingsCountFromObserver($observer);
      break;
      case "OpenCluster":
      $total = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"OPNCL\" and observations.observerid = \"" . $observer . "\"" . $extra));
      $total += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"CLANB\" and observations.observerid = \"" . $observer . "\"" . $extra));
      break;
      case "GlobularCluster":
      $total = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"GLOCL\" and observations.observerid = \"" . $observer . "\"" . $extra));
      break;
      case "PlanetaryNebula":
      $total = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"PLNNB\" and observations.observerid = \"" . $observer . "\"" . $extra));
      break;
      case "Galaxy":
      $total = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"GALXY\" and observations.observerid = \"" . $observer . "\"" . $extra));
      break;
      case "Nebula":
      $total = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"EMINB\" and observations.observerid = \"" . $observer . "\"" . $extra));
      $total += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"ENRNN\" and observations.observerid = \"" . $observer . "\"" . $extra));
      $total += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"ENSTR\" and observations.observerid = \"" . $observer . "\"" . $extra));
      $total += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"REFNB\" and observations.observerid = \"" . $observer . "\"" . $extra));
      $total += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"RNHII\" and observations.observerid = \"" . $observer . "\"" . $extra));
      $total += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"HII\" and observations.observerid = \"" . $observer . "\"" . $extra));
      $total += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"SNREM\" and observations.observerid = \"" . $observer . "\"" . $extra));
      $total += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"WRNEB\" and observations.observerid = \"" . $observer . "\"" . $extra));
      break;
      case "objects":
      case "Objects":
        if ($drawings) {
            $total = $objObservation->getNumberOfObjectDrawings($observer);
        } else {
            $total = $objObservation->getNumberOfObjects($observer);
        }
        break;
      default:
        if ($catalog == "messier") {
            $catalog = "M";
        } elseif ($catalog == "Herschel") {
            $catalog = "H400";
        } elseif ($catalog == "HerschelII") {
            $catalog = "H400-II";
        }
        if ($drawings) {
            $total = $objObservation->getDrawingsCountFromCatalog($observer, $catalog);
        } else {
            $total = $objObservation->getObservedCountFromCatalogOrList($observer, $catalog);
        }
        break;
      }
      if ($max == 0) {
          return $this->ranking($total, $this->getNumberOfCategories($catalog));
      } else {
          return $this->ranking($total, $this->getNumberOfCategories($catalog), $max);
      }
  }

    /** Returns the number of categories for the accomplishments of a given catalog.
    @param $catalog The catalog to use.
    @return 3,5 or 10: The number of categories for the catalog.
    */
    private function getNumberOfCategories($catalog)
    {
        if ($catalog == "M") {
            return 3;
        } elseif  ($catalog == "Caldwell") {
            return 4;
        } elseif ($catalog == "H400" || $catalog == "HII" || $catalog == "H400-II") {
            return 5;
        }
        return 10;
    }

    /** Returns an boolean array with the accomplishments.
    @param $numberOfObjects The number of objects already seen and for which to calculate the accomplishments.
    @param $categories The number of categories for the accomplishments. this can be:
    + 3 : Typically Bronze, Silver or gold. Only for catalogs with 110 objects.
    + 5 : Typically Bronze, Silver, gold, diamond, platina. Only for catalogs with 400 objects.
    + default: 10 different accomplishments. Standard for 5000 observations. This can be overruled by using the $total parameter.
    @param $total The total number of observations to calculate the accomplishments.
    @return boolean[] An array with the accomplishments. The number of elements in the array depends on the $categories parameter.
    */
    private function ranking($numberOfObjects, $categories, $total = 5000)
    {
        if ($categories == 3) {
            return $this->accomplishments3($numberOfObjects);
        } elseif ($categories == 4) {
            return $this->accomplishments4($numberOfObjects);
        } elseif ($categories == 5) {
            return $this->accomplishments5($numberOfObjects);
        } else {
            return $this->accomplishments10($numberOfObjects, $total);
        }
    }

    /** Returns a boolean array with the accomplishments when there are 3 categories : [ bronze, silver, gold ]. This only works for catalogs with 110 objects.
    @param $numberOfObjects The number of objects seen or drawn to use to calculate the accomplishments
    @return boolean[] An array with the accomplishments: [ bronze, silver, gold ]
    */
    private function accomplishments3($numberOfObjects)
    {
        return array( $numberOfObjects >= 25 ? 1:0, $numberOfObjects >= 50 ? 1:0,
      $numberOfObjects >= 110 ? 1:0 );
    }

    /** Returns a boolean array with the accomplishments when there are 3 categories : [ bronze, silver, gold ]. This only works for catalogs with 109 objects.
    @param $numberOfObjects The number of objects seen or drawn to use to calculate the accomplishments
    @return boolean[] An array with the accomplishments: [ bronze, silver, gold ]
    */
    private function accomplishments4($numberOfObjects)
    {
        return array( $numberOfObjects >= 25 ? 1:0, $numberOfObjects >= 50 ? 1:0,
      $numberOfObjects >= 109 ? 1:0 );
    }

    /** Returns a boolean array with the accomplishments when there are 5 categories : [ bronze, silver, gold, diamond, platina ]. This only works for catalogs with 400 objects.
    @param $numberOfObjects The number of objects seen or drawn to use to calculate the accomplishments
    @return boolean[] An array with the accomplishments: [ bronze, silver, gold, diamond, platina ]
    */
    private function accomplishments5($numberOfObjects)
    {
        return array( $numberOfObjects >= 25 ? 1:0, $numberOfObjects >= 50 ? 1:0,
      $numberOfObjects >= 100 ? 1:0, $numberOfObjects >= 200 ? 1:0,
      $numberOfObjects >= 400 ? 1:0 );
    }

    /** Returns a boolean array with the accomplishments when there are 10 categories : [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ].
    @param $numberOfObjects The number of objects seen or drawn to use to calculate the accomplishments
    @return boolean[] An array with the accomplishments: [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
    */
    private function accomplishments10($numberOfObjects, $total)
    {
        $total1 = 1;
        $total10 = ($total / 500) >= 2 ? ($total / 500):2;
        $total25 = ($total / 200) >= 3 ? ($total / 200):3;
        $total50 = ($total / 100) >= 4 ? ($total / 100):4;
        $total100 = ($total / 50) >= 5 ? ($total / 50):5;
        $total250 = ($total / 20) >= 6 ? ($total / 20):6;
        $total500 = ($total / 10) >= 7 ? ($total / 10):7;
        $total1000 = ($total / 5) >= 8 ? ($total / 5):8;
        $total2500 = ($total / 2) >= 9 ? ($total / 2):9;
        $total5000 = $total >= 10 ? $total:10;
        return array( $numberOfObjects >= $total1 ? 1:0, $numberOfObjects >= $total10 ? 1:0,
      $numberOfObjects >= $total25 ? 1:0, $numberOfObjects >= $total50 ? 1:0,
      $numberOfObjects >= ($total / 50) ? 1:0, $numberOfObjects >= ($total / 20) ? 1:0,
      $numberOfObjects >= ($total / 10) ? 1:0, $numberOfObjects >= ($total / 5) ? 1:0,
      $numberOfObjects >= ($total / 2) ? 1:0, $numberOfObjects >= ($total) ? 1:0 );
    }

    /** Create an entry for a new observer in the accomplishments table.
    This method should be called whenever a new observer is created.

    @param $observerId The id of the new observer.
    */
    public function addObserver($observerId)
    {
        global $objDatabase;
        $sql = "INSERT INTO accomplishments (observer, messierBronze, messierSilver, messierGold, messierDrawingsBronze, messierDrawingsSilver, messierDrawingsGold, caldwellBronze, caldwellSilver, caldwellGold, caldwellDrawingsBronze, caldwellDrawingsSilver, caldwelldrawingsGold, herschelBronze, herschelSilver, herschelGold, herschelDiamond, herschelPlatina, herschelDrawingsBronze, herschelDrawingsSilver, herschelDrawingsGold, herschelDrawingsDiamond, herschelDrawingsPlatina, herschelIIBronze, herschelIISilver, herschelIIGold, herschelIIDiamond, herschelIIPlatina, herschelIIDrawingsBronze, herschelIIDrawingsSilver, herschelIIDrawingsGold, herschelIIDrawingsDiamond, herschelIIDrawingsPlatina, drawingsNewbie, drawingsRookie, drawingsBeginner, drawingsTalented, drawingsSkilled, drawingsIntermediate, drawingsExperienced, drawingsAdvanced, drawingsSenior, drawingsExpert, cometObservationsNewbie, cometObservationsRookie, cometObservationsBeginner, cometObservationsTalented, cometObservationsSkilled, cometObservationsIntermediate, cometObservationsExperienced, cometObservationsAdvanced, cometObservationsSenior, cometObservationsExpert, cometsObservedNewbie, cometsObservedRookie, cometsObservedBeginner, cometsObservedTalented, cometsObservedSkilled, cometsObservedIntermediate, cometsObservedExperienced, cometsObservedAdvanced, cometsObservedSenior, cometsObservedExpert, cometDrawingsNewbie, cometDrawingsRookie, cometDrawingsBeginner, cometDrawingsTalented, cometDrawingsSkilled, cometDrawingsIntermediate, cometDrawingsExperienced, cometDrawingsAdvanced, cometDrawingsSenior, cometDrawingsExpert, openClusterNewbie, openClusterRookie, openClusterBeginner, openClusterTalented, openClusterSkilled, openClusterIntermediate, openClusterExperienced, openClusterAdvanced, openClusterSenior, openClusterExpert, openClusterDrawingsNewbie, openClusterDrawingsRookie, openClusterDrawingsBeginner, openClusterDrawingsTalented, openClusterDrawingsSkilled, openClusterDrawingsIntermediate, openClusterDrawingsExperienced, openClusterDrawingsAdvanced, openClusterDrawingsSenior, openClusterDrawingsExpert, globularClusterNewbie, globularClusterRookie, globularClusterBeginner, globularClusterTalented, globularClusterSkilled, globularClusterIntermediate, globularClusterExperienced, globularClusterAdvanced, globularClusterSenior, globularClusterExpert, globularClusterDrawingsNewbie, globularClusterDrawingsRookie, globularClusterDrawingsBeginner, globularClusterDrawingsTalented, globularClusterDrawingsSkilled, globularClusterDrawingsIntermediate, globularClusterDrawingsExperienced, globularClusterDrawingsAdvanced, globularClusterDrawingsSenior, globularClusterDrawingsExpert, planetaryNebulaNewbie, planetaryNebulaRookie, planetaryNebulaBeginner, planetaryNebulaTalented, planetaryNebulaSkilled, planetaryNebulaIntermediate, planetaryNebulaExperienced, planetaryNebulaAdvanced, planetaryNebulaSenior, planetaryNebulaExpert, planetaryNebulaDrawingsNewbie, planetaryNebulaDrawingsRookie, planetaryNebulaDrawingsBeginner, planetaryNebulaDrawingsTalented, planetaryNebulaDrawingsSkilled, planetaryNebulaDrawingsIntermediate, planetaryNebulaDrawingsExperienced, planetaryNebulaDrawingsAdvanced, planetaryNebulaDrawingsSenior, planetaryNebulaDrawingsExpert, galaxyNewbie, galaxyRookie, galaxyBeginner, galaxyTalented, galaxySkilled, galaxyIntermediate, galaxyExperienced, galaxyAdvanced, galaxySenior, galaxyExpert, galaxyDrawingsNewbie, galaxyDrawingsRookie, galaxyDrawingsBeginner, galaxyDrawingsTalented, galaxyDrawingsSkilled, galaxyDrawingsIntermediate, galaxyDrawingsExperienced, galaxyDrawingsAdvanced, galaxyDrawingsSenior, galaxyDrawingsExpert, nebulaNewbie, nebulaRookie, nebulaBeginner, nebulaTalented, nebulaSkilled, nebulaIntermediate, nebulaExperienced, nebulaAdvanced, nebulaSenior, nebulaExpert, nebulaDrawingsNewbie, nebulaDrawingsRookie, nebulaDrawingsBeginner, nebulaDrawingsTalented, nebulaDrawingsSkilled, nebulaDrawingsIntermediate, nebulaDrawingsExperienced, nebulaDrawingsAdvanced, nebulaDrawingsSenior, nebulaDrawingsExpert, objectsNewbie, objectsRookie, objectsBeginner, objectsTalented, objectsSkilled, objectsIntermediate, objectsExperienced, objectsAdvanced, objectsSenior, objectsExpert, objectsDrawingsNewbie, objectsDrawingsRookie, objectsDrawingsBeginner, objectsDrawingsTalented, objectsDrawingsSkilled, objectsDrawingsIntermediate, objectsDrawingsExperienced, objectsDrawingsAdvanced, objectsDrawingsSenior, objectsDrawingsExpert) " .
      "VALUES (\"". $observerId ."\", 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
      0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
      0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
      0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
      0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
      0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
      0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);";
        $objDatabase->execSQL($sql);
    }

    /** Delete an entry for a deleted observer in the accomplishments table.
    All the accomplishments for the observer will be deleted.

    @param $observerId The id of the observer to delete.
    */
    public function deleteObserver($observerId)
    {
        global $objDatabase;
        $sql = "DELETE FROM accomplishments WHERE observer = \"". $observerId ."\");";
        $objDatabase->execSQL($sql);
    }

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

    /** Returns 1 if the observer has seen the correct number of objects.

    @param $observerId The observer for which the accomplishments should be returned from the database.
    @param $catalog The catalog for which the accomplishments should be calculated.
    @param $drawing If true, the drawing accomplishments should be calculated.
    @return integer[] [ bronze, silver, gold, ... ]
    */
    private function getAccomplishments($observerId, $catalog, $drawing)
    {
        global $objDatabase;
        if ($catalog == "messier" || $catalog == "Caldwell") {
            if ($drawing) {
                $catalog .= "Drawings";
            }
            $recordArray = $objDatabase->selectRecordsetArray("select " . $catalog . "Bronze as '0', " . $catalog ."Silver as '1', " . $catalog . "Gold as '2' from accomplishments where observer = \"". $observerId . "\";");
        } elseif ($catalog == "Herschel" || $catalog == "HerschelII") {
            if ($drawing) {
                $catalog .= "Drawings";
            }
            $recordArray = $objDatabase->selectRecordsetArray("select " . $catalog . "Bronze as '0', " . $catalog ."Silver as '1', " . $catalog . "Gold as '2', " . $catalog . "Diamond as '3', " . $catalog . "Platina as '4' from accomplishments where observer = \"". $observerId . "\";");
        } else {
            if ($drawing) {
                $catalog .= "Drawings";
            }
            $recordArray = $objDatabase->selectRecordsetArray("select " . $catalog . "Newbie as '0', " . $catalog . "Rookie as '1', " . $catalog . "Beginner as '2', " . $catalog . "Talented as '3', " . $catalog . "Skilled as '4', " . $catalog . "Intermediate as '5', " . $catalog . "Experienced as '6', " . $catalog . "Advanced as '7', " . $catalog . "Senior as '8', " . $catalog . "Expert as '9' from accomplishments where observer = \"". $observerId . "\";");
        }
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

    /** Recalculates all deepsky accomplishments (for example after adding, removing or changing an observation)
      @param $observerId The observer for which all deepsky accomplishments should be recalculated.
    */
    public function recalculateDeepsky($observerId)
    {
        $this->recalculateDeepskyObjectsSeen($observerId, "messier", false);
        $this->recalculateDeepskyObjectsSeen($observerId, "messier", true);
        $this->recalculateDeepskyObjectsSeen($observerId, "Caldwell", false);
        $this->recalculateDeepskyObjectsSeen($observerId, "Caldwell", true);
        $this->recalculateDeepskyObjectsSeen($observerId, "Herschel", false);
        $this->recalculateDeepskyObjectsSeen($observerId, "Herschel", true);
        $this->recalculateDeepskyObjectsSeen($observerId, "HerschelII", false);
        $this->recalculateDeepskyObjectsSeen($observerId, "HerschelII", true);
        $this->recalculateDeepskyObjectsSeen($observerId, "drawings", false);
        $this->recalculateDeepskyObjectsSeen($observerId, "OpenCluster", false);
        $this->recalculateDeepskyObjectsSeen($observerId, "OpenCluster", true);
        $this->recalculateDeepskyObjectsSeen($observerId, "GlobularCluster", false);
        $this->recalculateDeepskyObjectsSeen($observerId, "GlobularCluster", true);
        $this->recalculateDeepskyObjectsSeen($observerId, "PlanetaryNebula", false);
        $this->recalculateDeepskyObjectsSeen($observerId, "PlanetaryNebula", true);
        $this->recalculateDeepskyObjectsSeen($observerId, "Galaxy", false);
        $this->recalculateDeepskyObjectsSeen($observerId, "Galaxy", true);
        $this->recalculateDeepskyObjectsSeen($observerId, "Nebula", false);
        $this->recalculateDeepskyObjectsSeen($observerId, "Nebula", true);
        $this->recalculateDeepskyObjectsSeen($observerId, "objects", false);
        $this->recalculateDeepskyObjectsSeen($observerId, "Objects", true);
    }

    /** Recalculates all comet accomplishments (for example after adding, removing or changing an observation)
      @param $observerId The observer for which all comet accomplishments should be recalculated.
    */
    public function recalculateComets($observerId)
    {
        $this->recalculateCometObservations($observerId);
        $this->recalculateCometsObserved($observerId);
        $this->recalculateCometDrawings($observerId);
    }

    /** Gets the subject for the message when a new accomplishment is earned (because of extra objects seen)
      @param $catalog The catalog for which the subject should be returned.
      @param $numberOfObjects The number of objects seen from the given catalog.
      @return The subject for the message.
    */
    public function getSeenSubject($catalog, $numberOfObjects)
    {
        return sprintf(
            _('New DeepskyLog star! %s observed!'),
            round($numberOfObjects) . ' ' . $catalog
        );
    }

    /** Gets the body for the message when a new accomplishment is earned (because of extra objects seen)
      @param $catalog The catalog for which the subject should be returned.
      @param $numberOfObjects The number of objects seen from the given catalog.
      @return The body for the message.
    */
    public function getSeenMessage($catalog, $numberOfObjects, $observerId)
    {
        global $baseURL;
        return sprintf(
            _('Congratulations! You have observed %s and receive a DeepskyLog star! Check out your DeepskyLog stars at %s'),
            round($numberOfObjects) . " " . $catalog,
            $baseURL . "/index.php?indexAction=detail_observer&user=" . urlencode($observerId) . ""
        );
    }

    /** Gets the subject for the message when a new accomplishment is earned (because of extra objects drawn)
      @param $catalog The catalog for which the subject should be returned.
      @param $numberOfObjects The number of objects drawn from the given catalog.
      @return The subject for the message.
    */
    public function getDrawSubject($catalog, $numberOfObjects)
    {
        return sprintf(
            _('New DeepskyLog star! %s drawn!'),
            round($numberOfObjects) . ' ' . $catalog
        );
    }

    /** Gets the body for the message when a new accomplishment is earned (because of extra objects drawn)
      @param $catalog The catalog for which the subject should be returned.
      @param $numberOfObjects The number of objects drawn from the given catalog.
      @return The body for the message.
    */
    public function getDrawMessage($catalog, $numberOfObjects, $observerId)
    {
        global $baseURL;
        return sprintf(
            _('Congratulations! You have drawn %s and receive a DeepskyLog star! Check out your DeepskyLog stars at %s'),
            round($numberOfObjects) . " " . $catalog,
            " " . $baseURL . "/index.php?indexAction=detail_observer&user="
            . urlencode($observerId) . ""
        );
    }

    /** Recalculates the number of objects seen or drawn and send a mail when a new accomplishment is reached.
      @param $observerId The observer for which we want to recalculate the number of objects seen or drawn.
      @param $catalog The catalog we want to use.
      @param $drawing True if we want to do the calculations for the drawings.
    */
    private function recalculateDeepskyObjectsSeen($observerId, $catalog, $drawing)
    {
        global $objDatabase, $objMessages, $loggedUser;
      // Calculate the accomplishments for the given catalog, the number of accomplishments and if the observer has seen or drawn the object.
      $objectsObserved = $this->calculateAccomplishments($observerId, $catalog, $drawing, $this->getNumberOfObjectsInCatalog($catalog));
        $objectsObservedFromDatabase = $this->getAccomplishments($observerId, $catalog, $drawing);
      // Get the correct type of the accomplishments
      $accomplishmentType = $this->getAccomplishmentType(sizeof($objectsObserved));

        $numberSeen = $this->getNumberOfObjects(sizeof($objectsObserved), $this->getNumberOfObjectsInCatalog($catalog));

      // Get the correct string from the translation files
      $objectText = $this->getCorrectLanguageFile($catalog, $numberSeen);
        if ($drawing) {
            $catalog .= "Drawings";
        }

        for ($cnt=0;$cnt < sizeof($objectsObserved);$cnt++) {
            $sql = "UPDATE accomplishments SET " . $catalog . $accomplishmentType[$cnt] . " = " . $objectsObserved[$cnt] . " WHERE observer = \"". $observerId ."\";";

            $objDatabase->execSQL($sql);

            if ($objectsObservedFromDatabase[$cnt] == 0 && $objectsObserved[$cnt] == 1) {
                if ($drawing) {
                    $subject = $this->getDrawSubject($objectText, $numberSeen[$cnt]);
                    $body = $this->getDrawMessage($objectText, $numberSeen[$cnt], $observerId);
                } else {
                    $subject = $this->getSeenSubject($objectText, $numberSeen[$cnt]);
                    $body = $this->getSeenMessage($objectText, $numberSeen[$cnt], $observerId);
                }
                $objMessages->sendMessage('DeepskyLog', $loggedUser, $subject, $body);
            }
        }
    }

    /** Returns the number of objects in a catalog.
    @param $catalog The catalog to use.
    @return Then number of objects in the catalog. For messier, caldwell, H400, HII, and the catalog with enough objects, 5000 is returned.
    */
    private function getNumberOfObjectsInCatalog($catalog)
    {
        if ($catalog == "Nebula") {
            return 384;
        } elseif ($catalog == "PlanetaryNebula") {
            return 1023;
        } elseif ($catalog == "GlobularCluster") {
            return 152;
        } elseif ($catalog == "OpenCluster") {
            return 1700;
        } else {
            return 5000;
        }
    }

    /** Returns the correct text from the language file to describe the catalog.
    @param $catalog The catalog to use.
    @param $numberOfObjects The number of observations for this catalog. Needed to return the plural if needed.
    @return The correct text to use in the getDrawMessage, getDrawSubject, getSeenMessage and getSeenSubject methods
    */
    private function getCorrectLanguageFile($catalog, $numberOfObjects)
    {
        if ($numberOfObjects > 1) {
            if ($catalog == "messier") {
                return _('Messier objects');
            } elseif ($catalog == "Caldwell") {
                return _('Caldwell objects');
            } elseif ($catalog == "Herschel") {
                return _('Herschel 400 objects');
            } elseif ($catalog == "HerschelII") {
                return _('Herschel II objects');
            } elseif ($catalog == "OpenCluster") {
                return strtolower(_('Open clusters'));
            } elseif ($catalog == "GlobularCluster") {
                return strtolower(_('Globular clusters'));
            } elseif ($catalog == "PlanetaryNebula") {
                return strtolower(_('Planetary Nebulae'));
            } elseif ($catalog == "Galaxy") {
                return strtolower(_('Galaxies'));
            } elseif ($catalog == "Nebula") {
                return strtolower(_('Nebulae'));
            } elseif ($catalog == "Objects") {
                return _('object');
            } elseif ($catalog == "Drawings") {
                return _('objects');
            }
        } else {
            if ($catalog == "OpenCluster") {
                return _('open cluster');
            } elseif ($catalog == "GlobularCluster") {
                return _('globular cluster');
            } elseif ($catalog == "PlanetaryNebula") {
                return _('planetary nebula');
            } elseif ($catalog == "Galaxy") {
                return _('galaxy');
            } elseif ($catalog == "Nebula") {
                return _('nebula');
            } elseif ($catalog == "Objects") {
                return _('object');
            } elseif ($catalog == "Drawings") {
                return _('objects');
            }
        }
    }

    /** Returns the name of the accomplishments.
    @param $numberOfAccomplishments The number of accomplishments: 3, 5 or 10
    @return The accomplishments: [Bronze, Silver, Gold, ...]
    */
    private function getAccomplishmentType($numberOfAccomplishments)
    {
        if ($numberOfAccomplishments == 3) {
            return [ "Bronze", "Silver", "Gold" ];
        } elseif ($numberOfAccomplishments == 5) {
            return [ "Bronze", "Silver", "Gold", "Diamond", "Platina" ];
        } else {
            return [ "Newbie", "Rookie", "Beginner", "Talented", "Skilled", "Intermediate", "Experienced", "Advanced", "Senior", "Expert" ];
        }
    }

    /** Returns the number of objects needed to receive an accomplishments.
    @param $numberOfAccomplishments The number of accomplishments: 3, 5 or 10
    @param $total The number of objects in the catalog.
    @return The number of objects needed to receive an accomplishment: [1, 2, 3, ...]
    */
    private function getNumberOfObjects($numberOfAccomplishments, $total = 5000)
    {
        if ($numberOfAccomplishments == 3) {
            return [ 25, 50, 110 ];
        } elseif ($numberOfAccomplishments == 5) {
            return [ 25, 50, 100, 200, 400 ];
        } else {
            return [ 1, ($total / 500) >= 2 ? ($total / 500):2, ($total / 200) >= 3 ? ($total / 200):3,
                 ($total / 100) >= 4 ? ($total / 100):4, ($total / 50) >= 5 ? ($total / 50):5, ($total / 20) >= 6 ? ($total / 20):6,
                 ($total / 10) >= 7 ? ($total / 10):7, ($total / 5) >= 8 ? ($total / 5):8, ($total / 2) >= 9 ? ($total / 2):9, $total >= 10 ? $total:10];
        }
    }





    public function recalculateCometObservations($observerId)
    {
        global $objDatabase, $objMessages, $loggedUser;
      // Objects
      $CometObservations = $this->calculateAccomplishments($observerId, "cometObservations", false);
        $oldCometObservations = $this->getCometObservationsAccomplishments($observerId);

        $sql = "UPDATE accomplishments SET CometObservationsNewbie = " . $CometObservations[0] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometObservationsRookie = " . $CometObservations[1] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometObservationsBeginner = " . $CometObservations[2] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometObservationsTalented = " . $CometObservations[3] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometObservationsSkilled = " . $CometObservations[4] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometObservationsIntermediate = " . $CometObservations[5] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometObservationsExperienced = " . $CometObservations[6] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometObservationsAdvanced = " . $CometObservations[7] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometObservationsSenior = " . $CometObservations[8] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometObservationsExpert = " . $CometObservations[9] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        if ($oldCometObservations[0] == 0 && $CometObservations[0] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 1), $this->getSeenMessage(_('object'), 1, $observerId));
        }

        if ($oldCometObservations[1] == 0 && $CometObservations[1] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 10), $this->getSeenMessage(_('object'), 10, $observerId));
        }

        if ($oldCometObservations[2] == 0 && $CometObservations[2] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 25), $this->getSeenMessage(_('object'), 25, $observerId));
        }

        if ($oldCometObservations[3] == 0 && $CometObservations[3] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 50), $this->getSeenMessage(_('object'), 50, $observerId));
        }

        if ($oldCometObservations[4] == 0 && $CometObservations[4] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 100), $this->getSeenMessage(_('object'), 100, $observerId));
        }

        if ($oldCometObservations[5] == 0 && $CometObservations[5] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 250), $this->getSeenMessage(_('object'), 250, $observerId));
        }

        if ($oldCometObservations[6] == 0 && $CometObservations[6] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 500), $this->getSeenMessage(_('object'), 500, $observerId));
        }

        if ($oldCometObservations[7] == 0 && $CometObservations[7] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 1000), $this->getSeenMessage(_('object'), 1000, $observerId));
        }

        if ($oldCometObservations[8] == 0 && $CometObservations[8] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 2500), $this->getSeenMessage(_('object'), 2500, $observerId));
        }

        if ($oldCometObservations[9] == 0 && $CometObservations[9] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 5000), $this->getSeenMessage(_('object'), 5000, $observerId));
        }
    }

    public function recalculateCometsObserved($observerId)
    {
        global $objDatabase, $objMessages, $loggedUser;
      // Objects
      $CometsObserved = $this->calculateAccomplishments($observerId, "cometsObserved", false);
        $oldCometsObserved = $this->getCometsObservedAccomplishments($observerId);

        $sql = "UPDATE accomplishments SET CometsObservedNewbie = " . $CometsObserved[0] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometsObservedRookie = " . $CometsObserved[1] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometsObservedBeginner = " . $CometsObserved[2] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometsObservedTalented = " . $CometsObserved[3] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometsObservedSkilled = " . $CometsObserved[4] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometsObservedIntermediate = " . $CometsObserved[5] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometsObservedExperienced = " . $CometsObserved[6] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometsObservedAdvanced = " . $CometsObserved[7] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometsObservedSenior = " . $CometsObserved[8] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometsObservedExpert = " . $CometsObserved[9] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        if ($oldCometsObserved[0] == 0 && $CometsObserved[0] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 1), $this->getSeenMessage(_('object'), 1, $observerId));
        }

        if ($oldCometsObserved[1] == 0 && $CometsObserved[1] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 10), $this->getSeenMessage(_('object'), 10, $observerId));
        }

        if ($oldCometsObserved[2] == 0 && $CometsObserved[2] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 25), $this->getSeenMessage(_('object'), 25, $observerId));
        }

        if ($oldCometsObserved[3] == 0 && $CometsObserved[3] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 50), $this->getSeenMessage(_('object'), 50, $observerId));
        }

        if ($oldCometsObserved[4] == 0 && $CometsObserved[4] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 100), $this->getSeenMessage(_('object'), 100, $observerId));
        }

        if ($oldCometsObserved[5] == 0 && $CometsObserved[5] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 250), $this->getSeenMessage(_('object'), 250, $observerId));
        }

        if ($oldCometsObserved[6] == 0 && $CometsObserved[6] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 500), $this->getSeenMessage(_('object'), 500, $observerId));
        }

        if ($oldCometsObserved[7] == 0 && $CometsObserved[7] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 1000), $this->getSeenMessage(_('object'), 1000, $observerId));
        }

        if ($oldCometsObserved[8] == 0 && $CometsObserved[8] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 2500), $this->getSeenMessage(_('object'), 2500, $observerId));
        }

        if ($oldCometsObserved[9] == 0 && $CometsObserved[9] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 5000), $this->getSeenMessage(_('object'), 5000, $observerId));
        }
    }

    public function recalculateCometDrawings($observerId)
    {
        global $objDatabase, $objMessages, $loggedUser;
      // Objects
      $CometDrawings = $this->calculateAccomplishments($observerId, "cometDrawings", false);
        $oldCometDrawings = $this->getCometDrawingsAccomplishments($observerId);

        $sql = "UPDATE accomplishments SET CometDrawingsNewbie = " . $CometDrawings[0] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometDrawingsRookie = " . $CometDrawings[1] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometDrawingsBeginner = " . $CometDrawings[2] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometDrawingsTalented = " . $CometDrawings[3] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometDrawingsSkilled = " . $CometDrawings[4] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometDrawingsIntermediate = " . $CometDrawings[5] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometDrawingsExperienced = " . $CometDrawings[6] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometDrawingsAdvanced = " . $CometDrawings[7] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometDrawingsSenior = " . $CometDrawings[8] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        $sql = "UPDATE accomplishments SET CometDrawingsExpert = " . $CometDrawings[9] . " WHERE observer = \"". $observerId ."\";";
        $objDatabase->execSQL($sql);

        if ($oldCometDrawings[0] == 0 && $CometDrawings[0] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 1), $this->getSeenMessage(_('object'), 1, $observerId));
        }

        if ($oldCometDrawings[1] == 0 && $CometDrawings[1] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 10), $this->getSeenMessage(_('object'), 10, $observerId));
        }

        if ($oldCometDrawings[2] == 0 && $CometDrawings[2] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 25), $this->getSeenMessage(_('object'), 25, $observerId));
        }

        if ($oldCometDrawings[3] == 0 && $CometDrawings[3] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 50), $this->getSeenMessage(_('object'), 50, $observerId));
        }

        if ($oldCometDrawings[4] == 0 && $CometDrawings[4] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 100), $this->getSeenMessage(_('object'), 100, $observerId));
        }

        if ($oldCometDrawings[5] == 0 && $CometDrawings[5] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 250), $this->getSeenMessage(_('object'), 250, $observerId));
        }

        if ($oldCometDrawings[6] == 0 && $CometDrawings[6] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 500), $this->getSeenMessage(_('object'), 500, $observerId));
        }

        if ($oldCometDrawings[7] == 0 && $CometDrawings[7] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 1000), $this->getSeenMessage(_('object'), 1000, $observerId));
        }

        if ($oldCometDrawings[8] == 0 && $CometDrawings[8] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 2500), $this->getSeenMessage(_('object'), 2500, $observerId));
        }

        if ($oldCometDrawings[9] == 0 && $CometDrawings[9] == 1) {
            $objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(_('object'), 5000), $this->getSeenMessage(_('object'), 5000, $observerId));
        }
    }
}
