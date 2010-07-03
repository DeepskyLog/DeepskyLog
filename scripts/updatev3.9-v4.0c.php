<?php
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 print "Database update will add the observer default FoVs for overview, lookup and detail charts.\n";
 $sql = "ALTER TABLE observers 
          ADD COLUMN overviewFoV VARCHAR(5) NOT NULL DEFAULT '120' AFTER atlaspagefont,
          ADD COLUMN lookupFoV   VARCHAR(5) NOT NULL DEFAULT '60' AFTER overviewFoV,
          ADD COLUMN detailFoV   VARCHAR(5) NOT NULL DEFAULT '15' AFTER lookupFoV;";
 $run = mysql_query($sql) or die(mysql_error());
 print "Database update successful.\n";



?>