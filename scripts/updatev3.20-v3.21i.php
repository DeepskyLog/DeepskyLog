<?php
  include_once "../lib/database.php";

  $db = new database;
  $dbid = $db->login();
	
  $sql = "ALTER TABLE `deepskylog`.`objects` MODIFY COLUMN `description` VARCHAR(1024) NOT NULL DEFAULT '';";
  $run = mysql_query($sql) or die(mysql_error());

  echo "Database was updated succesfully!\n";

  $db->logout();
?>
