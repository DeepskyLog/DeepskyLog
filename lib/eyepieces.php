<?php

// The eyepieces class collects all functions needed to enter, retrieve and
// adapt eyepiece data from the database.
//
// Version 3.2, WDM 16/01/2007

include_once "database.php";
include_once "setup/language.php";

class Eyepieces
{
 // addEyepiece adds a new eyepiece to the database. The name, focalLength and
 // apparentFOV should be given as parameters. 
 function addEyepiece($name, $focalLength, $apparentFOV)
 {
  $db = new database;
  $db->login();

  if (!$_SESSION['lang'])
  {
   $_SESSION['lang'] = "English";
  }

  $sql = "INSERT INTO eyepieces (name, focalLength, apparentFOV) VALUES (\"$name\", \"$focalLength\", \"$apparentFOV\")";

  mysql_query($sql) or die(mysql_error());

  $query = "SELECT id FROM eyepieces ORDER BY id DESC LIMIT 1";
  $run = mysql_query($query) or die(mysql_error());

  $db->logout();
  $get = mysql_fetch_object($run);
  if($get) 
  {
   return $get->id; 
  }
  else
  {
   return '';
  }
 }
 
 // getId returns the id for this eyepiece
 function getEyepieceId($name, $observer)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM eyepieces where name=\"$name\" and observer=\"$observer\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get)
  {
    $id = $get->id;
  }
  else
  {
    $id = -1;
  }

  $db->logout();

  return $id;
 }

 // deleteEyepiece removes the eyepiece with id = $id 
 function deleteEyepiece($id)
 {
  $db = new database;
  $db->login();

  $sql = "DELETE FROM eyepieces WHERE id=\"$id\"";
  mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // getAllIds returns a list with all id's which have the same name as the name of the given id
 function getAllEyepiecesIds($id)
 {
  $sql = "SELECT name FROM eyepieces WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $sql = "SELECT id FROM eyepieces WHERE name = \"$get->name\"";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $ids[] = $get->id;
  }

  return $ids;
 }

 // getEyepiecesId returns the id of the given name of the eyepiece
 function getEyepiecesId($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM eyepieces where name=\"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);


	if ($get)
	{
		$eyepieceid = $get->id;
	}
	else
	{
		$eyepieceid = 0;
	}

  $db->logout();

  return $eyepieceid;
 }

 // getEyepieces returns an array with all eyepieces
 function getEyepieces()
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM eyepieces";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $eyepieces[] = $get->id;
  }

  $db->logout();

  return $eyepieces;
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

 // getFocalLength returns the focal length of the given eyepiece
 function getFocalLength($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM eyepieces WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get)
	{
	  $focalLength = $get->focalLength;
    }
	else
  {
		$focalLength = -1.0;
	}
		
  $db->logout();

  return $focalLength;
 }

 // getMaxFocalLength returns the maximum focal length of the given eyepiece (for zoom eyepieces)
 function getMaxFocalLength($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM eyepieces WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get)
	{
	  $maxFocalLength = $get->maxFocalLength;
  }
	else
  {
		$maxFocalLength = -1.0;
	}
		
  $db->logout();

  return $maxFocalLength;
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
		$fov = -1.0;
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
 function setFocalLength($id, $focalLength)
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
 function setName($id, $name)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE eyepieces SET name = \"$name\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setObserver sets the observer for the eyepiece with id = $id
 function setObserver($id, $observer)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE eyepieces SET observer = \"$observer\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // getObserver returns the observerid for this location
 function getObserver($id)
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
 {
  $eyepieces = $this->getEyepieces();

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
?>
