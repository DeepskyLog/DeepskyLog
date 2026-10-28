<?php

// The ICQMETHOD class collects all functions needed to retrieve data of the 
// ICQMETHOD data.
//
// Version 1.0 : 27/11/2005, WDM
//


class ICQMETHOD
{
 // getDescription returns the description of an ICQMETHOD
 function getDescription($id)
 {global $objDatabase_new;
  $sql = "SELECT * FROM legacy_icq_method WHERE id = \"$id\"";
  $run = $objDatabase_new->selectRecordset($sql);

  $get = $run->fetch(PDO::FETCH_OBJ);

  $description = $get->description;

  return $description;
 }

 // getIds returns an array with the ids of all ICQ METHODS
 function getIds()
 {
  global $objDatabase_new;
  $sql = "SELECT * FROM legacy_icq_method";
  $run = $objDatabase_new->selectRecordset($sql);

  while($get = $run->fetch(PDO::FETCH_OBJ))
  {
   $ids[] = $get->id;
  }
  
  return $ids;
 }
}
?>
