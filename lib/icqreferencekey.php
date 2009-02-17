<?php

// The ICQREFERENCEKEY class collects all functions needed to retrieve data of 
// the ICQREFERENCEKEY data.
//
// Version 1.0 : 27/11/2005, WDM
//

include_once "database.php";

class ICQREFERENCEKEY
{
 // getDescription returns the description of an ICQMETHOD
 function getDescription($id)
 {

  $sql = "SELECT * FROM ICQ_REFERENCE_KEY WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $description = $get->description;


  return $description;
 }

 // getIds returns an array with the ids of all ICQ METHODS
 function getIds()
 {

  $sql = "SELECT * FROM ICQ_REFERENCE_KEY";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $ids[] = $get->id;
  }


  return $ids;
 }
}
?>
