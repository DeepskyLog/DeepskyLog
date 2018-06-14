<?php
$inIndex = true;
require_once "../lib/setup/databaseInfo.php";
require_once "../lib/database.php";
require_once "../lib/locations.php";
date_default_timezone_set ( 'UTC' );

$objDatabase = new Database ();
$objLocation = new Locations ();
print "Database update will correct all the bad locations.<br />\n";

$locationsToCheck = $objDatabase->selectRecordsetArray ( "SELECT id FROM locations where checked=\"1\"", 'id' );

print sizeof($locationsToCheck);
print "<br />";
if (sizeof ( $locationsToCheck ) > 0) {
  foreach ( $locationsToCheck as $location ) {
    print $location['id'] . "<br />";
    // We adapt the timezone, elevation and country
    $latitude = $objLocation->getLocationPropertyFromId ( $location ['id'], "latitude" );
    $longitude = $objLocation->getLocationPropertyFromId ( $location ['id'], "longitude" );

    $url = "https://maps.googleapis.com/maps/api/timezone/json?key=AIzaSyD8QoWrJk48kEjHhaiwU77Tp-qSaT2xCNE&location=" . $latitude . "," . $longitude . "&timestamp=0";
    $json = file_get_contents ( $url );
    $obj = json_decode ( $json );
    if ($obj->status == "OK") {
      $objLocation->setLocationProperty ( $location ['id'], "timezone", $obj->timeZoneId );

      // Get the elevation
      $url = "https://maps.googleapis.com/maps/api/elevation/json?key=AIzaSyD8QoWrJk48kEjHhaiwU77Tp-qSaT2xCNE&locations=" . $latitude . "," . $longitude;
      $json = file_get_contents ( $url );
      $obj = json_decode ( $json );
      if ($obj->status == "OK") {
        $results = $obj->results [0];
        $objLocation->setLocationProperty ( $location ['id'], "elevation", (( int ) $results->elevation) );

        // Get the country
        $url = "https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyD8QoWrJk48kEjHhaiwU77Tp-qSaT2xCNE&latlng=" . $latitude . "," . $longitude . "&language=en&key=AIzaSyDGQJvhs1ItqmrFfYPRrh3vNpBzNbWntis";
        $json = file_get_contents ( $url );
        $obj = json_decode ( $json );
        if ($obj->status == "OK") {
          $results = $obj->results [0];
          $components = $results->address_components;
          for($ac = 0; $ac < sizeof ( $components ); $ac ++) {
            if ($components [$ac]->types [0] == "country") {
              $objLocation->setLocationProperty ( $location ['id'], "country", $components [$ac]->long_name );
              $objLocation->setLocationProperty ( $location ['id'], "checked", 2 );
            }
          }
        }
      }
    }
  }
}
?>
