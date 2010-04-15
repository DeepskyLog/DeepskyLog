<?php


 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 require_once "../lib/reportlayouts.php";

 $loggedUser="defaultuser";
  
 print "Database update will add the reports layout storage table.\n";
 $sql="DROP TABLE IF EXISTS reportlayouts";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "CREATE TABLE `reportlayouts` (
  `reportlayoutpk` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `observerid`     VARCHAR(255) NOT NULL DEFAULT '',
  `reportname`     VARCHAR(255) NOT NULL DEFAULT '',
  `reportlayout`   VARCHAR(255) NOT NULL DEFAULT '',
  `fieldname`      VARCHAR(255) NOT NULL DEFAULT '',
  `fieldline`      INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `fieldposition`  INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `fieldwidth`     INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `fieldheight`    INTEGER UNSIGNED NOT NULL DEFAULT 12,
  `fieldstyle`     VARCHAR(255) NOT NULL DEFAULT '',
  primary key(`reportlayoutpk`)
  ) TYPE=MyISAM;";
 $run = mysql_query($sql) or die(mysql_error());
 
 print "Starting making default report profiles.\n";
 $objReportLayout->saveLayoutField("execute_query_objects","default","bottom",0,40,0,0,'');
 
 
 print "Database update successful.\n";

?>
