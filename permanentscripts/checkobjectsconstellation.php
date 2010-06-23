<?php

$inIndex='true';

	require_once "../lib/setup/databaseInfo.php";
	require_once "../lib/database.php";
	require_once "../lib/util.php";
	//require_once "../lib/setup/language.php";
	require_once "../lib/setup/language/nl/lang_main.php";
	require_once "../lib/observers.php";
  require_once "../lib/observerqueries.php";
	require_once "../lib/setup/vars.php";
//	require_once "../common/control/loginuser.php";
	require_once "../lib/atlasses.php";
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
	require_once "../lib/cometobservations.php";
	require_once "../lib/cometobjects.php";
  require_once "../lib/presentation.php";
  require_once "../lib/constellations.php";
  require_once "../lib/formlayouts.php";
  require_once "../lib/reportlayouts.php";
  require_once "../lib/catalogs.php";
  require_once "../lib/moonphase.inc.php";
  require_once "../lib/astrocalc.php";
  
  $theobjects=$objDatabase->selectRecordsetArray("SELECT name AS objectname, con AS objectconstellation, ra AS objectra, decl AS objectdecl FROM objects;");
  echo "Checking ".($objCnt=count($theobjects))." objects.\n";
  echo "\n";
  $correct=0;
  for($i=0;$i<$objCnt;$i++)
  { if($theobjects[$i]['objectconstellation']==($newconstellation=$objConstellation->getConstellationFromCoordinates($theobjects[$i]['objectra'],$theobjects[$i]['objectdecl'])))
      $correct++;
    else
      echo "- ".$theobjects[$i]['objectname']." constellation ".$theobjects[$i]['objectconstellation']." should be ".$newconstellation."\n"; 
  }
  echo "\n";
  echo "Correct ".$correct.".\n";
  echo"\nEnd checking names.\n";

?>