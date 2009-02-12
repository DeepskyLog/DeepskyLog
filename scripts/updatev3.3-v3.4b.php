<?php

 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 
 $db = new database;
 $db->newlogin();

 $sql = "ALTER TABLE observations ADD COLUMN dateDec FLOAT NOT NULL DEFAULT 0 AFTER hasDrawing";
 $run = mysql_query($sql) or die(mysql_error());
 
 

 print "Database update was successful!\n"


?>