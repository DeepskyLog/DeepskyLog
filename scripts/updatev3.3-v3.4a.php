<?php
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 require_once "../lib/observations.php";
 
 $db = new database;
 $db->newlogin();

 $upload_dir = '../deepsky/drawings';
 $dir = opendir($upload_dir);
 while (FALSE !== ($file = readdir($dir))) 
   if((!(("."==$file)OR(".."==$file)OR(".svn"==$file)))
	 && (strpos($file,'resized')==0))
		 echo $objObservation->setDsObservationProperty(substr($file,0,strpos($file,'.jpg')),'hasDrawing',1);

 print "Database update was successful!\n"
?>
