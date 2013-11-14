<?php 
// objectOutlines.php
// The objects class collects all functions needed to enter, retrieve and adapt object Outlines data from the database.

global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";

class ObjectOutlines
{ 
  public  function getAllObjects()                  // returns a list of all objects which have outlines
  { global $objDatabase;
	$objectNames=$objDatabase->selectSingleArray("select DISTINCT(objectname) from objectOutlines", "objectname");
    return $objectNames;
  }
  public  function getOutlines($name)                // returns a list of all coordinates for the outlines of an object
  { global $objDatabase;
	$objectCoordinates=$objDatabase->selectRecordSetArray("select * from objectOutlines where objectname = \"".$name."\"");
    return $objectCoordinates;
  }
}
?>
