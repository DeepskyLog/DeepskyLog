<?php
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 
 $db = new database;
 //$db->login();
 $db->newlogin();

 require_once "../lib/objects.php";

//print "TEST" . $db;

 
 $sql = "CREATE TABLE atlasses (atlasCode VARCHAR(100) NOT NULL DEFAULT 'Atlas', PRIMARY KEY (`atlasCode`))";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO atlasses(atlasCode) VALUES ('urano')";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "INSERT INTO atlasses(atlasCode) VALUES ('urano_new')";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "INSERT INTO atlasses(atlasCode) VALUES ('sky')";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "INSERT INTO atlasses(atlasCode) VALUES ('milleniumbase')";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "INSERT INTO atlasses(atlasCode) VALUES ('taki')";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "INSERT INTO atlasses(atlasCode) VALUES ('psa')";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "INSERT INTO atlasses(atlasCode) VALUES ('torresB')";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "INSERT INTO atlasses(atlasCode) VALUES ('torresBC')";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "INSERT INTO atlasses(atlasCode) VALUES ('torresC')";
 $run = mysql_query($sql) or die(mysql_error());

 require_once "../lib/atlasses.php";

 $object = new Objects;
 $atlas = new Atlasses;
 
 $sql = "ALTER TABLE objects MODIFY COLUMN description VARCHAR(1024) NOT NULL DEFAULT '';";
 $run = mysql_query($sql) or die(mysql_error());

 // Insert the psa, torresB, torresBC, torresC pages
 $sql="ALTER TABLE objects ADD COLUMN psa varchar(3) NOT NULL default ''";
 $run = mysql_query($sql) or die(mysql_error());

 $sql="ALTER TABLE objects ADD COLUMN torresB varchar(3) NOT NULL default ''";
 $run = mysql_query($sql) or die(mysql_error());

 $sql="ALTER TABLE objects ADD COLUMN torresBC varchar(3) NOT NULL default ''";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql="ALTER TABLE objects ADD COLUMN torresC varchar(3) NOT NULL default ''";
 $run = mysql_query($sql) or die(mysql_error());

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

// SQM for the observations
$sql="ALTER TABLE observations ADD COLUMN SQM float NOT NULL default '-1.0'";
$run = mysql_query($sql) or die(mysql_error());

 $sql = "SELECT * FROM objects";
 $run = mysql_query($sql) or die(mysql_error());

 while($get = mysql_fetch_object($run))
 {
  $ra = $get->ra;
  $dec = $get->decl;
  $psa = trim($atlas->calculateAtlasPage('psa',$ra, $dec));
  $torresB = trim($atlas->calculateAtlasPage('torresB',$ra, $dec));
  $torresBC = trim($atlas->calculateAtlasPage('torresBC',$ra, $dec));
  $torresC = trim($atlas->calculateAtlasPage('torresC',$ra, $dec));
  $name = $get->name;
	
  $sql2 = "UPDATE objects SET psa = \"$psa\" WHERE name = \"$name\"";
  $run2 = mysql_query($sql2) or die(mysql_error());
  $sql2 = "UPDATE objects SET torresB = \"$torresB\" WHERE name = \"$name\"";
  $run2 = mysql_query($sql2) or die(mysql_error());
  $sql2 = "UPDATE objects SET torresBC = \"$torresBC\" WHERE name = \"$name\"";
  $run2 = mysql_query($sql2) or die(mysql_error());
  $sql2 = "UPDATE objects SET torresC = \"$torresC\" WHERE name = \"$name\"";
  $run2 = mysql_query($sql2) or die(mysql_error());
 }
 
 $sql = "ALTER TABLE objects ADD COLUMN milleniumbase VARCHAR(4) NOT NULL AFTER torresC";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE objects SET milleniumbase=(LEFT(objects.millenium, INSTR(objects.millenium, '/')-1))";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "ALTER TABLE observers ADD COLUMN standardAtlasCode VARCHAR(100) NOT NULL DEFAULT '' AFTER usedLanguages";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE observers SET standardAtlasCode = 'urano' WHERE stdAtlas=0";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE observers SET standardAtlasCode = 'urano_new' WHERE stdAtlas=1";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE observers SET standardAtlasCode = 'sky' WHERE stdAtlas=2";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE observers SET standardAtlasCode = 'milleniumbase' WHERE stdAtlas=3";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE observers SET standardAtlasCode = 'taki' WHERE stdAtlas=4";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE observers SET standardAtlasCode = 'psa' WHERE stdAtlas=5";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE observers SET standardAtlasCode = 'torresB' WHERE stdAtlas=6";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE observers SET standardAtlasCode = 'torresBC' WHERE stdAtlas=7";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE observers SET standardAtlasCode = 'torresC' WHERE stdAtlas=8";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "UPDATE observerobjectlist, objects SET observerobjectlist.description=objects.description WHERE observerobjectlist.objectname=objects.name AND observerobjectlist.description=\"\"";
 $run = mysql_query($sql) or die(mysql_error());


 print "Database update was successful!\n"
?>
