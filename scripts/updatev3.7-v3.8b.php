<?php

 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 
 print "Database update will add the forms layout storage table.\n";

 $sql = "CREATE TABLE `formlayouts` (
           `observerid` VARCHAR(255) NOT NULL,
           `formName` VARCHAR(255) NOT NULL,
           `layoutName` VARCHAR(255) NOT NULL,
           `restoreColumns` LONGTEXT NOT NULL,
           `orderColumns` LONGTEXT NOT NULL          
         ) TYPE=MyISAM;";
 $run = mysql_query($sql) or die(mysql_error());
 
 
 
 print "Database update successful.\n";
 

 ?>