<?php
// The observations class collects all functions needed to enter, retrieve and
// adapt observation data from the database.

include_once "database.php";

class Observations
{
  // addObservation adds a new observation to the database. The name, observerid,
  // instrumentid, locationid, date, time, description, seeing and limiting
  // magnitude should be given as parameters. The id of the latest observation is returned.
  // If the time and date are given in local time, you should execute setLocalDateAndTime after
  // inserting the observation!
  function addDSObservation($objectname, $observerid, $instrumentid, $locationid, $date, $time, $description, $seeing, $limmag, $visibility, $language)
  { if (!$_SESSION['lang'])
      $_SESSION['lang'] = "English";
    if ($seeing == "-1" || $seeing == "")
      $seeing = "NULL";
    if ($limmag == "")
      $limmag = "NULL";
    else
    { if (ereg('([0-9]{1})[.,]([0-9]{1})', $limmag, $matches))                  // limiting magnitude like X.X or X,X with X a number between 0 and 9
        $limmag=$matches[1].".".$matches[2];                                    // valid limiting magnitude // save current magnitude limit
      $limmag="$limmag";
    }
    $description = html_entity_decode($description, ENT_COMPAT, "ISO-8859-15");
    $description = preg_replace("/(\")/", "", $description);
    $description = preg_replace("/;/", ",", $description);
    $GLOBALS['objDatabase']->execSQL("INSERT INTO observations (objectname, observerid, instrumentid, locationid, date, time, description, seeing, limmag, visibility, language) " .
	                                   "VALUES (\"$objectname\", \"$observerid\", \"$instrumentid\", \"$locationid\", \"$date\", \"$time\", \"$description\", $seeing, $limmag, $visibility, \"$language\")");
    return $GLOBALS['objDatabase']->selectSingleValue("SELECT id FROM observations ORDER BY id DESC LIMIT 1",'id');
  }
  function deleteDSObservation($id)                                             // deleteObservation removes the observation with id = $id
  { $GLOBALS['objDatabase']->execSQL("DELETE FROM observations WHERE id=\"$id\"");
  }
  function getDsObservationLocalDate($id)                                       // getLocalDate returns the date of the given observation in local time
  { $run=$GLOBALS['objDatabase']->selectRecordset("SELECT date, time, locationid FROM observations WHERE id = \"".$id."\"");
    if($get=mysql_fetch_object($run))
    { $date = $get->date;
      $time = $get->time;
      $loc = $get->locationid;
      if($time >= 0)
      { $date = sscanf($get->date, "%4d%2d%2d");
        $timezone = $GLOBALS['objLocation']->getTimezone($get->locationid);
        $dateTimeZone = new DateTimeZone($timezone);
        $datestr =  sprintf("%02d", $date[1]) . "/" . sprintf("%02d", $date[2]) . "/" . $date[0];
        $dateTime = new DateTime($datestr, $dateTimeZone);
        // Geeft tijdsverschil terug in seconden
        $timedifference = $dateTimeZone->getOffset($dateTime);
        $timedifference = $timedifference / 3600.0;
        $jd = cal_to_jd(CAL_GREGORIAN, $date[1], $date[2], $date[0]);
        $time = sscanf(sprintf("%04d", $time), "%2d%2d");
        $hours = $time[0] + (int)$timedifference;
        $minutes = $time[1];
        // We are converting from UT to local time -> we should add the time difference!
        $timedifferenceminutes = ($timedifference - (int)$timedifference) * 60;
        $minutes = $minutes + $timedifferenceminutes;
        if ($minutes < 0)
        { $hours = $hours - 1;
          $minutes = $minutes + 60;
        }
        else if ($minutes > 60)
        { $hours = $hours + 1;
          $minutes = $minutes - 60;
        }
        if ($hours < 0)
        { $hours = $hours + 24;
          $jd = $jd - 1;
        }
        if ($hours >= 24)
        { $hours = $hours - 24;
          $jd = $jd + 1;
        }
        $dte = JDToGregorian($jd);
        sscanf($dte, "%2d/%2d/%4d", $month, $day, $year);
        $date = sprintf("%d%02d%02d", $year, $month, $day);
      }
    return $date;
    }
  }
  function getAllInfoDsObservation($id)                                         // getAllInfo returns all information of an observation
  { $get = mysql_fetch_object($GLOBALS['objDatabase']->selectRecordset("SELECT * FROM observations WHERE id=\"$id\""));
    $ob["name"] = $get->objectname;
    $ob["observer"] = $get->observerid;
    $ob["instrument"] = $get->instrumentid;
    $ob["location"] = $get->locationid;
    $ob["date"] = $get->date;
    $ob["time"] = $get->time;
    $ob["description"] = $get->description;
    $ob["seeing"] = $get->seeing;
    $ob["limmag"] = $get->limmag;
    $ob["visibility"] = $get->visibility;
    $ob["localdate"] = $this->getDsObservationLocalDate($id);
    $ob["localtime"] = $this->getDsObservationLocalTime($id);
    $ob["language"] = $this->getDsObservationLanguage($id);
    $ob["eyepiece"] = $get->eyepieceid;
    $ob["filter"] = $get->filterid;
    $ob["lens"] = $get->lensid;
    return $ob;
  }

  // getObservationFromQuery returns an array with the names of all observations
  //  where the queries are defined in an array.
  // An example of an array :
  //  $q = array("object" => "NGC 7293", "observer" => "wim",
  // 		"instrument" => "3", "location" => "24",
  //		"mindate" => "20040512", "maxdate" => "20040922",
  //             "mindiameter" => "100", "maxdiameter" => "200", "type" => "GALXY", "con" => "AND",
  //             "minmag" => "6.0", "maxmag" => "14.0", "minsubr" => "13.0",
  //             "maxsubr" => "14.0", "minra" => "0.3", "maxra" => "0.9",
  //             "mindecl" => "24.0", "maxdecl" => "30.0", "urano" => "111",
  //             "uranonew" => "111", "sky" => "11", "msa" => "222",
  //             "mindiam1" => "12.2", "maxdiam1" => "13.2", "mindiam2" => "11.1",
  //             "maxdiam2" => "22.2", "description" => "Doughnut", "minvisibility" => "5",
  //		          "maxvisibility" => "3", "minseeing" => "2", "maxseeing" => "4",
  //             "minlimmag" => "5.5", "maxlimmag" => "6.0", $languages =>  Array ( [0] => en )),
  //             "eyepiece" => "4", "filter" => "2", "lens" => "3", "minSmallDiameter" => "3.4",
  //             "maxSmallDiameter" => "3.7", "minLargeDiameter" => "5.3", "maxLargeDiameter" => "6.5",
  //             "stellar" => "1", "extended" => "0", "resolved" => "0", "mottled" => "1",
  //             "characterType" => "A", "unusualShape" => "0", "partlyUnresolved" => "1", 
  //             "colorContrasts" => "0", "minSQM" => "18.9", "maxSQM" => "21.2";
  function getObservationFromQuery($queries, $seenpar="D", $exactinstrumentlocation = "0")
  { $object = "";
    $sqland = "";
    $alternative = "";
		if(!array_key_exists('countquery',$queries))
      $sql1="SELECT DISTINCT observations.id as observationid, 
			                       observations.objectname as objectname,
						  							 observations.date as observationdate,
														 observations.description as observationdescription, 
  													 observers.id as observerid,
														 CONCAT(observers.firstname , ' ' , observers.name) as observername,
  													 CONCAT(observers.name , ' ' , observers.firstname) as observersortname,
														 objects.con as objectconstellation, 
													   instruments.id as instrumentid,
														 instruments.name as instrumentname,
														 instruments.diameter as instrumentdiameter,
  													 CONCAT(10000+instruments.diameter,' mm ',instruments.name) as instrumentsort
														 ";
		else
		  $sql1="SELECT count(DISTINCT observations.id) as ObsCnt ";
    $sql2=$sql1;
		$sql1.= "FROM observations " .
	          "JOIN instruments on observations.instrumentid=instruments.id " .
  					"JOIN objects on observations.objectname=objects.name " .
	  				"JOIN locations on observations.locationid=locations.id " .
		  			"JOIN objectnames on observations.objectname=objectnames.objectname " .
			  		"JOIN observers on observations.observerid=observers.id ";
    $sql2.= "FROM observations " .
	          "JOIN objectpartof on objectpartof.objectname=observations.objectname " .
	          "JOIN instruments on observations.instrumentid=instruments.id " .
				 	  "JOIN objects on observations.objectname=objects.name " .
				 	  "JOIN locations on observations.locationid=locations.id " .
					  "JOIN objectnames on objectpartof.partofname=objectnames.objectname " .
					  "JOIN observers on observations.observerid=observers.id ";
    if(array_key_exists('object',$queries) && ($queries["object"] != ""))
      $sqland.="AND (objectnames.altname like \"".$queries["object"]."\") ";
    elseif(array_key_exists('catalog',$queries)&&$queries["catalog"]&&$queries['catalog']!='%')
      $sqland.="AND (objectnames.altname like \"".trim($queries["catalog"].' '.$queries['number'].'%')."\") "; 
    $sqland.=(isset($queries["observer"])&&$queries["observer"])?" AND observations.observerid = \"" . $queries["observer"] . "\" ":'';
    if (isset($queries["instrument"]) && ($queries["instrument"] != ""))
    { $sqland .= "AND (observations.instrumentid = \"" . $queries["instrument"] . "\" ";
      if(!$exactinstrumentlocation == 1)
      { $insts = $instruments->getAllInstrumentsIds($queries["instrument"]);
        while(list($key,$value)=each($insts))
        $sqland .= " || observations.instrumentid = \"" . $value . "\" ";
      }
      $sqland .= ") ";
    }
    if (isset($queries["eyepiece"]) && ($queries["eyepiece"] != ""))
    { $sqland .= "AND (observations.eyepieceid = \"" . $queries["eyepiece"] . "\" ";
      if(!$exactinstrumentlocation)
      { $eyeps = $eyepieces->getAllEyepiecesIds($queries["eyepiece"]);
        while(list($key,$value)=each($eyeps))
        $sqland .= " || observations.eyepieceid = \"" . $value . "\" ";
      }
      $sqland .= ") ";
    }
    if (isset($queries["filter"]) && ($queries["filter"] != ""))
    { $sqland .= " AND (observations.filterid = \"" . $queries["filter"] . "\" ";
      if (!$exactinstrumentlocation)
      { $filts = $filters->getAllFiltersIds($queries["filter"]);
        while (list($key,$value)=each($filts))
        $sqland .= " || observations.filterid = \"" . $value . "\" ";
      }
      $sqland .= ") ";
    }
    if (isset($queries["lens"]) && ($queries["lens"] != ""))
    { $sqland .= "AND (observations.lensid = \"" . $queries["lens"] . "\" ";
      if(!$exactinstrumentlocation)
      { $lns = $lenses->getAllLensesIds($queries["lens"]);
        while(list($key,$value)=each($lns))
        $sqland .= " || observations.lensid = \"" . $value . "\" ";
      }
      $sqland .= ") ";
    }
    if (isset($queries["location"]) && ($queries["location"] != ""))
    { $sqland .= "AND (observations.locationid = \"" . $queries["location"] . "\" ";
      if(!$exactinstrumentlocation)
      { $locs = $GLOBALS['objLocation']->getAllLocationsIds($queries["location"]);
        while(list($key,$value)=each($locs))
        if($value!=$queries["location"]) $sqland .= " || observations.locationid = \"" . $value ."\" ";
      }
      $sqland.=") ";
    }
    if (isset($queries["maxdate"]) && ($queries["maxdate"] != ""))
      if(strlen($queries["maxdate"])>4)
        $sqland.="AND observations.date <= \"".$queries["maxdate"]."\" ";
      else
        $sqland.="AND RIGHT(observations.date,4) <= \"".$queries["maxdate"]."\" ";
    if (isset($queries["mindate"]) && ($queries["mindate"] != ""))
      if(strlen($queries["mindate"])>4)
        $sqland.="AND observations.date >= \"".$queries["mindate"]."\" ";
      else
        $sqland.="AND RIGHT(observations.date,4) >= \"".$queries["mindate"]."\" ";
    $sqland.=(isset($queries["description"])&&$queries["description"])?"AND observations.description like \"%".$queries["description"]."%\" ":'';
    $sqland.=(isset($queries["mindiameter"])&&$queries["mindiameter"])?"AND instruments.diameter >= \"".$queries["mindiameter"]."\" ":'';
    $sqland.=(isset($queries["maxdiameter"])&&$queries["maxdiameter"])?"AND instruments.diameter <= \"".$queries["maxdiameter"]."\" ":'';
    $sqland.=(isset($queries["type"])&&$queries["type"])?"AND objects.type = \"".$queries["type"]."\" ":'';
    $sqland.=(isset($queries["con"])&&$queries["con"])?"AND objects.con = \"".$queries["con"]."\" ":'';
    $sqland.=(isset($queries["minmag"])&&(strcmp($queries["minmag"],"") != 0))?"AND (objects.mag > \"".$queries["minmag"]."\" OR objects.mag like \"".$queries["minmag"]."\") ":'';
    if (isset($queries["maxmag"]) && (strcmp($queries["maxmag"], "") != 0))     $sqland .= "AND (objects.mag < \"".$queries["maxmag"]."\" OR objects.mag like \"".$queries["maxmag"]."\") ";
    if (isset($queries["minsb"]) && (strcmp($queries["minsb"], "") != 0))       $sqland .= "AND objects.subr >= \"".$queries["minsb"]."\" ";
    if (isset($queries["maxsb"]) && (strcmp($queries["maxsb"], "") != 0))       $sqland .= "AND objects.subr <= \"".$queries["maxsb"]."\" ";
    if (isset($queries["minra"]) && (strcmp($queries["minra"], "") != 0))       $sqland .= "AND (objects.ra >= \"".$queries["minra"]."\" OR objects.ra like \"".$queries["minra"]."\") ";
    if (isset($queries["maxra"]) && (strcmp($queries["maxra"], "") != 0))       $sqland .= "AND (objects.ra <= \"".$queries["maxra"]."\" OR objects.ra like \"".$queries["maxra"]."\") ";
    if (isset($queries["mindecl"]) && (strcmp($queries["mindecl"], "") != 0))   $sqland .= "AND objects.decl >= \"".$queries["mindecl"]."\" ";
    if (isset($queries["maxdecl"]) && (strcmp($queries["maxdecl"], "") != 0))   $sqland .= "AND objects.decl <= \"".$queries["maxdecl"]."\" ";
    if (isset($queries["minLat"]) && (strcmp($queries["minLat"], "") != 0))     $sqland .= "AND locations.latitude >= ".$queries["minLat"]." ";
    if (isset($queries["maxLat"]) && (strcmp($queries["maxLat"], "") != 0))     $sqland .= "AND locations.latitude <= ".$queries["maxLat"]." ";
    if (isset($queries["mindiam1"]) && (strcmp($queries["mindiam1"], "") != 0)) $sqland .= "AND (objects.diam1 > \"$diam1\" or objects.diam1 like \"".$queries["mindiam1"]."\") ";
    if (isset($queries["maxdiam1"]) && (strcmp($queries["maxdiam1"], "") != 0)) $sqland .= "AND (objects.diam1 <= \"$diam1\" or objects.diam1 like \"".$queries["maxdiam1"]."\") ";
    if (isset($queries["mindiam2"]) && (strcmp($queries["mindiam2"], "") != 0)) $sqland .= "AND (objects.diam2 > \"$diam2\" or objects.diam2 like \"".$queries["mindiam2"]."\") ";
    if (isset($queries["maxdiam2"]) && (strcmp($queries["maxdiam2"], "") != 0)) $sqland .= "AND (objects.diam2 <= \"$diam2\" or objects.diam2 like \"".$queries["mindiam2"]."\") ";
    $sqland.=(isset($queries["atlas"])&&$queries["atlas"]&&isset($queries["atlasPageNumber"])&&$queries["atlasPageNumber"])?"AND ".$queries["atlas"]."=\"".$queries["atlasPageNumber"]."\" ":'';
    if (isset($queries["minvisibility"]) && ($queries["minvisibility"] != ""))  $sqland .= "AND observations.visibility <= \"".$queries["minvisibility"]."\" AND observations.visibility >= \"1\" ";
    if (isset($queries["maxvisibility"]) && ($queries["maxvisibility"] != ""))  $sqland .= "AND observations.visibility >= \"".$queries["maxvisibility"]."\" ";
    if (isset($queries["minseeing"]) && ($queries["minseeing"] != ""))          $sqland .= "AND observations.seeing <= \"".$queries["minseeing"]."\" ";
    if (isset($queries["maxseeing"]) && ($queries["maxseeing"] != ""))          $sqland .= "AND observations.seeing >= \"".$queries["maxseeing"]."\" ";
    if (isset($queries["minlimmag"]) && ($queries["minlimmag"] != ""))          $sqland .= "AND observations.limmag >= \"".$queries["minlimmag"]."\" ";
    if (isset($queries["maxlimmag"]) && ($queries["maxlimmag"] != ""))          $sqland .= "AND observations.limmag <= \"".$queries["maxlimmag"]."\" ";
    if (isset($queries["minSmallDiameter"]) && ($queries["minSmallDiameter"] != ""))          $sqland .= "AND observations.smallDiameter >= \"".$queries["smallDiameter"]."\" ";
    if (isset($queries["maxSmallDiameter"]) && ($queries["maxSmallDiameter"] != ""))          $sqland .= "AND observations.smallDiameter <= \"".$queries["smallDiameter"]."\" ";
    if (isset($queries["minLargeDiameter"]) && ($queries["minLargeDiameter"] != ""))          $sqland .= "AND observations.largeDiameter >= \"".$queries["largeDiameter"]."\" ";
    if (isset($queries["maxLargeDiameter"]) && ($queries["maxLargeDiameter"] != ""))          $sqland .= "AND observations.largeDiameter <= \"".$queries["largeDiameter"]."\" ";
    if (isset($queries["stellar"]) && ($queries["stellar"] != ""))              $sqland .= "AND observations.stellar = \"".$queries["stellar"]."\" ";
    if (isset($queries["extended"]) && ($queries["extended"] != ""))            $sqland .= "AND observations.extended = \"".$queries["extended"]."\" ";
    if (isset($queries["resolved"]) && ($queries["resolved"] != ""))            $sqland .= "AND observations.resolved = \"".$queries["resolved"]."\" ";
    if (isset($queries["mottled"]) && ($queries["mottled"] != ""))              $sqland .= "AND observations.mottled = \"".$queries["mottled"]."\" ";
    if (isset($queries["characterType"]) && ($queries["characterType"] != ""))  $sqland .= "AND observations.characterType = \"".$queries["characterType"]."\" ";
    if (isset($queries["unusualShape"]) && ($queries["unusualShape"] != ""))    $sqland .= "AND observations.unusualShape = \"".$queries["unusualShape"]."\" ";
    if (isset($queries["partlyUnresolved"]) && ($queries["partlyUnresolved"] != ""))          $sqland .= "AND observations.partlyUnresolved = \"".$queries["partlyUnresolved"]."\" ";
    if (isset($queries["colorContrasts"]) && ($queries["colorContrasts"] != ""))              $sqland .= "AND observations.colorContrasts = \"".$queries["colorContrasts"]."\" ";
    if (isset($queries["minSQM"]) && ($queries["minSQM"] != ""))                $sqland .= "AND observations.SQM >= \"".$queries["minSQM"]."\" ";
    if (isset($queries["maxSQM"]) && ($queries["maxSQM"] != ""))                $sqland .= "AND observations.SQM <= \"".$queries["minSQM"]."\" ";
    if (isset($queries["languages"]))
    { $extra2 = "";
      for($i=0;$i<count($queries["languages"]);$i++)
        $extra2.="OR observations.language = \"".$queries["languages"][$i]."\" ";
      if($extra2)
        $sqland.=" AND (".substr($extra2,3).") ";
    }
    $sql="(".$sql1;
		if($sqland)
		  $sql.=" WHERE " . substr($sqland,4);
    if(array_key_exists('object',$queries)&&($queries["object"]!="")&&(!array_key_exists('countquery',$queries)))
    { $sql.=") UNION (".$sql2;
		  if($sqland)
		    $sql.=" WHERE " . substr($sqland,4);
    }
		$sql.=")";
		if(!array_key_exists('countquery',$queries))
		  $sql .= " ORDER BY observationid DESC"; 
    $sql = $sql.";";
//echo $sql.'<p>'; //=========================================================== HANDY DEBUG LINE
      $run = mysql_query($sql) or die(mysql_error());
		if(!array_key_exists('countquery',$queries))
		{ $j=0;
		  $result=array();
			while($get = mysql_fetch_object($run))
      { $seentype="X";
        if(array_key_exists('deepskylog_id',$_SESSION)&&($seenpar!="D"))
          if ($GLOBALS['objDatabase']->SelectSingleValue("SELECT observations.id FROM observations WHERE objectname = \"". $get->objectname ."\" AND observerid = \"".$_SESSION['deepskylog_id']."\"",'id')) // object has been seen by the observer logged in
            $seentype="Y";
        if(($seenpar=="D")||($seenpar==$seentype))
				{ while(list($key,$value) = each($get))
				    $result[$j][$key]=$value;
          $j++;
				}
      }
      return $result;
    }
		else
		{ $get = mysql_fetch_object($run);
		  return $get->ObsCnt;
		} 
  }

  // getObservations returns an array with all observations
  function getObservations()
  { $run = $GLOBALS['objDatabase']->selectRecordset("SELECT * FROM observations;");
    while($get=mysql_fetch_object($run))
      $observations[]=$get->id;
    if($observations)
      return sort($observations);
    else 
		  array();
  }

  // getObjectId returns the name of the observed object
  function getObjectId($id)
  {
    $db = new database;
    $db->login();
    $sql = "SELECT observations.objectname FROM observations WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());
    $get = mysql_fetch_object($run);
    if($get)
      $name = $get->objectname;
    else
      $name = '';
    $db->logout();
    return $name;
  }


  function getObservationsCountFromObserver($id)
  {
    $db = new database;
    $db->login();

    $sql = "SELECT COUNT(*) as Cnt FROM observations WHERE observations.observerid = \"$id\" and visibility != 7 ";
    $run = mysql_query($sql) or die(mysql_error());

    $get = mysql_fetch_object($run);

    if($get)
    {
      $cnt = $get->Cnt;
    }
    else
    {
      $cnt = 0;
    }

    $db->logout();

    return $cnt;
  }

  
  function getObserverId($id)                                                   // getObserverId returns the name of the observer
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT observerid FROM observations WHERE id=\"".$id."\"",'observerid',0);
  }

  // getPopularObserversSorted()
  // returns an array with a list with for each observer
  // total number of observations
  // total number of observations this year
  // number of observed messier objects
  // number of different objects observed
  // the field to be sorted on should be given as a parameter

  function getPopularObservers()                                                // getPopularObservers() returns the number of observations of the observers
  { return $GLOBALS['objDatabase']->selectSingleArray("SELECT observations.observerid, COUNT(observations.id) As Cnt FROM observations GROUP BY observations.observerid ORDER BY Cnt DESC", 'observerid');
  }
	
  // getPopularObservers() returns the number of observations of the
  // observers

  function getPopularObserversOverviewCatOrList($sort, $cat="")
  {
    include "setup/databaseInfo.php";
    $observers = new Observers;
    $db = new database;
    $db->login();
    if($sort=="jaar")
    { $t = getdate();
      $sql = "SELECT observations.observerid, COUNT(*) AS Cnt, observers.name " .
	           "FROM observations " .
					   "JOIN observers on observations.observerid = observers.id " .
					   "WHERE observations.date > \"" . date('Ymd', strtotime('-1 year')) . "\" AND observations.visibility != \"7\" ";
    }
    elseif($sort=="catalog")
    { if(substr($cat,0,5)=="List:")
        if(substr($cat,5,7)=="Public:")
				  $sql = "SELECT observations.observerid, COUNT(DISTINCT observations.objectname) AS Cnt, observers.name " .
	               "FROM observations " .
					       "JOIN observerobjectlist on observerobjectlist.objectname=observations.objectname " . 
					       "JOIN observers on observations.observerid = observers.id " .
						     "WHERE observerobjectlist.listname=\"" . substr($cat,5) . "\" " .
						     "AND observations.visibility != 7 ";
				else
				  $sql = "SELECT observations.observerid, COUNT(DISTINCT observations.objectname) AS Cnt, observers.name " .
	               "FROM observations " .
					       "JOIN observerobjectlist on observerobjectlist.objectname=observations.objectname " . 
					       "JOIN observers on observations.observerid = observers.id " .
						     "WHERE observerobjectlist.listname=\"" . substr($cat,5) . "\" " .
						     "AND observerobjectlist.observerid = \"" . $_SESSION['deepskylog_id'] . "\" " .
						     "AND observations.visibility != 7 ";
			else
        $sql = "SELECT observations.observerid, COUNT(DISTINCT objectnames.catindex) AS Cnt, observers.name " .
	             "FROM observations " .
					     "JOIN objectnames on observations.objectname=objectnames.objectname " .
					     "JOIN observers on observations.observerid = observers.id " .
		           "WHERE objectnames.catalog=\"$cat\" AND observations.visibility != 7 ";
    }
    elseif($sort=="objecten")
    { $sql = "SELECT observations.observerid, COUNT(DISTINCT observations.objectname) AS Cnt " .
	           "FROM observations " . 
				  	 "JOIN observers on observations.observerid = observers.id WHERE observations.visibility != 7 ";
    }
    else
    { $sql = "SELECT observations.observerid, COUNT(*) AS Cnt " .
	           "FROM observations " .
				  	 "JOIN observers on observations.observerid = observers.id WHERE observations.visibility != 7 ";
    }
    $sql .= "GROUP BY observations.observerid, observers.name ";
    if ($sort=="observer")
      $sql .= "ORDER BY observers.name ASC ";
    else
      $sql .= "ORDER BY Cnt DESC, observers.name ASC ";
    $run = mysql_query($sql) or die(mysql_error());
    while($get = mysql_fetch_object($run))
      $observations[$get->observerid] = $get->Cnt;
    $db->logout();
    return $observations;
  }

  function getObservationsUserObject($userid, $object)
  {
    $db = new database;
    $db->login();
    $sql = "SELECT COUNT(*) As ObsCnt FROM observations " .
	       "WHERE observerid = \"$userid\" AND observations.objectname = \"$object\"";
    $run = mysql_query($sql) or die(mysql_error());
    $get = mysql_fetch_object($run);
    $observations = $get->ObsCnt;
    $db->logout();
    return $observations;
  }

  function getObservationsUserCatalog($userid, $cat)
  {
    $db = new database;
    $db->login();
    $sql = "SELECT COUNT(*) As ObsCnt FROM observations " .
	         "WHERE observerid = \"$userid\" AND observations.objectname = \"$object\"";
    $run = mysql_query($sql) or die(mysql_error());
    $get = mysql_fetch_object($run);
    $observations = $get->ObsCnt;
    $db->logout();
    return $observations;
  }

  // getObservationsThisYear($id) returns the number of observations of the
  // observer the last year
  function getObservationsThisYear($id)
  {
    $date = date("Y")."0101";
    $q = array("observer" => $id, "mindate" => $date);
    $observations = $this->getObservationFromQuery($q);
    $numberOfObservations = count($observations);
    return $numberOfObservations;
  }

  function getObservationsLastYear($id)
  {
    $db = new database;
    $db->login();
    $t = getdate();
    $sql = "SELECT COUNT(*) AS Cnt " .
	         "FROM observations " .
					 "WHERE observations.observerid=\"$id\" AND observations.date > \"" . date("Ymd", ($t[0]-31536000)) . "\" AND observations.visibility != 7 ";
    $run = mysql_query($sql) or die(mysql_error());
    $get = mysql_fetch_object($run);
    $observations = $get->Cnt;
    $db->logout();
    return $observations;
  }

  // getNumberOfObjectsInCatalog($id) return the number of catalog objects
  function getNumberOfObjectsInCatalog($cat)
  {
    include "setup/databaseInfo.php";

    $db = new database;
    $db->login();
    $sql = "SELECT DISTINCT objectnames.objectname FROM objectnames " .
 	  		 "WHERE objectnames.catalog = \"$cat\" ";
    $run = mysql_query($sql) or die(mysql_error());
    return mysql_result($run, 0, 0);
  }


  function getObservedFromCatalogue($id, $catalog)
  {
    $db = new database;
    $db->login();
    $obs=array();
  	if(substr($catalog,0,5)=="List:")
      if(substr($catalog,5,7)=="Public:")
        $sql = "SELECT DISTINCT observerobjectlist.objectname FROM observerobjectlist " .
               "INNER JOIN observations ON observations.objectname = observerobjectlist.objectname " . 
  	  		     "WHERE ((observerobjectlist.listname = \"" . substr($catalog,5) . "\") " .
							 "AND (observations.observerid = \"" . $id . "\") " .
  				     "AND (observations.visibility != 7))";
  	  else
        $sql = "SELECT DISTINCT observerobjectlist.objectname FROM observerobjectlist " .
               "INNER JOIN observations ON observations.objectname = observerobjectlist.objectname " . 
  	  	   	   "WHERE ((observerobjectlist.listname = \"" . substr($catalog,5) . "\") AND (observerobjectlist.observerid = \"" . $_SESSION['deepskylog_id'] . "\") " .
							 "AND (observations.observerid = \"" . $id . "\") " .
  				     "AND (observations.visibility != 7))";
  	else
      $sql = "SELECT DISTINCT objectnames.objectname FROM objectnames " .
             "INNER JOIN observations ON observations.objectname = objectnames.objectname " . 
  	  		   "WHERE ((objectnames.catalog = \"$catalog\") " .
  		       "AND (observations.observerid=\"$id\") " .
  				   "AND (observations.visibility != 7))";
    $run = mysql_query($sql) or die(mysql_error());
    while($get = mysql_fetch_object($run))
      if(!in_array($get->objectname, $obs))
        $obs[] = $get->objectname;
    $db->logout();
    if(isset($obs))
      return $obs;
    else
      return null;
  }

  function getObservedFromCataloguePartOf($id, $catalog)
  {
    $db = new database;
    $db->login();
    $obs=array();
    $sql = "SELECT DISTINCT(objectnames.objectname) ".
         "FROM observations " .
	  	   "INNER JOIN (objectpartof INNER JOIN objectnames ON objectpartof.partofname = objectnames.objectname) " .
		     "ON observations.objectname = objectpartof.objectname " .	 
			   "WHERE ((observations.observerid=\"$id\") " .
				 "AND (objectnames.catalog=\"$catalog\") " .
				 "AND (observations.visibility != 7))";
    $run = mysql_query($sql) or die(mysql_error());
    while($get = mysql_fetch_object($run))
    if(!in_array($get->objectname, $obs))
    $obs[] = $get->objectname;
    $db->logout();
    if(isset($obs))
    return $obs;
    else
    return null;
  }

  function getObservedCountFromCatalogue($id, $catalog)
  {
    $db = new database;
    $db->login();
    $sql = "SELECT COUNT(DISTINCT objectnames.catindex) AS CatCnt FROM objectnames " .
         "INNER JOIN observations ON observations.objectname = objectnames.objectname " . 
	  		 "WHERE objectnames.catalog = \"$catalog\" " .
		     "AND observations.observerid=\"$id\" " .
				 "AND observations.visibility != 7 ";
    $run = mysql_query($sql) or die(mysql_error());
    return mysql_result($run, 0, 0);
  }

  function getObservedCountFromCatalogueOrList($id, $catalog)
  {
    $db = new database;
    $db->login();
		if(substr($catalog,0,5)=='List:')
		{ $ret=10;
		}
    else
		{	$sql = "SELECT COUNT(DISTINCT objectnames.catindex) AS CatCnt FROM objectnames " .
             "INNER JOIN observations ON observations.objectname = objectnames.objectname " . 
	  		     "WHERE objectnames.catalog = \"$catalog\" " .
		         "AND observations.observerid=\"$id\" " .
				     "AND observations.visibility != 7 ";
      $run = mysql_query($sql) or die(mysql_error());
			$ret = mysql_result($run, 0, 0);
    }
		return $ret;
  }


  // getNumberOfObservations() returns the total number of observations
  function getNumberOfDsObservations()
  {
   include "setup/databaseInfo.php";
    include_once "observers.php";
    $observers = new Observers;
    $db = new database;
    $db->login();
    $sql = "SELECT COUNT(objectname) FROM observations WHERE visibility != 7 ";
    $run = mysql_query($sql) or die(mysql_error());
    return mysql_result($run, 0, 0);
  }

  // getNumberOfDifferentObjects() returns the number of different objects
  // observed
  function getNumberOfDifferentObjects()
  { $sql = "SELECT COUNT(DISTINCT objectname) FROM observations WHERE visibility != 7 ";
    $run = mysql_query($sql) or die(mysql_error());
    return mysql_result($run, 0, 0);
  }

  // getNumberOfObservationsThisYear() returns the number of observations this
  // year
  function getNumberOfObservationsThisYear()
  { $date = date("Y")."0101";
    $sql = "SELECT COUNT(objectname) FROM observations WHERE date > \"".date("Y")."0101"."\" and visibility != 7 ";
    $run = mysql_query($sql) or die(mysql_error());
    return mysql_result($run, 0, 0);
  }

  function getNumberOfObservationsLastYear()                                    // getNumberOfObservationsThisYear() returns the number of observations this year
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT COUNT(*) AS Cnt FROM observations WHERE observations.date >= \"" . date('Ymd', strtotime('-1 year')) . "\"", 'Cnt', 0);
  }
  function getNumberOfObjects($id)                                              // getNumberOfObjects($id) return the number of different objects seen by the observer
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT COUNT(DISTINCT objectname) As Cnt FROM observations WHERE observerid=\"$id\" AND visibility != 7 ",'Cnt',0);
  }
  function getPopularObservations()                                             // getPopularObservations() returns the number of observations of the objects
  { $run = $GLOBALS['objDatabase']->selectRecordset("SELECT observations.objectname, COUNT(observations.id) As ObservationCount FROM observations GROUP BY observations.objectname ORDER BY ObservationCount DESC");
    $i=0;
		while($get = mysql_fetch_object($run))
      $numberOfObservations[$get->objectname.' ('.$i.')'] = array($i++,$get->objectname);
    return $numberOfObservations;
  }
  function getDsObservationInstrumentId($id)
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT instrumentid FROM observations WHERE id = \"$id\"",'instrumentid','');
  }
  function getDsObservationEyepieceId($id)                                      // getDsObservationEyepieceId returns the id of the eyepiece of the observation
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT eyepieceid FROM observations WHERE id = \"$id\"",'eyepieceid','');
  }
  function getDsObservationFilterId($id)                                        // getFilterId returns the id of the filter of the observation
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT filterid FROM observations WHERE id = \"$id\"",'filterid','');
  }
  function getDsObservationLensId($id)                                          // getLensId returns the id of the lens of the observation
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT lensid FROM observations WHERE id = \"$id\"",'lensid','');
  }
  function getDsSmallDiameter($id)                                              // getDsSmallDiameter returns the small diameter estimation of the observation
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT smallDiameter FROM observations WHERE id = \"$id\"",'smallDiameter','');
  }
  function getDsLargeDiameter($id)                                              // getDsLargeDiameter returns the large diameter estimation of the observation
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT largeDiameter FROM observations WHERE id = \"$id\"",'largeDiameter','');
  }
  function getDsStellar($id)                                                    // getDsStellar returns true if the object was seen stellar
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT stellar FROM observations WHERE id = \"$id\"",'stellar','');
  }
  function getDsExtended($id)                                                   // getDsExtended returns true if the object was seen stellar
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT extended FROM observations WHERE id = \"$id\"",'extended','');
  }
	function getDsResolved($id)                                                   // getDsResolved returns true if the object was seen resolved
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT resolved FROM observations WHERE id = \"$id\"",'resolved','');
  }
  function getDsMottled($id)                                                    // getDsMottled returns true if the object was seen mottled
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT mottled FROM observations WHERE id = \"$id\"",'mottled','');
  }
  function getDsCharacterType($id)                                              // getDsCharacterType returns the character type of the observation
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT characterType FROM observations WHERE id = \"$id\"",'characterType','');
  }
  function getDsUnusualShape($id)                                               // getDsUnusualShape returns true if the object was seen with an unusual shape
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT unusualShape FROM observations WHERE id = \"$id\"",'unusualShape','');
  }
  function getDsPartlyUnresolved($id)                                           // getDsPartlyUnresolved returns true if the object was seen partly unresolved
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT partlyUnresolved FROM observations WHERE id = \"$id\"",'partlyUnresolved','');
  }
  function getDsColorContrasts($id)                                             // getDsColorContrasts returns true if the object was seen with color contrasts
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT colorContrasts FROM observations WHERE id = \"$id\"",'colorContrasts','');
  }
  function getDsObservationLanguage($id)                                        // getLanguage returns the idlanguage of the observation
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT language FROM observations WHERE id = \"$id\"",'language','');
  }
  function getDsObservationLocationId($id)                                      // getLocationId returns the location of the observation
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT locationid FROM observations WHERE id = \"$id\"",'locationid',"ERROR ID: " . $id);
  }
  function getDateDsObservation($id)  // getDate returns the date of the given observation in UT
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT date FROM observations WHERE id = \"$id\"",'date','');
  }
  function getLimitingMagnitude($id)                                            // getLimitingMagnitude returns the limiting magnitude of the observation
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT limmag FROM observations WHERE id = \"$id\"",'limmag','');
  }
  function getSQM($id)                                                          // getSQM returns the SQM of the observation
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT SQM FROM observations WHERE id = \"$id\"",'SQM','');
  }
  function getSeeing($id)                                                       // getSeeing returns the seeing of the observation
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT seeing FROM observations WHERE id = \"$id\"",'seeing','');
  }
  function getTime($id)                                                         // getTime returns the time of the given observation in UT
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT time FROM observations WHERE id = \"$id\"",'time','');
  }
  function getDsObservationLocalTime($id)                                       // getLocalTime returns the time of the given observation in local time
  { if($get=mysql_fetch_object($GLOBALS['objDatabase']->selectrecordset("SELECT date, time, locationid FROM observations WHERE id=\"$id\"")))
    { $date = $get->date;
      $time = $get->time;
      $loc = $get->locationid;
      $date = sscanf($date, "%4d%2d%2d");
      $timezone = $GLOBALS['objLocation']->getTimezone($loc);
      $dateTimeZone = new DateTimeZone($timezone);
      $datestr =  sprintf("%02d", $date[1]) . "/" . sprintf("%02d", $date[2]) . "/" . $date[0];
      $dateTime = new DateTime($datestr, $dateTimeZone);
      // Geeft tijdsverschil terug in seconden
      $timedifference = $dateTimeZone->getOffset($dateTime);
      $timedifference = $timedifference / 3600.0;
      if ($time < 0)
        return $time;
      $time = sscanf(sprintf("%04d", $time), "%2d%2d");
      $hours = $time[0] + (int)$timedifference;
      $minutes = $time[1];
      // We are converting from UT to local time -> we should add the time difference!
      $timedifferenceminutes = ($timedifference - (int)$timedifference) * 60;
      $minutes = $minutes + $timedifferenceminutes;
      if ($minutes < 0)
      { $hours = $hours - 1;
        $minutes = $minutes + 60;
      }
      else if ($minutes > 60)
      { $hours = $hours + 1;
        $minutes = $minutes - 60;
      }
      if ($hours < 0)
        $hours = $hours + 24;
      if ($hours >= 24)
        $hours = $hours - 24;
      $time = $hours * 100 + $minutes;
      return $time;
		}
		else
		  throw new Exception("Error in getDsObservationLocalTime of observations.php");
  }
  function getDescriptionDsObservation($id)                                                         // getDescription returns the description of the given observation
  { return preg_replace("/&amp;/", "&", $GLOBALS['objDatabase']->selectSingleValue("SELECT description FROM observations WHERE id = \"$id\"",'description',''));
  }


  // getSortedObservations returns an array with the ids of all observations,
  // sorted by the column specified in $sort
  function getSortedObservationsId($sort, $AscDesc)
  {
    include "setup/databaseInfo.php";
    $observers = new Observers;
    $extra = $observers->getObserversFromClub($club);
    $extra3 = "";
    $extra4 = "";
    $sort = "observations.".$sort;
    if ($sort == "date")
    {
      $sort = "date, time";
    }
    if ($sort=="observations.instrumentid")
    {
      $extra3 = "LEFT JOIN instruments on (observations.instrumentid=instruments.id) ";
      $sort = " instruments.diameter, instruments.id";
    }
    if ($sort=="observations.observerid")
    {
      $extra4 = "LEFT JOIN observers on (observations.observerid=observers.id) ";
      $sort = " observers.name, observers.firstname";
    }
    $extra2 = "";
    if ($_SESSION['deepskylog_id'] != "")
    {
      $observer = new Observers;
      $languages = $observer->getUsedLanguages($_SESSION['deepskylog_id']);
      for ($i = 0;$i < count($languages);$i++)
      {
        $extra2 = $extra2 . "observations.language = \"" . $languages[$i] . "\"";

        if ($i != count($languages) - 1)
        {
          $extra2 = $extra2 . " OR ";
        }
      }
      if ($extra2 != "")
      {
        if ($extra != "")
        {
          $extra2 = " AND (" . $extra2 . ")";
        }
        else
        {
          $extra2 = " WHERE (" . $extra2 . ")";
        }
      }
    }
    else
    {
    }
    $sql = "SELECT observations.id FROM observations " .
    $extra3 .
    $extra4 .
    $extra .
    $extra2 .
				 " ORDER BY $sort $AscDesc";
    $db = new database;
    $db->login();
    $run = mysql_query($sql) or die(mysql_error());
    while($get = mysql_fetch_object($run))
    {
      $observations[] = $get->id;
    }
    $db->logout();
    return $observations;
  }


  // getVisibility returns the visibility of the observation
  function getVisibility($id)
  {
    $db = new database;
    $db->login();

    $sql = "SELECT * FROM observations WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $get = mysql_fetch_object($run);

    if($get)
    {
      $visibility = $get->visibility;
    }
    else
    {
      $visibility = '';
    }


    $db->logout();

    return $visibility;
  }

  // setObjectId sets a new name of the observed object
  function setObjectId($id, $name)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET objectname = \"$objectname\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setObserverId sets a new observer for the given observation
  function setObserverId($id, $observer)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET observerid = \"$observer\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setInstrumentId sets the id of the instrument for the given observation
  function setInstrumentId($id, $instrument)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET instrumentid = \"$instrument\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setEyepieceId sets the id of the eyepiece for the given observation
  function setEyepieceId($id, $eyepiece)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET eyepieceid = \"$eyepiece\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setFilterId sets the id of the filter for the given observation
  function setFilterId($id, $filter)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET filterid = \"$filter\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setLensId sets the id of the lens for the given observation
  function setLensId($id, $lens)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET lensid = \"$lens\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setLanguage sets the language for the given observation
  function setObservationLanguage($id, $language)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET language = \"$language\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setLocationId sets the location for the given observation
  function setLocationId($id, $location)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET locationid = \"$location\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setDate sets the date for the given observation
  function setDate($id, $date)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET date = \"$date\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setLocalDateAndTime sets the date and time for the given observation
  // when the time is given in  local time
  function setLocalDateAndTime($id, $date, $time)
  {
    include_once "locations.php";
    $locations = new Locations();

    if ($time >= 0)
    {
      $db = new database;
      $db->login();

      $sql = "SELECT * FROM observations WHERE id = \"$id\"";
      $run = mysql_query($sql) or die(mysql_error());

      $get = mysql_fetch_object($run);

      $location = $get->locationid;

      $db->logout();

      $timezone = $locations->getTimezone($location);

      $datearray = sscanf($date, "%4d%2d%2d");

      $dateTimeZone = new DateTimeZone($timezone);
      $date =  sprintf("%02d", $datearray[1]) . "/" . sprintf("%02d", $datearray[2]) . "/" . $datearray[0];

      $dateTime = new DateTime($date, $dateTimeZone);
      // Returns the timedifference in seconds
      $timedifference = $dateTimeZone->getOffset($dateTime);
      $timedifference = $timedifference / 3600.0;

      $timestr = sscanf(sprintf("%04d", $time), "%2d%2d");

      $jd = cal_to_jd(CAL_GREGORIAN, $datearray[1], $datearray[2], $datearray[0]);

      $hours = $timestr[0] - (int)$timedifference;

      $timedifferenceminutes = ($timedifference - (int)$timedifference) * 60;

      $minutes = $timestr[1] - $timedifferenceminutes;

      if ($minutes < 0)
      {
        $hours = $hours - 1;
        $minutes = $minutes + 60;
      }
      else if ($minutes > 60)
      {
        $hours = $hours + 1;
        $minutes = $minutes - 60;
      }

      if ($hours < 0)
      {
        $hours = $hours + 24;
        $jd = $jd - 1;
      }
      if ($hours >= 24)
      {
        $hours = $hours - 24;
        $jd = $jd + 1;
      }

      $time = $hours * 100 + $minutes;

      $dte = JDToGregorian($jd);
      sscanf($dte, "%2d/%2d/%4d", $month, $day, $year);
      $date = $year . sprintf("%02d", $month) . sprintf("%02d", $day);
    }

    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET date = \"$date\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $sql = "UPDATE observations SET time = \"$time\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setDescription sets the description for the given observation
  function setDescription($id, $description)
  {
    $db = new database;

    $description = html_entity_decode($description, ENT_COMPAT, "ISO-8859-15");

    $db->login();

    $sql = "UPDATE observations SET description = \"$description\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setTime sets the time for the given observation in UT
  function setTime($id, $time)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET time = \"$time\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setSeeing sets the seeing for the given observation
  function setSeeing($id, $seeing)
  {
    $db = new database;
    $db->login();

    if ($seeing == "-1" || $seeing == "")
    {
      $seeing = "NULL";
    }

    $sql = "UPDATE observations SET seeing = $seeing WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setLimitingMagnitude sets the limiting magnitude for the given observation
  function setObservationLimitingMagnitude($id, $limmag)
  {
    $db = new database;
    $db->login();

    if ($limmag == "")
    {
      $limmag = "NULL";
    }

    $limmag = preg_replace("/,/", ".", $limmag);

    $sql = "UPDATE observations SET limmag = $limmag WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setSQM sets the SQM for the given observation
  function setSQM($id, $sqm)
  {
    $db = new database;
    $db->login();

    if ($sqm == "")
    {
      $sqm = "NULL";
    }

    $sqm = preg_replace("/,/", ".", $sqm);

    $sql = "UPDATE observations SET limmag = $sqm WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setVisibility sets a new visibility for the given observation
  function setVisibility($id, $visibility)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET visibility = \"$visibility\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setSmallDiameter sets the estimated small diameter for the given observation
  function setSmallDiameter($id, $smallDiameter)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET smallDiameter = \"$smallDiameter\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setLargeDiameter sets a new estimated large diameter for the given observation
  function setLargeDiameter($id, $largeDiameter)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET largeDiameter = \"$largeDiameter\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setStellar sets whether is object is stellar
  function setStellar($id, $stellar)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET stellar = \"$stellar\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setExtended sets whether the object is extended
  function setExtended($id, $extended)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET extended = \"$extended\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setResolved sets whether the object is resolved
  function setResolved($id, $resolved)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET resolved = \"$resolved\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setMottled sets whether the object is mottled
  function setMottled($id, $mottled)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET mottled = \"$mottled\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setCharacterType sets sets a new character type for the given observation
  function setCharacterType($id, $characterType)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET characterType = \"$characterType\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setUnusualShape sets whether the object has an unusual shape
  function setUnusualShape($id, $unusualShape)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET unusualShape = \"$unusualShape\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setPartlyUnresolved sets whether the object is partly unresolved
  function setPartlyUnresolved($id, $partlyUnresolved)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET partlyUnresolved = \"$partlyUnresolved\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // setColorContrasts sets whether the object has nice color contrasts
  function setColorContrasts($id, $colorContrasts)
  {
    $db = new database;
    $db->login();

    $sql = "UPDATE observations SET colorContrasts = \"$colorContrasts\" WHERE id = \"$id\"";
    $run = mysql_query($sql) or die(mysql_error());

    $db->logout();
  }

  // showObservations prints a table showing all observations.
  function showObservations()
  {
    $inst = new Instruments;
    $loc = new Locations;

    $observations = $this->getObservations();

    $count = 0;

    echo "<table width=\"100%\">
         <tr class=\"type3\">
          <td>id</td>
          <td>object</td>
          <td>observer</td>
          <td>instrument</td>
          <td>location</td>
          <td>date</td>
          <td>time</td>
          <td>seeing</td>
          <td>limiting magnitude</td>
          <td>visibility</td>
          <td>description</td>
         </tr>";

    while(list ($key, $value) = each($observations))
    {
      print $value."<br />";
      if ($count % 2)
      {
        $class = "class=\"type1\"";
      }
      else
      {
        $class = "class=\"type2\"";
      }

      $objectname = $this->getObjectId($value);
      $observername = $this->getObserverId($value);
      $instrumentid = $this->getDsObservationInstrumentId($value);
      $instrument = $inst->getInstrumentName($instrumentid);
      $locationid = $this->getDsObservationLocationId($value);
      $location = $loc->getLocationName($locationid);
      $date = $this->getDateDsObservation($value);
      $time = $this->getTime($value);
      $description = $this->getDescriptionDsObservation($value);
      $seeing = $this->getSeeing($value);
      $visibility = $this->getVisibility($value);
      $limmag = $this->getLimitingMagnitude($value);
      $sqm = $this->getSQM($value);

      //   echo "<tr $class><td> $value </td><td> $objectname </td><td> $observername </td><td> $instrument </td><td> $location </td><td> $date </td><td> $time </td><td> $seeing </td><td> $limmag </td><td> $visibility </td><td> $description </td>";

      //   echo "</tr>\n";

      $count++;
    }
    echo "</table>";
  }

  function getLOObservationId($objectname, $userid, $notobservation)
  { return $GLOBALS['objDatabase']->selectSingleValue("SELECT id FROM observations WHERE objectname = \"$objectname\" and observerid = \"$userid\" and id != \"$notobservation\" ORDER BY id DESC",'id',0);
  }

  function getMOObservationsId($object, $userid, $notobservation)
  {
    $db = new database;
    $db->login();
    $sql = "SELECT observations.id FROM observations " .
	        "WHERE objectname = \"$object\" and observerid = \"$userid\" " . 
					"AND id != \"$notobservation\" ORDER BY id DESC";
    $run = mysql_query($sql) or die(mysql_error());
    $obs=array();
    while ($get = mysql_fetch_object($run))
    {
      $obs[] = $get->id;
    }
    $db->logout();
    return $obs;
  }

  function getAOObservationsId($object, $notobservation)
  {
    $db = new database;
    $db->login();
    $sql = "SELECT observations.id FROM observations " .
	        "WHERE objectname = \"$object\" AND id != \"$notobservation\" ORDER BY id DESC";
    $run = mysql_query($sql) or die(mysql_error());
    $obs=array();
    while ($get = mysql_fetch_object($run))
    {
      $obs[] = $get->id;
    }
    $db->logout();
    return $obs;
  }

  function showObservation($LOid)
  { global $dateformat;
		
    include_once "../lib/instruments.php";
    $instruments = new Instruments;
    include_once "../lib/observers.php";
    $observer = new Observers;
    include_once "../lib/locations.php";
    $locations = new Locations;
    include_once "../lib/eyepieces.php";
    $eyepieces = new Eyepieces;
    include_once "../lib/filters.php";
    $filters = new Filters;
    include_once "../lib/lenses.php";
    $lenses = new Lenses;


    echo("<table width=\"100%\">");
    echo("<tr class=\"type3\">");
    echo("<td class=\"fieldname\" width=\"25%\" align=\"right\">");
    echo LangViewObservationField2;
    echo("</td>");
    echo("<td width=\"25%\">");
    echo("<a href=\"common/indexCommon.php?indexAction=detail_observer&amp;user=".urlencode($this->getObserverId($LOid))."&amp;back=index.php?indexAction=detail_observation\">");
    echo($observer->getFirstName($this->getObserverId($LOid)) . "&nbsp;" . $observer->getObserverName($this->getObserverId($LOid)));
    print("</a>");
    print("</td>");
    print("</tr>");

    print("<tr class=\"type1\">");
    echo("<td class=\"fieldname\" width=\"25%\" align=\"right\">");
    echo LangViewObservationField3;
    echo("</td>");
    echo("<td width=\"25%\">");
    $inst =  $instruments->getInstrumentName($this->getDsObservationInstrumentId($LOid));
    if ($inst == "Naked eye")
    {
      $inst = InstrumentsNakedEye;
    }
    echo("<a href=\"common/detail_instrument.php?instrument=" . $this->getDsObservationInstrumentId($LOid) . "\">" . $inst . "</a>");
    print("</td>");
    print("<td class=\"fieldname\" width=\"25%\" align=\"right\">");
    echo LangViewObservationField31;
    echo("</td>");
    echo("<td width=\"25%\">");
    $filter = $this->getDsObservationFilterId($LOid);
    if ($filter == "" || $filter == 0)
    {
      echo ("-");
    }
    else
    {
      echo("<a href=\"common/detail_filter.php?filter=" . $filter . "\">" . $filters->getFilterName($filter) . "</a>");
    }
    echo("</td>");
    echo("</tr>");

    print("<tr class=\"type2\">");
    print("<td class=\"fieldname\" width=\"25%\" align=\"right\">");
    echo LangViewObservationField30;
    echo("</td>");
    echo("<td width=\"25%\">");
    $eyepiece = $this->getDsObservationEyepieceId($LOid);
    if ($eyepiece == "" || $eyepiece == 0)
    {
      echo ("-");
    }
    else
    {
      echo("<a href=\"common/detail_eyepiece.php?eyepiece=" . $eyepiece . "\">" . $eyepieces->getEyepieceName($eyepiece) . "</a>");
    }
    print("</td>");
    print("<td class=\"fieldname\" width=\"25%\" align=\"right\">");
    echo LangViewObservationField32;
    echo("</td>");
    echo("<td width=\"25%\">");
    $lens = $this->getDsObservationLensId($LOid);
    if ($lens == "" || $lens == 0)
    {
      echo ("-");
    }
    else
    {
      echo("<a href=\"common/detail_lens.php?lens=" . $lens . "\">" . $lenses->getLensName($lens) . "</a>");
    }
    echo("</td>");
    echo("</tr>");

    print("<tr class=\"type1\">");
    print("<td class=\"fieldname\" width=\"25%\" align=\"right\">");
    echo LangViewObservationField4;
    echo("</td>");
    echo("<td width=\"25%\">");
    echo("<a href=\"common/detail_location.php?location=" . $this->getDsObservationLocationId($LOid) . "\">" . $locations->getLocationName($this->getDsObservationLocationId($LOid)) . "</a>");
    print("</td>");
    print("<td class=\"fieldname\" width=\"25%\" align=\"right\">");
    echo LangViewObservationField5;
    $date = sscanf($this->getDateDsObservation($LOid), "%4d%2d%2d");
    $time="";
    if($this->getTime($LOid) >= 0)
    {
      if (array_key_exists('deepskylog_id',$_SESSION) && ($_SESSION['deepskylog_id']) && ($observer->getUseLocal($_SESSION['deepskylog_id'])))
      {
        $date = sscanf($this->getDsObservationLocalDate($LOid), "%4d%2d%2d");
      }
    }
    if($this->getTime($LOid) >= 0)
    {
      if (array_key_exists('deepskylog_id',$_SESSION) && ($_SESSION['deepskylog_id']) && $observer->getUseLocal($_SESSION['deepskylog_id']))
      {
        echo("&nbsp;" . LangViewObservationField9lt);
        $time = $this->getDsObservationLocalTime($LOid);
      }
      else
      {
        echo("&nbsp;" . LangViewObservationField9);
        $time = $this->getTime($LOid);
      }
    }
    echo("</td>");
    echo("<td width=\"25%\">");
    if($date)
    {
      $time = sscanf(sprintf("%04d", $time), "%2d%2d");
      echo date ($dateformat, mktime (0,0,0,$date[1],$date[2],$date[0]));
      if(($time[0]>0) ||($time[1]>0))
      {
        echo ("&nbsp;" . $time[0] . ":");
        printf("%02d", $time[1]);
      }
    }
    echo("</td>");
    echo("</tr>");

    echo("</table>");
    echo("<table width=\"100%\">");
    echo("<tr class=\"type2\">");
    echo("<td class=\"fieldname\" width=\"12%\" align=\"right\">" . LangViewObservationField6 . "</td>");
    // SEEING
    $seeing = $this->getSeeing($LOid);
    echo("<td width=\"12%\">");
    if($seeing != ("-1" & ""))
    {
      if($seeing == 1)
      {
        echo(SeeingExcellent);
      }
      elseif($seeing == 2)
      {
        echo(SeeingGood);
      }
      elseif($seeing == 3)
      {
        echo(SeeingModerate);
      }
      elseif($seeing == 4)
      {
        echo(SeeingPoor);
      }
      elseif($seeing == 5)
      {
        echo(SeeingBad);
      }
    }
    else
    {
      echo("-");
    }
    echo("</td>");
    echo("<td class=\"fieldname\" width=\"13%\" align=\"right\">");
    echo(LangViewObservationField7);
    echo("</td>");
    echo("<td width=\"13%\">");
    // LIMITING MAGNITUDE
    if($this->getLimitingMagnitude($LOid) != ("-1" & "")) // limiting magnitude is set
    {
      echo(sprintf("%1.1f", $this->getLimitingMagnitude($LOid))); // always print 2 digits
    }
    else
    {
      echo("-");
    }
    echo("</td>");
    echo("<td class=\"fieldname\" width=\"25%\" align=\"right\">" . LangViewObservationField22 . "</td>");
    echo("<td width=\"25%\">");
    // VISIBILITY
    $visibility = $this->getVisibility($LOid);
    if ($visibility != ("0"))
    {
      if($visibility == 1)
      {
        echo(LangVisibility1);
      }
      elseif($visibility == 2)
      {
        echo(LangVisibility2);
      }
      elseif($visibility == 3)
      {
        echo(LangVisibility3);
      }
      elseif($visibility == 4)
      {
        echo(LangVisibility4);
      }
      elseif($visibility == 5)
      {
        echo(LangVisibility5);
      }
      elseif($visibility == 6)
      {
        echo(LangVisibility6);
      }
      elseif($visibility == 7)
      {
        echo(LangVisibility7);
      }
      else
      {
        echo("-");
      }
    }
    else
    {
      echo("-");
    }
    echo("</td>");
    echo("</tr>");
    echo("</table>");

    echo("<table width=\"100%\">");
    echo("<tr>");
    echo("<td class=\"fieldname\" width=\"100%\">");
    //echo LangViewObservationField8;
    echo("</td>");
   	echo("</tr>");
    echo("<tr>");
    echo("<td width=\"100%\">");
    	
    $LOdescription = $this->getDescriptionDsObservation($LOid);
     
    // automatically add links towards Messier, NGC, IC and Arp objects in description
    $patterns[0] = "/\s+(M)\s*(\d+)\s/";
    $replacements[0] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=M%20\\2\">&nbsp;M&nbsp;\\2&nbsp;</a>";
    $patterns[1] = "/(NGC|Ngc|ngc)\s*(\d+\w+)/";
    $replacements[1] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=NGC%20\\2\">NGC&nbsp;\\2</a>";
    $patterns[2] = "/(IC|Ic|ic)\s*(\d+)/";
    $replacements[2] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=IC%20\\2\">IC&nbsp;\\2</a>";
    $patterns[3] = "/(Arp|ARP|arp)\s*(\d+)/";
    $replacements[3] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=Arp%20\\2\">Arp&nbsp;\\2</a>";
    echo preg_replace($patterns,$replacements,$LOdescription);
    echo("</td>");
    echo("</tr>");
    echo("</table>");
    $upload_dir = 'drawings';
    $dir = opendir($upload_dir);
    while (FALSE !== ($file = readdir($dir)))
    {
      if ("." == $file OR ".." == $file)
      {
        continue; // skip current directory and directory above
      }
      if(fnmatch($LOid . "_resized.gif", $file) ||
      fnmatch($LOid . "_resized.jpg", $file) ||
    	 fnmatch($LOid. "_resized.png", $file))
      {
        echo("<p><a href=\"deepsky/" . $upload_dir . "/" . $LOid . ".jpg" . "\">
        <img class=\"account\" src=\"deepsky/$upload_dir" . "/" . "$file\">
        </img></a></p>");
      }
    }
    echo "<table width=\"100%\">";
    echo "<tr>";
    if(array_key_exists('deepskylog_id',$_SESSION) && ($_SESSION['deepskylog_id']) && ($this->getObserverId($LOid) == $_SESSION['deepskylog_id'])) // own observation
    {
      echo("<td width=\"33%\"><a href=\"deepsky/index.php?indexAction=adapt_observation&observation=" . $LOid . "\">" . LangChangeObservationTitle . "</a><td>");
      echo("<td width=\"33%\"><a href=\"deepsky/index.php?indexAction=validate_delete_observation&amp;observationid=" . $LOid . "\">" . LangDeleteObservation . "</a></td>");
    }
    echo "</tr></table>";
    echo("<hr>");
  }


  function showCompactObservationLO($obsKey, $link, $myList = false)
  {
    global $observers, $dateformat;
		global $objInstrument;
		global $objObject;
		global $objObserver;
		$value=$_SESSION['Qobs'][$obsKey];
    $object = $value['objectname'];
    $observer = $value['observerid'];
    $temp = $value['instrumentid'];
		$instrument = $value['instrumentname'];
    $instrumentsize = round($value['instrumentdiameter'], 0);
    $desc = $value['observationdescription'];
    $patterns[0] = "/\s+(M)\s*(\d+)/";
    $replacements[0] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=M%20\\2\">&nbsp;M&nbsp;\\2</a>";
    $patterns[1] = "/(NGC|Ngc|ngc)\s*(\d+\w+)/";
    $replacements[1] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=NGC%20\\2\">NGC&nbsp;\\2</a>";
    $patterns[2] = "/(IC|Ic|ic)\s*(\d+)/";
    $replacements[2] = "<a 	href=\"deepsky/index.php?indexAction=detail_object&object=IC%20\\2\">IC&nbsp;\\2</a>";
    $patterns[3] = "/(Arp|ARP|arp)\s*(\d+)/";
    $replacements[3] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=Arp%20\\2\">Arp&nbsp;\\2</a>";
    $description = preg_replace($patterns,$replacements,$desc);
    $AOid = $this->getLOObservationId($object, $_SESSION['deepskylog_id'], $value['observationid']);
    $LOid="";
    $LOdescription="";
    if($AOid)
    { $LOid = $AOid;
      $LOdesc = $this->getDescriptionDsObservation($LOid);
      $patterns[0] = "/\s+(M)\s*(\d+)/";
      $replacements[0] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=M%20\\2\">&nbsp;M&nbsp;\\2</a>";
      $patterns[1] = "/(NGC|Ngc|ngc)\s*(\d+\w+)/";
      $replacements[1] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=NGC%20\\2\">NGC&nbsp;\\2</a>";
      $patterns[2] = "/(IC|Ic|ic)\s*(\d+)/";
      $replacements[2] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=IC%20\\2\">IC&nbsp;\\2</a>";
      $patterns[3] = "/(Arp|ARP|arp)\s*(\d+)/";
      $replacements[3] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=Arp%20\\2\">Arp&nbsp;\\2</a>";
      $LOdescription = preg_replace($patterns,$replacements,$LOdesc);
    }
    if($LOdescription)
    {
      $LOtemp = $this->getDsObservationInstrumentId($LOid);
      $LOinstrument = $objInstrument->getInstrumentName($LOtemp);
      $LOinstrumentsize = round($objInstrument->getDiameter($LOtemp), 0);
    }
    else
    {
      $LOtemp='';
      $LOinstrument='';
      $LOinstrumentsize='';
      $LOdescription='';
    }
    if ($instrument == "Naked eye")
    {
      $instrument = InstrumentsNakedEye;
    }
    if ($LOinstrument == "Naked eye")
    {
      $LOinstrument = InstrumentsNakedEye;
    }
    if ($objObserver->getUseLocal($_SESSION['deepskylog_id']))
    {
      $date = sscanf($this->getDsObservationLocalDate($value['observationid']), "%4d%2d%2d");
    }
    else
    {
      $date = sscanf($this->getDateDsObservation($value['observationid']), "%4d%2d%2d");
    }
    if ($objObserver->getUseLocal($_SESSION['deepskylog_id']))
    {
      $LOdate = sscanf($this->getDsObservationLocalDate($LOid), "%4d%2d%2d");
    }
    else
    {
      $LOdate = sscanf($this->getDateDsObservation($LOid), "%4d%2d%2d");
    }
    // OUTPUT
    $con = $value['objectconstellation'];
    echo("<tr class=\"type2\">\n
         <td><a href=\"deepsky/index.php?indexAction=detail_object&object=".urlencode($object)."\">$object</a></td>\n
    <td> " . $GLOBALS[$con] . "</td>\n
        <td><a href=\"common/indexCommon.php?indexAction=detail_observer&amp;user=".urlencode($observer)."\">" .$value['observername'] . "</a></td>\n
        <td><a href=\"common/indexCommon.php?indexAction=detail_instrument&amp;instrument=".urlencode($temp)."\">$instrument &nbsp;"
    );
    if($instrument != InstrumentsNakedEye)
    {
      echo("(" . $instrumentsize . "&nbsp;mm" . ")");
    }
    echo("</a></td><td>");
    echo date ($dateformat, mktime (0,0,0,$date[1],$date[2],$date[0]));
    echo("</td>\n");
    echo("<td>");
    if($LOdescription)
    {
      echo("<a href=\"common/detail_instrument.php?instrument=" . $LOtemp . "\">$LOinstrument &nbsp;");
      if($LOinstrument != InstrumentsNakedEye)
      {
        echo("(" . $LOinstrumentsize . "&nbsp;mm" . ")");
      }
      echo("</a>");
    }
    echo("</td>");
    echo("<td>");
    if($LOdescription)
    {
      echo date ($dateformat, mktime (0,0,0,$LOdate[1],$LOdate[2],$LOdate[0]));
    }
    echo("</td>");
    echo("<td>");
    echo("<a href=\"deepsky/index.php?indexAction=detail_observation&amp;observation=" . $value['observationid'] . "&amp;QobsKey=".$key."&amp;dalm=D\" title=\"" . LangDetail . "\">" . LangDetailText);
    // LINK TO DRAWING (IF AVAILABLE)
    $upload_dir = 'drawings';
    $dir = opendir($upload_dir);
    while (FALSE !== ($file = readdir($dir)))
    {
      if ("." == $file OR ".." == $file)
      {
        continue; // skip current directory and directory above
      }
      if(fnmatch($value['observationid'] . "_resized.jpg",$file))
      {
        echo LangDetailDrawingText;
      }
    }
    echo("</a>&nbsp;");
    echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value['observationid'] . "&dalm=AO\" title=\"" . LangAO . "\">");
    echo LangAOText;
    echo("</a>");
    echo("&nbsp;");
    if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'])                  // LOGGED IN
    {
      $objectid = $this->getObjectId($value['observationid']);
      if ($LOdescription)
      {
        echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value['observationid'] . "&dalm=MO\" title=\"" . LangMO . "\">");
        echo LangMOText;
        echo("</a>&nbsp;");
        echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value['observationid'] . "&dalm=LO\" title=\"" . LangLO . "\">");
        echo LangLOText;
        echo("</a>&nbsp;");
      }
    }
    echo("</td>");
    if(array_key_exists("listname",$_SESSION) && $_SESSION['listname'] && array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && $myList)
    {
      echo("<td>");
      $db = new database;
      $db->login();
      $listname=$_SESSION['listname'];
      $observer=$_SESSION['deepskylog_id'];
      $sql = "SELECT Count(observerobjectlist.objectname) As ObjCnt FROM observerobjectlist WHERE observerid = \"$observer\" AND objectname=\"$object\" AND listname=\"$listname\"";
      $run = mysql_query($sql) or die(mysql_error());
      $db->logout();
      $get = mysql_fetch_object($run);
      if($get->ObjCnt > 0)
       echo("<a href=" . $link . "&amp;addObservationToList=" . urlencode($value['observationid']) . ">E</a>");
      else
       echo("<a href=" . $link . "&amp;addObservationToList=" . urlencode($value['observationid']) . ">L</a>");
      echo("</td>");
    }
    echo("</tr>\n");

    echo("<tr class=\"type1\">\n");
    echo("<td valign=\"top\">");
    $altnames = $objObject->getAlternativeNames($object);
    $alt="";
    while(list($key, $altvalue) = each($altnames)) // go through names array
    {
      if(trim($altvalue)!=trim($object))
      {
        if($alt)
        $alt .= "<br>" . trim($altvalue);
        else
        $alt = trim($altvalue);
      }
    }
    echo $alt;
    echo("</td>");
    echo("<td colspan=\"4\">");
    echo($description . "<P>" );
    echo("</td>\n");
    echo("<td colspan=\"3\">");
    echo($LOdescription . "<P>" );
    echo("</td>\n");
    echo("</tr>\n");

    echo"<tr>";
    echo"<td> &nbsp; </td>";
    echo"<td colspan=4>";
    $upload_dir = 'drawings';
    $dir = opendir($upload_dir);
    while (FALSE !== ($file = readdir($dir)))
    {
      if ("." == $file OR ".." == $file)
      {
        continue; // skip current directory and directory above
      }
      if(fnmatch($value['observationid'] . "_resized.jpg", $file))
      {
        echo("<p><a href=\"deepsky/" . $upload_dir . "/" . $value['observationid'] . ".jpg" . "\">
        <img class=\"account\" src=\"deepsky/$upload_dir" . "/" . "$file\">
        </img></a></p>");
      }
    }
    echo"</td>";
    echo"<td colspan=3>";
    if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'])                  // LOGGED IN
    {
      if ($LOdescription)
      {
        $upload_dir = 'drawings';
        $dir = opendir($upload_dir);
        while (FALSE !== ($file = readdir($dir)))
        {
          if ("." == $file OR ".." == $file)
          {
            continue; // skip current directory and directory above
          }
          if(fnmatch($LOid . "_resized.jpg", $file))
          {
            echo("<p><a href=\"deepsky/" . $upload_dir . "/" . $LOid . ".jpg" . "\">
            <img class=\"account\" src=\"deepsky/$upload_dir" . "/" . "$file\">
            </img></a></p>");
          }
        }
      }
    }
    echo"</td>";
    echo"</tr>";
  }

  function ShowCompactObservation($obsKey, $link, $myList = false)
  {
    global $dateformat, $objObserver;

    include_once "objects.php";
    $objects = new Objects;

		$value=$_SESSION['Qobs'][$obsKey];
    // OBJECT
    $object = $value['objectname'];
    // OBSERVER
    $observer = $value['observerid'];
    // INSTRUMENT
    $temp = $value['instrumentid'];
    $instrument = $value['instrumentname'];
    $instrumentsize = round($value['instrumentdiameter'], 0);
    // DESCRIPTION
    $desc = $value['observationdescription'];
    $patterns[0] = "/\s+(M)\s*(\d+)/";
    $replacements[0] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=M%20\\2\">&nbsp;M&nbsp;\\2</a>";
    $patterns[1] = "/(NGC|Ngc|ngc)\s*(\d+\w+)/";
    $replacements[1] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=NGC%20\\2\">NGC&nbsp;\\2</a>";
    $patterns[2] = "/(IC|Ic|ic)\s*(\d+)/";
    $replacements[2] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=IC%20\\2\">IC&nbsp;\\2</a>";
    $patterns[3] = "/(Arp|ARP|arp)\s*(\d+)/";
    $replacements[3] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=Arp%20\\2\">Arp&nbsp;\\2</a>";
    $description = preg_replace($patterns,$replacements,$desc);
    if ($instrument == "Naked eye")
      $instrument = InstrumentsNakedEye;
    // OUTPUT
    $con = $objects->getConstellation($object);
    echo("<tr class=\"type2\">\n
         <td><a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($object) . "\">$object</a></td>\n
    <td> " . $GLOBALS[$con] . "</td>\n
         <td><a href=\"common/indexCommon.php?indexAction=detail_observer&amp;user=".urlencode($value['observerid'])."\">".$value['observername']."</a></td>\n
         <td><a href=\"common/indexCommon.php?detail_instrument&amp;instrument=".urlencode($temp)."\">$instrument &nbsp;"
    );
    if($instrument != InstrumentsNakedEye)
      echo("(" . $instrumentsize . "&nbsp;mm" . ")");
    echo("</a></td><td>");
    // DATE
    if (array_key_exists('deepskylog_id',$_SESSION)&&$_SESSION['deepskylog_id']&&$objObserver->getUseLocal($_SESSION['deepskylog_id']))
      $date = sscanf($this->getDsObservationLocalDate($value['observationid']), "%4d%2d%2d");
    else
      $date = sscanf($this->getDateDsObservation($value['observationid']), "%4d%2d%2d");
    echo date ($dateformat, mktime (0,0,0,$date[1],$date[2],$date[0]));
    echo("</td>\n");
    echo("<td>");
    echo("<a href=\"deepsky/index.php?indexAction=detail_observation&amp;observation=" . $value['observationid'] . "&amp;QobsKey=".$obsKey."&amp;dalm=D\" title=\"" . LangDetail . "\">" . LangDetailText);
    // LINK TO DRAWING (IF AVAILABLE)
    $upload_dir = 'drawings';
    $dir = opendir($upload_dir);
    while (FALSE !== ($file = readdir($dir)))
    {
      if ("." == $file OR ".." == $file)
      {
        continue; // skip current directory and directory above
      }
      if(fnmatch($value['observationid'] . "_resized.gif", $file) || fnmatch($value['observationid'] . "_resized.jpg",$file) || fnmatch($value['observationid']. "_resized.png", $file))
      {
        echo LangDetailDrawingText;
      }
    }
    echo("</a>&nbsp;");
    echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value['observationid'] . "&dalm=AO\" title=\"" . LangAO . "\">");
    echo LangAOText;
    echo("</a>&nbsp;");
    if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'])                  // LOGGED IN
    { if($this->getLOObservationId($object, $_SESSION['deepskylog_id'], $value['observationid']))
      { echo("<a href=\"deepsky/index.php?indexAction=detail_observation&amp;observation=" . $value['observationid'] . "&amp;dalm=MO\" title=\"" . LangMO . "\">");
        echo LangMOText;
        echo("</a>&nbsp;");
        echo("<a href=\"deepsky/index.php?indexAction=detail_observation&amp;observation=" . $value['observationid'] . "&amp;dalm=LO\" title=\"" . LangLO . "\">");
        echo LangLOText;
        echo("</a>&nbsp;");
      }
    }
    echo("</td>");
    if(array_key_exists("listname",$_SESSION) && $_SESSION['listname'] && array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && $myList)
    { echo("<td>");
      $db = new database;
      $db->login();
      $listname=$_SESSION['listname'];
      $observer=$_SESSION['deepskylog_id'];
      $sql = "SELECT Count(observerobjectlist.objectname) As ObjCnt FROM observerobjectlist WHERE observerid = \"$observer\" AND objectname=\"$object\" AND listname=\"$listname\"";
      $run = mysql_query($sql) or die(mysql_error());
      $db->logout();
      $get = mysql_fetch_object($run);
      if($get->ObjCnt > 0)
  	   echo("<a href=" . $link . "&amp;addObservationToList=" . urlencode($value['observationid']) . ">E</a>");
  	  else
  	   echo("<a href=" . $link . "&amp;addObservationToList=" . urlencode($value['observationid']) . ">L</a>");
  	  echo("</td>");
    }
    echo("</tr>\n");
    echo("<tr class=\"type1\">\n");
    echo("<td valign=\"top\">");
    $altnames = $objects->getAlternativeNames($object);
    $alt="";
    while(list($key, $altvalue) = each($altnames)) // go through names array
    {
      if(trim($altvalue)!=trim($object))
      {
        if($alt)
        $alt .= "<br>" . trim($altvalue);
        else
        $alt = trim($altvalue);
      }
    }
    echo $alt;
    echo("</td>");
    echo("<td colspan=\"5\">");
    echo($description . "<P>" );
    echo("</td>\n");
    echo("</tr>\n");
    echo"<tr>";
		echo "<td>";
		echo"</td>";
    echo"<td colspan=6>";
    $upload_dir='drawings';
    $dir=opendir($upload_dir);
    while(FALSE!==($file=readdir($dir)))
    { if ("." == $file OR ".." == $file)
      continue; // skip current directory and directory above
      if(fnmatch($value['observationid'] . "_resized.jpg", $file))
      { echo "<p>";
			  echo "<a href=\"deepsky/" . $upload_dir . "/" . $value['observationid'] . ".jpg" . "\"> <img class=\"account\" src=\"deepsky/".$upload_dir."/".$file."\"> </img></a>";
			  echo "</p>";
			}
    }
    echo"</td>";
    echo"</tr>";

  }

  function showOverviewObservation($obsKey, $count, $link, $myList = false)
  { global $dateformat;

    global $objInstrument;
		global $objObject;
		global $objObserver;

		$value=$_SESSION['Qobs'][$obsKey];
    $typefield = "class=\"type" . (2-($count%2)) . "\"";
    // OBJECT
    $object = $value['objectname'];
    // OBSERVER
    $observer = $value['observerid'];
    // INSTRUMENT
    $temp = $value['instrumentid'];
    $instrument = $value['instrumentname'];
    $instrumentsize = round($value['instrumentdiameter'], 0);
    if ($instrument == "Naked eye")
    {
      $instrument = InstrumentsNakedEye;
    }
    // OUTPUT
    $con = $objObject->getConstellation($object);
    echo("<tr $typefield>\n
    <td><a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($object) . "\">$object</a></td>\n
    <td> " . $GLOBALS[$con] . "</td>\n
         <td><a href=\"common/indexCommon.php?indexAction=detail_observer&amp;user=".urlencode($value['observerid'])."\">" . $value['observername'] . "</a></td>\n
         <td><a href=\"common/indexCommon.php?indexAction=detail_instrument&amp;instrument=".urlencode($temp)."\">$instrument &nbsp;"
    );
    if($instrument != InstrumentsNakedEye)
    {
      echo("(" . $instrumentsize . "&nbsp;mm" . ")");
    }
    echo("</a></td><td>");
    // DATE
    if (array_key_exists('deepskylog_id', $_SESSION) && $objObserver->getUseLocal($_SESSION['deepskylog_id']))
    {
      $date = sscanf($this->getDsObservationLocalDate($value['observationid']), "%4d%2d%2d");
    }
    else
    {
      $date = sscanf($this->getDateDsObservation($value['observationid']), "%4d%2d%2d");
    }
    echo date ($dateformat, mktime (0,0,0,$date[1],$date[2],$date[0]));
    echo("</td>\n
         <td><a href=\"deepsky/index.php?indexAction=detail_observation&amp;observation=" . $value['observationid'] . "&amp;QobsKey=".$obsKey."&amp;dalm=D\" title=\"" . LangDetail . "\">" . LangDetails);
    // LINK TO DRAWING (IF AVAILABLE)
    $upload_dir = 'drawings';
    $dir = opendir($upload_dir);
    while (FALSE !== ($file = readdir($dir)))
    {
      if ("." == $file OR ".." == $file)
      {
        continue; // skip current directory and directory above
      }
      if(fnmatch($value['observationid'] . "_resized.gif", $file) || fnmatch($value['observationid'] . "_resized.jpg",$file) || fnmatch($value['observationid']. "_resized.png", $file))
      {
        echo("&nbsp;+&nbsp;");
        echo LangDrawing;
      }
    }
    echo("</a>&nbsp;");

    echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value['observationid'] . "&dalm=AO\" title=\"" . LangAO . "\">");
    echo LangAOText;
    echo("</a>");
    echo("&nbsp;");
    if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'])                  // LOGGED IN
    {
      if($this->getLOObservationId($object, $_SESSION['deepskylog_id'], $value['observationid']))
      {
        echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value['observationid'] . "&dalm=MO\" title=\"" . LangMO . "\">");
        echo LangMOText;
        echo("</a>&nbsp;");
        echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value['observationid'] . "&dalm=LO\" title=\"" . LangLO . "\">");
        echo LangLOText;
        echo("</a>&nbsp;");
      }
    }
    echo("</td>");
    if(array_key_exists("listname",$_SESSION) && $_SESSION['listname'] && array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && $myList)
    {
      echo("<td>");
      $db = new database;
      $db->login();
      $listname=$_SESSION['listname'];
      $observer=$_SESSION['deepskylog_id'];
      $sql = "SELECT Count(observerobjectlist.objectname) As ObjCnt FROM observerobjectlist WHERE observerid = \"$observer\" AND objectname=\"$object\" AND listname=\"$listname\"";
      $run = mysql_query($sql) or die(mysql_error());
      $db->logout();
      $get = mysql_fetch_object($run);      if($get->ObjCnt > 0)
  	   echo("<a href=" . $link . "&amp;addObservationToList=" . urlencode($value['observationid']) . ">E</a>");
  	  else
  	   echo("<a href=" . $link . "&amp;addObservationToList=" . urlencode($value['observationid']) . ">L</a>");
  	  echo("</td>");
    }
    echo("</tr>\n");
  }
	
 function getObjectsFromObservations($observations)
 { $objects = array();
	 $i=0;
   while(list($key, $observation)=each($observations))
     if(!array_key_exists($observation['objectname'], $objects))
   	    $objects[$observation['objectname']] = array($i++,$observation['objectname']);		
	 return $objects;
 }
}
$objObservation=new Observations;
?>
