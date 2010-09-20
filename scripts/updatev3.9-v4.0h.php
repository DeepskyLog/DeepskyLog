<?php
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 print "Database update will add the 'instrumentactive' field on the instrument table.\n";
 $sql = "ALTER TABLE instruments ADD COLUMN instrumentactive BOOLEAN NOT NULL DEFAULT true AFTER observer;";
 $run = mysql_query($sql) or die(mysql_error());
 print "Database update successful.\n";
?>
