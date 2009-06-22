<?php
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 
 $sql = "UPDATE observations SET time=time-2400 WHERE time >= 2400";
 $run = mysql_query($sql) or die(mysql_error());
 
 
 $sql = "UPDATE observations SET dateDec=date+(time/2400) WHERE time >= 0";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "UPDATE observations SET dateDec=date+100000000 WHERE time < 0";
 $run = mysql_query($sql) or die(mysql_error());
 
 print "Database update was successful!\n"

?>