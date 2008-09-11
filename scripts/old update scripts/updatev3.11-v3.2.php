<?php
 include_once "../lib/database.php";
 include_once "../lib/instruments.php";
 include_once "../lib/locations.php";


 $db = new database;
 $dbid = $db->login();

 // Create table for observation lists 
 $sql= "CREATE TABLE IF NOT EXISTS`observerobjectlist` (`observerid` VARCHAR(255) NOT NULL, `listname` VARCHAR(255) NOT NULL, `objectname` VARCHAR(255) NOT NULL, `objectplace` INTEGER NOT NULL DEFAULT '0', " .
       "INDEX Index_observer(`observerid`), INDEX Index_list(`observerid`, `listname`, `objectplace`)) ENGINE = MyISAM;";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "Describe locations";
 $run = mysql_query($sql) or die(mysql_error());

 $limitingMagnitude = False;
 $skyBackground = False;

 while($get = mysql_fetch_object($run))
 {
  if ($get->Field == "limitingMagnitude")
  {
    $limitingMagnitude = True;
  }
  if ($get->Field == "skyBackground")
  {
    $skyBackground = True;
  }
 }


 // Adapt table to add limiting magnitude and Sky Background
 if ($limitingMagnitude == False)
 {
  $sql = "ALTER TABLE locations ADD limitingMagnitude float NOT NULL default '-999'";
  $run = mysql_query($sql) or die(mysql_error());
 }

 if ($skyBackground == False)
 {
  $sql = "ALTER TABLE locations ADD skyBackground float NOT NULL default '-999'";
  $run = mysql_query($sql) or die(mysql_error());
 }

 // Copy the structure of the locations to locations2
 $sql = "CREATE TABLE locations2 SELECT * from locations Where '0' = '1'";
 $run = mysql_query($sql) or die(mysql_error());

 // Add the new observer column.
 $sql = "ALTER TABLE locations2 ADD observer varchar(255) default ''";
 $run = mysql_query($sql) or die(mysql_error());

 $locations = new Locations();

 // The database is created and empty
 // Select the locations and observerid from the deepsky observations
 $sql = "select distinct(locationid),observerid from observations order by locationid";
 $run = mysql_query($sql) or die(mysql_error());

 $id = 999;
 while($get = mysql_fetch_object($run))
 {
   $id = $id + 1;

   $sql2 = "SELECT * FROM locations WHERE id = \"$get->locationid\"";
   $run2 = mysql_query($sql2) or die(mysql_error());
   $get2 = mysql_fetch_object($run2);

   $name = $get2->name;
   $longitude = $get2->longitude;
   $latitude = $get2->latitude;
   $region = $get2->region;
   $country = $get2->country;
   $timezone = $get2->timezone;

   $sql3 = "INSERT INTO locations2 (id, name, longitude, latitude, region, country, timezone, observer) VALUES (\"$id\", \"$name\", \"$longitude\", \"$latitude\", \"$region\", \"$country\", \"$timezone\", \"$get->observerid\")";
   $run3 = mysql_query($sql3) or die(mysql_error());

   $sql3 = "update observations set locationid = \"$id\" where observerid = \"$get->observerid\" and locationid = \"$get->locationid\"";
   $run3 = mysql_query($sql3) or die(mysql_error());
 }

 // Select the locations and observerid from the comet observations
 $sql = "select distinct(locationid),observerid from cometobservations order by locationid";
 $run = mysql_query($sql) or die(mysql_error());

 while($get = mysql_fetch_object($run))
 {
   if ($get->locationid != 0)
   {
    $sql2 = "SELECT * FROM locations WHERE id = \"$get->locationid\"";
    $run2 = mysql_query($sql2) or die(mysql_error());
    $get2 = mysql_fetch_object($run2);

    $name = $get2->name;
    $longitude = $get2->longitude;
    $latitude = $get2->latitude;
    $region = $get2->region;
    $country = $get2->country;
    $timezone = $get2->timezone;

    $sql3 = "SELECT * from locations2 where name = \"$name\" and observer = \"$get->observerid\"";
    $run3 = mysql_query($sql3) or die(mysql_error());

    if ($get3 = mysql_fetch_object($run3))
    {
     $sql4 = "update cometobservations set locationid = \"$get3->id\" where observerid = \"$get->observerid\" and locationid = \"$get->locationid\"";
     $run4 = mysql_query($sql4) or die(mysql_error());
    }
    else
    {
      $id = $id + 1;
      $sql4 = "INSERT INTO locations2 (id, name, longitude, latitude, region, country, timezone, observer) VALUES (\"$id\", \"$name\", \"$longitude\", \"$latitude\", \"$region\", \"$country\", \"$timezone\", \"$get->observerid\")";
      $run4 = mysql_query($sql4) or die(mysql_error());

      $sql4 = "update cometobservations set locationid = \"$id\" where observerid = \"$get->observerid\" and locationid = \"$get->locationid\"";
      $run4 = mysql_query($sql4) or die(mysql_error());
    }
  }
 }

  $sql = "ALTER TABLE locations2 ADD PRIMARY KEY (id)";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "ALTER TABLE locations2 CHANGE id id int(11) auto_increment";
  $run = mysql_query($sql) or die(mysql_error());

  // Change the standard location for the observers
  $sql = "select stdlocation, id from observers where stdlocation > 0";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
    $sql2 = "select * from locations where id = \"$get->stdlocation\"";
    $run2 = mysql_query($sql2) or die(mysql_error());
    $get2 = mysql_fetch_object($run2);

    $sql3 = "select id from locations2 where name = \"$get2->name\" and observer = \"$get->id\"";
    $run3 = mysql_query($sql3) or die(mysql_error());

    if ($get3 = mysql_fetch_object($run3))
    {
     $sql4 = "update observers set stdlocation = \"$get3->id\" where stdlocation = \"$get->stdlocation\" and id = \"$get->id\"";

     $run4 = mysql_query($sql4) or die(mysql_error());
    }
    else
    {
      $id = $id + 1;
      $sql4 = "INSERT INTO locations2 (id, name, longitude, latitude, region, country, timezone, observer) VALUES (\"$id\", \"$get2->name\", \"$get2->longitude\", \"$get2->latitude\", \"$get2->region\", \"$get2->country\", \"$get2->timezone\", \"$get->id\")";
      $run4 = mysql_query($sql4) or die(mysql_error());

      $sql4 = "update observers set stdlocation = \"$id\" where stdlocation = \"$get->stdlocation\" and id = \"$get->id\"";

      $run4 = mysql_query($sql4) or die(mysql_error());
    }
  }

 $sql = "DROP TABLE locations";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "RENAME TABLE locations2 TO locations";
 $run = mysql_query($sql) or die(mysql_error());
 
 echo "Database was updated succesfully!\n";

 $db->logout();
 
?>
