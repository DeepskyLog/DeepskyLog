<?php // The observers class collects all functions needed to enter, retrieve and adapt observer data from the database and functions to display the data.
interface iObservers
{ public  function addObserver($id, $name, $firstname, $email, $password);                // adds a new observer to the database. The id, name, first name email address and password should be given as parameters. The password must be encoded using md5(...). The new observer will not be able to log in yet. Before being able to do so, the administrator must validate the new user.
  public  function getAdministrators();
  public  function getCometRank($observer);                                               // returns the number of observations of the given observer
  public  function getDsRank($observer);                                                  // returns the number of observations of the given observer
  public  function getListOfInstruments();                                                // returns a list of all StandardInstruments of all observers
  public  function getListOfLocations();                                                  // returns a list of all StandardLocations of all observers
  public  function getNumberOfCometObservations($observerid);                             // returns the number of comet observations for the given observerid
  public  function getNumberOfDsObservations($observerid);                                // returns the number of observations of the given observerid
  public  function getObserverProperty($id,$property,$defaultValue='');
  public  function getPopularObserversByName();                                           // returns an array with the ids(key) and names(value) of all active observers, sorted by name
  public  function getSortedObservers($sort);                                             // returns an array with the ids of all observers, sorted by the column specified in $sort
  public  function getUsedLanguages($id);
  public  function setObserverProperty($id, $property, $propertyValue);                   // sets a new value for the property of the observer
//private function setUsedLanguages($id, $language);                                      // setUsedLanguages sets all the used languages for the observer with id = $id
  public  function showTopObservers($catalog,$rank,$sort,$min,$max,$step); 
  public  function valideAccount();
  public  function validateObserver();                                                    // validates the user with the given id and gives the user  the given role (which should be $ADMIN or $USER).
}
class Observers implements iObservers
{ public  function addObserver($id, $name, $firstname, $email, $password)                       // addObserver adds a new observer to the database. The id, name, first name email address and password should be given as parameters. The password must be encoded using md5(...). The new observer will not be able to log in yet. Before being able to do so, the administrator must validate the new user.
  { global $objDatabase; 
    return $objDatabase->execSQL("INSERT INTO observers (id, name, firstname, email, password, role, language) VALUES (\"$id\", \"$name\", \"$firstname\", \"$email\", \"$password\", \"".RoleWaitlist."\", \"".$_SESSION['lang']."\")");
  }
  public  function getAdministrators()
  { global $objDatabase; 
    return $objDatabase->selectSingleArray("SELECT id FROM observers WHERE role = \"RoleAdmin\"",'id');
  }
  public  function getCometRank($observer)                                               // getCometRank() returns the number of observations of the given observer
  { global $objCometObservation;
    return array_search($observer,$objCometObservation->getPopularObservers());
  }
  public  function getDsRank($observer)                                                    // getRank() returns the number of observations of the given observer
  { global $objObservation;
    return array_search($observer,$objObservation->getPopularObservers());
  }
  public  function getListOfInstruments()                                                // getListOfInstruments returns a list of all StandardInstruments of all observers
  { global $objDatabase; 
    return $objDatabase->selectSingleArray("SELECT stdtelescope FROM observers GROUP BY stdtelescope",'stdtelescope');
  }
  public  function getListOfLocations()                                                  // getListOfLocations returns a list of all StandardLocations of all observers
  { global $objDatabase; 
    return $objDatabase->selectSingleArray("SELECT stdlocation FROM observers GROUP BY stdlocation",'stdlocation');
  }
  public  function getNumberOfCometObservations($observerid)                             // getNumberOfCometObservations($name) returns the number of comet observations for the given observerid
  { global $objDatabase; 
    return $objDatabase->selectSingleValue("SELECT COUNT(cometobservations.id) As Cnt FROM cometobservations ".($observerid?"WHERE observerid = \"".$observerid."\"":""),'Cnt',0);
  }
  public  function getNumberOfDsObservations($observerid)                                // getNumberOfObservations($name) returns the number of observations of the given observerid
  { global $objDatabase; 
    return $objDatabase->selectSingleValue("SELECT COUNT(observations.id) As Cnt FROM observations ".($observerid?"WHERE observerid = \"".$observerid."\"":""),'Cnt',0);
  }
  public  function getObserverProperty($id,$property,$defaultValue='')
  { global $objDatabase; 
    return $objDatabase->selectSingleValue("SELECT ".$property." FROM observers WHERE id=\"".$id."\"",$property,$defaultValue);
  }
  public  function getPopularObserversByName()                                           // getSortedActiveObservers returns an array with the ids(key) and names(value) of all active observers, sorted by name
  { global $objDatabase; 
   return $objDatabase->selectKeyValueArray("SELECT DISTINCT observers.id, CONCAT(observers.firstname,' ',observers.name) As observername, observers.name FROM observers JOIN observations ON (observers.id = observations.observerid) ORDER BY observers.name",'id','observername');
  }
  public  function getSortedObservers($sort)                                             // getSortedObservers returns an array with the ids of all observers, sorted by the column specified in $sort
  { global $objDatabase; 
    return $objDatabase->selectSingleArray("SELECT observers.id FROM observers ORDER BY $sort",'id');
  }
  public  function getSortedObserversAdmin($sort)                                             // getSortedObservers returns an array with the ids of all observers, sorted by the column specified in $sort
  { global $objDatabase; 
    return $objDatabase->selectRecordsetArray(
    "SELECT observers.*, A.maxLogDate, B.instrumentCount, C.listCount, D.obsCount, E.cometobsCount, (IFNULL(B.instrumentCount,0) + IFNULL(C.listCount,0) + IFNULL(D.obsCount,0) + IFNULL(E.cometobsCount,0)) AS maxMax FROM observers 
     LEFT JOIN (SELECT logging.loginid, MAX(logging.logdate) as maxLogDate FROM logging GROUP BY logging.loginid) AS A ON observers.id=A.loginid 
     LEFT JOIN (SELECT instruments.observer, COUNT(instruments.id) AS instrumentCount FROM instruments GROUP BY instruments.observer) AS B ON observers.id=B.observer 
     LEFT JOIN (SELECT observerobjectlist.observerid, COUNT(DISTINCT observerobjectlist.listname) AS listCount FROM observerobjectlist GROUP BY observerobjectlist.observerid) AS C on observers.id=C.observerid
     LEFT JOIN (SELECT observations.observerid, COUNT(observations.id) AS obsCount FROM observations GROUP BY observations.observerid) AS D on observers.id=D.observerid
     LEFT JOIN (SELECT cometobservations.observerid, COUNT(cometobservations.id) AS cometobsCount FROM cometobservations GROUP BY cometobservations.observerid) AS E on observers.id=E.observerid
     GROUP BY observers.id ORDER BY ".$sort);
  }
  public  function getUsedLanguages($id)
  { global $objDatabase; 
    return unserialize($objDatabase->selectSingleValue("SELECT usedLanguages FROM observers WHERE id = \"$id\"",'usedLanguages',''));
  }
  public  function setObserverProperty($id, $property, $propertyValue)                                                 // sets a new value for the property of the observer
  { global $objDatabase; 
   $objDatabase->execSQL("UPDATE observers SET ".$property."=\"".$propertyValue."\" WHERE id=\"".$id."\"");
  }
  private function setUsedLanguages($id, $language)                                      // setUsedLanguages sets all the used languages for the observer with id = $id
  { global $objDatabase; 
   $objDatabase->execSQL("UPDATE observers SET usedLanguages = '".serialize($language)."' WHERE id=\"$id\"");
  }
  public  function showTopObservers($catalog,$rank,$sort,$min,$max,$step)
  { global $baseURL,$objObservation,$objUtil,$objObserver,$objObject,$catalogs,$FF;
  	$outputtable = "";   $count=0;
		$objectsInCatalog=$objObject->getNumberOfObjectsInCatalog($catalog);
    echo "<div><table>";
	  if($FF)
	    echo "<thead>";
    echo "<tr class=\"type3\">";
		echo "<td class=\"centered\">".LangTopObserversHeader1."</td>";
		echo "<td class=\"centered\"><a href=\"".$baseURL."index.php?indexAction=rank_observers&amp;sort=observer&amp;catalog=".urlencode($catalog)."\">".LangTopObserversHeader2."</a></td>";
		echo "<td class=\"centered\"><a href=\"".$baseURL."index.php?indexAction=rank_observers&amp;sort=totaal&amp;catalog="  .urlencode($catalog)."\">".LangTopObserversHeader3."</a></td>";
		echo "<td class=\"centered\"><a href=\"".$baseURL."index.php?indexAction=rank_observers&amp;sort=jaar&amp;catalog="    .urlencode($catalog)."\">".LangTopObserversHeader4."</a></td>";
		echo "<td class=\"width125px centered\">";
		echo "<select class=\"width125px inputfield\" onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
		while(list($key,$value)=each($catalogs))
		{ if(!($value))
		    $value="-----------";
		  if($value==stripslashes($catalog))
		    echo "<option selected=\"selected\" value=\"".$baseURL."index.php?sort=catalog&amp;indexAction=rank_observers&amp;catalog=".urlencode($value)."\">".$value."</option>";
		  else
			  echo "<option value=\"".$baseURL."index.php?sort=catalog&amp;indexAction=rank_observers&amp;catalog=".urlencode($value)."\">".$value."</option>";
		}
		echo "</select>";
		echo "</td>";
		echo "<td class=\"centered\"><a href=\"".$baseURL."index.php?indexAction=rank_observers&amp;sort=objecten&amp;catalog=".urlencode($catalog)."\">".LangTopObserversHeader6."</a></td>";
		if($FF)
		  echo "<td>&nbsp;&nbsp;</td>";
		echo"</tr>";
		$numberOfObservations = $objObservation->getNumberOfDsObservations();
		$numberOfObservationsThisYear = $objObservation->getObservationsLastYear('%');
		$numberOfDifferentObjects = $objObservation->getNumberOfDifferentObservedDSObjects();
		if($FF)
		{ echo "</thead>";
		  echo "<tfoot>";
		  echo "<tr class=\"type3 centered\"><td>".LangTopObservers1."</td><td></td>".
		                "<td class=\"centered\">$numberOfObservations</td>" .
			              "<td class=\"centered\">$numberOfObservationsThisYear</td>" .
		 							  "<td class=\"centered\">".$objectsInCatalog."</td>" .
									  "<td class=\"centered\">".$numberOfDifferentObjects."</td></tr>";
      echo "</tfoot>";
		  echo "<tbody id=\"topobs_list\" class=\"tbody_obs\">";
		}
		$countline=0;
		while(list($key,$value)=each($rank))
		{ if(($count>=$min)&&($count<$max))
		  { $name = $objObserver->getObserverProperty($key,'name');
		    $firstname = $objObserver->getObserverProperty($key,'firstname');
		    $outputtable .= "<tr class=\"type".(2-($count%2))."\">";
		    $outputtable.="<td class=\"centered\">" . ($count + 1) . "</td><td> <a href=\"".$baseURL."index.php?indexAction=detail_observer&amp;user=".urlencode($key)."\">$firstname&nbsp;$name</a> </td>";
		    if($sort=="totaal") $value2 = $value; else $value2 = $objObservation->getDsObservationsCountFromObserver($key);
		    $outputtable .= "<td class=\"centered\"> $value2 &nbsp;&nbsp;&nbsp;&nbsp;(" . sprintf("%.2f", (($value2 / $numberOfObservations) * 100)). "%)</td>";
		    if($sort=="jaar") $observationsThisYear = $value; else $observationsThisYear = $objObservation->getObservationsLastYear($key);
		    if ($numberOfObservationsThisYear != 0) $percentObservations = ($observationsThisYear / $numberOfObservationsThisYear) * 100; else $percentObservations = 0;
		    $outputtable .= "<td class=\"centered\">". $observationsThisYear . "&nbsp;&nbsp;&nbsp;&nbsp;(".sprintf("%.2f", $percentObservations)."%)</td>";
		    if($sort=="catalog") $objectsCount = $value; else $objectsCount = $objObservation->getObservedCountFromCatalogOrList($key,$catalog);
				$outputtable .= "<td class=\"centered\"> <a href=\"".$baseURL."index.php?indexAction=view_observer_catalog&amp;catalog=".urlencode($catalog)."&amp;user=".urlencode($key)."&amp;title=".urlencode(LangTitleview_observer_catalog." ".$catalog)."\">". $objectsCount . "</a> (" . sprintf("%.2f",(($objectsCount / $objectsInCatalog)*100)) . "%)</td>";
		    if($sort=="objecten") $numberOfObjects = $value; else $numberOfObjects = $objObservation->getNumberOfObjects($key);
		    $outputtable .= "<td class=\"centered\">". $numberOfObjects . "&nbsp;&nbsp;&nbsp;&nbsp;(".sprintf("%.2f", (($numberOfObjects / $numberOfDifferentObjects) * 100))."%)</td>";
		    $outputtable .= "</tr>";
		    $countline++;
		  }
		  $count++;
		}
    if($FF) 
    { while($countline++<$step)
        $outputtable .= "<tr><td>&nbsp;</td></tr>";  
      $outputtable .= "</tbody>"; 
    }
		else
		  $outputtable .= "<tr class=\"type3 centered\"><td>".LangTopObservers1."</td><td></td>".
		                  "<td class=\"centered\">$numberOfObservations</td>" .
			                "<td class=\"centered\">$numberOfObservationsThisYear</td>" .
		 			  				  "<td class=\"centered\">".$objectsInCatalog."</td>" .
						   			  "<td class=\"centered\">".$numberOfDifferentObjects."</td></tr>";
		$outputtable .= "</table></div>";
		$outputtable .= "<hr />";
		echo $outputtable;
  }
  public  function valideAccount()
	{	global $entryMessage, $objUtil, $objLanguage, $developversion,$loggedUser,$allLanguages;
		if(!$_POST['email']||!$_POST['firstname']||!$_POST['name']||!$_POST['passwd']||!$_POST['passwd_again'])
		{ $entryMessage.=LangValidateAccountMessage1;
			if($objUtil->checkPostKey('change')) $_GET['indexAction']='change_account';
			else  $_GET['indexAction']='subscribe';
		}
		elseif($_POST['passwd']!=$_POST['passwd_again'])
		{ $entryMessage.=LangValidateAccountMessage2;                                                              
			if($objUtil->checkPostKey('change')) $_GET['indexAction']='change_account';
			else  $_GET['indexAction']='subscribe';
		}
		elseif(!preg_match("/.*@.*..*/", $_POST['email']) | preg_match("/(<|>)/", $_POST['email']))
		{ $entryMessage.=LangValidateAccountMessage3;                              // check if email address is legal (contains @ symbol)
			if($objUtil->checkPostKey('change')) $_GET['indexAction']='change_account';
			else  $_GET['indexAction']='subscribe';
		} 
		elseif(array_key_exists('register',$_POST)&&array_key_exists('deepskylog_id',$_POST)&&$_POST['register']&&$_POST['deepskylog_id']) 
		{ if($this->getObserverProperty($_POST['deepskylog_id'],'name'))               // user doesn't exist yet
		  { $entryMessage.=LangValidateAccountMessage4;                              // check if email address is legal (contains @ symbol)
			  if($objUtil->checkPostKey('change')) $_GET['indexAction']='change_account';
			  else  $_GET['indexAction']='subscribe';
		  }  
		  else
			{ $this->addObserver($_POST['deepskylog_id'],$_POST['name'],$_POST['firstname'],$_POST['email'],md5($_POST['passwd']));
		    $allLanguages=$objLanguage->getAllLanguages($_SESSION['lang']);         // READ ALL THE LANGUAGES FROM THE CHECKBOXES
		    while(list($key,$value)=each($allLanguages))
		      if(array_key_exists($key,$_POST))
		        $usedLanguages[]=$key;
		    $this->setUsedLanguages($_POST['deepskylog_id'], $usedLanguages);
		    $this->setObserverProperty($_POST['deepskylog_id'],'observationlanguage', $_POST['description_language']);
		    $this->setObserverProperty($_POST['deepskylog_id'],'language', $_POST['language']);
		    $this->setObserverProperty($_POST['deepskylog_id'],'registrationDate', date("Ymd H:i"));
		    $body = LangValidateAccountEmailLine1 . "\n"                            // send mail to administrator
		              . "\n" . LangValidateAccountEmailLine1bis
		              . $_POST['deepskylog_id']
		              . "\n" . LangValidateAccountEmailLine2
		              . $_POST['email']
		              . "\n" . LangValidateAccountEmailLine3
		              . $_POST['firstname'] . " " . $_POST['name']
		              . "\n\n" . LangValidateAccountEmailLine4;
		    $admins=$this->getAdministrators();                              // message recipient(s)
		    while(list($key,$value)=each($admins))
		      if($this->getObserverProperty($value,'email'))
		        $adminMails[]=$this->getObserverProperty($value,'email');
		    $to=implode(",",$adminMails);
		    $subject=LangValidateAccountEmailTitle;
		    $administrators=$this->getAdministrators();
		    $fromMail=$this->getObserverProperty($administrators[0],'email');
		    $headers="From:".$fromMail;
        if(isset($developversion)&&($developversion==true))
          $entryMessage.="On the live server, a mail would be sent with the subject: ".$subject.".<p>";
        else
          mail($to, $subject, $body, $headers);
        $entryMessage = LangAccountSubscribed1.LangAccountSubscribed2.LangAccountSubscribed3.LangAccountSubscribed4.LangAccountSubscribed5.LangAccountSubscribed6.LangAccountSubscribed7.LangAccountSubscribed8.LangAccountSubscribed9;
		    $_GET['user']=$_POST['deepskylog_id'];
		    $_GET['indexAction']='detail_observer';
		  }
		}  
		elseif($objUtil->checkPostKey('change'))                // pressed change button
		{ if(!$loggedUser)                                           // extra control on login
		  { $entryMessage.=LangValidateAccountMessage1;                              
			  $_GET['indexAction']='change_account';
		  }
		  else
			{ $usedLanguages=array();
			  while(list($key,$value)=each($allLanguages))
		      if(array_key_exists($key,$_POST))
		        $usedLanguages[]=$key;
		    $this->setUsedLanguages($loggedUser, $usedLanguages);
		    $this->setObserverProperty($loggedUser,'name', $_POST['name']);  
		    $this->setObserverProperty($loggedUser,'firstname', $_POST['firstname']);
		    $this->setObserverProperty($loggedUser,'email', $_POST['email']);
		    $this->setObserverProperty($loggedUser,'password', md5($_POST['passwd'])); 
		    $this->setObserverProperty($loggedUser,'language', $_POST['language']);
		    $this->setObserverProperty($loggedUser,'observationlanguage', $_POST['description_language']);
		    $this->setObserverProperty($loggedUser,'stdlocation', $_POST['site']);
		    $this->setObserverProperty($loggedUser,'stdtelescope', $_POST['instrument']);
		    $this->setObserverProperty($loggedUser,'standardAtlasCode', $_POST['atlas']);
        $this->setObserverProperty($loggedUser,'fstOffset', $_POST['fstOffset']);
		    $this->setObserverProperty($loggedUser,'UT', ((array_key_exists('local_time', $_POST)&&($_POST['local_time']=="on"))?"0":"1"));
		    if ($_POST['icq_name'] != "")
		      $this->setObserverProperty($loggedUser,'icqname', $_POST['icq_name']);
		    $_SESSION['lang']=$_POST['language'];
		    if($_FILES['picture']['tmp_name'] != "")
		    { $upload_dir = 'common/observer_pics';
		      $dir = opendir($upload_dir);
		      include $instDir."common/control/resize.php";                                             // resize code
		      $original_image = $_FILES['picture']['tmp_name'];
		      $destination_image = $upload_dir . "/" . $loggedUser . ".jpg"; 
		      $new_image = image_createThumb($original_image,$destination_image,300,300,75);
		    }
		    $entryMessage.=LangValidateAccountMessage5;
		    $_GET['user']=$loggedUser;
		    $_GET['indexAction']='detail_observer';
		  }
		}
	}
	public  function validateDeleteObserver()                                          // validateObserver validates the user with the given id and gives the user the given role
  { global $objDatabase,$objUtil, $entryMessage,$loggedUser, $developversion;
    if(!($objUtil->checkSessionKey('admin')=='yes'))
      throw new Exception(LangException001);
    $objDatabase->execSQL("DELETE FROM observers WHERE id=\"".($id=$objUtil->checkGetKey('validateDelete'))."\"");
    $subject = "Deepskylog account deleted";
    $body = "The account for ".$id." was deleted by ".$loggedUser;
    $administrators = $this->getAdministrators();
    $fromMail = $this->getObserverProperty($administrators[0],'email');
    $headers = "From:".$fromMail;
    if(isset($developversion)&&($developversion==1))
      $entryMessage.="On the live server, a mail would be sent with the subject: ".$subject.".<p>";
    else
      mail($fromMail, $subject, $body, $headers);
    return "The user has been erased.";
  }	
  public  function validateObserver()                                          // validateObserver validates the user with the given id and gives the user the given role
  { global $objDatabase,$objUtil, $entryMessage, $developversion;
    if(!($objUtil->checkSessionKey('admin')=='yes'))
      throw new Exception(LangException001);
    $objDatabase->execSQL("UPDATE observers SET role = \"".($role=RoleUser)."\" WHERE id=\"".($id=$objUtil->checkGetKey('validate'))."\"");
    $subject = LangValidateSubject;
    if ($role == RoleAdmin) $ad = LangValidateAdmin;
	  else                    $ad = "";
    $array = array(LangValidateMail1, $id, LangValidateMail2, $ad, LangValidateMail3);
    $body = implode("", $array);
    $administrators = $this->getAdministrators();
    $fromMail = $this->getObserverProperty($administrators[0],'email');
    $headers = "From:".$fromMail;
    if(isset($developversion)&&($developversion==1))
      $entryMessage.="On the live server, a mail would be sent with the subject: ".$subject.".<br />";
    else
      mail($this->getObserverProperty($id,'email'), $subject, $body, $headers);
    return LangValidateObserverMessage1.' '.LangValidateObserverMessage2;
  }
}
$objObserver=new Observers;
?>
