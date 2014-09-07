<?php 
// instruments.php
// The instruments class collects all functions needed to enter, retrieve and adapt instrument data from the database.

global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";

class Instruments
{ public  function addInstrument($name, $diameter, $fd, $type, $fixedMagnification, $observer)    // adds a new instrument to the database. The name, diameter, fd and type should be given as parameters. 
  { global $objDatabase; $objDatabase->execSQL("INSERT INTO instruments (name, diameter, fd, type, fixedMagnification, observer) VALUES (\"$name\", \"$diameter\", \"$fd\", \"$type\", \"$fixedMagnification\", \"$observer\")");
    return $objDatabase->selectSingleValue("SELECT id FROM instruments ORDER BY id DESC LIMIT 1",'id');
  }
  public  function getAllInstrumentsIds($id)                                                      // returns a list with all id's which have the same name as the name of the given id
  { global $objDatabase;
    return $objDatabase->selectSingleArray("SELECT id FROM instruments WHERE name = \"".($objDatabase->selectSingleValue("SELECT name FROM instruments WHERE id = \"".addslashes($id)."\"",'name'))."\"",'id');
  }
  public  function getInstrumentEchoType($instrumentType)
  { if($instrumentType== InstrumentNakedEye)          return InstrumentsNakedEye;
    if($instrumentType== InstrumentBinoculars)        return InstrumentsBinoculars;
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
  public  function getInstrumentEchoListType($type, $disabled="")
  { $tempTypeList ="<select name=\"type\" class=\"form-control\" ".$disabled." >";
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
  { global $objDatabase; return $objDatabase->selectSingleValue("SELECT id FROM instruments where name=\"".$name."\" and observer=\"".$observer."\"",'id',-1);
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
  public  function getSortedInstruments($sort,$observer="",$active='')                                       // returns an array with the ids of all instruments, sorted by the column specified in $sort
  { global $objDatabase; 
    return $objDatabase->selectSingleArray("SELECT id, name FROM instruments ".($observer?"WHERE observer LIKE \"".$observer."\" ".($active?" AND instrumentactive=".$active:""):" GROUP BY name")." ORDER BY ".$sort.", name",'id');  
  } 
  public  function getSortedInstrumentsList($sort,$observer="",$active='')                                   // returns an array with the ids of all instruments, sorted by the column specified in $sort
  { global $objDatabase; 
    return $objDatabase->selectKeyValueArray("SELECT id, name FROM instruments ".($observer?"WHERE observer LIKE \"".$observer."\" ".($active?" AND instrumentactive=".$active:""):" GROUP BY name")." ORDER BY ".$sort.", name",'id','name');  
  } 
  public  function setInstrumentProperty($id,$property,$propertyValue)                            // sets the property to the specified value for the given instrument
  { global $objDatabase;
    return $objDatabase->execSQL("UPDATE instruments SET ".$property." = \"".$propertyValue."\" WHERE id = \"".$id."\"");
  }
  public  function showInstrumentsObserver()
  { global $baseURL,$loggedUser,$objUtil,$objObserver,$objInstrument,$objPresentations,$loggedUserName;
		$insts=$objInstrument->getSortedInstruments('id',$loggedUser);
		if(count($insts)>0)
		{ echo "<form action=\"".$baseURL."index.php\" method=\"post\"><div>";
		  echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_instrument\" />";
		  echo "<input type=\"hidden\" name=\"adaption\" value=\"1\" />";
		  // Add the button to select which columns to show
		  $objUtil->addTableColumSelector();
		  
		  echo "<table class=\"table sort-table table-condensed table-striped table-hover tablesorter custom-popup\">";
		  echo "<thead>";
		  echo "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\">".LangViewActive."</td>";
		  
		  echo "<th>".LangOverviewInstrumentsName."</th>";
		  echo "<th>".LangOverviewInstrumentsDiameter."</th>";
		  echo "<th>".LangOverviewInstrumentsFD."</th>";
		  echo "<th>".LangOverviewInstrumentsFixedMagnification."</th>";
		  echo "<th>".LangOverviewInstrumentsType."</th>";
		  echo "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\">".LangChangeAccountField8."</th>";
		  echo "<th>".LangTopObserversHeader3."</th>";
		  echo "</thead>";
			while(list($key,$value)=each($insts))
		  { $name = $objInstrument->getInstrumentPropertyFromId($value,'name');
		    $diameter = round($objInstrument->getInstrumentPropertyFromId($value,'diameter'), 0);
		    $fd=round($objInstrument->getInstrumentPropertyFromId($value,'fd'), 1);
		    if($fd=="0")
		      $fd = "-";
		    $type = $objInstrument->getInstrumentPropertyFromId($value,'type');
		    $fixedMagnification = $objInstrument->getInstrumentPropertyFromId($value,'fixedMagnification');
		    echo "<tr>";
        echo "<td>".
            "<input id=\"instrumentactive".$value."\" type=\"checkbox\" ".($objInstrument->getInstrumentPropertyFromId($value,'instrumentactive')?" checked=\"checked\" ":"").
                    " onclick=\"setactivation('instrument',".$value.");\" />".
            "</td>";
		    if ($name == "Naked eye")
		      echo "<td><a href=\"".$baseURL."index.php?indexAction=detail_instrument&amp;instrument=".urlencode($value)."\">".InstrumentsNakedEye."</a></td>";
		    else
		      echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_instrument&amp;instrument=".urlencode($value)."\">".$name."</a></td>";
		    echo "<td>$diameter</td>";
		    echo "<td>$fd</td>";
				echo "<td>";
		    if($fixedMagnification>0)
		      echo($fixedMagnification);
		    else
		      echo("-");
				echo "</td>";
				echo "<td>";
		    echo $objInstrument->getInstrumentEchoType($type);
		    echo "</td>";
				echo "<td>";
				
				
				// Radio button for the standard instrument
		    if($value==$objObserver->getObserverProperty($loggedUser,'stdtelescope'))
			    echo("<input type=\"radio\" name=\"stdtelescope\" value=\"". $value ."\" checked=\"checked\" onclick=\"submit();\" />&nbsp;<br />");
			  else
					echo("<input type=\"radio\" name=\"stdtelescope\" value=\"". $value ."\" onclick=\"submit();\"/>&nbsp;<br />");
		    echo "</td>";
				echo "<td>";
		    if(!($obsCnt=$objInstrument->getInstrumentUsedFromId($value))) {
		      echo "<a href=\"".$baseURL."index.php?indexAction=validate_delete_instrument&amp;instrumentid=".urlencode($value)."\">".LangRemove."</a>";
		    } else {
		      echo "<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;observer=".$loggedUser."&amp;instrument=".$value."&amp;exactinstrumentlocation=true\">";
		      if ($obsCnt > 1) {
		        echo $obsCnt.' '.LangGeneralObservations."</a>";
		      } else {
		       echo $obsCnt.' '.LangGeneralObservation."</a>";
		      }
		    }
			echo "</td>";
			echo "</tr>";
		  }
		  echo "</table>";
		  echo $objUtil->addTablePager();
		  
		  echo $objUtil->addTableJavascript();
		  
		  echo "</div></form>";
		  echo "<hr />";
		}  	
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
	{ global $objUtil, $objDatabase, $objObserver,$loggedUser;
	  if(($objUtil->checkPostKey('adaption')==1)
    && $objUtil->checkPostKey('stdtelescope')
    && $objUtil->checkUserID($this->getObserverFromInstrument($objUtil->checkPostKey('stdtelescope'))))
    { $objObserver->setObserverProperty($loggedUser,'stdtelescope', $_POST['stdtelescope']);
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
  	    $this->addInstrument($instrumentname, $diameter, $fd, $type, $fixedMag, $loggedUser);
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
        return LangValidateInstrumentMessage4;
      }
    }
    else
      return LangValidateMessage1;
	}
}
?>
