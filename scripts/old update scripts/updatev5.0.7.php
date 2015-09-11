<?php
$inIndex = true;
require_once "../../lib/setup/databaseInfo.php";
require_once "../../lib/database.php";
date_default_timezone_set ( 'UTC' );

$objDatabase = new Database ();
print "Database update will update the observer table: add version to the last version where the What's new was read.<br />\n";
$sql = "ALTER TABLE observers ADD COLUMN version VARCHAR(10) NOT NULL DEFAULT '5.0.1';";
$objDatabase->execSQL ( $sql );

print "Database update successful.\n";
?>
