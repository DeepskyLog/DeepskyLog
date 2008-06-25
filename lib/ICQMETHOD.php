<?php

// The ICQMETHOD class collects all functions needed to retrieve data of the 
// ICQMETHOD data.
//
// Version 1.0 : 27/11/2005, WDM
//

include_once "database.php";

class ICQMETHOD
{
 // getDescription returns the description of an ICQMETHOD
 function getDescription($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM ICQ_METHOD WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $description = $get->description;

  $db->logout();

  return $description;
 }

 // getIds returns an array with the ids of all ICQ METHODS
 function getIds()
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM ICQ_METHOD";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $ids[] = $get->id;
  }

  $db->logout();

  return $ids;
 }
}
?>
