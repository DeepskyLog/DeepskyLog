<?php
// validate_object.php
// checks if the add new object form is correctly filled in
// and eventually adds the object to the database

if ($_POST['newobject'])
{ $check = true;
  $ra=$objUtil->checkPostKey('RAhours',0)+($objUtil->checkPostKey('RAminutes',0)/60)+($objUtil->checkPostKey('RAseconds',0)/3600);
  if(array_key_exists('DeclDegrees',$_POST)&&(($_POST['DeclDegrees']<0)||(strcmp($_POST['DeclDegrees'],'-0')==0)))
    $declination=$objUtil->checkPostKey('DeclDegrees',0)-($objUtil->checkPostKey('DeclMinutes',0)/60)-($objUtil->checkPostKey('DeclSeconds',0)/3600);
  else
    $declination=$objUtil->checkPostKey('DeclDegrees',0)+($objUtil->checkPostKey('DeclMinutes',0)/60)+($objUtil->checkPostKey('DeclSeconds',0)/3600);
  if(!$objUtil->checkPostKey('number')||!$objUtil->checkPostKey('type')||!$objUtil->checkPostKey('con')||($ra==0.0)||($declination==0.0))
  { $entryMessage=LangValidateObjectMessage1;                                   // check if required fields are filled in
    $_GET['indexAction']='add_object';
  } 
  if($check)                                                                    // check name
  { $catalog=trim($_POST['catalog']);
    $catalogs=$objObject->getCatalogs();
    $foundcatalog="";
    while((list($key,$value)=each($catalogs))&&(!$foundcatalog))
      if(strtoupper($value) == strtoupper($catalog))
        $foundcatalog = $value;
    if($foundcatalog)
      $catalog = $foundcatalog;
    $name = trim($catalog." ".ucwords(trim($_POST['number'])));
    $query1=array("name"=>$name);
    if($objObject->getObjectFromQuery($query1, 1))                                // object already exists
    { $entryMessage=LangValidateObjectMessage2;
      $_GET['indexAction']='add_object';
			$check = false;
    }
	}
  if($check)                                                                    // calculate right ascension
    if((!$objUtil->checkLimitsInclusive($objUtil->checkPostKey('RAhours',-1),0,23))||
	     (!$objUtil->checkLimitsInclusive($objUtil->checkPostKey('RAminutes',-1),0,59))||
			 (!$objUtil->checkLimitsInclusive($objUtil->checkPostKey('RAseconds',-1),0,59)))
    { $entryMessage=LangValidateObjectMessage4;
      $_GET['indexAction']='add_object';
			$check = false;
    }
  if($check)                                                                    // calculate declination
    if((!$objUtil->checkLimitsInclusive($objUtil->checkPostKey('DeclDegrees',-100),-90,90))||
	     (!$objUtil->checkLimitsInclusive($objUtil->checkPostKey('DeclMinutes',-1),0,59))||
			 (!$objUtil->checkLimitsInclusive($objUtil->checkPostKey('DeclSeconds',-1),0,59)))
    { $entryMessage=LangValidateObjectMessage5;
      $_GET['indexAction']='add_object';
			$check = false;
    }
  if($check)                                                                    // magnitude
  { $magnitude = "99.9";
    if($objUtil->checkPostKey('magnitude')&&(!(ereg('^([0-9]{1,2})[.,]{0,1}([0-9]{0,1})$',$_POST['magnitude'],$matches))))
    { $entryMessage=LangValidateObjectMessage9;
      $_GET['indexAction']='add_object';
			$check = false;
    }
    elseif($objUtil->checkPostKey('magnitude'))
    { $magnitude=$matches[1].".";
      if($matches[2]!= "")
        $magnitude=$magnitude.$matches[2];
      else
			  $magnitude=magnitude."0";
    }
  }

  if($check)                                                                    // postion angle
  { $posangle = "999";
    if(!$objUtil->checkLimitsInclusive('posangle',0,359))
    { $entryMessage=LangValidateObjectMessage6;
      $_GET['indexAction']='add_object';
			$check = false;
    }
    elseif($objUtil->checkPostKey('posangle'))
      $posangle=$_POST['posangle'];
  }
  if($check)                                                                    // surface brightness
  { $sb = "99.9";
    if($_POST['sb'] && ereg('^([0-9]{1,2})[.,]{0,1}([0-9]{0,1})$', $_POST['sb'], $matches)) 
    { $sb = "" . $matches[1] . ".";
      if($matches[2] != "")
			  $sb = $sb . $matches[2];
      else
        $sb = $sb . "0";
    }
  }
  if($check)                                                                    // check diam1
  { $diam1=0.0;
    if($objUtil->checkPostKey('size_x')&&$objUtil->checkPostKey('size_x_units'))
    { if($objUtil->checkPostKey('size_x_units')=="min")
        $diam1=$objUtil->checkPostKey('size_x')*60.0;
      elseif($objUtil->checkPostKey('size_x_units')=="sec")
        $diam1=$objUtil->checkPostKey('size_x');
      else
      { $entryMessage=LangValidateObjectMessage7;
        $_GET['indexAction']='add_object';
	  		$check = false;
      }
    }
  }
  if($check)                                                                    // check diam2
  { $diam2=0.0;
    if($objUtil->checkPostKey('size_y')&&$objUtil->checkPostKey('size_y_units'))
    { if($objUtil->checkPostKey('size_y_units')=="min")
       $diam2 = $objUtil->checkPostKey('size_y',0)*60.0;
      elseif($objUtil->checkPostKey('size_y_units')=="sec")
        $diam2 = $objUtil->checkPostKey('size_y',0);
      else
      { $entryMessage=LangValidateObjectMessage7;
        $_GET['indexAction']='add_object';
	  		$check = false;
      }
    }
  }
  if($check)                                                                    // fill database
  { $objObject->addDSObject($name, $catalog , ucwords(trim($_POST['number'])), $_POST['type'], $_POST['con'], $ra, $declination, $magnitude, $sb, $diam1, $diam2, $posangle, "", "DeepskyLogUser");
    $admins = $objObserver->getAdministrators();
    while(list($key, $value)=each($admins))
      if ($objObserver->getObserverProperty($value,'email'))
        $adminMails[] = $objObserver->getObserverProperty($value,'email');
    $to=implode(",", $adminMails);
    $subject = LangValidateAccountEmailTitleObject . " " . $name;
    reset($admins);
    $headers="From:".$objObserver->getObserverProperty($admins[0],'email');
    $body=LangValidateAccountEmailTitleObject." ".$name." ". "www.deepskylog.org/index.php?indexAction=detail_object&object=".urlencode($name)." ".
		      LangValidateAccountEmailTitleObjectObserver." ".$objObserver->getObserverProperty($_SESSION['deepskylog_id'],'name')." ".$objObserver->getObserverProperty($_SESSION['deepskylog_id'],'firstname')." www.deepskylog.org/index.php?indexAction=detail_observer&user=".urlencode($_SESSION['deepskylog_id']);
		mail($to, $subject, $body, $headers);
    $_GET['indexAction']='detail_object';
		$_GET['object']=$name;
  }
}
elseif ($_POST['clearfields'])                                                  // pushed clear fields button
  $_GET['indexAction']="add_object";	
?>