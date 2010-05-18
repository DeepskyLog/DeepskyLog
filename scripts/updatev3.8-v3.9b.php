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

  echo "Deleting faulty object on objectnames (MCG +0-12-53)\n";
  $objDatabase->execSQL("DELETE FROM objectnames WHERE catindex=\"+0-12-53\";");
  
  echo "Checking names:\n";
  echo $objObject->checknames();
  echo"\nEnd checking names.\n";

?>