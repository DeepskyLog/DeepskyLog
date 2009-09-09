<?php

 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 
 print "Database update will extra fields for double stars.\n";

 // equalBrightness
 $sql="ALTER TABLE observations ADD COLUMN equalBrightness int(1) NOT NULL default '-1'";
 $run = mysql_query($sql) or die(mysql_error());

 // niceField
 $sql="ALTER TABLE observations ADD COLUMN niceField int(1) NOT NULL default '-1'";
 $run = mysql_query($sql) or die(mysql_error());

 // component1
 $sql="ALTER TABLE observations ADD COLUMN component1 int(1) NOT NULL default '-1'";
 $run = mysql_query($sql) or die(mysql_error());

 // component2
 $sql="ALTER TABLE observations ADD COLUMN component2 int(1) NOT NULL default '-1'";
 $run = mysql_query($sql) or die(mysql_error());
?>
