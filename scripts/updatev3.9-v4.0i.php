<?php
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 print "Database update will add the 'filteractive' field on the filters table.\n";
 $sql = "ALTER TABLE filters ADD COLUMN filteractive BOOLEAN NOT NULL DEFAULT true AFTER observer;";
 $run = mysql_query($sql) or die(mysql_error());
 print "Database update successful.\n";
?>
