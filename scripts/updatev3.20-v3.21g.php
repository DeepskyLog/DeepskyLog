<?php
 include_once "../lib/database.php";

 $db = new database;
 $dbid = $db->login();


 $sql= "ALTER TABLE objects ADD COLUMN `description` VARCHAR(1024) NOT NULL AFTER `SBObj`;";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql= "ALTER TABLE `observerobjectlist` ADD COLUMN `description` VARCHAR(1024) NOT NULL AFTER `objectshowname`;";
 $run = mysql_query($sql) or die(mysql_error());
 
 echo "Database was updated succesfully!\n";

 $db->logout();
?>
