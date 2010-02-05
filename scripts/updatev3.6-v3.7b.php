<?php

 require_once "../lib/setup/databaseInfo.php";
 require_once "../lib/database.php";
 
 print "Database update will rearrange stars tables.\n";

 
 
 // GELIEVE NA TE KIJKEN OF DEZE INDEXEN BESTAAN IN DE DATABANK
 // ANDERS LOOP HET SCRIPT HIEROP VAST
 
 // ALS ZE ER NIET ZIJN, MAG DIT STUK IN COMMENTAAR 
 
 $sql="ALTER TABLE stars8 DROP INDEX SearchIndex;";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars9 DROP INDEX SearchIndex;";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars10 DROP INDEX SearchIndex;";
 $run = mysql_query($sql) or die(mysql_error());
 $sql="ALTER TABLE stars11 DROP INDEX SearchIndex;";
 $run = mysql_query($sql) or die(mysql_error());
 
 
 // TOT HIER.
 
 $sql= "DROP TABLE IF EXISTS stars12";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars13";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars14";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars140";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars145";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars15";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1500";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1525";
 $run = mysql_query($sql) or die(mysql_error());
  $sql= "DROP TABLE IF EXISTS stars1550";
 $run = mysql_query($sql) or die(mysql_error());
  $sql= "DROP TABLE IF EXISTS stars1575";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars16";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1600";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1625";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1650";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1675";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars17";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1700";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1725";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1750";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars1775";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars18";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars19";
 $run = mysql_query($sql) or die(mysql_error());
 $sql= "DROP TABLE IF EXISTS stars20";
 $run = mysql_query($sql) or die(mysql_error());
 
 
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
 
  $sql = "CREATE TABLE stars12 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());
 
  $sql = "CREATE TABLE stars13 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "CREATE TABLE stars140 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());
  
  $sql = "CREATE TABLE stars145 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());
  

  $sql = "CREATE TABLE stars1500 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "CREATE TABLE stars1525 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "CREATE TABLE stars1550 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "CREATE TABLE stars1575 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());
  
  $sql = "CREATE TABLE stars1600 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());
  
  $sql = "CREATE TABLE stars1625 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());
  
  $sql = "CREATE TABLE stars1650 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());
  
  $sql = "CREATE TABLE stars1675 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());
  
  $sql = "CREATE TABLE stars1700 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());
  
  $sql = "CREATE TABLE stars1725 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());
  
  $sql = "CREATE TABLE stars1750 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());
  
  $sql = "CREATE TABLE stars1775 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());
  
  $sql = "CREATE TABLE stars18 (
  RA2000        FLOAT                          NOT NULL,
  DE2000        FLOAT                          NOT NULL,
  vMag          INTEGER                        SIGNED
  )";
  $run = mysql_query($sql) or die(mysql_error());
  

 print "Database update succesful.\n";
 
?>