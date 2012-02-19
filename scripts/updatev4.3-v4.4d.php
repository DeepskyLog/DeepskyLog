<?php
 $inIndex=true;
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 $objDatabase=new Database();
 print "Database update will set the SN objects to object type 'Supernova', hereby also introducing the 'Supernova' choice for the new objects page.\n";
 $sql = "UPDATE objects o SET o.type='SNOVA' WHERE o.name LIKE 'SN %';";
 $run = mysql_query($sql) or die(mysql_error()); 
 $sql = "UPDATE objects o SET o.type='SNOVA' WHERE o.name LIKE 'PSN %';";
 $run = mysql_query($sql) or die(mysql_error());
 
  print "Database update successful.\n";
?>