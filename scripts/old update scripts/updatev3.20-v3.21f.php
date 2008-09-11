<?php
 include_once "../lib/database.php";

 $db = new database;
 $dbid = $db->login();

 // Create table for filters lists 
 $sql= "CREATE TABLE IF NOT EXISTS `lenses` (`id` int(11) NOT NULL auto_increment, `name` varchar(255) NOT NULL default '', `factor` float NOT NULL default '0.0', `observer` varchar(255) NOT NULL default '', primary key(id)) ENGINE = MyISAM;";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO lenses (name, factor, observer) VALUES (\"\", \"0.0\", \"\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "ALTER TABLE observations ADD lensid int default NULL";
 $run = mysql_query($sql) or die(mysql_error());

 echo "Database was updated succesfully!\n";

 $db->logout();
?>
