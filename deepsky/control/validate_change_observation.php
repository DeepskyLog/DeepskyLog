<?php

// validate_change_observation.php
// checks if the change new observation form is correctly filled in

if (array_key_exists('changeobservation', $_POST) && $_POST['changeobservation']) // pushed change observation button
	{
	if (!$_POST['day'] || !$_POST['month'] || !$_POST['year'] || $_POST['location'] == "1" || !$_POST['instrument'] || !$_POST['description'])
		throw new Exception(LangValidateObservationMessage1);
	elseif ($_FILES['drawing']['size'] > $maxFileSize) // file size of drawing too big
	{
		throw new Exception(LangValidateObservationMessage6);
	}
	elseif (array_key_exists('observationid', $_POST) && $_POST['observationid']) // all fields filled in and observationid given
	{
		if ($objObservation->getObserverId($_POST['observationid']) == $_SESSION['deepskylog_id']) // only allowed to change your own observations
			{
			$date = $_POST['year'] . sprintf("%02d", $_POST['month']) . sprintf("%02d", $_POST['day']);

			if (array_key_exists('hours', $_POST) && ($_POST['hours'] != '')) {
				if (array_key_exists('minutes', $_POST) && $_POST['minutes']) {
					$time = ($_POST['hours'] * 100) + $_POST['minutes'];
				} else {
					$time = ($_POST['hours'] * 100);
				}
			} else {
				$time = -9999;
			}

			$objObservation->setDescription($_POST['observationid'], nl2br($_POST['description']));
			$GLOBALS['objObservation']->setCharacterType($_POST['observationid'], $GLOBALS['objUtil']->checkPostKey('characterType'));

			if ($_POST['filter']) {
				$objObservation->setFilterId($_POST['observationid'], $_POST['filter']);
			} else {
				$objObservation->setFilterId($_POST['observationid'], 0);
			}

			if ($_POST['lens']) {
				$objObservation->setLensId($_POST['observationid'], $_POST['lens']);
			} else {
				$objObservation->setLensId($_POST['observationid'], 0);
			}

			if ($_POST['eyepiece']) {
				$objObservation->setEyepieceId($_POST['observationid'], $_POST['eyepiece']);
			} else {
				$objObservation->setEyepieceId($_POST['observationid'], 0);
			}

			if ($objObserver->getUseLocal($_SESSION['deepskylog_id'])) {
				$objObservation->setLocalDateAndTime($_POST['observationid'], $date, $time);
			} else {
				$objObservation->setTime($_POST['observationid'], $time);
				$objObservation->setDate($_POST['observationid'], $date);
			}
			$objObservation->setInstrumentId($_POST['observationid'], $_POST['instrument']);
			$objObservation->setLocationId($_POST['observationid'], $_POST['location']);

			$objObservation->setSeeing($_POST['observationid'], $_POST['seeing']);

			if (array_key_exists('limit', $_POST) && $_POST['limit']) {
				if (ereg('([0-9]{1})[.,]{0,1}([0-9]{0,1})', $_POST['limit'], $matches)) // limiting magnitude like X.X or X,X with X a number between 0 and 9
					{
					// valid limiting magnitude
					$_SESSION['limit'] = $matches[1] . ".";
					if ($matches[2] != "") {
						$_SESSION['limit'] = $_SESSION['limit'] . $matches[2];
					} else {
						$_SESSION['limit'] = $_SESSION['limit'] . "0";
					}
				} else // invalid limiting magnitude
					{
					$_SESSION['limit'] = ""; // clear current magnitude limit
				}
			} else
				$_SESSION['limit'] = "";
			$objObservation->setObservationLimitingMagnitude($_POST['observationid'], $_SESSION['limit']);
			$objObservation->setObservationLanguage($_POST['observationid'], $_POST['description_language']);
			if (array_key_exists('visibility', $_POST) && $_POST['visibility'])
				$visibility = $_POST['visibility'];
			else
				$visibility = 0;
			$objObservation->setVisibility($_POST['observationid'], $visibility);

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
				$GLOBALS['objObservation']->setSQM($_POST['observationid'], $_POST['sqm']);
			if ($_POST['smallDiam'])
				$GLOBALS['objObservation']->setSmallDiameter($_POST['observationid'], $_POST['smallDiam']);
			if ($_POST['largeDiam'])
				$GLOBALS['objObservation']->setLargeDiameter($_POST['observationid'], $_POST['largeDiam']);
			if (array_key_exists('stellarextended', $_POST)&&($_POST['stellarextended']=="stellar"))
				$GLOBALS['objObservation']->setStellar($_POST['observationid'], 1);
			else
				$GLOBALS['objObservation']->setStellar($_POST['observationid'], -1);
			if (array_key_exists('stellarextended', $_POST)&&($_POST['stellarextended']=="extended"))
				$GLOBALS['objObservation']->setExtended($_POST['observationid'], 1);
			else
				$GLOBALS['objObservation']->setExtended($_POST['observationid'], -1);
			if (array_key_exists('resolved', $_POST))
				$GLOBALS['objObservation']->setResolved($_POST['observationid'], 1);
			else
				$GLOBALS['objObservation']->setResolved($_POST['observationid'], -1);
			if (array_key_exists('mottled', $_POST))
				$GLOBALS['objObservation']->setMottled($_POST['observationid'], 1);
			else
				$GLOBALS['objObservation']->setMottled($_POST['observationid'], -1);
			if (array_key_exists('unusualShape', $_POST))
				$GLOBALS['objObservation']->setUnusualShape($_POST['observationid'], 1);
			else
				$GLOBALS['objObservation']->setUnusualShape($_POST['observationid'], -1);
			if (array_key_exists('partlyUnresolved', $_POST))
				$GLOBALS['objObservation']->setPartlyUnresolved($_POST['observationid'], 1);
			else
				$GLOBALS['objObservation']->setPartlyUnresolved($_POST['observationid'], -1);
			if (array_key_exists('colorContrasts', $_POST))
				$GLOBALS['objObservation']->setColorContrasts($_POST['observationid'], 1);
			else
				$GLOBALS['objObservation']->setColorContrasts($_POST['observationid'], -1);

			if ($_FILES['drawing']['tmp_name'] != "") {
				$upload_dir = $instDir . 'deepsky/drawings';
				$dir = opendir($upload_dir);

				// resize code

				include $instDir . "common/control/resize.php";

				$original_image = $_FILES['drawing']['tmp_name'];
				$destination_image = $upload_dir . "/" . $_POST['observationid'] . "_resized.jpg";
				$max_width = "490";
				$max_height = "490";
				$resample_quality = "100";

				$new_image = image_createThumb($original_image, $destination_image, $max_width, $max_height, $resample_quality);

				move_uploaded_file($_FILES['drawing']['tmp_name'], $upload_dir . "/" . $_POST['observationid'] . ".jpg");
			}

			// save current details for faster submission of multiple observations

			$_SESSION['year'] = $_POST['year']; // save current year
			$_SESSION['month'] = $_POST['month']; // save current month
			$_SESSION['day'] = $_POST['day']; // save current day
			$_SESSION['instrument'] = $_POST['instrument']; // save current instrument for new observations
			$_SESSION['location'] = $_POST['location']; // save current location
			$_SESSION['seeing'] = $_POST['seeing']; // save current seeing
			$_SESSION['savedata'] = "yes"; // session variable to tag multiple observations

			$_GET['indexAction'] = 'detail_observation';
			$_GET['dalm'] = 'D';
			$_GET['observation'] = $_POST['observationid'];
			$_GET['new'] = "yes";

		} // end if own observation.php
		else // try to change an observation which doesn't belong to the observer logged in
			{
			$_GET['indexAction'] = 'default_action';
		}
	} else // no observation id given
		{
		$_GET['indexAction'] = 'default_action';
	}

}
?>
