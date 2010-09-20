<?php
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 print "Database update will add the 'lensactive' field on the lenses table.\n";
 $sql = "ALTER TABLE lenses ADD COLUMN lensactive BOOLEAN NOT NULL DEFAULT true AFTER observer;";
 $run = mysql_query($sql) or die(mysql_error());
 print "Database update successful.\n";
?>
