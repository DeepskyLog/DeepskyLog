<?php // The location class collects all functions needed to enter, retrieve and adapt location data from the database.
interface iLocations
{ public  function addLocation($name, $longitude, $latitude, $region, $country, $timezone);                            // adds a new location to the database. The name, longitude, latitude, region and country should be given as parameters. 
  public  function getAllLocationsIds($id);                                                                            // returns a list with all id's which have the same name as the name of the given id
  public  function getCountries();                                                                                     // returns all possible countries
  public  function getDatabaseCountries();                                                                             // returns all countries for which the database of the locations is available
  public  function getLocationId($name, $observer);                                                                    // returns the id for this location
  public  function getLocationPropertyFromId($id,$property,$defaultValue='');
  public  function getLocations();                                                                                     // returns an array with all locations
  public  function getLocationsFromDatabase($name, $country);                                                          // returns an array with all information about the location where the name equals the given name in the given country (given the country string - e.g. Belgium).
  public  function getLocationTimeDifference($id);
  public  function getLocationUsedFromId($id);                                                                         // returns the number of times the location is used in observations
  public  function getSortedLocations($sort,$observer="");                                                             // returns an array with the ids of all locations, sorted by the column specified in $sort
  public  function getSortedLocationsList($sort, $observer = "");                                                      // returns an array with the ids of all locations, sorted by the column specified in $sort. Locations withthe same name are adapted by adding the province.
  public  function setLocationProperty($id,$property,$propertyValue); 
  public  function showLocationsObserver();                                                 // sets the property to the specified value for the given location  public  function validateDeleteLocation();                                                                           // deletes teh location of the list with locations
  public  function validateDeleteLocation();
  public  function validateSaveLocation();
}
class Locations
{ public function addLocation($name, $longitude, $latitude, $region, $country, $timezone)                             // addLocation adds a new location to the database. The name, longitude, latitude, region and country should be given as parameters. 
  { global $objDatabase;
  	$objDatabase->execSQL("INSERT INTO locations (name, longitude, latitude, region, country, timezone) VALUES (\"$name\", \"$longitude\", \"$latitude\", \"$region\", \"$country\", \"$timezone\")");
    return $objDatabase->selectSingleValue("SELECT id FROM locations ORDER BY id DESC LIMIT 1",'id');
  }
  public  function getAllLocationsIds($id)                                                   // returns a list with all id's which have the same name as the name of the given id
  { global $objDatabase;
    return $objDatabase->selectSingleArray("SELECT id FROM locations WHERE name = \"".$objDatabase->selectSingleValue("SELECT name FROM locations WHERE id = \"".$id."\"",'name')."\"",'id');
  }
  public  function getCountries() // getCountries returns all possible countries
  { global $instDir;
 	  $filename=$instDir."lib/setup/locations/countries.txt";
    $fh=fopen($filename,"r") or die("Could not open countries file"+$filename);
    while(!feof($fh))
    { $data=fgets($fh);
      $vars=explode(" - ",$data);
      $a=sscanf($vars[1],"(%c%c)");
      $countries[$a[0].$a[1]]=ucfirst(strtolower($vars[0]));
    }
    fclose($fh);
    return $countries;
  }
  public function getDatabaseCountries()                                                                                 // returns all countries for which the database of the locations is available
  { global $instDir;
    $filename=$instDir."lib/setup/locations/countries.txt";
    $fh = fopen($filename,"r") or die("Could not open countries file");
    while(!feof($fh))
    { $data=fgets($fh);
      $vars=explode(" - ",$data);
      $a=sscanf($vars[1],"(%c%c)");
      $countriesConversion[$a[0].$a[1]]=ucfirst(strtolower($vars[0]));
    }
    fclose($fh);
    $maindir=$instDir."lib/setup/locations/" ;
    $mydir=opendir($maindir) ;
    $exclude=array("index.php",".","..");
    $countries=array();
    while($fn=readdir($mydir))
    { if(in_array($fn,$exclude)) 
        continue;
      $code=explode(".",$fn);
      if($code[1]=="ast")
        $countries[] = $countriesConversion[strtoupper($code[0])];
    }
    closedir($mydir);
    return $countries;
  }
  public  function getLocationId($name, $observer)                                              // returns the id for this location
  { global $objDatabase; 
    return $objDatabase->selectSingleValue("SELECT id FROM locations where name=\"".htmlentities($name)."\" and observer=\"".$observer."\"",'id',-1);
  }
  public  function getLocationPropertyFromId($id,$property,$defaultValue='')
  { global $objDatabase; return $objDatabase->selectSingleValue("SELECT ".$property." FROM locations WHERE id = \"".$id."\"",$property,$defaultValue);
  }
  public  function getLocations()                                                               // returns an array with all locations
  { global $objDatabase;
    return $objDatabase->selectSingleArray("SELECT id FROM locations",'id');
  }
  public  function getLocationsFromDatabase($name, $country)                                    // returns an array with all information about the location where the name equals the given name in the given country (given the country string - e.g. Belgium).
  { global $objDatabase, $instDir;
	  $locations=array();
    $filename=$instDir."lib/setup/locations/countries.txt";
    $fh=fopen($filename,"r") or die("Could not open countries file");
    while(!feof($fh))
    { $data=fgets($fh);
      $vars=explode(" - ",$data);
      $a=sscanf($vars[1],"(%c%c)");
      $countriesConversion[ucfirst(strtolower($vars[0]))]=$a[0].$a[1];
    }
    fclose($fh);
    $filename=$instDir."lib/setup/locations/".strtolower($countriesConversion[$country]).".ast";
    $fh=fopen($filename, "r") or die("Could not read file");
    while(!feof($fh))
    { $data=fgets($fh);
      $vars=explode("\t", $data);
      if (strtolower($vars[0]) == strtolower($name))
        $locations[] = str_replace("\n","",$data);
    }
    return $locations;
  }
  public  function getLocationTimeDifference($id)
  { global $objDatabase;
		$timezone=$this->getLocationPropertyFromId($id,'timezone');
		$dateTimeZone = new DateTimeZone($timezone);
		$timedifference = $dateTimeZone->getOffset();
		$timedifference = $timedifference / 3600.0;
		$timedifferenceminutes = ($timedifference - (int) $timedifference) * 60;
  }  
  public  function getLocationUsedFromId($id)                                                   // returns the number of times the location is used in observations
  { global $objDatabase; 
    return $objDatabase->selectSingleValue("SELECT count(id) as ObsCnt FROM observations WHERE locationid=\"".$id."\"",'ObsCnt',0)
         + $objDatabase->selectSingleValue("SELECT count(id) as ObsCnt FROM cometobservations WHERE locationid=\"".$id."\"",'ObsCnt',0);
	}
  public  function getSortedLocations($sort,$observer="")                                       // returns an array with the ids of all locations, sorted by the column specified in $sort
  { global $objDatabase; 
    return $objDatabase->selectSingleArray("SELECT id, name FROM locations ".($observer?"WHERE observer LIKE \"".$observer."\"":" GROUP BY name")." ORDER BY ".$sort.", name",'id');  
  } 
  public  function getSortedLocationsList($sort, $observer = "")                             // returns an array with the ids of all locations, sorted by the column specified in $sort. Locations withthe same name are adapted by adding the province.
  { global $objDatabase; 
    $sites=$objDatabase->selectRecordsetArray("SELECT id, name FROM locations ".($observer?"WHERE observer LIKE \"".$observer."\"":" GROUP BY name")." ORDER BY ".$sort.",name",'id');  
    $previous = "fdgsdg";
    for($i=0;$i<count($sites);$i++)
    { $adapt[$i] = 0;
      if($sites[$i]['name'] == $previous)
      { $adapt[$i]=1;
        $adapt[$i-1]=1;
      }
      $previous=$sites[$i]['name'];
    }
    for($i= 0;$i<count($sites);$i++)
    { if($adapt[$i])
      { $new_sites[$i][0] = $sites[$i]['id'];
        $new_sites[$i][1] = $sites[$i]['name']." (".$this->getLocationPropertyFromId($sites[$i]['id'],'region').")";
      }
      else
      { $new_sites[$i][0] = $sites[$i]['id'];
        $new_sites[$i][1] = $sites[$i]['name'];
      }
    }
    return $new_sites;
  }
  public  function setLocationProperty($id,$property,$propertyValue)                            // sets the property to the specified value for the given location
  { global $objDatabase;
    return $objDatabase->execSQL("UPDATE locations SET ".$property." = \"".$propertyValue."\" WHERE id = \"".$id."\"");
  }
  public  function showLocationsObserver()
  { global $baseURL,$loggedUser,$objObserver,$objUtil,$objLocation,$objPresentations,$loggedUserName,$objContrast,$locationid,$sites;
    $sort=$objUtil->checkGetKey('sort','name');
		if($sites!=null)
		{ $orig_previous=$objUtil->checkGetKey('previous','');
		  if((isset($_GET['sort']))&&($orig_previous==$_GET['sort']))                   // reverse sort when pushed twice
		  { if ($_GET['sort'] == "name")
		      $insts = array_reverse($insts, true);
		    else
		    { krsort($insts);
		      reset($insts);
		    }
		    $previous = "";
		  }
		  else
		    $previous = $sort;
		  echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
		  echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_site\" />";
		  echo "<input type=\"hidden\" name=\"adaptStandardLocation\" value=\"1\" />";
		  echo "<table width=\"100%\">";
		  echo "<tr class=\"type3\">";
		  echo "<td><a href=\"".$baseURL."index.php?indexAction=add_site&amp;sort=name&amp;previous=$previous\">".LangViewLocationLocation."</a></td>";
		  echo "<td><a href=\"".$baseURL."index.php?indexAction=add_site&amp;sort=region&amp;previous=$previous\">".LangViewLocationProvince."</a></td>";
		  echo "<td><a href=\"".$baseURL."index.php?indexAction=add_site&amp;sort=country&amp;previous=$previous\">".LangViewLocationCountry."</a></td>";
		  echo "<td class=\"centered\"><a href=\"".$baseURL."index.php?indexAction=add_site&amp;sort=longitude&amp;previous=$previous\">".LangViewLocationLongitude."</a></td>";
		  echo "<td class=\"centered\"><a href=\"".$baseURL."index.php?indexAction=add_site&amp;sort=latitude&amp;previous=$previous\">".LangViewLocationLatitude."</a></td>";
		  echo "<td><a href=\"".$baseURL."index.php?indexAction=add_site&amp;sort=timezone&amp;previous=$previous\">".LangAddSiteField6."</a></td>";
		  echo "<td class=\"centered\"><a href=\"".$baseURL."index.php?indexAction=add_site&amp;sort=limitingMagnitude&amp;previous=$previous\">".LangViewLocationLimMag."</a></td>";
		  echo "<td class=\"centered\"><a href=\"".$baseURL."index.php?indexAction=add_site&amp;sort=skyBackground&amp;previous=$previous\">".LangViewLocationSB."</a></td>";
		  echo "<td class=\"centered\">".LangViewLocationStd."</td>";
		  echo "<td></td>";
		  echo "</tr>";
		  $count = 0;
		  while(list($key,$value)=each($sites))
		  { $sitename=stripslashes($objLocation->getLocationPropertyFromId($value,'name'));
		    $region=stripslashes($objLocation->getLocationPropertyFromId($value,'region'));
		    $country=$objLocation->getLocationPropertyFromId($value,'country');
		    if($objLocation->getLocationPropertyFromId($value,'longitude')>0)
		      $longitude = "&nbsp;" . $objPresentations->decToString($objLocation->getLocationPropertyFromId($value,'longitude'));
		    else
		      $longitude = $objPresentations->decToString($objLocation->getLocationPropertyFromId($value,'longitude'));
		    if($objLocation->getLocationPropertyFromId($value,'latitude')>0)
		      $latitude = "&nbsp;" . $objPresentations->decToString($objLocation->getLocationPropertyFromId($value,'latitude'));
		    else
		      $latitude = $objPresentations->decToString($objLocation->getLocationPropertyFromId($value,'latitude'));
		    $timezone = $objLocation->getLocationPropertyFromId($value,'timezone');
		    $observer = $objLocation->getLocationPropertyFromId($value,'observer');
		    $limmag = $objLocation->getLocationPropertyFromId($value,'limitingMagnitude');
		    $sb = $objLocation->getLocationPropertyFromId($value,'skyBackground');
		    if(($limmag<-900)&&($sb>0))
		      $limmag = sprintf("%.1f", $objContrast->calculateLimitingMagnitudeFromSkyBackground($sb));
		    elseif(($limmag<-900)&&($sb<-900)) 
		    { $limmag="&nbsp;";
		      $sb="&nbsp;";
		    } 
		    else
		      $sb=sprintf("%.1f", $objContrast->calculateSkyBackgroundFromLimitingMagnitude($limmag));
		    if($value!="1")
		    { echo "<tr class=\"type".(2-($count%2))."\">";
		      echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_site&amp;location=".urlencode($value)."\">".$sitename."</a></td>";
		      echo "<td>".$region."</td>";
		      echo "<td>".$country."</td>";
		      echo "<td class=\"centered\">".$longitude."</td>";
		      echo "<td class=\"centered\">".$latitude."</td>";
		      echo "<td>".$timezone."</td>";
		      echo "<td class=\"centered\">".$limmag."</td>";
		      echo "<td class=\"centered\">".$sb."</td>";
		      echo "<td class=\"centered\"><input type=\"radio\" name=\"stdlocation\" value=\"". $value ."\"".(($value==$objObserver->getObserverProperty($loggedUser,'stdlocation'))?" checked ":"")." onclick=\"submit();\" />&nbsp;<br /></td>";
					echo "<td>";
		      if(!($obsCnt=$objLocation->getLocationUsedFromId($value)))
		        echo "<a href=\"".$baseURL."index.php?indexAction=validate_delete_location&amp;locationid=".urlencode($value)."\">".LangRemove."</a>";
		      else
		        echo "<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;observer=".$loggedUser."&amp;site=".$value."&amp;exactinstrumentlocation=true\">".$obsCnt.' '.LangGeneralObservations."</a>";
		      echo "</td>";
					echo "</tr>";
		      $count++;
		    }
		  }
		  echo "</table>";
		  echo "</form>";
		  echo "<hr />";
		}  	
  }
  public  function validateDeleteLocation()
  { global $objUtil, $objDatabase;
    if(($locationid=$objUtil->checkGetKey('locationid'))
    && ($objUtil->checkAdminOrUserID($this->getLocationPropertyFromId($locationid,'observer')))
    && (!($this->getLocationUsedFromId($locationid))))
    { $objDatabase->execSQL("DELETE FROM locations WHERE id=\"".$locationid."\"");
      return LangValidateLocationMessage3;
    }
  }
  public  function validateSaveLocation()
	{ global $objUtil, $objDatabase, $objObserver,$loggedUser;  
	  if(($objUtil->checkPostKey('adaptStandardLocation')==1)
    &&  $objUtil->checkUserID($this->getLocationPropertyFromId($objUtil->checkPostKey('stdlocation'),'observer')))
    { $objObserver->setObserverProperty($loggedUser,'stdlocation', $_POST['stdlocation']);
    } 
    if($objUtil->checkPostKey('sitename')
    && $objUtil->checkPostKey('region')
    && $objUtil->checkPostKey('country')
    && $objUtil->checkPostKey('timezone'))
    { $latitude  = $objUtil->checkPostKey('latitude',0) + $objUtil->checkPostKey('latitudemin',0) / 60.0;
      $longitude = $objUtil->checkPostKey('longitude',0) + $objUtil->checkPostKey('longitudemin',0) / 60.0;
      $timezone  = $_POST['timezone'];
      if($objUtil->checkPostKey('add'))
      { $id = $this->addLocation($_POST['sitename'], $longitude, $latitude, $_POST['region'], $_POST['country'], $timezone);
        if (array_key_exists('lm', $_POST) && $_POST['lm'])
        { $this->setLocationProperty($id, 'limitingMagnitude', $_POST['lm']);
          $this->setLocationProperty($id, 'skyBackground', -999);
        } 
        elseif(array_key_exists('sb', $_POST) && $_POST['sb'])
        { $this->setLocationProperty($id, 'skyBackground', $_POST['sb']);
          $this->setLocationProperty($id, 'limitingMagnitude', -999);
        } 
        else
        { $this->setLocationProperty($id, 'skyBackground', -999);
          $this->setLocationProperty($id, 'limitingMagnitude', -999);
    		}
    		$this->setLocationProperty($id, 'observer', $loggedUser);
        return LangValidateSiteMessage2;
      }
      if($objUtil->checkPostKey('change')
      && $objUtil->checkAdminOrUserID($this->getLocationPropertyFromId($objUtil->checkPostKey('id'),'observer')))
      { $this->setLocationProperty($_POST['id'], 'name',      $_POST['sitename']);
        $this->setLocationProperty($_POST['id'], 'region',    $_POST['region']);
        $this->setLocationProperty($_POST['id'], 'country',   $_POST['country']);
        $this->setLocationProperty($_POST['id'], 'longitude', $longitude);
        $this->setLocationProperty($_POST['id'], 'latitude',  $latitude);
        $this->setLocationProperty($_POST['id'], 'timezone',  $timezone);
        //$this->setLocationProperty($_POST['id'], 'observer',  $loggedUser);
        if($objUtil->checkPostKey('lm'))
        { $this->setLocationProperty($_POST['id'], 'limitingMagnitude', $_POST['lm']);
          $this->setLocationProperty($_POST['id'], 'skyBackground', -999);
        } 
        elseif($objUtil->checkPostKey('sb'))
        { $this->setLocationProperty($_POST['id'], 'skyBackground', $_POST['sb']);
          $this->setLocationProperty($_POST['id'], 'limitingMagnitude', -999);
        } 
        else
        { $this->setLocationProperty($_POST['id'], 'skyBackground', -999);
          $this->setLocationProperty($_POST['id'], 'limitingMagnitude', -999);
    		}
        return LangValidateSiteMessage5;
      }
    }
  }	
}
$objLocation=new Locations;
?>
