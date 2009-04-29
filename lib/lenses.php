<?php  // The lenses class collects all functions needed to enter, retrieve and adapt lenses data from the database.
interface iLenses
{ public  function addLens($name, $factor);                                      // adds a new lens to the database. The name and the factor should be given as parameters. 
  public  function getAllFiltersIds($id);                                        // returns a list with all id's which have the same name as the name of the given id
  public  function getLensId($name, $observer);                                  // returns the id for this lens
  public  function getLensObserverPropertyFromName($name, $observer, $property); // returns the property for the eyepiece of the observer
  public  function getLensPropertyFromId($id,$property,$defaultValue='');        // returns the property of the given lens
	public  function getSortedLenses($sort, $observer = "");                       // returns an array with the ids of all lenses, sorted by the column specified in $sort
  public  function setLensProperty($id,$property,$propertyValue);                // sets the property to the specified value for the given lens
  public  function getLensUsedFromId($id);                                       // returns the number of times the lens is used in observations
  public  function validateDeleteLens();                                         // validates and removes the lens with id
  public  function validateSaveLens();                                           // validates and saves a lens and returns a message 
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
  public  function getLensId($name, $observer)                                    // returns the id for this lens
  { global $objDatabase; return $objDatabase->selectSingleValue("SELECT id FROM lenses where name=\"".htmlentities($name,ENT_COMPAT,"ISO-8859-15",0)."\" and observer=\"".$observer."\"",'id',-1);
  }
  public  function getLensObserverPropertyFromName($name, $observer, $property) // returns the property for the eyepiece of the observer
  { global $objDatabase; 
    return $objDatabase->selectSingleValue("SELECT ".$property." FROM lenses where name=\"".$name."\" and observer=\"".$observer."\"",$property);
  }
  public  function getLensPropertyFromId($id,$property,$defaultValue='')        // returns the property of the given lens
  { global $objDatabase; 
    return $objDatabase->selectSingleValue("SELECT ".$property." FROM lenses WHERE id = \"".$id."\"",$property,$defaultValue);
  }
	public  function getSortedLenses($sort, $observer = "")                       // returns an array with the ids of all lenses, sorted by the column specified in $sort
  { global $objDatabase; 
    return $objDatabase->selectSingleArray("SELECT id, name FROM lenses ".($observer?"WHERE observer LIKE \"".$observer."\"":" GROUP BY name")." ORDER BY ".$sort.", name",'id');  
  } 
  public  function setLensProperty($id,$property,$propertyValue)                // sets the property to the specified value for the given lens
  { global $objDatabase;
    return $objDatabase->execSQL("UPDATE lenses SET ".$property." = \"".$propertyValue."\" WHERE id = \"".$id."\"");
  }
  public  function getLensUsedFromId($id)                                       // returns the number of times the lens is used in observations
  { global $objDatabase; 
    return $objDatabase->selectSingleValue("SELECT count(id) as ObsCnt FROM observations WHERE lensid=\"".$id."\"",'ObsCnt',0);
  }
  public  function validateDeleteLens()                                         // validates and removes the lens with id
  { global $objUtil, $objDatabase;
    if(($lensid=$objUtil->checkGetKey('lensid'))
    && $objUtil->checkAdminOrUserID($this->getLensPropertyFromId($lensid,'observer'))
    && (!($this->getLensUsedFromId($lensid))))
    { $objDatabase->execSQL("DELETE FROM lenses WHERE id=\"".$lensid."\"");
      return LangValidateLensMessage1;
	  }
  }
  public  function validateSaveLens()                                           // validates and saves a lens and returns a message 
  { global $objUtil;
    if($objUtil->checkPostKey('add')
    && $objUtil->checkPostKey('lensname')
    && $objUtil->checkPostKey('factor')
    && $objUtil->checkSessionKey('deepskylog_id'))
    { $id = $this->addLens($_POST['lensname'], $_POST['factor']);     
    	$this->setLensProperty($id, 'observer', $_SESSION['deepskylog_id']);
      return LangValidateLensMessage2;
    }
    if($objUtil->checkPostKey('change')
    && $objUtil->checkAdminOrUserID($this->getLensPropertyFromId($objUtil->checkPostKey('id'),'observer'))
    && $objUtil->checkPostKey('lensname')
    && $objUtil->checkPostKey('factor'))
    { $this->setLensProperty($_POST['id'], 'name', $_POST['lensname']);
      $this->setLensProperty($_POST['id'], 'factor', $_POST['factor']);
      $this->setLensProperty($_POST['id'], 'observer', $_SESSION['deepskylog_id']);
      return LangValidateLensMessage5;
    }
 }
}
$objLens=new Lenses;
?>
