<?php



 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 
 print "Database update will add a logging table.\n";

 $sql= "DROP TABLE IF EXISTS logging";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "CREATE TABLE logging (
  loginid           VARCHAR(255)          NOT NULL DEFAULT '',
  logdate           INTEGER               NOT NULL DEFAULT '19000101',
  logtime           INTEGER               NOT NULL DEFAULT '000000',
  logurl            VARCHAR(255)          NOT NULL DEFAULT '',
  navigator         VARCHAR(255)          NOT NULL DEFAULT '',
  screenresolution  VARCHAR(12)           NOT NULL DEFAULT '',
  language          VARCHAR(2)            NOT NULL DEFAULT '',
  sqlstatement      VARCHAR(255)          NOT NULL DEFAULT '',
  INDEX             IndexLogDateTime(logdate,logtime)
  )";
 
 $run = mysql_query($sql) or die(mysql_error());
 print "Database update succesful.\n";
 

?>