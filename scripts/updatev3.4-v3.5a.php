<?php

 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 
 print "Database update will add a stars table.\n";

 $sql= "DROP TABLE IF EXISTS stars";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "CREATE TABLE stars (
  type        varchar(7)                     DEFAULT 'AA1STAR',
  starPK      INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  nameHR      VARCHAR(4)                     DEFAULT '',
  nameCon     VARCHAR(3)                     DEFAULT '',
  nameBayer   VARCHAR(3)                     DEFAULT '',
  nameBayer2  VARCHAR(2)                     DEFAULT '',
  nameDM      VARCHAR(12)                    DEFAULT '',
  nameHD      VARCHAR(6)                     DEFAULT '',
  nameSOA     VARCHAR(6)                     DEFAULT '',
  nameFK5     VARCHAR(4)                     DEFAULT '',
  nameADS     VARCHAR(6)                     DEFAULT '',
  nameADSComp VARCHAR(2)                     DEFAULT '',
  RA2000      FLOAT                 NOT NULL           ,
  DE2000      FLOAT                 NOT NULL           ,
  RA2000I     INTEGER      UNSIGNED NOT NULL           ,
  DE2000I     INTEGER      UNSIGNED NOT NULL           ,
  RA2000S     VARCHAR(8)            NOT NULL           ,
  DE2000S     VARCHAR(8)            NOT NULL           ,
  vMag        INTEGER(4)   SIGNED                      ,
  spType      VARCHAR(25)                    DEFAULT '',
  PRIMARY KEY   (starPK)                               ,
  INDEX       DE(DE2000)                               ,
  INDEX       RA(RA2000)                               ,
  INDEX       SearchIndex(vMag,RA2000,DE2000)
  )";
 
 $run = mysql_query($sql) or die(mysql_error());
 
 
 print "Database update succesful.\n";
 
?>