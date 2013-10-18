<?php
 $inIndex=true;
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 $objDatabase=new Database();
 print "Database update will add the deepskylog atlas as one of the standard atlasses.\n";
 $sql = "INSERT INTO atlasses VALUES ('DSLOP');";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "INSERT INTO atlasses VALUES ('DSLLP');";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "INSERT INTO atlasses VALUES ('DSLDP');";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "INSERT INTO atlasses VALUES ('DSLOL');";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "INSERT INTO atlasses VALUES ('DSLLL');";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "INSERT INTO atlasses VALUES ('DSLDL');";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "INSERT INTO atlasses VALUES ('DeepskyHunter');";
 $run = mysql_query($sql) or die(mysql_error());
 
 print "Database update successful.\n";
?>
