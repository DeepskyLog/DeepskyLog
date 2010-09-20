<?php
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 print "Database update will add the 'locationactive' field on the eyepieces table.\n";
 $sql = "ALTER TABLE locations ADD COLUMN locationactive BOOLEAN NOT NULL DEFAULT true AFTER observer;";
 $run = mysql_query($sql) or die(mysql_error());
 print "Database update successful.\n";
?>
