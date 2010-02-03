<?php


 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 
 print "Database update will rearrange stars tables indexes. ONLY RUN THIS AFTER THE STARS IMPORT! Rerun updatev3.6-v3.7b.php if this is not the case.\n";



 $sql="ALTER TABLE stars8 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars9 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars10 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars11 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars12 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars13 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars140 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars145 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars1500 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars1525 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars1550 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars1575 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars1600 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars1625 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars1650 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars1675 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars1700 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars1725 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars1750 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars1775 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars18 ADD INDEX SearchIndex(RA2000, DE2000);";
 $run = mysql_query($sql) or die(mysql_error());
 
 
 print "Database update successful.\n";
 
?>