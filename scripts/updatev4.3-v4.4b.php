<?php
 $inIndex=true;
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 $objDatabase=new Database();
 print "Database update will add the deepskylog atlasses fields as columns in the objects table (column definition, no data).\n";
 $sql = "ALTER TABLE objects ADD COLUMN DSLDL VARCHAR(4) NOT NULL DEFAULT 0 ;";
 $run = mysql_query($sql) or die(mysql_error()); 
 $sql = "ALTER TABLE objects ADD COLUMN DSLDP VARCHAR(4) NOT NULL DEFAULT 0 ;";
 $run = mysql_query($sql) or die(mysql_error()); 
  $sql = "ALTER TABLE objects ADD COLUMN DSLLL VARCHAR(4) NOT NULL DEFAULT 0 ;";
 $run = mysql_query($sql) or die(mysql_error()); 
  $sql = "ALTER TABLE objects ADD COLUMN DSLLP VARCHAR(4) NOT NULL DEFAULT 0 ;";
 $run = mysql_query($sql) or die(mysql_error()); 
  $sql = "ALTER TABLE objects ADD COLUMN DSLOL VARCHAR(4) NOT NULL DEFAULT 0 ;";
 $run = mysql_query($sql) or die(mysql_error()); 
  $sql = "ALTER TABLE objects ADD COLUMN DSLOP VARCHAR(4) NOT NULL DEFAULT 0 ;";
 $run = mysql_query($sql) or die(mysql_error()); 
  $sql = "ALTER TABLE objects ADD COLUMN DeepskyHunter VARCHAR(4) NOT NULL DEFAULT 0 ;";
 $run = mysql_query($sql) or die(mysql_error()); 
 print "Database update successful.\n";
?>