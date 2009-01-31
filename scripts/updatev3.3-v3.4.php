<?php
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 
 $db = new database;
 $db->newlogin();

 $sql = "ALTER TABLE observations ADD COLUMN hasDrawing INT(1) NOT NULL DEFAULT 0 AFTER SQM";

 

 print "Database update was successful!\n"
?>
