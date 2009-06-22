<?php

 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";

 print "Database update will add a magnification field to the observations table.\n";
 
 $sql = "ALTER TABLE observations ADD COLUMN magnification VARCHAR(6) NOT NULL DEFAULT '' AFTER dateDec;";
 $run = mysql_query($sql) or die(mysql_error());
 
 print "Database update was successful!\n";





?>