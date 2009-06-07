<?php

 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 require_once "../lib/eyepieces.php";
 require_once "../lib/filters.php";
 require_once "../lib/lenses.php";
 require_once "../lib/locations.php";
 
 print "Database update will set default eyepiece name to '-----'.\n";
 $objEyepiece->setEyepieceProperty(1,'name','-----');
 print "Database update will set default filter name to '-----'.\n";
 $objFilter->setFilterProperty(1,'name','-----');
 print "Database update will set default lens name to '-----'.\n";
 $objLens->setLensProperty(1,'name','-----');
 print "Database update will set default location name to '-----'.\n";
 $objLocation->setLocationProperty(1000,'name','-----');
 $sql = "UPDATE locations SET name='-----' WHERE (name=\"\");";
 $run = mysql_query($sql) or die(mysql_error());
 
 print "Database update succesful.\n";
 
?>