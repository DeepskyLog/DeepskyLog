<?php

// The cometobjects class collects all functions needed to enter, retrieve and
// adapt comets data from the database and functions to display the data.
//
// Version 1.0 : 05/02/2006, WDM
//

include_once "database.php";
include_once "cometobservations.php";

class CometObjects
{
 // addObject adds a new object to the database. The name and icqname should be given as 
 // parameters.
 function addObject($name)
 {
  global $objDatabase;
  if (!$_SESSION['lang'])
  {
   $_SESSION['lang'] = "English";
  }

  $array = array("INSERT INTO cometobjects (name) VALUES (\"$name\")");

  $sql = implode("", $array);

  $objDatabase->execSQL($sql);

  $query = "SELECT id FROM cometobjects ORDER BY id DESC LIMIT 1";
  $run = $objDatabase->selectRecordset($query);
  $get = $run->fetch(PDO::FETCH_OBJ);
  $id = $get->id;
  return $id;
 }

 // deleteObject removes the object with id = $id 
 function deleteObject($id)
 {
  global $objDatabase;
  $sql = "DELETE FROM cometobjects WHERE id=\"$id\"";
  $objDatabase->execSQL($sql);
}

 // getAllInfo returns all information of an object
 function getAllInfo($id)
 {
  global $objDatabase;

  $sql = "SELECT * FROM cometobjects WHERE id = \"$id\"";
  $run = $objDatabase->selectRecordset($sql);

  $get = $run->fetch(PDO::FETCH_OBJ);

  $object["name"] = $get->name;
  $object["icqname"] = $get->icqname;

  return $object;
 }

 // getId returns the id of an object
 function getId($name)
 { 
   global $objDatabase;
    	
   $id="";
   $sql = "SELECT * FROM cometobjects WHERE name = \"$name\"";
      
   $run = $objDatabase->selectRecordset($sql);
   if($get = $run->fetch(PDO::FETCH_OBJ))
     $id = $get->id;
   return $id;
 }

 
 // getName returns the name of an object
 function getName($id)
 {
  global $objDatabase;

  $sql = "SELECT * FROM cometobjects WHERE id = \"$id\"";
  $run = $objDatabase->selectRecordset($sql);

  $get = $run->fetch(PDO::FETCH_OBJ);

  if ($get)
  {
    $name = $get->name;
  }
  else
  {
    $name = '';
  }

  return $name;
 }

 // getIcqName returns the name of an object
 function getIcqName($id)
 {
  global $objDatabase;

  $sql = "SELECT * FROM cometobjects WHERE id = \"$id\"";
  $run = $objDatabase->selectRecordset($sql);

  $get = $run->fetch(PDO::FETCH_OBJ);

  $icqname = $get->icqname;

  return $icqname;
 }

 // getObjects returns an array with the names of all objects
 function getObjects()
 {
  global $objDatabase;

  $sql = "SELECT * FROM cometobjects";
  $run = $objDatabase->selectRecordset($sql);

  while($get = $run->fetch(PDO::FETCH_OBJ))
  {
   $obs[] = $get->name;
  }

  return $obs;
 }

 // getObjectFromQuery returns an array with the names of all objects where
 // the queries are defined in an array.
 // An example of an array :  
 //  $q = array("name" => "NGC", "icqname" => "C200512");
 function getObjectFromQuery($queries, $sort, $exact = 0)
 {
  global $objDatabase;
 
  $sql = "SELECT * FROM cometobjects where";

  if ($queries["name"] != "")
  {
   $name = $queries["name"];
   if ($exact == 0)
   {
    $sql = $sql." (name like \"%$name%\")"; 
   }
   else
   {
    $sql = $sql." (name = \"$name\")"; 
   }
   $sqland = 1;
  }

  if (array_key_exists('icqname',$queries) && $queries["icqname"] != "")
  {
   $icqname = $queries["icqname"];
   if ($sqland == 1)
   {
    $sql = $sql." and";
   }
   $sql = $sql." icqname like \"%$icqname%\"";
  }

  $sql = $sql.";";

  $run = $objDatabase->selectRecordset($sql);
  $obs=array();
  while($get = $run->fetch(PDO::FETCH_OBJ))
  {
   $obs[] = $get->name;
  }

  if ($sort == "name")
  {
   if ($obs != "")
   {
    natcasesort($obs);
   }
  }

  return $obs;
 }

 // getSelectedObjects returns an array with the names of all objects where the 
 // databasefield has the given value.
 function getSelectedObjects($dbfield, $value)
 {
  global $objDatabase;

  if ($dbfield == "name")
  {
   $sql = "SELECT * FROM cometobjects where $dbfield like \"$value%\"";
  }

  $run = $objDatabase->selectRecordset($sql);

  while($get = $run->fetch(PDO::FETCH_OBJ))
  {
   $obs[] = $get->name;
  }

  return $obs;
 }

 // getExactObject returns an array with the name of the object where the
 // databasefield has the given name.
 function getExactObject($value)
 {
  global $objDatabase;  

  $sql = "SELECT * FROM cometobjects where name = \"$value\"";

  $run = $objDatabase->selectRecordset($sql);

  if(isset($get))
  {
    while($get = $run->fetch(PDO::FETCH_OBJ))
    {
     $obs[] = $get->name;
    }
  }
  else
  {
    $obs[] = null;
  }

  return $obs;
 }

 // getSortedObjects returns an array with the names of all objects, sorted by 
 // the column specified in $sort
 function getSortedObjects($sort)
 {
  global $objDatabase;

  if ($sort == "seen")
  {
   $sql = "SELECT * FROM cometobjects";
  }
  else
  {
   $sql = "SELECT * FROM cometobjects ORDER BY $sort";
  }
  $run = $objDatabase->selectRecordset($sql);

  while($get = $run->fetch(PDO::FETCH_OBJ))
  {
   $obs[] = $get->name;
  }

  if ($sort == "name")
  {
   natcasesort($obs);
  }

  if(sizeof($obs) > 0)
  {
   while(list($key, $value) = each($obs))
   {
    $result2[$key][0] = $value;

    $id = $this->getId($value);

    if ($sort == "seen")
    {
     $result2[$key][1] = "-";
     $sql = "SELECT observerid FROM cometobservations WHERE objectid = \"$id\"";
     $run = $objDatabase->selectRecordset($sql);

     $get = $run->fetch(PDO::FETCH_OBJ);

     if ($get->observerid != "")
     {
      $result2[$key][1] = "X";
     }
     if ($loggedUser)
     { $sql = "SELECT observerid FROM cometobservations WHERE objectid = \"$id\" AND observerid = \"".$loggedUser."\"";
       $run = $objDatabase->selectRecordset($sql);

      $get = $run->fetch(PDO::FETCH_OBJ);

      if ($get->observerid != "")
      {
       $result2[$key][1] = "Y";
      }
     }
    }
   }

   if ($sort == "seen")
   {
    $sorted = $result2;

    for ($i=0; $i < sizeof($sorted)-1; $i++)
    {
     for ($j=0; $j<sizeof($sorted)-1-$i; $j++)
     {
      if ($sorted[$j][1] > $sorted[$j+1][1])
      {
       $tmp = $sorted[$j];
       $sorted[$j] = $sorted[$j+1];
       $sorted[$j+1] = $tmp;
      }
     }
    }
    $result2 = $sorted;
   }

   $obs = $result2;
  }

  return $obs;
 }

 // my_array_unique returns a unique array, where the keys increment.
 function my_array_unique($somearray)
 { 
  $tmparr = array_unique($somearray); 
  $i=0; 
  foreach ($tmparr as $v) 
  { 
   $newarr[$i] = $v; 
   $i++; 
  } 
  return $newarr; 
 } 
 
 // getObservedByUser returns +1 if the object is already observed by the 
 // given user, -1 if the object is not yet observed
 function getObservedbyUser($name, $observerid)
 {
  $observations = new CometObservations;
  $query = array("object" => "$name", "observer" => "$observerid", 
		 "instrument" => "", "location" => "", "mindate" => "", 
		 "maxdate" => "");

  $obs = $observations->getObservationFromQuery($query);

  $return = -1;

  if ($obs != "")
  {
   $return = +1;
  }

  return $return;
 }

 // getObserved returns +1 if the object is already observed, -1 if the object 
 // is not yet observed
 function getObserved($name)
 {
  $observations = new CometObservations;
  $query = array("object" => "$name", "observer" => "",
                 "instrument" => "", "location" => "", "mindate" => "",
                 "maxdate" => "");

  $obs = $observations->getObservationFromQuery($query);

  $return = -1;

  if ($obs != "")
  {
   $return = +1;
  }

  return $return;
 }

 // setName sets a new name for the object.
 function setName($id, $name)
 {
  global $objDatabase;

  $sql = "UPDATE cometobjects SET name = \"$name\" WHERE id = \"$id\"";
  $objDatabase->execSQL($sql);
 }

 // setIcqName sets a new ICQ name for the object.
 function setIcqName($id, $icq)
 {
  global $objDatabase;

  $sql = "UPDATE cometobjects SET icqname = \"$icq\" WHERE id = \"$id\"";
  $objDatabase->execSQL($sql);
 }
}
?>
