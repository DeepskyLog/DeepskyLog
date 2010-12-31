<?php
 $inIndex=true;
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 $objDatabase=new Database();
 print "Database update will add the deepskylog session table named 'sessions'.\n";
 $sql ="DROP TABLE IF EXISTS sessions";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "CREATE TABLE sessions (
             id									INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
             name   						VARCHAR(200)     NOT NULL DEFAULT '',
             observerid					VARCHAR(200)     NOT NULL DEFAULT '',
             begindate					DATETIME				 NOT NULL,
             enddate						DATETIME				 NOT NULL,
             locationid 				INTEGER UNSIGNED NOT NULL,
             weather						VARCHAR(500)     NOT NULL DEFAULT '',
             equipment					VARCHAR(500)     NOT NULL DEFAULT '',
             comments						VARCHAR(500)     NOT NULL DEFAULT '',
             language						VARCHAR(255)		 NOT NULL DEFAULT '',
             active							INTEGER UNSIGNED NOT NULL,
             PRIMARY KEY (id)
         )";
 $run = mysql_query($sql) or die(mysql_error());
 
 print "Database update will add the deepskylog sessionObservers table named 'sessionObservers'.\n";
 $sql ="DROP TABLE IF EXISTS sessionObservers";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "CREATE TABLE sessionObservers (
             sessionid					INTEGER UNSIGNED NOT NULL,
             observer						VARCHAR(55)      NOT NULL DEFAULT '',
         PRIMARY KEY (sessionid,observer)
         )";
 $run = mysql_query($sql) or die(mysql_error());
 
 print "Database update will add the deepskylog sessionObservations table named 'sessionObservations'.\n";
 $sql ="DROP TABLE IF EXISTS sessionObservations";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "CREATE TABLE sessionObservations (
             sessionid					INTEGER UNSIGNED NOT NULL,
             observationid			VARCHAR(55)      NOT NULL DEFAULT '',
         PRIMARY KEY (sessionid,observationid)
         )";
 $run = mysql_query($sql) or die(mysql_error());
 
 print "Database update successful.\n";
?>
