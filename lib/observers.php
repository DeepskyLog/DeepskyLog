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
  public  function valideAccount()
	{	global $entryMessage, $objUtil, $objLanguage;
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
		    if(!mail($to,$subject,$body,$headers))
		  	  throw new Exception('Unable to mail');
        $entryMessage = LangAccountSubscribed1.LangAccountSubscribed2.LangAccountSubscribed3.LangAccountSubscribed4.LangAccountSubscribed5.LangAccountSubscribed6.LangAccountSubscribed7.LangAccountSubscribed8.LangAccountSubscribed9;
		    $_GET['user']=$_POST['deepskylog_id'];
		    $_GET['indexAction']='detail_observer';
		  }
		}  
		elseif($objUtil->checkPostKey('change'))                // pressed change button
		{ if(!$_SESSION['deepskylog_id'])                                           // extra control on login
		  { $entryMessage.=LangValidateAccountMessage1;                              
			  $_GET['indexAction']='change_account';
		  }
		  else
			{ $usedLanguages=array();
			  while(list($key,$value)=each($allLanguages))
		      if(array_key_exists($key,$_POST))
		        $usedLanguages[]=$key;
		    $this->setUsedLanguages($_SESSION['deepskylog_id'], $usedLanguages);
		    $this->setObserverProperty($_SESSION['deepskylog_id'],'name', $_POST['name']);  
		    $this->setObserverProperty($_SESSION['deepskylog_id'],'firstname', $_POST['firstname']);
		    $this->setObserverProperty($_SESSION['deepskylog_id'],'email', $_POST['email']);
		    $this->setObserverProperty($_SESSION['deepskylog_id'],'password', md5($_POST['passwd'])); 
		    $this->setObserverProperty($_SESSION['deepskylog_id'],'language', $_POST['language']);
		    $this->setObserverProperty($_SESSION['deepskylog_id'],'observationlanguage', $_POST['description_language']);
		    $this->setObserverProperty($_SESSION['deepskylog_id'],'stdlocation', $_POST['site']);
		    $this->setObserverProperty($_SESSION['deepskylog_id'],'stdtelescope', $_POST['instrument']);
		    $this->setObserverProperty($_SESSION['deepskylog_id'],'standardAtlasCode', $_POST['atlas']);
		    $this->setObserverProperty($_SESSION['deepskylog_id'],'UT', ((array_key_exists('local_time', $_POST)&&($_POST['local_time']=="on"))?"0":"1"));
		    if ($_POST['icq_name'] != "")
		      $this->setObserverProperty($_SESSION['deepskylog_id'],'icqname', $_POST['icq_name']);
		    $_SESSION['lang']=$_POST['language'];
		    if($_FILES['picture']['tmp_name'] != "")
		    { $upload_dir = 'common/observer_pics';
		      $dir = opendir($upload_dir);
		      include $instDir."common/control/resize.php";                                             // resize code
		      $original_image = $_FILES['picture']['tmp_name'];
		      $destination_image = $upload_dir . "/" . $_SESSION['deepskylog_id'] . ".jpg"; 
		      $max_width = "300";
		      $max_height = "300";
		      $resample_quality = "75";
		      $new_image = image_createThumb($original_image, $destination_image, $max_width, $max_height, $resample_quality);
		    }
		    $entryMessage.=LangValidateAccountMessage5;
		    $_GET['user']=$_SESSION['deepskylog_id'];
		    $_GET['indexAction']='detail_observer';
		  }
		}
	}
	public  function validateObserver()                                          // validateObserver validates the user with the given id and gives the user the given role
  { global $objDatabase; 
    $objDatabase->execSQL("UPDATE observers SET role = \"".RoleUser."\" WHERE id=\"".$objUtil->checkGetKey('validate')."\"");
    $subject = LangValidateSubject;
    if ($role == RoleAdmin) $ad = LangValidateAdmin;
	  else                    $ad = "";
    $array = array(LangValidateMail1, $id, LangValidateMail2, $ad, LangValidateMail3);
    $body = implode("", $array);
    $administrators = $this->getAdministrators();
    $fromMail = $this->getObserverProperty($administrators[0],'email');
    $headers = "From:".$fromMail;
    mail($this->getObserverProperty($id,'email'), $subject, $body, $headers);
    return LangValidateObserverMessage1.' '.LangValidateObserverMessage2;
  }
}
$objObserver=new Observers;
?>
