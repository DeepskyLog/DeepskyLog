<?php  
// lenses.php
// The lenses class collects all functions needed to enter, retrieve and adapt lenses data from the database.

global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";

class Lenses
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
	public  function getSortedLenses($sort, $observer = "",$active='')                       // returns an array with the ids of all lenses, sorted by the column specified in $sort
  { global $objDatabase; 
    return $objDatabase->selectSingleArray("SELECT id, name FROM lenses ".($observer?"WHERE observer LIKE \"".$observer."\" ".($active?" AND lensactive=".$active:""):" GROUP BY name")." ORDER BY ".$sort.", name",'id');  
  } 
  public  function setLensProperty($id,$property,$propertyValue)                // sets the property to the specified value for the given lens
  { global $objDatabase;
    return $objDatabase->execSQL("UPDATE lenses SET ".$property." = \"".$propertyValue."\" WHERE id = \"".$id."\"");
  }
  public  function getLensUsedFromId($id)                                       // returns the number of times the lens is used in observations
  { global $objDatabase; 
    return $objDatabase->selectSingleValue("SELECT count(id) as ObsCnt FROM observations WHERE lensid=\"".$id."\"",'ObsCnt',0);
  }
  public  function showLensesObserver()
  { global $baseURL,$loggedUser,$objUtil,$objLens,$objPresentations,$loggedUserName;
    $sort=$objUtil->checkGetKey('sort','name');
		$lns =$objLens->getSortedLenses($sort, $loggedUser);
		if ($lns!=null)
		{ $orig_previous=$objUtil->checkGetKey('previous','');
		  if((isset($_GET['sort']))&&($orig_previous==$_GET['sort'])) // reverse sort when pushed twice
		  { if ($_GET['sort'] == "name")
		      $lns = array_reverse($lns, true);
		    else
		    { krsort($lns);
		      reset($lns);
		    }
		    $previous = "";
		  }
		  else
		    $previous = $sort;
		  echo "<table>";
		  echo "<tr class=\"type3\">";
      echo "<td class=\"centered\">".LangViewActive."</td>";
		  echo "<td><a href=\"".$baseURL."index.php?indexAction=add_lens&amp;sort=name&amp;previous=$previous\">".LangViewLensName."</a></td>";
		  echo "<td><a href=\"".$baseURL."index.php?indexAction=add_lens&amp;sort=factor&amp;previous=$previous\" class=\"centered\">".LangViewLensFactor."</a></td>";
		  echo "<td></td>";
		  echo "</tr>";
		  $count = 0;
		  while(list($key,$value)=each($lns))
		  { $name = stripslashes($objLens->getLensPropertyFromId($value,'name'));
		    $factor = $objLens->getLensPropertyFromId($value,'factor');
		    echo "<tr class=\"type".(2-($count%2))."\">";
        echo "<td class=\"centered\">".
             "<input id=\"lensactive".$value."\" type=\"checkbox\" ".($objLens->getLensPropertyFromId($value,'lensactive')?" checked=\"checked\" ":"").
                    " onclick=\"setactivation('lens',".$value.");\" />".
            "</td>";
		    echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_lens&amp;lens=".urlencode($value)."\">".$name."</a></td>";
		    echo "<td class=\"centered\">".$factor."</td>";
		    echo "<td>";
		    if(!($obsCnt=$objLens->getLensUsedFromId($value)))
		      echo "<a href=\"".$baseURL."index.php?indexAction=validate_delete_lens&amp;lensid=".urlencode($value)."\">".LangRemove."</a>";
		    else
		      echo "<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;observer=".$loggedUser."&amp;lens=".$value."&amp;exactinstrumentlocation=true\">".$obsCnt.' '.LangGeneralObservations."</a>";
		    echo "</td>";
		    echo "</tr>";
		    $count++;
		  }
		  echo "</table>";
		  echo "<hr />";
		} 	
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
  { global $objUtil,$loggedUser;
    if($objUtil->checkPostKey('add')
    && $objUtil->checkPostKey('lensname')
    && $objUtil->checkPostKey('factor')
    && $loggedUser)
    { $id = $this->addLens($_POST['lensname'], $_POST['factor']);     
    	$this->setLensProperty($id, 'observer', $loggedUser);
      return LangValidateLensMessage2;
    }
    elseif($objUtil->checkPostKey('change')
    && $objUtil->checkAdminOrUserID($this->getLensPropertyFromId($objUtil->checkPostKey('id'),'observer'))
    && $objUtil->checkPostKey('lensname')
    && $objUtil->checkPostKey('factor'))
    { $this->setLensProperty($_POST['id'], 'name', $_POST['lensname']);
      $this->setLensProperty($_POST['id'], 'factor', $_POST['factor']);
      //$this->setLensProperty($_POST['id'], 'observer', $loggedUser);
      return LangValidateLensMessage5;
    }
    else
      return LangValidateMessage1;
 }
}
?>
