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
 {global $objDatabase;
  $sql = "SELECT * FROM ICQ_METHOD WHERE id = \"$id\"";
  $run = $objDatabase->selectRecordset($sql);

  $get = $run->fetch(PDO::FETCH_OBJ);

  $description = $get->description;

  return $description;
 }

 // getIds returns an array with the ids of all ICQ METHODS
 function getIds()
 {
  global $objDatabase;
  $sql = "SELECT * FROM ICQ_METHOD";
  $run = $objDatabase->selectRecordset($sql);

  while($get = $run->fetch(PDO::FETCH_OBJ))
  {
   $ids[] = $get->id;
  }
  
  return $ids;
 }
}
?>
