<?php

// The instruments class collects all functions needed to enter, retrieve and
// adapt instrument data from the database.
//
// Version 0.2 : 06/04/2005, WDM
// Version 3.1, DE 20061119

include_once "database.php";
include_once "setup/language.php";

class Instruments
{
 // addInstrument adds a new instrument to the database. The name, diameter, 
 // fd and type should be given as parameters. 
 function addInstrument($name, $diameter, $fd, $type, $fixedMagnification, $observer)
 {
  $db = new database;
  $db->login();

  if (!$_SESSION['lang'])
  {
   $_SESSION['lang'] = "English";
  }

  $sql = "INSERT INTO instruments (name, diameter, fd, type, fixedMagnification, observer) VALUES (\"$name\", \"$diameter\", \"$fd\", \"$type\", \"$fixedMagnification\", \"$observer\")";

  mysql_query($sql) or die(mysql_error());

  $db->logout();
 }
 
 // setObserver sets the observer for the instrument with id = $id
 function setObserver($id, $observer)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE instruments SET observer = \"$observer\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // getObserver returns the observerid for this instrument
 function getObserver($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM instruments WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $observer = $get->observer;

  $db->logout();

  return $observer;
 }

 // getId returns the id for this instrument
 function getInstrumentId($name, $observer)
 {
  $db = new database;
  $db->login();
  $name=htmlentities($name);
  $sql = "SELECT * FROM instruments where name=\"$name\" and observer=\"$observer\"";
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

 // deleteInstrument removes the instrument with id = $id 
 function deleteInstrument($id)
 {
  $db = new database;
  $db->login();

  $sql = "DELETE FROM instruments WHERE id=\"$id\"";
  mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // getInstrumentId returns the id of the given name of the instrument
 function getInstrumentsId($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM instruments where name=\"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  
	if ($get)
	  {
		$instrumentid = $get->id;
		}
	else
	  {
		$instrumentid = 0;
		}

  $db->logout();

  return $instrumentid;
 }

 // getInstruments returns an array with all instruments
 function getInstruments()
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM instruments";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $instruments[] = $get->id;
  }

  $db->logout();

  return $instruments;
 }

 // getInstrumentsName returns an array with all instruments and names
 function getInstrumentsName()
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM instruments";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $ins[$get->id] = $get->name;
  }

  $db->logout();

  return $ins;
 }

 // getDiameter returns the diameter of the given instrument
 function getDiameter($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM instruments WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get)
	  {
	  $diameter = $get->diameter;
    }
	else
	  {
		$diameter = 1.0;
		}
		
  $db->logout();

  return $diameter;
 }

 // getFixedMagnification returns the fixed magnification of the given instrument
 function getFixedMagnification($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM instruments WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get)
	{
	  $fixedMagnification = $get->fixedMagnification;
  }
	else
	{
		$fixedMagnification = 0.0;
	}
		
  $db->logout();

  return $fixedMagnification;
 }

 // getFd returns the Fd of the given instrument
 function getFd($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM instruments WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get)
	  {
	  $fd = $get->fd;
	  }
	else
	  {
		$fd = 1.0;
		}

  $db->logout();

  return $fd;
 }

 // getFocalLength returns the focal length of the given instrument
 function getInstrumentFocalLength($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM instruments WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);
  
	if ($get)
	  {
    $diameter = $get->diameter;
    $fd = $get->fd;
    }
	else
	  {
    $diameter = 1.0;
    $fd = 1.0;
		}
		
  $db->logout();

  $focalLength = $diameter * $fd;

  return $focalLength;
 }

 // getInstrumentName returns the name of the given instrument
 function getInstrumentName($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM instruments WHERE id = \"$id\"";
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

 // getType returns the type of the given instrument
 function getType($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM instruments WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get)
	  {
	  $type = $get->type;
    }
	else
	  {
	  $type = 0;
		}
		
  $db->logout();

  return $type;
 }

 // getSortedInstruments returns an array with the ids of all instruments, 
 // sorted by the column specified in $sort
 function getSortedInstruments($sort, $observer = "", $unique = false)
 {
  $insts = array();
  $db = new database;
  $db->login();

  if ($unique == false)
  {
   if ($observer == "")
   {
    $sql = "SELECT * FROM instruments ORDER BY $sort";
   } 
   else
   {
    $sql = "SELECT * FROM instruments where observer = \"$observer\" ORDER BY $sort";
   }
  }
  else
  {
   if ($observer == "")
   {
    $sql = "SELECT id, name FROM instruments GROUP BY name";
   } 
   else
   {
    $sql = "SELECT id, name FROM instruments where observer = \"$observer\" GROUP BY name ORDER BY $sort";
   }
  } 

  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $insts[] = $get->id;
  }
  $db->logout();

  return $insts;
 }

 // getSortedInstrumentsList returns an array with the ids of all instruments,
 // sorted by the column specified in $sort. Instruments with the same name
 // are adapted by adding the f/d.
 function getSortedInstrumentsList($sort, $observer = "", $unique = false, $InstrumentsNakedEye)
 {
   $instruments = $this->getSortedInstruments($sort, $observer, $unique);

  // If there are locations with the same name, the province should also
  // be shown
  $previous = "fdgdsgsd";

  for ($i = 0;$i < count($instruments);$i++)
  {
   $adapt[$i] = 0;

   if ($this->getInstrumentName($instruments[$i]) == $previous)
   {
    $adapt[$i] = 1;
    $adapt[$i - 1] = 1;
   }
   $previous = $this->getInstrumentName($instruments[$i]);
  }

  for ($i = 0;$i < count($instruments);$i++)
  {
   if ($adapt[$i])
   {
    $new_instruments[$i][0] = $instruments[$i];
    $new_instruments[$i][1] = $this->getInstrumentName($instruments[$i])." (F/".$this->getFd($instruments[$i]).")";
   }
   else
   {
    $new_instruments[$i][0] = $instruments[$i];
    if ($this->getType($instruments[$i]) == InstrumentNakedEye)
    {
      $new_instruments[$i][1] = InstrumentsNakedEye;
    }
    else
    {
      $new_instruments[$i][1] = $this->getInstrumentName($instruments[$i]);
    }
   }
  }
  return $new_instruments;
 }

 // setDiameter sets a new diameter for the given instrument
 function setDiameter($id, $diameter)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE instruments SET diameter = \"$diameter\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setFixedMagnification sets a new fixed magnification for the given instrument
 function setFixedMagnification($id, $fixedMagnification)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE instruments SET fixedMagnification = \"$fixedMagnification\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setFd sets a new fd for the given instrument
 function setFd($id, $fd)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE instruments SET fd = \"$fd\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setFocalLength sets the focal length for the given instrument
 function setFocalLength($id, $focalLength)
 {
  $diameter = $this->getDiameter($id);

  $db = new database;
  $db->login();

  $fd = $focalLength / $diameter;
  $sql = "UPDATE instruments SET fd = \"$fd\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setName sets the name for the given instrument
 function setName($id, $name)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE instruments SET name = \"$name\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setType sets the type for the given instrument
 function setType($id, $type)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE instruments SET type = \"$type\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // showInstruments prints a table showing all instruments. For testing 
 // purposes only.
 function showInstruments()
 {
  $instruments = $this->getInstruments();

  $count = 0;

  echo "<table width=\"100%\">
         <tr class=\"type3\">
          <td>id</td>
          <td>name</td>
          <td>diameter (mm)</td>
          <td>f/d</td>
          <td>focal length (mm)</td>
          <td>type</td>
         </tr>";

  while(list ($key, $value) = each($instruments))
  {
   if ($count % 2)
   {
    $class = "class=\"type1\"";
   }
   else
   {
    $class = "class=\"type2\"";
   }

   $name = $this->getInstrumentName($value);
   $diameter = $this->getDiameter($value);
   $fd = $this->getFd($value);
   $focalLength = $this->getInstrumentFocalLength($value);
   $type = $this->getType($value);

   if ($type == InstrumentNakedEye)
   {
    $types = "Naked eye";
   }
   else if ($type == InstrumentBinoculars)   
   {    
    $types = "Binoculars";
   }
   elseif ($type == InstrumentReflector)   
   {
    $types = "Reflector";
   }   
   elseif ($type == InstrumentRefractor)   
   {
    $types = "Refractor";
   }
   elseif ($type == InstrumentFinderscope)
   {
    $types = "Finderscope";
   }
   elseif ($type == InstrumentRest)
   {
    $types = "Rest";
   }

   echo "<tr $class><td> $value </td><td> $name </td><td> $diameter </td><td> $fd </td><td> $focalLength </td><td> $types </td>";

   echo "</tr>\n";

   $count++;
  }
  echo "</table>";
 }

 // getAllIds returns a list with all id's which have the same name as the name of the given id
 function getAllInstrumentsIds($id)
 {
  $ids[] = null;

  $sql = "SELECT name FROM instruments WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get)
  {
    $sql = "SELECT id FROM instruments WHERE name = \"$get->name\"";
    $run = mysql_query($sql) or die(mysql_error());

    while($get = mysql_fetch_object($run))
    {
     $ids[] = $get->id;
    }
  }

  return $ids;
 }
}
?>
