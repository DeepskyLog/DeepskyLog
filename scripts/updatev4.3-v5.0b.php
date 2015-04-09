<?php
$inIndex = true;
require_once "../lib/setup/databaseInfo.php";
require_once "../lib/database.php";
require_once "../lib/observers.php";

date_default_timezone_set('UTC');

$objDatabase = new Database ();
$objObserver = new Observers ();

print "Database update add a sendMail field to observers.\n";
$sql = "ALTER TABLE observers ADD sendMail BOOL default '0'";
$run = $objDatabase->execSQL ( $sql );

print "Database update successful.\n";
?>
