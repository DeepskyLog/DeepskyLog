<?php
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 print "Database update will add the 'eyepieceactive' field on the eyepieces table.\n";
 $sql = "ALTER TABLE eyepieces ADD COLUMN eyepieceactive BOOLEAN NOT NULL DEFAULT true AFTER maxFocalLength;";
 $run = mysql_query($sql) or die(mysql_error());
 print "Database update successful.\n";
?>
