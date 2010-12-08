<?php
 $inIndex=true;
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 $objDatabase=new Database();
 print "Database update will add the deepskylog messages storage table named 'messages'.\n";
 $sql ="DROP TABLE IF EXISTS messages";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "CREATE TABLE messages (
             id									INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
             sender							VARCHAR(55)      NOT NULL DEFAULT '',
             receiver						VARCHAR(55)      NOT NULL DEFAULT '',
             subject						VARCHAR(200)     NOT NULL DEFAULT '',
             message						VARCHAR(5000)    NOT NULL DEFAULT '',
             date								DATETIME				 NOT NULL,
         PRIMARY KEY (id)
         )";
 $run = mysql_query($sql) or die(mysql_error());

 print "Database update will add the deepskylog messages read table named 'messages'.\n";
 $sql ="DROP TABLE IF EXISTS messagesRead";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "CREATE TABLE messagesRead (
             id									INTEGER UNSIGNED NOT NULL,
             receiver						VARCHAR(55)      NOT NULL DEFAULT '',
         PRIMARY KEY (id,receiver)
         )";
 $run = mysql_query($sql) or die(mysql_error());
 
 print "Database update will add the deepskylog deleted messages table named 'messages'.\n";
 $sql ="DROP TABLE IF EXISTS messagesDeleted";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "CREATE TABLE messagesDeleted (
             id									INTEGER UNSIGNED NOT NULL,
             receiver						VARCHAR(55)      NOT NULL DEFAULT '',
         PRIMARY KEY (id,receiver)
         )";
 $run = mysql_query($sql) or die(mysql_error());
 
 print "Database update successful.\n";
?>
