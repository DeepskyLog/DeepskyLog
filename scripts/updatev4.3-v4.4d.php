<?php
 $inIndex=true;
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 $objDatabase=new Database();
 print "Database update will set the SN objects to object type 'Supernova', hereby also introducing the 'Supernova' choice for the new objects page.\n";
 $sql = "UPDATE objects o SET o.type='SNOVA' WHERE o.name LIKE 'SN %';";
 $objDatabase->execSQL($sql); 
 $sql = "UPDATE objects o SET o.type='SNOVA' WHERE o.name LIKE 'PSN %';";
 $objDatabase->execSQL($sql);
 
  print "Database update successful.\n";
?>