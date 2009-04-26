<?php // The instruments class collects all functions needed to enter, retrieve and adapt instrument data from the database.
interface iInstruments
{ public  function addInstrument($name, $diameter, $fd, $type, $fixedMagnification, $observer);    // adds a new instrument to the database. The name, diameter, fd and type should be given as parameters. 
  public  function getAllInstrumentsIds($id);                                                      // returns a list with all id's which have the same name as the name of the given id
  public  function getInstrumentEchoType($instrumentType);                                         // returns the text corresponding to a certain instrument type
  public  function getInstrumentEchoListType($type);                                               // returns the html code for a list containing the Instrument types, with the $type selected
  public  function getInstrumentId($name, $observer);                                              // returns the id for this instrument
  public  function getInstrumentPropertyFromId($id,$property,$defaultValue='');                    // returns the specified property for instrument id                    
  public  function getInstrumentUsedFromId($id);                                                   // returns the number of times the instrument is used in observations
  public  function getObserverFromInstrument($id);                                                 // returns the observerid for this instrument
  public  function getSortedInstruments($sort,$observer="");                                       // returns an array with the ids of all instruments, sorted by the column specified in $sort
  public  function getSortedInstrumentsList($sort,$observer="");                                   // returns an array with the ids of all instruments as key, and the name as value, sorted by the column specified in $sort
  public  function setInstrumentProperty($id,$property,$propertyValue);                            // sets the property to the specified value for the given instrument
  public  function validateDeleteInstrument();                                                     // validates and deletes the instrument with id
  public  function validateSaveInstrument();                                                       // validates and saves the instrument
}
class Instruments implements iInstruments
{ public  function addInstrument($name, $diameter, $fd, $type, $fixedMagnification, $observer)    // adds a new instrument to the database. The name, diameter, fd and type should be given as parameters. 
  { global $objDatabase; $objDatabase->execSQL("INSERT INTO instruments (name, diameter, fd, type, fixedMagnification, observer) VALUES (\"$name\", \"$diameter\", \"$fd\", \"$type\", \"$fixedMagnification\", \"$observer\")");
  }
  public  function getAllInstrumentsIds($id)                                                      // returns a list with all id's which have the same name as the name of the given id
  { global $objDatabase;
   return $objDatabase->selectSingleAray("SELECT id FROM instruments WHERE name = \"".$objDatabase->selectSingleValue("SELECT name FROM instruments WHERE id = \"$id\"",'name'),'id');
  }
  public  function getInstrumentEchoType($instrumentType)
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
  public  function getInstrumentEchoListType($type)
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
  public  function getInstrumentId($name, $observer)                                              // returns the id for this instrument
  { global $objDatabase; return $objDatabase->selectSingleValue("SELECT id FROM instruments where name=\"".htmlentities($name,ENT_COMPAT,"ISO-8859-15",0)."\" and observer=\"".$observer."\"",'id',-1);
  }
  public  function getInstrumentPropertyFromId($id,$property,$defaultValue='')
  { global $objDatabase; return $objDatabase->selectSingleValue("SELECT ".$property." FROM instruments WHERE id = \"".$id."\"",$property,$defaultValue);
  }
  public  function getInstrumentUsedFromId($id)                                                   // returns the number of times the instrument is used in observations
  { global $objDatabase; 
    return $objDatabase->selectSingleValue("SELECT count(id) as ObsCnt FROM observations WHERE instrumentid=\"".$id."\"",'ObsCnt',0)
         + $objDatabase->selectSingleValue("SELECT count(id) as ObsCnt FROM cometobservations WHERE instrumentid=\"".$id."\"",'ObsCnt',0);
	}
	public  function getObserverFromInstrument($id)                                                 // returns the observerid for this instrument
  { global $objDatabase; return $objDatabase->selectSingleValue("SELECT observer FROM instruments WHERE id = \"".$id."\"",'observer');
  }
  public  function getSortedInstruments($sort,$observer="")                                       // returns an array with the ids of all instruments, sorted by the column specified in $sort
  { global $objDatabase; 
    return $objDatabase->selectSingleArray("SELECT id, name FROM instruments ".($observer?"WHERE observer LIKE \"".$observer."\"":" GROUP BY name")." ORDER BY ".$sort.", name",'id');  
  } 
  public  function getSortedInstrumentsList($sort,$observer="")                                   // returns an array with the ids of all instruments, sorted by the column specified in $sort
  { global $objDatabase; 
    return $objDatabase->selectKeyValueArray("SELECT id, name FROM instruments ".($observer?"WHERE observer LIKE \"".$observer."\"":" GROUP BY name")." ORDER BY ".$sort.", name",'id','name');  
  } 
  public  function setInstrumentProperty($id,$property,$propertyValue)                            // sets the property to the specified value for the given instrument
  { global $objDatabase;
    return $objDatabase->execSQL("UPDATE instruments SET ".$property." = \"".$propertyValue."\" WHERE id = \"".$id."\"");
  }
  public  function validateDeleteInstrument()                                                     // validates and deletes the instrument with id = $id 
  { global $objUtil, $objDatabase;
    if(($instrumentid=$objUtil->checkGetKey('instrumentid'))                                                     
    && $objUtil->checkAdminOrUserID($this->getObserverFromInstrument($instrumentid))
		&& (!($this->getInstrumentUsedFromId($instrumentid))))
		{ $objDatabase->execSQL("DELETE FROM instruments WHERE id=\"".$instrumentid."\"");
		  return LangValidateInstrumentMessage5;
		}
  }
  public  function validateSaveInstrument()
	{ global $objUtil, $objDatabase, $objObserver;
	  if(($objUtil->checkPostKey('adaption')==1)
    && $objUtil->checkPostKey('stdtelescope')
    && $objUtil->checkUserID($this->getObserverFromInstrument($objUtil->checkPostKey('stdtelescope'))))
    { echo "hello";
    	$objObserver->setObserverProperty($_SESSION['deepskylog_id'],'stdtelescope', $_POST['stdtelescope']);
      return;
    }
    if($objUtil->checkPostKey('instrumentname')
    && $objUtil->checkPostKey('diameter')
    && $objUtil->checkPostKey('type'))
    { $instrumentname=htmlspecialchars($_POST['instrumentname']);
      $instrumentname=htmlspecialchars_decode($instrumentname, ENT_QUOTES);
      $type=htmlspecialchars($_POST['type']);
      $diameter=$_POST['diameter'];
      if($objUtil->checkPostKey('fd')
      || $objUtil->checkPostKey('focallength')
      ||($objUtil->checkPostKey('type')==InstrumentBinoculars||$objUtil->checkPostKey('type')==InstrumentFinderscope)) 
      { $fd=0;
        $fixedMagnification=$objUtil->checkPostKey('fixedMagnification');
        if($objUtil->checkPostKey('diameterunits')=="inch")
          $diameter*=25.4;
        if($_POST['focallength']&&($_POST['type']!= InstrumentBinoculars)) // focal length filled in
        { $focallength=$_POST['focallength'];
          if(array_key_exists('focallengthunits', $_POST) 
          && $_POST['focallengthunits'] == "inch" 
          && !array_key_exists('fd', $_POST))
            $focallength = $focallength * 25.4;
          if($diameter>0)
            $fd=$focallength/$diameter;
        }
        elseif (array_key_exists('fd', $_POST)
             && $_POST['fd']
             && array_key_exists('type',$_POST)
             && ($_POST['type']!= InstrumentBinoculars))
          $fd=$objUtil->checkPostKey('fd',1.0);
      }
      if($objUtil->checkPostKey('add'))
      { if ($fd > 1.0) 
  		    $fixedMag = 0;
				else 
  		  { $fixedMag = $objUtil->checkPostKey('fixedMagnification',0);
  		    $fd = 0.0;
  	    }
  	    $this->addInstrument($instrumentname, $diameter, $fd, $type, $fixedMag, $_SESSION['deepskylog_id']);
        return LangValidateInstrumentMessage3;
      }
      if($objUtil->checkPostKey('change')
      && $objUtil->checkAdminOrUserID($this->getObserverFromInstrument($objUtil->checkPostKey('id')))) // change instrument of this user
      { $id = $_POST['id'];
        $this->setInstrumentProperty($_POST['id'], 'type', $type);
        $this->setInstrumentProperty($_POST['id'], 'name', $instrumentname);
        $this->setInstrumentProperty($_POST['id'], 'diameter', $diameter);
        if ($fd > 1.0)
        { $this->setInstrumentProperty($_POST['id'], 'fd', $fd);
    	    $this->setInstrumentProperty($_POST['id'], 'fixedMagnification', 0);
        } 
				else 
				{ $this->setInstrumentProperty($_POST['id'], 'fd', 0);
          $this->setInstrumentProperty($_POST['id'], 'fixedMagnification', $objUtil->checkPostKey('fixedMagnification'));
        }
      }
    }
    return LangValidateInstrumentMessage4;
	}
}
$objInstrument=new Instruments;
?>
