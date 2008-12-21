<?php

// The location class collects all functions needed to enter, retrieve and
// adapt location data from the database.
//
// Version 0.4 : 19/11/2006, WDM
//

include_once "database.php";
//include "setup/vars.php";

class Locations
{
 // addLocation adds a new location to the database. The name, longitude, 
 // latitude, region and country should be given as parameters. 
 function addLocation($name, $longitude, $latitude, $region, $country, $timezone)
 {
  $db = new database;
  $db->login();

  if (!$_SESSION['lang'])
  {
   $_SESSION['lang'] = "English";
  }

  $sql = "INSERT INTO locations (name, longitude, latitude, region, country, timezone) VALUES (\"$name\", \"$longitude\", \"$latitude\", \"$region\", \"$country\", \"$timezone\")";

  mysql_query($sql) or die(mysql_error());

  $query = "SELECT id FROM locations ORDER BY id DESC LIMIT 1";
  $run = mysql_query($query) or die(mysql_error());

  $db->logout();
  $get = mysql_fetch_object($run);
  if($get) 
  {
   return $get->id; 
  }
  else
  {
   return '';
  }
 }

 function deleteLocation($id)
 {
  $db = new database;
  $db->login();

  $sql = "DELETE FROM locations WHERE id=\"$id\"";
  mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // getId returns the id for this instrument
 function getLocationId($name, $observer)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM locations where name=\"$name\" and observer=\"$observer\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get)
  {
    $id = $get->id;
  }
  else
  {
    $id = -1;
  }

  $db->logout();

  return $id;
 }

 // getCountries returns all possible countries
 function getCountries()
 {
   // Reading the file with the country codes.
   $filename = "lib/setup/locations/countries.txt";
   $fh = fopen($filename, "r") or die("Could not open countries file" + $filename);

   while (!feof($fh))
   {
    $data = fgets($fh);
    $vars = explode(" - ", $data);

    $a = sscanf($vars[1], "(%c%c)");
    $countries[$a[0].$a[1]] = ucfirst(strtolower($vars[0]));
   }
   fclose($fh);

   return $countries;
 }

 // getDatabaseCountries returns all countries for which the database of 
 // the locations is available
 function getDatabaseCountries()
 {
   // Reading the file with the country codes.
   //$filename = "lib/setup/locations/_country_code_help.txt";
   $filename = "lib/setup/locations/countries.txt";
   $fh = fopen($filename, "r") or die("Could not open countries file");

   while (!feof($fh))
   {
    $data = fgets($fh);
    $vars = explode(" - ", $data);

    $a = sscanf($vars[1], "(%c%c)");
    $countriesConversion[$a[0].$a[1]] = ucfirst(strtolower($vars[0]));
   }
   fclose($fh);

   $maindir = "lib/setup/locations/" ;
   $mydir = opendir($maindir) ;
   $exclude = array( "index.php" , ".", "..") ;
   $countries = array();
   while($fn = readdir($mydir))
   {
    if ($fn == $exclude[0] || $fn == $exclude[1] || $fn == $exclude[2]) continue;
    $code = explode(".", $fn);
    
    if ($code[1] == "ast")
    {
     $countries[] = $countriesConversion[strtoupper($code[0])];
    }
   }
   closedir($mydir);

   return $countries;
 }

 // getLocationsId returns the id of the given name of the location
 function getLocationsId($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM locations WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $locationid = $get->id;

  $db->logout();

  return $locationid;
 }

 // getObserver returns the observerid for this location
 function getObserverFromLocation($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM locations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $observer = $get->observer;

  $db->logout();

  return $observer;
 }

 // getLocations returns an array with all locations
 function getLocations()
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM locations";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $locs[] = $get->id;
  }

  $db->logout();

  return $locs;
 }

 // getLocations returns an array with all locations and names
 function getLocationsName()
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM locations";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $locs[$get->id] = $get->name;
  }

  $db->logout();

  return $locs;
 }

 // getLocationsFromDatabase returns an array with all information about the 
 // location where the name equals the given name in the given country (given
 // the country string - e.g. Belgium).
 function getLocationsFromDatabase($name, $country)
 {
  $locations=array();
 	// Reading the file with the country codes.
  $filename = "lib/setup/locations/countries.txt";

  $fh = fopen($filename, "r") or die("Could not open countries file");

  while (!feof($fh))
  {
   $data = fgets($fh);
   $vars = explode(" - ", $data);

   $a = sscanf($vars[1], "(%c%c)");
   $countriesConversion[ucfirst(strtolower($vars[0]))] = $a[0].$a[1];
  }
  fclose($fh);
  
  $filename = "lib/setup/locations/".strtolower($countriesConversion[$country]).".ast";

  $fh = fopen($filename, "r") or die("Could not read file");

  while (!feof($fh))
  {
   $data = fgets($fh);
   $vars = explode("\t", $data);

   if (strtolower($vars[0]) == strtolower($name))
   {
    $locations[] = $data;
   }
  }
  return $locations;
 }

 // getCountry returns the country of the given id
 function getCountry($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM locations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $country = $get->country;

  $db->logout();

  return $country;
 }

 // getLatitude returns the latitude of the given id
 function getLatitude($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM locations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $latitude = $get->latitude;

  $db->logout();

  return $latitude;
 }

 // getLongitude returns the longitude of the given id
 function getLongitude($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM locations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $longitude = $get->longitude;

  $db->logout();

  return $longitude;
 }

 // getLocationName returns the name of the given id
 function getLocationName($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM locations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);
  if($get)
	{
    $name = $get->name;
  }
	else
	{
	  $name = "";
	}
	
  $db->logout();

  return $name;
 }

 // getLimitingMagnitude returns the typical limiting magnitude of the given id
 function getLocationLimitingMagnitude($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM locations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get != null)
  {
   $limmag = $get->limitingMagnitude;
  }
  else 
  {
   $limmag = -999;
  }
  $db->logout();

  return $limmag;
 }

 // getCountry returns the typical sky background for the id
 function getSkyBackground($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM locations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get != null)
  {
   $skyBack = $get->skyBackground;
  }
  else
  {
   $skyBack = -999;
  }


  $db->logout();

  return $skyBack;
 }

 // getRegion returns the region of the given id
 function getRegion($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM locations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $region = $get->region;

  $db->logout();

  return $region;
 }

 // getSortedLocations returns an array with the ids of all locations, sorted
 // by the column specified in $sort
 function getSortedLocations($sort, $observer = "", $unique = false)
 {
  $locs = array();

  $db = new database;
  $db->login();

  if ($unique == false)
   if ($observer == "")
     $sql = "SELECT * FROM locations ORDER BY $sort"; 
   else
     $sql = "SELECT * FROM locations where observer = \"$observer\" ORDER BY $sort";
  else
   if ($observer == "")
     $sql = "SELECT id, name FROM locations GROUP BY name";
   else
     $sql = "SELECT id, name FROM locations where observer = \"$observer\" GROUP BY name ORDER BY $sort";
  $run = mysql_query($sql) or die(mysql_error());
  while($get = mysql_fetch_object($run))
    $locs[] = $get->id;
  $db->logout();
  return $locs;
 }

 // getAllIds returns a list with all id's which have the same name as the name of the given id
 function getAllLocationsIds($id)
 {
  $ids = array();
  $sql = "SELECT name FROM locations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
  if ($get)
  {
    $sql = "SELECT id FROM locations WHERE name = \"$get->name\"";
    $run = mysql_query($sql) or die(mysql_error());
    while($get = mysql_fetch_object($run))
     $ids[] = $get->id;
  }
  return $ids;
 }

 // getSortedLocationsList returns an array with the ids of all locations,
 // sorted by the column specified in $sort. Locations withthe same name
 // are adapted by adding the province.
 function getSortedLocationsList($sort, $observer = "")
 {
  $sites = $this->getSortedLocations("name", $observer);

  // If there are locations with the same name, the province should also
  // be shown
  $previous = "fdgsdg";

  for ($i = 0;$i < count($sites);$i++)
  {
   $adapt[$i] = 0;

   if ($this->getLocationName($sites[$i]) == $previous)
   {
    $adapt[$i] = 1;
    $adapt[$i - 1] = 1;
   }
   $previous = $this->getLocationName($sites[$i]);
  }

  for ($i = 0;$i < count($sites);$i++)
  {
   if ($adapt[$i])
   {
    $new_sites[$i][0] = $sites[$i];
    $new_sites[$i][1] = $this->getLocationName($sites[$i])." (".$this->getRegion($sites[$i]).")";
   }
   else
   {
    $new_sites[$i][0] = $sites[$i];
    $new_sites[$i][1] = $this->getLocationName($sites[$i]);
   }
  }
  return $new_sites;
 }

 // getTimezone returns the timezone of the given id
 function getTimezone($id)
 {
  $db = new database;
  $db->login();
  $sql = "SELECT * FROM locations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
  $db->logout();
	
  $timezone = $get->timezone;
  return $timezone;
 }

 // setCountry sets a new country for the location with id = $id
 function setCountry($id, $country)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE locations SET country = \"$country\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setLatitude sets a new latitude for the location with id = $id
 function setLatitude($id, $latitude)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE locations SET latitude = \"$latitude\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setLongitude sets the longitude for the location with id = $id
 function setLongitude($id, $longitude)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE locations SET longitude = \"$longitude\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setName sets the name for the location with id = $id
 function setLocationName($id, $name)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE locations SET name = \"$name\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // Set the typical limiting magnitude for the location
 function setLocationLimitingMagnitude($id, $lm)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE locations SET limitingMagnitude = \"$lm\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // Set the typical sky background for the id
 function setSkyBackground($id, $sb)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE locations SET skyBackground = \"$sb\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setRegion sets the region for the location with id = $id
 function setRegion($id, $region)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE locations SET region = \"$region\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setObserver sets the observer for the location with id = $id
 function setLocationObserver($id, $observer)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE locations SET observer = \"$observer\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setTimezone sets a new timezone for the location with id = $id
 function setTimezone($id, $timezone)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE locations SET timezone = \"$timezone\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // showLocations prints a table showing all locations. 
 function showLocations()
 {
  $locations = $this->getLocations();

  $count = 0;

  echo "<table width=\"100%\">
         <tr class=\"type3\">
          <td>id</td>
          <td>location</td>
          <td>longitude</td>
          <td>latitude</td>
          <td>region</td>
          <td>country</td>
         </tr>";

  while(list ($key, $value) = each($locations))
  {
   if ($count % 2)
   {
    $type = "class=\"type1\"";
   }
   else
   {
    $type = "class=\"type2\"";
   }

   $name = $this->getLocationName($value);
   $longitude = $this->getLongitude($value);
   $latitude = $this->getLatitude($value);
   $region = $this->getRegion($value);
   $country = $this->getCountry($value);

   echo "<tr $type><td> $value </td><td> $name </td><td> $longitude </td><td> $latitude </td><td> $region </td><td> $country </td>";

   echo "</tr>\n";

   $count++;
  }
  echo "</table>";
 }
}
$objLocation=new Locations;
?>
