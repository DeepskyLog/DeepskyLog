<?php
 include_once "../lib/database.php";


 $db = new database;
 $db->login();

 
 $sql= "ALTER TABLE `objectnames` ADD COLUMN `altname` VARCHAR(255) NOT NULL AFTER `catindex`, ADD INDEX Index_altname USING BTREE(`altname`);";
 $run = mysql_query($sql) or die(mysql_error());

 $sql= "UPDATE objectnames SET altname=TRIM(CONCAT(objectnames.catalog, \" \", objectnames.catindex));";
 $run = mysql_query($sql) or die(mysql_error());

 
 echo "Database was updated succesfully!\n";

 $db->logout();
 
?>
