<?php

 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 
 print "Database update will add a stars table.\n";

 $sql= "DROP TABLE IF EXISTS stars8";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars9";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars10";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars11";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars12";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars13";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars14";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars15";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars16";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars17";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars18";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars19";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars20";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "CREATE TABLE stars8 (
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
 
  $sql = "CREATE TABLE stars9 (
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
 $sql = "CREATE TABLE stars10 (
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
 $sql = "CREATE TABLE stars11 (
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
 $sql = "CREATE TABLE stars12 (
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
 $sql = "CREATE TABLE stars13 (
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
 $sql = "CREATE TABLE stars14 (
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
 $sql = "CREATE TABLE stars15 (
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
 $sql = "CREATE TABLE stars16 (
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
 $sql = "CREATE TABLE stars17 (
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
 $sql = "CREATE TABLE stars18 (
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
 $sql = "CREATE TABLE stars19 (
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
 $sql = "CREATE TABLE stars20 (
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