<?php

 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";

  print "Database update will set a default value for the comets description in the comet observations table.\n";
 
 $sql = "ALTER TABLE cometobservations MODIFY COLUMN description LONGTEXT NOT NULL DEFAULT '';";
 $run = mysql_query($sql) or die(mysql_error());
 
 print "Database update will add a default value to the seeing field of the observations table.\n";

?>