<?php
// add_csv_observations.php
// adds observations from a csv file to the database
if($_FILES['csv']['tmp_name']!="")
  $csvfile=$_FILES['csv']['tmp_name'];
$data_array=file($csvfile); 
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
  { $username=$objObserver->getFirstname($_SESSION['deepskylog_id']). " ".$objObserver->getObserverName($_SESSION['deepskylog_id']);
    for($i=1;$i<count($parts_array);$i++)
    { $observername = $objObserver->getFirstname($parts_array[$i][1]). " ".$objObserver->getObserverName($parts_array[$i][1]);
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
				  $objObservation->setEyepieceId($obsid, $objEyepiece->getEyepieceObserverPropertyFromName($parts_array[$i][6], $_SESSION['deepskylog_id'],'id'));
				if ($parts_array[$i][7] != "")
					$objObservation->setFilterId($obsid, $objFilter->getFilterObserverPropertyFromName($parts_array[$i][7], $_SESSION['deepskylog_id'],'id'));
				if ($parts_array[$i][8] != "")
					$objObservation->setLensId($obsid, $objLens->getLensId($parts_array[$i][8], $_SESSION['deepskylog_id']));
      }
      unset($_SESSION['QobsParams']);
    }
    // upload successful
    $_GET['indexAction']='default_action';
  }
}
?>
