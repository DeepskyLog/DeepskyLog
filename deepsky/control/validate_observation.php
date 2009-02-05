<?php

if (!array_key_exists('deepskylog_id', $_SESSION) || !$_SESSION['deepskylog_id'])
	throw new Exception("Not logged in");
elseif ($GLOBALS['objUtil']->checkArrayKey($_SESSION, 'addObs',0)!=$GLOBALS['objUtil']->checkPostKey('timestamp', -1)) 
{ $_GET['indexAction'] = "default_action";
	$_GET['dalm'] = 'D';
	//$_GET['observation']=$current_observation;
}
elseif ((!$_POST['day']) || (!$_POST['month']) || (!$_POST['year']) || ($_POST['site'] == "1") || (!$_POST['instrument']) || (!$_POST['description'])) {
	if ($GLOBALS['objUtil']->checkPostKey('limit'))
		if (ereg('([0-9]{1})[.,]{0,1}([0-9]{0,1})', $_POST['limit'], $matches)) // limiting magnitude like X.X or X,X with X a number between 0 and 9
			$_POST['limit'] = $matches[1] . "." . (($matches[2]) ? $matches[2] : "0");
		else
			$_POST['limit'] = ""; // clear current magnitude limit
	else
		if ($GLOBALS['objUtil']->checkPostKey('sqm'))
			if (ereg('([0-9]{1})([0-9]{1})[.,]{0,1}([0-9]{0,1})', $_POST['sqm'], $matches)) // sqm value
				$_POST['sqm'] = $matches[1] . $matches[2] . "." . (($matches[3]) ? $matches[3] : "0");
			else
				$_POST['sqm'] = ""; // clear current magnitude limit
	else {
		$_POST['limit'] = "";
		$_POST['sqm'] = "";
	}
	$entryMessage .= LangValidateObservationMessage1;
	$_GET['indexAction'] = 'add_observation';
} 
else // all fields filled in
{ $time = -9999;
  if ($_POST['hours']) 
  { if (isset ($_POST['minutes']))
		  $time = ($_POST['hours'] * 100) + $_POST['minutes'];
		else
			$time = ($_POST['hours'] * 100);
	} 
  if ($_FILES['drawing']['size'] > $maxFileSize) // file size of drawing too big
	{ $entryMessage .= LangValidateObservationMessage6;
		$_GET['indexAction'] = 'add_observation';
	} 
  elseif((!is_numeric($_POST['month']))||(!is_numeric($_POST['day']))||(!is_numeric($_POST['year']))||(!checkdate($_POST['month'],$_POST['day'],$_POST['year']))) {
  	$entryMessage .= LangValidateObservationMessage2;
		$_GET['indexAction'] = 'add_observation';
  }
  elseif(($date=$_POST['year'].sprintf("%02d", $_POST['month']).sprintf("%02d", $_POST['day']))>date('Ymd')) {
  	$entryMessage .= LangValidateObservationMessage3;
		$_GET['indexAction'] = 'add_observation';
  }
  elseif(($time>-9999)&&((!is_numeric($_POST['hours']))||(!is_numeric($_POST['minutes']))||($_POST['hours']<0)||($_POST['hours']>23)||($_POST['minutes']<0)||($_POST['minutes']>59))) {
  	$entryMessage .= LangValidateObservationMessage4;
		$_GET['indexAction'] = 'add_observation';
  }
  else {
		if ($GLOBALS['objUtil']->checkPostKey('limit'))
			if (ereg('([0-9]{1})[.,]{0,1}([0-9]{0,1})', $_POST['limit'], $matches)) // limiting magnitude like X.X or X,X with X a number between 0 and 9
				$_POST['limit'] = $matches[1] . "." . (($matches[2]) ? $matches[2] : "0");
			else // clear current magnitude limit
				$_POST['limit'] = "";
		$current_observation = $GLOBALS['objObservation']->addDSObservation($_POST['object'], $_SESSION['deepskylog_id'], $_POST['instrument'], $_POST['site'], $date, $time, nl2br($_POST['description']), $_POST['seeing'], $_POST['limit'], $GLOBALS['objUtil']->checkPostKey('visibility'), $_POST['description_language']);
		$_SESSION['addObs'] = '';
		$_SESSION['Qobs'] = array ();
		$_SESSION['QobsParams'] = array ();
		if ($GLOBALS['objUtil']->checkPostKey('sqm'))
			if (ereg('([0-9]{1})([0-9]{0,1})[.,]{0,1}([0-9]{0,1})', $_POST['sqm'], $matches)) // sqm value
				$_POST['sqm'] = $matches[1] . $matches[2] . "." . (($matches[3]) ? $matches[3] : "0");
			else
				$_POST['sqm'] = ""; // clear current magnitude limit
		if ($GLOBALS['objUtil']->checkPostKey('largeDiam'))
			if (ereg('([0-9]+)[.,]{0,1}([0-9]{0,1})', $_POST['largeDiam'], $matches)) // large diameter
				$_POST['largeDiam'] = (($matches[1]) ? $matches[1] : "0") . "." . (($matches[2]) ? $matches[2] : "0");
			else // clear current large diameter
				$_POST['largeDiam'] = "";
		if ($GLOBALS['objUtil']->checkPostKey('smallDiam'))
			if (ereg('([0-9]+)[.,]{0,1}([0-9]{0,1})', $_POST['smallDiam'], $matches)) // large diameter
				$_POST['smallDiam'] = (($matches[1]) ? $matches[1] : "0") . "." . (($matches[2]) ? $matches[2] : "0");
			else // clear current large diameter
				$_POST['smallDiam'] = "";

		if ($_POST['smallDiam'] > $_POST['largeDiam']) {
			$tmp = $_POST['largeDiam'];
			$_POST['largeDiam'] = $_POST['smallDiam'];
			$_POST['smallDiam'] = $tmp;
		}
		if ($GLOBALS['objUtil']->checkPostKey('size_units') == "min") {
			$_POST['smallDiam'] = $_POST['smallDiam'] * 60.0;
			$_POST['largeDiam'] = $_POST['largeDiam'] * 60.0;
		}
		if ($_POST['sqm'])
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'SQM', preg_replace("/,/", ".", $_POST['sqm']));
		if ($_POST['smallDiam'])
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'smallDiameter', $_POST['smallDiam']);
		if ($_POST['largeDiam'])
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'largeDiameter', $_POST['largeDiam']);
		if (array_key_exists('stellarextended', $_POST)&&($_POST['stellarextended']=="stellar"))
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'stellar', 1);
		else
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'stellar', -1);
		if (array_key_exists('stellarextended', $_POST)&&($_POST['stellarextended']=="extended"))
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'extended', 1);
		else
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'extended', -1);
  	if (array_key_exists('resolved', $_POST))
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'resolved', 1);
		else
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'resolved', -1);
		if (array_key_exists('mottled', $_POST))
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'mottled', 1);
		else
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'mottled', -1);
		if (array_key_exists('unusualShape', $_POST))
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'unusualShape', 1);
		else
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'unusualShape', -1);
		if (array_key_exists('partlyUnresolved', $_POST))
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'partlyUnresolved', 1);
		else
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'partlyUnresolved', -1);
		if (array_key_exists('colorContrasts', $_POST))
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'colorContrasts', 1);
		else
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'colorContrasts', -1);
		if ($_POST['filter'])
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'filterid', $_POST['filter']);
		if ($_POST['lens'])
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'lensid', $_POST['lens']);
		if ($_POST['eyepiece'])
			$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'eyepieceid', $_POST['eyepiece']);
		if(!($GLOBALS['objObserver']->getObserverProperty($_SESSION['deepskylog_id'],'UT')))
			$GLOBALS['objObservation']->setLocalDateAndTime($current_observation, $date, $time);
		$GLOBALS['objObservation']->setDsObservationProperty($current_observation,'characterType', $GLOBALS['objUtil']->checkPostKey('characterType'));
		if ($_FILES['drawing']['tmp_name'] != "") // drawing to upload
			{
			$upload_dir = $instDir . 'deepsky/drawings';
			$dir = opendir($upload_dir);
			$original_image = $_FILES['drawing']['tmp_name'];
			$destination_image = $upload_dir . "/" . $current_observation . "_resized.jpg";
			$max_width = "490";
			$max_height = "490";
			$resample_quality = "100";

			include $instDir . "common/control/resize.php"; // resize code
			$new_image = image_createThumb($original_image, $destination_image, $max_width, $max_height, $resample_quality);
			move_uploaded_file($_FILES['drawing']['tmp_name'], $upload_dir . "/" . $current_observation . ".jpg");
		  $objObservation->setDsObservationProperty($current_observation,'hasDrawing',1);
		}
		$_SESSION['newObsYear'] = $_POST['year']; // save current details for faster submission of multiple observations
		$_SESSION['newObsMonth'] = $_POST['month'];
		$_SESSION['newObsDay'] = $_POST['day'];
		$_SESSION['newObsInstrument'] = $_POST['instrument'];
		$_SESSION['newObsLocation'] = $_POST['site'];
		$_SESSION['newObsLimit'] = $_POST['limit'];
		$_SESSION['newObsSqm'] = $_POST['sqm'];
		$_SESSION['newObsSQM'] = $_POST['sqm'];
		$_SESSION['newObsSeeing'] = $_POST['seeing'];
		$_SESSION['newObsLanguage'] = $_POST['description_language'];
		$_SESSION['newObsSavedata'] = "yes";
		$_GET['indexAction'] = "detail_observation";
		$_GET['dalm'] = 'D';
		$_GET['observation'] = $current_observation;
	}
}
  
?>