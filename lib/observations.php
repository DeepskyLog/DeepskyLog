<?php

// The observations class collects all functions needed to enter, retrieve and
// adapt observation data from the database.
//
// Version 0.8 : 12/09/2005, WDM
// version 3.1, DE 20061119
//

include_once "database.php";
include_once "instruments.php";
include_once "filters.php";
include_once "lenses.php";
include_once "eyepieces.php";
include_once "locations.php";
include_once "observers.php";
include_once "objects.php";
include_once "setup/databaseInfo.php";

class Observations
{
 // addObservation adds a new observation to the database. The name, observerid,
 // instrumentid, locationid, date, time, description, seeing and limiting 
 // magnitude should be given as parameters. The id of the latest observation is returned.
 // If the time and date are given in local time, you should execute setLocalDateAndTime after
 // inserting the observation!
 function addDSObservation($objectname, $observerid, $instrumentid, $locationid, $date, $time, $description, $seeing, $limmag, $visibility, $language)
 {
  $db = new database;
  $db->login();

  if (!$_SESSION['lang'])
  {
    $_SESSION['lang'] = "English";
  }
  if ($seeing == "-1" || $seeing == "")
  {
    $seeing = "NULL";
  }
  if ($limmag == "")
  {
    $limmag = "NULL";
  }
  else
  {
    if (ereg('([0-9]{1})[.,]([0-9]{1})', $limmag, $matches)) // limiting magnitude like X.X or X,X with X a number between 0 and 9
    {
      // valid limiting magnitude
      $limmag = $matches[1] . "." . $matches[2]; // save current magnitude limit
     }
     $limmag = "$limmag";
  }
  $description = html_entity_decode($description, ENT_COMPAT, "ISO-8859-15");
  $description = preg_replace("/(\")/", "", $description);
  $description = preg_replace("/;/", ",", $description);
  
	$sql = "INSERT INTO observations (objectname, observerid, instrumentid, locationid, date, time, description, seeing, limmag, visibility, language) " . 
	       "VALUES (\"$objectname\", \"$observerid\", \"$instrumentid\", \"$locationid\", \"$date\", \"$time\", \"$description\", $seeing, $limmag, $visibility, \"$language\")";
  mysql_query($sql) or die(mysql_error());

  $query = "SELECT id FROM observations ORDER BY id DESC LIMIT 1";
  $run = mysql_query($query) or die(mysql_error());
  $db->logout();
  $get = mysql_fetch_object($run);
  if($get) return $get->id; else return '';
 }
 
 // deleteObservation removes the observation with id = $id 
 function deleteDSObservation($id)
 {
  $db = new database;
  $db->login();
  $sql = "DELETE FROM observations WHERE id=\"$id\"";
  mysql_query($sql) or die(mysql_error());
  $db->logout();
 }
 
 // getLocalDate returns the date of the given observation in local time
 function getLocalDate($id)
 {
   include_once "locations.php";
   $locations = new Locations();

   $db = new database;
   $db->login();
   $sql = "SELECT * FROM observations WHERE id = \"$id\"";
   $run = mysql_query($sql) or die(mysql_error());
   $get = mysql_fetch_object($run);
 	 $db->logout();
	
   $date = "";
   if($get)
   {
     $date = $get->date;
     $time = $get->time;
     $loc = $get->locationid;
     if($time >= 0)
     {
       $date = sscanf($date, "%4d%2d%2d");
       $timezone = $locations->getTimezone($loc);		 
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
       $dte = JDToGregorian($jd);
       sscanf($dte, "%2d/%2d/%4d", $month, $day, $year);
       $date = sprintf("%d%02d%02d", $year, $month, $day);			
     } 
   }
   return $date;
 }


 // getAllInfo returns all information of an observation
 function getAllInfo($id)
 {
  $db = new database;
  $db->login();
  $sql = "SELECT * FROM observations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
  $db->logout();

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
  $ob["localdate"] = $this->getLocalDate($id);
  $ob["localtime"] = $this->getLocalTime($id);
  $ob["language"] = $this->getLanguage($id);
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
 //		"maxvisibility" => "3", "minseeing" => "2", "maxseeing" => "4", 
 //             "minlimmag" => "5.5", "maxlimmag" => "6.0", $languages =>  Array ( [0] => en )), 
 //             "eyepiece" => "4", "filter" => "2", "lens" => "3";
 function getObservationFromQuery($queries, $sort = "", $exactmatch = "1", $clubOnly = "True", $seenpar="D", $exactinstrumentlocation = "0")
 {
  include "setup/databaseInfo.php";
  $observers = new Observers;
	$instruments = new Instruments;
  $objects = new Objects;
  $locations = new Locations;
  $filters = new Filters;
  $eyepieces = new Eyepieces;
  $lenses = new Lenses;
  $object = "";
  $sqland = "";
  $alternative = "";
	
  $extra = $observers->getObserversFromClub($club);
  $db = new database;
  $db->login();
  $sql1 = "SELECT DISTINCT observations.id, observations.objectname ";
  if (isset($sort) && ($sort != ""))
  {
   if ($sort=="instrumentid")
     $sql1 .= ",instruments.diameter AS A, instruments.id AS B ";
   else if (isset($sort) && ($sort == "observerid"))
    $sql1 .= ",observers.name AS A, observers.firstname AS B ";
   else if (isset($sort) && ($sort != "id") && ($sort!="objectname"))
    $sql1 .= ",$sort AS A ";
  }
	$sql1.= "FROM observations " .
	        "LEFT JOIN instruments on observations.instrumentid=instruments.id " .
					"LEFT JOIN objects on observations.objectname=objects.name " .
					"LEFT JOIN locations on observations.locationid=locations.id " .
					"LEFT JOIN objectnames on observations.objectname=objectnames.objectname " .
					"LEFT JOIN observers on observations.observerid=observers.id WHERE ";

  $sql2 = "SELECT DISTINCT observations.id, observations.objectname ";
  if (isset($sort) && ($sort != ""))
  {
   if ($sort=="instrumentid")
     $sql2 .= ",instruments.diameter AS A, instruments.id AS B ";
   else if (isset($sort) && ($sort == "observerid"))
    $sql2 .= ",observers.name AS A, observers.firstname AS B ";
   else if (isset($sort) && ($sort != "id") && ($sort!="objectname"))
    $sql2 .= ",$sort AS A ";
  }
	$sql2.= "FROM observations " .
	        "JOIN objectpartof on objectpartof.objectname=observations.objectname " .
	        "JOIN instruments on observations.instrumentid=instruments.id " .
					"JOIN objects on observations.objectname=objects.name " .
					"JOIN locations on observations.locationid=locations.id " .
					"JOIN objectnames on objectpartof.partofname=objectnames.objectname " .
					"JOIN observers on observations.observerid=observers.id WHERE ";
 
	if(array_key_exists('object',$queries) && ($queries["object"] != ""))
  {
    if ($exactmatch == "1")
      $sqland .= "AND (objectnames.altname = \"" . $queries["object"] . "\") ";
    else
	    if($queries["object"]=="* ")
        $sqland .= "AND (objectnames.altname like \"%\")";
		  else
        $sqland .= "AND (objectnames.altname like \"" . $queries["object"] . "%\") ";
  }

	if (isset($queries["observer"]) && ($queries["observer"] != ""))      
	  $sqland .= " AND observations.observerid = \"" . $queries["observer"] . "\" ";
  if (isset($queries["instrument"]) && ($queries["instrument"] != ""))
  {
	  $sqland .= "AND (observations.instrumentid = \"" . $queries["instrument"] . "\" ";
    if(!$exactinstrumentlocation == 1)	    
		{
		  $insts = $instruments->getAllInstrumentsIds($queries["instrument"]);
      while(list($key,$value)=each($insts))
        $sqland .= " || observations.instrumentid = \"" . $value . "\" ";
    }
		$sqland .= ") ";
  }
  if (isset($queries["eyepiece"]) && ($queries["eyepiece"] != ""))
  {
    $sqland .= "AND (observations.eyepieceid = \"" . $queries["eyepiece"] . "\" ";
    if(!$exactinstrumentlocation)
	  {
      $eyeps = $eyepieces->getAllEyepiecesIds($queries["eyepiece"]);
      while(list($key,$value)=each($eyeps))
        $sqland .= " || observations.eyepieceid = \"" . $value . "\" ";
    }
		$sqland .= ") ";
	}
  if (isset($queries["filter"]) && ($queries["filter"] != ""))
  {
   $sqland .= " AND (observations.filterid = \"" . $queries["filter"] . "\" ";
   if (!$exactinstrumentlocation)
	 {
     $filts = $filters->getAllFiltersIds($queries["filter"]);
     while (list($key,$value)=each($filts))
       $sqland .= " || observations.filterid = \"" . $value . "\" ";
   }
   $sqland .= ") ";
  }
  if (isset($queries["lens"]) && ($queries["lens"] != ""))
  {
   $sqland .= "AND (observations.lensid = \"" . $queries["lens"] . "\" ";
   if(!$exactinstrumentlocation)
	 {
     $lns = $lenses->getAllLensesIds($queries["lens"]);
     while(list($key,$value)=each($lns))
       $sqland .= " || observations.lensid = \"" . $value . "\" ";
   }
  $sqland .= ") ";
  }
  if (isset($queries["location"]) && ($queries["location"] != ""))
  {
   $sqland .= "AND (observations.locationid = \"" . $queries["location"] . "\" ";
   if(!$exactinstrumentlocation)
	 {
     $locs = $locations->getAllLocationsIds($queries["location"]);
     while(list($key,$value)=each($locs))
       if($value!=$queries["location"]) $sqland .= " || observations.locationid = \"" . $value ."\" ";
   }
   $sqland .= ") ";
  }
  if (isset($queries["maxdate"]) && ($queries["maxdate"] != ""))
   if(strlen($queries["maxdate"])>4)
     $sqland .= "AND observations.date <= \"" . $queries["maxdate"] . "\" ";
   else
     $sqland .= "AND RIGHT(observations.date,4) <= \"" . $queries["maxdate"] . "\" ";
  if (isset($queries["mindate"]) && ($queries["mindate"] != ""))
   if(strlen($queries["mindate"])>4)
     $sqland .= "AND observations.date >= \"".$queries["mindate"]."\" ";
   else
     $sqland .= "AND RIGHT(observations.date,4) >= \"".$queries["mindate"]."\" ";
  if (isset($queries["description"]) && ($queries["description"] != ""))      $sqland .= "AND observations.description like \"%".$queries["description"]."%\" ";
  if (isset($queries["mindiameter"]) && ($queries["mindiameter"] != ""))      $sqland .= "AND instruments.diameter >= \"".$queries["mindiameter"]."\" ";
  if (isset($queries["maxdiameter"]) && ($queries["maxdiameter"] != ""))      $sqland .= "AND instruments.diameter <= \"".$queries["maxdiameter"]."\" ";
  if (isset($queries["type"]) && ($queries["type"] != ""))                    $sqland .= "AND objects.type = \"".$queries["type"]."\" ";
  if (isset($queries["con"]) && ($queries["con"] != ""))                      $sqland .= "AND objects.con = \"".$queries["con"]."\" ";
  if (isset($queries["minmag"]) && (strcmp($queries["minmag"], "") != 0))     $sqland .= "AND (objects.mag > \"".$queries["minmag"]."\" OR objects.mag like \"".$queries["minmag"]."\") ";
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
  if (isset($queries["urano"]) && ($queries["urano"] != ""))                  $sqland .= "AND objects.urano = \"".$queries["urano"]."\" ";
  if (isset($queries["uranonew"]) && ($queries["uranonew"] != ""))            $sqland .= "AND objects.urano_new = \"".$queries["uranonew"]."\" ";
  if (isset($queries["sky"]) && ($queries["sky"] != ""))                      $sqland .= "AND objects.sky = \"".$queries["sky"]."\" ";
  if (isset($queries["msa"]) && ($queries["msa"] != ""))                      $sqland .= "AND objects.millenium = \"".$queries["msa"]."\" ";
  if (isset($queries["taki"]) && ($queries["taki"] != ""))                    $sqland .= "AND objects.taki = \"".$queries["taki"]."\" ";
  if (isset($queries["minvisibility"]) && ($queries["minvisibility"] != ""))  $sqland .= "AND observations.visibility <= \"".$queries["minvisibility"]."\" AND observations.visibility >= \"1\" ";
  if (isset($queries["maxvisibility"]) && ($queries["maxvisibility"] != ""))  $sqland .= "AND observations.visibility >= \"".$queries["maxvisibility"]."\" ";
  if (isset($queries["minseeing"]) && ($queries["minseeing"] != ""))          $sqland .= "AND observations.seeing <= \"".$queries["minseeing"]."\" ";
  if (isset($queries["maxseeing"]) && ($queries["maxseeing"] != ""))          $sqland .= "AND observations.seeing >= \"".$queries["maxseeing"]."\" ";
  if (isset($queries["minlimmag"]) && ($queries["minlimmag"] != ""))          $sqland .= "AND observations.limmag >= \"".$queries["minlimmag"]."\" ";
  if (isset($queries["maxlimmag"]) && ($queries["maxlimmag"] != ""))          $sqland .= "AND observations.limmag <= \"".$queries["maxlimmag"]."\" ";
  if (isset($queries["languages"]))
  {
    $extra2 = "";
    for($i=0;$i<count($queries["languages"]);$i++)
      $extra2 .= "OR " . "observations.language = \"" . $queries["languages"][$i] . "\" ";
    if ($extra2 != "")
      $sqland .= " AND (" . substr($extra2,3) . ") ";;
  }
  $sql = "(" . $sql1 . substr($sqland,4);
  if ($extra != "" && $clubOnly == "True")
    $sql .= "AND " . $extra;
  if(array_key_exists('object',$queries)&&($queries["object"]!="")&&($queries["object"]!="* "))
  {
	  $sql =  $sql . ") UNION (" . $sql2 . substr($sqland,4); 		
    if ($extra != "" && $clubOnly == "True")
      $sql .= "AND " . $extra;
	}
	$sql = $sql . ")";
  if (isset($sort) && ($sort != ""))
  {
   if ($sort=="instrumentid")
     $sql .= " ORDER BY A, B";
   else if (isset($sort) && ($sort == "observerid"))
    $sql .= " ORDER BY A, B";
   else if (isset($sort) && ($sort != "id") && ($sort!="objectname"))
    $sql .= " ORDER BY A";
   else
    $sql .= " ORDER BY $sort";
  }

  $sql = $sql.";";
  $run = mysql_query($sql) or die(mysql_error());
  while($get = mysql_fetch_object($run))
  {
    if($seenpar != "D")
		{ 
		  $sql = "SELECT COUNT(observations.id) AS cnt " .
		         "FROM observations " .
				  	 "WHERE objectname = \"". $get->objectname ."\" " .
					   "AND observerid = \"" . $_SESSION['deepskylog_id'] . "\"";
      $run2 = mysql_query($sql) or die(mysql_error());
      $get2 = mysql_fetch_object($run2);
		  if ($get2->cnt > 0) // object has been seen by the observer logged in
        $seentype="Y";
			else
			  $seentype="X";
    }
    if(($seenpar == "D")||($seenpar == $seentype))
      $obs[] = $get->id;
  }
  $db->logout();
  if(isset($obs))
    return $obs;
  else
    return null;
 }

 // getObservations returns an array with all observations
 function getObservations()
 {
  include "setup/databaseInfo.php";
  $observers = new Observers;
  $extra = $observers->getObserversFromClub($club);

  $db = new database;
  $db->login();
	$sql = "SELECT * FROM observations ".$extra;
  $run = mysql_query($sql) or die(mysql_error());
  while($get = mysql_fetch_object($run))
  {
   $observations[] = $get->id;
  }
  $db->logout();

  if ($observations)
  {
   sort ($observations);
  }
  return $observations;
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
	{
	   $name = $get->objectname;
  }
	else
	{
	   $name = '';
	}
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
 
 // getObserverId returns the name of the observer
 function getObserverId($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
	{
	   $observerid = $get->observerid;
  }
	else
	{
	  $observerid = 0;
	}
	
  $db->logout();

  return $observerid;
 }

 // getPopularObserversSorted()
 // returns an array with a list with for each observer
 // total number of observations
 // total number of observations this year
 // number of observed messier objects
 // number of different objects observed
 // the field to be sorted on should be given as a parameter

 function getPopularObserversSorted($sort)
 {
    $result = array();

    $observers = new Observers;
    $observerids = $observers->getSortedActiveObservers(id);

    return $result;

 }


 // getPopularObservers() returns the number of observations of the
 // observers
 function getPopularObservers()
 {
  include "setup/databaseInfo.php";
  $observers = new Observers;
  $extra = $observers->getObserversFromClub($club);

  $db = new database;
  $db->login();

  if ($extra != "")
  {
   $sql = "SELECT * FROM observations ".$extra." AND visibility != 7 ";
  }
  else
  {
   $sql = "SELECT * FROM observations WHERE visibility != 7 ";
  }
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $observations[] = $get->observerid;
  }
  $db->logout();

  if ($observations)
  {
   $numberOfObservations = array_count_values ($observations);
   arsort($numberOfObservations);
   return $numberOfObservations;
  }
  else
  {
   return null;
  }
 }
  // getPopularObservers() returns the number of observations of the
 // observers
 
 function getPopularObserversOverview($sort, $cat="")
 {
  include "setup/databaseInfo.php";
  $observers = new Observers;
  $extra = $observers->getObserversFromClub($club);

  $db = new database;
  $db->login();

	if($sort=="jaar")
	{
	  $t = getdate(); 
    $sql = "SELECT observations.observerid, COUNT(*) AS Cnt, observers.name " .
	         "FROM observations " .
					 "JOIN observers on observations.observerid = observers.id " .
					 "WHERE observations.date > \"" . date("Ymd", ($t[0]-31536000)) . "\" AND observations.visibility != \"7\" ";
	}
	elseif($sort=="catalog")
	{
    $sql = "SELECT observations.observerid, COUNT(DISTINCT objectnames.catindex) AS Cnt, observers.name " .
	         "FROM observations " .
					 "JOIN objectnames on observations.objectname=objectnames.objectname " .
					 "JOIN observers on observations.observerid = observers.id " .
		       "WHERE objectnames.catalog=\"$cat\" AND observations.visibility != 7 ";
	}
	elseif($sort=="objecten")
	{
    $sql = "SELECT observations.observerid, COUNT(DISTINCT observations.objectname) AS Cnt " .
	         "FROM observations " . 
					 "JOIN observers on observations.observerid = observers.id WHERE observations.visibility != 7 ";
	}
	else
	{
		$sql = "SELECT observations.observerid, COUNT(*) AS Cnt " .
	         "FROM observations " .
					 "JOIN observers on observations.observerid = observers.id WHERE observations.visibility != 7 ";
	}
	$sql .= "GROUP BY observations.observerid, observers.name ";
	if ($sort=="observer")
	{
	  $sql .= "ORDER BY observers.name ASC ";
	}
	else
	{
		$sql .= "ORDER BY Cnt DESC, observers.name ASC ";
	}
	$sql .=  $extra;

  $run = mysql_query($sql) or die(mysql_error());
  while($get = mysql_fetch_object($run))
  {
   $observations[$get->observerid] = $get->Cnt;
  }
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

 // getNumberOfObservations() returns the total number of observations
 function getNumberOfObservations()
 {
  include "setup/databaseInfo.php";
  include_once "observers.php";
  
  $observers = new Observers;
  $extra = $observers->getObserversFromClub($club);

  $db = new database;
  $db->login();
  if ($extra != "")
  {
   $sql = "SELECT COUNT(objectname) FROM observations ".$extra." AND visibility != 7 ";
  }
  else
  {
   $sql = "SELECT COUNT(objectname) FROM observations WHERE visibility != 7 ";
  }

  $run = mysql_query($sql) or die(mysql_error());

  return mysql_result($run, 0, 0);
 }

 // getNumberOfDifferentObjects() returns the number of different objects 
 // observed
 function getNumberOfDifferentObjects()
 {
  include "setup/databaseInfo.php";
  include_once "observers.php";

  $observers = new Observers;
  $extra = $observers->getObserversFromClub($club);

  $db = new database;
  $db->login();
  if ($extra != "")
  {
   $sql = "SELECT COUNT(DISTINCT objectname) FROM observations ".$extra." AND visibility != 7 ";
  }
  else
  {
   $sql = "SELECT COUNT(DISTINCT objectname) FROM observations WHERE visibility != 7 ";
  }

  $run = mysql_query($sql) or die(mysql_error());

  return mysql_result($run, 0, 0);
 }

 // getNumberOfObservationsThisYear() returns the number of observations this
 // year
 function getNumberOfObservationsThisYear()
 {
  include "setup/databaseInfo.php";
  include_once "observers.php";

  $observers = new Observers;
  $extra = $observers->getObserversFromClub($club);

  $date = date("Y")."0101";

  $db = new database;
  $db->login();
  if ($extra != "")
  {
   $sql = "SELECT COUNT(objectname) FROM observations ".$extra." AND date > \"$date\" and visibility != 7 ";
  }
  else
  {
   $sql = "SELECT COUNT(objectname) FROM observations WHERE date > \"$date\" and visibility != 7 ";
  }

  $run = mysql_query($sql) or die(mysql_error());

  return mysql_result($run, 0, 0);
 }

 // getNumberOfObservationsThisYear() returns the number of observations this
 // year
 function getNumberOfObservationsLastYear()
 {
    $db = new database;
    $db->login();
	  $t = getdate(); 
    $sql = "SELECT COUNT(*) AS Cnt " .
	         "FROM observations " .
					 "WHERE observations.date > \"" . date("Ymd", ($t[0]-31536000)) . "\" AND observations.visibility != 7 ";
	  $run = mysql_query($sql) or die(mysql_error());
    $get = mysql_fetch_object($run);
    $observations = $get->Cnt;
    $db->logout();
    return $observations;
 }




 // getNumberOfObjects($id) return the number of different objects seen by
 // the observer
 function getNumberOfObjects($id)
 {
  include "setup/databaseInfo.php";

  $db = new database;
  $db->login();
  $sql = "SELECT COUNT(DISTINCT objectname) FROM observations WHERE observerid=\"$id\" AND visibility != 7 ";

  $run = mysql_query($sql) or die(mysql_error());

  return mysql_result($run, 0, 0);
 }


 // getPopularObservations() returns the number of observations of the
 // objects
 function getPopularObservations()
 {
  include "setup/databaseInfo.php";
  $observers = new Observers;
  $extra = $observers->getObserversFromClub($club);

  $db = new database;
  $db->login();

  if ($extra != "")
  {
   $sql = "SELECT * FROM observations".$extra. " AND visibility != 7 ";
  }
  else
  {
   $sql = "SELECT * FROM observations".$extra;
  }
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $observations[] = $get->objectname;
  }
  $db->logout();
  $numberOfObservations = array_count_values ($observations);
  arsort($numberOfObservations);
  return $numberOfObservations;
 }

 // getInstrumentId returns the id of the instrument of the observation
 function getInstrumentId($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
	{
	   $instrumentid = $get->instrumentid;
  }
  else
	{
	  $instrumentid = '';
	}
	
  $db->logout();

  return $instrumentid;
 }
 
 // getEyepieceId returns the id of the eyepiece of the observation
 function getEyepieceId($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
	{
	   $eyepieceid = $get->eyepieceid;
  }
  else
	{
	  $eyepieceid = '';
	}
	
  $db->logout();

  return $eyepieceid;
 }

 // getFilterId returns the id of the filter of the observation
 function getFilterId($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
	{
	   $filterid = $get->filterid;
  }
  else
	{
	  $filterid = '';
	}
	
  $db->logout();

  return $filterid;
 }

 // getLensId returns the id of the lens of the observation
 function getLensId($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
	{
	   $lensid = $get->lensid;
  }
  else
	{
	  $lensid = '';
	}
	
  $db->logout();

  return $lensid;
 }

 // getLanguage returns the idlanguage of the observation
 function getLanguage($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
  {
    $language = $get->language;
  }
  else
  {
    $language = '';
  }

  $db->logout();

  return $language;
 }

 // getLocationId returns the location of the observation
 function getLocationId($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
	if($get)
	{
	}
	else
	{
	echo("ERROR ID: " . $id);
	}
  $locationid = $get->locationid;
  $db->logout();
  return $locationid;
 }

 // getDate returns the date of the given observation in UT
 function getDate($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
	{
	   $thedate = $get->date;
  }
	else
	{
	  $thedate = '';
	}
	
  $db->logout();

  return $thedate;
 }
 
 
 // getLimitingMagnitude returns the limiting magnitude of the observation
 function getLimitingMagnitude($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $limmag = $get->limmag;
                  
  $db->logout();

  return $limmag;
 }

 // getSeeing returns the seeing of the observation
 function getSeeing($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
	{
     $seeing = $get->seeing;  
	}
	else
	{
	   $seeing = '';
	}

  $db->logout();

  return $seeing;
 }

 // getTime returns the time of the given observation in UT
 function getTime($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
  {
     $time = $get->time;
  }
  else
  {
    $time = '';
  }

  $db->logout();

  return $time;
 }

 // getLocalTime returns the time of the given observation in local time
 function getLocalTime($id)
 {
  include_once "locations.php";
  $locations = new Locations();

  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
  {
    $date = $get->date;
    $time = $get->time;
    $loc = $get->locationid;

    $db->logout();

    $date = sscanf($date, "%4d%2d%2d");

    $timezone = $locations->getTimezone($loc);

    $dateTimeZone = new DateTimeZone($timezone);

    $datestr =  sprintf("%02d", $date[1]) . "/" . sprintf("%02d", $date[2]) . "/" . $date[0];

    $dateTime = new DateTime($datestr, $dateTimeZone);
    // Geeft tijdsverschil terug in seconden
    $timedifference = $dateTimeZone->getOffset($dateTime); 
    $timedifference = $timedifference / 3600.0;

    if ($time < 0)
    {
      return $time;
    }
    $time = sscanf(sprintf("%04d", $time), "%2d%2d");

    $hours = $time[0] + (int)$timedifference;
    $minutes = $time[1];

    // We are converting from UT to local time -> we should add the time difference!
    $timedifferenceminutes = ($timedifference - (int)$timedifference) * 60;

    $minutes = $minutes + $timedifferenceminutes;

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
    }
    if ($hours >= 24)
    {
      $hours = $hours - 24;
    }

    $time = $hours * 100 + $minutes;
  }

  return $time;
 }

 // getDescription returns the description of the given observation
 function getDescription($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM observations WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if($get)
  {
	$description = $get->description;

	$description = preg_replace("/&amp;/", "&", $description);;
  }
  else
  {
	   $description = '';
  }

  $db->logout();

  return $description;
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
 function setLanguage($id, $language)
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
 function setLimitingMagnitude($id, $limmag)
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

 // setVisibility sets a new visibility for the given observation
 function setVisibility($id, $visibility)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE observations SET visibility = \"$visibility\" WHERE id = \"$id\"";
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
   $instrumentid = $this->getInstrumentId($value);
   $instrument = $inst->getName($instrumentid);
   $locationid = $this->getLocationId($value);
   $location = $loc->getName($locationid);
   $date = $this->getDate($value);
   $time = $this->getTime($value);
   $description = $this->getDescription($value);
   $seeing = $this->getSeeing($value);
   $visibility = $this->getVisibility($value);
   $limmag = $this->getLimitingMagnitude($value);

//   echo "<tr $class><td> $value </td><td> $objectname </td><td> $observername </td><td> $instrument </td><td> $location </td><td> $date </td><td> $time </td><td> $seeing </td><td> $limmag </td><td> $visibility </td><td> $description </td>";

//   echo "</tr>\n";

   $count++;
  }
  echo "</table>";
 }
 
 function getLOObservationId($objectname, $userid, $notobservation)
 {
   $db = new database;
   $db->login();
   $sql = "SELECT * FROM observations WHERE objectname = \"$objectname\" and observerid = \"$userid\" and id != \"$notobservation\" ORDER BY id DESC";
   $run = mysql_query($sql) or die(mysql_error());
   $get = mysql_fetch_object($run);
   $db->logout();
	 if($get)
   { 
	   $obs[] = $get->id;
     return $obs;
   }
	 else
	 {
	   return array();
	 }
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
 {

  global $AND,$ANT,$APS,$AQR,$AQL,$ARA,$ARI,$AUR,$BOO,$CAE,$CAM,$CNC,$CVN,$CMA,$CMI,$CAP,$CAR,$CAS,$CEN,$CEP,$CET,$CHA,$CIR,$COL,$COM,$CRA,$CRB,$CRV,$CRT,$CRU,
         $CYG,$DEL,$DOR,$DRA,$EQU,$ERI,$FOR,$GEM,$GRU,$HER,$HOR,$HYA,$HYI,$IND,$LAC,$LEO,$LMI,$LEP,$LIB,$LUP,$LYN,$LYR,$MEN,$MIC,$MON,$MUS,$NOR,$OCT,$OPH,
         $ORI,$PAV,$PEG,$PER,$PHE,$PIC,$PSC,$PSA,$PUP,$PYX,$RET,$SGE,$SGR,$SCO,$SCL,$SCT,$SER,$SEX,$TAU,$TEL,$TRA,$TRI,$TUC,$UMA,$UMI,$VEL,$VIR,$VOL,$VUL; 
 
  global $ASTER,$BRTNB,$CLANB,$DRKNB,$GALCL,$GALXY,$GLOCL,$GXADN,$GXAGC,$GACAN,$LMCCN,$LMCDN,$LMCGC,$LMCOC,$NONEX,$OPNCL,$PLNNB,
	       $SMCCN,$SMCDN,$SMCGC,$SMCOC,$SNREM,$QUASR,$AA1STAR,$AA2STAR,$AA3STAR,$AA4STAR,$AA8STAR;

	global $dateformat;
	
	
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
       echo("<a href=\"common/detail_observer.php?user=" . $this->getObserverId($LOid) . "&amp;back=index.php?indexAction=detail_observation\">");
          echo($observer->getFirstName($this->getObserverId($LOid)) . "&nbsp;" . $observer->getName($this->getObserverId($LOid)));
          print("</a>");
  	  	print("</td>");
       echo("<td class=\"fieldname\" width=\"25%\" align=\"right\">");
          echo LangViewObservationField3;
        echo("</td>");
  	  	echo("<td width=\"25%\">");
          $inst =  $instruments->getName($this->getInstrumentId($LOid));
          if ($inst == "Naked eye")
          {
            $inst = InstrumentsNakedEye;
          }
          echo("<a href=\"common/detail_instrument.php?instrument=" . $this->getInstrumentId($LOid) . "\">" . $inst . "</a>");
        print("</td>");
  	  print("</tr>");

    	print("<tr class=\"type1\">");
       print("<td class=\"fieldname\" width=\"25%\" align=\"right\">");
          echo LangViewObservationField30;
        echo("</td>");
       echo("<td width=\"25%\">");
          $eyepiece = $this->getEyepieceId($LOid);
          if ($eyepiece == "" || $eyepiece == 0)
          {
            echo ("-");
          }
          else
          {
            echo("<a href=\"common/detail_eyepiece.php?eyepiece=" . $eyepiece . "\">" . $eyepieces->getName($eyepiece) . "</a>");
          }
        print("</td>");
        print("<td class=\"fieldname\" width=\"25%\" align=\"right\">");
          echo LangViewObservationField31;
          echo("</td>");
          echo("<td width=\"25%\">");
          $filter = $this->getFilterId($LOid);
          if ($filter == "" || $filter == 0)
          {
            echo ("-");
          }
          else
          {
            echo("<a href=\"common/detail_filter.php?filter=" . $filter . "\">" . $filters->getName($filter) . "</a>");
          }
        echo("</td>");
  	  echo("</tr>");

    	print("<tr class=\"type1\">");
       print("<td class=\"fieldname\" width=\"25%\" align=\"right\">");
          echo LangViewObservationField32;
        echo("</td>");
       echo("<td width=\"25%\">");
          $lens = $this->getLensId($LOid);
          if ($lens == "" || $lens == 0)
          {
            echo ("-");
          }
          else
          {
            echo("<a href=\"common/detail_lens.php?lens=" . $lens . "\">" . $lenses->getName($lens) . "</a>");
          }
        echo("</td>");
  	  echo("</tr>");

    	print("<tr class=\"type1\">");
       print("<td class=\"fieldname\" width=\"25%\" align=\"right\">");
          echo LangViewObservationField4;
        echo("</td>");
       echo("<td width=\"25%\">");
          echo("<a href=\"common/detail_location.php?location=" . $this->getLocationId($LOid) . "\">" . $locations->getName($this->getLocationId($LOid)) . "</a>");
        print("</td>");
        print("<td class=\"fieldname\" width=\"25%\" align=\"right\">");
          echo LangViewObservationField5;
          $date = sscanf($this->getDate($LOid), "%4d%2d%2d");
          $time="";
					if($this->getTime($LOid) >= 0)
          {
            if (array_key_exists('deepskylog_id',$_SESSION) && ($_SESSION['deepskylog_id']) && ($observer->getUseLocal($_SESSION['deepskylog_id'])))
            {
              $date = sscanf($this->getLocalDate($LOid), "%4d%2d%2d");
            } 
          }
          if($this->getTime($LOid) >= 0)
          { 
            if (array_key_exists('deepskylog_id',$_SESSION) && ($_SESSION['deepskylog_id']) && $observer->getUseLocal($_SESSION['deepskylog_id']))
            {
  	        echo("&nbsp;" . LangViewObservationField9lt);
  		  	  $time = $this->getLocalTime($LOid);
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
  	  
    		  $LOdescription = $this->getDescription($LOid);
  	
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
      echo("<td width=\"33%\"><a href=\"deepsky/control/validate_delete_observation.php?observationid=" . $LOid . "\">" . LangDeleteObservation . "</a></td>");
    }
    if(isset($_GET['new']) && ($_GET['new'] == "yes")) // follow-up observation of multiple observations
    {
      echo("<td width=\"33%\"><a href=\"deepsky/index.php?indexAction=add_observation&object=" . urlencode($this->getObjectId($LOid)) .  "&new=yes\">" . LangViewObservationNew . "</a></td>");
    }
  echo "</tr></table>";
  echo("<hr>");
 }
 
 
 function showCompactObservationLO($value, $link, $myList = false)
 {
  global $instruments, $observers, $observer, $dateformat;
	
	global $AND,$ANT,$APS,$AQR,$AQL,$ARA,$ARI,$AUR,$BOO,$CAE,$CAM,$CNC,$CVN,$CMA,$CMI,$CAP,$CAR,$CAS,$CEN,$CEP,$CET,$CHA,$CIR,$COL,$COM,$CRA,$CRB,$CRV,$CRT,$CRU,
         $CYG,$DEL,$DOR,$DRA,$EQU,$ERI,$FOR,$GEM,$GRU,$HER,$HOR,$HYA,$HYI,$IND,$LAC,$LEO,$LMI,$LEP,$LIB,$LUP,$LYN,$LYR,$MEN,$MIC,$MON,$MUS,$NOR,$OCT,$OPH,
         $ORI,$PAV,$PEG,$PER,$PHE,$PIC,$PSC,$PSA,$PUP,$PYX,$RET,$SGE,$SGR,$SCO,$SCL,$SCT,$SER,$SEX,$TAU,$TEL,$TRA,$TRI,$TUC,$UMA,$UMI,$VEL,$VIR,$VOL,$VUL; 

	include_once "objects.php";
	$objects = new Objects;
	
  $object = $this->getObjectId($value);
  $observer = $this->getObserverId($value);
  $temp = $this->getInstrumentId($value);
  $instrument = $instruments->getName($temp);
  $instrumentsize = round($instruments->getDiameter($temp), 0);
  $desc = $this->getDescription($value);
  $patterns[0] = "/\s+(M)\s*(\d+)/";
  $replacements[0] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=M%20\\2\">&nbsp;M&nbsp;\\2</a>";
  $patterns[1] = "/(NGC|Ngc|ngc)\s*(\d+\w+)/";
  $replacements[1] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=NGC%20\\2\">NGC&nbsp;\\2</a>";
  $patterns[2] = "/(IC|Ic|ic)\s*(\d+)/";
  $replacements[2] = "<a 	href=\"deepsky/index.php?indexAction=detail_object&object=IC%20\\2\">IC&nbsp;\\2</a>";
  $patterns[3] = "/(Arp|ARP|arp)\s*(\d+)/";
  $replacements[3] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=Arp%20\\2\">Arp&nbsp;\\2</a>";
  $description = preg_replace($patterns,$replacements,$desc);
  $AOid = $this->getLOObservationId($object, $_SESSION['deepskylog_id'], $value);
  $LOid="";
  $LOdescription="";
  if($AOid)
  {
   	list($LOid) = $AOid;
    $LOdesc = $this->getDescription($LOid);
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
    $LOtemp = $this->getInstrumentId($LOid);
    $LOinstrument = $instruments->getName($LOtemp);
  	$LOinstrumentsize = round($instruments->getDiameter($LOtemp), 0);
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
  if ($observers->getUseLocal($_SESSION['deepskylog_id']))
  {
    $date = sscanf($this->getLocalDate($value), "%4d%2d%2d");
  }
  else
  {
    $date = sscanf($this->getDate($value), "%4d%2d%2d");
  }
  if ($observers->getUseLocal($_SESSION['deepskylog_id']))
  {
    $LOdate = sscanf($this->getLocalDate($LOid), "%4d%2d%2d");
  }
  else
  {
    $LOdate = sscanf($this->getDate($LOid), "%4d%2d%2d");
  }
  // OUTPUT
	 $con = $objects->getConstellation($object);
   echo("<tr class=\"type2\">\n
         <td><a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($object) . "\">$object</a></td>\n
         <td> " . $$con . "</td>\n
        <td><a href=\"common/detail_observer.php?user=" . $observer . "\">" . 
	                                                        $observers->getFirstName($observer) . "&nbsp;" . 
                                                          $observers->getName($observer) . "</a></td>\n
        <td><a href=\"common/detail_instrument.php?instrument=" . $temp . "\">$instrument &nbsp;"
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
  echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value . "&dalm=D\" title=\"" . LangDetail . "\">" . LangDetailText);
  // LINK TO DRAWING (IF AVAILABLE)
  $upload_dir = 'drawings';
  $dir = opendir($upload_dir);
  while (FALSE !== ($file = readdir($dir)))
  {
    if ("." == $file OR ".." == $file)
    {
      continue; // skip current directory and directory above
    }
    if(fnmatch($value . "_resized.jpg",$file))
    {
      echo LangDetailDrawingText;
    }
  }    
  echo("</a>&nbsp;");
  echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value . "&dalm=AO\" title=\"" . LangAO . "\">");
  echo LangAOText; 
  echo("</a>");
  echo("&nbsp;");
  if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'])                  // LOGGED IN
  {
    $objectid = $this->getObjectId($value);
    if ($LOdescription)
    {
  	  echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value . "&dalm=MO\" title=\"" . LangMO . "\">");
      echo LangMOText; 
      echo("</a>&nbsp;");
      echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value . "&dalm=LO\" title=\"" . LangLO . "\">");
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
  	  echo("<a href=" . $link . "&amp;removeObjectFromList=" . urlencode($object) . ">R</a>");
  	else
  	  echo("<a href=" . $link . "&amp;addObjectToList=" . urlencode($object) . ">L</a>");
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
  echo("<td colspan=\"4\">");
  echo($description . "<P>" );
  echo("</td>\n");
  echo("<td colspan=\"3\">");
  echo($LOdescription . "<P>" );
  echo("</td>\n");
  echo("</tr>\n");
	
	echo"<tr>";
	echo"<td> &nbsp; </td>";
	echo"<td colspan=3>";
  $upload_dir = 'drawings';
  $dir = opendir($upload_dir);
  while (FALSE !== ($file = readdir($dir)))
  {
    if ("." == $file OR ".." == $file)
      {
        continue; // skip current directory and directory above
      }
      if(fnmatch($value . "_resized.jpg", $file))
      {
				echo("<p><a href=\"deepsky/" . $upload_dir . "/" . $value . ".jpg" . "\">
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
 
 function ShowCompactObservation($value, $link, $myList = false)
 {
   global $instruments, $observers, $observer, $dateformat;	
	 
   global $AND,$ANT,$APS,$AQR,$AQL,$ARA,$ARI,$AUR,$BOO,$CAE,$CAM,$CNC,$CVN,$CMA,$CMI,$CAP,$CAR,$CAS,$CEN,$CEP,$CET,$CHA,$CIR,$COL,$COM,$CRA,$CRB,$CRV,$CRT,$CRU,
         $CYG,$DEL,$DOR,$DRA,$EQU,$ERI,$FOR,$GEM,$GRU,$HER,$HOR,$HYA,$HYI,$IND,$LAC,$LEO,$LMI,$LEP,$LIB,$LUP,$LYN,$LYR,$MEN,$MIC,$MON,$MUS,$NOR,$OCT,$OPH,
         $ORI,$PAV,$PEG,$PER,$PHE,$PIC,$PSC,$PSA,$PUP,$PYX,$RET,$SGE,$SGR,$SCO,$SCL,$SCT,$SER,$SEX,$TAU,$TEL,$TRA,$TRI,$TUC,$UMA,$UMI,$VEL,$VIR,$VOL,$VUL; 
	 
	 include_once "objects.php";
	 $objects = new Objects;

	 
	 // OBJECT 
   $object = $this->getObjectId($value);
   // OBSERVER 
   $observer = $this->getObserverId($value);
   // INSTRUMENT 
   $temp = $this->getInstrumentId($value);
   $instrument = $instruments->getName($temp);
   $instrumentsize = round($instruments->getDiameter($temp), 0);
	 // DESCRIPTION
	 $desc = $this->getDescription($value);
   $patterns[0] = "/\s+(M)\s*(\d+)/";
   $replacements[0] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=M%20\\2\">&nbsp;M&nbsp;\\2</a>";
   $patterns[1] = "/(NGC|Ngc|ngc)\s*(\d+\w+)/";
   $replacements[1] = "<a href=\"deepsky/index.php?indexAcion=detail_object&object=NGC%20\\2\">NGC&nbsp;\\2</a>";
   $patterns[2] = "/(IC|Ic|ic)\s*(\d+)/";
   $replacements[2] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=IC%20\\2\">IC&nbsp;\\2</a>";
   $patterns[3] = "/(Arp|ARP|arp)\s*(\d+)/";
   $replacements[3] = "<a href=\"deepsky/index.php?indexAction=detail_object&object=Arp%20\\2\">Arp&nbsp;\\2</a>";
   $description = preg_replace($patterns,$replacements,$desc);
	 if ($instrument == "Naked eye")
   {
     $instrument = InstrumentsNakedEye;
   }
   // OUTPUT
	 $con = $objects->getConstellation($object);
   echo("<tr class=\"type2\">\n
         <td><a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($object) . "\">$object</a></td>\n
         <td> " . $$con . "</td>\n
         <td><a href=\"common/detail_observer.php?user=" . $observer . "\">" . 
			                                                   $observers->getFirstName($observer) . "&nbsp;" . 
																												 $observers->getName($observer) . "</a></td>\n
         <td><a href=\"common/detail_instrument.php?instrument=" . $temp . "\">$instrument &nbsp;"
		   );
   if($instrument != InstrumentsNakedEye)
   {
     echo("(" . $instrumentsize . "&nbsp;mm" . ")");
   }
   echo("</a></td><td>");
   // DATE
   if (array_key_exists('deepskylog_id',$_SESSION)&&$_SESSION['deepskylog_id']&&$observers->getUseLocal($_SESSION['deepskylog_id']))
   {
     $date = sscanf($this->getLocalDate($value), "%4d%2d%2d");
   }
   else
   {
     $date = sscanf($this->getDate($value), "%4d%2d%2d");
   }
   echo date ($dateformat, mktime (0,0,0,$date[1],$date[2],$date[0]));
   echo("</td>\n");
   echo("<td>");
   echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value . "&dalm=D\" title=\"" . LangDetail . "\">" . LangDetailText);
   // LINK TO DRAWING (IF AVAILABLE)
   $upload_dir = 'drawings';
   $dir = opendir($upload_dir);
   while (FALSE !== ($file = readdir($dir)))
   {
     if ("." == $file OR ".." == $file)
     {
       continue; // skip current directory and directory above
     }
     if(fnmatch($value . "_resized.gif", $file) || fnmatch($value . "_resized.jpg",$file) || fnmatch($value. "_resized.png", $file))
     {
       echo LangDetailDrawingText;
     }
   }    
	 echo("</a>&nbsp;");
   echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value . "&dalm=AO\" title=\"" . LangAO . "\">");
   echo LangAOText; 
	 echo("</a>&nbsp;");
   if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'])                  // LOGGED IN
   {
 		 if($this->getLOObservationId($object, $_SESSION['deepskylog_id'], $value))
		 {
 			 echo("<a href=\"deepsky/index.php?indexAction=detail_observation&amp;observation=" . $value . "&amp;dalm=MO\" title=\"" . LangMO . "\">");
       echo LangMOText; 
	     echo("</a>&nbsp;");
	     echo("<a href=\"deepsky/index.php?indexAction=detail_observation&amp;observation=" . $value . "&amp;dalm=LO\" title=\"" . LangLO . "\">");
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
  	   echo("<a href=" . $link . "&amp;removeObjectFromList=" . urlencode($object) . ">R</a>");
  	 else
  	   echo("<a href=" . $link . "&amp;addObjectToList=" . urlencode($object) . ">L</a>");
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
	echo"<td colspan=7>";
  $upload_dir = 'drawings';
  $dir = opendir($upload_dir);
  while (FALSE !== ($file = readdir($dir)))
  {
    if ("." == $file OR ".." == $file)
      continue; // skip current directory and directory above
    if(fnmatch($value . "_resized.jpg", $file))
			echo("<p><a href=\"deepsky/" . $upload_dir . "/" . $value . ".jpg" . "\">
              <img class=\"account\" src=\"deepsky/$upload_dir" . "/" . "$file\">
                </img></a></p>");
  }
	echo"</td>";
  echo"</tr>";
	 
 } 
 
 function showOverviewObservation($value, $count, $link, $myList = false)
 {
   global $instruments, $observers, $observer, $dateformat;
	 
   global $AND,$ANT,$APS,$AQR,$AQL,$ARA,$ARI,$AUR,$BOO,$CAE,$CAM,$CNC,$CVN,$CMA,$CMI,$CAP,$CAR,$CAS,$CEN,$CEP,$CET,$CHA,$CIR,$COL,$COM,$CRA,$CRB,$CRV,$CRT,$CRU,
         $CYG,$DEL,$DOR,$DRA,$EQU,$ERI,$FOR,$GEM,$GRU,$HER,$HOR,$HYA,$HYI,$IND,$LAC,$LEO,$LMI,$LEP,$LIB,$LUP,$LYN,$LYR,$MEN,$MIC,$MON,$MUS,$NOR,$OCT,$OPH,
         $ORI,$PAV,$PEG,$PER,$PHE,$PIC,$PSC,$PSA,$PUP,$PYX,$RET,$SGE,$SGR,$SCO,$SCL,$SCT,$SER,$SEX,$TAU,$TEL,$TRA,$TRI,$TUC,$UMA,$UMI,$VEL,$VIR,$VOL,$VUL; 
	 
	 include_once "objects.php";
	 $objects = new Objects;
 
   if ($count % 2)
   {
     $typefield = "class=\"type1\"";
   }
   else
   {
     $typefield = "class=\"type2\"";
   }
   // OBJECT 
   $object = $this->getObjectId($value);
    // OBSERVER 
   $observer = $this->getObserverId($value);
   // INSTRUMENT 
   $temp = $this->getInstrumentId($value);
   $instrument = $instruments->getName($temp);
   $instrumentsize = round($instruments->getDiameter($temp), 0);
   if ($instrument == "Naked eye")
   { 
     $instrument = InstrumentsNakedEye;
   }
   // OUTPUT
	 $con = $objects->getConstellation($object);
   echo("<tr $typefield>\n
         <td><a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($object) . "\">$object</a></td>\n
         <td> " . $$con . "</td>\n
         <td><a href=\"common/detail_observer.php?user=" . $observer . "\">" . 
	                                                 $observers->getFirstName($observer) . "&nbsp;" . 
																									 $observers->getName($observer) . "</a></td>\n
         <td><a href=\"common/detail_instrument.php?instrument=" . $temp . "\">$instrument &nbsp;"
		     );
   if($instrument != InstrumentsNakedEye)
   {
     echo("(" . $instrumentsize . "&nbsp;mm" . ")");
   }
   echo("</a></td><td>");
   // DATE
   if (array_key_exists('deepskylog_id', $_SESSION) && $observers->getUseLocal($_SESSION['deepskylog_id']))
   {
     $date = sscanf($this->getLocalDate($value), "%4d%2d%2d");
   }
   else
   {
     $date = sscanf($this->getDate($value), "%4d%2d%2d");
   }
   echo date ($dateformat, mktime (0,0,0,$date[1],$date[2],$date[0]));
   echo("</td>\n
         <td><a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value . "&dalm=D\" title=\"" . LangDetail . "\">" . LangDetails);
   // LINK TO DRAWING (IF AVAILABLE)
   $upload_dir = 'drawings';
   $dir = opendir($upload_dir);
   while (FALSE !== ($file = readdir($dir)))
   {
     if ("." == $file OR ".." == $file)
     {
       continue; // skip current directory and directory above
     }
     if(fnmatch($value . "_resized.gif", $file) || fnmatch($value . "_resized.jpg",$file) || fnmatch($value. "_resized.png", $file))
     {
       echo("&nbsp;+&nbsp;");
       echo LangDrawing; 
     }
   }
   echo("</a>&nbsp;");

   echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value . "&dalm=AO\" title=\"" . LangAO . "\">");
   echo LangAOText; 
   echo("</a>");
	 echo("&nbsp;");
   if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'])                  // LOGGED IN
   {
 		 if($this->getLOObservationId($object, $_SESSION['deepskylog_id'], $value))
		 {
 			 echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value . "&dalm=MO\" title=\"" . LangMO . "\">");
       echo LangMOText; 
	     echo("</a>&nbsp;");
	     echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $value . "&dalm=LO\" title=\"" . LangLO . "\">");
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
  	   echo("<a href=" . $link . "&amp;removeObjectFromList=" . urlencode($object) . ">R</a>");
  	 else
  	   echo("<a href=" . $link . "&amp;addObjectToList=" . urlencode($object) . ">L</a>");
	   echo("</td>");	 
	 }
	 echo("</tr>\n");
 }
 
}
?>
