<?php
 include_once "../lib/database.php";
 include_once "../lib/instruments.php";
 include_once "../lib/locations.php";


 $db = new database;
 $dbid = $db->login();

 // Create table for observation lists 
 $sql= "CREATE TABLE IF NOT EXISTS `eyepieces` (`id` int(11) NOT NULL auto_increment, `name` varchar(255) NOT NULL default '', `focalLength` int NOT NULL default '0', `apparentFOV` int NOT NULL default '0', `observer` varchar(255) NOT NULL default '', primary key(id)) ENGINE = MyISAM;";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO eyepieces (name, focalLength, apparentFOV) VALUES (\"\", \"0\", \"0\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "ALTER TABLE observations ADD eyepieceid int default NULL";
 $run = mysql_query($sql) or die(mysql_error());

 echo "Database was updated succesfully!\n";

 $db->logout();
?>
