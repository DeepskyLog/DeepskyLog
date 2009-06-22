<?php

 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";

  print "Database update will set all null values to the default value for the seeing field of the observations table.\n";
 
 $sql = "UPDATE observations SET seeing=0 WHERE (seeing IS NULL);";
 $run = mysql_query($sql) or die(mysql_error());
 
 print "Database update will add a default value to the seeing field of the observations table.\n";
 
 $sql = "ALTER TABLE observations MODIFY COLUMN seeing INT(1) DEFAULT 0;";
 $run = mysql_query($sql) or die(mysql_error());
 
 
 print "Database update was successful!\n";

?>