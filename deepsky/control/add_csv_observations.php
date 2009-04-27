<?php // add_csv_observations.php - adds observations from a csv file to the database
$_GET['indexAction']='default_action';
if($_FILES['csv']['tmp_name']!="")
  $csvfile=$_FILES['csv']['tmp_name'];
$data_array=file($csvfile); 
for($i=0;$i<count($data_array);$i++ ) 
  $parts_array[$i]=explode(";",$data_array[$i]); 
for($i=0;$i<count($parts_array);$i++)
{ $objects[$i] = htmlentities($parts_array[$i][0]);
  $dates[$i] = $parts_array[$i][2];
  $locations[$i] = htmlentities($parts_array[$i][4]);
  $instruments[$i] = htmlentities($parts_array[$i][5]);
  $filters[$i] = htmlentities($parts_array[$i][7]);
  $eyepieces[$i] = htmlentities($parts_array[$i][6]);
  $lenses[$i] = htmlentities($parts_array[$i][8]);
}
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
  $dates = array_unique($dates);
  $dates = array_values($dates);
  $noDates=array();
  $wrongDates=array();
  $objectsMissing = array();
	$locationsMissing = array();
	$instrumentsMissing = array();
	$filtersMissing = array();
  $eyepiecesMissing = array();
  $lensesMissing = array();
  $errorlist=array();
  // Test if the objects, locations and instruments are available in the database
  for($i=0,$j=0;$i<count($objects);$i++)
  { $objectsquery=$objObject->getExactDSObject(trim($objects[$i]));
    if(!$objectsquery)
    { $objectsMissing[$j++]=trim($objects[$i]);
      $errorlist[]=$i+1;
    }
    else
      $correctedObjects[$i]=$objectsquery;
  }
	// Check for existence of locations
  for($i= 0,$j=0,$temploc='';$i<count($locations);$i++)
    if((!trim($locations[$i]))||($temploc!=trim($locations[$i]))&&($objLocation->getLocationId(trim($locations[$i]),$loggedUser)==-1))
    { $locationsMissing[$j++]=trim($locations[$i]);
      $errorlist[]=$i+1;
    }
	  else
		  $temploc=trim($locations[$i]);
  // Check for existence of instruments
  for($i=0,$j=0,$tempinst='';$i<count($instruments);$i++)
    if((!trim($instruments[$i]))||($objInstrument->getInstrumentId(trim($instruments[$i]),$loggedUser)==-1))
    { $instrumentsMissing[$j++]=trim($instruments[$i]);
		  $errorlist[]=$i+1;
    }
    else
		  $tempinst=$instruments[$i];
  // Check for the existence of the eyepieces
  for($i=0,$j=0;$i<count($eyepieces);$i++)
    if(trim($eyepieces[$i])&&(!($objEyepiece->getEyepieceObserverPropertyFromName(trim($eyepieces[$i]),$loggedUser,'id'))))
    { $eyepiecesMissing[$j++]=trim($eyepieces[$i]);
      $errorlist[]=$i+1;
    }
      // Check for the existence of the filters
  for($i=0,$j=0;$i<count($filters);$i++)
    if(trim($filters[$i])&&(!($objFilter->getFilterObserverPropertyFromName(trim($filters[$i]), $loggedUser,'id'))))
    { $filtersMissing[$j++]=trim($filters[$i]);
      $errorlist[]=$i+1;
    }
      // Check for the existence of the lenses
  for($i=0,$j=0;$i<count($lenses);$i++)
    if(trim($lenses[$i])&&(!($objLens->getLensObserverPropertyFromName(trim($lenses[$i]),$loggedUser,'id'))))
    { $lensesMissing[$j++]=trim($lenses[$i]);
      $errorlist[]=$i+1;
    }
      // Check for the correctness of dates
  for($i=0,$j=0,$k=0;$i<count($dates);$i++)
  { $datepart=sscanf(trim($dates[$i]),"%2d%c%2d%c%4d");
    if((!is_numeric($datepart[0]))||(!is_numeric($datepart[2]))||(!is_numeric($datepart[4]))||(!checkdate($datepart[2],$datepart[0],$datepart[4])))
    { $noDates[$j++]=$dates[$i]; 
      $errorlist[]=$i+1;
    }
    elseif((sprintf("%04d",$datepart[4]).sprintf("%02d",$datepart[2]).sprintf("%02d",$datepart[0]))>date('Ymd')) 
    { $wrongDates[$k++]=trim($dates[$i]);
      $errorlist[]=$i+1;
    }
  }
  // error catching
  if(count($errorlist)>0)
  { $errormessage=LangCSVError1 . "<br />\n";
    if(count($noDates)>0)
    { $errormessage = $errormessage . "<ul>";
      $errormessage = $errormessage .  "<li>".LangCSVError8." : ";
      $errormessage = $errormessage .  "<ul>";
      for ( $i = 0;$i < count($noDates);$i++ )
        $errormessage = $errormessage . "<li>".$noDates[$i]."</li>";
      $errormessage = $errormessage .  "</ul>";
      $errormessage = $errormessage .  "</li>\n";
      $errormessage = $errormessage .  "</ul>";
    }
    if(count($wrongDates)>0)
    { $errormessage = $errormessage . "<ul>";
      $errormessage = $errormessage .  "<li>".LangCSVError9." : ";
      $errormessage = $errormessage .  "<ul>";
      for ( $i = 0;$i < count($wrongDates);$i++ )
        $errormessage = $errormessage . "<li>".$wrongDates[$i]."</li>";
      $errormessage = $errormessage .  "</ul>";
      $errormessage = $errormessage .  "</li>\n";
      $errormessage = $errormessage .  "</ul>";
    }
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
    
    while(list($key,$j)=each($errorlist))
    { $_SESSION['csvImportErrorData']=$parts_array[$j];
    }
    
    $messageLines[] = "<h2>".LangCSVError0."</h2>"."<p />".LangCSVError0."<p />".$errormessage."<p />".LangCSVError10."<a href=\"".$baseURL."index.php?indexAction=add_csv\">".LangCSVError10a."</a>".LangCSVError10b."<a href=\"".$baseURL."errorobjects.csv\">".LangCSVError10c."</a>".LangCSVError10d."<p />".LangCSVMessage4;
    $_GET['indexAction']='message';
  }
  $username=$objObserver->getObserverProperty($loggedUser,'firstname'). " ".$objObserver->getObserverProperty($loggedUser,'name');
  $added=0;
  $double=0;
  for($i=0;$i<count($parts_array);$i++)
  { if(!in_array($i+1,$errorlist))
    { $observername=$objObserver->getObserverProperty(htmlentities(trim($parts_array[$i][1])),'firstname'). " ".$objObserver->getObserverProperty(htmlentities(trim($parts_array[$i][1])),'name');
      if(trim($parts_array[$i][1])==$username)
      { $instrum=$objInstrument->getInstrumentId(htmlentities(trim($parts_array[$i][5])), $loggedUser);
        $locat  =$objLocation->getLocationId(htmlentities(trim($parts_array[$i][4])), $loggedUser);
        $dates  =sscanf(trim($parts_array[$i][2]), "%2d%c%2d%c%4d");
        $date   =sprintf("%04d%02d%02d", $dates[4], $dates[2], $dates[0]);
        if($parts_array[$i][3])
        { $times  =sscanf(trim($parts_array[$i][3]), "%2d%c%2d");
          $time   =sprintf("%02d%02d", $times[0], $times[2]);
        }
        else
          $time="-9999";
        $obsid  =$objObservation->addDSObservation2($correctedObjects[$i],
                                                  $loggedUser,
                                                  $instrum,
                                                  $locat,
                                                  $date,
                                                  $time,
                                                  htmlentities(trim($parts_array[$i][13])),
                                                  htmlentities(trim($parts_array[$i][9])),
                                                  htmlentities(trim($parts_array[$i][10])),
                                                  htmlentities(((trim($parts_array[$i][11])=="")?"0":trim($parts_array[$i][11]))),
                                                  htmlentities(trim($parts_array[$i][12])),
                                                  ((trim($parts_array[$i][6])!="")?$objEyepiece->getEyepieceObserverPropertyFromName(htmlentities(trim($parts_array[$i][6])), $loggedUser,'id'):0),
				                                          ((trim($parts_array[$i][7])!="")?$objFilter->getFilterObserverPropertyFromName(htmlentities(trim($parts_array[$i][7])), $loggedUser,'id'):0),
				                                          ((trim($parts_array[$i][8])!="")?$objLens->getLensObserverPropertyFromName(htmlentities(trim($parts_array[$i][8])), $loggedUser,'id'):0)
				                                          );
      if($obsid)
        $added++;
      else
        $double++;
      }
      unset($_SESSION['QobsParams']);
    }
  }
  $objPresentations->alertMessage($added.LangCSVMessage8.count($errorlist).LangCSVMessage9.$double.LangCSVMessage10);
}
?>
