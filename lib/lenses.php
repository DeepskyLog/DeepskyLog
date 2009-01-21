<?php  // The lenses class collects all functions needed to enter, retrieve and adapt lenses data from the database.
interface iLenses
{ public  function addLens($name, $factor);                                      // adds a new lens to the database. The name and the factor should be given as parameters. 
  public  function getAllFiltersIds($id);                                        // returns a list with all id's which have the same name as the name of the given id
  public  function getLensObserverPropertyFromName($name, $observer, $property); // returns the property for the eyepiece of the observer
  public  function getLensPropertyFromId($id,$property,$defaultValue='');        // returns the property of the given lens
	public  function getSortedLenses($sort, $observer = "");                       // returns an array with the ids of all lenses, sorted by the column specified in $sort
  public  function setLensProperty($id,$property,$propertyValue);                // sets the property to the specified value for the given lens
  public  function validateDeleteLens($id);                                      // validates and removes the lens with id
}
class Lenses implements iLenses
{ public  function addLens($name, $factor)                                      // adds a new lens to the database. The name and the factor should be given as parameters. 
  { global $objDatabase;
	  $objDatabase->execSQL("INSERT INTO lenses (name, factor) VALUES (\"".$name."\", \"".$factor."\")");
    return $objDatabase->selectSingleValue("SELECT id FROM lenses ORDER BY id DESC LIMIT 1",'id');
  }
  public  function getAllFiltersIds($id)                                        // returns a list with all id's which have the same name as the name of the given id
  { global $objDatabase;
    return $objDatabase->selectSinleArray("SELECT id FROM lenses WHERE name = \"".$objDatabase->selectSingleValue("SELECT name FROM lenses WHERE id = \"".$id."\"")."\"");
  }
  public  function getLensObserverPropertyFromName($name, $observer, $property)   // returns the property for the eyepiece of the observer
  { global $objDatabase; 
    return $objDatabase->selectSingleValue("SELECT ".$property." FROM lens where name=\"".$name."\" and observer=\"".$observer."\"",$property);
  }
  public  function getLensPropertyFromId($id,$property,$defaultValue='')          // returns the property of the given lens
  { global $objDatabase; 
    return $objDatabase->selectSingleValue("SELECT ".$property." FROM lenses WHERE id = \"".$id."\"",$property,$defaultValue);
  }
	public  function getSortedLenses($sort, $observer = "")                               // returns an array with the ids of all lenses, sorted by the column specified in $sort
  { global $objDatabase; 
    return $objDatabase->selectSingleArray("SELECT id, name FROM lenses ".($observer?"WHERE observer LIKE \"".$observer."\"":" GROUP BY name")." ORDER BY ".$sort.", name",'id');  
  } 
  public function validateDeleteLens($id)                                         // validates and removes the lens with id
  { global $objUtil, $objDatabase;
    if($objUtil->checkGetKey('lensid')
    && $objUtil->checkAdminOrUserID($this->getLensPropertyFromId($objUtil->checkGetKey('lensid'),'observer'))
    && (!($this->getFilterUsedFromId($_GET['filterid']))))
    { $objDatabase->execSQL("DELETE FROM lenses WHERE id=\"".$_GET['lensid']."\"");
      return LangValidateLensMessage5;
	  }
  }
  public  function setLensProperty($id,$property,$propertyValue)                       // sets the property to the specified value for the given lens
  { global $objDatabase;
    return $objDatabase->execSQL("UPDATE lenses SET ".$property." = \"".$propertyValue."\" WHERE id = \"".$id."\"");
  }

  public  function validateSaveLens()                                                  // validates and saves a lens and returns a message 
  { global $objUtil;
    if($objUtil->checkPostKey('add')
    && $objUtil->checkPostKey('lensname')
    && $objUtil->checkPostKey('factor')
    && $objUtil->checkSessionKey('deepskylog_id'))
    { $id = $this->addLens($_POST['lensname'], $_POST['factor']);     
    	$this->setLensProperty($id, 'observer', $_SESSION['deepskylog_id']);
      return LangValidateLensMessage2.' '.LangValidateLensMessage3;
    }
    if($objUtil->checkPostKey('change')
    && $objUtil->checkAdminOrUserID($this->getObserverFromLens($objUtil->checkPostKey('id')))
    && $objUtil->checkPostKey('lensname')
    && $objUtil->checkPostKey('factor'))
    { $this->setLensProperty($_POST['id'], 'name', $_POST['lensname']);
      $this->setLensProperty($_POST['id'], 'factor', $_POST['factor']);
      $this->setLensProperty($_POST['id'], 'observer', $_SESSION['deepskylog_id']);
      return LangValidateLensMessage5.' '.LangValidateLensMessage4;
    }
 }
	
	


 // setType sets a new factor for the given lens
 function setFactor($id, $factor)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE lenses SET factor = \"$factor\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setLensName sets the name for the given lens
 function setLensName($id, $name)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE lenses SET name = \"$name\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setLensObserver sets the observer for the lens with id = $id
 function setLensObserver($id, $observer)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE lenses SET observer = \"$observer\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }
 public function getObserverFromLens($id) // getObserver returns the observerid for this lens
 { return $GLOBALS['objDatabase']->selectSingleValue("SELECT * FROM lenses WHERE id = \"$id\"",'observer');
 }
 function showLenses() // showLenses prints a table showing all lenses. For testing  purposes only.
 
 {
  $filters = $this->getLenses();

  $count = 0;

  echo "<table width=\"100%\">
         <tr class=\"type3\">
          <td>id</td>
          <td>name</td>
          <td>factor</td>
          <td>observer</td>
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

   $name = $this->getLensName($value);
   $factor = $this->getFactor($value);
   $observer = $this->getObserverFromLens($value);

   echo "<tr $class><td> $value </td><td> $name </td><td> $factor </td><td> $observer </td>";

   echo "</tr>\n";

   $count++;
  }
  echo "</table>";
 }
}
$objLens=new Lenses;
?>
