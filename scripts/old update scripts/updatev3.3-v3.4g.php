<?php

 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";

 print "Database update will add a registration date field.\n";
 
 $sql = "ALTER TABLE observers ADD COLUMN registrationDate VARCHAR(14) NOT NULL DEFAULT '00000000 00:00' AFTER standardAtlasCode;";
 $run = mysql_query($sql) or die(mysql_error());

 print "The registration dates will be set to 20090101 00:00 for existing observers.\n";
 
 $sql = "UPDATE observers SET registrationDate='20090101 00:00';";
 $run = mysql_query($sql) or die(mysql_error());
 
 print "Database update was successful!\n";



?>