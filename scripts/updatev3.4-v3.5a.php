<?php

 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 
 print "Database update will add a stars table.\n";

 $sql= "DROP TABLE IF EXISTS stars";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "CREATE TABLE stars (
  type          varchar(7)                     DEFAULT 'AA1STAR',
  nameCon       VARCHAR(3)                     DEFAULT '',
  nameBayer     VARCHAR(3)                     DEFAULT '',
  nameBayer2    VARCHAR(2)                     DEFAULT '',
  nameFlamSteed VARCHAR(3)                     DEFAULT '',
  name          VARCHAR(25)                    DEFAULT '',
  RA2000mas     INTEGER                        NOT NULL,
  DE2000mas     INTEGER                        NOT NULL,
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED,
  spType        VARCHAR(25)                    DEFAULT '',
  INDEX         SearchIndex(vMag,RA2000mas,DE2000mas)
  )";
 
 $run = mysql_query($sql) or die(mysql_error());
 
 
 print "Database update succesful.\n";
 
?>