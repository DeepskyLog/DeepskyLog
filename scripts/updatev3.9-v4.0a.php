<?php
 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 print "Database update will add the observer query storage table named 'observerqueries'.\n";
 $sql ="DROP TABLE IF EXISTS observerqueries";
 $run = mysql_query($sql) or die(mysql_error());
 $sql = "CREATE TABLE observerqueries (
             observerquerypk   INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
             observerid        VARCHAR(255)     NOT NULL DEFAULT '',
             observerquerytype VARCHAR(10)      NOT NULL DEFAULT '',
             observerqueryname VARCHAR(255)     NOT NULL DEFAULT '',
             observerquery     LONGTEXT         NOT NULL,
         PRIMARY KEY (observerquerypk)
         )";
 $run = mysql_query($sql) or die(mysql_error());
 print "Database update successful.\n";
?>
