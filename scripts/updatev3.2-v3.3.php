<?php
 include_once "../lib/database.php";
 include_once "../lib/objects.php";
 include_once "../lib/atlasses.php";
 
 $db = new database;
 $db->login();

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

 $sql = "SELECT * FROM objects";
 $run = mysql_query($sql) or die(mysql_error());

 while($get = mysql_fetch_object($run))
 {
  $ra = $get->ra;
  $dec = $get->decl;
  $psa = trim($atlas->calculatePocketSkyAtlasPage($ra, $dec));
  $torresB = trim($atlas->calculateTorresBPage($ra, $dec));
  $torresBC = trim($atlas->calculateTorresBCPage($ra, $dec));
  $torresC = trim($atlas->calculateTorresCPage($ra, $dec));
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
 
 print "Database update was successful!\n"
?>
