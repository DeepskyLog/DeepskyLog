<?php // The eyepieces class collects all functions needed to enter, retrieve and adapt eyepiece data from the database.
interface iEyepieces
{ public  function addEyepiece($name, $focalLength, $apparentFOV);                   // adds a new eyepiece to the database. The name, focalLength and apparentFOV should be given as parameters. 
  public  function getAllEyepiecesIds($id);                                          // returns a list with all id's which have the same name as the name of the given id
  public  function getEyepieceId($name, $observer);                                  // returns the id for this eyepiece
  public  function getEyepieceObserverPropertyFromName($name, $observer, $property); // returns the property for the eyepiece of the observer
  public  function getEyepiecePropertiesFromId($id);                                 // returns the properties of the eyepiece with id in an array(propertyname)=propertyvalue
  public  function getEyepiecePropertyFromId($id,$property,$defaultValue='');        // returns the property of the given eyepiece
  public  function getEyepieceUsedFromId($id);                                       // returns the number of times the eyepiece is used in observations
  public  function getSortedEyepieces($sort,$observer="");                           // returns an array with the ids of all eyepieces, sorted by the column specified in $sort
  public  function setEyepieceProperty($id,$property,$propertyValue);                // sets the property to the specified value for the given eyepiece
  public  function showEyepiecesObserver();
  public  function validateDeleteEyepiece();                                         // validates and deletes an eyepiece
  public  function validateSaveEyepiece();                                           // validates and saves an eyepiece and returns a message 
} 
class Eyepieces implements iEyepieces
{public function addEyepiece($name, $focalLength, $apparentFOV)                      // addEyepiece adds a new eyepiece to the database. The name, focalLength and apparentFOV should be given as parameters. 
 { global $objDatabase;
   $objDatabase->execSQL("INSERT INTO eyepieces (name, focalLength, apparentFOV) VALUES (\"".$name."\", \"".$focalLength."\", \"".$apparentFOV."\")");
   return $objDatabase->selectSingleValue("SELECT id FROM eyepieces ORDER BY id DESC LIMIT 1",'id','');
 }
 public  function getAllEyepiecesIds($id)                                            // getAllIds returns a list with all id's which have the same name as the name of the given id
 { global $objDatabase;
   return $objDatabase->selectSingleArray("SELECT id FROM eyepieces WHERE name=".$objDatabase->selectSingleValue("SELECT name FROM eyepieces WHERE id = \"".$id."\"",'name'),'id');
 }
 public  function getEyepieceId($name, $observer)                                    // returns the id for this eyepiece
 { global $objDatabase; return $objDatabase->selectSingleValue("SELECT id FROM eyepieces where name=\"".htmlentities($name,ENT_COMPAT,"ISO-8859-15",0)."\" and observer=\"".$observer."\"",'id',-1);
 }
 public  function getEyepieceObserverPropertyFromName($name, $observer, $property)   // returns the property for the eyepiece of the observer
 { global $objDatabase; 
   return $objDatabase->selectSingleValue("SELECT ".$property." FROM eyepieces where name=\"".$name."\" and observer=\"".$observer."\"",$property);
 }
 public  function getEyepiecePropertiesFromId($id)                                   // returns the properties of the eyepiece with id
 { global $objDatabase;
   return $objDatabase->selectRecordArray("SELECT * FROM eyepieces WHERE id=\"".$id."\"");
 }
 public  function getEyepiecePropertyFromId($id,$property,$defaultValue='')          // returns the property of the given eyepiece
 { global $objDatabase; 
   return $objDatabase->selectSingleValue("SELECT ".$property." FROM eyepieces WHERE id = \"".$id."\"",$property,$defaultValue);
 }
 public  function getEyepieceUsedFromId($id)                                         // returns the number of times the eyepiece is used in observations
 { global $objDatabase; 
   return $objDatabase->selectSingleValue("SELECT count(id) as ObsCnt FROM observations WHERE eyepieceid=\"".$id."\"",'ObsCnt',0);
 }
 public  function getSortedEyepieces($sort,$observer="")                             // returns an array with the ids of all eyepieces, sorted by the column specified in $sort
 { global $objDatabase; 
   return $objDatabase->selectSingleArray("SELECT id, name FROM eyepieces ".($observer?"WHERE observer LIKE \"".$observer."\"":" GROUP BY name")." ORDER BY ".$sort.", name",'id');  
 }
 public  function setEyepieceProperty($id,$property,$propertyValue)                  // sets the property to the specified value for the given eyepiece
 { global $objDatabase;
   return $objDatabase->execSQL("UPDATE eyepieces SET ".$property." = \"".$propertyValue."\" WHERE id = \"".$id."\"");
 }
 public  function showEyepiecesObserver()
 { global $baseURL,$loggedUser,$objUtil,$objEyepiece,$objPresentations,$loggedUserName;
   $sort=$objUtil->checkGetKey('sort','focalLength');
   $eyeps = $objEyepiece->getSortedEyepieces($sort, $loggedUser);
   if($eyeps!=null)
   { $orig_previous=$objUtil->checkGetKey('previous','');
     if((isset($_GET['sort'])) && ($orig_previous==$_GET['sort'])) // reverse sort when pushed twice
     { if($_GET['sort']=="name")
         $eyeps = array_reverse($eyeps, true);
       else
       { krsort($eyeps);
         reset($eyeps);
       }
       $previous=""; // reset previous field to sort on
     }
     else
       $previous=$sort;
     $objPresentations->line(array("<h5>".LangOverviewEyepieceTitle." ".$loggedUserName."</h5>"),"L",array(),50);
     echo "<hr />"; 
     echo "<table width=\"100%\">";
     echo "<tr class=\"type3\">";
     echo "<td><a href=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;sort=name&amp;previous=$previous\">".LangViewEyepieceName."</a></td>";
     echo "<td align=\"center\"><a href=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;sort=focalLength&amp;previous=$previous\">".LangViewEyepieceFocalLength."</a></td>";
     echo "<td align=\"center\"><a href=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;sort=maxFocalLength&amp;previous=$previous\">".LangViewEyepieceMaxFocalLength."</a></td>";
     echo "<td align=\"center\"><a href=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;sort=apparentFOV&amp;previous=$previous\">".LangViewEyepieceApparentFieldOfView."</a></td>";
     echo "<td></td>";
     echo "</tr>";
     $count = 0;
     while(list($key,$value) = each($eyeps))
     { $eyepiece=$objEyepiece->getEyepiecePropertiesFromId($value);
       echo "<tr class=\"type".(2-($count%2))."\">";
		   echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_eyepiece&amp;eyepiece=".urlencode($value)."\">".stripslashes($eyepiece['name'])."</a></td>";
		   echo "<td align=\"center\">".$eyepiece['focalLength']."</td>";
		   echo "<td align=\"center\">".(($eyepiece['maxFocalLength']!=-1)?$eyepiece['maxFocalLength']:"-")."</td>";
		   echo "<td align=\"center\">".$eyepiece['apparentFOV']."</td>";
		   echo "<td>";
       if(!($obsCnt=$objEyepiece->getEyepieceUsedFromId($value)))
         echo("<a href=\"".$baseURL."index.php?indexAction=validate_delete_eyepiece&amp;eyepieceid=" . urlencode($value) . "\">" . LangRemove . "</a>");
       else
         echo "<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;observer=".$loggedUser."&amp;eyepiece=".$value."&amp;exactinstrumentlocation=true\">".$obsCnt.' '.LangGeneralObservations."</a>";
       echo "</td></tr>";
       $count++;
     }
     echo "</table>";
     echo "<hr />";
   }
 }
 public  function validateDeleteEyepiece()                                          // validates and deletes an eyepiece
 { global $objUtil, $objDatabase;
   if(($eyepieceid=$objUtil->checkGetKey('eyepieceid')) 
   && $objUtil->checkAdminOrUserID($this->getEyepiecePropertyFromId($eyepieceid,'observer'))
   && (!($this->getEyepieceUsedFromId($eyepieceid))))
     return $objDatabase->execSQL("DELETE FROM eyepieces WHERE id=\"".$eyepieceid."\"");
 }
 public  function validateSaveEyepiece()                                             // validates and saves an eyepiece and returns a message 
 { global $objUtil;
   if($objUtil->checkPostKey('eyepiecename')
   && $objUtil->checkSessionKey('deepskylog_id')
   && $objUtil->checkPostKey('focalLength')
   && $objUtil->checkPostKey('apparentFOV')
   && $objUtil->checkPostKey('add'))
   { $id=$this->addEyepiece($_POST['eyepiecename'],$_POST['focalLength'],$_POST['apparentFOV']);
     $this->setEyepieceProperty($id,'observer', $_SESSION['deepskylog_id']);
	   $this->setEyepieceProperty($id,'maxFocalLength', $objUtil->checkPostKey('maxFocalLength',-1));
     return LangValidateEyepieceMessage2; 
   }
   if($objUtil->checkPostKey('id')
   && $objUtil->checkPostKey('eyepiecename')
   && $objUtil->checkPostKey('focalLength')
   && $objUtil->checkPostKey('apparentFOV')
   && $objUtil->checkPostKey('change')
   && $objUtil->checkAdminOrUserID($this->getEyepiecePropertyFromId($_POST['id'],'observer')))
   { $this->setEyepieceProperty($_POST['id'],'name', $_POST['eyepiecename']);
     $this->setEyepieceProperty($_POST['id'],'focalLength', $_POST['focalLength']);
     $this->setEyepieceProperty($_POST['id'],'apparentFOV', $_POST['apparentFOV']);
     $this->setEyepieceProperty($_POST['id'],'observer', $_SESSION['deepskylog_id']);
     $this->setEyepieceProperty($_POST['id'],'maxFocalLength', $objUtil->checkPostKey('maxFocalLength',-1));
 	   return LangValidateEyepieceMessage5.' '.LangValidateEyepieceMessage4;
   }
 }
}
$objEyepiece=new Eyepieces;
?>
