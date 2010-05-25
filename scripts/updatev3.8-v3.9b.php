<?php

$inIndex='true';

	require_once "../lib/setup/databaseInfo.php";
	require_once "../lib/database.php";
	require_once "../lib/util.php";
	require_once "../lib/observers.php";
	require_once "../lib/setup/vars.php";
	require_once "../lib/locations.php";
	require_once "../lib/instruments.php";
	require_once "../lib/filters.php";
	require_once "../lib/lenses.php";
	require_once "../lib/contrast.php";
	require_once "../lib/eyepieces.php";
	require_once "../lib/observations.php";
	require_once "../lib/lists.php";
	require_once "../lib/objects.php";
  require_once "../lib/astrocalc.php";
	require_once "../lib/stars.php";
	include_once "../lib/cometobservations.php";
	include_once "../lib/cometobjects.php";
  include_once '../lib/presentation.php';
  include_once '../lib/constellations.php';
  include_once '../lib/formlayouts.php';
  include_once '../lib/reportlayouts.php';
  include_once '../lib/catalogs.php';

  
  echo "Deleting faulty object or objectnames.\n";
  $objDatabase->execSQL("DELETE FROM objectnames WHERE catindex=\"+0-12-53\";");
  $objDatabase->execSQL("DELETE FROM objects     WHERE name='MCG 8-31-3A';");
  $objObject->removeAndReplaceObjectBy('Feinstein 1','Fein','1');
  $objObject->newName('Markarian 38','Mrk','38');
  $objObject->removeAndReplaceObjectBy('Markarian 205','Mrk','205');
  $objObject->newName('Markarian 756','Mrk','756');
  $objObject->newName('Markarian 829','Mrk','829');
  $objObject->newName('Markarian 839','Mrk','839');
  $objObject->newName('Markarian 897','Mrk','897');
  $objObject->removeAltName('HICKSON 82A','NPM','1G+32.0473');
  $objObject->newAltName('HICKSON 82A','NPM1G','+32.0473');  
  $objObject->removeAltName('Stock 2','ST','2');
  
  
  echo "Checking names:\n";
  $objObject->checknames();
  echo"\nEnd checking names.\n";

?>