<?php
// validate_site.php
// checks if the add new location or change location form is correctly filled in

if($objUtil->checkPostKey('adaption')==1
&& $objUtil->checkUserID($objLocation->getObserverFromLocation($objUtil->checkPostKey('stdlocation'))))
{ $objObserver->setStandardLocation($_SESSION['deepskylog_id'], $_POST['stdlocation']);
} 
if($objUtil->checkPostKey('sitename')
&& $objUtil->checkPostKey('region')
&& $objUtil->checkPostKey('country')
&& $objUtil->checkPostKey('longitude')
&& $objUtil->checkPostKey('latitude')
&& $objUtil->checkPostKey('timezone'))
{ $latitude = $_POST['latitude'] + $_POST['latitudemin'] / 60.0;
  $longitude = $_POST['longitude'] + $_POST['longitudemin'] / 60.0;
  $timezone = $_POST['timezone'];
  if($objUtil->checkPostKey('add'))
  { $id = $objLocation->addLocation($_POST['sitename'], $longitude, $latitude, $_POST['region'], $_POST['country'], $timezone);
    if (array_key_exists('lm', $_POST) && $_POST['lm'])
    { $objLocation->setLocationLimitingMagnitude($id, $_POST['lm']);
      $objLocation->setSkyBackground($id, -999);
    } 
    elseif(array_key_exists('sb', $_POST) && $_POST['sb'])
    { $objLocation->setSkyBackground($id, $_POST['sb']);
      $locations-setLocationLimitingMagnitude($id, -999);
    } 
    else
    { $objLocation->setSkyBackground($id, -999);
      $locations-setLocationLimitingMagnitude($id, -999);
		}
		$objLocation->setLocationObserver($id, $_SESSION['deepskylog_id']);
    $_SESSION['message'] = LangValidateSiteMessage2;
	  $_SESSION['title'] = LangValidateSiteMessage3;
  }
  if($objUtil->checkPostKey('change')
  && $objUtil->checkUserID($objLocation->getObserverFromLocation($objUtil->checkPostKey('id'))))
  { $objLocation->setLocationName($_POST['id'], $_POST['sitename']);
    $objLocation->setRegion($_POST['id'], $_POST['region']);
    $objLocation->setCountry($_POST['id'], $_POST['country']);
    $objLocation->setLongitude($_POST['id'], $longitude);
    $objLocation->setLatitude($_POST['id'], $latitude);
    $objLocation->setTimezone($_POST['id'], $timezone);
    $objLocation->setLocationObserver($_POST['id'], $_SESSION['deepskylog_id']);
    $entryMessage=LangValidateSiteMessage5.' '.LangValidateSiteMessage4;
    if($objUtil->checkPostKey('lm'))
    { $locations-setLocationLimitingMagnitude($_POST['id'], $_POST['lm']);
      $objLocation->setSkyBackground($_POST['id'], -999);
    } 
    elseif($objUtil->checkPostKey('sb'))
    { $objLocation->setSkyBackground($_POST['id'], $_POST['sb']);
      $objLocation->setLocationLimitingMagnitude($_POST['id'], -999);
    } 
    else
    { $objLocation->setSkyBackground($_POST['id'], -999);
      $locations-setLocationLimitingMagnitude($_POST['id'], -999);
		}
  }
}
$_GET['indexAction']="add_site";

?>
