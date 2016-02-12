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
   @param $catalog The catalog to use.
   @param $ranking The number of categories in the result
   @param $drawings True if the drawings should be calculated
   @return integer[] [ bronze, silver, gold ]
   */
  public function calculateAccomplishments($observer, $catalog, $ranking, $drawings = false)
  { global $objObservation;
    $objObservation = new Observations();
    if ($drawings) {
      $numberOfObjects = $objObservation->getDrawingsCountFromCatalog($observer,$catalog);
    } else {
      $numberOfObjects = $objObservation->getObservedCountFromCatalogOrList($observer,$catalog);
    }

    return $this->ranking($numberOfObjects, $ranking);
  }

  // Calculates the total number of drawings the observer has made and
  // returns an array [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  public function calculateDrawings($observer)
  { global $objObservation;
    $objObservation = new Observations();
    $drawingsMade = $objObservation->getDsDrawingsCountFromObserver($observer);

    return $this->ranking($drawingsMade, 10);
  }

  // Calculates the total number of comet observations the observer has made and
  // returns an array [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  public function calculateCometObservations($observer) {
    global $objObserver;
    $userCometobservation=$objObserver->getNumberOfCometObservations($observer);
    return $this->ranking($userCometobservation, 10);
  }

  // Calculates the number of different comet observed by the observer and
  // returns an array [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  public function calculateCometsObserved($observer) {
    global $objCometObservation;
    $userCometObjects = $objCometObservation->getNumberOfObjects($observer);
    return $this->ranking($userCometObjects, 10);
  }

  // Calculates the total number of comet drawings the observer has made and
  // returns an array [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  public function calculateCometDrawings($observer) {
    global $objCometObservation;
    $drawingsMade = $objCometObservation->getCometDrawingsCountFromObserver($observer);
    return $this->ranking($drawingsMade, 10);
  }

  // Calculates the number of different open clusters the observer has seen and
  // returns an array [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  public function calculateOpenClusters($observer)
  { global $objDatabase;
    $opncl = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"OPNCL\" and observations.observerid = \"" . $observer . "\""));
    $opncl += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"CLANB\" and observations.observerid = \"" . $observer . "\""));

    return $this->ranking($opncl, 10, 1700);
  }

  // Calculates the number of different open clusters the observer has drawn and
  // returns an array [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  public function calculateOpenClusterDrawings($observer)
  { global $objDatabase;
    $opnclDr = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"OPNCL\" and observations.observerid = \"" . $observer . "\" and observations.hasDrawing = 1"));
    $opnclDr += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"CLANB\" and observations.observerid = \"" . $observer . "\" and observations.hasDrawing = 1"));

    return $this->ranking($opnclDr, 10, 1700);
  }

  // Calculates the number of different globular clusters the observer has seen and
  // returns an array [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  public function calculateGlobularClusters($observer)
  { global $objDatabase;
    $glocl = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"GLOCL\" and observations.observerid = \"" . $observer . "\""));

    return $this->ranking($glocl, 10, 152);
  }

  // Calculates the number of different globular clusters the observer has drawn and
  // returns an array [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  public function calculateGlobularClusterDrawings($observer)
  { global $objDatabase;
    $gloclDr = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"GLOCL\" and observations.observerid = \"" . $observer . "\" and observations.hasDrawing = 1"));

    return $this->ranking($gloclDr, 10, 152);
  }

  // Calculates the number of different planetary nebulae the observer has seen and
  // returns an array [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  public function calculatePlanetaryNebulae($observer)
  { global $objDatabase;
    $plnnb = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"PLNNB\" and observations.observerid = \"" . $observer . "\""));

    return $this->ranking($plnnb, 10, 1023);
  }

  // Calculates the number of different planetary nebulae the observer has drawn and
  // returns an array [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  public function calculatePlanetaryNebulaDrawings($observer)
  { global $objDatabase;
    $plnnbDr = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"PLNNB\" and observations.observerid = \"" . $observer . "\" and observations.hasDrawing = 1"));

    return $this->ranking($plnnbDr, 10, 1023);
  }

  // Calculates the number of different galaxies the observer has seen and
  // returns an array [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  public function calculateGalaxies($observer)
  { global $objDatabase;
    $galxy = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"GALXY\" and observations.observerid = \"" . $observer . "\""));

    return $this->ranking($galxy, 10);
  }

  // Calculates the number of different galaxies the observer has drawn and
  // returns an array [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  public function calculateGalaxyDrawings($observer)
  { global $objDatabase;
    $galxyDr = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"GALXY\" and observations.observerid = \"" . $observer . "\" and observations.hasDrawing = 1"));

    return $this->ranking($galxyDr, 10);
  }

  // Calculates the number of different nebulae the observer has seen and
  // returns an array [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  public function calculateNebulae($observer)
  { global $objDatabase;

    $eminb = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"EMINB\" and observations.observerid = \"" . $observer . "\""));
    $eminb += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"ENRNN\" and observations.observerid = \"" . $observer . "\""));
    $eminb += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"ENSTR\" and observations.observerid = \"" . $observer . "\""));
    $eminb += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"REFNB\" and observations.observerid = \"" . $observer . "\""));
    $eminb += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"RNHII\" and observations.observerid = \"" . $observer . "\""));
    $eminb += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"HII\" and observations.observerid = \"" . $observer . "\""));
    $eminb += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"SNREM\" and observations.observerid = \"" . $observer . "\""));
    $eminb += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"WRNEB\" and observations.observerid = \"" . $observer . "\""));

    return $this->ranking($eminb, 10, 384);
  }

  // Calculates the number of different nebulae the observer has drawn and
  // returns an array [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  public function calculateNebulaDrawings($observer)
  { global $objDatabase;

    $eminbDr = count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"EMINB\" and observations.observerid = \"" . $observer . "\" and observations.hasDrawing = 1"));
    $eminbDr += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"ENRNN\" and observations.observerid = \"" . $observer . "\" and observations.hasDrawing = 1"));
    $eminbDr += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"ENSTR\" and observations.observerid = \"" . $observer . "\" and observations.hasDrawing = 1"));
    $eminbDr += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"REFNB\" and observations.observerid = \"" . $observer . "\" and observations.hasDrawing = 1"));
    $eminbDr += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"RNHII\" and observations.observerid = \"" . $observer . "\" and observations.hasDrawing = 1"));
    $eminbDr += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"HII\" and observations.observerid = \"" . $observer . "\" and observations.hasDrawing = 1"));
    $eminbDr += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"SNREM\" and observations.observerid = \"" . $observer . "\" and observations.hasDrawing = 1"));
    $eminbDr += count($objDatabase->selectRecordsetArray("select DISTINCT(objects.name) from objects,observations where objects.name = observations.objectname and objects.type = \"WRNEB\" and observations.observerid = \"" . $observer . "\" and observations.hasDrawing = 1"));

    return $this->ranking($eminbDr, 10, 384);
  }

  // Calculates the number of different objects the observer has seen and
  // returns an array [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  public function calculateDifferentObjects($observer)
  {
    $objObservation = new Observations();
    $totalDSobjects = $objObservation->getNumberOfObjects($observer);

    return $this->ranking($totalDSobjects, 10);
  }

  // Calculates the number of different objects the observer has drawn and
  // returns an array [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
  public function calculateDifferentObjectDrawings($observer)
  {
    $objObservation = new Observations();
    $totalDSDrawings = $objObservation->getNumberOfObjectDrawings($observer);

    return $this->ranking($totalDSDrawings, 10);
  }

  // Returns an boolean array with the accomplishments
  private function ranking($numberOfObjects, $categories, $total = 5000) {
  	if ($categories == 3) {
  		return $this->accomplishments3($numberOfObjects);
  	} else if ($categories == 5) {
  		return $this->accomplishments5($numberOfObjects);
  	} else {
  		return $this->accomplishments10($numberOfObjects, $total);
  	}
  }
  // Returns a boolean array for [ bronze, silver, gold ]
  private function accomplishments3($numberOfObjects) {
    return array( $numberOfObjects >= 25 ? 1:0, $numberOfObjects >= 50 ? 1:0,
             $numberOfObjects >= 110 ? 1:0 );
  }

  // Returns a boolean array for [ bronze, silver, gold, diamond, platina ]
  private function accomplishments5($numberOfObjects) {
    return array( $numberOfObjects >= 25 ? 1:0, $numberOfObjects >= 50 ? 1:0,
             $numberOfObjects >= 100 ? 1:0, $numberOfObjects >= 200 ? 1:0,
             $numberOfObjects >= 400 ? 1:0 );
  }

  // Returns a boolean array for [ Newbie, Rookie, Beginner, Talented, Skilled, Intermediate, Experienced, Advanced, Senior, Expert ]
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

  // Create an entry for a new observer in the accomplishments table
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

  // Delete an entry for a deleted observer in the accomplishments table
  public function deleteObserver($observerId) {
  	global $objDatabase;
  	$sql = "DELETE FROM accomplishments WHERE observer = \"". $observerId ."\");";
  	$objDatabase->execSQL($sql);
  }

  // Returns 1 if the observer has seen 25 messiers
  public function getMessierBronze($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select messierBronze from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["messierBronze"];
  }

  // Returns 1 if the observer has seen 50 messiers
  public function getMessierSilver($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select messierSilver from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["messierSilver"];
  }

  // Returns 1 if the observer has seen 110 messiers
  public function getMessierGold($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select messierGold from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["messierGold"];
  }

  // Returns 1 if the observer has drawn 25 messiers
  public function getMessierDrawingsBronze($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select messierDrawingsBronze from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["messierDrawingsBronze"];
  }

  // Returns 1 if the observer has drawn 50 messiers
  public function getMessierDrawingsSilver($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select messierDrawingsSilver from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["messierDrawingsSilver"];
  }

  // Returns 1 if the observer has drawn 110 messiers
  public function getMessierDrawingsGold($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select messierDrawingsGold from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["messierDrawingsGold"];
  }

  // Returns 1 if the observer has seen 25 Caldwells
  public function getCaldwellBronze($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CaldwellBronze from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CaldwellBronze"];
  }

  // Returns 1 if the observer has seen 50 Caldwells
  public function getCaldwellSilver($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CaldwellSilver from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CaldwellSilver"];
  }

  // Returns 1 if the observer has seen 110 Caldwells
  public function getCaldwellGold($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CaldwellGold from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CaldwellGold"];
  }

  // Returns 1 if the observer has drawn 25 Caldwells
  public function getCaldwellDrawingsBronze($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CaldwellDrawingsBronze from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CaldwellDrawingsBronze"];
  }

  // Returns 1 if the observer has drawn 50 Caldwells
  public function getCaldwellDrawingsSilver($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CaldwellDrawingsSilver from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CaldwellDrawingsSilver"];
  }

  // Returns 1 if the observer has drawn 110 Caldwells
  public function getCaldwellDrawingsGold($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select CaldwellDrawingsGold from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["CaldwellDrawingsGold"];
  }

  // Returns 1 if the observer has seen 25 Herschels
  public function getHerschelBronze($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelBronze from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelBronze"];
  }

  // Returns 1 if the observer has seen 50 Herschels
  public function getHerschelSilver($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelSilver from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelSilver"];
  }

  // Returns 1 if the observer has seen 100 Herschels
  public function getHerschelGold($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelGold from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelGold"];
  }

  // Returns 1 if the observer has seen 200 Herschels
  public function getHerschelDiamond($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelDiamond from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelDiamond"];
  }

  // Returns 1 if the observer has seen 400 Herschels
  public function getHerschelPlatina($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelPlatina from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelPlatina"];
  }

  // Returns 1 if the observer has drawn 25 Herschels
  public function getHerschelDrawingsBronze($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelDrawingsBronze from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelDrawingsBronze"];
  }

  // Returns 1 if the observer has drawn 50 Herschels
  public function getHerschelDrawingsSilver($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelDrawingsSilver from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelDrawingsSilver"];
  }

  // Returns 1 if the observer has drawn 100 Herschels
  public function getHerschelDrawingsGold($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelDrawingsGold from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelDrawingsGold"];
  }

  // Returns 1 if the observer has drawn 200 Herschels
  public function getHerschelDrawingsDiamond($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelDrawingsDiamond from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelDrawingsDiamond"];
  }

  // Returns 1 if the observer has drawn 400 Herschels
  public function getHerschelDrawingsPlatina($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelDrawingsPlatina from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelDrawingsPlatina"];
  }

  // Returns 1 if the observer has seen 25 HerschelIIs
  public function getHerschelIIBronze($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelIIBronze from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelIIBronze"];
  }

  // Returns 1 if the observer has seen 50 HerschelIIs
  public function getHerschelIISilver($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelIISilver from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelIISilver"];
  }

  // Returns 1 if the observer has seen 100 HerschelIIs
  public function getHerschelIIGold($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelIIGold from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelIIGold"];
  }

  // Returns 1 if the observer has seen 200 HerschelIIs
  public function getHerschelIIDiamond($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelIIDiamond from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelIIDiamond"];
  }

  // Returns 1 if the observer has seen 400 HerschelIIs
  public function getHerschelIIPlatina($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelIIPlatina from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelIIPlatina"];
  }

  // Returns 1 if the observer has drawn 25 HerschelIIs
  public function getHerschelIIDrawingsBronze($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelIIDrawingsBronze from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelIIDrawingsBronze"];
  }

  // Returns 1 if the observer has drawn 50 HerschelIIs
  public function getHerschelIIDrawingsSilver($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelIIDrawingsSilver from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelIIDrawingsSilver"];
  }

  // Returns 1 if the observer has drawn 100 HerschelIIs
  public function getHerschelIIDrawingsGold($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelIIDrawingsGold from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelIIDrawingsGold"];
  }

  // Returns 1 if the observer has drawn 200 HerschelIIs
  public function getHerschelIIDrawingsDiamond($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelIIDrawingsDiamond from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelIIDrawingsDiamond"];
  }

  // Returns 1 if the observer has drawn 400 HerschelIIs
  public function getHerschelIIDrawingsPlatina($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select HerschelIIDrawingsPlatina from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["HerschelIIDrawingsPlatina"];
  }

  // Returns 1 if the observer has one drawing
  public function getDrawingsNewbie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select DrawingsNewbie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["DrawingsNewbie"];
  }

  // Returns 1 if the observer has 10 drawings
  public function getDrawingsRookie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select DrawingsRookie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["DrawingsRookie"];
  }

  // Returns 1 if the observer has 25 drawings
  public function getDrawingsBeginner($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select DrawingsBeginner from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["DrawingsBeginner"];
  }

  // Returns 1 if the observer has 50 drawings
  public function getDrawingsTalented($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select DrawingsTalented from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["DrawingsTalented"];
  }

  // Returns 1 if the observer has 100 drawings
  public function getDrawingsSkilled($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select DrawingsSkilled from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["DrawingsSkilled"];
  }

  // Returns 1 if the observer has 250 drawings
  public function getDrawingsIntermediate($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select DrawingsIntermediate from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["DrawingsIntermediate"];
  }

  // Returns 1 if the observer has 500 drawings
  public function getDrawingsExperienced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select DrawingsExperienced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["DrawingsExperienced"];
  }

  // Returns 1 if the observer has 1000 drawings
  public function getDrawingsAdvanced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select DrawingsAdvanced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["DrawingsAdvanced"];
  }

  // Returns 1 if the observer has 2500 drawings
  public function getDrawingsSenior($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select DrawingsSenior from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["DrawingsSenior"];
  }

  // Returns 1 if the observer has 5000 drawings
  public function getDrawingsExpert($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select DrawingsExpert from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["DrawingsExpert"];
  }

  // Returns 1 if the observer has one open clusters
  public function getOpenClustersNewbie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterNewbie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterNewbie"];
  }

  // Returns 1 if the observer has 10 OpenClusters
  public function getOpenClustersRookie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterRookie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterRookie"];
  }

  // Returns 1 if the observer has 25 OpenClusters
  public function getOpenClustersBeginner($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterBeginner from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterBeginner"];
  }

  // Returns 1 if the observer has 50 OpenClusters
  public function getOpenClustersTalented($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterTalented from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterTalented"];
  }

  // Returns 1 if the observer has 100 OpenClusters
  public function getOpenClustersSkilled($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterSkilled from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterSkilled"];
  }

  // Returns 1 if the observer has 250 OpenClusters
  public function getOpenClustersIntermediate($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterIntermediate from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterIntermediate"];
  }

  // Returns 1 if the observer has 500 OpenClusters
  public function getOpenClustersExperienced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterExperienced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterExperienced"];
  }

  // Returns 1 if the observer has 1000 OpenClusters
  public function getOpenClustersAdvanced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterAdvanced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterAdvanced"];
  }

  // Returns 1 if the observer has 2500 OpenClusters
  public function getOpenClustersSenior($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterSenior from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterSenior"];
  }

  // Returns 1 if the observer has 5000 OpenClusters
  public function getOpenClustersExpert($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterExpert from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterExpert"];
  }

  // Returns 1 if the observer has one open clusters
  public function getOpenClusterDrawingsNewbie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterDrawingsNewbie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterDrawingsNewbie"];
  }

  // Returns 1 if the observer has 10 OpenClusterDrawings
  public function getOpenClusterDrawingsRookie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterDrawingsRookie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterDrawingsRookie"];
  }

  // Returns 1 if the observer has 25 OpenClusterDrawings
  public function getOpenClusterDrawingsBeginner($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterDrawingsBeginner from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterDrawingsBeginner"];
  }

  // Returns 1 if the observer has 50 OpenClusterDrawings
  public function getOpenClusterDrawingsTalented($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterDrawingsTalented from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterDrawingsTalented"];
  }

  // Returns 1 if the observer has 100 OpenClusterDrawings
  public function getOpenClusterDrawingsSkilled($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterDrawingsSkilled from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterDrawingsSkilled"];
  }

  // Returns 1 if the observer has 250 OpenClusterDrawings
  public function getOpenClusterDrawingsIntermediate($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterDrawingsIntermediate from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterDrawingsIntermediate"];
  }

  // Returns 1 if the observer has 500 OpenClusterDrawings
  public function getOpenClusterDrawingsExperienced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterDrawingsExperienced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterDrawingsExperienced"];
  }

  // Returns 1 if the observer has 1000 OpenClusterDrawings
  public function getOpenClusterDrawingsAdvanced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterDrawingsAdvanced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterDrawingsAdvanced"];
  }

  // Returns 1 if the observer has 2500 OpenClusterDrawings
  public function getOpenClusterDrawingsSenior($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterDrawingsSenior from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterDrawingsSenior"];
  }

  // Returns 1 if the observer has 5000 OpenClusterDrawings
  public function getOpenClusterDrawingsExpert($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select OpenClusterDrawingsExpert from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["OpenClusterDrawingsExpert"];
  }

  // Returns 1 if the observer has one Globular clusters
  public function getGlobularClustersNewbie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterNewbie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterNewbie"];
  }

  // Returns 1 if the observer has 10 GlobularClusters
  public function getGlobularClustersRookie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterRookie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterRookie"];
  }

  // Returns 1 if the observer has 25 GlobularClusters
  public function getGlobularClustersBeginner($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterBeginner from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterBeginner"];
  }

  // Returns 1 if the observer has 50 GlobularClusters
  public function getGlobularClustersTalented($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterTalented from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterTalented"];
  }

  // Returns 1 if the observer has 100 GlobularClusters
  public function getGlobularClustersSkilled($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterSkilled from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterSkilled"];
  }

  // Returns 1 if the observer has 250 GlobularClusters
  public function getGlobularClustersIntermediate($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterIntermediate from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterIntermediate"];
  }

  // Returns 1 if the observer has 500 GlobularClusters
  public function getGlobularClustersExperienced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterExperienced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterExperienced"];
  }

  // Returns 1 if the observer has 1000 GlobularClusters
  public function getGlobularClustersAdvanced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterAdvanced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterAdvanced"];
  }

  // Returns 1 if the observer has 2500 GlobularClusters
  public function getGlobularClustersSenior($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterSenior from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterSenior"];
  }

  // Returns 1 if the observer has 5000 GlobularClusters
  public function getGlobularClustersExpert($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterExpert from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterExpert"];
  }

  // Returns 1 if the observer has one Globular clusters
  public function getGlobularClusterDrawingsNewbie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterDrawingsNewbie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterDrawingsNewbie"];
  }

  // Returns 1 if the observer has 10 GlobularClusterDrawings
  public function getGlobularClusterDrawingsRookie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterDrawingsRookie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterDrawingsRookie"];
  }

  // Returns 1 if the observer has 25 GlobularClusterDrawings
  public function getGlobularClusterDrawingsBeginner($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterDrawingsBeginner from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterDrawingsBeginner"];
  }

  // Returns 1 if the observer has 50 GlobularClusterDrawings
  public function getGlobularClusterDrawingsTalented($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterDrawingsTalented from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterDrawingsTalented"];
  }

  // Returns 1 if the observer has 100 GlobularClusterDrawings
  public function getGlobularClusterDrawingsSkilled($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterDrawingsSkilled from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterDrawingsSkilled"];
  }

  // Returns 1 if the observer has 250 GlobularClusterDrawings
  public function getGlobularClusterDrawingsIntermediate($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterDrawingsIntermediate from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterDrawingsIntermediate"];
  }

  // Returns 1 if the observer has 500 GlobularClusterDrawings
  public function getGlobularClusterDrawingsExperienced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterDrawingsExperienced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterDrawingsExperienced"];
  }

  // Returns 1 if the observer has 1000 GlobularClusterDrawings
  public function getGlobularClusterDrawingsAdvanced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterDrawingsAdvanced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterDrawingsAdvanced"];
  }

  // Returns 1 if the observer has 2500 GlobularClusterDrawings
  public function getGlobularClusterDrawingsSenior($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterDrawingsSenior from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterDrawingsSenior"];
  }

  // Returns 1 if the observer has 5000 GlobularClusterDrawings
  public function getGlobularClusterDrawingsExpert($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GlobularClusterDrawingsExpert from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GlobularClusterDrawingsExpert"];
  }

  // Returns 1 if the observer has one planetary nebula
  public function getPlanetaryNebulaNewbie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaNewbie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaNewbie"];
  }

  // Returns 1 if the observer has 10 PlanetaryNebula
  public function getPlanetaryNebulaRookie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaRookie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaRookie"];
  }

  // Returns 1 if the observer has 25 PlanetaryNebula
  public function getPlanetaryNebulaBeginner($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaBeginner from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaBeginner"];
  }

  // Returns 1 if the observer has 50 PlanetaryNebula
  public function getPlanetaryNebulaTalented($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaTalented from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaTalented"];
  }

  // Returns 1 if the observer has 100 PlanetaryNebula
  public function getPlanetaryNebulaSkilled($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaSkilled from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaSkilled"];
  }

  // Returns 1 if the observer has 250 PlanetaryNebula
  public function getPlanetaryNebulaIntermediate($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaIntermediate from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaIntermediate"];
  }

  // Returns 1 if the observer has 500 PlanetaryNebula
  public function getPlanetaryNebulaExperienced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaExperienced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaExperienced"];
  }

  // Returns 1 if the observer has 1000 PlanetaryNebula
  public function getPlanetaryNebulaAdvanced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaAdvanced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaAdvanced"];
  }

  // Returns 1 if the observer has 2500 PlanetaryNebula
  public function getPlanetaryNebulaSenior($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaSenior from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaSenior"];
  }

  // Returns 1 if the observer has 5000 PlanetaryNebula
  public function getPlanetaryNebulaExpert($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaExpert from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaExpert"];
  }

  // Returns 1 if the observer has one Globular clusters
  public function getPlanetaryNebulaDrawingsNewbie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaDrawingsNewbie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaDrawingsNewbie"];
  }

  // Returns 1 if the observer has 10 PlanetaryNebulaDrawings
  public function getPlanetaryNebulaDrawingsRookie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaDrawingsRookie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaDrawingsRookie"];
  }

  // Returns 1 if the observer has 25 PlanetaryNebulaDrawings
  public function getPlanetaryNebulaDrawingsBeginner($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaDrawingsBeginner from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaDrawingsBeginner"];
  }

  // Returns 1 if the observer has 50 PlanetaryNebulaDrawings
  public function getPlanetaryNebulaDrawingsTalented($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaDrawingsTalented from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaDrawingsTalented"];
  }

  // Returns 1 if the observer has 100 PlanetaryNebulaDrawings
  public function getPlanetaryNebulaDrawingsSkilled($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaDrawingsSkilled from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaDrawingsSkilled"];
  }

  // Returns 1 if the observer has 250 PlanetaryNebulaDrawings
  public function getPlanetaryNebulaDrawingsIntermediate($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaDrawingsIntermediate from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaDrawingsIntermediate"];
  }

  // Returns 1 if the observer has 500 PlanetaryNebulaDrawings
  public function getPlanetaryNebulaDrawingsExperienced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaDrawingsExperienced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaDrawingsExperienced"];
  }

  // Returns 1 if the observer has 1000 PlanetaryNebulaDrawings
  public function getPlanetaryNebulaDrawingsAdvanced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaDrawingsAdvanced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaDrawingsAdvanced"];
  }

  // Returns 1 if the observer has 2500 PlanetaryNebulaDrawings
  public function getPlanetaryNebulaDrawingsSenior($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaDrawingsSenior from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaDrawingsSenior"];
  }

  // Returns 1 if the observer has 5000 PlanetaryNebulaDrawings
  public function getPlanetaryNebulaDrawingsExpert($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select PlanetaryNebulaDrawingsExpert from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["PlanetaryNebulaDrawingsExpert"];
  }

  // Returns 1 if the observer has one planetary nebula
  public function getGalaxyNewbie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyNewbie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyNewbie"];
  }

  // Returns 1 if the observer has 10 Galaxy
  public function getGalaxyRookie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyRookie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyRookie"];
  }

  // Returns 1 if the observer has 25 Galaxy
  public function getGalaxyBeginner($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyBeginner from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyBeginner"];
  }

  // Returns 1 if the observer has 50 Galaxy
  public function getGalaxyTalented($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyTalented from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyTalented"];
  }

  // Returns 1 if the observer has 100 Galaxy
  public function getGalaxySkilled($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxySkilled from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxySkilled"];
  }

  // Returns 1 if the observer has 250 Galaxy
  public function getGalaxyIntermediate($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyIntermediate from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyIntermediate"];
  }

  // Returns 1 if the observer has 500 Galaxy
  public function getGalaxyExperienced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyExperienced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyExperienced"];
  }

  // Returns 1 if the observer has 1000 Galaxy
  public function getGalaxyAdvanced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyAdvanced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyAdvanced"];
  }

  // Returns 1 if the observer has 2500 Galaxy
  public function getGalaxySenior($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxySenior from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxySenior"];
  }

  // Returns 1 if the observer has 5000 Galaxy
  public function getGalaxyExpert($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyExpert from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyExpert"];
  }

  // Returns 1 if the observer has one galaxy Drawing
  public function getGalaxyDrawingsNewbie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyDrawingsNewbie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyDrawingsNewbie"];
  }

  // Returns 1 if the observer has 10 GalaxyDrawings
  public function getGalaxyDrawingsRookie($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyDrawingsRookie from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyDrawingsRookie"];
  }

  // Returns 1 if the observer has 25 GalaxyDrawings
  public function getGalaxyDrawingsBeginner($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyDrawingsBeginner from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyDrawingsBeginner"];
  }

  // Returns 1 if the observer has 50 GalaxyDrawings
  public function getGalaxyDrawingsTalented($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyDrawingsTalented from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyDrawingsTalented"];
  }

  // Returns 1 if the observer has 100 GalaxyDrawings
  public function getGalaxyDrawingsSkilled($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyDrawingsSkilled from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyDrawingsSkilled"];
  }

  // Returns 1 if the observer has 250 GalaxyDrawings
  public function getGalaxyDrawingsIntermediate($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyDrawingsIntermediate from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyDrawingsIntermediate"];
  }

  // Returns 1 if the observer has 500 GalaxyDrawings
  public function getGalaxyDrawingsExperienced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyDrawingsExperienced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyDrawingsExperienced"];
  }

  // Returns 1 if the observer has 1000 GalaxyDrawings
  public function getGalaxyDrawingsAdvanced($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyDrawingsAdvanced from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyDrawingsAdvanced"];
  }

  // Returns 1 if the observer has 2500 GalaxyDrawings
  public function getGalaxyDrawingsSenior($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyDrawingsSenior from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyDrawingsSenior"];
  }

  // Returns 1 if the observer has 5000 GalaxyDrawings
  public function getGalaxyDrawingsExpert($observerId) {
  	global $objDatabase;
  	$recordArray = $objDatabase->selectRecordsetArray("select GalaxyDrawingsExpert from accomplishments where observer = \"". $observerId . "\";");
  	return $recordArray[0]["GalaxyDrawingsExpert"];
  }

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
  	global $objDatabase, $objMessages;
  	// MESSIER
  	$messiers = $this->calculateAccomplishments($observerId, "M", 3, false);
  	$oldMessierBronze = $this->getMessierBronze($observerId);
  	$newMessierBronze = $messiers[0];
  	$sql = "UPDATE accomplishments SET messierBronze = " . $newMessierBronze . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldMessierBronze == 0 && $newMessierBronze == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangMessier, 25), $this->getSeenMessage(LangMessier, 25, $observerId));
  	}

  	$oldMessierSilver = $this->getMessierSilver($observerId);
  	$newMessierSilver = $messiers[1];
  	$sql = "UPDATE accomplishments SET messierSilver = " . $newMessierSilver . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldMessierSilver == 0 && $newMessierSilver == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangMessier, 50), $this->getSeenMessage(LangMessier, 50, $observerId));
  	}

  	$oldMessierGold = $this->getMessierGold($observerId);
  	$newMessierGold = $messiers[2];
  	$sql = "UPDATE accomplishments SET messierGold = " . $newMessierGold . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldMessierGold == 0 && $newMessierGold == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangMessier, 110), $this->getSeenMessage(LangMessier, 110, $observerId));
  	}

  	// MESSIER DRAWINGS
  	$messierDrawings = $this->calculateAccomplishments($observerId, "M", 3, true);
  	$oldMessierDrawingsBronze = $this->getMessierDrawingsBronze($observerId);
  	$newMessierDrawingsBronze = $messierDrawings[0];
  	$sql = "UPDATE accomplishments SET messierDrawingsBronze = " . $newMessierDrawingsBronze . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldMessierDrawingsBronze == 0 && $newMessierDrawingsBronze == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangMessier, 25), $this->getDrawMessage(LangMessier, 25, $observerId));
  	}

  	$oldMessierDrawingsSilver = $this->getMessierDrawingsSilver($observerId);
  	$newMessierDrawingsSilver = $messierDrawings[1];
  	$sql = "UPDATE accomplishments SET messierDrawingsSilver = " . $newMessierDrawingsSilver . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldMessierDrawingsSilver == 0 && $newMessierDrawingsSilver == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangMessier, 50), $this->getDrawMessage(LangMessier, 50, $observerId));
  	}

  	$oldMessierDrawingsGold = $this->getMessierDrawingsGold($observerId);
  	$newMessierDrawingsGold = $messierDrawings[2];
  	$sql = "UPDATE accomplishments SET messierDrawingsGold = " . $newMessierDrawingsGold . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldMessierDrawingsGold == 0 && $newMessierDrawingsGold == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangMessier, 110), $this->getDrawMessage(LangMessier, 110, $observerId));
  	}
  }

  public function recalculateCaldwells($observerId) {
  	global $objDatabase, $objMessages;
  	// CALDWELL
  	$caldwells = $this->calculateAccomplishments($observerId, "Caldwell", 3, false);
  	$oldCaldwellBronze = $this->getCaldwellBronze($observerId);
  	$newCaldwellBronze = $caldwells[0];
  	$sql = "UPDATE accomplishments SET CaldwellBronze = " . $newCaldwellBronze . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCaldwellBronze == 0 && $newCaldwellBronze == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangCaldwell, 25), $this->getSeenMessage(LangCaldwell, 25, $observerId));
  	}

  	$oldCaldwellSilver = $this->getCaldwellSilver($observerId);
  	$newCaldwellSilver = $caldwells[1];
  	$sql = "UPDATE accomplishments SET CaldwellSilver = " . $newCaldwellSilver . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCaldwellSilver == 0 && $newCaldwellSilver == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangCaldwell, 50), $this->getSeenMessage(LangCaldwell, 50, $observerId));
  	}

  	$oldCaldwellGold = $this->getCaldwellGold($observerId);
  	$newCaldwellGold = $caldwells[2];
  	$sql = "UPDATE accomplishments SET CaldwellGold = " . $newCaldwellGold . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCaldwellGold == 0 && $newCaldwellGold == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangCaldwell, 110), $this->getSeenMessage(LangCaldwell, 110, $observerId));
  	}

  	// CALDWELL DRAWINGS
  	$caldwellDrawings = $this->calculateAccomplishmentsDrawings($observerId, "Caldwell", 3, true);
  	$oldCaldwellDrawingsBronze = $this->getCaldwellDrawingsBronze($observerId);
  	$newCaldwellDrawingsBronze = $caldwellDrawings[0];
  	$sql = "UPDATE accomplishments SET CaldwellDrawingsBronze = " . $newCaldwellDrawingsBronze . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCaldwellDrawingsBronze == 0 && $newCaldwellDrawingsBronze == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangCaldwell, 25), $this->getDrawMessage(LangCaldwell, 25, $observerId));
  	}

  	$oldCaldwellDrawingsSilver = $this->getCaldwellDrawingsSilver($observerId);
  	$newCaldwellDrawingsSilver = $caldwellDrawings[1];
  	$sql = "UPDATE accomplishments SET CaldwellDrawingsSilver = " . $newCaldwellDrawingsSilver . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCaldwellDrawingsSilver == 0 && $newCaldwellDrawingsSilver == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangCaldwell, 50), $this->getDrawMessage(LangCaldwell, 50, $observerId));
  	}

  	$oldCaldwellDrawingsGold = $this->getCaldwellDrawingsGold($observerId);
  	$newCaldwellDrawingsGold = $caldwellDrawings[2];
  	$sql = "UPDATE accomplishments SET CaldwellDrawingsGold = " . $newCaldwellDrawingsGold . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldCaldwellDrawingsGold == 0 && $newCaldwellDrawingsGold == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangCaldwell, 110), $this->getDrawMessage(LangCaldwell, 110, $observerId));
  	}

  }

  public function recalculateHerschels($observerId) {
  	global $objDatabase, $objMessages;
  	// Herschel
  	$herschels = $this->calculateAccomplishments($observerId, "H400", 5, false);
  	$oldHerschelBronze = $this->getHerschelBronze($observerId);
  	$newHerschelBronze = $herschels[0];
  	$sql = "UPDATE accomplishments SET HerschelBronze = " . $newHerschelBronze . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelBronze == 0 && $newHerschelBronze == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschel400, 25), $this->getSeenMessage(LangHerschel400, 25, $observerId));
  	}

  	$oldHerschelSilver = $this->getHerschelSilver($observerId);
  	$newHerschelSilver = $herschels[1];
  	$sql = "UPDATE accomplishments SET HerschelSilver = " . $newHerschelSilver . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelSilver == 0 && $newHerschelSilver == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschel400, 50), $this->getSeenMessage(LangHerschel400, 50, $observerId));
  	}

  	$oldHerschelGold = $this->getHerschelGold($observerId);
  	$newHerschelGold = $herschels[2];
  	$sql = "UPDATE accomplishments SET HerschelGold = " . $newHerschelGold . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelGold == 0 && $newHerschelGold == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschel400, 100), $this->getSeenMessage(LangHerschel400, 100, $observerId));
  	}

    $oldHerschelDiamond = $this->getHerschelDiamond($observerId);
  	$newHerschelDiamond = $herschels[3];
  	$sql = "UPDATE accomplishments SET HerschelDiamond = " . $newHerschelDiamond . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelDiamond == 0 && $newHerschelDiamond == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschel400, 200), $this->getSeenMessage(LangHerschel400, 200, $observerId));
  	}

    $oldHerschelPlatina = $this->getHerschelPlatina($observerId);
  	$newHerschelPlatina = $herschels[4];
  	$sql = "UPDATE accomplishments SET HerschelPlatina = " . $newHerschelPlatina . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelPlatina == 0 && $newHerschelPlatina == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschel400, 400), $this->getSeenMessage(LangHerschel400, 400, $observerId));
  	}

  	// Herschel DRAWINGS
  	$herschelDrawings = $this->calculateAccomplishmentsDrawings($observerId, "H400", 5, true);
  	$oldHerschelDrawingsBronze = $this->getHerschelDrawingsBronze($observerId);
  	$newHerschelDrawingsBronze = $herschelDrawings[0];
  	$sql = "UPDATE accomplishments SET HerschelDrawingsBronze = " . $newHerschelDrawingsBronze . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelDrawingsBronze == 0 && $newHerschelDrawingsBronze == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschel400, 25), $this->getDrawMessage(LangHerschel400, 25, $observerId));
  	}

  	$oldHerschelDrawingsSilver = $this->getHerschelDrawingsSilver($observerId);
  	$newHerschelDrawingsSilver = $herschelDrawings[1];
  	$sql = "UPDATE accomplishments SET HerschelDrawingsSilver = " . $newHerschelDrawingsSilver . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelDrawingsSilver == 0 && $newHerschelDrawingsSilver == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschel400, 50), $this->getDrawMessage(LangHerschel400, 50, $observerId));
  	}

  	$oldHerschelDrawingsGold = $this->getHerschelDrawingsGold($observerId);
  	$newHerschelDrawingsGold = $herschelDrawings[2];
  	$sql = "UPDATE accomplishments SET HerschelDrawingsGold = " . $newHerschelDrawingsGold . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelDrawingsGold == 0 && $newHerschelDrawingsGold == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschel400, 100), $this->getDrawMessage(LangHerschel400, 100, $observerId));
  	}

  	$oldHerschelDrawingsDiamond = $this->getHerschelDrawingsDiamond($observerId);
  	$newHerschelDrawingsDiamond = $herschelDrawings[3];
  	$sql = "UPDATE accomplishments SET HerschelDrawingsDiamond = " . $newHerschelDrawingsDiamond . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelDrawingsDiamond == 0 && $newHerschelDrawingsDiamond == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschel400, 200), $this->getDrawMessage(LangHerschel400, 200, $observerId));
  	}

  	$oldHerschelDrawingsPlatina = $this->getHerschelDrawingsPlatina($observerId);
  	$newHerschelDrawingsPlatina = $herschelDrawings[4];
  	$sql = "UPDATE accomplishments SET HerschelDrawingsPlatina = " . $newHerschelDrawingsPlatina . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelDrawingsPlatina == 0 && $newHerschelDrawingsPlatina == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschel400, 400), $this->getDrawMessage(LangHerschel400, 400, $observerId));
  	}
  }

  public function recalculateHerschelIIs($observerId) {
  	global $objDatabase, $objMessages;
  	// HerschelII
  	$HerschelIIs = $this->calculateAccomplishments($observerId, "HII", 5, false);
  	$oldHerschelIIBronze = $this->getHerschelIIBronze($observerId);
  	$newHerschelIIBronze = $HerschelIIs[0];
  	$sql = "UPDATE accomplishments SET HerschelIIBronze = " . $newHerschelIIBronze . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelIIBronze == 0 && $newHerschelIIBronze == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschelII, 25), $this->getSeenMessage(LangHerschelII, 25, $observerId));
  	}

  	$oldHerschelIISilver = $this->getHerschelIISilver($observerId);
  	$newHerschelIISilver = $HerschelIIs[1];
  	$sql = "UPDATE accomplishments SET HerschelIISilver = " . $newHerschelIISilver . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelIISilver == 0 && $newHerschelIISilver == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschelII, 50), $this->getSeenMessage(LangHerschelII, 50, $observerId));
  	}

  	$oldHerschelIIGold = $this->getHerschelIIGold($observerId);
  	$newHerschelIIGold = $HerschelIIs[2];
  	$sql = "UPDATE accomplishments SET HerschelIIGold = " . $newHerschelIIGold . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelIIGold == 0 && $newHerschelIIGold == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschelII, 100), $this->getSeenMessage(LangHerschelII, 100, $observerId));
  	}

  	$oldHerschelIIDiamond = $this->getHerschelIIDiamond($observerId);
  	$newHerschelIIDiamond = $HerschelIIs[3];
  	$sql = "UPDATE accomplishments SET HerschelIIDiamond = " . $newHerschelIIDiamond . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelIIDiamond == 0 && $newHerschelIIDiamond == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschelII, 200), $this->getSeenMessage(LangHerschelII, 200, $observerId));
  	}

  	$oldHerschelIIPlatina = $this->getHerschelIIPlatina($observerId);
  	$newHerschelIIPlatina = $HerschelIIs[4];
  	$sql = "UPDATE accomplishments SET HerschelIIPlatina = " . $newHerschelIIPlatina . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelIIPlatina == 0 && $newHerschelIIPlatina == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangHerschelII, 400), $this->getSeenMessage(LangHerschelII, 400, $observerId));
  	}

  	// HerschelII DRAWINGS
  	$HerschelIIDrawings = $this->calculateAccomplishmentsDrawings($observerId, "HII", 5, true);
  	$oldHerschelIIDrawingsBronze = $this->getHerschelIIDrawingsBronze($observerId);
  	$newHerschelIIDrawingsBronze = $HerschelIIDrawings[0];
  	$sql = "UPDATE accomplishments SET HerschelIIDrawingsBronze = " . $newHerschelIIDrawingsBronze . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelIIDrawingsBronze == 0 && $newHerschelIIDrawingsBronze == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschelII, 25), $this->getDrawMessage(LangHerschelII, 25, $observerId));
  	}

  	$oldHerschelIIDrawingsSilver = $this->getHerschelIIDrawingsSilver($observerId);
  	$newHerschelIIDrawingsSilver = $HerschelIIDrawings[1];
  	$sql = "UPDATE accomplishments SET HerschelIIDrawingsSilver = " . $newHerschelIIDrawingsSilver . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelIIDrawingsSilver == 0 && $newHerschelIIDrawingsSilver == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschelII, 50), $this->getDrawMessage(LangHerschelII, 50, $observerId));
  	}

  	$oldHerschelIIDrawingsGold = $this->getHerschelIIDrawingsGold($observerId);
  	$newHerschelIIDrawingsGold = $HerschelIIDrawings[2];
  	$sql = "UPDATE accomplishments SET HerschelIIDrawingsGold = " . $newHerschelIIDrawingsGold . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelIIDrawingsGold == 0 && $newHerschelIIDrawingsGold == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschelII, 100), $this->getDrawMessage(LangHerschelII, 100, $observerId));
  	}

  	$oldHerschelIIDrawingsDiamond = $this->getHerschelIIDrawingsDiamond($observerId);
  	$newHerschelIIDrawingsDiamond = $HerschelIIDrawings[3];
  	$sql = "UPDATE accomplishments SET HerschelIIDrawingsDiamond = " . $newHerschelIIDrawingsDiamond . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelIIDrawingsDiamond == 0 && $newHerschelIIDrawingsDiamond == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschelII, 200), $this->getDrawMessage(LangHerschelII, 200, $observerId));
  	}

  	$oldHerschelIIDrawingsPlatina = $this->getHerschelIIDrawingsPlatina($observerId);
  	$newHerschelIIDrawingsPlatina = $HerschelIIDrawings[4];
  	$sql = "UPDATE accomplishments SET HerschelIIDrawingsPlatina = " . $newHerschelIIDrawingsPlatina . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldHerschelIIDrawingsPlatina == 0 && $newHerschelIIDrawingsPlatina == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangHerschelII, 400), $this->getDrawMessage(LangHerschelII, 400, $observerId));
  	}
  }

  public function recalculateDrawings($observerId) {
  	global $objDatabase, $objMessages;
  	// drawings
  	$drawings = $this->calculateDrawings($observerId);
  	$oldDrawingsNewbie = $this->getDrawingsNewbie($observerId);
  	$newDrawingsNewbie = $drawings[0];
  	$sql = "UPDATE accomplishments SET drawingsNewbie = " . $newDrawingsNewbie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldDrawingsNewbie == 0 && $newDrawingsNewbie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangObject, 1), $this->getDrawMessage(LangObject, 1, $observerId));
  	}

  	$oldDrawingsRookie = $this->getDrawingsRookie($observerId);
  	$newDrawingsRookie = $drawings[1];
  	$sql = "UPDATE accomplishments SET drawingsRookie = " . $newDrawingsRookie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldDrawingsRookie == 0 && $newDrawingsRookie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 10), $this->getDrawMessage(LangAccomplishmentsObjects, 10, $observerId));
  	}

  	$oldDrawingsBeginner = $this->getDrawingsBeginner($observerId);
  	$newDrawingsBeginner = $drawings[2];
  	$sql = "UPDATE accomplishments SET drawingsBeginner = " . $newDrawingsBeginner . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldDrawingsBeginner == 0 && $newDrawingsBeginner == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 25), $this->getDrawMessage(LangAccomplishmentsObjects, 25, $observerId));
  	}

  	$oldDrawingsTalented = $this->getDrawingsTalented($observerId);
  	$newDrawingsTalented = $drawings[3];
  	$sql = "UPDATE accomplishments SET drawingsTalented = " . $newDrawingsTalented . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldDrawingsTalented == 0 && $newDrawingsTalented == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 50), $this->getDrawMessage(LangAccomplishmentsObjects, 50, $observerId));
  	}

 		$oldDrawingsSkilled = $this->getDrawingsSkilled($observerId);
  	$newDrawingsSkilled = $drawings[4];
  	$sql = "UPDATE accomplishments SET drawingsSkilled = " . $newDrawingsSkilled . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldDrawingsSkilled == 0 && $newDrawingsSkilled == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 100), $this->getDrawMessage(LangAccomplishmentsObjects, 100, $observerId));
  	}

  	$oldDrawingsIntermediate = $this->getDrawingsIntermediate($observerId);
  	$newDrawingsIntermediate = $drawings[5];
  	$sql = "UPDATE accomplishments SET drawingsIntermediate = " . $newDrawingsIntermediate . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldDrawingsIntermediate == 0 && $newDrawingsIntermediate == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 250), $this->getDrawMessage(LangAccomplishmentsObjects, 250, $observerId));
  	}

  	$oldDrawingsExperienced = $this->getDrawingsExperienced($observerId);
  	$newDrawingsExperienced = $drawings[6];
  	$sql = "UPDATE accomplishments SET drawingsExperienced = " . $newDrawingsExperienced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldDrawingsExperienced == 0 && $newDrawingsExperienced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 500), $this->getDrawMessage(LangAccomplishmentsObjects, 500, $observerId));
  	}

    $oldDrawingsAdvanced = $this->getDrawingsAdvanced($observerId);
  	$newDrawingsAdvanced = $drawings[7];
  	$sql = "UPDATE accomplishments SET drawingsAdvanced = " . $newDrawingsAdvanced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldDrawingsAdvanced == 0 && $newDrawingsAdvanced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 1000), $this->getDrawMessage(LangAccomplishmentsObjects, 1000, $observerId));
  	}

    $oldDrawingsSenior = $this->getDrawingsSenior($observerId);
  	$newDrawingsSenior = $drawings[8];
  	$sql = "UPDATE accomplishments SET drawingsSenior = " . $newDrawingsSenior . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldDrawingsSenior == 0 && $newDrawingsSenior == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 2500), $this->getDrawMessage(LangAccomplishmentsObjects, 2500, $observerId));
  	}

    $oldDrawingsExpert = $this->getDrawingsExpert($observerId);
  	$newDrawingsExpert = $drawings[9];
  	$sql = "UPDATE accomplishments SET drawingsExpert = " . $newDrawingsExpert . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldDrawingsExpert == 0 && $newDrawingsExpert == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangAccomplishmentsObjects, 5000), $this->getDrawMessage(LangAccomplishmentsObjects, 5000, $observerId));
  	}
  }

  public function recalculateOpenClusters($observerId) {
  	global $objDatabase, $objMessages;
  	// OpenClusters
  	$OpenClusters = $this->calculateOpenClusters($observerId);
  	$oldOpenClustersNewbie = $this->getOpenClustersNewbie($observerId);
  	$newOpenClustersNewbie = $OpenClusters[0];
  	$sql = "UPDATE accomplishments SET OpenClusterNewbie = " . $newOpenClustersNewbie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClustersNewbie == 0 && $newOpenClustersNewbie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenCluster, 1), $this->getSeenMessage(LangOpenCluster, 1, $observerId));
  	}

  	$oldOpenClustersRookie = $this->getOpenClustersRookie($observerId);
  	$newOpenClustersRookie = $OpenClusters[1];
  	$sql = "UPDATE accomplishments SET OpenClusterRookie = " . $newOpenClustersRookie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClustersRookie == 0 && $newOpenClustersRookie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenClusters, 10), $this->getSeenMessage(LangOpenClusters, 10, $observerId));
  	}

  	$oldOpenClustersBeginner = $this->getOpenClustersBeginner($observerId);
  	$newOpenClustersBeginner = $OpenClusters[2];
  	$sql = "UPDATE accomplishments SET OpenClusterBeginner = " . $newOpenClustersBeginner . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClustersBeginner == 0 && $newOpenClustersBeginner == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenClusters, 25), $this->getSeenMessage(LangOpenClusters, 25, $observerId));
  	}

  	$oldOpenClustersTalented = $this->getOpenClustersTalented($observerId);
  	$newOpenClustersTalented = $OpenClusters[3];
  	$sql = "UPDATE accomplishments SET OpenClusterTalented = " . $newOpenClustersTalented . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClustersTalented == 0 && $newOpenClustersTalented == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenClusters, 50), $this->getSeenMessage(LangOpenClusters, 50, $observerId));
  	}

  	$oldOpenClustersSkilled = $this->getOpenClustersSkilled($observerId);
  	$newOpenClustersSkilled = $OpenClusters[4];
  	$sql = "UPDATE accomplishments SET OpenClusterSkilled = " . $newOpenClustersSkilled . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClustersSkilled == 0 && $newOpenClustersSkilled == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenClusters, 100), $this->getSeenMessage(LangOpenClusters, 100, $observerId));
  	}

  	$oldOpenClustersIntermediate = $this->getOpenClustersIntermediate($observerId);
  	$newOpenClustersIntermediate = $OpenClusters[5];
  	$sql = "UPDATE accomplishments SET OpenClusterIntermediate = " . $newOpenClustersIntermediate . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClustersIntermediate == 0 && $newOpenClustersIntermediate == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenClusters, 250), $this->getSeenMessage(LangOpenClusters, 250, $observerId));
  	}

  	$oldOpenClustersExperienced = $this->getOpenClustersExperienced($observerId);
  	$newOpenClustersExperienced = $OpenClusters[6];
  	$sql = "UPDATE accomplishments SET OpenClusterExperienced = " . $newOpenClustersExperienced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClustersExperienced == 0 && $newOpenClustersExperienced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenClusters, 500), $this->getSeenMessage(LangOpenClusters, 500, $observerId));
  	}

  	$oldOpenClustersAdvanced = $this->getOpenClustersAdvanced($observerId);
  	$newOpenClustersAdvanced = $OpenClusters[7];
  	$sql = "UPDATE accomplishments SET OpenClusterAdvanced = " . $newOpenClustersAdvanced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClustersAdvanced == 0 && $newOpenClustersAdvanced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenClusters, 1000), $this->getSeenMessage(LangOpenClusters, 1000, $observerId));
  	}

  	$oldOpenClustersSenior = $this->getOpenClustersSenior($observerId);
  	$newOpenClustersSenior = $OpenClusters[8];
  	$sql = "UPDATE accomplishments SET OpenClusterSenior = " . $newOpenClustersSenior . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClustersSenior == 0 && $newOpenClustersSenior == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenClusters, 2500), $this->getSeenMessage(LangOpenClusters, 2500, $observerId));
  	}

  	$oldOpenClustersExpert = $this->getOpenClustersExpert($observerId);
  	$newOpenClustersExpert = $OpenClusters[9];
  	$sql = "UPDATE accomplishments SET OpenClusterExpert = " . $newOpenClustersExpert . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClustersExpert == 0 && $newOpenClustersExpert == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangOpenClusters, 5000), $this->getSeenMessage(LangOpenClusters, 5000, $observerId));
  	}
  }

  public function recalculateOpenClusterDrawings($observerId) {
  	global $objDatabase, $objMessages, $loggedUser;
  	// OpenClusterDrawings
  	$OpenClusterDrawings = $this->calculateOpenClusterDrawings($observerId);
  	$oldOpenClusterDrawingsNewbie = $this->getOpenClusterDrawingsNewbie($observerId);
  	$newOpenClusterDrawingsNewbie = $OpenClusterDrawings[0];
  	$sql = "UPDATE accomplishments SET OpenClusterDrawingsNewbie = " . $newOpenClusterDrawingsNewbie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClusterDrawingsNewbie == 0 && $newOpenClusterDrawingsNewbie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenCluster, 1), $this->getDrawMessage(LangOpenCluster, 1, $observerId));
  	}

  	$oldOpenClusterDrawingsRookie = $this->getOpenClusterDrawingsRookie($observerId);
  	$newOpenClusterDrawingsRookie = $OpenClusterDrawings[1];
  	$sql = "UPDATE accomplishments SET OpenClusterDrawingsRookie = " . $newOpenClusterDrawingsRookie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClusterDrawingsRookie == 0 && $newOpenClusterDrawingsRookie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenClusters, 10), $this->getDrawMessage(LangOpenClusters, 10, $observerId));
  	}

  	$oldOpenClusterDrawingsBeginner = $this->getOpenClusterDrawingsBeginner($observerId);
  	$newOpenClusterDrawingsBeginner = $OpenClusterDrawings[2];
  	$sql = "UPDATE accomplishments SET OpenClusterDrawingsBeginner = " . $newOpenClusterDrawingsBeginner . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClusterDrawingsBeginner == 0 && $newOpenClusterDrawingsBeginner == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenClusters, 25), $this->getDrawMessage(LangOpenClusters, 25, $observerId));
  	}

  	$oldOpenClusterDrawingsTalented = $this->getOpenClusterDrawingsTalented($observerId);
  	$newOpenClusterDrawingsTalented = $OpenClusterDrawings[3];
  	$sql = "UPDATE accomplishments SET OpenClusterDrawingsTalented = " . $newOpenClusterDrawingsTalented . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClusterDrawingsTalented == 0 && $newOpenClusterDrawingsTalented == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenClusters, 50), $this->getDrawMessage(LangOpenClusters, 50, $observerId));
  	}

  	$oldOpenClusterDrawingsSkilled = $this->getOpenClusterDrawingsSkilled($observerId);
  	$newOpenClusterDrawingsSkilled = $OpenClusterDrawings[4];
  	$sql = "UPDATE accomplishments SET OpenClusterDrawingsSkilled = " . $newOpenClusterDrawingsSkilled . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClusterDrawingsSkilled == 0 && $newOpenClusterDrawingsSkilled == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenClusters, 100), $this->getDrawMessage(LangOpenClusters, 100, $observerId));
  	}

  	$oldOpenClusterDrawingsIntermediate = $this->getOpenClusterDrawingsIntermediate($observerId);
  	$newOpenClusterDrawingsIntermediate = $OpenClusterDrawings[5];
  	$sql = "UPDATE accomplishments SET OpenClusterDrawingsIntermediate = " . $newOpenClusterDrawingsIntermediate . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClusterDrawingsIntermediate == 0 && $newOpenClusterDrawingsIntermediate == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenClusters, 250), $this->getDrawMessage(LangOpenClusters, 250, $observerId));
  	}

  	$oldOpenClusterDrawingsExperienced = $this->getOpenClusterDrawingsExperienced($observerId);
  	$newOpenClusterDrawingsExperienced = $OpenClusterDrawings[6];
  	$sql = "UPDATE accomplishments SET OpenClusterDrawingsExperienced = " . $newOpenClusterDrawingsExperienced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClusterDrawingsExperienced == 0 && $newOpenClusterDrawingsExperienced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenClusters, 500), $this->getDrawMessage(LangOpenClusters, 500, $observerId));
  	}

  	$oldOpenClusterDrawingsAdvanced = $this->getOpenClusterDrawingsAdvanced($observerId);
  	$newOpenClusterDrawingsAdvanced = $OpenClusterDrawings[7];
  	$sql = "UPDATE accomplishments SET OpenClusterDrawingsAdvanced = " . $newOpenClusterDrawingsAdvanced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClusterDrawingsAdvanced == 0 && $newOpenClusterDrawingsAdvanced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenClusters, 1000), $this->getDrawMessage(LangOpenClusters, 1000, $observerId));
  	}

  	$oldOpenClusterDrawingsSenior = $this->getOpenClusterDrawingsSenior($observerId);
  	$newOpenClusterDrawingsSenior = $OpenClusterDrawings[8];
  	$sql = "UPDATE accomplishments SET OpenClusterDrawingsSenior = " . $newOpenClusterDrawingsSenior . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClusterDrawingsSenior == 0 && $newOpenClusterDrawingsSenior == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenClusters, 2500), $this->getDrawMessage(LangOpenClusters, 2500, $observerId));
  	}

  	$oldOpenClusterDrawingsExpert = $this->getOpenClusterDrawingsExpert($observerId);
  	$newOpenClusterDrawingsExpert = $OpenClusterDrawings[9];
  	$sql = "UPDATE accomplishments SET OpenClusterDrawingsExpert = " . $newOpenClusterDrawingsExpert . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldOpenClusterDrawingsExpert == 0 && $newOpenClusterDrawingsExpert == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangOpenClusters, 5000), $this->getDrawMessage(LangOpenClusters, 5000, $observerId));
  	}
  }

  public function recalculateGlobularClusters($observerId) {
  	global $objDatabase, $objMessages;
  	// GlobularClusters
  	$GlobularClusters = $this->calculateGlobularClusters($observerId);
  	$oldGlobularClustersNewbie = $this->getGlobularClustersNewbie($observerId);
  	$newGlobularClustersNewbie = $GlobularClusters[0];
  	$sql = "UPDATE accomplishments SET GlobularClusterNewbie = " . $newGlobularClustersNewbie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClustersNewbie == 0 && $newGlobularClustersNewbie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularCluster, 1), $this->getSeenMessage(LangGlobularCluster, 1, $observerId));
  	}

  	$oldGlobularClustersRookie = $this->getGlobularClustersRookie($observerId);
  	$newGlobularClustersRookie = $GlobularClusters[1];
  	$sql = "UPDATE accomplishments SET GlobularClusterRookie = " . $newGlobularClustersRookie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClustersRookie == 0 && $newGlobularClustersRookie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularClusters, 2), $this->getSeenMessage(LangGlobularClusters, 2, $observerId));
  	}

  	$oldGlobularClustersBeginner = $this->getGlobularClustersBeginner($observerId);
  	$newGlobularClustersBeginner = $GlobularClusters[2];
  	$sql = "UPDATE accomplishments SET GlobularClusterBeginner = " . $newGlobularClustersBeginner . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClustersBeginner == 0 && $newGlobularClustersBeginner == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularClusters, 3), $this->getSeenMessage(LangGlobularClusters, 3, $observerId));
  	}

  	$oldGlobularClustersTalented = $this->getGlobularClustersTalented($observerId);
  	$newGlobularClustersTalented = $GlobularClusters[3];
  	$sql = "UPDATE accomplishments SET GlobularClusterTalented = " . $newGlobularClustersTalented . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClustersTalented == 0 && $newGlobularClustersTalented == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularClusters, 4), $this->getSeenMessage(LangGlobularClusters, 4, $observerId));
  	}

  	$oldGlobularClustersSkilled = $this->getGlobularClustersSkilled($observerId);
  	$newGlobularClustersSkilled = $GlobularClusters[4];
  	$sql = "UPDATE accomplishments SET GlobularClusterSkilled = " . $newGlobularClustersSkilled . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClustersSkilled == 0 && $newGlobularClustersSkilled == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularClusters, 5), $this->getSeenMessage(LangGlobularClusters, 5, $observerId));
  	}

  	$oldGlobularClustersIntermediate = $this->getGlobularClustersIntermediate($observerId);
  	$newGlobularClustersIntermediate = $GlobularClusters[5];
  	$sql = "UPDATE accomplishments SET GlobularClusterIntermediate = " . $newGlobularClustersIntermediate . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClustersIntermediate == 0 && $newGlobularClustersIntermediate == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularClusters, 7), $this->getSeenMessage(LangGlobularClusters, 7, $observerId));
  	}

  	$oldGlobularClustersExperienced = $this->getGlobularClustersExperienced($observerId);
  	$newGlobularClustersExperienced = $GlobularClusters[6];
  	$sql = "UPDATE accomplishments SET GlobularClusterExperienced = " . $newGlobularClustersExperienced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClustersExperienced == 0 && $newGlobularClustersExperienced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularClusters, 15), $this->getSeenMessage(LangGlobularClusters, 15, $observerId));
  	}

  	$oldGlobularClustersAdvanced = $this->getGlobularClustersAdvanced($observerId);
  	$newGlobularClustersAdvanced = $GlobularClusters[7];
  	$sql = "UPDATE accomplishments SET GlobularClusterAdvanced = " . $newGlobularClustersAdvanced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClustersAdvanced == 0 && $newGlobularClustersAdvanced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularClusters, 30), $this->getSeenMessage(LangGlobularClusters, 30, $observerId));
  	}

  	$oldGlobularClustersSenior = $this->getGlobularClustersSenior($observerId);
  	$newGlobularClustersSenior = $GlobularClusters[8];
  	$sql = "UPDATE accomplishments SET GlobularClusterSenior = " . $newGlobularClustersSenior . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClustersSenior == 0 && $newGlobularClustersSenior == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularClusters, 76), $this->getSeenMessage(LangGlobularClusters, 76, $observerId));
  	}

  	$oldGlobularClustersExpert = $this->getGlobularClustersExpert($observerId);
  	$newGlobularClustersExpert = $GlobularClusters[9];
  	$sql = "UPDATE accomplishments SET GlobularClusterExpert = " . $newGlobularClustersExpert . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClustersExpert == 0 && $newGlobularClustersExpert == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGlobularClusters, 152), $this->getSeenMessage(LangGlobularClusters, 152, $observerId));
  	}
  }

  public function recalculateGlobularClusterDrawings($observerId) {
  	global $objDatabase, $objMessages;
  	// GlobularClusterDrawings
  	$GlobularClusterDrawings = $this->calculateGlobularClusterDrawings($observerId);
  	$oldGlobularClusterDrawingsNewbie = $this->getGlobularClusterDrawingsNewbie($observerId);
  	$newGlobularClusterDrawingsNewbie = $GlobularClusterDrawings[0];
  	$sql = "UPDATE accomplishments SET GlobularClusterDrawingsNewbie = " . $newGlobularClusterDrawingsNewbie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClusterDrawingsNewbie == 0 && $newGlobularClusterDrawingsNewbie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularCluster, 1), $this->getDrawMessage(LangGlobularCluster, 1, $observerId));
  	}

  	$oldGlobularClusterDrawingsRookie = $this->getGlobularClusterDrawingsRookie($observerId);
  	$newGlobularClusterDrawingsRookie = $GlobularClusterDrawings[1];
  	$sql = "UPDATE accomplishments SET GlobularClusterDrawingsRookie = " . $newGlobularClusterDrawingsRookie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClusterDrawingsRookie == 0 && $newGlobularClusterDrawingsRookie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularClusters, 2), $this->getDrawMessage(LangGlobularClusters, 2, $observerId));
  	}

  	$oldGlobularClusterDrawingsBeginner = $this->getGlobularClusterDrawingsBeginner($observerId);
  	$newGlobularClusterDrawingsBeginner = $GlobularClusterDrawings[2];
  	$sql = "UPDATE accomplishments SET GlobularClusterDrawingsBeginner = " . $newGlobularClusterDrawingsBeginner . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClusterDrawingsBeginner == 0 && $newGlobularClusterDrawingsBeginner == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularClusters, 3), $this->getDrawMessage(LangGlobularClusters, 3, $observerId));
  	}

  	$oldGlobularClusterDrawingsTalented = $this->getGlobularClusterDrawingsTalented($observerId);
  	$newGlobularClusterDrawingsTalented = $GlobularClusterDrawings[3];
  	$sql = "UPDATE accomplishments SET GlobularClusterDrawingsTalented = " . $newGlobularClusterDrawingsTalented . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClusterDrawingsTalented == 0 && $newGlobularClusterDrawingsTalented == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularClusters, 4), $this->getDrawMessage(LangGlobularClusters, 4, $observerId));
  	}

  	$oldGlobularClusterDrawingsSkilled = $this->getGlobularClusterDrawingsSkilled($observerId);
  	$newGlobularClusterDrawingsSkilled = $GlobularClusterDrawings[4];
  	$sql = "UPDATE accomplishments SET GlobularClusterDrawingsSkilled = " . $newGlobularClusterDrawingsSkilled . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClusterDrawingsSkilled == 0 && $newGlobularClusterDrawingsSkilled == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularClusters, 5), $this->getDrawMessage(LangGlobularClusters, 5, $observerId));
  	}

  	$oldGlobularClusterDrawingsIntermediate = $this->getGlobularClusterDrawingsIntermediate($observerId);
  	$newGlobularClusterDrawingsIntermediate = $GlobularClusterDrawings[5];
  	$sql = "UPDATE accomplishments SET GlobularClusterDrawingsIntermediate = " . $newGlobularClusterDrawingsIntermediate . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClusterDrawingsIntermediate == 0 && $newGlobularClusterDrawingsIntermediate == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularClusters, 7), $this->getDrawMessage(LangGlobularClusters, 7, $observerId));
  	}

  	$oldGlobularClusterDrawingsExperienced = $this->getGlobularClusterDrawingsExperienced($observerId);
  	$newGlobularClusterDrawingsExperienced = $GlobularClusterDrawings[6];
  	$sql = "UPDATE accomplishments SET GlobularClusterDrawingsExperienced = " . $newGlobularClusterDrawingsExperienced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClusterDrawingsExperienced == 0 && $newGlobularClusterDrawingsExperienced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularClusters, 15), $this->getDrawMessage(LangGlobularClusters, 15, $observerId));
  	}

  	$oldGlobularClusterDrawingsAdvanced = $this->getGlobularClusterDrawingsAdvanced($observerId);
  	$newGlobularClusterDrawingsAdvanced = $GlobularClusterDrawings[7];
  	$sql = "UPDATE accomplishments SET GlobularClusterDrawingsAdvanced = " . $newGlobularClusterDrawingsAdvanced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClusterDrawingsAdvanced == 0 && $newGlobularClusterDrawingsAdvanced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularClusters, 30), $this->getDrawMessage(LangGlobularClusters, 30, $observerId));
  	}

  	$oldGlobularClusterDrawingsSenior = $this->getGlobularClusterDrawingsSenior($observerId);
  	$newGlobularClusterDrawingsSenior = $GlobularClusterDrawings[8];
  	$sql = "UPDATE accomplishments SET GlobularClusterDrawingsSenior = " . $newGlobularClusterDrawingsSenior . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClusterDrawingsSenior == 0 && $newGlobularClusterDrawingsSenior == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularClusters, 76), $this->getDrawMessage(LangGlobularClusters, 76, $observerId));
  	}

  	$oldGlobularClusterDrawingsExpert = $this->getGlobularClusterDrawingsExpert($observerId);
  	$newGlobularClusterDrawingsExpert = $GlobularClusterDrawings[9];
  	$sql = "UPDATE accomplishments SET GlobularClusterDrawingsExpert = " . $newGlobularClusterDrawingsExpert . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGlobularClusterDrawingsExpert == 0 && $newGlobularClusterDrawingsExpert == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGlobularClusters, 152), $this->getDrawMessage(LangGlobularClusters, 152, $observerId));
  	}
  }

  public function recalculatePlanetaryNebulae($observerId) {
  	global $objDatabase, $objMessages;
  	// PlanetaryNebulae
  	$PlanetaryNebulae = $this->calculatePlanetaryNebulae($observerId);
  	$oldPlanetaryNebulaeNewbie = $this->getPlanetaryNebulaNewbie($observerId);
  	$newPlanetaryNebulaeNewbie = $PlanetaryNebulae[0];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaNewbie = " . $newPlanetaryNebulaeNewbie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaeNewbie == 0 && $newPlanetaryNebulaeNewbie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebula, 1), $this->getSeenMessage(LangPlanetaryNebula, 1, $observerId));
  	}

  	$oldPlanetaryNebulaeRookie = $this->getPlanetaryNebulaRookie($observerId);
  	$newPlanetaryNebulaeRookie = $PlanetaryNebulae[1];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaRookie = " . $newPlanetaryNebulaeRookie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaeRookie == 0 && $newPlanetaryNebulaeRookie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebulaeSeen, 2), $this->getSeenMessage(LangPlanetaryNebulaeSeen, 2, $observerId));
  	}

  	$oldPlanetaryNebulaeBeginner = $this->getPlanetaryNebulaBeginner($observerId);
  	$newPlanetaryNebulaeBeginner = $PlanetaryNebulae[2];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaBeginner = " . $newPlanetaryNebulaeBeginner . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaeBeginner == 0 && $newPlanetaryNebulaeBeginner == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebulaeSeen, 5), $this->getSeenMessage(LangPlanetaryNebulaeSeen, 5, $observerId));
  	}

  	$oldPlanetaryNebulaeTalented = $this->getPlanetaryNebulaTalented($observerId);
  	$newPlanetaryNebulaeTalented = $PlanetaryNebulae[3];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaTalented = " . $newPlanetaryNebulaeTalented . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaeTalented == 0 && $newPlanetaryNebulaeTalented == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebulaeSeen, 10), $this->getSeenMessage(LangPlanetaryNebulaeSeen, 10, $observerId));
  	}

  	$oldPlanetaryNebulaeSkilled = $this->getPlanetaryNebulaSkilled($observerId);
  	$newPlanetaryNebulaeSkilled = $PlanetaryNebulae[4];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaSkilled = " . $newPlanetaryNebulaeSkilled . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaeSkilled == 0 && $newPlanetaryNebulaeSkilled == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebulaeSeen, 20), $this->getSeenMessage(LangPlanetaryNebulaeSeen, 20, $observerId));
  	}

  	$oldPlanetaryNebulaeIntermediate = $this->getPlanetaryNebulaIntermediate($observerId);
  	$newPlanetaryNebulaeIntermediate = $PlanetaryNebulae[5];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaIntermediate = " . $newPlanetaryNebulaeIntermediate . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaeIntermediate == 0 && $newPlanetaryNebulaeIntermediate == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebulaeSeen, 51), $this->getSeenMessage(LangPlanetaryNebulaeSeen, 51, $observerId));
  	}

  	$oldPlanetaryNebulaeExperienced = $this->getPlanetaryNebulaExperienced($observerId);
  	$newPlanetaryNebulaeExperienced = $PlanetaryNebulae[6];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaExperienced = " . $newPlanetaryNebulaeExperienced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaeExperienced == 0 && $newPlanetaryNebulaeExperienced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebulaeSeen, 102), $this->getSeenMessage(LangPlanetaryNebulaeSeen, 102, $observerId));
  	}

  	$oldPlanetaryNebulaeAdvanced = $this->getPlanetaryNebulaAdvanced($observerId);
  	$newPlanetaryNebulaeAdvanced = $PlanetaryNebulae[7];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaAdvanced = " . $newPlanetaryNebulaeAdvanced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaeAdvanced == 0 && $newPlanetaryNebulaeAdvanced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebulaeSeen, 204), $this->getSeenMessage(LangPlanetaryNebulaeSeen, 204, $observerId));
  	}

  	$oldPlanetaryNebulaeSenior = $this->getPlanetaryNebulaSenior($observerId);
  	$newPlanetaryNebulaeSenior = $PlanetaryNebulae[8];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaSenior = " . $newPlanetaryNebulaeSenior . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaeSenior == 0 && $newPlanetaryNebulaeSenior == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebulaeSeen, 511), $this->getSeenMessage(LangPlanetaryNebulaeSeen, 511, $observerId));
  	}

  	$oldPlanetaryNebulaeExpert = $this->getPlanetaryNebulaExpert($observerId);
  	$newPlanetaryNebulaeExpert = $PlanetaryNebulae[9];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaExpert = " . $newPlanetaryNebulaeExpert . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaeExpert == 0 && $newPlanetaryNebulaeExpert == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangPlanetaryNebulaeSeen, 1023), $this->getSeenMessage(LangPlanetaryNebulaeSeen, 1023, $observerId));
  	}
  }

  public function recalculatePlanetaryNebulaDrawings($observerId) {
  	global $objDatabase, $objMessages;
  	// PlanetaryNebulaDrawings
  	$PlanetaryNebulaDrawings = $this->calculatePlanetaryNebulaDrawings($observerId);
  	$oldPlanetaryNebulaDrawingsNewbie = $this->getPlanetaryNebulaDrawingsNewbie($observerId);
  	$newPlanetaryNebulaDrawingsNewbie = $PlanetaryNebulaDrawings[0];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsNewbie = " . $newPlanetaryNebulaDrawingsNewbie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaDrawingsNewbie == 0 && $newPlanetaryNebulaDrawingsNewbie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebula, 1), $this->getDrawMessage(LangPlanetaryNebula, 1, $observerId));
  	}

  	$oldPlanetaryNebulaDrawingsRookie = $this->getPlanetaryNebulaDrawingsRookie($observerId);
  	$newPlanetaryNebulaDrawingsRookie = $PlanetaryNebulaDrawings[1];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsRookie = " . $newPlanetaryNebulaDrawingsRookie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaDrawingsRookie == 0 && $newPlanetaryNebulaDrawingsRookie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebulaeSeen, 2), $this->getDrawMessage(LangPlanetaryNebulaeSeen, 2, $observerId));
  	}

  	$oldPlanetaryNebulaDrawingsBeginner = $this->getPlanetaryNebulaDrawingsBeginner($observerId);
  	$newPlanetaryNebulaDrawingsBeginner = $PlanetaryNebulaDrawings[2];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsBeginner = " . $newPlanetaryNebulaDrawingsBeginner . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaDrawingsBeginner == 0 && $newPlanetaryNebulaDrawingsBeginner == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebulaeSeen, 5), $this->getDrawMessage(LangPlanetaryNebulaeSeen, 5, $observerId));
  	}

  	$oldPlanetaryNebulaDrawingsTalented = $this->getPlanetaryNebulaDrawingsTalented($observerId);
  	$newPlanetaryNebulaDrawingsTalented = $PlanetaryNebulaDrawings[3];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsTalented = " . $newPlanetaryNebulaDrawingsTalented . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaDrawingsTalented == 0 && $newPlanetaryNebulaDrawingsTalented == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebulaeSeen, 10), $this->getDrawMessage(LangPlanetaryNebulaeSeen, 10, $observerId));
  	}

  	$oldPlanetaryNebulaDrawingsSkilled = $this->getPlanetaryNebulaDrawingsSkilled($observerId);
  	$newPlanetaryNebulaDrawingsSkilled = $PlanetaryNebulaDrawings[4];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsSkilled = " . $newPlanetaryNebulaDrawingsSkilled . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaDrawingsSkilled == 0 && $newPlanetaryNebulaDrawingsSkilled == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebulaeSeen, 20), $this->getDrawMessage(LangPlanetaryNebulaeSeen, 20, $observerId));
  	}

  	$oldPlanetaryNebulaDrawingsIntermediate = $this->getPlanetaryNebulaDrawingsIntermediate($observerId);
  	$newPlanetaryNebulaDrawingsIntermediate = $PlanetaryNebulaDrawings[5];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsIntermediate = " . $newPlanetaryNebulaDrawingsIntermediate . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaDrawingsIntermediate == 0 && $newPlanetaryNebulaDrawingsIntermediate == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebulaeSeen, 51), $this->getDrawMessage(LangPlanetaryNebulaeSeen, 51, $observerId));
  	}

  	$oldPlanetaryNebulaDrawingsExperienced = $this->getPlanetaryNebulaDrawingsExperienced($observerId);
  	$newPlanetaryNebulaDrawingsExperienced = $PlanetaryNebulaDrawings[6];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsExperienced = " . $newPlanetaryNebulaDrawingsExperienced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaDrawingsExperienced == 0 && $newPlanetaryNebulaDrawingsExperienced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebulaeSeen, 102), $this->getDrawMessage(LangPlanetaryNebulaeSeen, 102, $observerId));
  	}

  	$oldPlanetaryNebulaDrawingsAdvanced = $this->getPlanetaryNebulaDrawingsAdvanced($observerId);
  	$newPlanetaryNebulaDrawingsAdvanced = $PlanetaryNebulaDrawings[7];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsAdvanced = " . $newPlanetaryNebulaDrawingsAdvanced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaDrawingsAdvanced == 0 && $newPlanetaryNebulaDrawingsAdvanced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebulaeSeen, 204), $this->getDrawMessage(LangPlanetaryNebulaeSeen, 204, $observerId));
  	}

  	$oldPlanetaryNebulaDrawingsSenior = $this->getPlanetaryNebulaDrawingsSenior($observerId);
  	$newPlanetaryNebulaDrawingsSenior = $PlanetaryNebulaDrawings[8];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsSenior = " . $newPlanetaryNebulaDrawingsSenior . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaDrawingsSenior == 0 && $newPlanetaryNebulaDrawingsSenior == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebulaeSeen, 511), $this->getDrawMessage(LangPlanetaryNebulaeSeen, 511, $observerId));
  	}

  	$oldPlanetaryNebulaDrawingsExpert = $this->getPlanetaryNebulaDrawingsExpert($observerId);
  	$newPlanetaryNebulaDrawingsExpert = $PlanetaryNebulaDrawings[9];
  	$sql = "UPDATE accomplishments SET PlanetaryNebulaDrawingsExpert = " . $newPlanetaryNebulaDrawingsExpert . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldPlanetaryNebulaDrawingsExpert == 0 && $newPlanetaryNebulaDrawingsExpert == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangPlanetaryNebulaeSeen, 1023), $this->getDrawMessage(LangPlanetaryNebulaeSeen, 1023, $observerId));
  	}
  }

  public function recalculateGalaxies($observerId) {
  	global $objDatabase, $objMessages;
  	// Galaxies
  	$Galaxies = $this->calculateGalaxies($observerId);
  	$oldGalaxiesNewbie = $this->getGalaxyNewbie($observerId);
  	$newGalaxiesNewbie = $Galaxies[0];
  	$sql = "UPDATE accomplishments SET GalaxyNewbie = " . $newGalaxiesNewbie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxiesNewbie == 0 && $newGalaxiesNewbie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxy, 1), $this->getSeenMessage(LangGalaxy, 1, $observerId));
  	}

  	$oldGalaxiesRookie = $this->getGalaxyRookie($observerId);
  	$newGalaxiesRookie = $Galaxies[1];
  	$sql = "UPDATE accomplishments SET GalaxyRookie = " . $newGalaxiesRookie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxiesRookie == 0 && $newGalaxiesRookie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxiesSeen, 10), $this->getSeenMessage(LangGalaxiesSeen, 10, $observerId));
  	}

  	$oldGalaxiesBeginner = $this->getGalaxyBeginner($observerId);
  	$newGalaxiesBeginner = $Galaxies[2];
  	$sql = "UPDATE accomplishments SET GalaxyBeginner = " . $newGalaxiesBeginner . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxiesBeginner == 0 && $newGalaxiesBeginner == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxiesSeen, 25), $this->getSeenMessage(LangGalaxiesSeen, 25, $observerId));
  	}

  	$oldGalaxiesTalented = $this->getGalaxyTalented($observerId);
  	$newGalaxiesTalented = $Galaxies[3];
  	$sql = "UPDATE accomplishments SET GalaxyTalented = " . $newGalaxiesTalented . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxiesTalented == 0 && $newGalaxiesTalented == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxiesSeen, 50), $this->getSeenMessage(LangGalaxiesSeen, 50, $observerId));
  	}

  	$oldGalaxiesSkilled = $this->getGalaxySkilled($observerId);
  	$newGalaxiesSkilled = $Galaxies[4];
  	$sql = "UPDATE accomplishments SET GalaxySkilled = " . $newGalaxiesSkilled . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxiesSkilled == 0 && $newGalaxiesSkilled == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxiesSeen, 100), $this->getSeenMessage(LangGalaxiesSeen, 100, $observerId));
  	}

  	$oldGalaxiesIntermediate = $this->getGalaxyIntermediate($observerId);
  	$newGalaxiesIntermediate = $Galaxies[5];
  	$sql = "UPDATE accomplishments SET GalaxyIntermediate = " . $newGalaxiesIntermediate . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxiesIntermediate == 0 && $newGalaxiesIntermediate == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxiesSeen, 250), $this->getSeenMessage(LangGalaxiesSeen, 250, $observerId));
  	}

  	$oldGalaxiesExperienced = $this->getGalaxyExperienced($observerId);
  	$newGalaxiesExperienced = $Galaxies[6];
  	$sql = "UPDATE accomplishments SET GalaxyExperienced = " . $newGalaxiesExperienced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxiesExperienced == 0 && $newGalaxiesExperienced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxiesSeen, 500), $this->getSeenMessage(LangGalaxiesSeen, 500, $observerId));
  	}

  	$oldGalaxiesAdvanced = $this->getGalaxyAdvanced($observerId);
  	$newGalaxiesAdvanced = $Galaxies[7];
  	$sql = "UPDATE accomplishments SET GalaxyAdvanced = " . $newGalaxiesAdvanced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxiesAdvanced == 0 && $newGalaxiesAdvanced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxiesSeen, 1000), $this->getSeenMessage(LangGalaxiesSeen, 1000, $observerId));
  	}

  	$oldGalaxiesSenior = $this->getGalaxySenior($observerId);
  	$newGalaxiesSenior = $Galaxies[8];
  	$sql = "UPDATE accomplishments SET GalaxySenior = " . $newGalaxiesSenior . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxiesSenior == 0 && $newGalaxiesSenior == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxiesSeen, 2500), $this->getSeenMessage(LangGalaxiesSeen, 2500, $observerId));
  	}

  	$oldGalaxiesExpert = $this->getGalaxyExpert($observerId);
  	$newGalaxiesExpert = $Galaxies[9];
  	$sql = "UPDATE accomplishments SET GalaxyExpert = " . $newGalaxiesExpert . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxiesExpert == 0 && $newGalaxiesExpert == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getSeenSubject(LangGalaxiesSeen, 5000), $this->getSeenMessage(LangGalaxiesSeen, 5000, $observerId));
  	}
  }

  public function recalculateGalaxyDrawings($observerId) {
  	global $objDatabase, $objMessages;
  	// GalaxyDrawings
  	$GalaxyDrawings = $this->calculateGalaxyDrawings($observerId);
  	$oldGalaxyDrawingsNewbie = $this->getGalaxyDrawingsNewbie($observerId);
  	$newGalaxyDrawingsNewbie = $GalaxyDrawings[0];
  	$sql = "UPDATE accomplishments SET GalaxyDrawingsNewbie = " . $newGalaxyDrawingsNewbie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxyDrawingsNewbie == 0 && $newGalaxyDrawingsNewbie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxy, 1), $this->getDrawMessage(LangGalaxy, 1, $observerId));
  	}

  	$oldGalaxyDrawingsRookie = $this->getGalaxyDrawingsRookie($observerId);
  	$newGalaxyDrawingsRookie = $GalaxyDrawings[1];
  	$sql = "UPDATE accomplishments SET GalaxyDrawingsRookie = " . $newGalaxyDrawingsRookie . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxyDrawingsRookie == 0 && $newGalaxyDrawingsRookie == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxiesSeen, 10), $this->getDrawMessage(LangGalaxiesSeen, 10, $observerId));
  	}

  	$oldGalaxyDrawingsBeginner = $this->getGalaxyDrawingsBeginner($observerId);
  	$newGalaxyDrawingsBeginner = $GalaxyDrawings[2];
  	$sql = "UPDATE accomplishments SET GalaxyDrawingsBeginner = " . $newGalaxyDrawingsBeginner . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxyDrawingsBeginner == 0 && $newGalaxyDrawingsBeginner == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxiesSeen, 25), $this->getDrawMessage(LangGalaxiesSeen, 25, $observerId));
  	}

  	$oldGalaxyDrawingsTalented = $this->getGalaxyDrawingsTalented($observerId);
  	$newGalaxyDrawingsTalented = $GalaxyDrawings[3];
  	$sql = "UPDATE accomplishments SET GalaxyDrawingsTalented = " . $newGalaxyDrawingsTalented . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxyDrawingsTalented == 0 && $newGalaxyDrawingsTalented == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxiesSeen, 50), $this->getDrawMessage(LangGalaxiesSeen, 50, $observerId));
  	}

  	$oldGalaxyDrawingsSkilled = $this->getGalaxyDrawingsSkilled($observerId);
  	$newGalaxyDrawingsSkilled = $GalaxyDrawings[4];
  	$sql = "UPDATE accomplishments SET GalaxyDrawingsSkilled = " . $newGalaxyDrawingsSkilled . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxyDrawingsSkilled == 0 && $newGalaxyDrawingsSkilled == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxiesSeen, 100), $this->getDrawMessage(LangGalaxiesSeen, 100, $observerId));
  	}

  	$oldGalaxyDrawingsIntermediate = $this->getGalaxyDrawingsIntermediate($observerId);
  	$newGalaxyDrawingsIntermediate = $GalaxyDrawings[5];
  	$sql = "UPDATE accomplishments SET GalaxyDrawingsIntermediate = " . $newGalaxyDrawingsIntermediate . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxyDrawingsIntermediate == 0 && $newGalaxyDrawingsIntermediate == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxiesSeen, 250), $this->getDrawMessage(LangGalaxiesSeen, 250, $observerId));
  	}

  	$oldGalaxyDrawingsExperienced = $this->getGalaxyDrawingsExperienced($observerId);
  	$newGalaxyDrawingsExperienced = $GalaxyDrawings[6];
  	$sql = "UPDATE accomplishments SET GalaxyDrawingsExperienced = " . $newGalaxyDrawingsExperienced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxyDrawingsExperienced == 0 && $newGalaxyDrawingsExperienced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxiesSeen, 500), $this->getDrawMessage(LangGalaxiesSeen, 500, $observerId));
  	}

  	$oldGalaxyDrawingsAdvanced = $this->getGalaxyDrawingsAdvanced($observerId);
  	$newGalaxyDrawingsAdvanced = $GalaxyDrawings[7];
  	$sql = "UPDATE accomplishments SET GalaxyDrawingsAdvanced = " . $newGalaxyDrawingsAdvanced . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxyDrawingsAdvanced == 0 && $newGalaxyDrawingsAdvanced == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxiesSeen, 1000), $this->getDrawMessage(LangGalaxiesSeen, 1000, $observerId));
  	}

  	$oldGalaxyDrawingsSenior = $this->getGalaxyDrawingsSenior($observerId);
  	$newGalaxyDrawingsSenior = $GalaxyDrawings[8];
  	$sql = "UPDATE accomplishments SET GalaxyDrawingsSenior = " . $newGalaxyDrawingsSenior . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxyDrawingsSenior == 0 && $newGalaxyDrawingsSenior == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxiesSeen, 2500), $this->getDrawMessage(LangGalaxiesSeen, 2500, $observerId));
  	}

  	$oldGalaxyDrawingsExpert = $this->getGalaxyDrawingsExpert($observerId);
  	$newGalaxyDrawingsExpert = $GalaxyDrawings[9];
  	$sql = "UPDATE accomplishments SET GalaxyDrawingsExpert = " . $newGalaxyDrawingsExpert . " WHERE observer = \"". $observerId ."\";";
  	$objDatabase->execSQL($sql);

  	if ($oldGalaxyDrawingsExpert == 0 && $newGalaxyDrawingsExpert == 1) {
  		$objMessages->sendMessage('DeepskyLog', $loggedUser, $this->getDrawSubject(LangGalaxiesSeen, 5000), $this->getDrawMessage(LangGalaxiesSeen, 5000, $observerId));
  	}
  }

  public function recalculateNebulae($observerId) {
  	global $objDatabase, $objMessages;
  	// Nebula
  	$Nebula = $this->calculateNebulae($observerId);
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
  	global $objDatabase, $objMessages;
  	// NebulaDrawings
  	$NebulaDrawings = $this->calculateNebulaDrawings($observerId);
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
  	global $objDatabase, $objMessages;
  	// Different Objects
  	$Objects = $this->calculateDifferentObjects($observerId);
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
  	global $objDatabase, $objMessages;
  	// ObjectsDrawings
  	$ObjectsDrawings = $this->calculateDifferentObjectDrawings($observerId);
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
  	global $objDatabase, $objMessages;
  	// Comet Observations
  	$CometObservations = $this->calculateCometObservations($observerId);
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
  	global $objDatabase, $objMessages;
  	// Comet Observations
  	$CometsObserved = $this->calculateCometsObserved($observerId);
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
  	global $objDatabase, $objMessages;
  	// Comet Observations
  	$CometDrawings = $this->calculateCometDrawings($observerId);
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
