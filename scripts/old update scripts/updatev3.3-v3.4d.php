<?php
require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 
 $sql = "ALTER TABLE objects MODIFY COLUMN datasource VARCHAR(50)";
 $run = mysql_query($sql) or die(mysql_error());

  print "Database update was successful!\n"



?>