<?php
if (!array_key_exists('deepskylog_id', $_SESSION) || !$_SESSION['deepskylog_id'])
	throw new Exception("Not logged in");
elseif ($GLOBALS['objUtil']->checkArrayKey($_SESSION, 'addObs', 0) != $GLOBALS['objUtil']->checkPostKey('timestamp', -1)) {
	$_GET['indexAction'] = "detail_observation";
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
	$entryMessage .= "Not all necessary fields are filled in." . LangValidateObservationMessage1;
	$_GET['indexAction'] = 'add_observation';
} else // all fields filled in
	{
	if ($_FILES['drawing']['size'] > $maxFileSize) // file size of drawing too big
		{
		$entryMessage .= LangValidateObservationMessage6;
		$entryMessage .= "File size of drawing too big";
		$_GET['indexAction'] = 'add_observation';
	} else {
		$date = $_POST['year'] . sprintf("%02d", $_POST['month']) . sprintf("%02d", $_POST['day']);
		if ($_POST['hours']) {
			if (isset ($_POST['minutes']))
				$time = ($_POST['hours'] * 100) + $_POST['minutes'];
			else
				$time = ($_POST['hours'] * 100);
		} else
			$time = -9999;
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
			$GLOBALS['objObservation']->setSQM($current_observation, $_POST['sqm']);
		if ($_POST['smallDiam'])
			$GLOBALS['objObservation']->setSmallDiameter($current_observation, $_POST['smallDiam']);
		if ($_POST['largeDiam'])
			$GLOBALS['objObservation']->setLargeDiameter($current_observation, $_POST['largeDiam']);
		if ($_POST['stellar'])
			$GLOBALS['objObservation']->setStellar($current_observation, 1);
		else
			$GLOBALS['objObservation']->setStellar($current_observation, -1);
		if ($_POST['extended'])
			$GLOBALS['objObservation']->setExtended($current_observation, 1);
		else
			$GLOBALS['objObservation']->setExtended($current_observation, -1);
		if ($_POST['resolved'])
			$GLOBALS['objObservation']->setResolved($current_observation, 1);
		else
			$GLOBALS['objObservation']->setResolved($current_observation, -1);
		if ($_POST['mottled'])
			$GLOBALS['objObservation']->setMottled($current_observation, 1);
		else
			$GLOBALS['objObservation']->setMottled($current_observation, -1);
		if ($_POST['unusualShape'])
			$GLOBALS['objObservation']->setUnusualShape($current_observation, 1);
		else
			$GLOBALS['objObservation']->setUnusualShape($current_observation, -1);
		if ($_POST['partlyUnresolved'])
			$GLOBALS['objObservation']->setPartlyUnresolved($current_observation, 1);
		else
			$GLOBALS['objObservation']->setPartlyUnresolved($current_observation, -1);
		if ($_POST['colorContrasts'])
			$GLOBALS['objObservation']->setColorContrasts($current_observation, 1);
		else
			$GLOBALS['objObservation']->setColorContrasts($current_observation, -1);
		if ($_POST['filter'])
			$GLOBALS['objObservation']->setFilterId($current_observation, $_POST['filter']);
		if ($_POST['lens'])
			$GLOBALS['objObservation']->setLensId($current_observation, $_POST['lens']);
		if ($_POST['eyepiece'])
			$GLOBALS['objObservation']->setEyepieceId($current_observation, $_POST['eyepiece']);
		if ($GLOBALS['objObserver']->getUseLocal($_SESSION['deepskylog_id']))
			$GLOBALS['objObservation']->setLocalDateAndTime($current_observation, $date, $time);
		$GLOBALS['objObservation']->setCharacterType($current_observation, $GLOBALS['objUtil']->checkPostKey('characterType'));
		if ($_FILES['drawing']['tmp_name'] != "") // drawing to upload
			{
			$upload_dir = $instDir . 'deepsky/drawings';
			$dir = opendir($upload_dir);
			$original_image = $_FILES['drawing']['tmp_name'];
			$destination_image = $upload_dir . "/" . $current_observation . "_resized.jpg";
			$max_width = "490";
			$max_height = "490";
			$resample_quality = "100";

			include $instDir . "/common/control/resize.php"; // resize code
			$new_image = image_createThumb($original_image, $destination_image, $max_width, $max_height, $resample_quality);
			move_uploaded_file($_FILES['drawing']['tmp_name'], $upload_dir . "/" . $current_observation . ".jpg");
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