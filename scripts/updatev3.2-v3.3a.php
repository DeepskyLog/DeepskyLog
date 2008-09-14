<?php
include_once "../lib/database.php";

$db = new database;
$db->login();

// Insert the estimated small and large diameter in arc minutes
$sql="ALTER TABLE observations ADD COLUMN smallDiameter float NOT NULL default '0.0'";
$run = mysql_query($sql) or die(mysql_error());

$sql="ALTER TABLE observations ADD COLUMN largeDiameter float NOT NULL default '0.0'";
$run = mysql_query($sql) or die(mysql_error());

// stellar
$sql="ALTER TABLE observations ADD COLUMN stellar int(1) NOT NULL default '-1'";
$run = mysql_query($sql) or die(mysql_error());

// extended
$sql="ALTER TABLE observations ADD COLUMN extended int(1) NOT NULL default '-1'";
$run = mysql_query($sql) or die(mysql_error());

// resolved
$sql="ALTER TABLE observations ADD COLUMN resolved int(1) NOT NULL default '-1'";
$run = mysql_query($sql) or die(mysql_error());

// mottled
$sql="ALTER TABLE observations ADD COLUMN mottled int(1) NOT NULL default '-1'";
$run = mysql_query($sql) or die(mysql_error());

// Only for open clusters : characterType, see page 17 of http://deepsky.fg-vds.de/download/dsl8.pdf
$sql="ALTER TABLE observations ADD COLUMN characterType varchar(1) NOT NULL default ''";
$run = mysql_query($sql) or die(mysql_error());

// Only for open clusters : unusualShape
$sql="ALTER TABLE observations ADD COLUMN unusualShape int(1) NOT NULL default '-1'";
$run = mysql_query($sql) or die(mysql_error());

// Only for open clusters : partlyUnresolved
$sql="ALTER TABLE observations ADD COLUMN partlyUnresolved int(1) NOT NULL default '-1'";
$run = mysql_query($sql) or die(mysql_error());

// Only for open clusters : colorContrasts
$sql="ALTER TABLE observations ADD COLUMN colorContrasts int(1) NOT NULL default '-1'";
$run = mysql_query($sql) or die(mysql_error());

print "Database update was successful!"
?>
