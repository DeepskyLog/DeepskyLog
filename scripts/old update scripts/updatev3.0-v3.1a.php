<?php
 include_once "../lib/database.php";
 include_once "../lib/objects.php";

 $db = new database;
 $db->login();

 $object = new Objects;

 // Insert the taki pages
 $sql="ALTER TABLE objects ADD COLUMN taki varchar(3) NOT NULL default ''";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "SELECT * FROM objects";
 $run = mysql_query($sql) or die(mysql_error());

 while($get = mysql_fetch_object($run))
 {
  $ra = $get->ra;
  $dec = $get->decl;
  $taki = trim($object->calculateTakiPage($ra, $dec));
  $name = $get->name;
	
  $sql2 = "UPDATE objects SET taki = \"$taki\" WHERE name = \"$name\"";
  $run2 = mysql_query($sql2) or die(mysql_error());
 }
?>
