<?php



 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 
 print "Database update will star and dso magnitudes for atlas pages in user profile.\n";

 $sql="ALTER TABLE `observers`
            ADD COLUMN `overviewdsos`  VARCHAR(5) NOT NULL DEFAULT '' AFTER `copyright`,
            ADD COLUMN `lookupdsos`    VARCHAR(5) NOT NULL DEFAULT '' AFTER `overviewdsos`,
            ADD COLUMN `detaildsos`    VARCHAR(5) NOT NULL DEFAULT '' AFTER `lookupdsos`,
            ADD COLUMN `overviewstars` VARCHAR(5) NOT NULL DEFAULT '' AFTER `detaildsos`,
            ADD COLUMN `lookupstars`   VARCHAR(5) NOT NULL DEFAULT '' AFTER `overviewstars`,
            ADD COLUMN `detailstars`   VARCHAR(5) NOT NULL DEFAULT '' AFTER `lookupstars`;";
 $run = mysql_query($sql) or die(mysql_error());
 
 
 
 print "Database update successful.\n";
 


?>
