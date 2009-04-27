<?php
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 
 $sql = "UPDATE observations SET eyepieceid=0 WHERE eyepieceid IS NULL;";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE observations SET lensid=0 WHERE lensid IS NULL;";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE observations SET filterid=0 WHERE filterid IS NULL;";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "ALTER TABLE observations MODIFY COLUMN eyepieceid INT(11) DEFAULT 0, MODIFY COLUMN filterid INT(11) DEFAULT 0, MODIFY COLUMN lensid INT(11) DEFAULT 0;";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "UPDATE observations SET limmag=0 WHERE limmag IS NULL;";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = " ALTER TABLE observations MODIFY COLUMN limmag FLOAT DEFAULT '0';";
 $run = mysql_query($sql) or die(mysql_error());
 
 print "Database update was successful!\n"

?>