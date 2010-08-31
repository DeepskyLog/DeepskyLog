<?php
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 print "Database update will add the observer default photo sizes.\n";
 $sql = "ALTER TABLE observers 
          ADD COLUMN photosize1 VARCHAR(5) NOT NULL DEFAULT '60' AFTER atlaspagefont,
          ADD COLUMN photosize2 VARCHAR(5) NOT NULL DEFAULT '25' AFTER overviewFoV;";
 $run = mysql_query($sql) or die(mysql_error());
 print "Database update successful.\n";



?>