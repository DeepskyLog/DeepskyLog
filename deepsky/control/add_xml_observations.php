<?php
// add_xml_observations.php
// adds observations from an OpenAstronomyLog xml file to the database
  if($_FILES['xml']['tmp_name']!="") {
    $xmlfile=$_FILES['xml']['tmp_name'];
  }

  // Make a DomDocument from the file.
  $dom = new DomDocument();
  $xmlfile = realpath($xmlfile);

  //Load the xml document in the DOMDocument object
  $dom->Load($xmlfile);

  $searchNode = $dom->getElementsByTagName( "observations" ); 
  $version = $searchNode->item(0)->getAttribute("version");

  if ($version != "1.7") { // && $version != "2.0") {
    throw new Exception(LangXMLError1);
  }

  // Use the correct schema definition to check the xml file. Does not work with 2.0 yet (as 
  // there is no final scheme for 2.0 yet)
  $xmlschema = str_replace(' ', '/', $searchNode->item(0)->getAttribute("xsi:schemaLocation")); 

  // TODO : Remove : Only for offline testing
  $xmlschema = $baseURL . "xml/comast17.xsd"; 
  
  //Validate the XML file against the schema
  if ($dom->schemaValidate($xmlschema)) {
    // The XML file is valid. Let's start reading in the file.
    // At this moment only 1.7 files!

    // Check the observers -> In OpenAstronomyLog 2.0 the deepskylog_id is also added
    $searchNode = $dom->getElementsByTagName( "observers" ); 
    $observer = $searchNode->item(0)->getElementsByTagName( "observer" ); 
    $id = "";
    foreach( $observer as $observer )
    {
      // Get the id and the name of the observers in the comast file
      $comastid = $observer->getAttribute("id");
      $name = $observer->getElementsByTagName( "name" )->item(0)->nodeValue;
      $surname = $observer->getElementsByTagName( "surname" )->item(0)->nodeValue;

      // Get the name of the observer which is logged in in DeepskyLog
      $deepskylog_username=$objObserver->getObserverProperty($_SESSION['deepskylog_id'],'firstname'). " ".$objObserver->getObserverProperty($_SESSION['deepskylog_id'],'name');
      
      if ($deepskylog_username == $name . " " . $surname) {
        $id = $comastid;
      }
    }
    if ($id == "") {
      $errormessage = LangXMLError2 . $deepskylog_username . LangXMLError2a;
      throw new Exception($errormessage);
    }
    
    $targets = $dom->getElementsByTagName( "targets" ); 
    $target = $targets->item(0)->getElementsByTagName( "target" ); 
    
    foreach( $target as $target )
    {
      $targetname = $target->getElementsByTagName( "name" )->item(0)->nodeValue;
      $aliases = $target->getElementsByTagName( "alias" );
      
      $aliasesArray = Array();
      $cnt = 0;
      foreach ($aliases as $aliases) {
        //print "Alias " . $cnt . " ";
        //print $aliases->nodeValue . "<br />";
        $aliasesArray["alias".$cnt] = $aliases->nodeValue; 
        $cnt = $cnt + 1;
      }
      print_r($aliasesArray);
//      print "TEST : " . $targetname . "<br />";
    }
    
    // Check if there are observations for the given observer
    $searchNode = $dom->getElementsByTagName( "observations" ); 
    $observation = $searchNode->item(0)->getElementsByTagName( "observation" ); 
    foreach( $observation as $observation )
    {
      $observerid = $observation->getElementsByTagName( "observer" )->item(0)->nodeValue;
      if ($observerid == $id) {
        // Check if the observation already exists in DeepskyLog (target and begin should tell this)
        print "Date and time : " . $observation->getElementsByTagName( "begin" )->item(0)->nodeValue . "<br />";
        print "Target : " . $observation->getElementsByTagName( "target" )->item(0)->nodeValue . "<br />";

//        print $target->getElementById($observation->getElementsByTagName( "target" )->item(0)->nodeValue); //->getElementsByTagName("name")->item(0)->nodeValue;
//        print $targets->getElementById("_155092")->nodeValue;
//        print "ID : " . $observation->getAttribute("id") . "<br />";
        
        // TODO : What if I change the eyepiece? What if I change the coordinates of an object? 
      }
    }
    
  } else {
    throw new Exception(LangXMLError3);
  }
  
exit;

// Duplicated code from add_csv_observations
for($i=0;$i<count($data_array);$i++ ) 
  $parts_array[$i]=explode(";",$data_array[$i]); 
for ( $i = 1; $i < count($parts_array); $i++)
{ $objects[$i] = $parts_array[$i][0];
  $locations[$i] = $parts_array[$i][4];
  $instruments[$i] = $parts_array[$i][5];
  $filters[$i] = $parts_array[$i][7];
  $eyepieces[$i] = $parts_array[$i][6];
  $lenses[$i] = $parts_array[$i][8];
}
//$objects = array_unique($objects);
// JV 20060224 add check to see if $objects contains data or not
// -> show error page
if(!is_array($objects))
 throw new Exception(LangInvalidCSVfile);
else
{ $objects = array_values($objects);
  $locations = array_unique($locations);
  $locations = array_values($locations);
  $instruments = array_unique($instruments);
  $instruments = array_values($instruments);
  $filters = array_unique($filters);
  $filters = array_values($filters);
  $eyepieces = array_unique($eyepieces);
  $eyepieces = array_values($eyepieces);
  $lenses = array_unique($lenses);
  $lenses = array_values($lenses);
	$objectsMissing = array();
	$locationsMissing = array();
	$instrumentsMissing = array();
	$filtersMissing = array();
  $eyepiecesMissing = array();
  $lensesMissing = array();
  // Test if the objects, locations and instruments are available in the database
  for($i=0,$j=0;$i<count($objects);$i++)
  { $objectsquery=$objObject->getExactDSObject($objects[$i]);
    if(!$objectsquery)
      $objectsMissing[$j++]=$objects[$i];
    else
      $correctedObjects[]=$objectsquery;
  }
	// Check for existence of locations
  for($i= 0,$j=0,$temploc='';$i<count($locations);$i++)
    if((!$locations[$i])||($temploc!=$locations[$i])&&($objLocation->getLocationId($locations[$i],$_SESSION['deepskylog_id'])==-1))
	    $locationsMissing[$j++]=$locations[$i];
		else
		  $temploc=$locations[$i];
  // Check for existence of instruments
  for($i=0,$j=0,$tempinst='';$i<count($instruments);$i++)
    if((!$instruments[$i])||($objInstrument->getInstrumentId($instruments[$i],$_SESSION['deepskylog_id'])==-1))
      $instrumentsMissing[$j++]=$instruments[$i];
		else
		  $tempinst=$instruments[$i];
  // Check for the existence of the eyepieces
  for($i=0,$j=0;$i<count($eyepieces);$i++)
    if($eyepieces[$i]&&(!($objEyepiece->getEyepieceObserverPropertyFromName($eyepieces[$i],$loggedUser,'id'))))
      $eyepiecesMissing[$j++]=$eyepieces[$i];
  // Check for the existence of the filters
  for($i=0,$j=0;$i<count($filters);$i++)
    if($filters[$i]&&(!($objFilter->getFilterObserverPropertyFromName($filters[$i], $_SESSION['deepskylog_id'],'id'))))
      $filtersMissing[$j++]=$filters[$i];
  // Check for the existence of the eyepieces
  for($i=0,$j=0;$i<count($lenses);$i++)
    if($lenses[$i]&&($objLens->getLensId($lenses[$i],$_SESSION['deepskylog_id'])==-1))
      $lensesMissing[$j++] = $lenses[$i];
// error catching
  if((count($objectsMissing)>0)||(count($locationsMissing)>0)||(count($instrumentsMissing)>0)||(count($eyepiecesMissing)>0)||(count($filtersMissing)>0)||(count($lensesMissing)>0))
  { $errormessage=LangCSVError1 . "<br />\n";
    if(count($objectsMissing)>0)
    { $errormessage = $errormessage . "<ul>";
      $errormessage = $errormessage .  "<li>".LangCSVError2." : ";
      $errormessage = $errormessage .  "<ul>";
      for ( $i = 0;$i < count($objectsMissing);$i++ )
        $errormessage = $errormessage . "<li>".$objectsMissing[$i]."</li>";
      $errormessage = $errormessage .  "</ul>";
      $errormessage = $errormessage .  "</li>\n";
      $errormessage = $errormessage .  "</ul>";
    }
    if(count($locationsMissing)>0)
    { $errormessage = $errormessage . "<ul>";
      $errormessage = $errormessage .  "<li>".LangCSVError3." : ";
      $errormessage = $errormessage . "<ul>";
      for ( $i = 0;$i < count($locationsMissing);$i++ )
        $errormessage = $errormessage . "<li>".$locationsMissing[$i]."</li>";
      $errormessage = $errormessage . "</ul>";
      $errormessage = $errormessage .  "</li>\n";
      $errormessage = $errormessage .  "</ul>";
    }
    if(count($instrumentsMissing)>0)
    { $errormessage = $errormessage . "<ul>";
      $errormessage = $errormessage . "<li>".LangCSVError4." : ";
      $errormessage = $errormessage . "<ul>";
      for ( $i = 0;$i < count($instrumentsMissing);$i++ )
        $errormessage = $errormessage . "<li>".$instrumentsMissing[$i]."</li>";
      $errormessage = $errormessage . "</ul>";
      $errormessage = $errormessage . "</li>\n";
      $errormessage = $errormessage . "</ul>";
    }
    if(count($filtersMissing)>0)
    { $errormessage = $errormessage . "<ul>";
      $errormessage = $errormessage .  "<li>".LangCSVError5." : ";
      $errormessage = $errormessage . "<ul>";
      for ( $i = 0;$i < count($filtersMissing);$i++ )
        $errormessage = $errormessage . "<li>".$filtersMissing[$i]."</li>";
      $errormessage = $errormessage . "</ul>";
      $errormessage = $errormessage .  "</li>\n";
      $errormessage = $errormessage .  "</ul>";
    }
    if (count($eyepiecesMissing) > 0)
    { $errormessage = $errormessage . "<ul>";
      $errormessage = $errormessage .  "<li>".LangCSVError6." : ";
      $errormessage = $errormessage . "<ul>";
      for ( $i = 0;$i < count($eyepiecesMissing);$i++ )
        $errormessage = $errormessage . "<li>".$eyepiecesMissing[$i]."</li>";
      $errormessage = $errormessage . "</ul>";
      $errormessage = $errormessage .  "</li>\n";
      $errormessage = $errormessage .  "</ul>";
    }
    if (count($lensesMissing) > 0)
    { $errormessage = $errormessage . "<ul>";
      $errormessage = $errormessage .  "<li>".LangCSVError7." : ";
      $errormessage = $errormessage . "<ul>";
      for ( $i = 0;$i < count($lensesMissing);$i++ )
        $errormessage = $errormessage . "<li>".$lensesMissing[$i]."</li>";
      $errormessage = $errormessage . "</ul>";
      $errormessage = $errormessage .  "</li>\n";
      $errormessage = $errormessage .  "</ul>";
    }
    throw new Exception($errormessage);
  }
  else
  { $username=$objObserver->getObserverProperty($_SESSION['deepskylog_id'],'firstname'). " ".$objObserver->getObserverProperty($_SESSION['deepskylog_id'],'name');
    for($i=1;$i<count($parts_array);$i++)
    { $observername = $objObserver->getObserverProperty($parts_array[$i][1],'firstname'). " ".$objObserver->getObserverProperty($parts_array[$i][1],'name');
      if($parts_array[$i][1]==$username)
      { $instrum = $objInstrument->getInstrumentId($parts_array[$i][5], $_SESSION['deepskylog_id']);
        $locat = $objLocation->getLocationId($parts_array[$i][4], $_SESSION['deepskylog_id']);
        $dates = sscanf($parts_array[$i][2], "%2d%c%2d%c%4d");
        $date = sprintf("%04d%02d%02d", $dates[4], $dates[2], $dates[0]);
        $times = sscanf($parts_array[$i][3], "%2d%c%2d");
        $time = sprintf("%02d%02d", $times[0], $times[2]);
        if ($parts_array[$i][11] == "")
          $parts_array[$i][11] = "0";
        $obsid=$objObservation->addDSObservation($correctedObjects[$i-1],$_SESSION['deepskylog_id'],$instrum,$locat,$date,$time,htmlentities($parts_array[$i][13]),$parts_array[$i][9],$parts_array[$i][10],$parts_array[$i][11],$parts_array[$i][12]);
				if ($parts_array[$i][6] != "")
				  $objObservation->setDsObservationProperty($obsid,'eyepieceid', $objEyepiece->getEyepieceObserverPropertyFromName($parts_array[$i][6], $_SESSION['deepskylog_id'],'id'));
				if ($parts_array[$i][7] != "")
					$objObservation->setDsObservationProperty($obsid,'filterid', $objFilter->getFilterObserverPropertyFromName($parts_array[$i][7], $_SESSION['deepskylog_id'],'id'));
				if ($parts_array[$i][8] != "")
					$objObservation->setDsObservationProperty($obsid,'lensid', $objLens->getLensId($parts_array[$i][8], $_SESSION['deepskylog_id']));
      }
      unset($_SESSION['QobsParams']);
    }
    // upload successful
    $_GET['indexAction']='default_action';
  }
}
?>
