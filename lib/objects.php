<?php

// The objects class collects all functions needed to enter, retrieve and
// adapt object data from the database and functions to display the data.
//
// Version 0.9 : 21/06/2005, WDM
// version 3.1, DE 20061119
//
// $$ ok

include_once "database.php";
include_once "observations.php";
//include_once "setup/vars.php";

class Objects
{
 // addObject adds a new object to the database. The name, alternative name, 
 // type, constellation, right ascension, declination, magnitude, surface 
 // brightness, diam1, diam2, position angle and info about the catalogs should
 // be given as parameters. The chart numbers for uranometria, uranometria
 // second edition, sky atlas, taki, pocket sky atlas, torresB, torresBC, 
 // torresC and millenium star atlas are also put in the
 // database. $datasource describes where the data comes from eg : SAC7.2, 
 // DeepskyLogUser or E&T 2.5
 function addDSObject($name, $cat, $catindex, $type, $con, $ra, $dec, $mag, $subr, $diam1, $diam2, $pa, $catalogs, $datasource)
 {
  $db = new database;
  $db->login();

  if (!$_SESSION['lang'])
  {
   $_SESSION['lang'] = "English";
  }
  $urano = $this->calculateUranometriaPage($ra, $dec);
  $uranonew = $this->calculateNewUranometriaPage($ra, $dec);
  $skyatlas = $this->calculateSkyAtlasPage($ra, $dec);
  $millenium = $this->calculateMilleniumPage($ra, $dec);
  $taki = $this->calculateTakiPage($ra, $dec);
  $psa = $this->calculatePocketSkyAtlasPage($ra, $dec);
  $torresB = $this->calculateTorresBPage($ra, $dec);
  $torresBC = $this->calculateTorresBCPage($ra, $dec);
  $torresC = $this->calculateTorresCPage($ra, $dec);
  $array = array("INSERT INTO objects (name, type, con, ra, decl, mag, subr, diam1, diam2, pa, datasource, urano, urano_new, sky, millenium, taki, psa, torresB, torresBC, torresC) VALUES (\"$name\", \"$type\", \"$con\", \"$ra\", \"$dec\", \"$mag\", \"$subr\", \"$diam1\", \"$diam2\", \"$pa\", \"$datasource\", \"$urano\", \"$uranonew\", \"$skyatlas\", \"$millenium\", \"$taki\", \"$psa\", \"$torresB\", \"$torresBC\", \"$torresC\")");
  $sql = implode("", $array);
  mysql_query($sql) or die(mysql_error());
  $newcatindex = ucwords(trim($catindex));
  $sql= "INSERT INTO objectnames (objectname, catalog, catindex, altname) VALUES (\"$name\", \"$cat\", \"$catindex\", TRIM(CONCAT(\"$cat\", \" \", \"$newcatindex\")))";
  mysql_query($sql) or die(mysql_error());	
  
	// Calculate and set the SBObj
	if ($mag != 99.9 && ($diam1 != 0 || $diam2 != 0))
	{
		if ($diam1 != 0 && $diam2 == 0)
		{
			$diam2 = $diam1;
		} else if ($diam2 != 0 && $diam1 == 0)
		{
			$diam1 = $diam2;
		}
		$SBObj = ($mag + (2.5 * log10(2827.0 * ($diam1/60) * ($diam2/60))));
	}
	else
	{
		$SBObj = -999;
	}
  $sql4 = "update objects set SBObj = \"$SBObj\" where name = \"$name\";";
  $run4 = mysql_query($sql4) or die(mysql_error());

  $db->logout();
 }
 function newName($name, $cat, $catindex)
 {
  $db = new database;
  $db->login();
  $newname = trim($cat . " " . ucwords(trim($catindex)));
	$newcatindex = ucwords(trim($catindex));
  $sql= "UPDATE objectnames SET catalog=\"$cat\", catindex=\"$newcatindex\", altname=TRIM(CONCAT(\"$cat\", \" \", \"$newcatindex\")) 
	       WHERE objectname = \"$name\" AND altname = \"$name\"";
  mysql_query($sql) or die(mysql_error());	
  $sql= "UPDATE objectnames SET objectname=\"$newname\" WHERE objectname = \"$name\"";
  mysql_query($sql) or die(mysql_error());	
  $sql= "UPDATE objects SET name=\"$newname\" WHERE name = \"$name\"";
  mysql_query($sql) or die(mysql_error());	
  $sql= "UPDATE observations SET objectname=\"$newname\" WHERE objectname = \"$name\"";
  mysql_query($sql) or die(mysql_error());	
  $sql= "UPDATE objectpartof SET objectname=\"$newname\" WHERE objectname = \"$name\"";
  mysql_query($sql) or die(mysql_error());	
  $sql= "UPDATE objectpartof SET partofname=\"$newname\" WHERE partofname = \"$name\"";
  mysql_query($sql) or die(mysql_error());	
  $db->logout();
 } 
 function removeAndReplaceObjectBy($name, $cat, $catindex)
 {
  $db = new database;
  $db->login();
  $newname = trim($cat . " " . ucwords(trim($catindex)));
	$newcatindex = ucwords(trim($catindex));
  $sql= "UPDATE observations SET objectname=\"$newname\" WHERE objectname = \"$name\"";
  mysql_query($sql) or die(mysql_error());	
  $sql= "DELETE objectnames.* FROM objectnames WHERE objectname = \"$name\"";
  mysql_query($sql) or die(mysql_error());	
  $sql= "DELETE objectpartof.* FROM objectpartof WHERE objectname=\"$name\" OR partofname = \"$name\"";
  mysql_query($sql) or die(mysql_error());	
  $sql= "DELETE objects.* FROM objects WHERE name = \"$name\"";
  mysql_query($sql) or die(mysql_error());	
  $db->logout();
 } 
 function newAltName($name, $cat, $catindex)
 {
  $db = new database;
  $db->login();
  $catindex = ucwords(trim($catindex));
  $sql= "INSERT INTO objectnames (objectname, catalog, catindex, altname) VALUES (\"$name\", \"$cat\", \"$catindex\", TRIM(CONCAT(\"$cat\", \" \", \"$catindex\")))";
  mysql_query($sql) or die(mysql_error());	
  $db->logout();
 }
 function removeAltName($name, $cat, $catindex)
 {
  $db = new database;
  $db->login();
  $catindex = ucwords(trim($catindex));
	$sql= "DELETE objectnames.* FROM objectnames WHERE objectname = \"$name\" AND catalog = \"$cat\" AND catindex=\"$catindex\"";
  mysql_query($sql) or die(mysql_error());	
  $db->logout();
 }
 function newPartOf($name, $cat, $catindex)
 {
  $db = new database;
  $db->login();
	$partofname = trim($cat . " " . ucwords(trim($catindex)));
  $sql= "INSERT INTO objectpartof (objectname, partofname) VALUES (\"$name\", \"$partofname\")";
  mysql_query($sql) or die(mysql_error());	
  $db->logout();
 }
 function removePartOf($name, $cat, $catindex)
 {
  $db = new database;
  $db->login();
	$partofname = trim($cat . " " . ucwords(trim($catindex)));
  $sql= "DELETE objectpartof.* FROM objectpartof WHERE objectname = \"$name\" AND partofname = \"$partofname\"";
  mysql_query($sql) or die(mysql_error());	
  $db->logout();
 }
 
 // deleteObject removes the object with name = $name 
 function deleteDSObject($name)
 {
  $db = new database;
  $db->login();
  $sql = "DELETE FROM objects WHERE name=\"$name\"";
  mysql_query($sql) or die(mysql_error());
  $db->logout();
 }

 // getAllInfo returns all information of an object
 function getAllInfoDsObject($name)
 { 
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);

  $object["mag"] = $get->mag;
  $object["sb"] = $get->subr;
  $object["con"] = $get->con;
  $object["type"] = $get->type;
  $object["urano"] = $get->urano;
  $object["newurano"] = $get->urano_new;
  $object["sky"] = $get->sky;
  $object["taki"] = $get->taki;
  $object["msa"] = $get->millenium;
  $object["psa"] = $get->psa;
  $object["torresB"] = $get->torresB;
  $object["torresBC"] = $get->torresBC;
  $object["torresC"] = $get->torresC;
  $object["ra"] = $get->ra;
  $object["dec"] = $get->decl;
	if($get->pa != 999)
    $object["pa"] =$get->pa;
  else
    $object["pa"] = "";
	$diam1 = $get->diam1;
  $diam2 = $get->diam2;
  $object["seen"] = "-";
  $object["datasource"] = $get->datasource;

  $object["size"] = $this->calculateSize($diam1, $diam2);
  
  $sql = "SELECT COUNT(id) As CountId FROM observations " .
         "WHERE objectname = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  $seeget = mysql_fetch_object($run);
	$see = $seeget->CountId;
	if ($see > 0)
  {
    $object["seen"] = "X (" . $see . ")";
  }
  if (array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
  {
    $user = $_SESSION['deepskylog_id'];
    $sql = "SELECT observerid, date FROM observations " .
	         "WHERE objectname = \"$name\" AND observerid = \"$user\" ORDER BY date DESC";
    $run = mysql_query($sql) or die(mysql_error());
    $get = mysql_fetch_object($run);
    $sql = "SELECT COUNT(id) As CountId FROM observations " .
	         "WHERE objectname = \"$name\" AND observerid = \"$user\"";
    $run = mysql_query($sql) or die(mysql_error());
    $seeget = mysql_fetch_object($run);
  	$see = $seeget->CountId;
    if ($get)
      $object["seen"] = "Y (" . $get->date . " - " . $see . ")";
  }
 
  $sql = "SELECT altname ".
	       "FROM objectnames " .
				 "WHERE objectnames.objectname = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  $db->logout();

  $object["altname"]="";
	while($get = mysql_fetch_object($run))
    if($get->altname!=$name)
		  if($object["altname"])
		    $object["altname"] .= "/" . $get->altname;
			else
		    $object["altname"]= $get->altname;
			
  return $object;
 }

 // Construct a string from the sizes
 function calculateSize($diam1, $diam2)
 {
  $size = "";
  if ($diam1 != 0.0)
  {
   if ($diam1 >= 40.0)
   { 
    if (round($diam1 / 60.0) == ($diam1 / 60.0))
    {
     if ($diam1 / 60.0 > 30.0)
     {
      $size = sprintf("%.0f'", $diam1 / 60.0);
     }
     else
     {
      $size = sprintf("%.1f'", $diam1 / 60.0);
     }
    }
    else
    {
     $size = sprintf("%.1f'", $diam1 / 60.0);
    }

    if ($diam2 != 0.0)
    {
     if (round($diam2 / 60.0) == ($diam2 / 60.0))
     {
      if ($diam2 / 60.0 > 30.0)
      {
       $size = $size.sprintf("x%.0f'", $diam2 / 60.0);
      }
      else
      {
       $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
      }
     }
     else
     {
      $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
     }
    }
   }
   else
   {
    $size = sprintf("%.1f''", $diam1);

    if ($diam2 != 0.0)
    {
     $size = $size.sprintf("x%.1f''", $diam2);
    }
   }
  }
  return $size;
 }


 
 // getType returns the type of the object
 function getType($name)
 {
  $db = new database;
  $db->login();
  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
	$type = "";
  if ($get)
    $type = $get->type;
  $db->logout();
  return $type;
 }

 // getDatasource returns the datasource of the object
 function getDatasource($name)
 {
  $db = new database;
  $db->login();
  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
  $datasource = $get->datasource;
  $db->logout();
  return $datasource;
 }

 // getConstellation returns the constellation of the object
 function getConstellation($name)
 {
  $db = new database;
  $db->login();
  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
	$constellation = "";
  if ($get)
    $constellation = $get->con;
  $db->logout();
  return $constellation;
 }

 // getRA returns the right ascension of the object
 function getRA($name)
 {
  $db = new database;
  $db->login();
  $sql = "SELECT objects.ra FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
	$ra = "";
  if ($get)
	  $ra = $get->ra;
  $db->logout();
  return $ra;
 }

 // getDeclination returns the declination of the object
 function getDeclination($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
  $db->logout();

  if($get) return $get->decl; else return "";
 }

 // getMagnitude returns the magnitude of the object
 function getDsObjectMagnitude($name)
 {
  $db = new database;
  $db->login();
  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
  $mag = $get->mag;
  $db->logout();
  return $mag;
 }
 
 // getSBObj returns the SBObj of the object
 function getSBObj($name)
 {
  $db = new database;
  $db->login();
  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
  $SBObj = $get->SBObj;
  $db->logout();
  return $SBObj;
 }

 // getObjects returns an array with the names of all objects
 function getObjects()
 {
  $db = new database;
  $db->login();
  $sql = "SELECT objects.name FROM objects";
  $run = mysql_query($sql) or die(mysql_error());
  while($get = mysql_fetch_object($run))
    $obs[] = $get->name;
  $db->logout();

  return $obs;
 }

 function sortObjects($result, $sort, $reverse=false)
 {
  if(!$result ||count($result)<2)
	  return $result;
  $sortmethod = "strnatcasecmp";
	$k=0;
  if($sort == "name")      
    while(list($key, $value) = each($result))
	    $result3[$value[0].$value[4]] = $value;
  if($sort == "type")		  
    while(list($key, $value) = each($result))
      $result3[$value[1].$value[4]] = $value;
  if($sort == "con")
    while(list($key, $value) = each($result))
	    $result3[$value[2].$value[4]] = $value;
  if($sort == "seen")
    while(list($key, $value) = each($result))
	    $result3[$value[3].$value[4]] = $value;
  if($sort == "showname")
    while(list($key, $value) = each($result))
      $result3[$value[4]] = $value;
  if($sort == "mag")
    while(list($key, $value) = each($result))
      $result3[sprintf("%.2f", $value[5]).$value[4]] = $value;
  if($sort == "subr")
    while(list($key, $value) = each($result))
      $result3[sprintf("%.2f", $value[6]).$value[4]] = $value;
  if($sort == "ra")    
    while(list($key, $value) = each($result))
      $result3[$value[7].$value[4]] = $value;
  if($sort == "decl")   
    while(list($key, $value) = each($result))
     $result3[$value[8].$value[4]] = $value;
  if($sort == "urano") 
  {
    $cnt = 0;
    while(list($key, $value) = each($result))
		{
      $result3[$value[9].sprintf("%05d", $cnt) / 10000] = $value;
			$cnt = $cnt + 1;
		}
	}
  if($sort == "urano_new")
  {
    $cnt = 0;
    while(list($key, $value) = each($result))
		{
      $result3[$value[10].sprintf("%05d", $cnt) / 10000] = $value;
			$cnt = $cnt + 1;
		}
	}
  if($sort == "sky")    
  {
    $cnt = 0;
    while(list($key, $value) = each($result))
		{
      $result3[$value[11].sprintf("%05d", $cnt) / 10000] = $value;
			$cnt = $cnt + 1;
		}
	}
  if($sort == "millenium")
    while(list($key, $value) = each($result))
      $result3[$value[12].$value[4]] = $value;
  if($sort == "taki")
  {
    $cnt = 0;
    while(list($key, $value) = each($result))
		{
      $result3[$value[13].sprintf("%05d", $cnt) / 10000] = $value;
			$cnt = $cnt + 1;
		}
	}
  if($sort == "psa")
  {
    $cnt = 0;
    while(list($key, $value) = each($result))
		{
      $result3[$value[14].sprintf("%05d", $cnt) / 10000] = $value;
			$cnt = $cnt + 1;
		}
	}
  if($sort == "torresB")
  {
    $cnt = 0;
    while(list($key, $value) = each($result))
		{
		$result3[$value[15].sprintf("%05d", $cnt) / 10000] = $value;
			$cnt = $cnt + 1;
		}
	}
  if($sort == "torresBC")
  {
    $cnt = 0;
    while(list($key, $value) = each($result))
		{
		$result3[$value[16].sprintf("%05d", $cnt) / 10000] = $value;
			$cnt = $cnt + 1;
		}
	}
  if($sort == "torresC")
  {
    $cnt = 0;
    while(list($key, $value) = each($result))
		{
		// UPDATE!!
		$result3[$value[17].sprintf("%05d", $cnt) / 10000] = $value;
			$cnt = $cnt + 1;
		}
	}
	
  if($sort == "contrast")
  {
    $sortmethod = array( new contrastcompare( $reverse ), "compare" );
    while(list($key, $value) = each($result))
    {
      if (strcmp($value[21], "-") == 0)
      {
        $result3["-/".$value[4]] = $value;
      }
      else
      {
       $result3[sprintf("%.2f", $value[21])."/".$value[4]] = $value;
      }
    }
  }
  if($sort == "magnification")
  {
    $cnt = 0;
    while(list($key, $value) = each($result))
		{
			if($value[21] == "-")
			{
				$result3["-".sprintf("%05d", $cnt) / 10000] = $value;
			} else {
      	$result3[$value[25].sprintf("%05d", $cnt) / 10000] = $value;
			}
			$cnt = $cnt + 1;
		}
	}
  if($sort == "objectplace")     
    while(list($key, $value) = each($result))
      $result3[$value[24].$value[4]] = $value;
  uksort($result3, $sortmethod);
  $result=array();
  while(list($key, $value) = each($result3))
    $result[]=$value;

  if($sort != "contrast" && $reverse == true)
  {
   $result = array_reverse($result, false);
  }
  return $result;
 }

 function prepareObjectsContrast($doLogin=false)
 {
   include_once "contrast.php";
   $contrastObj = new Contrast;
 
	 if($doLogin)
	 {
     $db = new database;
     $db->login();
	 }

   if(!array_key_exists('LTC',$_SESSION)||(!$_SESSION['LTC']))
		$_SESSION['LTC'] = array(array(4, -0.3769, -1.8064, -2.3368, -2.4601, -2.5469, -2.5610, -2.5660), 
                             array(5, -0.3315, -1.7747, -2.3337, -2.4608, -2.5465, -2.5607, -2.5658),
                             array(6, -0.2682, -1.7345, -2.3310, -2.4605, -2.5467, -2.5608, -2.5658),
                             array(7, -0.1982, -1.6851, -2.3140, -2.4572, -2.5481, -2.5615, -2.5665),
                             array(8, -0.1238, -1.6252, -2.2791, -2.4462, -2.5463, -2.5597, -2.5646),
                             array(9, -0.0424, -1.5529, -2.2297, -2.4214, -2.5343, -2.5501, -2.5552),
                             array(10, 0.0498, -1.4655, -2.1659, -2.3763, -2.5047, -2.5269, -2.5333),
                             array(11, 0.1596, -1.3581, -2.0810, -2.3036, -2.4499, -2.4823, -2.4937),
                             array(12, 0.2934, -1.2256, -1.9674, -2.1965, -2.3631, -2.4092, -2.4318),
                             array(13, 0.4557, -1.0673, -1.8186, -2.0531, -2.2445, -2.3083, -2.3491),
                             array(14, 0.6500, -0.8841, -1.6292, -1.8741, -2.0989, -2.1848, -2.2505),
                             array(15, 0.8808, -0.6687, -1.3967, -1.6611, -1.9284, -2.0411, -2.1375),
                             array(16, 1.1558, -0.3952, -1.1264, -1.4176, -1.7300, -1.8727, -2.0034),
                             array(17, 1.4822, -0.0419, -0.8243, -1.1475, -1.5021, -1.6768, -1.8420),
                             array(18, 1.8559, 0.3458, -0.4924, -0.8561, -1.2661, -1.4721, -1.6624),
                             array(19, 2.2669, 0.6960, -0.1315, -0.5510, -1.0562, -1.2892, -1.4827),
                             array(20, 2.6760, 1.0880, 0.2060, -0.3210, -0.8800, -1.1370, -1.3620),
                             array(21, 2.7766, 1.2065, 0.3467, -0.1377, -0.7361, -0.9964, -1.2439),
                             array(22, 2.9304, 1.3821, 0.5353, 0.0328, -0.5605, -0.8606, -1.1187),
                             array(23, 3.1634, 1.6107, 0.7708, 0.2531, -0.3895, -0.7030, -0.9681),
                             array(24, 3.4643, 1.9034, 1.0338, 0.4943, -0.2033, -0.5259, -0.8288),
                             array(25, 3.8211, 2.2564, 1.3265, 0.7605, 0.0172, -0.2992, -0.6394),
                             array(26, 4.2210, 2.6320, 1.6990, 1.1320, 0.2860, -0.0510, -0.4080),
                             array(27, 4.6100, 3.0660, 2.1320, 1.5850, 0.6520, 0.2410, -0.1210));

    if(!array_key_exists('LTCSize',$_SESSION)||(!$_SESSION['LTCSize']))
      $_SESSION['LTCSize'] = 24;
    if(!array_key_exists('angleSize',$_SESSION)||(!$_SESSION['angleSize']))
      $_SESSION['angleSize'] = 7;
    if(!array_key_exists('angle',$_SESSION)||(!$_SESSION['angle']))
      $_SESSION['angle'] = array(-0.2255, 0.5563, 0.9859, 1.260, 1.742, 2.083, 2.556);
    $popup="";
 		if(!(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id'])))
		  $popup = LangContrastNotLoggedIn;
    else
		{
      $sql5 = "SELECT stdlocation, stdtelescope from observers where id = \"" . $_SESSION['deepskylog_id'] . "\"";
      $run5 = mysql_query($sql5) or die(mysql_error());
      $get5 = mysql_fetch_object($run5);
      if ($get5->stdlocation==0)
        $popup = LangContrastNoStandardLocation;
      elseif($get5->stdtelescope==0)
				$popup = LangContrastNoStandardInstrument;
			else
			{
        // Check for eyepieces or a fixed magnification
        $sql6 = "SELECT fixedMagnification, diameter, fd from instruments where id = \"" . $get5->stdtelescope . "\"";
        $run6 = mysql_query($sql6) or die(mysql_error());
        $get6 = mysql_fetch_object($run6);

        if ($get6->fd == 0 && $get6->fixedMagnification == 0)
        {
					// We are not setting $magnifications
					$magnifications = array();
				}
        else if ($get6->fixedMagnification == 0)
        {
	        $sql7 = "SELECT focalLength, name, apparentFOV, maxFocalLength from eyepieces where observer = \"" . $_SESSION['deepskylog_id'] . "\"";
  	      $run7 = mysql_query($sql7) or die(mysql_error());

				  while($get7 = mysql_fetch_object($run7))
					{
						if ($get7->maxFocalLength > 0.0)
						{
							$fRange = $get7->maxFocalLength - $get7->focalLength;
							for ($i = 0;$i < 5;$i++)
							{
								$focalLengthEyepiece = $get7->focalLength + $i * $fRange / 5.0;
								$magnifications[] = $get6->diameter * $get6->fd / $focalLengthEyepiece;
 						  	$magnificationsName[] = $get7->name . " - " . $focalLengthEyepiece . "mm";
								$fov[] = 1.0 / ($get6->diameter * $get6->fd / $focalLengthEyepiece) * 60.0 * $get7->apparentFOV;
							}
						}
						else
						{
							$magnifications[] = $get6->diameter * $get6->fd / $get7->focalLength;
 					  	$magnificationsName[] = $get7->name;
							$fov[] = 1.0 / ($get6->diameter * $get6->fd / $get7->focalLength) * 60.0 * $get7->apparentFOV;
						}
  				}

	        $sql8 = "SELECT name, factor from lenses where observer = \"" . $_SESSION['deepskylog_id'] . "\"";
  	      $run8 = mysql_query($sql8) or die(mysql_error());

					$origmagnifications = $magnifications;
					$origmagnificationsName = $magnificationsName;
					$origfov = $fov;

				  while($get8 = mysql_fetch_object($run8))
					{
						$name = $get8->name;
						$factor = $get8->factor;

						for ($i = 0;$i < count($origmagnifications);$i++)
						{
							$magnifications[] = $origmagnifications[$i] * $factor;
							$magnificationsName[] = $origmagnificationsName[$i] . ", " . $name;
							$fov[] = $fov[$i] / $factor;
						}
					}
        }
        else
        {
					$magnifications[] = $get6->fixedMagnification;
					$magnificationsName[] = "";
					$fov[] = "";
        }

        $_SESSION['magnifications'] = $magnifications; 
        $_SESSION['magnificationsName'] = $magnificationsName; 
				$_SESSION['fov'] = $fov;

				if (count($magnifications) == 0)
				{
					$popup = LangContrastNoEyepiece;
				}
				else
        {
  				$sql6 = "SELECT limitingMagnitude, skyBackground, name from locations where id = \"" . $get5->stdlocation . "\"";
      	  $run6 = mysql_query($sql6) or die(mysql_error());
        	$get6 = mysql_fetch_object($run6);
    	    if(($get6->limitingMagnitude < -900)&&($get6->skyBackground < -900))
      	    $popup = LangContrastNoLimMag;
					else
      	  {
        	  if($get6->skyBackground < -900)
          	  $_SESSION['initBB'] = $contrastObj->calculateSkyBackgroundFromLimitingMagnitude($get6->limitingMagnitude);
        	  else
          	  $_SESSION['initBB'] = $get6->skyBackground;
  	        $sql7 = "SELECT diameter, name from instruments where id = \"" . $get5->stdtelescope . "\"";
    	      $run7 = mysql_query($sql7) or die(mysql_error());
      	    $get7 = mysql_fetch_object($run7);
        	  $_SESSION['aperMm'] = $get7->diameter;
						$_SESSION['aperIn'] = $_SESSION['aperMm'] / 25.4;
					//$scopeTrans = 0.8;
          //$pupil = 7.5;
          //$nakedEyeMag = 8.5;
          //Faintest star
          //$limitMag = $nakedEyeMag + 2.5 * log10( $_SESSION['aperMm'] * $_SESSION['aperMm'] * $scopeTrans / ($pupil * $pupil));
	        // Minimum useful magnification
       			$_SESSION['minX'] = $_SESSION['aperIn'] * 3.375 + 0.5;
						$_SESSION['SBB1'] = $_SESSION['initBB'] - (5 * log10(2.833 * 	$_SESSION['aperIn']));
						$_SESSION['SBB2'] = -2.5 * log10( (2.833 * $_SESSION['aperIn']) * (2.833 * $_SESSION['aperIn']));

						$_SESSION['telescope'] = $get7->name;
						$_SESSION['location'] = $get6->name;
					}
        }
	   }
	 }
	 if($doLogin)
	   $db->logout();
   return $popup;
 }
 
 function getSeen($object)
 {
   $db = new database;
   $db->login();

   $seen='-';
   $sql = "SELECT COUNT(observations.id) As ObsCnt FROM observations WHERE objectname = \"" . $object . "\" AND visibility != 7 ";
   $run = mysql_query($sql) or die(mysql_error());
   $get2 = mysql_fetch_object($run);
   if ($get2->ObsCnt)
   {
     $seen="X(" . $get2->ObsCnt . ")";
     if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'] && ($_SESSION['deepskylog_id'] != ""))
     {
       $sql = "SELECT COUNT(observations.id) As PersObsCnt FROM observations WHERE objectname = \"" . $object . "\" AND observerid = \"" . $_SESSION['deepskylog_id'] . "\" AND visibility != 7";
       $run = mysql_query($sql) or die(mysql_error());
       $get3 = mysql_fetch_object($run);
       if ($get3->PersObsCnt)
         $seen="Y(" . $get2->ObsCnt . "/" . $get3->PersObsCnt . ")";
     }
	 }
	 $db->logout();
	 return $seen;
 }
 

 function getObjectVisibilities($obs)
 {
  $db = new database;
  $db->login();

  include_once "contrast.php";
  $contrastObj = new Contrast;

	$popup='';
  $popupT = $this->prepareObjectsContrast();
  $result2=$obs;

	$obscnt=sizeof($obs);
  if($obscnt > 0)
  {
    $j=0;
		reset($obs);

    while(list($key, $value) = each($obs))
    {
      $contrast = "-";
      $contype = "";
		  $contrastcalc1="";
		
      if($popupT)
		    $popup=$popupT;
		  else
      {
        $magni = $result2[$j][5];
				$subrobj = $result2[$j][26];
        if($magni>90)
          $popup = LangContrastNoMagnitude;
        else 
		    {
          $diam1 = $result2[$j][18];
          $diam1 = $diam1 / 60.0;

          if($diam1==0)
            $popup = LangContrastNoDiameter;
          else
          {
            $diam2 = $result2[$j][19];
            $diam2 = $diam2 / 60.0;
            if ($diam2 == 0)
              $diam2 = $diam1;
            $contrastCalc = $contrastObj->calculateContrast($magni, $subrobj, $diam1,$diam2);
            if ($contrastCalc[0] < -0.2) 
						$popup = $result2[$j][0] . LangContrastNotVisible . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
            else if ($contrastCalc[0] < 0.1)
						$popup = LangContrastQuestionable . $result2[$j][0] . LangContrastQuestionableB . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
            else if ($contrastCalc[0] < 0.35)
						$popup = $result2[$j][0] . LangContrastDifficult . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
            else if ($contrastCalc[0] < 0.5)
						$popup = $result2[$j][0] . LangContrastQuiteDifficult . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
            else if ($contrastCalc[0] < 1.0)
						$popup = $result2[$j][0] . LangContrastEasy . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
            else
						$popup = $result2[$j][0] . LangContrastVeryEasy . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
				      
           if ($contrastCalc[2] == "")
						$contrastcalc1=((int)$contrastCalc[1]) . "x";
					else
						$contrastcalc1=((int)$contrastCalc[1]) . "x - " . $contrastCalc[2];

            $contrast = sprintf("%.2f",       $contrastCalc[0]);
    	      if ($contrastCalc[0] < -0.2)      $contype = "typeNotVisible";
            else if ($contrastCalc[0] < 0.1)  $contype = "typeQuestionable";
            else if ($contrastCalc[0] < 0.35) $contype = "typeDifficult";
            else if ($contrastCalc[0] < 0.5)  $contype = "typeQuiteDifficult";
            else if ($contrastCalc[0] < 1.0)  $contype = "typeEasy";
            else                              $contype = "typeVeryEasy";
          }
        }
      }


      $result2[$j][21] = $contrast;
      $result2[$j][22] = $contype;
      $result2[$j][23] = $popup;
      $result2[$j][25] = $contrastcalc1;

      $j++;		
    }
	}
	$obs = $result2;
  $db->logout();

  return $obs; 
 }
 
 function getSeenObjectDetails($obs, $seen="D")
 {
//  include_once "locations.php";
//  $locations = new Locations;

  include_once "observers.php";
  $observer = new Observers;
  include_once "instruments.php";
  $instrumentObj = new Instruments;
  include_once "contrast.php";
  $contrastObj = new Contrast;
  $db = new database;
  $db->login();

  $result2=array();
	$obscnt=sizeof($obs);
  if($obscnt > 0)
  {
    $j=0;
		reset($obs);

    while(list($key, $value) = each($obs))
    {
		  $object=$value[1];
      $seentype = "-";
      $sql = "SELECT COUNT(observations.id) As ObsCnt FROM observations WHERE objectname = \"" . $object . "\" AND visibility != 7 ";
      $run = mysql_query($sql) or die(mysql_error());
      $get2 = mysql_fetch_object($run);
      if ($get2->ObsCnt)
      {
        $seentype="X";
        if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'] && ($_SESSION['deepskylog_id'] != ""))
        {
          $user = $_SESSION['deepskylog_id'];
          $sql = "SELECT COUNT(observations.id) As PersObsCnt FROM observations WHERE objectname = \"" . $object . "\" AND observerid = \"$user\" AND visibility != 7";
          $run = mysql_query($sql) or die(mysql_error());
          $get3 = mysql_fetch_object($run);
          if ($get3->PersObsCnt)
				    $seentype="Y";
        }
      }
			if(($seen == "D") ||
			   (strpos(" " . $seen, $seentype)))
		  {
  	    $result2[$j][0] = $value[1];
        $sql = "SELECT * FROM objects WHERE name = \"". $value[1] . "\"";
        $run = mysql_query($sql) or die(mysql_error());
        $get = mysql_fetch_object($run);
        if($get)
				{
  				$type = $get->type;
          $con = $get->con;
          $result2[$j][1] =  $type;
          $result2[$j][2] =  $con;
          $result2[$j][3] = "-";
  	      if($seentype == "X") $result2[$j][3] = "X(" . $get2->ObsCnt . ")";
          if($seentype == "Y") $result2[$j][3] = "Y(" . $get2->ObsCnt . "/" . $get3->PersObsCnt . ")";
          $result2[$j][4] =  $key;
  	      $result2[$j][5] =  $get->mag;
  	      $result2[$j][6] =  $get->subr;
  	      $result2[$j][7] =  $get->ra;
  	      $result2[$j][8] =  $get->decl;
  	      $result2[$j][9] =  $get->urano;
  	      $result2[$j][10] = $get->urano_new;
  	      $result2[$j][11] = $get->sky;
  	      $result2[$j][12] = $get->millenium;
  	      $result2[$j][13] = $get->taki;
          $result2[$j][14] = $get->psa;
          $result2[$j][15] = $get->torresB;
          $result2[$j][16] = $get->torresBC;
          $result2[$j][17] = $get->torresC;
  	      $result2[$j][18] = $get->diam1;
  	      $result2[$j][19] = $get->diam2;
  	      $result2[$j][20] = $get->pa;
          $result2[$j][24] = $value[0]; 
          $result2[$j][26] = $get->SBObj; 
          $result2[$j][27] = $get->description;
		    }
        $j++;		
      }
    }
	}
	$obs=$result2;
  $obs=$this->getObjectVisibilities($obs);
  $db->logout();
  return $obs;
 }

 function getPartOfObjects($obs)
 {
   $db = new database;
   $poobs=array();
   $i=0;
	 while(list($key,$value)=each($obs))
   {
     $db->login();
     $poobs[]=$value;
		 $sql2 = "SELECT DISTINCT (objectpartof.objectname) AS name, " .
	                            "CONCAT((\"" . $value[0] . "\"), \"-\", (objectpartof.objectname)) As showname  " . 
	           "FROM objectpartof " . 
				     "WHERE objectpartof.partofname = \"" . $value[0] . "\";";
		 $run = mysql_query($sql2) or die(mysql_error());
		 $temp=array();
		 while($get = mysql_fetch_object($run))
	     $temp[$get->showname]=array($i++,$get->name);
		 if(count($temp)>0)
     {
 		   $temp=$this->getSeenObjectDetails($temp);
       $poobs=array_merge($poobs,$temp);
     }
     $db->logout();
	 }
   return $poobs;
 }

 // getObjectFromQuery returns an array with the names of all objects where
 // the queries are defined in an array.
 // An example of an array :  
 //  $q = array("name" => "NGC", "type" => "GALXY", "constellation" => "AND", 
 //             "minmag" => "12.0", "maxmag" => "14.0", "minsubr" => "13.0", 
 //             "maxsubr" => "14.0", "minra" => "0.3", "maxra" => "0.9", 
 //             "mindecl" => "24.0", "maxdecl" => "30.0", "urano" => "111", 
 // 		        "uranonew" => "111", "sky" => "11", "msa" => "222",
 //             "taki" => "11", "psa" => "12", "torresB" => "11", "torresBC" => "13",
 //             "torresC" => "31", "mindiam1" => "12.2", "maxdiam1" => "13.2", 
 // 		"mindiam2" => "11.1", "maxdiam2" => "22.2", "inList" => "Public: Edge-ons", "notInList" => "My observed Edge-ons");
 function getObjectFromQuery($queries, $exact = 0, $seen="D", $partof = 0)
 {

  include_once "lists.php";
  $list = new Lists;

  $db = new database;
  $db->login();
  
  $obs=NULL;
  $sql = "";
  $sqland = "";
  $sql1 = "SELECT DISTINCT (objectnames.objectname) AS name, " .
                          "(objectnames.altname) AS showname " . 
          "FROM objectnames " . 
          "JOIN objects ON objects.name = objectnames.objectname ";
  $sql2 = "SELECT DISTINCT (objectpartof.objectname) AS name, " .
                   "CONCAT((objectnames.altname), \"-\", (objectpartof.objectname)) As showname  " . 
          "FROM objectpartof " . 
          "JOIN objects ON (objects.name = objectpartof.objectname) " .
          "JOIN objectnames ON (objectnames.objectname = objectpartof.partofname) ";
  if(array_key_exists('inList',$queries) && $queries['inList'])
  {
	  if(substr($queries['inList'],0,7)=="Public:")
		{
      $sql1 .= "JOIN observerobjectlist AS A " .
	             "ON A.objectname = objects.name ";
      $sql2 .= "JOIN observerobjectlist AS A " .
	             "ON A.objectname = objects.name ";
		  $sqland .= "AND A.listname = \"" . $queries['inList'] . "\" AND A.objectname <>\"\" ";
	  }
		elseif(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
		{
      $sql1 .= "JOIN observerobjectlist AS A " .
	             "ON A.objectname = objects.name ";
      $sql2 .= "JOIN observerobjectlist AS A " .
	             "ON A.objectname = objects.name ";
	    $sqland .= "AND A.observerid = \"" . $_SESSION['deepskylog_id'] . "\" AND A.listname = \"" . $queries['inList'] . "\" AND A.objectname <>\"\" ";
		}
  }

/*
  if(array_key_exists('notInList',$queries) && $queries['notInList'])
  {
	  if(substr($queries['notInList'],0,7)=="Public:")
    {
		  $sql1 .= "LEFT JOIN observerobjectlist AS B " .
	             "ON B.objectname = objects.name ";
      $sql2 .= "LEFT JOIN observerobjectlist AS B " .
	             "ON B.objectname = objects.name ";
		  $sqland .= "AND B.listname = \"" . $queries['notInList'] . "\" AND B.objectname IS NULL ";
	  }
		elseif(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
    {
		  $sql1 .= "LEFT JOIN observerobjectlist AS B " .
	             "ON B.objectname = objects.name ";
      $sql2 .= "LEFT JOIN observerobjectlist AS B " .
	             "ON B.objectname = objects.name ";
	    $sqland .= "AND B.observerid = \"" . $_SESSION['deepskylog_id'] . "\" AND B.listname = \"" . $queries['notInList'] . "\" AND B.objectname IS NULL ";
    }
	} 
*/
	
	$sql1 .= "WHERE ";
	$sql2 .= "WHERE ";

  if (array_key_exists('name',$queries) && $queries["name"] != "")
    if ($exact == 0)
      $sqland = $sqland . " AND (objectnames.catalog = \"" . $queries["name"] . "\")"; 
    elseif ($exact == 1)
      $sqland = $sqland . " AND (UPPER(objectnames.altname) like \"" . strtoupper($queries["name"]) . "\")";
    else
      $sqland = $sqland . " AND (UPPER(objectnames.altname) = \"" . strtoupper($queries["name"]) . "\")";
  if (array_key_exists('type',$queries) && ($queries["type"] != ""))
    $sqland = $sqland . " and objects.type = \"" . $queries["type"] . "\"";
  if (array_key_exists('constellation',$queries) && ($queries["constellation"] != ""))
    $sqland = $sqland . " AND objects.con = \"" . $queries["constellation"] . "\"";
  if (array_key_exists('minmag',$queries) && (strcmp($queries["minmag"], "") != 0))
    $sqland = $sqland . " AND (objects.mag > \"" . $queries["minmag"] . "\" or objects.mag like \"" . $queries["minmag"] . "\")";
  if (array_key_exists('maxmag',$queries) && (strcmp($queries["maxmag"], "") != 0))
    $sqland = $sqland . " AND (objects.mag < \"" . $queries["maxmag"] . "\" or objects.mag like \"" . $queries["maxmag"] . "\")";
  if (array_key_exists('minsubr',$queries) && (strcmp($queries["minsubr"], "") != 0))
    $sqland = $sqland . " AND objects.subr >= \"" . $queries["minsubr"] . "\"";
  if (array_key_exists('maxsubr',$queries) && (strcmp($queries["maxsubr"], "") != 0))
    $sqland = $sqland . " AND objects.subr <= \"" . $queries["maxsubr"] . "\"";
  if (array_key_exists('minra',$queries) && (strcmp($queries["minra"], "") != 0))
    $sqland = $sqland . " AND (objects.ra > \"" . $queries["minra"] . "\" or objects.ra like \"" . $queries["minra"] . "\")";
  if (array_key_exists('maxra',$queries) && (strcmp($queries["maxra"], "") != 0))
    $sqland = $sqland . " AND (objects.ra <= \"" . $queries["maxra"] . "\" or objects.ra like \"" . $queries["maxra"] . "\")";
  if (array_key_exists('mindecl',$queries) && (strcmp($queries["mindecl"], "") != 0))
    $sqland = $sqland . " AND objects.decl >= \"" . $queries["mindecl"] . "\"";
  if (array_key_exists('maxdecl',$queries) && (strcmp($queries["maxdecl"], "") != 0))
    $sqland = $sqland . " AND objects.decl <= \"" . $queries["maxdecl"] . "\"";
  if(array_key_exists('mindiam1',$queries) && (strcmp($queries["mindiam1"], "") != 0))
    $sqland  = $sqland . " AND (objects.diam1 > \"" . $queries["mindiam1"] . "\" or objects.diam1 like \"" . $queries["mindiam1"] . "\")";
  if(array_key_exists('maxdiam1',$queries) && (strcmp($queries["maxdiam1"], "") != 0))
    $sqland = $sqland . " AND (objects.diam1 <= \"" . $queries["maxdiam1"] . "\" or objects.diam1 like \"" . $queries["maxdiam1"] . "\")";
  if(array_key_exists('mindiam2',$queries) && (strcmp($queries["mindiam2"], "") != 0))
    $sqland = $sqland . " AND (objects.diam2 > \"" . $queries["mindiam2"] . "\" or objects.diam2 like \"" . $queries["mindiam2"] . "\")";
  if(array_key_exists('maxdiam2',$queries) && (strcmp($queries["maxdiam2"], "") != 0))
    $sqland = $sqland . " AND(objects.diam2 <= \"" . $queries["maxdiam2"] . "\" or objects.diam2 like \"" . $queries["maxdiam2"] . "\")";
  if(array_key_exists('atlas',$queries) && ($queries["atlas"] != "") &&
	   array_key_exists('atlasPageNumber',$queries) && ($queries["atlasPageNumber"] != ""))
    $sqland = $sqland . " AND objects." . $queries["atlas"] . " = \"" . $queries["atlasPageNumber"] . "\"";
	$sqland = substr($sqland, 4);
	
	if(trim($sqland)=='') $sqland = " (objectnames.altname like \"%\")";
	
  if($partof)
    $sql = "(" . $sql1. $sqland . ") UNION (" . $sql2 . $sqland . ")";
  else
    $sql = $sql1 . $sqland;		
  $run = mysql_query($sql) or die(mysql_error());
  $db->logout();
  $obs=array();
  $i=0;

  if (array_key_exists('name',$queries) && $queries["name"] != "")
	{
    while($get = mysql_fetch_object($run))
      if($get->showname==$get->name)
      {  
        if(!array_key_exists($get->showname, $obs))
   	      $obs[$get->showname] = array($i++,$get->name);		
      }
  		else
  		  if(!array_key_exists($get->showname." (".$get->name.")", $obs))
   	      $obs[$get->showname." (".$get->name.")"] = array($i++,$get->name);
  }
	else
    while($get = mysql_fetch_object($run))
      if(!array_key_exists($get->name, $obs))
   	    $obs[$get->name] = array($i++,$get->name);		
  			
  $obs = $this->getSeenObjectDetails($obs, $seen);
  
  if(array_key_exists('maxContrast', $queries) && ($queries["maxContrast"] != ""))
  {
    $new_obs = Array(Array());
    $cnt = 0;
    for($i = 0;$i < count($obs);$i++)
      if ($obs[$i][21] <= $queries["maxContrast"])
        $new_obs[$cnt++] = $obs[$i];
    $obs = Array();
    if ($cnt > 0)
      $obs = $new_obs;
  } 

  if(array_key_exists('minContrast', $queries) && ($queries["minContrast"] != ""))
  {
    $new_obs = Array(Array());
    $cnt = 0;
    for($i = 0;$i < count($obs);$i++)
      if ($obs[$i][21] >= $queries["minContrast"])
        $new_obs[$cnt++] = $obs[$i];
    $obs = Array();
    if ($cnt > 0)
      $obs = $new_obs;
  }        
 return $obs;
 }

 // getSelectedObjects returns an array with the names of all objects where the 
 // databasefield has the given value.
 function getSelectedObjects($dbfield, $value)
 {
  $db = new database;
  $db->login();
  if ($dbfield == "name")
  {
   $sql = "SELECT * FROM objects INNER JOIN objectnames WHERE objectname.catalog like \"$value%\"";
  }
  else
  {
   $sql = "SELECT * FROM objects where $dbfield like \"$value%\"";
  }  
  $run = mysql_query($sql) or die(mysql_error());
  while($get = mysql_fetch_object($run))
  {
   $obs[] = $get->name;
  }
  $db->logout();
  return $obs;
 }

function getObjectsFromCatalog($cat)
 {
  $db = new database;
  $db->login();
  $sql = "SELECT DISTINCT objectnames.objectname, objectnames.catindex, objectnames.altname " .
	       "FROM objectnames WHERE objectnames.catalog = \"$cat\"";
  $run = mysql_query($sql) or die(mysql_error());
  $obs=array();
	while($get = mysql_fetch_object($run))
   $obs[$get->catindex] = array($get->objectname, $get->altname);
	uksort($obs,"strnatcasecmp");
  $db->logout();
  return $obs;
 }


 // getExactObject returns an array with the name of the object where the
 // databasefield has the given name.
 function getExactDsObject($value, $cat='', $catindex='')
 {
  $db = new database;
  $db->login();
	$value2=trim($value);
	$value=strtoupper(trim($value));
  if($value!='')
    $sql = "SELECT objectnames.objectname FROM objectnames " .
		  	   "WHERE UPPER(altname) = \"$value\" " .
					 "OR altname = \"$value2\"";
	else
	{
	  $catindex=ucwords($catindex);
    $sql = "SELECT objectnames.objectname FROM objectnames " .
		       "WHERE objectnames.catalog = \"$cat\" AND objectnames.catindex = \"$catindex\"";
	}
	$run = mysql_query($sql) or die(mysql_error());
	$obs=array();
  while($get = mysql_fetch_object($run))
  {
   $obs[] = $get->objectname;
  }
  $db->logout();
  return $obs;
 }

 // getSurfaceBrightness returns the surface brightness of the object
 function getSurfaceBrightness($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $sb = $get->subr;

  $db->logout();

  return $sb;
 }

 // getSize returns the size of the object
 function getSize($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $diam1 = $get->diam1;
  $diam2 = $get->diam2;
  $size = $this->calculateSize($diam1, $diam2);
  $db->logout();

  return $size;
 }

 // getDiam1 returns the size of the object
 function getDiam1($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $diam1 = $get->diam1;
  $db->logout();

  return $diam1;
 }

 // getDiam2 returns the size of the object
 function getDiam2($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $diam2 = $get->diam2;
  $db->logout();

  return $diam2;
 }

 // getSortedObjects returns an array with the names of all objects, sorted by 
 // the column specified in $sort
 function getSortedObjects($sort)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects ORDER BY $sort";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $obs[] = $get->name;
  }

  if ($sort == "name")
  {
   natcasesort($obs);
  }

  $db->logout();

  return $obs;
 }

 // getConstellations returns a list of all different constellations
 function getConstellations()
 {
  include_once "setup/vars.php";

  $db = new database;
  $db->login();

  $sql = "SELECT DISTINCT con FROM objects";
  $run = mysql_query($sql) or die(mysql_error());
  //$get = mysql_fetch_object($run);

  while($get = mysql_fetch_object($run))
  {
   $con[] = $get->con;
  }
  $db->logout();

  $ret = $this->my_array_unique($con);
  sort($ret);
  reset($ret);

  return $ret;
 }

 // getCatalogues returns a list of all different catalogues
 function getCatalogues()
 {
  $db = new database;
  $db->login();

  $sql = "SELECT DISTINCT objectnames.catalog FROM objectnames";
  $run = mysql_query($sql) or die(mysql_error());
  //$get = mysql_fetch_object($run);

  while($get = mysql_fetch_object($run))
  {
//   $tmp = $get->catalog;
//   $cat = explode (" ", $tmp);

//   if(($cat[0] != "IC") && ($cat[0] != "M") && ($cat[0] != "NGC") && ($cat[0] != "") &&
//	    ($cat[0] != "Caldwell") && ($cat[0] != "H400")  && ($cat[0] != "HII")) 
//     $cats[] = $cat[0];
    $cats[]=$get->catalog;
  }
  $db->logout();

  $ret = $this->my_array_unique($cats);

  natcasesort($ret);
  reset($ret);

  array_unshift($ret, "M", "NGC", "Caldwell", "H400", "HII", "IC");

  return $ret;
 }

 // getTypes returns a list of all different types
 function getTypes()
 {
  $db = new database;
  $db->login();

  $sql = "SELECT DISTINCT type FROM objects";
  $run = mysql_query($sql) or die(mysql_error());
  //$get = mysql_fetch_object($run);

  while($get = mysql_fetch_object($run))
  {
   $type[] = $get->type;
  }
  $db->logout();

  $ret = $this->my_array_unique($type);

  sort($ret);
  reset($ret);

  return $ret;
 }

 // my_array_unique returns a unique array, where the keys increment.
 function my_array_unique($somearray)
 { 
  $tmparr = array_unique($somearray); 
  $i=0; 
  foreach ($tmparr as $v) 
  { 
   $newarr[$i] = $v; 
   $i++; 
  } 
  return $newarr; 
 } 
 
 function getAlternativeNames($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT objectnames.catalog, objectnames.catindex ".
	       "FROM objectnames WHERE objectnames.objectname = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  $altnames=array();
  while($get = mysql_fetch_object($run))
	{
	  $altnames[]=$get->catalog . " " . $get->catindex;
	}
  $db->logout();
  return $altnames;
 }
 
function getContainsNames($name)
 {
  $db = new database;
  $db->login();
  $sql = "SELECT objectpartof.objectname ".
	       "FROM objectpartof WHERE objectpartof.partofname = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  $containsnames=array();
  while($get = mysql_fetch_object($run))
	{
	  $containsnames[]=$get->objectname;
	}
  $db->logout();
  return $containsnames;
 }
 
function getPartOfNames($name)
 {
  $db = new database;
  $db->login();
  $sql = "SELECT objectpartof.partofname ".
	       "FROM objectpartof WHERE objectpartof.objectname = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  $partofnames=array();
  while($get = mysql_fetch_object($run))
	{
	  $partofnames[]=$get->partofname;
	}
  $db->logout();
  return $partofnames;
 }

 // getPositionAngle returns the position angle of the object
 function getPositionAngle($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $pa = $get->pa;

  $db->logout();

  return $pa;
 }
 
 // getCatalogs returns the catalogs of the object
 function getCatalogs($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $cats = $get->catalogs;

  $db->logout();

  return $cats;
 }

 // getUranometriaPage returns the old uranometriapage of the object
 function getUranometriaPage($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $urano = $get->urano;

  $db->logout();

  return $urano;
 }

 // calculateUranometriaPage calculates the old uranometriapage of the object
 function calculateUranometriaPage($ra, $decl)
 {
  $urano = 0;

  /* Page from uranometria */
  /* 90 to 85 */
  if ($decl >= 85)
  {
   if ($ra < 12)
   {
    $urano = 1;
   }
   else
   {
    $urano = 2;
   }
  }

  /* 84 to 73 */
  else if ($decl >= 73)
  {
   if (($ra >= 1) && ($ra < 23))
   {
    $urano = (int)$ra - 1;
    $urano = $urano / 2;
    $urano = $urano + 4;
   }
   else
   {
    $urano = 3;
   }
  }

  /* 72 to 61 */
  else if ($decl >= 61)
  {
   $hulp = (int)$ra * 60;
   if (($hulp >= 32) && ($hulp < 1400))
   {
    $urano = (($hulp - 32) / 72) + 16;
   }
   else
   {
    $urano = 15;
   }
  }

  /* 60 to 50 */
  else if ($decl >= 50)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 28) && ($hulp < 1408))
   {
    $urano = (($hulp - 28) / 60) + 36;
   }
   else
   {
    $urano = 35;
   }
  }

  /* 49 to 39 */
  else if ($decl >= 39)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 24) && ($hulp < 1416))
   {
    $urano = (($hulp - 24) / 48) + 60;
   }
   else
   {
    $urano = 59;
   }
  }

  /* 38 to 28 */
  else if ($decl >= 28)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 20) && ($hulp < 1420))
   {
    $urano = (($hulp - 20) / 40) + 90;
   }
   else
   {
    $urano = 89;
   }
  }

  /* 27 to 17 */
  else if ($decl >= 17)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 16) && ($hulp < 1424))
   {
    $urano = (($hulp - 16) / 32) + 126;
   }
   else
   {
    $urano = 125;
   }
  }

  /* 16 to 6 */
  else if ($decl >= 6)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 16) && ($hulp < 1424))
   {
    $urano = (($hulp - 16) / 32) + 171;
   }
   else
   {
    $urano = 170;
   }
  }

  /* 5 to -5 */
  else if ($decl >= -5)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 16) && ($hulp < 1424))
   {
    $urano = (($hulp - 16) / 32) + 216;
   }
   else
   {
    $urano = 215;
   }
  }

  /* -16 to -6 */
  else if ($decl >= -16)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 16) && ($hulp < 1424))
   {
    $urano = (($hulp - 16) / 32) + 261;
   }
   else
   {
    $urano = 260;
   }
  }

  /* -27 to -17 */
  else if ($decl >= -27)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 16) && ($hulp < 1424))
   {
    $urano = (($hulp - 16) / 32) + 306;
   }
   else
   {
    $urano = 305;
   }
  }

  /* -38 to -28 */
  else if ($decl >= -38)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 20) && ($hulp < 1420))
   {
    $urano = (($hulp - 20) / 40) + 351;
   }
   else
   {
    $urano = 350;
   }
  }

  /* -49 to -39 */
  else if ($decl >= -49)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 24) && ($hulp < 1416))
   {
    $urano = (($hulp - 24) / 48) + 387;
   }
   else
   {
    $urano = 386;
   }
  }

  /* -60 to -50 */
  else if ($decl >= -60)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 28) && ($hulp < 1408))
   {
    $urano = (($hulp - 28) / 60) + 417;
   }
   else
   {
    $urano = 416;
   }
  }

  /* -72 to -61 */
  else if ($decl >= -72)
  {
   $hulp = (int)($ra * 60.0);
   if (($hulp >= 32) && ($hulp < 1400))
   {
    $urano = (($hulp - 32) / 72) + 441;
   }
   else
   {
    $urano = 440;
   }
  }

  /* -84 to -73 */
  else if ($decl >= -84)
  {
   if (($ra >= 1.0) && ($ra < 23.0))
   {
    $urano = (int)($ra) - 1;
    $urano = $urano / 2;
    $urano = $urano + 461;
   }
   else
   {
    $urano = 460;
   }
  }

  /* -90 to -85 */
  else
  {
   if ($ra < 12.0)
   {
    $urano = 473;
   }
   else
   {
    $urano = 472;
   }
  }

  return (int)$urano;
 }

 // getPocketSkyAtlasPage returns the pocket sky atlas page of the object
 function getPocketSkyAtlasPage($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $psa = $get->psa;

  $db->logout();

  return $psa;
 }

 // calculatePocketSkyAtlas calculates the pocket sky atlas page of the object
 function calculatePocketSkyAtlasPage($ra, $decl)
 {
  $psa = 0;

  /* Page from pocket sky atlas */
  if ($ra >= 0.0 && $ra <= 3.0) 
  {
   if ($decl >= 60)
   {
     $psa = 1;
   } else if ($decl >= 30) {
     if ($ra <= 1.5) {
       $psa = 3;
     } else {
       $psa = 2;
     }
   } else if ($decl >= 0) {
     if ($ra <= 1.5) {
       $psa = 5;
     } else {
       $psa = 4;
     }
   } else if ($decl >= -30) {
     if ($ra <= 1.5) {
       $psa = 7;
     } else {
       $psa = 6;
     }
   } else if ($decl >= -60) {
     if ($ra <= 1.5) {
       $psa = 9;
     } else {
       $psa = 8;
     }
   } else {
     $psa = 10;
   }
  } else if ($ra >= 3.0 && $ra <= 6.0) 
  {
   if ($decl >= 60)
   {
     $psa = 11;
   } else if ($decl >= 30) {
     if ($ra <= 4.5) {
       $psa = 13;
     } else {
       $psa = 12;
     }
   } else if ($decl >= 0) {
     if ($ra <= 4.5) {
       $psa = 15;
     } else {
       $psa = 14;
     }
   } else if ($decl >= -30) {
     if ($ra <= 4.5) {
       $psa = 17;
     } else {
       $psa = 16;
     }
   } else if ($decl >= -60) {
     if ($ra <= 4.5) {
       $psa = 19;
     } else {
       $psa = 18;
     }
   } else {
     $psa = 20;
   }
  } else if ($ra >= 6.0 && $ra <= 9.0) {
   if ($decl >= 60)
   {
     $psa = 21;
   } else if ($decl >= 30) {
     if ($ra <= 7.5) {
       $psa = 23;
     } else {
       $psa = 22;
     }
   } else if ($decl >= 0) {
     if ($ra <= 7.5) {
       $psa = 25;
     } else {
       $psa = 24;
     }
   } else if ($decl >= -30) {
     if ($ra <= 7.5) {
       $psa = 27;
     } else {
       $psa = 26;
     }
   } else if ($decl >= -60) {
     if ($ra <= 7.5) {
       $psa = 29;
     } else {
       $psa = 28;
     }
   } else {
     $psa = 30;
   }
  } else if ($ra >= 9.0 && $ra <= 12.0) {
   if ($decl >= 60)
   {
     $psa = 31;
   } else if ($decl >= 30) {
     if ($ra <= 10.5) {
       $psa = 33;
     } else {
       $psa = 32;
     }
   } else if ($decl >= 0) {
     if ($ra <= 10.5) {
       $psa = 35;
     } else {
       $psa = 34;
     }
   } else if ($decl >= -30) {
     if ($ra <= 10.5) {
       $psa = 37;
     } else {
       $psa = 36;
     }
   } else if ($decl >= -60) {
     if ($ra <= 10.5) {
       $psa = 39;
     } else {
       $psa = 38;
     }
   } else {
     $psa = 40;
   }
  } else if ($ra >= 12.0 && $ra <= 15.0) {
   if ($decl >= 60)
   {
     $psa = 41;
   } else if ($decl >= 30) {
     if ($ra <= 13.5) {
       $psa = 43;
     } else {
       $psa = 42;
     }
   } else if ($decl >= 0) {
     if ($ra <= 13.5) {
       $psa = 45;
     } else {
       $psa = 44;
     }
   } else if ($decl >= -30) {
     if ($ra <= 13.5) {
       $psa = 47;
     } else {
       $psa = 46;
     }
   } else if ($decl >= -60) {
     if ($ra <= 13.5) {
       $psa = 49;
     } else {
       $psa = 48;
     }
   } else {
     $psa = 50;
   }
  } else if ($ra >= 15.0 && $ra <= 18.0) {
   if ($decl >= 60)
   {
     $psa = 51;
   } else if ($decl >= 30) {
     if ($ra <= 16.5) {
       $psa = 53;
     } else {
       $psa = 52;
     }
   } else if ($decl >= 0) {
     if ($ra <= 16.5) {
       $psa = 55;
     } else {
       $psa = 54;
     }
   } else if ($decl >= -30) {
     if ($ra <= 16.5) {
       $psa = 57;
     } else {
       $psa = 56;
     }
   } else if ($decl >= -60) {
     if ($ra <= 16.5) {
       $psa = 59;
     } else {
       $psa = 58;
     }
   } else {
     $psa = 60;
   }
  } else if ($ra >= 18.0 && $ra <= 21.0) {
   if ($decl >= 60)
   {
     $psa = 61;
   } else if ($decl >= 30) {
     if ($ra <= 19.5) {
       $psa = 63;
     } else {
       $psa = 62;
     }
   } else if ($decl >= 0) {
     if ($ra <= 19.5) {
       $psa = 65;
     } else {
       $psa = 64;
     }
   } else if ($decl >= -30) {
     if ($ra <= 19.5) {
       $psa = 67;
     } else {
       $psa = 66;
     }
   } else if ($decl >= -60) {
     if ($ra <= 19.5) {
       $psa = 69;
     } else {
       $psa = 68;
     }
   } else {
     $psa = 70;
   }
  } else if ($ra >= 21.0) {
   if ($decl >= 60)
   {
     $psa = 71;
   } else if ($decl >= 30) {
     if ($ra <= 22.5) {
       $psa = 73;
     } else {
       $psa = 72;
     }
   } else if ($decl >= 0) {
     if ($ra <= 22.5) {
       $psa = 75;
     } else {
       $psa = 74;
     }
   } else if ($decl >= -30) {
     if ($ra <= 22.5) {
       $psa = 77;
     } else {
       $psa = 76;
     }
   } else if ($decl >= -60) {
     if ($ra <= 22.5) {
       $psa = 79;
     } else {
       $psa = 78;
     }
   } else {
     $psa = 80;
   }   
  }
  return (int)$psa;
 }
 
 // getTorresCPage returns the TriAtlas C (torres) page of the object
 function getTorresCPage($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $torresC = $get->torresC;

  $db->logout();

  return $torresC;
 }

// calculateTorresC calculates the TriAtlas C (torres) page of the object
 function calculateTorresCPage($ra, $decl)
 {
  $torresC = 0;

  /* Page from torres C atlas */
  if ($decl >= 79.0) {
   if ($ra <= 1.2 || $ra >= 22.8) {
     $torresC = 1;
   } else {
     $torresC = 10 - (int)(($ra - 1.2) / 2.4);
   }
  } else if ($decl >= 69.0 ) {
   if ($ra <= 0.666 || $ra >= 23.333) {
     $torresC = 11;
   } else {
     $torresC = 28 - (int)(($ra - 0.666) / 1.33);
   }
  } else if ($decl >= 58.0 ) {
   if ($ra <= 0.4616 || $ra >= 23.5383) {
     $torresC = 29;
   } else {
     $torresC = 54 - (int)(($ra - 0.4616) / 0.9233);
   }
  } else if ($decl >= 48.0 ) {
   if ($ra <= 0.3633 || $ra >= 23.6366) {
     $torresC = 55;
   } else {
     $torresC = 87 - (int)(($ra - 0.3633) / 0.7266);
   }
  } else if ($decl >= 37.0 ) {
   if ($ra <= 0.315 || $ra >= 23.685) {
     $torresC = 88;
   } else {
     $torresC = 125 - (int)(($ra - 0.315) / 0.630);
   }
  } else if ($decl >= 27.0 ) {
   if ($ra <= 0.2783 || $ra >= 23.7216) {
     $torresC = 126;
   } else {
     $torresC = 168 - (int)(($ra - 0.2783) / 0.5566);
   }
  } else if ($decl >= 16.0 ) {
   if ($ra <= 0.2616 || $ra >= 23.7383) {
     $torresC = 169;
   } else {
     $torresC = 214 - (int)(($ra - 0.2616) / 0.5233);
   }
  } else if ($decl >= 5.0 ) {
   if ($ra <= 0.25 || $ra >= 23.75) {
     $torresC = 215;
   } else {
     $torresC = 262 - (int)(($ra - 0.25) / 0.5);
   }
  } else if ($decl >= -5.0 ) {
   if ($ra <= 0.255 || $ra >= 23.745) {
     $torresC = 263;
   } else {
     $torresC = 309 - (int)(($ra - 0.255) / 0.51);
   }
  } else if ($decl >= -16.0 ) {
   if ($ra <= 0.25 || $ra >= 23.75) {
     $torresC = 310;
   } else {
     $torresC = 357 - (int)(($ra - 0.25) / 0.5);
   }
  } else if ($decl >= -26.0 ) {
   if ($ra <= 0.2616 || $ra >= 23.7383) {
     $torresC = 358;
   } else {
     $torresC = 403 - (int)(($ra - 0.2616) / 0.5233);
   }
  } else if ($decl >= -37.0 ) {
   if ($ra <= 0.2783 || $ra >= 23.7216) {
     $torresC = 404;
   } else {
     $torresC = 446 - (int)(($ra - 0.2783) / 0.5566);
   }
  } else if ($decl >= -47.0 ) {
   if ($ra <= 0.315 || $ra >= 23.685) {
     $torresC = 447;
   } else {
     $torresC = 484 - (int)(($ra - 0.315) / 0.63);
   }
   
  } else if ($decl >= -58.0 ) {
   if ($ra <= 0.3633 || $ra >= 23.6366) {
     $torresC = 485;
   } else {
     $torresC = 517 - (int)(($ra - 0.3633) / 0.7266);
   }
  } else if ($decl >= -68.0 ) {
   if ($ra <= 0.4616 || $ra >= 23.5383) {
     $torresC = 518;
   } else {
     $torresC = 543 - (int)(($ra - 0.4616) / 0.9233);
   }
  } else if ($decl >= -79.0 ) {
   if ($ra <= 0.666 || $ra >= 23.333) {
   	$torresC = 544;
   } else {
    $torresC = 561 - (int)(($ra - 0.666) / 1.33);
   }
  } else {
   if ($ra <= 1.2 || $ra >= 22.8)
   {
     $torresC = 562;
   } else {
     $torresC = 571 - (int)(($ra - 1.2) / 2.4);
   }
  }

  return (int)$torresC;
 }
   
 // getTorresBCPage returns the TriAtlas BC (torres) page of the object
 function getTorresBCPage($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $torresBC = $get->torresBC;

  $db->logout();

  return $torresBC;
 }
 
 // calculateTorresBC calculates the TriAtlas BC (torres) page of the object
 function calculateTorresBCPage($ra, $decl)
 {
  $torresBC = 0;

  /* Page from torres BC atlas */
  if ($decl >= 72.0) {
   if ($ra <= 1.2 || $ra >= 22.8) {
     $torresBC = 1;
   } else {
     $torresBC = 10 - (int)(($ra - 1.2) / 2.4);
   }
  } else if ($decl >= 54.0 ) {
   if ($ra <= 0.666 || $ra >= 23.333) {
     $torresBC = 11;
   } else {
     $torresBC = 28 - (int)(($ra - 0.666) / 1.33);
   }
  } else if ($decl >= 36.0 ) {
   if ($ra <= 0.5 || $ra >= 23.5) {
     $torresBC = 29;
   } else {
     $torresBC = 52 - (int)(($ra - 0.5) / 1.0);
   }
  } else if ($decl >= 18.0 ) {
   if ($ra <= 0.42833 || $ra >= 23.57166) {
     $torresBC = 53;
   } else {
     $torresBC = 80 - (int)(($ra - 0.42833) / 0.85666);
   }
  } else if ($decl >= 0.0 ) {
   if ($ra <= 0.41333 || $ra >= 23.5866) {
     $torresBC = 81;
   } else {
     $torresBC = 109 - (int)(($ra - 0.41333) / 0.82666);
   }
  } else if ($decl >= -18.0 ) {
   if ($ra <= 0.41333 || $ra >= 23.5866) {
     $torresBC = 110;
   } else {
     $torresBC = 138 - (int)(($ra - 0.41333) / 0.82666);
   }
  } else if ($decl >= -36.0 ) {
   if ($ra <= 0.42833 || $ra >= 23.57166) {
     $torresBC = 139;
   } else {
     $torresBC = 166 - (int)(($ra - 0.42833) / 0.85666);
   }
  } else if ($decl >= -54.0 ) {
   if ($ra <= 0.5 || $ra >= 23.5) {
     $torresBC = 167;
   } else {
     $torresBC = 190 - (int)(($ra - 0.5) / 1.0);
   }
  } else if ($decl >= -72.0 ) {
   if ($ra <= 0.6666 || $ra >= 23.3333) {
     $torresBC = 191;
   } else {
     $torresBC = 208 - (int)(($ra - 0.6666) / 1.3333);
   }
  } else {
   if ($ra <= 1.2 || $ra >= 22.8) {
     $torresBC = 209;
   } else {
     $torresBC = 218 - (int)(($ra - 1.2) / 2.4);
   }
  }

  return (int)$torresBC;
 }

 // getTorresBPage returns the TriAtlas B (torres) page of the object
 function getTorresBPage($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $torresB = $get->torresB;

  $db->logout();

  return $torresB;
 }
 
 
 // calculateTorresB calculates the TriAtlas B (torres) page of the object
 function calculateTorresBPage($ra, $decl)
 {
  $torresB = 0;

  /* Page from torres B atlas */
  if ($decl >= 64.28333) {
   if ($ra <= 1.2 || $ra >= 22.8) {
     $torresB = 1;
   } else {
     $torresB = 9 - (int)(($ra - 1.2) / 2.4);
   }
  } else if ($decl >= 38.56666 ) {
   if ($ra <= 0.75 || $ra >= 23.25) {
     $torresB = 10;
   } else {
     $torresB = 25 - (int)(($ra - 0.75) / 1.5);
   }
  } else if ($decl >= 12.85 ) {
   if ($ra <= 0.63166 || $ra >= 23.36833) {
     $torresB = 26;
   } else {
     $torresB = 44 - (int)(($ra - 0.63166) / 1.2633);
   }
  } else if ($decl >= -12.85 ) {
   if ($ra <= 0.63166 || $ra >= 23.36833) {
     $torresB = 45;
   } else {
     $torresB = 63 - (int)(($ra - 0.63166) / 1.2633);
   }
  } else if ($decl >= -38.56666 ) {
   if ($ra <= 0.63166 || $ra >= 23.36833) {
     $torresB = 64;
   } else {
     $torresB = 82 - (int)(($ra - 0.63166) / 1.2633);
   }
  } else if ($decl >= -64.28333 ) {
   if ($ra <= 0.75 || $ra >= 23.25) {
     $torresB = 83;
   } else {
     $torresB = 98 - (int)(($ra - 0.75) / 1.5);
   }
  } else {
   if ($ra <= 1.2 || $ra >= 22.8) {
     $torresB = 99;
   } else {
     $torresB = 107 - (int)(($ra - 1.2) / 2.4);
   }
  }
  return (int)$torresB;
 }
 
 // getDsObjectName returns the name when the alternative name is given.
 function getDsObjectName($name)
 {
  $db = new database;
  $db->login();
  $sql = "SELECT objectnames.objectname FROM objectnames WHERE (objectnames.altname = \"$name\")";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
  $db->logout();
  if ($get)
	  return $get->objectname;
	else
		return "";
 }

// getDescription returns the Description when the name is given.
 function getDescriptionDsObject($name)
 {
  $db = new database;
  $db->login();
  $sql = "SELECT objects.description FROM objects WHERE (objects.name = \"$name\")";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
  $db->logout();
  if ($get)
	  return $get->description;
	else
		return "";
 }

 // getNameList returns a list of names when a part of the alternative name is 
 // given.
 function getNameList($catalog)
 {
  $name = Array();
  $db = new database;
  $db->login();
  $sql = "SELECT objectnames.objectname FROM objectnames WHERE objectnames.catalog = \"$catalog\"";
  $run = mysql_query($sql) or die(mysql_error());
  while($get = mysql_fetch_object($run))
   $name[] = $get->objectname;
  $db->logout();
  return $name;
 }

 // getNewUranometriaPage returns the new uranometriapage of the object
 function getNewUranometriaPage($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $uranonew = $get->urano_new;

  $db->logout();

  return $uranonew;
 }

 // calculateNewUranometriaPage calculates the new uranometriapage of the object
 function calculateNewUranometriaPage($ra, $decl)
 {
  $data = array(array(  84.5,   1,  1),  // 1st tier, chart 1
                array(  73.5,   7,  6),  // 2nd tier, charts 2->7
                array(  62.5,  17, 10),  // 3rd tier, charts 8->17
                array(  51.5,  29, 12),  // 4th tier, charts 18->29
                array(  40.5,  44, 15),  // 5th tier, charts 30->44
		array(  29.5,  62, 18),  // 6th tier, charts 45->62
  		array(  17.5,  80, 18),  // 7th tier, charts 63->80
		array(   5.5, 100, 20),  // 8th tier, charts 81->100
		array(  -5.5, 120, 20),  // 9th tier, charts 101->120
		array( -17.5, 140, 20),  // 10th tier, charts 121->140
		array( -29.5, 158, 18),  // 11th tier, charts 141->158
		array( -40.5, 176, 18),  // 12th tier, charts 159->176
		array( -51.5, 191, 15),  // 13th tier, charts 177->191
		array( -62.5, 203, 12),  // 14th tier, charts 192->203
		array( -73.5, 213, 10),  // 15th tier, charts 204->213
		array( -84.5, 219,  6),  // 16th tier, charts 214->219
		array( -90.0, 220,  1)); // 17th tier, chart 220

  // find proper tier
  for ($Tier = 0; $decl < $data[$Tier][0]; $Tier++);

  $HoursPerChart = 24.0 / $data[$Tier][2];

  $ra = $ra - ($HoursPerChart / 2);

  // Offset; middle of 1st map is in the middle of 0 hours RA

  $MapOffset = (int)($ra / $HoursPerChart);

  return (int)($data[$Tier][1] - $MapOffset);
 }

 // getTakiPage returns the taki page of the object
 function getTakiPage($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $taki = $get->taki;

  $db->logout();

  return $taki;
 }

 // calculateTakiPage calculates the taki page of the object
 function calculateTakiPage($ra, $decl)
 {
   if ($decl >= 83)
   {
     $taki = 1;
   }
   else if ($decl >= 62)
   {
     $taki = 2 + floor((24 - $ra) / 2);
   }
   else if ($decl >= 37)
   {
     $taki = 14 + floor(24 - $ra);
   }
   else if ($decl >= 12)
   {
     $taki = 38 + floor(24 - $ra);
   }
   else if ($decl >= -12)
   {
     $taki = 62 + floor(24 - $ra);
   }
   else if ($decl >= -37)
   {
     $taki = 86 + floor(24 - $ra);
   }
   else if ($decl >= -62)
   {
     $taki = 110 + floor(24 - $ra);
   }
   else if ($decl >= -83)
   {
     $taki = 134 + floor((24 - $ra) / 2);
   }
   else
   {
     $taki = 146;
   }
   return $taki;
 }

 // getSkyAtlasPage returns the page from the Sky Atlas of the object
 function getSkyAtlasPage($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $skyatlas = $get->sky;

  $db->logout();

  return $skyatlas;
 }

 // calculateSkyAtlasPage calculates the page from the Sky Atlas of the object
 function calculateSkyAtlasPage($ra, $decl)
 {
  $data = array(array(  50.0,   1,  3),  // 1st tier, charts 1->3
                array(  20.0,   4,  6),  // 2nd tier, charts 4->9
                array( -20.0,  10,  8),  // 3rd tier, charts 10->17
                array( -50.0,  18,  6),  // 4th tier, charts 18->23
                array( -90.0,  24,  3)); // 5th tier, charts 24->26

  // find proper tier
  for ($Tier = 0; $decl < $data[$Tier][0]; $Tier++);

  $HoursPerChart = 24.0 / $data[$Tier][2];

  // Offset; middle of 1st map is in the middle of 0 hours RA

  $MapOffset = (int)($ra / $HoursPerChart);

  return (int)($data[$Tier][1] + $MapOffset);
 }

 // getMilleniumPage returns the page from the Millenium Star atlas of the
 // object
 function getMilleniumPage($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $millenium = $get->millenium;

  $db->logout();

  return $millenium;
 }

 // calculateMilleniumPage calculates the page from the Millenium Star atlas 
 // of the object
 function calculateMilleniumPage($ra, $decl)
 {
  $rao = $ra;

  if (abs($decl) > 87)
  {
   $ra = 0;
  }
 
  if ($ra >= 0 && $ra <= 8)
  {
   $vol = "I";
   $vl = 0;
  }
  
  if ($ra > 8 && $ra <= 16)
  {
   $vol = "II";
   $vl = 1;
  }

  if ($ra > 16 && $ra < 24)
  {
   $vol = "III";
   $vl = 2;
  }

  $pa = 0;
  $qt = 0;
  $qn = 0;

  if (abs($decl) <= 90)
  {
   $pa = 240;
   $qt = $qt + 2;
   $qn = 2;
  }
  
  if (abs($decl) < 87)
  {
   $pa = 120;
   $qt = $qt + 4;
   $qn = 4;
  }

  if (abs($decl) < 81)
  {
   $pa = 60;
   $qt = $qt + 8;
   $qn = 8;
  }

  if (abs($decl) < 75)
  {
   $pa = 48;
   $qt = $qt + 10;
   $qn = 10;
  }

  if (abs($decl) < 69)
  {
   $pa = 40;
   $qt = $qt + 12;
   $qn = 12;
  }

  if (abs($decl) < 63)
  {
   $pa = 480 / 14;
   $qt = $qt + 14;
   $qn = 14;
  }

  if (abs($decl) < 57)
  {
   $pa = 30;
   $qt = $qt + 16;
   $qn = 16;
  }

  if (abs($decl) < 51)
  {
   $pa = 24;
   $qt = $qt + 20;
   $qn = 20;
  }

  if (abs($decl) < 45)
  {
   $pa = 24;
   $qt = $qt + 20;
   $qn = 20;
  }

  if (abs($decl) < 39)
  {
   $pa = 480 / 22;
   $qt = $qt + 22;
   $qn = 22;
  }

  if (abs($decl) < 33)
  {
   $pa = 480 / 22;
   $qt = $qt + 22;
   $qn = 22;
  }

  if (abs($decl) < 27)
  {
   $pa = 20;
   $qt = $qt + 24;
   $qn = 24;
  }

  if (abs($decl) < 21)
  {
   $pa = 20;
   $qt = $qt + 24;
   $qn = 24;
  }

  if (abs($decl) < 15)
  {
   $pa = 20;
   $qt = $qt + 24;
   $qn = 24;
  }

  if (abs($decl) < 9)
  {
   $pa = 20;
   $qt = $qt + 24;
   $qn = 24;
  }

  if (abs($decl) < 3)
  {
   $pa = 20;
   $qt = $qt + 24;
   $qn = 24;
  }

  if ($ra == 8)
  {
   $ra = 7.99;
  }

  if ($ra == 16)
  {
   $ra = 15.99;
  }

  if ($ra == 24)
  {
   $ra = 23.99;
  }

  if ($ra > $vl * 8)
  {
   $ra = $ra - ($vl * 8);
  }

  $ca = (int)(($ra * 60) / $pa);

  if (abs($decl) > 87 && ($rao > 4 && $rao < 16))
  {
   $qt = 1;
   $qn = 0;
  }

  $ch = $qt - $ca + ($vl * 516);

  if ($decl < 0)
  {
   $ch = 516 + ($vl * 516) - $qt + $qn - $ca;
  }

  return $ch."/".$vol;
 }

 // getObservedByUser returns +1 if the object is already observed by the 
 // given user, -1 if the object is not yet observed
 function getObservedbyUser($name, $observerid)
 {
  $observations = new Observations;
  $query = array("object" => "$name", "observer" => "$observerid", 
		 "instrument" => "", "location" => "", "mindate" => "", 
		 "maxdate" => "");
		
  $obs = $observations->getObservationFromQuery($query);

  $return = -1;
  if ($obs != "")
  {
    $return = count($obs);
  }
  return $return;
 }


 // getObserved returns +1 if the object is already observed, -1 if the object 
 // is not yet observed
 function getObserved($name)
 {
  $observations = new Observations;
  $query = array("object" => "$name", "observer" => "",
                 "instrument" => "", "location" => "", "mindate" => "",
                 "maxdate" => "");

  $obs = $observations->getObservationFromQuery($query);

  $return = -1;

  if ($obs != "")
  {
   $return = count($obs);
  }

  return $return;
 }


 // setType sets a new type for the object
 function setType($name, $type)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE objects SET type = \"$type\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setConstellation sets the constellation of the object
 function setConstellation($name, $con)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE objects SET con = \"$con\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setRA sets a new right ascension for the object
 function setRA($name, $ra)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE objects SET ra = \"$ra\" WHERE name = \"$name\"";
  
  $run = mysql_query($sql) or die(mysql_error());

  // Calculate the pages for the atlases
  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $decl = $get->decl;

  $urano = $this->calculateUranometriaPage($ra, $decl);
  $uranonew = $this->calculateNewUranometriaPage($ra, $decl);
  $skyatlas = $this->calculateSkyAtlasPage($ra, $decl);
  $msa = $this->calculateMilleniumPage($ra, $decl);
  $taki = $this->calculateTakiPage($ra, $decl);
  $psa = $this->calculatePocketSkyAtlasPage($ra, $decl);
  $torresB = $this->calculateTorresBPage($ra, $decl);
  $torresBC = $this->calculateTorresBCPage($ra, $decl);
  $torresC = $this->calculateTorresCPage($ra, $decl);
  
  $sql = "UPDATE objects SET urano = \"$urano\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  
  $sql = "UPDATE objects SET urano_new = \"$uranonew\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "UPDATE objects SET sky = \"$skyatlas\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "UPDATE objects SET millenium = \"$msa\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "UPDATE objects SET taki = \"$taki\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "UPDATE objects SET psa = \"$psa\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "UPDATE objects SET torresB = \"$torresB\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "UPDATE objects SET torresBC = \"$torresBC\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "UPDATE objects SET torresC = \"$torresC\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  
  $db->logout();
 }

 // setDeclination sets a new declination for the object
 function setDeclination($name, $decl)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE objects SET decl = \"$decl\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  // Calculate the pages for the atlases
  $get = mysql_fetch_object($run);

  $ra = $get->ra;

  $urano = $this->calculateUranometriaPage($ra, $decl);
  $uranonew = $this->calculateNewUranometriaPage($ra, $decl);
  $skyatlas = $this->calculateSkyAtlasPage($ra, $decl);
  $msa = $this->calculateMilleniumPage($ra, $decl);
  $taki = $this->calculateTakiPage($ra, $decl);
  $psa = $this->calculatePocketSkyAtlasPage($ra, $decl);
  $torresB = $this->calculateTorresBPage($ra, $decl);
  $torresBC = $this->calculateTorresBCPage($ra, $decl);
  $torresC = $this->calculateTorresCPage($ra, $decl);
  
  $sql = "UPDATE objects SET urano = \"$urano\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  
  $sql = "UPDATE objects SET urano_new = \"$uranonew\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "UPDATE objects SET sky = \"$skyatlas\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "UPDATE objects SET millenium = \"$msa\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "UPDATE objects SET taki = \"$taki\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "UPDATE objects SET psa = \"$psa\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "UPDATE objects SET torresB = \"$torresB\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "UPDATE objects SET torresBC = \"$torresBC\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "UPDATE objects SET torresC = \"$torresC\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());
  
  $db->logout();
 }

 function setDiam1($name, $diam1)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE objects SET diam1 = \"$diam1\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();

	$mag = $this->getDsObjectMagnitude($name);
  $diam2 = $this->getDiam2($name);

	// Calculate and set the SBObj
	if ($mag != 99.9 && ($diam1 != 0 || $diam2 != 0))
	{
		if ($diam1 != 0 && $diam2 == 0)
		{
			$diam2 = $diam1;
		} else if ($diam2 != 0 && $diam1 == 0)
		{
			$diam1 = $diam2;
		}
		$SBObj = ($mag + (2.5 * log10(2827.0 * ($diam1/60) * ($diam2/60))));
	}
	else
	{
		$SBObj = -999;
	}
  $db->login();
  $sql4 = "update objects set SBObj = \"$SBObj\" where name = \"$name\";";
  $run4 = mysql_query($sql4) or die(mysql_error());

  $db->logout();
 }

 function setDiam2($name, $diam2)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE objects SET diam2 = \"$diam2\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();

	$mag = $this->getDsObjectMagnitude($name);
  $diam1 = $this->getDiam1($name);

	// Calculate and set the SBObj
	if ($mag != 99.9 && ($diam1 != 0 || $diam2 != 0))
	{
		if ($diam1 != 0 && $diam2 == 0)
		{
			$diam2 = $diam1;
		} else if ($diam2 != 0 && $diam1 == 0)
		{
			$diam1 = $diam2;
		}
		$SBObj = ($mag + (2.5 * log10(2827.0 * ($diam1/60) * ($diam2/60))));
	}
	else
	{
		$SBObj = -999;
	}
  $db->login();
  $sql4 = "update objects set SBObj = \"$SBObj\" where name = \"$name\";";
  $run4 = mysql_query($sql4) or die(mysql_error());

  $db->logout();
 }

 
 // setMagnitude sets a new magnitude for the object
 function setMagnitude($name, $mag)
 {
  $db = new database;
  $db->login();
	

  $sql = "UPDATE objects SET mag = \"$mag\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();

	$diam1 = $this->getDiam1($name);
  $diam2 = $this->getDiam2($name);

	// Calculate and set the SBObj
	if ($mag != 99.9 && ($diam1 != 0 || $diam2 != 0))
	{
		if ($diam1 != 0 && $diam2 == 0)
		{
			$diam2 = $diam1;
		} else if ($diam2 != 0 && $diam1 == 0)
		{
			$diam1 = $diam2;
		}
		$SBObj = ($mag + (2.5 * log10(2827.0 * ($diam1/60) * ($diam2/60))));
	}
	else
	{
		$SBObj = -999;
	}
  $db->login();
  $sql4 = "update objects set SBObj = \"$SBObj\" where name = \"$name\";";
  $run4 = mysql_query($sql4) or die(mysql_error());

  $db->logout();
 }

 // setSurfaceBrightness sets a new surface brightness for the given object
 function setSurfaceBrightness($name, $subr)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE objects SET subr = \"$subr\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setDatasource sets a new datasource for the given object
 function setDatasource($name, $datasource)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE objects SET datasource = \"$datasource\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setSize sets a new size for the given object
 function setSize($name, $diam1, $diam2)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE objects SET diam1 = \"$diam1\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "UPDATE objects SET diam2 = \"$diam2\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // getNumberOfObjectsInCatalogue($catalogue)
 // returns the number of objects in the catalogue given as a parameter
 function getNumberOfObjectsInCatalogue($catalogue)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT COUNT( DISTINCT catindex) AS number FROM objectnames WHERE catalog = \"$catalogue\"";
  $run = mysql_query($sql) or die(mysql_error());
  $get = mysql_fetch_object($run);
  $db->logout();
  return $get->number; 
 }

 // setPositionAngle sets a new position angle for the given object
 function setPositionAngle($name, $pa)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE objects SET pa = \"$pa\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setCatalogs sets the new catalogs for the given object
 function setCatalogs($name, $catalogs)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE objects SET catalogs = \"$catalogs\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }


 function showObjects($link, $_SID, $min, $max, $myList, $noShow='')
 {
  global $AND,$ANT,$APS,$AQR,$AQL,$ARA,$ARI,$AUR,$BOO,$CAE,$CAM,$CNC,$CVN,$CMA,$CMI,$CAP,$CAR,$CAS,$CEN,$CEP,$CET,$CHA,$CIR,$COL,$COM,$CRA,$CRB,$CRV,$CRT,$CRU,
         $CYG,$DEL,$DOR,$DRA,$EQU,$ERI,$FOR,$GEM,$GRU,$HER,$HOR,$HYA,$HYI,$IND,$LAC,$LEO,$LMI,$LEP,$LIB,$LUP,$LYN,$LYR,$MEN,$MIC,$MON,$MUS,$NOR,$OCT,$OPH,
         $ORI,$PAV,$PEG,$PER,$PHE,$PIC,$PSC,$PSA,$PUP,$PYX,$RET,$SGE,$SGR,$SCO,$SCL,$SCT,$SER,$SEX,$TAU,$TEL,$TRA,$TRI,$TUC,$UMA,$UMI,$VEL,$VIR,$VOL,$VUL; 
 
  global $ASTER,$BRTNB,$CLANB,$DRKNB,$EMINB,$ENRNN,$ENSTR, $GALCL,$GALXY,$GLOCL,$GXADN,$GXAGC,$GACAN,$HII,$LMCCN,$LMCDN,$LMCGC,$LMCOC,$NONEX,$OPNCL,$PLNNB,$REFNB,$RNHII,
	       $SMCCN,$SMCDN,$SMCGC,$SMCOC,$SNREM,$STNEB,$QUASR,$WRNEB,$AA1STAR,$AA2STAR,$AA3STAR,$AA4STAR,$AA8STAR;

  include_once "../common/control/dec_to_dm.php";
  include_once "../common/control/ra_to_hms.php";
  include_once "../lib/lists.php";
  include_once "../lib/observers.php";
  $list = new Lists;
  $observer = new Observers;

  echo "<table width=\"100%\">\n";
  echo "<tr class=\"type3\">\n";
	if(array_key_exists('SO',$_GET) && ($_GET['SO']=="showname"))
    echo "<td><a href=\"" . $link . "&amp;RO=showname\" title=\"" . LangSortOn . mb_strtolower(LangOverviewObjectsHeader1) . "\">".LangOverviewObjectsHeader1."</a></td>\n";
	else
    echo "<td><a href=\"" . $link . "&amp;SO=showname\" title=\"" . LangSortOn . mb_strtolower(LangOverviewObjectsHeader1) . "\">".LangOverviewObjectsHeader1."</a></td>\n";
	if(array_key_exists('SO',$_GET) && ($_GET['SO']=="con"))
    echo "<td><a href=\"" . $link . "&amp;RO=con\" title=\"" . LangSortOn . mb_strtolower(LangOverviewObjectsHeader2) . "\">".LangOverviewObjectsHeader2."</a></td>\n";
  else
    echo "<td><a href=\"" . $link . "&amp;SO=con\" title=\"" . LangSortOn . mb_strtolower(LangOverviewObjectsHeader2) . "\">".LangOverviewObjectsHeader2."</a></td>\n";
	if(array_key_exists('SO',$_GET) && ($_GET['SO']=="mag"))
    echo "<td><a href=\"" . $link . "&amp;RO=mag\" title=\"" . LangSortOn . mb_strtolower(LangOverviewObjectsHeader3) . "\">".LangOverviewObjectsHeader3."</a></td>\n";
  else
	  echo "<td><a href=\"" . $link . "&amp;SO=mag\" title=\"" . LangSortOn . mb_strtolower(LangOverviewObjectsHeader3) . "\">".LangOverviewObjectsHeader3."</a></td>\n";
	if(array_key_exists('SO',$_GET) && ($_GET['SO']=="subr"))
	  echo "<td><a href=\"" . $link . "&amp;RO=subr\" title=\"" . LangSortOn . mb_strtolower(LangOverviewObjectsHeader3b) . "\">".LangOverviewObjectsHeader3b."</a></td>\n";
	else
	  echo "<td><a href=\"" . $link . "&amp;SO=subr\" title=\"" . LangSortOn . mb_strtolower(LangOverviewObjectsHeader3b) . "\">".LangOverviewObjectsHeader3b."</a></td>\n";
	if(array_key_exists('SO',$_GET) && ($_GET['SO']=="type"))
	  echo "<td><a href=\"" . $link . "&amp;RO=type\" title=\"" . LangSortOn . mb_strtolower(LangOverviewObjectsHeader4) . "\">".LangOverviewObjectsHeader4."</a></td>\n";
  else
	  echo "<td><a href=\"" . $link . "&amp;SO=type\" title=\"" . LangSortOn . mb_strtolower(LangOverviewObjectsHeader4) . "\">".LangOverviewObjectsHeader4."</a></td>\n";
  if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
	{
	  $atlas2 = $observer->getStandardAtlas($_SESSION['deepskylog_id']);
  	if ($atlas2 == 0) 
  	{     
  	  if((array_key_exists('SO',$_GET) && ($_GET['SO']=="urano"))||(array_key_exists('RO',$_GET) && ($_GET['RO']=="urano")))
  		  echo "<td><a href=\"" . $link . "&amp;RO=urano\" title=\"". LangSortOn . "atlas\">"."Atlas"."</a></td>\n";  
      else
  		  echo "<td><a href=\"" . $link . "&amp;SO=urano\" title=\"". LangSortOn . "atlas\">"."Atlas"."</a></td>\n";  
    }
  	if ($atlas2 == 1) 
  	{
  	  if((array_key_exists('SO',$_GET) && ($_GET['SO']=="urano_new"))||(array_key_exists('RO',$_GET) && ($_GET['RO']=="urano_new")))
  		  echo "<td><a href=\"" . $link . "&amp;RO=urano_new\" title=\"". LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
  	  else
  		  echo "<td><a href=\"" . $link . "&amp;SO=urano_new\" title=\"". LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
    }
  	if ($atlas2 == 2) 
  	{
  	  if((array_key_exists('SO',$_GET) && ($_GET['SO']=="sky"))||(array_key_exists('RO',$_GET) && ($_GET['RO']=="sky")))
  		  echo "<td><a href=\"" . $link . "&amp;RO=sky\" title=\"". LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
  	  else
  		  echo "<td><a href=\"" . $link . "&amp;SO=sky\" title=\"". LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
    }
  	if ($atlas2 == 3) 
    {     
  	  if((array_key_exists('SO',$_GET) && ($_GET['SO']=="millenium"))||(array_key_exists('RO',$_GET) && ($_GET['RO']=="millenium")))
  		  echo "<td><a href=\"" . $link . "&amp;RO=millenium\" title=\"". LangSortOn . "atlas\">"."Atlas"."</a></td>\n";			 
      else
  		  echo "<td><a href=\"" . $link . "&amp;SO=millenium\" title=\"". LangSortOn . "atlas\">"."Atlas"."</a></td>\n";			 
    }
  	if ($atlas2 == 4) 
  	{
  	  if((array_key_exists('SO',$_GET) && ($_GET['SO']=="taki"))||(array_key_exists('RO',$_GET) && ($_GET['RO']=="taki")))
  	    echo "<td><a href=\"" . $link . "&amp;RO=taki\" title=\"". $seenpar . LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
  	  else
  	    echo "<td><a href=\"" . $link . "&amp;SO=taki\" title=\"". $seenpar . LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
  	}
  	if ($atlas2 == 5) 
  	{
  	  if((array_key_exists('SO',$_GET) && ($_GET['SO']=="psa"))||(array_key_exists('RO',$_GET) && ($_GET['RO']=="psa")))
  	    echo "<td><a href=\"" . $link . "&amp;RO=psa\" title=\"". $seenpar . LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
  	  else
  	    echo "<td><a href=\"" . $link . "&amp;SO=psa\" title=\"". $seenpar . LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
  	}
  	if ($atlas2 == 6) 
  	{
  	  if((array_key_exists('SO',$_GET) && ($_GET['SO']=="torresB"))||(array_key_exists('RO',$_GET) && ($_GET['RO']=="torresB")))
  	    echo "<td><a href=\"" . $link . "&amp;RO=torresB\" title=\"". $seenpar . LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
  	  else
  	    echo "<td><a href=\"" . $link . "&amp;SO=torresB\" title=\"". $seenpar . LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
  	}
  	if ($atlas2 == 7) 
  	{
  	  if((array_key_exists('SO',$_GET) && ($_GET['SO']=="torresBC"))||(array_key_exists('RO',$_GET) && ($_GET['RO']=="torresBC")))
  	    echo "<td><a href=\"" . $link . "&amp;RO=torresBC\" title=\"". $seenpar . LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
  	  else
  	    echo "<td><a href=\"" . $link . "&amp;SO=torresBC\" title=\"". $seenpar . LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
  	}
  	if ($atlas2 == 8) 
  	{
  	  if((array_key_exists('SO',$_GET) && ($_GET['SO']=="torresC"))||(array_key_exists('RO',$_GET) && ($_GET['RO']=="torresC")))
  	    echo "<td><a href=\"" . $link . "&amp;RO=torresC\" title=\"". $seenpar . LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
  	  else
  	    echo "<td><a href=\"" . $link . "&amp;SO=torresC\" title=\"". $seenpar . LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
  	}
  	if((array_key_exists('SO',$_GET) && ($_GET['SO']=="contrast"))||(array_key_exists('RO',$_GET) && ($_GET['RO']=="contrast")))
  	  echo "<td><a href=\"" . $link . "&amp;RO=contrast\" title=\"". LangSortOn . mb_strtolower(LangViewObjectFieldContrastReserve) . "\">".  LangViewObjectFieldContrastReserve . "</a></td>\n";
    else
  	  echo "<td><a href=\"" . $link . "&amp;SO=contrast\" title=\"". LangSortOn . mb_strtolower(LangViewObjectFieldContrastReserve) . "\">".  LangViewObjectFieldContrastReserve . "</a></td>\n";
  	if((array_key_exists('SO',$_GET) && ($_GET['SO']=="magnification"))||(array_key_exists('RO',$_GET) && ($_GET['RO']=="magnification")))
  	  echo "<td><a href=\"" . $link . "&amp;RO=magnification\" title=\"". LangSortOn . mb_strtolower(LangViewObjectFieldMagnification) . "\">".  LangViewObjectFieldMagnification . "</a></td>\n";
    else
  	  echo "<td><a href=\"" . $link . "&amp;SO=magnification\" title=\"". LangSortOn . mb_strtolower(LangViewObjectFieldMagnification) . "\">".  LangViewObjectFieldMagnification . "</a></td>\n";
  }
	if((array_key_exists('SO',$_GET) && ($_GET['SO']=="seen"))||(array_key_exists('RO',$_GET) && ($_GET['RO']=="seen")))
	  echo "<td><a href=\"" . $link . "&amp;RO=seen\" title=\"". LangSortOn . mb_strtolower(LangOverviewObjectsHeader7) . "\">".LangOverviewObjectsHeader7."</a></td>\n";
  else
	  echo "<td><a href=\"" . $link . "&amp;SO=seen\" title=\"". LangSortOn . mb_strtolower(LangOverviewObjectsHeader7) . "\">".LangOverviewObjectsHeader7."</a></td>\n";
  if($myList)
    echo("<td><a href=\"" . $link . "&amp;min=" . $min . "&amp;addAllObjectsFromPageToList=true\" title=\"" . LangListQueryObjectsMessage1 . $_SESSION['listname'] . "\">P</a></td>");
 	$count = $min; // counter for altering table colors
	$countline = 0;
	while($count < $max)
  {
	  if ($_SESSION[$_SID][$count][0]!=$noShow)
  	{
  	  if ($countline % 2)
        $typefield = "class=\"type1\"";
      else
        $typefield = "class=\"type2\"";	
      // NAME
      $value = $_SESSION[$_SID][$count][0];
      $name = $_SESSION[$_SID][$count][0];
      $showname = $_SESSION[$_SID][$count][4];
      $con = $_SESSION[$_SID][$count][2];
      $type = $_SESSION[$_SID][$count][1];
      // MAGNITUDE   
      $magnitude = sprintf("%01.1f", $_SESSION[$_SID][$count][5]);
      if($magnitude == 99.9)
        $magnitude = "&nbsp;&nbsp;-&nbsp;";		
      $sb = sprintf("%01.1f", $_SESSION[$_SID][$count][6]);
      if($sb == 99.9)
        $sb = "&nbsp;&nbsp;-&nbsp;";
      // RIGHT ASCENSION
      $ra = RAToString($_SESSION[$_SID][$count][7]);
      // DECLINATION
      $decl = decToStringDegMin($_SESSION[$_SID][$count][8]);
			// SEEN
      $seen="<a href=\"deepsky/index.php?indexAction=detail_object&amp;object=" . urlencode($value) . "\" title=\"" . LangObjectNSeen . "\">-</a>";
      if(substr($_SESSION[$_SID][$count][3],0,1)=="X")
        $seen = "<a href=\"deepsky/index.php?indexAction=result_selected_observations&amp;object=" . urlencode($value) . "\" title=\"" . LangObjectXSeen . "\">" . $_SESSION[$_SID][$count][3] . "</a>";
      if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && (substr($_SESSION[$_SID][$count][3],0,1)=="Y"))
        $seen = "<a href=\"deepsky/index.php?indexAction=result_selected_observations&amp;object=" . urlencode($value) . "\" title=\"" . LangObjectYSeen . "\">" . $_SESSION[$_SID][$count][3] . "</a>";
      echo "<tr $typefield>\n";
      echo "<td><a href=\"deepsky/index.php?indexAction=detail_object&amp;object=" . urlencode($value) . "\">$showname</a></td>\n";
      echo "<td>".$$con."</td>\n";
      echo "<td>$magnitude</td>\n";
      echo "<td>$sb</td>\n";
      echo "<td>".$$type."</td>\n";
      // Page number in atlas
      if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id']) 
			{ $atlas = $observer->getStandardAtlas($_SESSION['deepskylog_id']); 
        $page = $_SESSION[$_SID][$count][$atlas+9];
        echo "<td>".$page."</td>\n";
        echo "<td class=\"" . $_SESSION[$_SID][$count][22] . "\" onmouseover=\"Tip('" . $_SESSION[$_SID][$count][23] . "')\">" .
             $_SESSION[$_SID][$count][21] . "</td>\n";
    
    		if ($_SESSION[$_SID][$count][21] == "-")
        {
          $magnification = "-";
        } else {
    			$magnification = $_SESSION[$_SID][$count][25];
    		}
        echo "<td>".$magnification."</td>\n";
			}
  
      echo "<td class=\"seen\">$seen</td>";
    	if($myList)
    	{
     	  echo("<td>");
        if($list->checkObjectInMyActiveList($name))
          echo("<a href=\"" . $link . "&amp;min=" . $min . "&amp;removeObjectFromList=" . urlencode($name) . "\" title=\"" . $name . LangListQueryObjectsMessage3 . $_SESSION['listname'] . "\">R</a>");
        else
          echo("<a href=\"" . $link . "&amp;min=" . $min . "&amp;addObjectToList=" . urlencode($name) . "&amp;showname=" . urlencode($showname) . "\" title=\"" . $name . LangListQueryObjectsMessage2 . $_SESSION['listname'] . "\">L</a>");
       echo("</td>");
    	}
      echo("</tr>");
      $countline++; // increase line counter
		}
    $count++; // increase object counter
  }   
  echo "</table>\n";
 }
 
 function showObject($object, $zoom = 30)
 {
  global $AND,$ANT,$APS,$AQR,$AQL,$ARA,$ARI,$AUR,$BOO,$CAE,$CAM,$CNC,$CVN,$CMA,$CMI,$CAP,$CAR,$CAS,$CEN,$CEP,$CET,$CHA,$CIR,$COL,$COM,$CRA,$CRB,$CRV,$CRT,$CRU,
         $CYG,$DEL,$DOR,$DRA,$EQU,$ERI,$FOR,$GEM,$GRU,$HER,$HOR,$HYA,$HYI,$IND,$LAC,$LEO,$LMI,$LEP,$LIB,$LUP,$LYN,$LYR,$MEN,$MIC,$MON,$MUS,$NOR,$OCT,$OPH,
         $ORI,$PAV,$PEG,$PER,$PHE,$PIC,$PSC,$PSA,$PUP,$PYX,$RET,$SGE,$SGR,$SCO,$SCL,$SCT,$SER,$SEX,$TAU,$TEL,$TRA,$TRI,$TUC,$UMA,$UMI,$VEL,$VIR,$VOL,$VUL; 
 
  global $ASTER,$BRTNB,$CLANB,$DRKNB,$EMINB,$ENRNN,$ENSTR, $GALCL,$GALXY,$GLOCL,$GXADN,$GXAGC,$GACAN,$HII,$LMCCN,$LMCDN,$LMCGC,$LMCOC,$NONEX,$OPNCL,$PLNNB,$REFNB,$RNHII,
	       $SMCCN,$SMCDN,$SMCGC,$SMCOC,$SNREM,$STNEB,$QUASR,$WRNEB,$AA1STAR,$AA2STAR,$AA3STAR,$AA4STAR,$AA8STAR;

  global $deepskylive;

//  include_once "../lib/locations.php";
//  $locations = new Locations;
 
  include_once "../common/control/ra_to_hms.php";
  include_once "../common/control/dec_to_dm.php";

  include_once "../lib/observations.php"; 
  include_once "../lib/observers.php";
  include_once "../lib/contrast.php";
  include_once "../lib/instruments.php";

  $observer = new Observers;
  $contrastObj = new Contrast;
  $instrumentObj = new Instruments;

  $_SESSION['object']=$object;
  //$objectDetails= $this->getSeenObjectDetails(array($object));
  echo("<table width=\"100%\">\n");
  // NAME
  echo("<tr class=\"type2\">\n
    <td class=\"fieldname\" align=\"right\" width=\"25%\">");
      if ($this->getRa($object) == "")
      {
        $object = $this->getDsObjectName($object);
      }
      echo LangViewObjectField1;
    echo("</td><td width=\"25%\">");
      echo"<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode(stripslashes($object)) . "\">" . (stripslashes($object)) . "</a>";
  echo("</td>");
	if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']))
	{
    if ($observer->getStandardAtlas($_SESSION['deepskylog_id']) == 0)
    {
      echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">"); 
      echo LangViewObjectField10;
      echo("</td><td width=\"25%\">");
      echo($this->getUranometriaPage($object));
      echo("</td>");
    }
    else if ($observer->getStandardAtlas($_SESSION['deepskylog_id']) == 1)
    {
      // NEW URANOMETRIA PAGE
      echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
      echo LangViewObjectField11;
      echo("</td><td width=\"25%\">");
      echo($this->getNewUranometriaPage($object));
      echo("</td>");
    }
    else if ($observer->getStandardAtlas($_SESSION['deepskylog_id']) == 2)
    {
      // SKY ATLAS PAGE
      echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
      echo LangViewObjectField13;
      echo("</td><td width=\"25%\">");
      echo($this->getSkyAtlasPage($object));
      echo("</td>");
    }
    else if ($observer->getStandardAtlas($_SESSION['deepskylog_id']) == 3)
    {
      // MILLENIUM STAR ATLAS PAGE
      echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
        echo LangViewObjectField14;
      echo("</td><td width=\"25%\">");
        echo($this->getMilleniumPage($object));
      echo("</td>");
    }
    else if ($observer->getStandardAtlas($_SESSION['deepskylog_id']) == 4)
    {
      // TAKI ATLAS PAGE
      echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
        echo LangViewObjectField15;
      echo("</td><td width=\"25%\">");
        echo($this->getTakiPage($object));
      echo("</td>");
    }
    else if ($observer->getStandardAtlas($_SESSION['deepskylog_id']) == 5)
    {
      // POCKET SKY ATLAS PAGE
      echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
      echo LangViewObjectField16;
      echo("</td><td width=\"25%\">");
      echo($this->getPocketSkyAtlasPage($object));
      echo("</td>");
    }
    else if ($observer->getStandardAtlas($_SESSION['deepskylog_id']) == 6)
    {
      // TORRES B ATLAS PAGE
      echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
      echo LangViewObjectField17;
      echo("</td><td width=\"25%\">");
      echo($this->getTorresBPage($object));
      echo("</td>");
    }
    else if ($observer->getStandardAtlas($_SESSION['deepskylog_id']) == 7)
    {
      // TORRES BC ATLAS PAGE
      echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
      echo LangViewObjectField18;
      echo("</td><td width=\"25%\">");
      echo($this->getTorresBCPage($object));
      echo("</td>");
    }
    else if ($observer->getStandardAtlas($_SESSION['deepskylog_id']) == 8)
    {
      // TORRES C ATLAS PAGE
      echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
      echo LangViewObjectField19;
      echo("</td><td width=\"25%\">");
      echo($this->getTorresCPage($object));
      echo("</td>");
    }
    else
    {
      echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
        echo "&nbsp;";
      echo("</td><td width=\"25%\">");
        echo "&nbsp;";
      echo("</td>");
    }
	}	
  else
  {
    echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
      echo "&nbsp;";
    echo("</td><td width=\"25%\">");
      echo "&nbsp;";
    echo("</td>");
  }

	echo("</tr>");
  // ALTERNATIVE NAME
  $altnames = $this->getAlternativeNames($object);
  echo("<tr class=\"type1\"><td class=\"fieldname\" align=\"right\" width=\"25%\">");
  echo LangViewObjectField2;
  echo("</td><td width=\"25%\">");
  $alt="";
	while(list($key, $value) = each($altnames)) // go through names array
  {
    if(trim($value)!=trim($object))
		{  
      if($alt)
			  $alt .= "<br>" . trim($value);
			else
			  $alt = trim($value);
    }
	}
	if($alt=="") echo "-"; else echo $alt;
  echo("</td>");
  // PART OF
  $contains = $this->getContainsNames($object);
	$partof = $this->getPartOfNames($object);
  echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
  echo LangViewObjectField2b;
  echo("</td><td width=\"25%\">");
	$containst="";
  while(list($key, $value) = each($contains)) // go through names array
  {
    if(trim($value)!=trim($object))
		{  
		  if($containst)
			  $containst .= "/" . "(<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode(trim($value)) . "\">" . trim($value) . "</a>)";
			else
			  $containst= "(<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode(trim($value)) . "\">" . trim($value) . "</a>)";
    }
  }
	if($containst=="") echo "(-)/"; else echo $containst . "/";
	$partoft = "";
  while(list($key, $value) = each($partof)) // go through names array
  {
    if(trim($value)!=trim($object))
		{  
		  if($partoft)
			  $partoft .= "/" . "<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode(trim($value)) . "\">" . trim($value) . "</a>";
			else
			  $partoft= "<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode(trim($value)) . "\">" . trim($value) . "</a>";
    }
  }
	if($partoft=="") echo "-"; else echo $partoft;
	echo("</td></tr>");
  // RIGHT ASCENSION
  echo("<tr class=\"type2\"><td class=\"fieldname\" align=\"right\" width=\"25%\">");
  echo LangViewObjectField3;
  echo("</td><td width=\"25%\">");
  $ra = $this->getRa($object);
  $raDSS = raToStringDSS($ra); // TODO add this method to util class!
  $util = new Util();
  echo($util->raToString($ra));
  echo("</td>");
  // DECLINATION
  echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
  echo LangViewObjectField4;
  echo("</td><td width=\"25%\">");
  $decl = $this->getDeclination($object);
  $declDSS = decToStringDSS($this->getDeclination($object));
  //echo(decToTrimmedString($decl));
  echo(decToStringDegMin($decl));
  echo("</td></tr>");
  // CONSTELLATION
  echo("<tr class=\"type1\">\n<td class=\"fieldname\" align=\"right\" width=\"25%\">");
  echo LangViewObjectField5;
  echo("</td><td width=\"25%\">");
  $const = $this->getConstellation($object);
  echo $$const;
  echo("</td>");
  // TYPE
  echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
  echo LangViewObjectField6;
  echo("</td><td width=\"25%\">");
  $type = $this->getType($object);
  echo $$type;
  echo("</td></tr>");
  // MAGNITUDE
  echo("<tr class=\"type2\"><td class=\"fieldname\" align=\"right\" width=\"25%\">");
  echo LangViewObjectField7;
  echo("</td><td width=\"25%\">");
  $magnitude = sprintf("%01.1f", $this->getDsObjectMagnitude($object));
  if(($magnitude == 99.9) || ($magnitude=="")) // unknown magnitude
  {
    $magnitude = "-";
  }
  echo($magnitude);
  echo("</td>");
  // SURFACE BRIGHTNESS
  echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
  echo LangViewObjectField8;
  echo("</td>");
	echo("<td width=\"25%\">");
	$sb = sprintf("%01.1f", $this->getSurfaceBrightness($object));
  if(($sb==99.9) ||($sb==""))
  {
	  $sb="-";
	}
  echo($sb);
  echo("</td>");
	echo("</tr>");
  echo("<tr class=\"type1\">");
	// SIZE
  echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
  echo LangViewObjectField9; 
  echo("</td><td width=\"25%\">");
  if($this->getSize($object) != "")
    echo($this->getSize($object));
  else
	  echo("-");
  echo("</td>"); 
  // POSITION ANGLE
  echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
  echo LangViewObjectField12; 
  echo("</td><td width=\"25%\">");
  if($this->getPositionAngle($object) != 999)
    echo($this->getPositionAngle($object) . "&deg;");
	else
	  echo "-";
  echo("</td>"); 
  echo("</tr>");
  echo("<tr class=\"type2\">
         <td class=\"fieldname\" align=\"right\" width=\"25%\">" .
          LangViewObjectFieldContrastReserve . "
         </td>");

  $contrast = "-";
  $prefMag = "-";

  $popupT = $this->prepareObjectsContrast(true);
  $popup = LangContrastNotLoggedIn;

	$contrastCalc = "";

  if($popupT)
    $popup=$popupT;
  else
  {
    $magni = $magnitude;
    if($magni == "-")
      $popup = LangContrastNoMagnitude;
    else 
    {
      $diam1 = $this->getDiam1($object);
      $diam1 = $diam1 / 60.0;
      if($diam1==0)
        $popup = LangContrastNoDiameter;
      else
      {
        $diam2 = $this->getDiam2($object);
        $diam2 = $diam2 / 60.0;
        if ($diam2 == 0)
          $diam2 = $diam1;
        $contrastCalc = $contrastObj->calculateContrast($magni, $this->getSBObj($object), $diam1, $diam2);
        if ($contrastCalc[0] < -0.2)      $popup = $object . LangContrastNotVisible . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
				else if ($contrastCalc[0] < 0.1)  $popup = LangContrastQuestionable . $object . LangContrastQuestionableB . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
			  else if ($contrastCalc[0] < 0.35) $popup = $object . LangContrastDifficult . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
			  else if ($contrastCalc[0] < 0.5)  $popup = $object . LangContrastQuiteDifficult . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
	      else if ($contrastCalc[0] < 1.0)  $popup = $object . LangContrastEasy . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
			  else                              $popup = $object . LangContrastVeryEasy . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);

        $contrast = $contrastCalc[0];
      }
    }
	}
  
  if ($contrast == "-")      $contype = "";
  else if ($contrast < -0.2) $contype = "typeNotVisible";
  else if ($contrast < 0.1)  $contype = "typeQuestionable";
  else if ($contrast < 0.35) $contype = "typeDifficult";
  else if ($contrast < 0.5)  $contype = "typeQuiteDifficult";
  else if ($contrast < 1.0)  $contype = "typeEasy";
  else                       $contype = "typeVeryEasy";
        

	if ($contrastCalc != "")
	{	
  	$contrast = sprintf("%.2f", $contrastCalc[0]);
		if ($contrastCalc[2] == "")
		{
			$prefMag = sprintf("%d", $contrastCalc[1]) . "x";
		}
		else
		{
			$prefMag = sprintf("%d", $contrastCalc[1]) . "x - " . $contrastCalc[2];
		}
  } else {
  	$contrast = "-";
  	$prefMag = "-";
  }

  echo ("<td class=\"" . $contype . "\" width=\"25%\"  onmouseover=\"Tip('" . $popup . "')\">");
  echo $contrast;
	echo "</td>";
	echo "<td class=\"fieldname\" align=\"right\" width=\"25%\">";
	echo LangViewObjectFieldOptimumDetectionMagnification;
	echo "</td>";
  echo "<td width=\"25%\">";
	echo $prefMag;
	echo "</td>";
	echo "</tr>";
	if($this->getDescriptionDsObject($object))
	{
  	echo "<tr>";
  	echo "<td align=\"right\">";
  	echo "Description";
  	echo "</td>";
  	echo "<td colspan=\"3\">";
  	echo $this->getDescriptionDsObject($object);
  	echo "</td>";
  	echo "</tr>";
  }
	echo "</table>";

  $ra = $this->getRa($object);
  $raDSS = raToStringDSS($ra); // TODO add this method to util class!
  $decl = $this->getDeclination($object);
  $declDSS = decToStringDSS($this->getDeclination($object));

  echo("<table width=\"100%\"><tr><td width=\"50%\" align=\"center\">");
  // LINK TO DSS IMAGE
  echo("<form action=\"deepsky/index.php?indexAction=view_image\" method=\"post\">\n");
  echo("<select name=\"imagesize\">\n");
  if($zoom<=15) echo("<option selected value=\"15\">15&#39;&nbsp;x&nbsp;15&#39;</option>"); else echo("<option value=\"15\">15&#39;&nbsp;x&nbsp;15&#39;</option>"); // 15 x 15 arcminutes
  if(($zoom>15)&& ($zoom<=30)) echo("<option selected value=\"30\">30&#39;&nbsp;x&nbsp;30&#39;</option>"); else echo("<option value=\"30\">30&#39;&nbsp;x&nbsp;30&#39;</option>"); // 30 x 30 arcminutes
    if($zoom>30) echo("<option selected value=\"60\">60&#39;&nbsp;x&nbsp;60&#39;</option>"); else echo("<option value=\"60\">60&#39;&nbsp;x&nbsp;60&#39;</option>"); // 60 x 60 arcminutes
    echo("</select>");
	
    echo("<input type=\"hidden\" name=\"raDSS\" value=\"" . $raDSS . "\" />");
    echo("<input type=\"hidden\" name=\"declDSS\" value=\"" . $declDSS . "\" />");
    echo("<input type=\"hidden\" name=\"name\" value=\"" . $object . "\" />");

    echo("<input type=\"submit\" name=\"dss\" value=\"" . LangViewObjectDSS . "\" />");
  echo("</form>");
  echo("</td><td width=\"50%\" align=\"center\">");
  // LINK TO DEEPSKYLIVE CHART
  if ($deepskylive == 1)
  {
    $raDSL = raToStringDSL($this->getRa($object));
    $declDSL = decToStringDSL($this->getDeclination($object));
    echo("<form action=\"deepsky/index.php?indexAction=detail_object&object=".urlencode($object)."&zoom=" . $zoom . "\" method=\"post\">");
      echo("<select name=\"dslsize\">\n");
        if($zoom<=30) echo("<option selected value=\"60\">1&deg;</option>"); else echo("<option value=\"60\">1&deg;</option>");
        if(($zoom>30) && ($zoom<=60)) echo("<option selected value=\"120\">2&deg;</option>"); else echo("<option value=\"120\">2&deg;</option>");
        if ($zoom>60) echo("<option selected value=\"180\">3&deg;</option>"); else echo("<option value=\"180\">3&deg;</option>"); 
      echo("</select>");
      echo("<input type=\"hidden\" name=\"showDSL\" value=\"1\" />");
      echo("<input type=\"hidden\" name=\"indexAction\" value=\"detail_object\" />");
      echo("<input type=\"submit\" name=\"dsl\" value=\"" . LangViewObjectDSL . "\" />");
      if (isset($_POST["showDSL"]) && $_POST["showDSL"] == 1)
      {
        $fov = $_POST["dslsize"];
        echo("<applet code=\"Deepskylive.class\" codebase=\"http://users.telenet.be/deepskylive/applet/\" height=\"1\" width=\"1\">
              <param name=\"ra\" value=\"".$raDSL."\">
              <param name=\"dec\" value=\"".$declDSL."\">
              <param name=\"fov\" value=\"".$fov."\">
              <param name=\"p\" value=\"1\">
              </applet>");
      }
    echo("\n</form>");
  }
  echo("</td></tr></table>");
	echo("<table width=\"100%\"><tr><td width=\"50%\" align=\"center\">");
  echo("</td></tr></table>");
	
	echo"<hr>";
 }
 
 function getOtherObjects($objectname, $dist)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT objects.ra, objects.decl FROM objects WHERE name = \"$objectname\"";
  $run = mysql_query($sql) or die(mysql_error());
	$get = mysql_fetch_object($run);
	$ra = $get->ra;
	$decl = $get->decl;
	$dra = 0.0011 * $dist / cos($decl/180*3.1415926535);

  $sql = "SELECT objects.name FROM objects " .
	       "WHERE ((objects.ra > $ra - $dra) AND (objects.ra < $ra + $dra) " .
				 "AND (objects.decl > $decl - ($dist/60)) AND (objects.decl < $decl + ($dist/60)))";
  $run = mysql_query($sql) or die(mysql_error());
  $db->logout();
	$result = array();
	$i=0;
  while($get = mysql_fetch_object($run))
	  $result[$get->name] = array($i++, $get->name);
	return $result;
 } 
}

class contrastcompare {
     var $_reverse;

     function contrastCompare( $reverse ) {
      $this->_reverse = $reverse;
     }

     function compare( $a, $b ) {
      $a = explode ( '/' , $a);
      $b = explode ( '/' , $b);
      $a = $a[0];
      $b = $b[0];

      if ($a == $b) return 0;

      if ($this->_reverse)
      {
       return ($b > $a) ? -1 : 1;
      }
      else
      {
       return ($a > $b) ? -1 : 1;
      }
     }
 }
?>
