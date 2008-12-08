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
  $db = new database;
  $db->login();

  if (!$_SESSION['lang'])
  {
   $_SESSION['lang'] = "English";
  }

  $array = array("INSERT INTO cometobjects (name) VALUES (\"$name\")");

  $sql = implode("", $array);

  mysql_query($sql) or die(mysql_error());

  $query = "SELECT id FROM cometobjects ORDER BY id DESC LIMIT 1";
  $run = mysql_query($query) or die(mysql_error());
  $get = mysql_fetch_object($run);
  $id = $get->id;

  $db->logout();

  return $id;
 }

 // deleteObject removes the object with id = $id 
 function deleteObject($id)
 {
  $db = new database;
  $db->login();

  $sql = "DELETE FROM cometobjects WHERE id=\"$id\"";
  mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // getAllInfo returns all information of an object
 function getAllInfo($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM cometobjects WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $object["name"] = $get->name;
  $object["icqname"] = $get->icqname;

  $db->logout();

  return $object;
 }

 // getId returns the id of an object
 function getId($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM cometobjects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $id = $get->id;

  $db->logout();

  return $id;
 }

 // getName returns the name of an object
 function getName($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM cometobjects WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get)
  {
    $name = $get->name;
  }
  else
  {
    $name = '';
  }

  $db->logout();

  return $name;
 }

 // getIcqName returns the name of an object
 function getIcqName($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM cometobjects WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $icqname = $get->icqname;

  $db->logout();

  return $icqname;
 }

 // getObjects returns an array with the names of all objects
 function getObjects()
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM cometobjects";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $obs[] = $get->name;
  }

  $db->logout();

  return $obs;
 }

 // getObjectFromQuery returns an array with the names of all objects where
 // the queries are defined in an array.
 // An example of an array :  
 //  $q = array("name" => "NGC", "icqname" => "C200512");
 function getObjectFromQuery($queries, $sort, $exact = 0)
 {
  $db = new database;
  $db->login();
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

  if ($queries["icqname"] != "")
  {
   $icqname = $queries["icqname"];
   if ($sqland == 1)
   {
    $sql = $sql." and";
   }
   $sql = $sql." icqname like \"%$icqname%\"";
  }

  $sql = $sql.";";

  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
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

  $db->logout();

  return $obs;
 }

 // getSelectedObjects returns an array with the names of all objects where the 
 // databasefield has the given value.
 function getSelectedObjects($dbfield, $value)
 {
  $db = new database;
  $db->login();

  if ($dbfield == "name")
  {
   $sql = "SELECT * FROM cometobjects where $dbfield like \"$value%\"";
  }

  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $obs[] = $get->name;
  }

  $db->logout();

  return $obs;
 }

 // getExactObject returns an array with the name of the object where the
 // databasefield has the given name.
 function getExactObject($value)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM cometobjects where name = \"$value\"";

  $run = mysql_query($sql) or die(mysql_error());

  if(isset($get))
  {
    while($get = mysql_fetch_object($run))
    {
     $obs[] = $get->name;
    }
  }
  else
  {
    $obs[] = null;
  }

  $db->logout();

  return $obs;
 }

 // getSortedObjects returns an array with the names of all objects, sorted by 
 // the column specified in $sort
 function getSortedObjects($sort)
 {
  $db = new database;
  $db->login();

  if ($sort == "seen")
  {
   $sql = "SELECT * FROM cometobjects";
  }
  else
  {
   $sql = "SELECT * FROM cometobjects ORDER BY $sort";
  }
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
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
     $db->login();

     $result2[$key][1] = "-";
     $sql = "SELECT observerid FROM cometobservations WHERE objectid = \"$id\"";
     $run = mysql_query($sql) or die(mysql_error());

     $get = mysql_fetch_object($run);

     if ($get->observerid != "")
     {
      $result2[$key][1] = "X";
     }
     if ($_SESSION['deepskylog_id'] != "")
     {
      $user = $_SESSION['deepskylog_id'];
      $sql = "SELECT observerid FROM cometobservations WHERE objectid = \"$id\" AND observerid = \"$user\"";
      $run = mysql_query($sql) or die(mysql_error());

      $get = mysql_fetch_object($run);

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
  $db = new database;
  $db->login();

  $sql = "UPDATE cometobjects SET name = \"$name\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setIcqName sets a new ICQ name for the object.
 function setIcqName($id, $icq)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE cometobjects SET icqname = \"$icq\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }
}
$objCometObject=new CometObjects;
?>
