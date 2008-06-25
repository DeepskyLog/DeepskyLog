<?php
 include_once "../lib/database.php";
 include_once "../lib/instruments.php";

 $db = new database;
 $dbid = $db->login();

 $sql = "ALTER TABLE eyepieces MODIFY focalLength float NOT NULL default '0.0'";
 $run = mysql_query($sql) or die(mysql_error());

 echo "Database was updated succesfully!\n";

 $db->logout();
?>
