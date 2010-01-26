<?php

 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 
 print "Database update will add a stars table.\n";

 $sql= "DROP TABLE IF EXISTS stars145";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1525";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1550";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1625";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1650";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1675";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1725";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1750";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1775";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "CREATE TABLE stars140 (
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
  INDEX         SearchIndex(RA2000mas,DE2000mas)
  )";
  $run = mysql_query($sql) or die(mysql_error());
 
  $sql = "CREATE TABLE stars145 (
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
  INDEX         SearchIndex(RA2000mas,DE2000mas)
  )";
  $run = mysql_query($sql) or die(mysql_error());
 
  $sql = "CREATE TABLE stars1500 (
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
  INDEX         SearchIndex(RA2000mas,DE2000mas)
  )";
  $run = mysql_query($sql) or die(mysql_error());
  
  $sql = "CREATE TABLE stars1525 (
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
  INDEX         SearchIndex(RA2000mas,DE2000mas)
  )";
  $run = mysql_query($sql) or die(mysql_error());
  
 $sql = "CREATE TABLE stars1550 (
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
  INDEX         SearchIndex(RA2000mas,DE2000mas)
  )";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "CREATE TABLE stars1575 (
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
  INDEX         SearchIndex(RA2000mas,DE2000mas)
  )";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "CREATE TABLE stars1600 (
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
  INDEX         SearchIndex(RA2000mas,DE2000mas)
  )";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "CREATE TABLE stars1625 (
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
  INDEX         SearchIndex(RA2000mas,DE2000mas)
  )";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "CREATE TABLE stars1650 (
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
  INDEX         SearchIndex(RA2000mas,DE2000mas)
  )";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "CREATE TABLE stars1675 (
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
  INDEX         SearchIndex(RA2000mas,DE2000mas)
  )";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "CREATE TABLE stars1700 (
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
  INDEX         SearchIndex(RA2000mas,DE2000mas)
  )";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "CREATE TABLE stars1725 (
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
  INDEX         SearchIndex(RA2000mas,DE2000mas)
  )";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "CREATE TABLE stars1750 (
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
  INDEX         SearchIndex(RA2000mas,DE2000mas)
  )";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "CREATE TABLE stars1775 (
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
  INDEX         SearchIndex(RA2000mas,DE2000mas)
  )";
 
 $run = mysql_query($sql) or die(mysql_error());

 print "Database update succesful.\n";
 
?>