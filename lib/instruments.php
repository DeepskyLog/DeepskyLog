<?php // The instruments class collects all functions needed to enter, retrieve and adapt instrument data from the database.
interface iInstruments
{ public  function addInstrument($name, $diameter, $fd, $type, $fixedMagnification, $observer);    // adds a new instrument to the database. The name, diameter, fd and type should be given as parameters. 
  public  function getInstrumentId($name, $observer);                                              // returns the id for this instrument
  public  function getInstrumentPropertyFromId($id,$property,$defaultValue='');
  public  function getObserverFromInstrument($id);                                                 // returns the observerid for this instrument
  public  function getSortedInstruments($sort,$observer="");                                       // returns an array with the ids of all instruments, sorted by the column specified in $sort
  public  function getSortedInstrumentsList($sort,$observer="");                                   // returns an array with the ids of all instruments as key, and the name as value, sorted by the column specified in $sort
  public  function validateDeleteInstrument();                                                     // validates and deletes the instrument with id = $id 
 
}
class Instruments implements iInstruments
{ public  function addInstrument($name, $diameter, $fd, $type, $fixedMagnification, $observer)    // adds a new instrument to the database. The name, diameter, fd and type should be given as parameters. 
  { global $objDatabase; $objDatabase->execSQL("INSERT INTO instruments (name, diameter, fd, type, fixedMagnification, observer) VALUES (\"$name\", \"$diameter\", \"$fd\", \"$type\", \"$fixedMagnification\", \"$observer\")");
  }
  public  function getInstrumentId($name, $observer)                                              // returns the id for this instrument
  { global $objDatabase; return $objDatabase->selectSingleValue("SELECT id FROM instruments where name=\"".htmlentities($name)."\" and observer=\"".$observer."\"",'id',-1);
  }
  public  function getInstrumentUsedFromId($id)                                                   // returns the number of times the instrument is used in observations
  { global $objDatabase; 
    return $objDatabase->selectSingleValue("SELECT count(id) as ObsCnt FROM observations WHERE instrumentid=\"".$id."\"",'ObsCnt',0)
         + $objDatabase->selectSingleValue("SELECT count(id) as ObsCnt FROM cometobservations WHERE instrumentid=\"".$id."\"",'ObsCnt',0);
	}
  public  function getObserverFromInstrument($id)                                                 // returns the observerid for this instrument
  { global $objDatabase; return $objDatabase->selectSingleValue("SELECT observer FROM instruments WHERE id = \"".$id."\"",'observer');
  }
  public  function getInstrumentPropertyFromId($id,$property,$defaultValue='')
  { global $objDatabase; return $objDatabase->selectSingleValue("SELECT ".$property." FROM instruments WHERE id = \"".$id."\"",$property,$defaultValue);
  }
  public  function getSortedInstruments($sort,$observer="")                                       // returns an array with the ids of all instruments, sorted by the column specified in $sort
  { global $objDatabase; 
    return $objDatabase->selectSingleArray("SELECT id, name FROM instruments ".($observer?"WHERE observer LIKE \"".$observer."\"":" GROUP BY name")." ORDER BY ".$sort.", name",'id');  
  } 
  public  function getSortedInstrumentsList($sort,$observer="")                                   // returns an array with the ids of all instruments, sorted by the column specified in $sort
  { global $objDatabase; 
    return $objDatabase->selectKeyValueArray("SELECT id, name FROM instruments ".($observer?"WHERE observer LIKE \"".$observer."\"":" GROUP BY name")." ORDER BY ".$sort.", name",'id','name');  
  } 
  public  function validateDeleteInstrument()                                                     // validates and deletes the instrument with id = $id 
  { global $objUtil, $objDatabase;
    if($objUtil->checkGetKey('instrumentid')                                                     
    && $objUtil->checkAdminOrUserID($this->getObserverFromInstrument($objUtil->checkGetKey('instrumentid')))
		&& (!($this->getInstrumentUsedFromId($_GET['instrumentid']))))
      return $objDatabase->execSQL("DELETE FROM instruments WHERE id=\"".$_GET['instrumentid']."\"");
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


 // setName sets the name for the given instrument
 function setInstrumentName($id, $name)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE instruments SET name = \"$name\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setType sets the type for the given instrument
 function setInstrumentType($id, $type)
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
 { global $objDatabase;
  $instruments = $objDatabase->selectSingleArray("SELECT id FROM instruements",'id');

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

   $name = $this->getInstrumentPropertyFromId($value,'name');
   $diameter = $this->getInstrumentPropertyFromId($value,'diameter');
   $fd = $this->getInstrumentPropertyFromId($value,'fd');
   $focalLength = $this->getInstrumentPropertyFromId($value,'diameter')*$this->getInstrumentPropertyFromId($value,'fd');
   $type = $this->getInstrumentPropertyFromId($value,'type');

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
 public function getInstrumentEchoType($instrumentType)
 { if($instrumentType== InstrumentBinoculars)        return InstrumentsBinoculars;
   if($instrumentType== InstrumentFinderscope)       return InstrumentsFinderscope;
   if($instrumentType== InstrumentReflector)         return InstrumentsReflector;
   if($instrumentType== InstrumentRefractor)         return InstrumentsRefractor;
   if($instrumentType== InstrumentRest)              return InstrumentsOther;
   if($instrumentType== InstrumentCassegrain)        return InstrumentsCassegrain;
   if($instrumentType== InstrumentSchmidtCassegrain) return InstrumentsSchmidtCassegrain;
   if($instrumentType== InstrumentKutter)            return InstrumentsKutter;
   if($instrumentType== InstrumentMaksutov)          return InstrumentsMaksutov;
   return "unkown instrument type";
 }
 public function getInstrumentEchoListType($type)
 { $tempTypeList ="<select name=\"type\" class=\"inputfield\">";
   $tempTypeList.="<option ".(($type==InstrumentReflector)?"selected=\"selected\" ":"")."value=\"".InstrumentReflector."\">".InstrumentsReflector."</option>";
   $tempTypeList.="<option ".(($type==InstrumentRefractor)?"selected=\"selected\" ":"")."value=\"".InstrumentRefractor."\">".InstrumentsRefractor."</option>";
   $tempTypeList.="<option ".(($type==InstrumentCassegrain)?"selected=\"selected\" ":"")."value=\"".InstrumentCassegrain."\">".InstrumentsCassegrain."</option>";
   $tempTypeList.="<option ".(($type==InstrumentSchmidtCassegrain)?"selected=\"selected\" ":"")."value=\"".InstrumentSchmidtCassegrain."\">".InstrumentsSchmidtCassegrain."</option>";
   $tempTypeList.="<option ".(($type==InstrumentKutter)?"selected=\"selected\" ":"")."value=\"".InstrumentKutter."\">".InstrumentsKutter."</option>";
   $tempTypeList.="<option ".(($type==InstrumentMaksutov)?"selected=\"selected\" ":"")."value=\"".InstrumentMaksutov."\">".InstrumentsMaksutov."</option>";
   $tempTypeList.="<option ".(($type==InstrumentBinoculars)?"selected=\"selected\" ":"")."value=\"".InstrumentBinoculars."\">".InstrumentsBinoculars."</option>";
   $tempTypeList.="<option ".(($type==InstrumentFinderscope)?"selected=\"selected\" ":"")."value=\"".InstrumentFinderscope."\">".InstrumentsFinderscope."</option>";
   $tempTypeList.="<option ".(($type==InstrumentOther)?"selected=\"selected\" ":"")."value=\"".InstrumentRest."\">".InstrumentsOther."</option>";
   $tempTypeList.="</select>";
   return    $tempTypeList;
 }
}
$objInstrument=new Instruments;
?>
