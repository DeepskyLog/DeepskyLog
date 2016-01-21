<?php
$inIndex = true;
require_once "../../lib/setup/databaseInfo.php";
require_once "../../lib/database.php";
date_default_timezone_set ( 'UTC' );

$objDatabase = new Database ();
print "Database update will update the observer table: add showInches to set wether user wants diameters in inches or mm.<br />\n";
$sql = "ALTER TABLE deepskylog.observers ADD COLUMN showInches TINYINT(1) NOT NULL DEFAULT '0';";
$objDatabase->execSQL ( $sql );

print "Database update successful.\n";
?>
