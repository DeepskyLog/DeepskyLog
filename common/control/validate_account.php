<?php
// validate_account.php
// checks if the change account or register form is correctly filled in

if (!$_POST['email']||!$_POST['firstname']||!$_POST['name']||!$_POST['passwd']||!$_POST['passwd_again'])
{ $entryMessage=LangValidateAccountMessage1;
	$_GET['indexAction']='subscribe';
}
elseif ($_POST['passwd']!=$_POST['passwd_again'])
{ $entryMessage=LangValidateAccountMessage2;                                                              
	$_GET['indexAction']='subscribe';
}
elseif (!preg_match("/.*@.*..*/", $_POST['email']) | preg_match("/(<|>)/", $_POST['email']))
{ $entryMessage=LangValidateAccountMessage3;                              // check if email address is legal (contains @ symbol)
	$_GET['indexAction']='subscribe';
} 
elseif(array_key_exists('register',$_POST)&&array_key_exists('deepskylog_id',$_POST)&&$_POST['register']&&$_POST['deepskylog_id']) 
{ if($objObserver->getObserverName($_POST['deepskylog_id']))               // user doesn't exist yet
  { $entryMessage=LangValidateAccountMessage4;                              // check if email address is legal (contains @ symbol)
	  $_GET['indexAction']='subscribe';
  }  
  else
	{ $objObserver->addObserver($_POST['deepskylog_id'],$_POST['name'],$_POST['firstname'],$_POST['email'],md5($_POST['passwd']));
    $allLanguages=$objLanguage->getAllLanguages($_SESSION['lang']);         // READ ALL THE LANGUAGES FROM THE CHECKBOXES
    while(list($key,$value)=each($allLanguages))
      if(array_key_exists($key,$_POST))
        $usedLanguages[]=$key;
    $objObserver->setUsedLanguages($_POST['deepskylog_id'], $usedLanguages);
    $objObserver->setObserverObservationLanguage($_POST['deepskylog_id'], $_POST['description_language']);
    $objObserver->setObserverLanguage($_POST['deepskylog_id'], $_POST['language']);
    $body = LangValidateAccountEmailLine1 . "\n"                            // send mail to administrator
              . "\n" . LangValidateAccountEmailLine1bis
              . $_POST['deepskylog_id']
              . "\n" . LangValidateAccountEmailLine2
              . $_POST['email']
              . "\n" . LangValidateAccountEmailLine3
              . $_POST['firstname'] . " " . $_POST['name']
              . "\n\n" . LangValidateAccountEmailLine4;
    $admins=$objObserver->getAdministrators();                              // message recipient(s)
    while(list ($key, $value) = each($admins))
      if($objObserver->getEmail($value))
        $adminMails[]=$objObserver->getEmail($value);
    $to = implode(",", $adminMails);
    $subject = LangValidateAccountEmailTitle;
    $administrators = $objObserver->getAdministrators();
    $fromMail = $objObserver->getEmail($administrators[0]);
    $headers = "From:".$fromMail;
    if(!mail($to, $subject, $body, $headers))
  	  throw new Exception('Unable to mail');
    $_GET['indexAction']="default_action";
  }
}  
elseif(array_key_exists('change', $_POST)&&$_POST['change'])                // pressed change button
{ if(!$_SESSION['deepskylog_id'])                                           // extra control on login
  { $entryMessage=LangValidateAccountMessage1;                              
	  $_GET['indexAction']='subscribe';
  }
  else
	{ $allLanguages=$objLanguage->getAllLanguages($_SESSION['lang']);         // READ ALL THE LANGUAGES FROM THE CHECKBOXES
    while(list($key,$value)=each($allLanguages))
      if(array_key_exists($key,$_POST))
        $usedLanguages[]=$key;
    $objObserver->setUsedLanguages($_SESSION['deepskylog_id'], $usedLanguages);
    $objObserver->setObserverName($_SESSION['deepskylog_id'], $_POST['name']);  
    $objObserver->setFirstName($_SESSION['deepskylog_id'], $_POST['firstname']);
    $objObserver->setEmail($_SESSION['deepskylog_id'], $_POST['email']);
    $objObserver->setPassword($_SESSION['deepskylog_id'], md5($_POST['passwd'])); 
    $objObserver->setObserverLanguage($_SESSION['deepskylog_id'], $_POST['language']);
    $objObserver->setObserverObservationLanguage($_SESSION['deepskylog_id'], $_POST['description_language']);
    $objObserver->setStandardLocation($_SESSION['deepskylog_id'], $_POST['site']);
    $objObserver->setStandardTelescope($_SESSION['deepskylog_id'], $_POST['instrument']);
    $objObserver->setStandardAtlas($_SESSION['deepskylog_id'], $_POST['atlas']);
    if (array_key_exists('local_time', $_POST)&&($_POST['local_time']=="on"))
      $objObserver->setUseLocal($_SESSION['deepskylog_id'],1);
    else
      $objObserver->setUseLocal($_SESSION['deepskylog_id'],0);
    if ($_POST['icq_name'] != "")
      $objObserver->setIcqName($_SESSION['deepskylog_id'], $_POST['icq_name']);
    $_SESSION['lang']=$_POST['language'];
    if($_FILES['picture']['tmp_name'] != "")
    { $upload_dir = '../observer_pics';
      $dir = opendir($upload_dir);
      include "resize.php";                                             // resize code
      $original_image = $_FILES['picture']['tmp_name'];
      $destination_image = $upload_dir . "/" . $_SESSION['deepskylog_id'] . ".jpg"; 
      $max_width = "300";
      $max_height = "300";
      $resample_quality = "75";
      $new_image = image_createThumb($original_image, $destination_image, $max_width, $max_height, $resample_quality);
    }
    $entryMessage=LangValidateAccountMessage5;
    $_GET['indexAction']='default_action';  
  }
}
?>
