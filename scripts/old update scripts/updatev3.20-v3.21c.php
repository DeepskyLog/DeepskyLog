<?php
 include_once "../lib/database.php";
 include_once "../lib/instruments.php";

 $db = new database;
 $dbid = $db->login();
 
 $sql= "ALTER TABLE `observerobjectlist` " .
       "ADD COLUMN `objectshowname` VARCHAR(255) NOT NULL AFTER `objectplace`";
 $run = mysql_query($sql) or die(mysql_error());

 $sql= "ALTER TABLE `observerobjectlist` ".
	     "ADD INDEX Index_list3(`observerid`, `objectshowname`, `listname`);";
 $run = mysql_query($sql) or die(mysql_error());

 $sql= "UPDATE observerobjectlist " .
       "SET objectshowname = objectname";
 $run = mysql_query($sql) or die(mysql_error());

 echo "Database was updated succesfully!\n";

 $db->logout();
?>
