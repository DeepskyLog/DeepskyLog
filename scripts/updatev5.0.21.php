<?php
$inIndex = true;
require_once "../lib/setup/databaseInfo.php";
require_once "../lib/database.php";
date_default_timezone_set ( 'UTC' );

$objDatabase = new Database ();
print "Database update will add a password_change_requests table.<br />\n";

$sql ="DROP TABLE IF EXISTS password_change_requests";
$run = $objDatabase->execSQL($sql);
$sql = "CREATE TABLE password_change_requests (
          id                                   VARCHAR(255)            NOT NULL DEFAULT '',
          time                                 DATETIME                DEFAULT CURRENT_TIMESTAMP,
          userid                               VARCHAR(255)            NOT NULL DEFAULT ''
);";
$objDatabase->execSQL ( $sql );

print "Database update successful.\n";
?>
