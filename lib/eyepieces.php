<?php
// The eyepieces class collects all functions needed to enter, retrieve and adapt eyepiece data from the database.
// Version 3.2, WDM 16/01/2007

interface iEyepiece
{ public  function getAllEyepiecesIds($id);                                          // returns a list with all id's which have the same name as the name of the given id
  public  function addEyepiece($name, $focalLength, $apparentFOV);                   // adds a new eyepiece to the database. The name, focalLength and apparentFOV should be given as parameters. 
  public  function deleteEyepiece($id);                                              // removes the eyepiece with id = $id 
  public  function getEyepieceObserverPropertyFromName($name, $observer, $property); // returns the property for the eyepiece of the observer
  public  function getFocalLength($id);                                              // returns the focal length of the given eyepiece
  public  function getMaxFocalLength($id);                                           // returns the maximum focal length of the given eyepiece (for zoom eyepieces)
} 

class Eyepieces implements iEyepiece
{public  function getAllEyepiecesIds($id)                                            // getAllIds returns a list with all id's which have the same name as the name of the given id
 { global $objDatabase;
   return $objDatabase->selectSingleArray("SELECT id FROM eyepieces WHERE name=".$objDatabase->selectSingleValue("SELECT name FROM eyepieces WHERE id = \"".$id."\"",'name'),'id');
 }
 public function addEyepiece($name, $focalLength, $apparentFOV)                     // addEyepiece adds a new eyepiece to the database. The name, focalLength and apparentFOV should be given as parameters. 
 { global $objDatabase;
   if (!$_SESSION['lang'])
     $_SESSION['lang'] = "English";
   $objectDatabase->execSQL("INSERT INTO eyepieces (name, focalLength, apparentFOV) VALUES (\"".$name."\", \"".$focalLength."\", \"".$apparentFOV."\")");
   return $objDatabase->selectSingleValue("SELECT id FROM eyepieces ORDER BY id DESC LIMIT 1",'id','');
 }
 public  function deleteEyepiece($id)                                                // deleteEyepiece removes the eyepiece with id = $id 
 { global $objDatabase;
   return $objDatabase->execSQL("DELETE FROM eyepieces WHERE id=\"".$id."\"");
 }
 public  function getEyepieceObserverPropertyFromName($name, $observer, $property)   // returns the property for the eyepiece of the observer
 { global $objDatabase; 
   return $objDatabase->returnSingleValue("SELECT ".$property." FROM eyepieces where name=\"".$name."\" and observer=\"".$observer."\"",$property);
 }
 public  function getFocalLength($id)                                               // getFocalLength returns the focal length of the given eyepiece
 { global $objDatabase; 
   return $objDatabase->selectSingleValue("SELECT focalLength FROM eyepieces WHERE id = \"".$id."\"","focalLength");
 }
 public  function getMaxFocalLength($id)                                           // getMaxFocalLength returns the maximum focal length of the given eyepiece (for zoom eyepieces)
 { global $objDatabase;
   return $objDatabase->selectSingleValue("SELECT maxFocalLength FROM eyepieces WHERE id = \"".$id."\"","maxFocalLength",-1.0);
 }
 public  function getEyepieceProperty($id,$property,$defaultValue)                // returns the property of the given eyepiece
 { global $objDatabase; 
   return $objDatabase->selectSingleValue("SELECT ".$property." FROM eyepieces WHERE id = \"".$id."\"",$property,$defaultValue);
 }
 public  function getEyepieceProperties($id)
 { global $objDatabase;
   return $objDatabase->selectRecordArray("SELECT * FROM eyepieces WHERE id=\"".$id."\"");
 }
 
 
 
 
 
 
 
 
 
 
 





 // getApparentFOV returns the apparent Field of View of the given eyepiece
 function getApparentFOV($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM eyepieces WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get)
	{
	  $fov = $get->apparentFOV;
	}
	else
	{
		$fov = '';
	}

  $db->logout();

  return $fov;
 }

 // getEyepieceName returns the name of the given eyepiece
 function getEyepieceName($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM eyepieces WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);
  if ($get)
	{
    $name = $get->name;
	}
	else
	{
		$name = "";
	}

  $db->logout();

  return $name;
 }

 // getEyepieceFocalLength returns the focal length of the given eyepiece
 function getEyepieceFocalLength($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM eyepieces WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);
  if ($get)
	{
      $focal = $get->focalLength;
	}
	else
	{
		$focal = "";
	}

  $db->logout();

  return $focal;
 }

 // getSortedEyepieces returns an array with the ids of all eyepieces, 
 // sorted by the column specified in $sort
 function getSortedEyepieces($sort, $observer = "", $unique = false)
 {
  $eps = null;
  $db = new database;
  $db->login();

  if ($unique == false)
  {
   if ($observer == "")
   {
    $sql = "SELECT * FROM eyepieces ORDER BY $sort";
   } 
   else
   {
    $sql = "SELECT * FROM eyepieces where observer = \"$observer\" ORDER BY $sort";
   }
  }
  else
  {
   if ($observer == "")
   {
    $sql = "SELECT id, name FROM eyepieces GROUP BY name ORDER BY $sort";
   } 
   else
   {
    $sql = "SELECT id, name FROM eyepieces where observer = \"$observer\" GROUP BY name ORDER BY $sort";
   }
  } 

  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $eps[] = $get->id;
  }
  $db->logout();

  return $eps;
 }

 // getSortedEyepiecesList returns an array with the ids of all eyepieces,
 // sorted by the column specified in $sort.
 function getSortedEyepiecesList($sort, $observer = "")
 {
   $eyepieces = $this->getSortedEyepieces($sort, $observer);

	 return $eyepieces;
 }


 // setFocalLength sets a new focal length for the given eyepiece
 function setEyepieceFocalLength($id, $focalLength)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE eyepieces SET focalLength = \"$focalLength\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setMaxFocalLength sets a new maximum focal length for the given eyepiece (for zoom eyepieces)
 function setMaxFocalLength($id, $maxFocalLength)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE eyepieces SET maxFocalLength = \"$maxFocalLength\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setApparentFOV sets a new apparent field of view for the given instrument
 function setApparentFOV($id, $apparentFOV)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE eyepieces SET apparentFOV = \"$apparentFOV\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setName sets the name for the given eyepiece
 function setEyepieceName($id, $name)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE eyepieces SET name = \"$name\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setObserver sets the observer for the eyepiece with id = $id
 function setEyepieceObserver($id, $observer)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE eyepieces SET observer = \"$observer\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // getObserver returns the observerid for this location
 function getObserverFromEyepiece($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM eyepieces WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $observer = $get->observer;

  $db->logout();

  return $observer;
 }


 // showEyepieces prints a table showing all eyepieces. For testing 
 // purposes only.
 function showEyepieces()
 {global $objDatabase;
  $eyepieces =$objDatabase->selectSingleArray("SELECT id FROM eyepieces",'id');;

  $count = 0;

  echo "<table width=\"100%\">
         <tr class=\"type3\">
          <td>id</td>
          <td>name</td>
          <td>focal length (mm)</td>
          <td>apparent FOV</td>
         </tr>";

  while(list ($key, $value) = each($eyepieces))
  {
   if ($count % 2)
   {
    $class = "class=\"type1\"";
   }
   else
   {
    $class = "class=\"type2\"";
   }

   $name = $this->getEyepieceName($value);
   $focalLength = $this->getFocalLength($value);
   $apparentFOV = $this->getApparentFOV($value);

   echo "<tr $class><td> $value </td><td> $name </td><td> $focalLength </td><td> $apparentFOV </td>";

   echo "</tr>\n";

   $count++;
  }
  echo "</table>";
 }
}
$objEyepiece=new Eyepieces;

/* OBSOLETE ??? 
 function getEyepiecesId($name)                                                      // getEyepiecesId returns the id of the given name of the eyepiece
 { $sql = "SELECT * FROM eyepieces where name=\"$name\"";
   $run = mysql_query($sql) or die(mysql_error());
   $get = mysql_fetch_object($run);
	 if($get)
	  $eyepieceid = $get->id;
	else
	  $eyepieceid = 0;
  return $eyepieceid;
 }
 
  // getEyepiecesName returns an array with all eyepieces and names
 function getEyepiecesName()
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM eyepieces";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $eps[$get->id] = $get->name;
  }

  $db->logout();

  return $eps;
 }


*/
?>
