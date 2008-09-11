<?php
 include_once "../lib/database.php";
 include_once "../lib/instruments.php";

 $db = new database;
 $dbid = $db->login();
 $instruments = new Instruments;

 // Create table for filters lists 
 $sql= "CREATE TABLE IF NOT EXISTS `filters` (`id` int(11) NOT NULL auto_increment, `name` varchar(255) NOT NULL default '', `type` int NOT NULL default '0', `color` int NOT NULL default '0', `wratten` varchar(5) NOT NULL default '', `schott` varchar(5) NOT NULL default '', `observer` varchar(255) NOT NULL default '', primary key(id)) ENGINE = MyISAM;";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO filters (name, type, color, wratten, schott, observer) VALUES (\"\", \"0\", \"0\", \"\", \"\", \"\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "ALTER TABLE observations ADD filterid int default NULL";
 $run = mysql_query($sql) or die(mysql_error());

 // Copy the structure of the instruments to instruments2
 $sql = "CREATE TABLE instruments2 SELECT * from instruments Where '0' = '1'";
 $run = mysql_query($sql) or die(mysql_error());

 // Add the new observer column and the new fixedMagnification column.
 $sql = "ALTER TABLE instruments2 ADD fixedMagnification int NOT NULL default '0'";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "ALTER TABLE instruments2 ADD observer varchar(255) default ''";
 $run = mysql_query($sql) or die(mysql_error());

 $instruments = new Instruments();

 // The database is created and empty
 // Select the instruments and observerid from the deepsky observations
 $sql = "select distinct(instrumentid),observerid from observations order by instrumentid";
 $run = mysql_query($sql) or die(mysql_error());

 $id = 999;
 while($get = mysql_fetch_object($run))
 {
   $id = $id + 1;

   $sql2 = "SELECT * FROM instruments WHERE id = \"$get->instrumentid\"";
   $run2 = mysql_query($sql2) or die(mysql_error());
   $get2 = mysql_fetch_object($run2);

   $name = $get2->name;
   $diameter = $get2->diameter;
   $fd = $get2->fd;
   $type = $get2->type;

   $sql3 = "INSERT INTO instruments2 (id, name, diameter, fd, type, observer) VALUES (\"$id\", \"$name\", \"$diameter\", \"$fd\", \"$type\", \"$get->observerid\")";
   $run3 = mysql_query($sql3) or die(mysql_error());

   $sql3 = "update observations set instrumentid = \"$id\" where observerid = \"$get->observerid\" and instrumentid = \"$get->instrumentid\"";
   $run3 = mysql_query($sql3) or die(mysql_error());
 }

 // Select the instruments and observerid from the comet observations
 $sql = "select distinct(instrumentid),observerid from cometobservations order by instrumentid";
 $run = mysql_query($sql) or die(mysql_error());

 while($get = mysql_fetch_object($run))
 {
   if ($get->instrumentid != 0)
   {
    $sql2 = "SELECT * FROM instruments WHERE id = \"$get->instrumentid\"";
    $run2 = mysql_query($sql2) or die(mysql_error());
    $get2 = mysql_fetch_object($run2);

    $name = $get2->name;
    $diameter = $get2->diameter;
    $fd = $get2->fd;
    $type = $get2->type;

    $sql3 = "SELECT * from instruments2 where name = \"$name\" and observer = \"$get->observerid\"";
    $run3 = mysql_query($sql3) or die(mysql_error());

    if ($get3 = mysql_fetch_object($run3))
    {
     $sql4 = "update cometobservations set instrumentid = \"$get3->id\" where observerid = \"$get->observerid\" and instrumentid = \"$get->instrumentid\"";
     $run4 = mysql_query($sql4) or die(mysql_error());
    }
    else
    {
      $id = $id + 1;
      $sql4 = "INSERT INTO instruments2 (id, name, diameter, fd, type, observer) VALUES (\"$id\", \"$name\", \"$diameter\", \"$fd\", \"$type\", \"$get->observerid\")";
      $run4 = mysql_query($sql4) or die(mysql_error());

      $sql4 = "update cometobservations set instrumentid = \"$id\" where observerid = \"$get->observerid\" and instrumentid = \"$get->instrumentid\"";
      $run4 = mysql_query($sql4) or die(mysql_error());
    }
  }
 }

  $sql = "ALTER TABLE instruments2 ADD PRIMARY KEY (id)";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "ALTER TABLE instruments2 CHANGE id id int(11) auto_increment";
  $run = mysql_query($sql) or die(mysql_error());

  // Change the standard instrument for the observers
  $sql = "select stdtelescope, id from observers where stdtelescope > 0";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
    $sql2 = "select * from instruments where id = \"$get->stdtelescope\"";
    $run2 = mysql_query($sql2) or die(mysql_error());
    $get2 = mysql_fetch_object($run2);

    $sql3 = "select id from instruments2 where name = \"$get2->name\" and observer = \"$get->id\"";
    $run3 = mysql_query($sql3) or die(mysql_error());

    if ($get3 = mysql_fetch_object($run3))
    {
     $sql4 = "update observers set stdtelescope = \"$get3->id\" where stdtelescope = \"$get->stdtelescope\" and id = \"$get->id\"";

     $run4 = mysql_query($sql4) or die(mysql_error());
    }
    else
    {
      $id = $id + 1;

      $sql5 = "select * from instruments where id = \"$get->stdtelescope\"";
      $run5 = mysql_query($sql5) or die(mysql_error());
      $get5 = mysql_fetch_object($run5);

      $name = $get5->name;
      $diameter = $get5->diameter;
      $fd = $get5->fd;
      $type = $get5->type;

      $sql4 = "INSERT INTO instruments2 (id, name, diameter, fd, type, observer) VALUES (\"$id\", \"$name\", \"$diameter\", \"$fd\", \"$type\", \"$get->id\")";
      $run4 = mysql_query($sql4) or die(mysql_error());

      $sql4 = "update observers set stdtelescope = \"$id\" where stdtelescope = \"$get->stdtelescope\" and id = \"$get->id\"";

      $run4 = mysql_query($sql4) or die(mysql_error());
    }
  }

 $sql = "DROP TABLE instruments";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "RENAME TABLE instruments2 TO instruments";
 $run = mysql_query($sql) or die(mysql_error());
 
 $sql = "UPDATE instruments set fixedMagnification='1' where name = 'Naked eye'";
 $run = mysql_query($sql) or die(mysql_error());

 echo "Adapt database to use fixed magnifications -> Manually!!!";
 echo "Database was updated succesfully!\n";

 $db->logout();
?>
