<?php 
// objectOutlines.php
// The objects class collects all functions needed to enter, retrieve and adapt object Outlines data from the database.

global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";

class ObjectOutlines
{ 
  public  function getAllObjects()                  // returns a list of all objects which have outlines
  { global $objDatabase;
	$objectNames=$objDatabase->selectRecordArray("select DISTINCT(objectname) from objectOutlines");
    return $objectNames;
  }
}
?>
