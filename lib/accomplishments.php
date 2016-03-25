<?php
// accomplishments.php
global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
require_once "observations.php";

/**
Collects all functions needed to calculate and retrieve the accomplishments of an observer.
*/
class Accomplishments {
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
   @param $ranking The number of categories in the result.
   @param $drawings True if the drawings should be calculated.
   @param $max The maximum number of elements to take into account.
   @return integer[] [ bronze, silver, gold ]
   */
  public function calculateAccomplishments($observer, $catalog, $ranking, $drawings = false, $max = 0)
  { global $objObservation, $objObserver, $objCometObservation, $objDatabase;
    $objObservation = new Observations();

    $extra = "";
    if ($drawings) {
      $extra = " and observations.hasDrawing = 1";
    }

    switch($catalog) {
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
      case "openClusters":
          $total = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"OPNCL\" and observations.observerid = \"" . $observer . "\"" . $extra));
          $total += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"CLANB\" and observations.observerid = \"" . $observer . "\"" . $extra));
          break;
      case "globularClusters":
        $total = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"GLOCL\" and observations.observerid = \"" . $observer . "\"" . $extra));
        break;
      case "planetaryNebulae":
        $total = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"PLNNB\" and observations.observerid = \"" . $observer . "\"" . $extra));
        break;
      case "galaxies":
        $total = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"GALXY\" and observations.observerid = \"" . $observer . "\"" . $extra));
        break;
      case "nebulae":
        $total = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"EMINB\" and observations.observerid = \"" . $observer . "\"" . $extra));
        $total += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"ENRNN\" and observations.observerid = \"" . $observer . "\"" . $extra));
        $total += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"ENSTR\" and observations.observerid = \"" . $observer . "\"" . $extra));
        $total += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"REFNB\" and observations.observerid = \"" . $observer . "\"" . $extra));
        $total += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"RNHII\" and observations.observerid = \"" . $observer . "\"" . $extra));
        $total += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"HII\" and observations.observerid = \"" . $observer . "\"" . $extra));
        $total += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"SNREM\" and observations.observerid = \"" . $observer . "\"" . $extra));
        $total += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"WRNEB\" and observations.observerid = \"" . $observer . "\"" . $extra));
        break;
      case "differentObjects":
        if ($drawings) {
          $total = $objObservation->getNumberOfObjectDrawings($observer);
        } else {
          $total = $objObservation->getNumberOfObjects($observer);
        }
        break;
      default:
        if ($drawings) {
          $total = $objObservation->getDrawingsCountFromCatalog($observer,$catalog);
        } else {
          $total = $objObservation->getObservedCountFromCatalogOrList($observer,$catalog);
        }
        break;
    }
    if ($max > 0) {
      return $this->ranking($total, $ranking, $max);

    } else {
      return $this->ranking($total, $ranking);
    }
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
  private function ranking($numberOfObjects, $categories, $total = 5000) {
  	if ($categories == 3) {
  		return $this->accomplishments3($numberOfObjects);
  	} else if ($categories == 5) {
  		return $this->accomplishments5($numberOfObjects);
  	} else {
  		return $this->accomplishments10($numberOfObjects, $total);
  	}
  }

  /** Returns a boolean array with the accomplishments when there are 3 categories : [ bronze, silver, gold ]. This only works for catalogs with 110 objects.
    @param $numberOfObjects The number of objects seen or drawn to use to calculate the accomplishments
    @return boolean[] An array with the accomplishments: [ bronze, silver, gold ]
  */
  private function accomplishments3($numberOfObjects) {
    return array( $numberOfObjects >= 25 ? 1:0, $numberOfObjects >= 50 ? 1:0,
             $numberOfObjects >= 110 ? 1:0 );
  }

  /** Returns a boolean array with the accomplishments when there are 5 categories : [ bronze, silver, gold, diamond, platina ]. This only works for catalogs with 400 objects.
    @param $numberOfObjects The number of objects seen or drawn to use to calculate the accomplishments
    @return boolean[] An array with the accomplishments: [ bronze, silver, gold, diamond, platina ]
  */
  private function accomplishments5($numberOfObjects) {
    return array( $numberOfObjects >= 25 ? 1:0, $numberOfObjects >= 50 ? 1:0,
             $numberOfObjects >= 100 ? 1:0, $numberOfObjects >= 200 ? 1:0,
             $numberOfObjects >= 400 ? 1:0 );
  }

  /** Returns a boolean array with the accomplishments when there are 10 categories : [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ].
    @param $numberOfObjects The number of objects seen or drawn to use to calculate the accomplishments
    @return boolean[] An array with the accomplishments: [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  */
  private function accomplishments10($numberOfObjects, $total) {
  	$total1 = 1;
  	$total10 = ($total / 500) >= 2 ? ($total / 500):2;
  	$total25 = ($total / 200) >= 3 ? ($total / 200):3;
  	$total50 = ($total / 100) >= 4 ? ($total / 100):4;
  	$total100 = ($total / 50) >= 5 ? ($total / 50):5;
  	$total250 = ($total / 20) >= 6 ? ($total / 20):6;
  	$total500 = ($total / 10) >= 7 ? ($total / 10):7;
  	$total1000 = ($total / 5) >= 8 ? ($total / 5):8;
  	$total2500 = ($total / 2) >= 9 ? ($total / 2):9;
  	$total5000 = $total >= 4 ? $total:4;
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
  public function addObserver($observerId) {
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
  public function deleteObserver($observerId) {
  	global $objDatabase;
  	$sql = "DELETE FROM accomplishments WHERE observer = \"". $observerId ."\");";
  	$objDatabase->execSQL($sql);
  }

  /** Returns 1 if the observer has an accomplishment.

    @param $observerId The observer for which the accomplishments should be returned from the database.
    @return integer[] [ messierBronze, messierSilver, messierGold, ... ]
  */
  public function getAccomplishments($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select * from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }

  /** Returns 1 if the observer has seen 25, 50 or 110 messiers.

    @param $observerId The observer for which the messier accomplishments should be returned from the database.
    @return integer[] [ bronze, silver, gold ]
  */
  public function getMessierAccomplishments($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select messierBronze as '0', messierSilver as '1', messierGold as '2' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }

  /** Returns 1 if the drawn has seen 25, 50 or 110 messiers.

    @param $observerId The observer for which the messier accomplishments should be returned from the database.
    @return integer[] [ bronze, silver, gold ]
  */
  public function getMessierAccomplishmentsDrawings($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select messierDrawingsBronze as '0', messierDrawingsSilver as '1', messierDrawingsGold as '2' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }

  /** Returns 1 if the observer has seen 25, 50 or 110 Caldwell objects.

    @param $observerId The observer for which the caldwell accomplishments should be returned from the database.
    @return integer[] [ bronze, silver, gold ]
  */
  public function getCaldwellAccomplishments($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CaldwellBronze as '0', CaldwellSilver as '1', CaldwellGold as '2' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }

  /** Returns 1 if the observer has drawn 25, 50 or 110 Caldwell objects.

    @param $observerId The observer for which the caldwell accomplishments should be returned from the database.
    @return integer[] [ bronze, silver, gold ]
  */
  public function getCaldwellAccomplishmentsDrawings($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CaldwellDrawingsBronze as '0', CaldwellDrawingsSilver as '1', CaldwellDrawingsGold as '2' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }

  /** Returns 1 if the observer has seen 25, 50, 100, 200 or 400 Herschel 400 objects.

    @param $observerId The observer for which the Herschel 400 accomplishments should be returned from the database.
    @return integer[] [ bronze, silver, gold, diamond, platina ]
  */
  public function getHerschelAccomplishments($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelBronze as '0', HerschelSilver as '1', HerschelGold as '2', HerschelDiamond as '3', HerschelPlatina as '4' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }

  /** Returns 1 if the observer has drawn 25, 50, 100, 200 or 400 Herschel 400 objects.

    @param $observerId The observer for which the Herschel 400 accomplishments should be returned from the database.
    @return integer[] [ bronze, silver, gold, diamond, platina ]
  */
  public function getHerschelAccomplishmentsDrawings($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelDrawingsBronze as '0', HerschelDrawingsSilver as '1', HerschelDrawingsGold as '2', HerschelDrawingsDiamond as '3', HerschelDrawingsPlatina as '4' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }

  /** Returns 1 if the observer has seen 25, 50, 100, 200 or 400 Herschel-II objects.

    @param $observerId The observer for which the Herschel-II accomplishments should be returned from the database.
    @return integer[] [ bronze, silver, gold, diamond, platina ]
  */
  public function getHerschelIIAccomplishments($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelIIBronze as '0', HerschelIISilver as '1', HerschelIIGold as '2', HerschelIIDiamond as '3', HerschelIIPlatina as '4' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }

  /** Returns 1 if the observer has drawn 25, 50, 100, 200 or 400 Herschel-II objects.

    @param $observerId The observer for which the Herschel-II accomplishments should be returned from the database.
    @return integer[] [ bronze, silver, gold, diamond, platina ]
  */
  public function getHerschelIIAccomplishmentsDrawings($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelIIDrawingsBronze as '0', HerschelIIDrawingsSilver as '1', HerschelIIDrawingsGold as '2', HerschelIIDrawingsDiamond as '3', HerschelIIDrawingsPlatina as '4' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }

  /** Returns 1 if the observer has drawn 1, 10, 25, 50, 100, 250, 500, 1000, 2500 or 5000 objects.

    @param $observerId The observer for which the different drawings should be returned from the database.
    @return integer[] [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  */
  public function getDrawingsAccomplishments($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select DrawingsNewbie as '0', DrawingsRookie as '1', DrawingsBeginner as '2', DrawingsTalented as '3', DrawingsSkilled as '4', DrawingsIntermediate as '5', DrawingsExperienced as '6', DrawingsAdvanced as '7', DrawingsSenior as '8', DrawingsExpert as '9' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }

  /** Returns 1 if the observer has seen 1, 10, 25, 50, 100, 250, 500, 1000, 2500 or 5000 open clusters.

    @param $observerId The observer for which the observed open clusters should be returned from the database.
    @return integer[] [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  */
  public function getOpenClustersAccomplishments($observerId) {
  	global $objDatabase;
    $recordArray = $objDatabase->selectRecordsetArray("select OpenClusterNewbie as '0', OpenClusterRookie as '1', OpenClusterBeginner as '2', OpenClusterTalented as '3', OpenClusterSkilled as '4', OpenClusterIntermediate as '5', OpenClusterExperienced as '6', OpenClusterAdvanced as '7', OpenClusterSenior as '8', OpenClusterExpert as '9' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }

  /** Returns 1 if the observer has drawn 1, 10, 25, 50, 100, 250, 500, 1000, 2500 or 5000 open clusters.

    @param $observerId The observer for which the drawn open clusters should be returned from the database.
    @return integer[] [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  */
  public function getOpenClustersAccomplishmentsDrawings($observerId) {
  	global $objDatabase;
    $recordArray = $objDatabase->selectRecordsetArray("select OpenClusterDrawingsNewbie as '0', OpenClusterDrawingsRookie as '1', OpenClusterDrawingsBeginner as '2', OpenClusterDrawingsTalented as '3', OpenClusterDrawingsSkilled as '4', OpenClusterDrawingsIntermediate as '5', OpenClusterDrawingsExperienced as '6', OpenClusterDrawingsAdvanced as '7', OpenClusterDrawingsSenior as '8', OpenClusterDrawingsExpert as '9' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }

  /** Returns 1 if the observer has seen 1, 10, 25, 50, 100, 250, 500, 1000, 2500 or 5000 globular clusters.

    @param $observerId The observer for which the observed globular clusters should be returned from the database.
    @return integer[] [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  */
  public function getGlobularClustersAccomplishments($observerId) {
  	global $objDatabase;
    $recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterNewbie as '0', GlobularClusterRookie as '1', GlobularClusterBeginner as '2', GlobularClusterTalented as '3', GlobularClusterSkilled as '4', GlobularClusterIntermediate as '5', GlobularClusterExperienced as '6', GlobularClusterAdvanced as '7', GlobularClusterSenior as '8', GlobularClusterExpert as '9' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }

  /** Returns 1 if the observer has drawn 1, 10, 25, 50, 100, 250, 500, 1000, 2500 or 5000 globular clusters.

    @param $observerId The observer for which the drawn globular clusters should be returned from the database.
    @return integer[] [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  */
  public function getGlobularClustersAccomplishmentsDrawings($observerId) {
  	global $objDatabase;
    $recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterDrawingsNewbie as '0', GlobularClusterDrawingsRookie as '1', GlobularClusterDrawingsBeginner as '2', GlobularClusterDrawingsTalented as '3', GlobularClusterDrawingsSkilled as '4', GlobularClusterDrawingsIntermediate as '5', GlobularClusterDrawingsExperienced as '6', GlobularClusterDrawingsAdvanced as '7', GlobularClusterDrawingsSenior as '8', GlobularClusterDrawingsExpert as '9' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }

  /** Returns 1 if the observer has seen 1, 10, 25, 50, 100, 250, 500, 1000, 2500 or 5000 planetary nebulae.

    @param $observerId The observer for which the observed planetary nebulae should be returned from the database.
    @return integer[] [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  */
  public function getPlanetaryNebulaeAccomplishments($observerId) {
  	global $objDatabase;
    $recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaNewbie as '0', PlanetaryNebulaRookie as '1', PlanetaryNebulaBeginner as '2', PlanetaryNebulaTalented as '3', PlanetaryNebulaSkilled as '4', PlanetaryNebulaIntermediate as '5', PlanetaryNebulaExperienced as '6', PlanetaryNebulaAdvanced as '7', PlanetaryNebulaSenior as '8', PlanetaryNebulaExpert as '9' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }

  /** Returns 1 if the observer has drawn 1, 10, 25, 50, 100, 250, 500, 1000, 2500 or 5000 planetary nebulae.

    @param $observerId The observer for which the drawn planetary nebulae should be returned from the database.
    @return integer[] [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  */
  public function getPlanetaryNebulaeAccomplishmentsDrawings($observerId) {
  	global $objDatabase;
    $recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaDrawingsNewbie as '0', PlanetaryNebulaDrawingsRookie as '1', PlanetaryNebulaDrawingsBeginner as '2', PlanetaryNebulaDrawingsTalented as '3', PlanetaryNebulaDrawingsSkilled as '4', PlanetaryNebulaDrawingsIntermediate as '5', PlanetaryNebulaDrawingsExperienced as '6', PlanetaryNebulaDrawingsAdvanced as '7', PlanetaryNebulaDrawingsSenior as '8', PlanetaryNebulaDrawingsExpert as '9' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }



  /** Returns 1 if the observer has seen 1, 10, 25, 50, 100, 250, 500, 1000, 2500 or 5000 galaxies.

    @param $observerId The observer for which the observed galaxies should be returned from the database.
    @return integer[] [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  */
  public function getGalaxiesAccomplishments($observerId) {
  	global $objDatabase;
    $recordArray = $objDatabase->selectRecordsetArray("select GalaxyNewbie as '0', GalaxyRookie as '1', GalaxyBeginner as '2', GalaxyTalented as '3', GalaxySkilled as '4', GalaxyIntermediate as '5', GalaxyExperienced as '6', GalaxyAdvanced as '7', GalaxySenior as '8', GalaxyExpert as '9' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }

  /** Returns 1 if the observer has drawn 1, 10, 25, 50, 100, 250, 500, 1000, 2500 or 5000 galaxies.

    @param $observerId The observer for which the drawn galaxies should be returned from the database.
    @return integer[] [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  */
  public function getGalaxiesAccomplishmentsDrawings($observerId) {
  	global $objDatabase;
    $recordArray = $objDatabase->selectRecordsetArray("select GalaxyDrawingsNewbie as '0', GalaxyDrawingsRookie as '1', GalaxyDrawingsBeginner as '2', GalaxyDrawingsTalented as '3', GalaxyDrawingsSkilled as '4', GalaxyDrawingsIntermediate as '5', GalaxyDrawingsExperienced as '6', GalaxyDrawingsAdvanced as '7', GalaxyDrawingsSenior as '8', GalaxyDrawingsExpert as '9' from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0];
  }


  // TODO: Start writing phpdoc for the next methods.
  // TODO: Refactor getXxxxxxNewbie, ...

  // Returns 1 if the observer has one nebula
  public function getNebulaNewbie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaNewbie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaNewbie"];
  }

  // Returns 1 if the observer has 10 Nebula
  public function getNebulaRookie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaRookie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaRookie"];
  }

  // Returns 1 if the observer has 25 Nebula
  public function getNebulaBeginner($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaBeginner from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaBeginner"];
  }

  // Returns 1 if the observer has 50 Nebula
  public function getNebulaTalented($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaTalented from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaTalented"];
  }

  // Returns 1 if the observer has 100 Nebula
  public function getNebulaSkilled($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaSkilled from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaSkilled"];
  }

  // Returns 1 if the observer has 250 Nebula
  public function getNebulaIntermediate($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaIntermediate from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaIntermediate"];
  }

  // Returns 1 if the observer has 500 Nebula
  public function getNebulaExperienced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaExperienced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaExperienced"];
  }

  // Returns 1 if the observer has 1000 Nebula
  public function getNebulaAdvanced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaAdvanced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaAdvanced"];
  }

  // Returns 1 if the observer has 2500 Nebula
  public function getNebulaSenior($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaSenior from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaSenior"];
  }

  // Returns 1 if the observer has 5000 Nebula
  public function getNebulaExpert($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaExpert from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaExpert"];
  }

  // Returns 1 if the observer has one Nebula Drawing
  public function getNebulaDrawingsNewbie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaDrawingsNewbie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaDrawingsNewbie"];
  }

  // Returns 1 if the observer has 10 NebulaDrawings
  public function getNebulaDrawingsRookie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaDrawingsRookie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaDrawingsRookie"];
  }

  // Returns 1 if the observer has 25 NebulaDrawings
  public function getNebulaDrawingsBeginner($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaDrawingsBeginner from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaDrawingsBeginner"];
  }

  // Returns 1 if the observer has 50 NebulaDrawings
  public function getNebulaDrawingsTalented($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaDrawingsTalented from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaDrawingsTalented"];
  }

  // Returns 1 if the observer has 100 NebulaDrawings
  public function getNebulaDrawingsSkilled($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaDrawingsSkilled from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaDrawingsSkilled"];
  }

  // Returns 1 if the observer has 250 NebulaDrawings
  public function getNebulaDrawingsIntermediate($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaDrawingsIntermediate from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaDrawingsIntermediate"];
  }

  // Returns 1 if the observer has 500 NebulaDrawings
  public function getNebulaDrawingsExperienced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaDrawingsExperienced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaDrawingsExperienced"];
  }

  // Returns 1 if the observer has 1000 NebulaDrawings
  public function getNebulaDrawingsAdvanced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaDrawingsAdvanced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaDrawingsAdvanced"];
  }

  // Returns 1 if the observer has 2500 NebulaDrawings
  public function getNebulaDrawingsSenior($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaDrawingsSenior from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaDrawingsSenior"];
  }

  // Returns 1 if the observer has 5000 NebulaDrawings
  public function getNebulaDrawingsExpert($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select NebulaDrawingsExpert from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["NebulaDrawingsExpert"];
  }

  // Returns 1 if the observer has one Objects
  public function getObjectsNewbie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsNewbie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsNewbie"];
  }

  // Returns 1 if the observer has 10 Objects
  public function getObjectsRookie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsRookie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsRookie"];
  }

  // Returns 1 if the observer has 25 Objects
  public function getObjectsBeginner($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsBeginner from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsBeginner"];
  }

  // Returns 1 if the observer has 50 Objects
  public function getObjectsTalented($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsTalented from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsTalented"];
  }

  // Returns 1 if the observer has 100 Objects
  public function getObjectsSkilled($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsSkilled from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsSkilled"];
  }

  // Returns 1 if the observer has 250 Objects
  public function getObjectsIntermediate($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsIntermediate from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsIntermediate"];
  }

  // Returns 1 if the observer has 500 Objects
  public function getObjectsExperienced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsExperienced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsExperienced"];
  }

  // Returns 1 if the observer has 1000 Objects
  public function getObjectsAdvanced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsAdvanced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsAdvanced"];
  }

  // Returns 1 if the observer has 2500 Objects
  public function getObjectsSenior($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsSenior from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsSenior"];
  }

  // Returns 1 if the observer has 5000 Objects
  public function getObjectsExpert($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsExpert from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsExpert"];
  }

  // Returns 1 if the observer has one Objects Drawing
  public function getObjectsDrawingsNewbie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsDrawingsNewbie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsDrawingsNewbie"];
  }

  // Returns 1 if the observer has 10 ObjectsDrawings
  public function getObjectsDrawingsRookie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsDrawingsRookie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsDrawingsRookie"];
  }

  // Returns 1 if the observer has 25 ObjectsDrawings
  public function getObjectsDrawingsBeginner($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsDrawingsBeginner from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsDrawingsBeginner"];
  }

  // Returns 1 if the observer has 50 ObjectsDrawings
  public function getObjectsDrawingsTalented($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsDrawingsTalented from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsDrawingsTalented"];
  }

  // Returns 1 if the observer has 100 ObjectsDrawings
  public function getObjectsDrawingsSkilled($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsDrawingsSkilled from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsDrawingsSkilled"];
  }

  // Returns 1 if the observer has 250 ObjectsDrawings
  public function getObjectsDrawingsIntermediate($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsDrawingsIntermediate from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsDrawingsIntermediate"];
  }

  // Returns 1 if the observer has 500 ObjectsDrawings
  public function getObjectsDrawingsExperienced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsDrawingsExperienced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsDrawingsExperienced"];
  }

  // Returns 1 if the observer has 1000 ObjectsDrawings
  public function getObjectsDrawingsAdvanced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsDrawingsAdvanced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsDrawingsAdvanced"];
  }

  // Returns 1 if the observer has 2500 ObjectsDrawings
  public function getObjectsDrawingsSenior($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select ObjectsDrawingsSenior from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["ObjectsDrawingsSenior"];
  }

  // Returns 1 if the observer has 5000 ObjectsDrawings
  public function getObjectsDrawingsExpert($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select objectsDrawingsExpert from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["objectsDrawingsExpert"];
  }

  // Returns 1 if the observer has one Objects Drawing
  public function getCometObservationsNewbie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometObservationsNewbie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometObservationsNewbie"];
  }

  // Returns 1 if the observer has 10 CometObservations
  public function getCometObservationsRookie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometObservationsRookie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometObservationsRookie"];
  }

  // Returns 1 if the observer has 25 CometObservations
  public function getCometObservationsBeginner($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometObservationsBeginner from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometObservationsBeginner"];
  }

  // Returns 1 if the observer has 50 CometObservations
  public function getCometObservationsTalented($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometObservationsTalented from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometObservationsTalented"];
  }

  // Returns 1 if the observer has 100 CometObservations
  public function getCometObservationsSkilled($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometObservationsSkilled from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometObservationsSkilled"];
  }

  // Returns 1 if the observer has 250 CometObservations
  public function getCometObservationsIntermediate($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometObservationsIntermediate from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometObservationsIntermediate"];
  }

  // Returns 1 if the observer has 500 CometObservations
  public function getCometObservationsExperienced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometObservationsExperienced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometObservationsExperienced"];
  }

  // Returns 1 if the observer has 1000 CometObservations
  public function getCometObservationsAdvanced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometObservationsAdvanced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometObservationsAdvanced"];
  }

  // Returns 1 if the observer has 2500 CometObservations
  public function getCometObservationsSenior($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometObservationsSenior from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometObservationsSenior"];
  }

  // Returns 1 if the observer has 5000 CometObservations
  public function getCometObservationsExpert($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometObservationsExpert from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometObservationsExpert"];
  }

  // Returns 1 if the observer has one Objects Drawing
  public function getCometsObservedNewbie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometsObservedNewbie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometsObservedNewbie"];
  }

  // Returns 1 if the observer has 10 CometsObserved
  public function getCometsObservedRookie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometsObservedRookie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometsObservedRookie"];
  }

  // Returns 1 if the observer has 25 CometsObserved
  public function getCometsObservedBeginner($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometsObservedBeginner from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometsObservedBeginner"];
  }

  // Returns 1 if the observer has 50 CometsObserved
  public function getCometsObservedTalented($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometsObservedTalented from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometsObservedTalented"];
  }

  // Returns 1 if the observer has 100 CometsObserved
  public function getCometsObservedSkilled($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometsObservedSkilled from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometsObservedSkilled"];
  }

  // Returns 1 if the observer has 250 CometsObserved
  public function getCometsObservedIntermediate($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometsObservedIntermediate from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometsObservedIntermediate"];
  }

  // Returns 1 if the observer has 500 CometsObserved
  public function getCometsObservedExperienced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometsObservedExperienced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometsObservedExperienced"];
  }

  // Returns 1 if the observer has 1000 CometsObserved
  public function getCometsObservedAdvanced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometsObservedAdvanced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometsObservedAdvanced"];
  }

  // Returns 1 if the observer has 2500 CometsObserved
  public function getCometsObservedSenior($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometsObservedSenior from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometsObservedSenior"];
  }

  // Returns 1 if the observer has 5000 CometsObserved
  public function getCometsObservedExpert($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometsObservedExpert from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometsObservedExpert"];
  }

  // Returns 1 if the observer has one Objects Drawing
  public function getCometDrawingsNewbie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometDrawingsNewbie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometDrawingsNewbie"];
  }

  // Returns 1 if the observer has 10 CometDrawings
  public function getCometDrawingsRookie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometDrawingsRookie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometDrawingsRookie"];
  }

  // Returns 1 if the observer has 25 CometDrawings
  public function getCometDrawingsBeginner($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometDrawingsBeginner from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometDrawingsBeginner"];
  }

  // Returns 1 if the observer has 50 CometDrawings
  public function getCometDrawingsTalented($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometDrawingsTalented from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometDrawingsTalented"];
  }

  // Returns 1 if the observer has 100 CometDrawings
  public function getCometDrawingsSkilled($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometDrawingsSkilled from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometDrawingsSkilled"];
  }

  // Returns 1 if the observer has 250 CometDrawings
  public function getCometDrawingsIntermediate($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometDrawingsIntermediate from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometDrawingsIntermediate"];
  }

  // Returns 1 if the observer has 500 CometDrawings
  public function getCometDrawingsExperienced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometDrawingsExperienced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometDrawingsExperienced"];
  }

  // Returns 1 if the observer has 1000 CometDrawings
  public function getCometDrawingsAdvanced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometDrawingsAdvanced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometDrawingsAdvanced"];
  }

  // Returns 1 if the observer has 2500 CometDrawings
  public function getCometDrawingsSenior($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometDrawingsSenior from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometDrawingsSenior"];
  }

  // Returns 1 if the observer has 5000 CometDrawings
  public function getCometDrawingsExpert($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CometDrawingsExpert from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CometDrawingsExpert"];
  }

  // Recalculates all deepsky accomplishments (for example after adding, removing or changing an observation)
  public function recalculateDeepsky($observerId) {
  	$this->recalculateMessiers($observerId);
  	$this->recalculateCaldwells($observerId);
  	$this->recalculateHerschels($observerId);
  	$this->recalculateHerschelIIs($observerId);
  	$this->recalculateDrawings($observerId);
  	$this->recalculateOpenClusters($observerId);
  	$this->recalculateOpenClusterDrawings($observerId);
  	$this->recalculateGlobularClusters($observerId);
  	$this->recalculateGlobularClusterDrawings($observerId);
  	$this->recalculatePlanetaryNebulae($observerId);
  	$this->recalculatePlanetaryNebulaDrawings($observerId);
  	$this->recalculateGalaxies($observerId);
  	$this->recalculateGalaxyDrawings($observerId);
  	$this->recalculateNebulae($observerId);
  	$this->recalculateNebulaDrawings($observerId);
  	$this->recalculateObjects($observerId);
  	$this->recalculateObjectDrawings($observerId);
  }

  // Recalculates all comet accomplishments (for example after adding, removing or changing an observation)
  public function recalculateComets($observerId) {
  	$this->recalculateCometObservations($observerId);
  	$this->recalculateCometsObserved($observerId);
  	$this->recalculateCometDrawings($observerId);
  }

  public function getSeenSubject($catalog, $numberOfObjects) {
  	return LangNewCertificat . $numberOfObjects . ' ' . $catalog . LangObserved;
  }

  public function getSeenMessage($catalog, $numberOfObjects, $observerId) {
  	return LangCongrats . $numberOfObjects . " " . $catalog . LangCheckout . " http://www.deepskylog.org/index.php?indexAction=detail_observer3&user=\"" . $observerId . "\"";
  }

  public function getDrawSubject($catalog, $numberOfObjects) {
  	return LangNewCertificat . $numberOfObjects . ' ' . $catalog . LangAccomplishmentsDrawn;
  }

  public function getDrawMessage($catalog, $numberOfObjects, $observerId) {
  	return LangDrawCongrats . $numberOfObjects . " " . $catalog . LangDrawCheckout . " http://www.deepskylog.org/index.php?indexAction=detail_observer3&user=\"" . $observerId . "\"";
  }

  public function recalculateMessiers($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// MESSIER
  	$messiers = $this->calculateAccomplishments($observerId, "M", 3, false);

    $oldMessiers = $this->getMessierAccomplishments($observerId);

  	$sql = "UPDATE accomplishments SET messierBronze = " . $messiers[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET messierSilver = " . $messiers[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET messierGold = " . $messiers[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldMessiers[0] == 0 && $messiers[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangMessier, 25), $this->getSeenMessage(LangMessier, 25, $observerId));
  	}

  	if ($oldMessiers[1] == 0 && $messiers[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangMessier, 50), $this->getSeenMessage(LangMessier, 50, $observerId));
  	}

  	if ($oldMessiers[2] == 0 && $messiers[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangMessier, 110), $this->getSeenMessage(LangMessier, 110, $observerId));
  	}

  	// MESSIER DRAWINGS
  	$messierDrawings = $this->calculateAccomplishments($observerId, "M", 3, true);
  	$oldMessierDrawings = $this->getMessierAccomplishmentsDrawings($observerId);

  	$sql = "UPDATE accomplishments SET messierDrawingsBronze = " . $messierDrawings[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET messierDrawingsSilver = " . $messierDrawings[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET messierDrawingsGold = " . $messierDrawings[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldMessierDrawings[0] == 0 && $messierDrawings[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangMessier, 25), $this->getDrawMessage(LangMessier, 25, $observerId));
  	}

  	if ($oldMessierDrawings[1] == 0 && $messierDrawings[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangMessier, 50), $this->getDrawMessage(LangMessier, 50, $observerId));
  	}

  	if ($oldMessierDrawings[2] == 0 && $messierDrawings[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangMessier, 110), $this->getDrawMessage(LangMessier, 110, $observerId));
  	}
  }

  public function recalculateCaldwells($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// CALDWELL
  	$caldwells = $this->calculateAccomplishments($observerId, "Caldwell", 3, false);
    $oldCaldwells = $this->getCaldwellAccomplishments($observerId);

  	$sql = "UPDATE accomplishments SET CaldwellBronze = " . $caldwells[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET CaldwellSilver = " . $caldwells[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET CaldwellGold = " . $caldwells[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCaldwells[0] == 0 && $caldwells[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangCaldwell, 25), $this->getSeenMessage(LangCaldwell, 25, $observerId));
  	}

  	if ($oldCaldwells[1] == 0 && $caldwells[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangCaldwell, 50), $this->getSeenMessage(LangCaldwell, 50, $observerId));
  	}

  	if ($oldCaldwells[2] == 0 && $caldwells[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangCaldwell, 110), $this->getSeenMessage(LangCaldwell, 110, $observerId));
  	}

  	// CALDWELL DRAWINGS
  	$caldwellDrawings = $this->calculateAccomplishments($observerId, "Caldwell", 3, true);
  	$oldCaldwellDrawings = $this->getCaldwellAccomplishmentsDrawings($observerId);

  	$sql = "UPDATE accomplishments SET CaldwellDrawingsBronze = " . $caldwellDrawings[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET CaldwellDrawingsSilver = " . $caldwellDrawings[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET CaldwellDrawingsGold = " . $caldwellDrawings[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCaldwellDrawings[0] == 0 && $caldwellDrawings[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangCaldwell, 25), $this->getDrawMessage(LangCaldwell, 25, $observerId));
  	}

  	if ($oldCaldwellDrawings[1] == 0 && $caldwellDrawings[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangCaldwell, 50), $this->getDrawMessage(LangCaldwell, 50, $observerId));
  	}

  	if ($oldCaldwellDrawings[2] == 0 && $caldwellDrawings[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangCaldwell, 110), $this->getDrawMessage(LangCaldwell, 110, $observerId));
  	}
  }

  public function recalculateHerschels($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// Herschel
  	$herschels = $this->calculateAccomplishments($observerId, "H400", 5, false);
  	$oldHerschels = $this->getHerschelAccomplishments($observerId);

  	$sql = "UPDATE accomplishments SET HerschelBronze = " . $herschels[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET HerschelSilver = " . $herschels[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET HerschelGold = " . $herschels[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET HerschelDiamond = " . $herschels[3] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET HerschelPlatina = " . $herschels[4] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschels[0] == 0 && $herschels[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschel400, 25), $this->getSeenMessage(LangHerschel400, 25, $observerId));
  	}

  	if ($oldHerschels[1] == 0 && $herschels[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschel400, 50), $this->getSeenMessage(LangHerschel400, 50, $observerId));
  	}

  	if ($oldHerschels[2] == 0 && $herschels[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschel400, 100), $this->getSeenMessage(LangHerschel400, 100, $observerId));
  	}

  	if ($oldHerschels[3] == 0 && $herschels[3] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschel400, 200), $this->getSeenMessage(LangHerschel400, 200, $observerId));
  	}

  	if ($oldHerschels[4] == 0 && $herschels[4] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschel400, 400), $this->getSeenMessage(LangHerschel400, 400, $observerId));
  	}

  	// Herschel DRAWINGS
  	$herschelDrawings = $this->calculateAccomplishments($observerId, "H400", 5, true);
  	$oldHerschelDrawings = $this->getHerschelAccomplishmentsDrawings($observerId);

  	$sql = "UPDATE accomplishments SET HerschelDrawingsBronze = " . $herschelDrawings[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET HerschelDrawingsSilver = " . $herschelDrawings[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET HerschelDrawingsGold = " . $herschelDrawings[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET HerschelDrawingsDiamond = " . $herschelDrawings[3] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET HerschelDrawingsPlatina = " . $herschelDrawings[4] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelDrawings[0] == 0 && $herschelDrawings[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschel400, 25), $this->getDrawMessage(LangHerschel400, 25, $observerId));
  	}

  	if ($oldHerschelDrawings[1] == 0 && $herschelDrawings[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschel400, 50), $this->getDrawMessage(LangHerschel400, 50, $observerId));
  	}

  	if ($oldHerschelDrawings[2] == 0 && $herschelDrawings[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschel400, 100), $this->getDrawMessage(LangHerschel400, 100, $observerId));
  	}

  	if ($oldHerschelDrawings[3] == 0 && $herschelDrawings[3] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschel400, 200), $this->getDrawMessage(LangHerschel400, 200, $observerId));
  	}

  	if ($oldHerschelDrawings[4] == 0 && $herschelDrawings[4] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschel400, 400), $this->getDrawMessage(LangHerschel400, 400, $observerId));
  	}
  }

  public function recalculateHerschelIIs($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// Herschel
  	$herschels = $this->calculateAccomplishments($observerId, "HII", 5, false);
  	$oldHerschels = $this->getHerschelIIAccomplishments($observerId);

  	$sql = "UPDATE accomplishments SET HerschelIIBronze = " . $herschels[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET HerschelIISilver = " . $herschels[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET HerschelIIGold = " . $herschels[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET HerschelIIDiamond = " . $herschels[3] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET HerschelIIPlatina = " . $herschels[4] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschels[0] == 0 && $herschels[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschelII, 25), $this->getSeenMessage(LangHerschelII, 25, $observerId));
  	}

  	if ($oldHerschels[1] == 0 && $herschels[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschelII, 50), $this->getSeenMessage(LangHerschelII, 50, $observerId));
  	}

  	if ($oldHerschels[2] == 0 && $herschels[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschelII, 100), $this->getSeenMessage(LangHerschelII, 100, $observerId));
  	}

  	if ($oldHerschels[3] == 0 && $herschels[3] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschelII, 200), $this->getSeenMessage(LangHerschelII, 200, $observerId));
  	}

  	if ($oldHerschels[4] == 0 && $herschels[4] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschelII, 400), $this->getSeenMessage(LangHerschelII, 400, $observerId));
  	}

  	// Herschel DRAWINGS
  	$herschelDrawings = $this->calculateAccomplishments($observerId, "HII", 5, true);
  	$oldHerschelDrawings = $this->getHerschelIIAccomplishmentsDrawings($observerId);

  	$sql = "UPDATE accomplishments SET HerschelIIDrawingsBronze = " . $herschelDrawings[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET HerschelIIDrawingsSilver = " . $herschelDrawings[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET HerschelIIDrawingsGold = " . $herschelDrawings[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET HerschelIIDrawingsDiamond = " . $herschelDrawings[3] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET HerschelIIDrawingsPlatina = " . $herschelDrawings[4] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelDrawings[0] == 0 && $herschelDrawings[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschelII, 25), $this->getDrawMessage(LangHerschelII, 25, $observerId));
  	}

  	if ($oldHerschelDrawings[1] == 0 && $herschelDrawings[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschelII, 50), $this->getDrawMessage(LangHerschelII, 50, $observerId));
  	}

  	if ($oldHerschelDrawings[2] == 0 && $herschelDrawings[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschelII, 100), $this->getDrawMessage(LangHerschelII, 100, $observerId));
  	}

  	if ($oldHerschelDrawings[3] == 0 && $herschelDrawings[3] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschelII, 200), $this->getDrawMessage(LangHerschelII, 200, $observerId));
  	}

  	if ($oldHerschelDrawings[4] == 0 && $herschelDrawings[4] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschelII, 400), $this->getDrawMessage(LangHerschelII, 400, $observerId));
  	}
  }

  public function recalculateDrawings($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// drawings
  	$drawings = $this->calculateAccomplishments($observerId, "drawings", 10, true);
  	$oldDrawings = $this->getDrawingsAccomplishments($observerId);

  	$sql = "UPDATE accomplishments SET drawingsNewbie = " . $drawings[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET drawingsRookie = " . $drawings[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET drawingsBeginner = " . $drawings[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET drawingsTalented = " . $drawings[3] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET drawingsSkilled = " . $drawings[4] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET drawingsIntermediate = " . $drawings[5] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET drawingsExperienced = " . $drawings[6] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET drawingsAdvanced = " . $drawings[7] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET drawingsSenior = " . $drawings[8] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET drawingsExpert = " . $drawings[9] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldDrawings[0] == 0 && $drawings[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangObject, 1), $this->getDrawMessage(LangObject, 1, $observerId));
  	}

  	if ($oldDrawings[1] == 0 && $drawings[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 10), $this->getDrawMessage(LangAccomplishmentsObjects, 10, $observerId));
  	}

  	if ($oldDrawings[2] == 0 && $drawings[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 25), $this->getDrawMessage(LangAccomplishmentsObjects, 25, $observerId));
  	}

  	if ($oldDrawings[3] == 0 && $drawings[3] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 50), $this->getDrawMessage(LangAccomplishmentsObjects, 50, $observerId));
  	}

  	if ($oldDrawings[4] == 0 && $drawings[4] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 100), $this->getDrawMessage(LangAccomplishmentsObjects, 100, $observerId));
  	}

  	if ($oldDrawings[5] == 0 && $drawings[5] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 250), $this->getDrawMessage(LangAccomplishmentsObjects, 250, $observerId));
  	}

  	if ($oldDrawings[6] == 0 && $drawings[6] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 500), $this->getDrawMessage(LangAccomplishmentsObjects, 500, $observerId));
  	}

  	if ($oldDrawings[7] == 0 && $drawings[7] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 1000), $this->getDrawMessage(LangAccomplishmentsObjects, 1000, $observerId));
  	}

  	if ($oldDrawings[8] == 0 && $drawings[8] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 2500), $this->getDrawMessage(LangAccomplishmentsObjects, 2500, $observerId));
  	}

  	if ($oldDrawings[9] == 0 && $drawings[9] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 5000), $this->getDrawMessage(LangAccomplishmentsObjects, 5000, $observerId));
  	}
  }

  public function recalculateOpenClusters($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// OpenClusters
  	$openClusters = $this->calculateAccomplishments($observerId, "openClusters", 10, false, 1700);
  	$oldOpenClusters = $this->getOpenClustersAccomplishments($observerId);

  	$sql = "UPDATE accomplishments SET OpenClusterNewbie = " . $openClusters[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterRookie = " . $openClusters[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterBeginner = " . $openClusters[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterTalented = " . $openClusters[3] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterSkilled = " . $openClusters[4] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterIntermediate = " . $openClusters[5] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterExperienced = " . $openClusters[6] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterAdvanced = " . $openClusters[7] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterSenior = " . $openClusters[8] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterExpert = " . $openClusters[9] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClusters[0] == 0 && $openClusters[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenCluster, 1), $this->getSeenMessage(LangOpenCluster, 1, $observerId));
  	}

  	if ($oldOpenClusters[1] == 0 && $openClusters[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenCluster, 10), $this->getSeenMessage(LangOpenCluster, 10, $observerId));
  	}

  	if ($oldOpenClusters[2] == 0 && $openClusters[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenCluster, 25), $this->getSeenMessage(LangOpenCluster, 25, $observerId));
  	}

  	if ($oldOpenClusters[3] == 0 && $openClusters[3] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenCluster, 50), $this->getSeenMessage(LangOpenCluster, 50, $observerId));
  	}

  	if ($oldOpenClusters[4] == 0 && $openClusters[4] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenCluster, 100), $this->getSeenMessage(LangOpenCluster, 100, $observerId));
  	}

  	if ($oldOpenClusters[5] == 0 && $openClusters[5] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenCluster, 250), $this->getSeenMessage(LangOpenCluster, 250, $observerId));
  	}

  	if ($oldOpenClusters[6] == 0 && $openClusters[6] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenCluster, 500), $this->getSeenMessage(LangOpenCluster, 500, $observerId));
  	}

  	if ($oldOpenClusters[7] == 0 && $openClusters[7] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenCluster, 1000), $this->getSeenMessage(LangOpenCluster, 1000, $observerId));
  	}

  	if ($oldOpenClusters[8] == 0 && $openClusters[8] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenCluster, 2500), $this->getSeenMessage(LangOpenCluster, 2500, $observerId));
  	}

  	if ($oldOpenClusters[9] == 0 && $openClusters[9] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenCluster, 5000), $this->getSeenMessage(LangOpenCluster, 5000, $observerId));
  	}
  }

  public function recalculateOpenClusterDrawings($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// OpenClusterDrawings
    $openClusterDrawings = $this->calculateAccomplishments($observerId, "openClusters", 10, true, 1700);
  	$oldOpenClusterDrawings = $this->getOpenClustersAccomplishmentsDrawings($observerId);

  	$sql = "UPDATE accomplishments SET OpenClusterDrawingsNewbie = " . $openClusterDrawings[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterDrawingsRookie = " . $openClusterDrawings[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterDrawingsBeginner = " . $openClusterDrawings[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterDrawingsTalented = " . $openClusterDrawings[3] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterDrawingsSkilled = " . $openClusterDrawings[4] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterDrawingsIntermediate = " . $openClusterDrawings[5] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterDrawingsExperienced = " . $openClusterDrawings[6] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterDrawingsAdvanced = " . $openClusterDrawings[7] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterDrawingsSenior = " . $openClusterDrawings[8] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET OpenClusterDrawingsExpert = " . $openClusterDrawings[9] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClusterDrawings[0] == 0 && $openClusterDrawings[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenCluster, 1), $this->getDrawMessage(LangOpenCluster, 1, $observerId));
  	}

  	if ($oldOpenClusterDrawings[1] == 0 && $openClusterDrawings[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenCluster, 10), $this->getDrawMessage(LangOpenCluster, 10, $observerId));
  	}

  	if ($oldOpenClusterDrawings[2] == 0 && $openClusterDrawings[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenCluster, 25), $this->getDrawMessage(LangOpenCluster, 25, $observerId));
  	}

  	if ($oldOpenClusterDrawings[3] == 0 && $openClusterDrawings[3] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenCluster, 50), $this->getDrawMessage(LangOpenCluster, 50, $observerId));
  	}

  	if ($oldOpenClusterDrawings[4] == 0 && $openClusterDrawings[4] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenCluster, 100), $this->getDrawMessage(LangOpenCluster, 100, $observerId));
  	}

  	if ($oldOpenClusterDrawings[5] == 0 && $openClusterDrawings[5] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenCluster, 250), $this->getDrawMessage(LangOpenCluster, 250, $observerId));
  	}

  	if ($oldOpenClusterDrawings[6] == 0 && $openClusterDrawings[6] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenCluster, 500), $this->getDrawMessage(LangOpenCluster, 500, $observerId));
  	}

  	if ($oldOpenClusterDrawings[7] == 0 && $openClusterDrawings[7] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenCluster, 1000), $this->getDrawMessage(LangOpenCluster, 1000, $observerId));
  	}

  	if ($oldOpenClusterDrawings[8] == 0 && $openClusterDrawings[8] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenCluster, 2500), $this->getDrawMessage(LangOpenCluster, 2500, $observerId));
  	}

  	if ($oldOpenClusterDrawings[9] == 0 && $openClusterDrawings[9] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenCluster, 5000), $this->getDrawMessage(LangOpenCluster, 5000, $observerId));
  	}
  }

  public function recalculateGlobularClusters($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// GlobularClusters
  	$globularClusters = $this->calculateAccomplishments($observerId, "globularClusters", 10, false, 152);
  	$oldGlobularClusters = $this->getGlobularClustersAccomplishments($observerId);

  	$sql = "UPDATE accomplishments SET GlobularClusterNewbie = " . $globularClusters[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterRookie = " . $globularClusters[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterBeginner = " . $globularClusters[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterTalented = " . $globularClusters[3] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterSkilled = " . $globularClusters[4] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterIntermediate = " . $globularClusters[5] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterExperienced = " . $globularClusters[6] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterAdvanced = " . $globularClusters[7] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterSenior = " . $globularClusters[8] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterExpert = " . $globularClusters[9] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClusters[0] == 0 && $globularClusters[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularCluster, 1), $this->getSeenMessage(LangGlobularCluster, 1, $observerId));
  	}

  	if ($oldGlobularClusters[1] == 0 && $globularClusters[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularCluster, 10), $this->getSeenMessage(LangGlobularCluster, 10, $observerId));
  	}

  	if ($oldGlobularClusters[2] == 0 && $globularClusters[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularCluster, 25), $this->getSeenMessage(LangGlobularCluster, 25, $observerId));
  	}

  	if ($oldGlobularClusters[3] == 0 && $globularClusters[3] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularCluster, 50), $this->getSeenMessage(LangGlobularCluster, 50, $observerId));
  	}

  	if ($oldGlobularClusters[4] == 0 && $globularClusters[4] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularCluster, 100), $this->getSeenMessage(LangGlobularCluster, 100, $observerId));
  	}

  	if ($oldGlobularClusters[5] == 0 && $globularClusters[5] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularCluster, 250), $this->getSeenMessage(LangGlobularCluster, 250, $observerId));
  	}

  	if ($oldGlobularClusters[6] == 0 && $globularClusters[6] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularCluster, 500), $this->getSeenMessage(LangGlobularCluster, 500, $observerId));
  	}

  	if ($oldGlobularClusters[7] == 0 && $globularClusters[7] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularCluster, 1000), $this->getSeenMessage(LangGlobularCluster, 1000, $observerId));
  	}

  	if ($oldGlobularClusters[8] == 0 && $globularClusters[8] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularCluster, 2500), $this->getSeenMessage(LangGlobularCluster, 2500, $observerId));
  	}

  	if ($oldGlobularClusters[9] == 0 && $globularClusters[9] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularCluster, 5000), $this->getSeenMessage(LangGlobularCluster, 5000, $observerId));
  	}
  }

  public function recalculateGlobularClusterDrawings($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// GlobularClusterDrawings
    $globularClusterDrawings = $this->calculateAccomplishments($observerId, "globularClusters", 10, true, 152);
  	$oldGlobularClusterDrawings = $this->getGlobularClustersAccomplishmentsDrawings($observerId);

  	$sql = "UPDATE accomplishments SET GlobularClusterDrawingsNewbie = " . $globularClusterDrawings[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterDrawingsRookie = " . $globularClusterDrawings[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterDrawingsBeginner = " . $globularClusterDrawings[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterDrawingsTalented = " . $globularClusterDrawings[3] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterDrawingsSkilled = " . $globularClusterDrawings[4] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterDrawingsIntermediate = " . $globularClusterDrawings[5] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterDrawingsExperienced = " . $globularClusterDrawings[6] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterDrawingsAdvanced = " . $globularClusterDrawings[7] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterDrawingsSenior = " . $globularClusterDrawings[8] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GlobularClusterDrawingsExpert = " . $globularClusterDrawings[9] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClusterDrawings[0] == 0 && $globularClusterDrawings[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularCluster, 1), $this->getDrawMessage(LangGlobularCluster, 1, $observerId));
  	}

  	if ($oldGlobularClusterDrawings[1] == 0 && $globularClusterDrawings[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularCluster, 10), $this->getDrawMessage(LangGlobularCluster, 10, $observerId));
  	}

  	if ($oldGlobularClusterDrawings[2] == 0 && $globularClusterDrawings[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularCluster, 25), $this->getDrawMessage(LangGlobularCluster, 25, $observerId));
  	}

  	if ($oldGlobularClusterDrawings[3] == 0 && $globularClusterDrawings[3] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularCluster, 50), $this->getDrawMessage(LangGlobularCluster, 50, $observerId));
  	}

  	if ($oldGlobularClusterDrawings[4] == 0 && $globularClusterDrawings[4] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularCluster, 100), $this->getDrawMessage(LangGlobularCluster, 100, $observerId));
  	}

  	if ($oldGlobularClusterDrawings[5] == 0 && $globularClusterDrawings[5] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularCluster, 250), $this->getDrawMessage(LangGlobularCluster, 250, $observerId));
  	}

  	if ($oldGlobularClusterDrawings[6] == 0 && $globularClusterDrawings[6] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularCluster, 500), $this->getDrawMessage(LangGlobularCluster, 500, $observerId));
  	}

  	if ($oldGlobularClusterDrawings[7] == 0 && $globularClusterDrawings[7] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularCluster, 1000), $this->getDrawMessage(LangGlobularCluster, 1000, $observerId));
  	}

  	if ($oldGlobularClusterDrawings[8] == 0 && $globularClusterDrawings[8] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularCluster, 2500), $this->getDrawMessage(LangGlobularCluster, 2500, $observerId));
  	}

  	if ($oldGlobularClusterDrawings[9] == 0 && $globularClusterDrawings[9] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularCluster, 5000), $this->getDrawMessage(LangGlobularCluster, 5000, $observerId));
  	}
  }

  public function recalculatePlanetaryNebulae($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// PlanetaryNebulae
  	$planetaryNebulae = $this->calculateAccomplishments($observerId, "planetaryNebulae", 10, false, 1023);
  	$oldPlanetaryNebulae = $this->getPlanetaryNebulaeAccomplishments($observerId);

  	$sql = "UPDATE accomplishments SET PlanetaryNebulaNewbie = " . $planetaryNebulae[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaRookie = " . $planetaryNebulae[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaBeginner = " . $planetaryNebulae[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaTalented = " . $planetaryNebulae[3] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaSkilled = " . $planetaryNebulae[4] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaIntermediate = " . $planetaryNebulae[5] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaExperienced = " . $planetaryNebulae[6] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaAdvanced = " . $planetaryNebulae[7] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaSenior = " . $planetaryNebulae[8] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaExpert = " . $planetaryNebulae[9] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulae[0] == 0 && $planetaryNebulae[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebula, 1), $this->getSeenMessage(LangPlanetaryNebula, 1, $observerId));
  	}

  	if ($oldPlanetaryNebulae[1] == 0 && $planetaryNebulae[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebula, 10), $this->getSeenMessage(LangPlanetaryNebula, 10, $observerId));
  	}

  	if ($oldPlanetaryNebulae[2] == 0 && $planetaryNebulae[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebula, 25), $this->getSeenMessage(LangPlanetaryNebula, 25, $observerId));
  	}

  	if ($oldPlanetaryNebulae[3] == 0 && $planetaryNebulae[3] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebula, 50), $this->getSeenMessage(LangPlanetaryNebula, 50, $observerId));
  	}

  	if ($oldPlanetaryNebulae[4] == 0 && $planetaryNebulae[4] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebula, 100), $this->getSeenMessage(LangPlanetaryNebula, 100, $observerId));
  	}

  	if ($oldPlanetaryNebulae[5] == 0 && $planetaryNebulae[5] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebula, 250), $this->getSeenMessage(LangPlanetaryNebula, 250, $observerId));
  	}

  	if ($oldPlanetaryNebulae[6] == 0 && $planetaryNebulae[6] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebula, 500), $this->getSeenMessage(LangPlanetaryNebula, 500, $observerId));
  	}

  	if ($oldPlanetaryNebulae[7] == 0 && $planetaryNebulae[7] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebula, 1000), $this->getSeenMessage(LangPlanetaryNebula, 1000, $observerId));
  	}

  	if ($oldPlanetaryNebulae[8] == 0 && $planetaryNebulae[8] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebula, 2500), $this->getSeenMessage(LangPlanetaryNebula, 2500, $observerId));
  	}

  	if ($oldPlanetaryNebulae[9] == 0 && $planetaryNebulae[9] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebula, 5000), $this->getSeenMessage(LangPlanetaryNebula, 5000, $observerId));
  	}
  }

  public function recalculatePlanetaryNebulaDrawings($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// PlanetaryNebulaDrawings
    $planetaryNebulaDrawings = $this->calculateAccomplishments($observerId, "planetaryNebulae", 10, true, 1023);
  	$oldPlanetaryNebulaDrawings = $this->getPlanetaryNebulaeAccomplishmentsDrawings($observerId);

  	$sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsNewbie = " . $planetaryNebulaDrawings[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsRookie = " . $planetaryNebulaDrawings[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsBeginner = " . $planetaryNebulaDrawings[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsTalented = " . $planetaryNebulaDrawings[3] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsSkilled = " . $planetaryNebulaDrawings[4] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsIntermediate = " . $planetaryNebulaDrawings[5] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsExperienced = " . $planetaryNebulaDrawings[6] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsAdvanced = " . $planetaryNebulaDrawings[7] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsSenior = " . $planetaryNebulaDrawings[8] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsExpert = " . $planetaryNebulaDrawings[9] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaDrawings[0] == 0 && $planetaryNebulaDrawings[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebula, 1), $this->getDrawMessage(LangPlanetaryNebula, 1, $observerId));
  	}

  	if ($oldPlanetaryNebulaDrawings[1] == 0 && $planetaryNebulaDrawings[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebula, 10), $this->getDrawMessage(LangPlanetaryNebula, 10, $observerId));
  	}

  	if ($oldPlanetaryNebulaDrawings[2] == 0 && $planetaryNebulaDrawings[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebula, 25), $this->getDrawMessage(LangPlanetaryNebula, 25, $observerId));
  	}

  	if ($oldPlanetaryNebulaDrawings[3] == 0 && $planetaryNebulaDrawings[3] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebula, 50), $this->getDrawMessage(LangPlanetaryNebula, 50, $observerId));
  	}

  	if ($oldPlanetaryNebulaDrawings[4] == 0 && $planetaryNebulaDrawings[4] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebula, 100), $this->getDrawMessage(LangPlanetaryNebula, 100, $observerId));
  	}

  	if ($oldPlanetaryNebulaDrawings[5] == 0 && $planetaryNebulaDrawings[5] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebula, 250), $this->getDrawMessage(LangPlanetaryNebula, 250, $observerId));
  	}

  	if ($oldPlanetaryNebulaDrawings[6] == 0 && $planetaryNebulaDrawings[6] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebula, 500), $this->getDrawMessage(LangPlanetaryNebula, 500, $observerId));
  	}

  	if ($oldPlanetaryNebulaDrawings[7] == 0 && $planetaryNebulaDrawings[7] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebula, 1000), $this->getDrawMessage(LangPlanetaryNebula, 1000, $observerId));
  	}

  	if ($oldPlanetaryNebulaDrawings[8] == 0 && $planetaryNebulaDrawings[8] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebula, 2500), $this->getDrawMessage(LangPlanetaryNebula, 2500, $observerId));
  	}

  	if ($oldPlanetaryNebulaDrawings[9] == 0 && $planetaryNebulaDrawings[9] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebula, 5000), $this->getDrawMessage(LangPlanetaryNebula, 5000, $observerId));
  	}
  }

  public function recalculateGalaxies($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// Galaxies
  	$galaxies = $this->calculateAccomplishments($observerId, "galaxies", 10, false, 152);
  	$oldGalaxies = $this->getGalaxiesAccomplishments($observerId);

  	$sql = "UPDATE accomplishments SET GalaxyNewbie = " . $galaxies[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxyRookie = " . $galaxies[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxyBeginner = " . $galaxies[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxyTalented = " . $galaxies[3] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxySkilled = " . $galaxies[4] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxyIntermediate = " . $galaxies[5] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxyExperienced = " . $galaxies[6] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxyAdvanced = " . $galaxies[7] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxySenior = " . $galaxies[8] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxyExpert = " . $galaxies[9] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxies[0] == 0 && $galaxies[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxy, 1), $this->getSeenMessage(LangGalaxy, 1, $observerId));
  	}

  	if ($oldGalaxies[1] == 0 && $galaxies[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxy, 10), $this->getSeenMessage(LangGalaxy, 10, $observerId));
  	}

  	if ($oldGalaxies[2] == 0 && $galaxies[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxy, 25), $this->getSeenMessage(LangGalaxy, 25, $observerId));
  	}

  	if ($oldGalaxies[3] == 0 && $galaxies[3] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxy, 50), $this->getSeenMessage(LangGalaxy, 50, $observerId));
  	}

  	if ($oldGalaxies[4] == 0 && $galaxies[4] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxy, 100), $this->getSeenMessage(LangGalaxy, 100, $observerId));
  	}

  	if ($oldGalaxies[5] == 0 && $galaxies[5] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxy, 250), $this->getSeenMessage(LangGalaxy, 250, $observerId));
  	}

  	if ($oldGalaxies[6] == 0 && $galaxies[6] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxy, 500), $this->getSeenMessage(LangGalaxy, 500, $observerId));
  	}

  	if ($oldGalaxies[7] == 0 && $galaxies[7] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxy, 1000), $this->getSeenMessage(LangGalaxy, 1000, $observerId));
  	}

  	if ($oldGalaxies[8] == 0 && $galaxies[8] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxy, 2500), $this->getSeenMessage(LangGalaxy, 2500, $observerId));
  	}

  	if ($oldGalaxies[9] == 0 && $galaxies[9] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxy, 5000), $this->getSeenMessage(LangGalaxy, 5000, $observerId));
  	}
  }

  public function recalculateGalaxyDrawings($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// GalaxyDrawings
    $galaxyDrawings = $this->calculateAccomplishments($observerId, "galaxies", 10, true, 152);
  	$oldGalaxyDrawings = $this->getGalaxiesAccomplishmentsDrawings($observerId);

  	$sql = "UPDATE accomplishments SET GalaxyDrawingsNewbie = " . $galaxyDrawings[0] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxyDrawingsRookie = " . $galaxyDrawings[1] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxyDrawingsBeginner = " . $galaxyDrawings[2] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxyDrawingsTalented = " . $galaxyDrawings[3] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxyDrawingsSkilled = " . $galaxyDrawings[4] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxyDrawingsIntermediate = " . $galaxyDrawings[5] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxyDrawingsExperienced = " . $galaxyDrawings[6] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxyDrawingsAdvanced = " . $galaxyDrawings[7] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxyDrawingsSenior = " . $galaxyDrawings[8] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

    $sql = "UPDATE accomplishments SET GalaxyDrawingsExpert = " . $galaxyDrawings[9] . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxyDrawings[0] == 0 && $galaxyDrawings[0] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxy, 1), $this->getDrawMessage(LangGalaxy, 1, $observerId));
  	}

  	if ($oldGalaxyDrawings[1] == 0 && $galaxyDrawings[1] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxy, 10), $this->getDrawMessage(LangGalaxy, 10, $observerId));
  	}

  	if ($oldGalaxyDrawings[2] == 0 && $galaxyDrawings[2] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxy, 25), $this->getDrawMessage(LangGalaxy, 25, $observerId));
  	}

  	if ($oldGalaxyDrawings[3] == 0 && $galaxyDrawings[3] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxy, 50), $this->getDrawMessage(LangGalaxy, 50, $observerId));
  	}

  	if ($oldGalaxyDrawings[4] == 0 && $galaxyDrawings[4] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxy, 100), $this->getDrawMessage(LangGalaxy, 100, $observerId));
  	}

  	if ($oldGalaxyDrawings[5] == 0 && $galaxyDrawings[5] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxy, 250), $this->getDrawMessage(LangGalaxy, 250, $observerId));
  	}

  	if ($oldGalaxyDrawings[6] == 0 && $galaxyDrawings[6] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxy, 500), $this->getDrawMessage(LangGalaxy, 500, $observerId));
  	}

  	if ($oldGalaxyDrawings[7] == 0 && $galaxyDrawings[7] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxy, 1000), $this->getDrawMessage(LangGalaxy, 1000, $observerId));
  	}

  	if ($oldGalaxyDrawings[8] == 0 && $galaxyDrawings[8] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxy, 2500), $this->getDrawMessage(LangGalaxy, 2500, $observerId));
  	}

  	if ($oldGalaxyDrawings[9] == 0 && $galaxyDrawings[9] == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxy, 5000), $this->getDrawMessage(LangGalaxy, 5000, $observerId));
  	}
  }





  public function recalculateNebulae($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// Nebula
  	$Nebula = $this->calculateAccomplishments($observerId, "nebulae", 10, false, 384);
  	$oldNebulaNewbie = $this->getNebulaNewbie($observerId);
  	$newNebulaNewbie = $Nebula[0];
  	$sql = "UPDATE accomplishments SET NebulaNewbie = " . $newNebulaNewbie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaNewbie == 0 && $newNebulaNewbie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangNebula, 1), $this->getSeenMessage(LangNebula, 1, $observerId));
  	}

  	$oldNebulaRookie = $this->getNebulaRookie($observerId);
  	$newNebulaRookie = $Nebula[1];
  	$sql = "UPDATE accomplishments SET NebulaRookie = " . $newNebulaRookie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaRookie == 0 && $newNebulaRookie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangNebulaeSeen, 2), $this->getSeenMessage(LangNebulaeSeen, 2, $observerId));
  	}

  	$oldNebulaBeginner = $this->getNebulaBeginner($observerId);
  	$newNebulaBeginner = $Nebula[2];
  	$sql = "UPDATE accomplishments SET NebulaBeginner = " . $newNebulaBeginner . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaBeginner == 0 && $newNebulaBeginner == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangNebulaeSeen, 3), $this->getSeenMessage(LangNebulaeSeen, 3, $observerId));
  	}

  	$oldNebulaTalented = $this->getNebulaTalented($observerId);
  	$newNebulaTalented = $Nebula[3];
  	$sql = "UPDATE accomplishments SET NebulaTalented = " . $newNebulaTalented . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaTalented == 0 && $newNebulaTalented == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangNebulaeSeen, 4), $this->getSeenMessage(LangNebulaeSeen, 4, $observerId));
  	}

  	$oldNebulaSkilled = $this->getNebulaSkilled($observerId);
  	$newNebulaSkilled = $Nebula[4];
  	$sql = "UPDATE accomplishments SET NebulaSkilled = " . $newNebulaSkilled . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaSkilled == 0 && $newNebulaSkilled == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangNebulaeSeen, 7), $this->getSeenMessage(LangNebulaeSeen, 7, $observerId));
  	}

  	$oldNebulaIntermediate = $this->getNebulaIntermediate($observerId);
  	$newNebulaIntermediate = $Nebula[5];
  	$sql = "UPDATE accomplishments SET NebulaIntermediate = " . $newNebulaIntermediate . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaIntermediate == 0 && $newNebulaIntermediate == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangNebulaeSeen, 19), $this->getSeenMessage(LangNebulaeSeen, 19, $observerId));
  	}

  	$oldNebulaExperienced = $this->getNebulaExperienced($observerId);
  	$newNebulaExperienced = $Nebula[6];
  	$sql = "UPDATE accomplishments SET NebulaExperienced = " . $newNebulaExperienced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaExperienced == 0 && $newNebulaExperienced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangNebulaeSeen, 38), $this->getSeenMessage(LangNebulaeSeen, 38, $observerId));
  	}

  	$oldNebulaAdvanced = $this->getNebulaAdvanced($observerId);
  	$newNebulaAdvanced = $Nebula[7];
  	$sql = "UPDATE accomplishments SET NebulaAdvanced = " . $newNebulaAdvanced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaAdvanced == 0 && $newNebulaAdvanced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangNebulaeSeen, 76), $this->getSeenMessage(LangNebulaeSeen, 76, $observerId));
  	}

  	$oldNebulaSenior = $this->getNebulaSenior($observerId);
  	$newNebulaSenior = $Nebula[8];
  	$sql = "UPDATE accomplishments SET NebulaSenior = " . $newNebulaSenior . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaSenior == 0 && $newNebulaSenior == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangNebulaeSeen, 192), $this->getSeenMessage(LangNebulaeSeen, 192, $observerId));
  	}

  	$oldNebulaExpert = $this->getNebulaExpert($observerId);
  	$newNebulaExpert = $Nebula[9];
  	$sql = "UPDATE accomplishments SET NebulaExpert = " . $newNebulaExpert . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaExpert == 0 && $newNebulaExpert == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangNebulaeSeen, 384), $this->getSeenMessage(LangNebulaeSeen, 384, $observerId));
  	}
  }

  public function recalculateNebulaDrawings($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// NebulaDrawings
  	$NebulaDrawings = $this->calculateAccomplishments($observerId, "nebulae", 10, true, 384);
  	$oldNebulaDrawingsNewbie = $this->getNebulaDrawingsNewbie($observerId);
  	$newNebulaDrawingsNewbie = $NebulaDrawings[0];
  	$sql = "UPDATE accomplishments SET NebulaDrawingsNewbie = " . $newNebulaDrawingsNewbie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaDrawingsNewbie == 0 && $newNebulaDrawingsNewbie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangNebula, 1), $this->getDrawMessage(LangNebula, 1, $observerId));
  	}

  	$oldNebulaDrawingsRookie = $this->getNebulaDrawingsRookie($observerId);
  	$newNebulaDrawingsRookie = $NebulaDrawings[1];
  	$sql = "UPDATE accomplishments SET NebulaDrawingsRookie = " . $newNebulaDrawingsRookie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaDrawingsRookie == 0 && $newNebulaDrawingsRookie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangNebulaeSeen, 2), $this->getDrawMessage(LangNebulaeSeen, 2, $observerId));
  	}

  	$oldNebulaDrawingsBeginner = $this->getNebulaDrawingsBeginner($observerId);
  	$newNebulaDrawingsBeginner = $NebulaDrawings[2];
  	$sql = "UPDATE accomplishments SET NebulaDrawingsBeginner = " . $newNebulaDrawingsBeginner . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaDrawingsBeginner == 0 && $newNebulaDrawingsBeginner == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangNebulaeSeen, 3), $this->getDrawMessage(LangNebulaeSeen, 3, $observerId));
  	}

  	$oldNebulaDrawingsTalented = $this->getNebulaDrawingsTalented($observerId);
  	$newNebulaDrawingsTalented = $NebulaDrawings[3];
  	$sql = "UPDATE accomplishments SET NebulaDrawingsTalented = " . $newNebulaDrawingsTalented . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaDrawingsTalented == 0 && $newNebulaDrawingsTalented == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangNebulaeSeen, 4), $this->getDrawMessage(LangNebulaeSeen, 4, $observerId));
  	}

  	$oldNebulaDrawingsSkilled = $this->getNebulaDrawingsSkilled($observerId);
  	$newNebulaDrawingsSkilled = $NebulaDrawings[4];
  	$sql = "UPDATE accomplishments SET NebulaDrawingsSkilled = " . $newNebulaDrawingsSkilled . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaDrawingsSkilled == 0 && $newNebulaDrawingsSkilled == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangNebulaeSeen, 7), $this->getDrawMessage(LangNebulaeSeen, 7, $observerId));
  	}

  	$oldNebulaDrawingsIntermediate = $this->getNebulaDrawingsIntermediate($observerId);
  	$newNebulaDrawingsIntermediate = $NebulaDrawings[5];
  	$sql = "UPDATE accomplishments SET NebulaDrawingsIntermediate = " . $newNebulaDrawingsIntermediate . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaDrawingsIntermediate == 0 && $newNebulaDrawingsIntermediate == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangNebulaeSeen, 19), $this->getDrawMessage(LangNebulaeSeen, 19, $observerId));
  	}

  	$oldNebulaDrawingsExperienced = $this->getNebulaDrawingsExperienced($observerId);
  	$newNebulaDrawingsExperienced = $NebulaDrawings[6];
  	$sql = "UPDATE accomplishments SET NebulaDrawingsExperienced = " . $newNebulaDrawingsExperienced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaDrawingsExperienced == 0 && $newNebulaDrawingsExperienced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangNebulaeSeen, 38), $this->getDrawMessage(LangNebulaeSeen, 38, $observerId));
  	}

  	$oldNebulaDrawingsAdvanced = $this->getNebulaDrawingsAdvanced($observerId);
  	$newNebulaDrawingsAdvanced = $NebulaDrawings[7];
  	$sql = "UPDATE accomplishments SET NebulaDrawingsAdvanced = " . $newNebulaDrawingsAdvanced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaDrawingsAdvanced == 0 && $newNebulaDrawingsAdvanced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangNebulaeSeen, 76), $this->getDrawMessage(LangNebulaeSeen, 76, $observerId));
  	}

  	$oldNebulaDrawingsSenior = $this->getNebulaDrawingsSenior($observerId);
  	$newNebulaDrawingsSenior = $NebulaDrawings[8];
  	$sql = "UPDATE accomplishments SET NebulaDrawingsSenior = " . $newNebulaDrawingsSenior . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaDrawingsSenior == 0 && $newNebulaDrawingsSenior == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangNebulaeSeen, 192), $this->getDrawMessage(LangNebulaeSeen, 192, $observerId));
  	}

  	$oldNebulaDrawingsExpert = $this->getNebulaDrawingsExpert($observerId);
  	$newNebulaDrawingsExpert = $NebulaDrawings[9];
  	$sql = "UPDATE accomplishments SET NebulaDrawingsExpert = " . $newNebulaDrawingsExpert . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldNebulaDrawingsExpert == 0 && $newNebulaDrawingsExpert == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangNebulaeSeen, 384), $this->getDrawMessage(LangNebulaeSeen, 384, $observerId));
  	}
  }

  public function recalculateObjects($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// Different Objects
  	$Objects = $this->calculateAccomplishments($observerId, "differentObjects", 10, false);
  	$oldObjectsNewbie = $this->getObjectsNewbie($observerId);
  	$newObjectsNewbie = $Objects[0];
  	$sql = "UPDATE accomplishments SET objectsNewbie = " . $newObjectsNewbie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsNewbie == 0 && $newObjectsNewbie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangObject, 1), $this->getSeenMessage(LangObject, 1, $observerId));
  	}

  	$oldObjectsRookie = $this->getObjectsRookie($observerId);
  	$newObjectsRookie = $Objects[1];
  	$sql = "UPDATE accomplishments SET objectsRookie = " . $newObjectsRookie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsRookie == 0 && $newObjectsRookie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangAccomplishmentsDifferentObjects, 10), $this->getSeenMessage(LangAccomplishmentsDifferentObjects, 10, $observerId));
  	}

  	$oldObjectsBeginner = $this->getObjectsBeginner($observerId);
  	$newObjectsBeginner = $Objects[2];
  	$sql = "UPDATE accomplishments SET objectsBeginner = " . $newObjectsBeginner . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsBeginner == 0 && $newObjectsBeginner == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangAccomplishmentsDifferentObjects, 25), $this->getSeenMessage(LangAccomplishmentsDifferentObjects, 25, $observerId));
  	}

  	$oldObjectsTalented = $this->getObjectsTalented($observerId);
  	$newObjectsTalented = $Objects[3];
  	$sql = "UPDATE accomplishments SET objectsTalented = " . $newObjectsTalented . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsTalented == 0 && $newObjectsTalented == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangAccomplishmentsDifferentObjects, 50), $this->getSeenMessage(LangAccomplishmentsDifferentObjects, 50, $observerId));
  	}

  	$oldObjectsSkilled = $this->getObjectsSkilled($observerId);
  	$newObjectsSkilled = $Objects[4];
  	$sql = "UPDATE accomplishments SET objectsSkilled = " . $newObjectsSkilled . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsSkilled == 0 && $newObjectsSkilled == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangAccomplishmentsDifferentObjects, 100), $this->getSeenMessage(LangAccomplishmentsDifferentObjects, 100, $observerId));
  	}

  	$oldObjectsIntermediate = $this->getObjectsIntermediate($observerId);
  	$newObjectsIntermediate = $Objects[5];
  	$sql = "UPDATE accomplishments SET objectsIntermediate = " . $newObjectsIntermediate . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsIntermediate == 0 && $newObjectsIntermediate == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangAccomplishmentsDifferentObjects, 250), $this->getSeenMessage(LangAccomplishmentsDifferentObjects, 250, $observerId));
  	}

  	$oldObjectsExperienced = $this->getObjectsExperienced($observerId);
  	$newObjectsExperienced = $Objects[6];
  	$sql = "UPDATE accomplishments SET objectsExperienced = " . $newObjectsExperienced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsExperienced == 0 && $newObjectsExperienced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangAccomplishmentsDifferentObjects, 500), $this->getSeenMessage(LangAccomplishmentsDifferentObjects, 500, $observerId));
  	}

  	$oldObjectsAdvanced = $this->getObjectsAdvanced($observerId);
  	$newObjectsAdvanced = $Objects[7];
  	$sql = "UPDATE accomplishments SET objectsAdvanced = " . $newObjectsAdvanced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsAdvanced == 0 && $newObjectsAdvanced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangAccomplishmentsDifferentObjects, 1000), $this->getSeenMessage(LangAccomplishmentsDifferentObjects, 1000, $observerId));
  	}

  	$oldObjectsSenior = $this->getObjectsSenior($observerId);
  	$newObjectsSenior = $Objects[8];
  	$sql = "UPDATE accomplishments SET objectsSenior = " . $newObjectsSenior . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsSenior == 0 && $newObjectsSenior == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangAccomplishmentsDifferentObjects, 2500), $this->getSeenMessage(LangAccomplishmentsDifferentObjects, 2500, $observerId));
  	}

  	$oldObjectsExpert = $this->getObjectsExpert($observerId);
  	$newObjectsExpert = $Objects[9];
  	$sql = "UPDATE accomplishments SET objectsExpert = " . $newObjectsExpert . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsExpert == 0 && $newObjectsExpert == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangAccomplishmentsDifferentObjects, 5000), $this->getSeenMessage(LangAccomplishmentsDifferentObjects, 5000, $observerId));
  	}
  }

  public function recalculateObjectDrawings($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// ObjectsDrawings
  	$ObjectsDrawings = $this->calculateAccomplishments($observerId, "differentObjects", 10, true);
  	$oldObjectsDrawingsNewbie = $this->getObjectsDrawingsNewbie($observerId);
  	$newObjectsDrawingsNewbie = $ObjectsDrawings[0];
  	$sql = "UPDATE accomplishments SET ObjectsDrawingsNewbie = " . $newObjectsDrawingsNewbie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);
  	if ($oldObjectsDrawingsNewbie == 0 && $newObjectsDrawingsNewbie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangObject, 1), $this->getDrawMessage(LangObject, 1, $observerId));
  	}

  	$oldObjectsDrawingsRookie = $this->getObjectsDrawingsRookie($observerId);
  	$newObjectsDrawingsRookie = $ObjectsDrawings[1];
  	$sql = "UPDATE accomplishments SET ObjectsDrawingsRookie = " . $newObjectsDrawingsRookie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsDrawingsRookie == 0 && $newObjectsDrawingsRookie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsDifferentObjects, 10), $this->getDrawMessage(LangAccomplishmentsDifferentObjects, 10, $observerId));
  	}

  	$oldObjectsDrawingsBeginner = $this->getObjectsDrawingsBeginner($observerId);
  	$newObjectsDrawingsBeginner = $ObjectsDrawings[2];
  	$sql = "UPDATE accomplishments SET ObjectsDrawingsBeginner = " . $newObjectsDrawingsBeginner . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsDrawingsBeginner == 0 && $newObjectsDrawingsBeginner == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsDifferentObjects, 25), $this->getDrawMessage(LangAccomplishmentsDifferentObjects, 25, $observerId));
  	}

  	$oldObjectsDrawingsTalented = $this->getObjectsDrawingsTalented($observerId);
  	$newObjectsDrawingsTalented = $ObjectsDrawings[3];
  	$sql = "UPDATE accomplishments SET ObjectsDrawingsTalented = " . $newObjectsDrawingsTalented . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsDrawingsTalented == 0 && $newObjectsDrawingsTalented == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsDifferentObjects, 50), $this->getDrawMessage(LangAccomplishmentsDifferentObjects, 50, $observerId));
  	}

  	$oldObjectsDrawingsSkilled = $this->getObjectsDrawingsSkilled($observerId);
  	$newObjectsDrawingsSkilled = $ObjectsDrawings[4];
  	$sql = "UPDATE accomplishments SET ObjectsDrawingsSkilled = " . $newObjectsDrawingsSkilled . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsDrawingsSkilled == 0 && $newObjectsDrawingsSkilled == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsDifferentObjects, 100), $this->getDrawMessage(LangAccomplishmentsDifferentObjects, 100, $observerId));
  	}

  	$oldObjectsDrawingsIntermediate = $this->getObjectsDrawingsIntermediate($observerId);
  	$newObjectsDrawingsIntermediate = $ObjectsDrawings[5];
  	$sql = "UPDATE accomplishments SET ObjectsDrawingsIntermediate = " . $newObjectsDrawingsIntermediate . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsDrawingsIntermediate == 0 && $newObjectsDrawingsIntermediate == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsDifferentObjects, 250), $this->getDrawMessage(LangAccomplishmentsDifferentObjects, 250, $observerId));
  	}

  	$oldObjectsDrawingsExperienced = $this->getObjectsDrawingsExperienced($observerId);
  	$newObjectsDrawingsExperienced = $ObjectsDrawings[6];
  	$sql = "UPDATE accomplishments SET ObjectsDrawingsExperienced = " . $newObjectsDrawingsExperienced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsDrawingsExperienced == 0 && $newObjectsDrawingsExperienced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsDifferentObjects, 500), $this->getDrawMessage(LangAccomplishmentsDifferentObjects, 500, $observerId));
  	}

  	$oldObjectsDrawingsAdvanced = $this->getObjectsDrawingsAdvanced($observerId);
  	$newObjectsDrawingsAdvanced = $ObjectsDrawings[7];
  	$sql = "UPDATE accomplishments SET ObjectsDrawingsAdvanced = " . $newObjectsDrawingsAdvanced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsDrawingsAdvanced == 0 && $newObjectsDrawingsAdvanced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsDifferentObjects, 1000), $this->getDrawMessage(LangAccomplishmentsDifferentObjects, 1000, $observerId));
  	}

  	$oldObjectsDrawingsSenior = $this->getObjectsDrawingsSenior($observerId);
  	$newObjectsDrawingsSenior = $ObjectsDrawings[8];
  	$sql = "UPDATE accomplishments SET objectsDrawingsSenior = " . $newObjectsDrawingsSenior . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsDrawingsSenior == 0 && $newObjectsDrawingsSenior == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsDifferentObjects, 2500), $this->getDrawMessage(LangAccomplishmentsDifferentObjects, 2500, $observerId));
  	}

  	$oldObjectsDrawingsExpert = $this->getObjectsDrawingsExpert($observerId);
  	$newObjectsDrawingsExpert = $ObjectsDrawings[9];
  	$sql = "UPDATE accomplishments SET objectsDrawingsExpert = " . $newObjectsDrawingsExpert . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldObjectsDrawingsExpert == 0 && $newObjectsDrawingsExpert == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsDifferentObjects, 5000), $this->getDrawMessage(LangAccomplishmentsDifferentObjects, 5000, $observerId));
  	}
  }

  public function recalculateCometObservations($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// Comet Observations
  	$CometObservations = $this->calculateAccomplishments($observerId, "cometObservations", 10, false);
  	$oldCometObservationsNewbie = $this->getCometObservationsNewbie($observerId);
  	$newCometObservationsNewbie = $CometObservations[0];
  	$sql = "UPDATE accomplishments SET CometObservationsNewbie = " . $newCometObservationsNewbie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometObservationsNewbie == 0 && $newCometObservationsNewbie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangComet, 1), $this->getSeenMessage(LangComet, 1, $observerId));
  	}

  	$oldCometObservationsRookie = $this->getCometObservationsRookie($observerId);
  	$newCometObservationsRookie = $CometObservations[1];
  	$sql = "UPDATE accomplishments SET CometObservationsRookie = " . $newCometObservationsRookie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometObservationsRookie == 0 && $newCometObservationsRookie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangComets, 10), $this->getSeenMessage(LangComets, 10, $observerId));
  	}

  	$oldCometObservationsBeginner = $this->getCometObservationsBeginner($observerId);
  	$newCometObservationsBeginner = $CometObservations[2];
  	$sql = "UPDATE accomplishments SET CometObservationsBeginner = " . $newCometObservationsBeginner . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometObservationsBeginner == 0 && $newCometObservationsBeginner == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangComets, 25), $this->getSeenMessage(LangComets, 25, $observerId));
  	}

  	$oldCometObservationsTalented = $this->getCometObservationsTalented($observerId);
  	$newCometObservationsTalented = $CometObservations[3];
  	$sql = "UPDATE accomplishments SET CometObservationsTalented = " . $newCometObservationsTalented . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometObservationsTalented == 0 && $newCometObservationsTalented == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangComets, 50), $this->getSeenMessage(LangComets, 50, $observerId));
  	}

  	$oldCometObservationsSkilled = $this->getCometObservationsSkilled($observerId);
  	$newCometObservationsSkilled = $CometObservations[4];
  	$sql = "UPDATE accomplishments SET CometObservationsSkilled = " . $newCometObservationsSkilled . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometObservationsSkilled == 0 && $newCometObservationsSkilled == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangComets, 100), $this->getSeenMessage(LangComets, 100, $observerId));
  	}

  	$oldCometObservationsIntermediate = $this->getCometObservationsIntermediate($observerId);
  	$newCometObservationsIntermediate = $CometObservations[5];
  	$sql = "UPDATE accomplishments SET CometObservationsIntermediate = " . $newCometObservationsIntermediate . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometObservationsIntermediate == 0 && $newCometObservationsIntermediate == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangComets, 250), $this->getSeenMessage(LangComets, 250, $observerId));
  	}

  	$oldCometObservationsExperienced = $this->getCometObservationsExperienced($observerId);
  	$newCometObservationsExperienced = $CometObservations[6];
  	$sql = "UPDATE accomplishments SET CometObservationsExperienced = " . $newCometObservationsExperienced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometObservationsExperienced == 0 && $newCometObservationsExperienced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangComets, 500), $this->getSeenMessage(LangComets, 500, $observerId));
  	}

  	$oldCometObservationsAdvanced = $this->getCometObservationsAdvanced($observerId);
  	$newCometObservationsAdvanced = $CometObservations[7];
  	$sql = "UPDATE accomplishments SET CometObservationsAdvanced = " . $newCometObservationsAdvanced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometObservationsAdvanced == 0 && $newCometObservationsAdvanced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangComets, 1000), $this->getSeenMessage(LangComets, 1000, $observerId));
  	}

  	$oldCometObservationsSenior = $this->getCometObservationsSenior($observerId);
  	$newCometObservationsSenior = $CometObservations[8];
  	$sql = "UPDATE accomplishments SET CometObservationsSenior = " . $newCometObservationsSenior . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometObservationsSenior == 0 && $newCometObservationsSenior == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangComets, 2500), $this->getSeenMessage(LangComets, 2500, $observerId));
  	}

  	$oldCometObservationsExpert = $this->getCometObservationsExpert($observerId);
  	$newCometObservationsExpert = $CometObservations[9];
  	$sql = "UPDATE accomplishments SET CometObservationsExpert = " . $newCometObservationsExpert . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometObservationsExpert == 0 && $newCometObservationsExpert == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangComets, 5000), $this->getSeenMessage(LangComets, 5000, $observerId));
  	}
  }

  public function recalculateCometsObserved($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// Comet Observations
  	$CometsObserved = $this->calculateAccomplishments($observerId, "cometsObserved", 10, false);
  	$oldCometsObservedNewbie = $this->getCometsObservedNewbie($observerId);
  	$newCometsObservedNewbie = $CometsObserved[0];
  	$sql = "UPDATE accomplishments SET CometsObservedNewbie = " . $newCometsObservedNewbie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometsObservedNewbie == 0 && $newCometsObservedNewbie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangComet, 1), $this->getSeenMessage(LangComet, 1, $observerId));
  	}

  	$oldCometsObservedRookie = $this->getCometsObservedRookie($observerId);
  	$newCometsObservedRookie = $CometsObserved[1];
  	$sql = "UPDATE accomplishments SET CometsObservedRookie = " . $newCometsObservedRookie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometsObservedRookie == 0 && $newCometsObservedRookie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangDifferentComets, 10), $this->getSeenMessage(LangDifferentComets, 10, $observerId));
  	}

  	$oldCometsObservedBeginner = $this->getCometsObservedBeginner($observerId);
  	$newCometsObservedBeginner = $CometsObserved[2];
  	$sql = "UPDATE accomplishments SET CometsObservedBeginner = " . $newCometsObservedBeginner . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometsObservedBeginner == 0 && $newCometsObservedBeginner == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangDifferentComets, 25), $this->getSeenMessage(LangDifferentComets, 25, $observerId));
  	}

  	$oldCometsObservedTalented = $this->getCometsObservedTalented($observerId);
  	$newCometsObservedTalented = $CometsObserved[3];
  	$sql = "UPDATE accomplishments SET CometsObservedTalented = " . $newCometsObservedTalented . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometsObservedTalented == 0 && $newCometsObservedTalented == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangDifferentComets, 50), $this->getSeenMessage(LangDifferentComets, 50, $observerId));
  	}

  	$oldCometsObservedSkilled = $this->getCometsObservedSkilled($observerId);
  	$newCometsObservedSkilled = $CometsObserved[4];
  	$sql = "UPDATE accomplishments SET CometsObservedSkilled = " . $newCometsObservedSkilled . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometsObservedSkilled == 0 && $newCometsObservedSkilled == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangDifferentComets, 100), $this->getSeenMessage(LangDifferentComets, 100, $observerId));
  	}

  	$oldCometsObservedIntermediate = $this->getCometsObservedIntermediate($observerId);
  	$newCometsObservedIntermediate = $CometsObserved[5];
  	$sql = "UPDATE accomplishments SET CometsObservedIntermediate = " . $newCometsObservedIntermediate . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometsObservedIntermediate == 0 && $newCometsObservedIntermediate == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangDifferentComets, 250), $this->getSeenMessage(LangDifferentComets, 250, $observerId));
  	}

  	$oldCometsObservedExperienced = $this->getCometsObservedExperienced($observerId);
  	$newCometsObservedExperienced = $CometsObserved[6];
  	$sql = "UPDATE accomplishments SET CometsObservedExperienced = " . $newCometsObservedExperienced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometsObservedExperienced == 0 && $newCometsObservedExperienced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangDifferentComets, 500), $this->getSeenMessage(LangDifferentComets, 500, $observerId));
  	}

  	$oldCometsObservedAdvanced = $this->getCometsObservedAdvanced($observerId);
  	$newCometsObservedAdvanced = $CometsObserved[7];
  	$sql = "UPDATE accomplishments SET CometsObservedAdvanced = " . $newCometsObservedAdvanced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometsObservedAdvanced == 0 && $newCometsObservedAdvanced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangDifferentComets, 1000), $this->getSeenMessage(LangDifferentComets, 1000, $observerId));
  	}

  	$oldCometsObservedSenior = $this->getCometsObservedSenior($observerId);
  	$newCometsObservedSenior = $CometsObserved[8];
  	$sql = "UPDATE accomplishments SET CometsObservedSenior = " . $newCometsObservedSenior . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometsObservedSenior == 0 && $newCometsObservedSenior == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangDifferentComets, 2500), $this->getSeenMessage(LangDifferentComets, 2500, $observerId));
  	}

  	$oldCometsObservedExpert = $this->getCometsObservedExpert($observerId);
  	$newCometsObservedExpert = $CometsObserved[9];
  	$sql = "UPDATE accomplishments SET CometsObservedExpert = " . $newCometsObservedExpert . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometsObservedExpert == 0 && $newCometsObservedExpert == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangDifferentComets, 5000), $this->getSeenMessage(LangDifferentComets, 5000, $observerId));
  	}
  }

  public function recalculateCometDrawings($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// Comet Observations
  	$CometDrawings = $this->calculateAccomplishments($observerId, "cometDrawings", 10, false);
  	$oldCometDrawingsNewbie = $this->getCometDrawingsNewbie($observerId);
  	$newCometDrawingsNewbie = $CometDrawings[0];
  	$sql = "UPDATE accomplishments SET CometDrawingsNewbie = " . $newCometDrawingsNewbie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometDrawingsNewbie == 0 && $newCometDrawingsNewbie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangComet, 1), $this->getDrawMessage(LangComet, 1, $observerId));
  	}

  	$oldCometDrawingsRookie = $this->getCometDrawingsRookie($observerId);
  	$newCometDrawingsRookie = $CometDrawings[1];
  	$sql = "UPDATE accomplishments SET CometDrawingsRookie = " . $newCometDrawingsRookie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometDrawingsRookie == 0 && $newCometDrawingsRookie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangComets, 10), $this->getDrawMessage(LangComets, 10, $observerId));
  	}

  	$oldCometDrawingsBeginner = $this->getCometDrawingsBeginner($observerId);
  	$newCometDrawingsBeginner = $CometDrawings[2];
  	$sql = "UPDATE accomplishments SET CometDrawingsBeginner = " . $newCometDrawingsBeginner . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometDrawingsBeginner == 0 && $newCometDrawingsBeginner == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangComets, 25), $this->getDrawMessage(LangComets, 25, $observerId));
  	}

  	$oldCometDrawingsTalented = $this->getCometDrawingsTalented($observerId);
  	$newCometDrawingsTalented = $CometDrawings[3];
  	$sql = "UPDATE accomplishments SET CometDrawingsTalented = " . $newCometDrawingsTalented . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometDrawingsTalented == 0 && $newCometDrawingsTalented == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangComets, 50), $this->getDrawMessage(LangComets, 50, $observerId));
  	}

  	$oldCometDrawingsSkilled = $this->getCometDrawingsSkilled($observerId);
  	$newCometDrawingsSkilled = $CometDrawings[4];
  	$sql = "UPDATE accomplishments SET CometDrawingsSkilled = " . $newCometDrawingsSkilled . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometDrawingsSkilled == 0 && $newCometDrawingsSkilled == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangComets, 100), $this->getDrawMessage(LangComets, 100, $observerId));
  	}

  	$oldCometDrawingsIntermediate = $this->getCometDrawingsIntermediate($observerId);
  	$newCometDrawingsIntermediate = $CometDrawings[5];
  	$sql = "UPDATE accomplishments SET CometDrawingsIntermediate = " . $newCometDrawingsIntermediate . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometDrawingsIntermediate == 0 && $newCometDrawingsIntermediate == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangComets, 250), $this->getDrawMessage(LangComets, 250, $observerId));
  	}

  	$oldCometDrawingsExperienced = $this->getCometDrawingsExperienced($observerId);
  	$newCometDrawingsExperienced = $CometDrawings[6];
  	$sql = "UPDATE accomplishments SET CometDrawingsExperienced = " . $newCometDrawingsExperienced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometDrawingsExperienced == 0 && $newCometDrawingsExperienced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangComets, 500), $this->getDrawMessage(LangComets, 500, $observerId));
  	}

  	$oldCometDrawingsAdvanced = $this->getCometDrawingsAdvanced($observerId);
  	$newCometDrawingsAdvanced = $CometDrawings[7];
  	$sql = "UPDATE accomplishments SET CometDrawingsAdvanced = " . $newCometDrawingsAdvanced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometDrawingsAdvanced == 0 && $newCometDrawingsAdvanced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangComets, 1000), $this->getDrawMessage(LangComets, 1000, $observerId));
  	}

  	$oldCometDrawingsSenior = $this->getCometDrawingsSenior($observerId);
  	$newCometDrawingsSenior = $CometDrawings[8];
  	$sql = "UPDATE accomplishments SET CometDrawingsSenior = " . $newCometDrawingsSenior . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometDrawingsSenior == 0 && $newCometDrawingsSenior == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangComets, 2500), $this->getDrawMessage(LangComets, 2500, $observerId));
  	}

  	$oldCometDrawingsExpert = $this->getCometDrawingsExpert($observerId);
  	$newCometDrawingsExpert = $CometDrawings[9];
  	$sql = "UPDATE accomplishments SET CometDrawingsExpert = " . $newCometDrawingsExpert . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCometDrawingsExpert == 0 && $newCometDrawingsExpert == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangComets, 5000), $this->getDrawMessage(LangComets, 5000, $observerId));
  	}
  }
}
?>
