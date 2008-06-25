<?php

// validate_site.php
// checks if the add new location or change location form is correctly filled in
// version 0.1: JV 20041126

session_start(); // start session

include "../../lib/locations.php";
include "../../lib/observers.php";
include_once "../../lib/setup/vars.php";
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

if ($_POST['adaption'] == 1)
{
	$observer = new Observers;
	$observer->setStandardLocation($_SESSION['deepskylog_id'], $_POST['stdlocation']);

  header("Location:../add_site.php");
} else if (!$_POST['sitename'] || !$_POST['region'] || !$_POST['country'] || !$_POST['longitude'] || !$_POST['latitude'] || !$_POST['timezone'])
{
      $_SESSION['message'] = LangValidateSiteMessage1; 
      header("Location:../error.php");
}
else // all fields filled in
{
      $locations = new Locations; // create new Locations object

      // fill database

$latitude = $_POST['latitude'] + $_POST['latitudemin'] / 60.0;
$longitude = $_POST['longitude'] + $_POST['longitudemin'] / 60.0;
$timezone = $_POST['timezone'];
if(array_key_exists('add', $_POST) && $_POST['add'])
{
	$id = $locations->addLocation($_POST['sitename'], $longitude, $latitude, $_POST['region'], $_POST['country'], $timezone);

          if (array_key_exists('lm', $_POST) && $_POST['lm'])
          {
             $locations->setLimitingMagnitude($id, $_POST['lm']);
             $locations->setSkyBackground($id, -999);
          } else if (array_key_exists('sb', $_POST) && $_POST['sb'])
          {
             $locations->setSkyBackground($id, $_POST['sb']);
             $locations->setLimitingMagnitude($id, -999);
          } else 
					{
             $locations->setSkyBackground($id, -999);
             $locations->setLimitingMagnitude($id, -999);
					}
          $locations->setObserver($id, $_SESSION['deepskylog_id']);
          $_SESSION['message'] = LangValidateSiteMessage2;
	  $_SESSION['title'] = LangValidateSiteMessage3;
}
if(array_key_exists('change', $_POST) && $_POST['change'])
{
          $locations->setName($_POST['id'], $_POST['sitename']);
          $locations->setRegion($_POST['id'], $_POST['region']);
          $locations->setCountry($_POST['id'], $_POST['country']);
          $locations->setLongitude($_POST['id'], $longitude);
          $locations->setLatitude($_POST['id'], $latitude);
          $locations->setTimezone($_POST['id'], $timezone);
          $locations->setObserver($_POST['id'], $_SESSION['deepskylog_id']);
          $_SESSION['message'] = LangValidateSiteMessage5;
          $_SESSION['title'] = LangValidateSiteMessage4;

          if (array_key_exists('lm', $_POST) && $_POST['lm'])
          {
             $locations->setLimitingMagnitude($_POST['id'], $_POST['lm']);
             $locations->setSkyBackground($_POST['id'], -999);
          } else if (array_key_exists('sb', $_POST) && $_POST['sb'])
          {
             $locations->setSkyBackground($_POST['id'], $_POST['sb']);
             $locations->setLimitingMagnitude($_POST['id'], -999);
          } else
					{
             $locations->setSkyBackground($_POST['id'], -999);
             $locations->setLimitingMagnitude($_POST['id'], -999);
					}
}
          header("Location:../add_site.php");
}
?>
