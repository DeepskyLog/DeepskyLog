<?php
 $inIndex=true;
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 $objDatabase=new Database();
 print "Database update will update the locations table: remove region and add elevation and checked.\n";
 $sql = "ALTER TABLE locations DROP COLUMN region;";
 $objDatabase->execSQL($sql); 
 $sql = "ALTER TABLE locations ADD COLUMN elevation SMALLINT NOT NULL DEFAULT 0;";
 $objDatabase->execSQL($sql);
 $sql = "ALTER TABLE locations ADD COLUMN checked BOOL NOT NULL DEFAULT '0';";
 $objDatabase->execSQL($sql);
 
  print "Database update successful.\n";
?>