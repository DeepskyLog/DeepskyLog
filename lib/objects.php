<?php
// The objects class collects all functions needed to enter, retrieve and
// adapt object data from the database and functions to display the data.

class Objects
{
 // addObject adds a new object to the database. The name, alternative name, 
 // type, constellation, right ascension, declination, magnitude, surface 
 // brightness, diam1, diam2, position angle and info about the catalogs should
 // be given as parameters. The chart numbers for different atlasses
 // are put in the
 // database. $datasource describes where the data comes from eg : SAC7.2, 
 // DeepskyLogUser or E&T 2.5
 function addDSObject($name, $cat, $catindex, $type, $con, $ra, $dec, $mag, $subr, $diam1, $diam2, $pa, $catalogs, $datasource)
 { if (!$_SESSION['lang'])
     $_SESSION['lang'] = "English";
   $urano = $GLOBALS['objAtlas']->calculateUranometriaPage($ra, $dec);
   $uranonew = $GLOBALS['objAtlas']->calculateNewUranometriaPage($ra, $dec);
   $skyatlas = $GLOBALS['objAtlas']->calculateSkyAtlasPage($ra, $dec);
   $millenium = $GLOBALS['objAtlas']->calculateMilleniumPage($ra, $dec);
   $taki = $GLOBALS['objAtlas']->calculateTakiPage($ra, $dec);
   $psa = $GLOBALS['objAtlas']->calculatePocketSkyAtlasPage($ra, $dec);
   $torresB = $GLOBALS['objAtlas']->calculateTorresBPage($ra, $dec);
   $torresBC = $GLOBALS['objAtlas']->calculateTorresBCPage($ra, $dec);
   $torresC = $GLOBALS['objAtlas']->calculateTorresCPage($ra, $dec);
   $array = array("INSERT INTO objects (name, type, con, ra, decl, mag, subr, diam1, diam2, pa, datasource, urano, urano_new, sky, millenium, taki, psa, torresB, torresBC, torresC, milleniumbase) 
	                            VALUES (\"$name\", \"$type\", \"$con\", \"$ra\", \"$dec\", \"$mag\", \"$subr\", \"$diam1\", \"$diam2\", \"$pa\", \"$datasource\", \"$urano\", \"$uranonew\", \"$skyatlas\", \"$millenium\", \"$taki\", \"$psa\", \"$torresB\", \"$torresBC\", \"$torresC\", \"$millenium\")");
   $sql = implode("", $array);
   $GLOBALS['objDatabase']->execSQL($sql);
   $newcatindex = ucwords(trim($catindex));
   $GLOBALS['objDatabase']->execSQL("INSERT INTO objectnames (objectname, catalog, catindex, altname) VALUES (\"$name\", \"$cat\", \"$catindex\", TRIM(CONCAT(\"$cat\", \" \", \"$newcatindex\")))");
	 if(($mag!=99.9)&&(($diam1!=0)||($diam2!=0)))                                 // Calculate and set the SBObj
	 { if(($diam1!=0)&&($diam2==0))
		   $diam2 = $diam1;
	   elseif(($diam2!=0)&&($diam1==0))
		   $diam1=$diam2;
		 $SBObj=($mag+(2.5*log10(2827.0*($diam1/60)*($diam2/60))));
	}
	else
		$SBObj = -999;
  $GLOBALS['objDatabase']->execSQL("update objects set SBObj = \"$SBObj\" where name = \"$name\";");
 }
 function newName($name, $cat, $catindex)
 { $newname = trim($cat . " " . ucwords(trim($catindex)));
	 $newcatindex = ucwords(trim($catindex));
   $GLOBALS['objDatabase']->execSQL("UPDATE objectnames SET catalog=\"$cat\", catindex=\"$newcatindex\", altname=TRIM(CONCAT(\"$cat\", \" \", \"$newcatindex\")) WHERE objectname = \"$name\" AND altname = \"$name\"");
   $GLOBALS['objDatabase']->execSQL("UPDATE objectnames SET objectname=\"$newname\" WHERE objectname = \"$name\"");
   $GLOBALS['objDatabase']->execSQL("UPDATE objects SET name=\"$newname\" WHERE name = \"$name\"");
   $GLOBALS['objDatabase']->execSQL("UPDATE observerobjectlist SET objectshowname=\"$newname\" WHERE objectname = \"$name\"");
   $GLOBALS['objDatabase']->execSQL("UPDATE observerobjectlist SET objectname=\"$newname\" WHERE objectname = \"$name\"");
   $GLOBALS['objDatabase']->execSQL("UPDATE observations SET objectname=\"$newname\" WHERE objectname = \"$name\"");
   $GLOBALS['objDatabase']->execSQL("UPDATE objectpartof SET objectname=\"$newname\" WHERE objectname = \"$name\"");
   $GLOBALS['objDatabase']->execSQL("UPDATE objectpartof SET partofname=\"$newname\" WHERE partofname = \"$name\"");
 } 
 function removeAndReplaceObjectBy($name, $cat, $catindex)
 { $newname = trim($cat . " " . ucwords(trim($catindex)));
	 $newcatindex = ucwords(trim($catindex));
   $GLOBALS['objDatabase']->execSQL("UPDATE observations SET objectname=\"$newname\" WHERE objectname=\"$name\"");
   $GLOBALS['objDatabase']->execSQL("UPDATE observations SET objectname=\"$newname\" WHERE objectname=\"$name\"");
   $GLOBALS['objDatabase']->execSQL("UPDATE observerobjectlist SET objectname=\"$newname\" WHERE objectname=\"$name\"");
   $GLOBALS['objDatabase']->execSQL("UPDATE observerobjectlist SET objectshowname=\"$newname\" WHERE objectname=\"$name\"");
   $GLOBALS['objDatabase']->execSQL("DELETE objectnames.* FROM objectnames WHERE objectname = \"$name\"");
   $GLOBALS['objDatabase']->execSQL("DELETE objectpartof.* FROM objectpartof WHERE objectname=\"$name\" OR partofname = \"$name\"");
   $GLOBALS['objDatabase']->execSQL("DELETE objects.* FROM objects WHERE name = \"$name\"");
 } 
 function newAltName($name, $cat, $catindex)
 { $GLOBALS['objDatabase']->execSQL("INSERT INTO objectnames (objectname, catalog, catindex, altname) VALUES (\"$name\", \"$cat\", \"$catindex\", TRIM(CONCAT(\"$cat\", \" \", \"".ucwords(trim($catindex))."\")))");
 }
 function removeAltName($name, $cat, $catindex)
 { $GLOBALS['objDatabase']->execSQL("DELETE objectnames.* FROM objectnames WHERE objectname = \"$name\" AND catalog = \"$cat\" AND catindex=\"".ucwords(trim($catindex))."\"");
 }
 function newPartOf($name, $cat, $catindex)
 { $GLOBALS['objDatabase']->execSQL("INSERT INTO objectpartof (objectname, partofname) VALUES (\"$name\", \"".trim($cat . " " . ucwords(trim($catindex)))."\")");
 }
 function removePartOf($name, $cat, $catindex)
 { $GLOBALS['objDatabase']->execSQL("DELETE objectpartof.* FROM objectpartof WHERE objectname = \"$name\" AND partofname = \"".trim($cat . " " . ucwords(trim($catindex)))."\"");
 } 
 function deleteDSObject($name) // deleteObject removes the object with name = $name
 { $GLOBALS['objDatabase']->execSQL("DELETE FROM objects WHERE name=\"$name\"");
 }
 function getAllInfoDsObject($name) // getAllInfo returns all information of an object
 { $get = mysql_fetch_object($GLOBALS['objDatabase']->selectRecordset("SELECT * FROM objects WHERE name = \"$name\""));
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
     $object["seen"] = "X (" . $see . ")";
   if (array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
   { $user = $_SESSION['deepskylog_id'];
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
   $run=$GLOBALS['objDatabase']->selectRecordset("SELECT altname FROM objectnames WHERE objectnames.objectname = \"$name\"");
   $object["altname"]="";
	 while($get=mysql_fetch_object($run))
     if($get->altname!=$name)
		   if($object["altname"])
		     $object["altname"].="/".$get->altname;
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
 function getDsObjectType($name)
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

 
 function getObjects()   // getObjects returns an array with the names of all objects
 { return $GLOBALS['objDatabase']->selectSingleArray("SELECT objects.name FROM OBJECTS",'name');
 }

 function sortObjects($result, $sort, $reverse=false)
 { if(!$result ||count($result)<2)
	  return $result;
   $sortmethod = "strnatcasecmp";
	 $k=0;
   if($sort == "name")      
     while(list($key, $value) = each($result))
	     $result3[$value['objectname'].$value[4]] = $value;
   if($sort == "type")		  
     while(list($key, $value) = each($result))
       $result3[$value['objecttype'].$value[4]] = $value;
   if($sort == "con")
     while(list($key, $value) = each($result))
	     $result3[$value['objectconstellation'].$value[4]] = $value;
   if($sort == "seen")
     while(list($key, $value) = each($result))
	     $result3[$value[3].$value[4]] = $value;
   if($sort == "seendate")
     while(list($key, $value) = each($result))
	     $result3[$value[28].$value[4]] = $value;
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
   if(substr($sort,0,5) == "atlas") 
   { $cnt = 0;
     while(list($key, $value) = each($result))
		 { $result3[$value[substr($sort,5)].sprintf("%05d", $cnt) / 10000] = $value;
			 $cnt = $cnt + 1;
		 }
	 }
  if($sort == "contrast")
  { $sortmethod = array( new contrastcompare( $reverse ), "compare" );
    while(list($key, $value) = each($result))
    { if (strcmp($value[21], "-") == 0)
        $result3["-/".$value[4]] = $value;
      else
       $result3[sprintf("%.2f", $value[21])."/".$value[4]] = $value;
    }
  }
  if($sort == "magnification")
  { $cnt = 0;
    while(list($key, $value) = each($result))
		{ if($value[21] == "-")
			{ $result3["-".sprintf("%05d", $cnt) / 10000] = $value;
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
  { $result = array_reverse($result, false);
  }
  return $result;
 }

 function prepareObjectsContrast($doLogin=false)
 { include_once "contrast.php";
   $contrastObj = new Contrast;
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
 		$magnificationsName='';
		$fov='';
		if(!(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id'])))
		  $popup = LangContrastNotLoggedIn;
    else
		{ $sql5 = "SELECT stdlocation, stdtelescope from observers where id = \"" . $_SESSION['deepskylog_id'] . "\"";
      $run5 = mysql_query($sql5) or die(mysql_error());
      $get5 = mysql_fetch_object($run5);
      if ($get5->stdlocation==0)
        $popup = LangContrastNoStandardLocation;
      elseif($get5->stdtelescope==0)
				$popup = LangContrastNoStandardInstrument;
			else
			{ // Check for eyepieces or a fixed magnification
        $sql6 = "SELECT fixedMagnification, diameter, fd from instruments where id = \"" . $get5->stdtelescope . "\"";
        $run6 = mysql_query($sql6) or die(mysql_error());
        $get6 = mysql_fetch_object($run6);
        if ($get6->fd == 0 && $get6->fixedMagnification == 0)
        { // We are not setting $magnifications
					$magnifications = array();
				}
        else if ($get6->fixedMagnification == 0)
        { $sql7 = "SELECT focalLength, name, apparentFOV, maxFocalLength from eyepieces where observer = \"" . $_SESSION['deepskylog_id'] . "\"";
  	      $run7 = mysql_query($sql7) or die(mysql_error());
				  while($get7 = mysql_fetch_object($run7))
					{ if ($get7->maxFocalLength > 0.0)
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
   return $popup;
 }
 
 function getSeen($object)
 { $seen='-';
   if($ObsCnt=$GLOBALS['objDatabase']->selectSingleValue("SELECT COUNT(observations.id) As ObsCnt FROM observations WHERE objectname = \"" . $object . "\" AND visibility != 7 ",'ObsCnt'))
   { $seen='X('.$ObsCnt.')';
     if(array_key_exists('deepskylog_id',$_SESSION)&&$_SESSION['deepskylog_id'])
     { $get3=mysql_fetch_object($GLOBALS['objDatabase']->selectRecordset("SELECT COUNT(observations.id) As PersObsCnt, MAX(observations.date) As PersObsMaxDate FROM observations WHERE objectname = \"" . $object . "\" AND observerid = \"" . $_SESSION['deepskylog_id'] . "\" AND visibility != 7"));
		   if($get3->PersObsCnt>0)
         $seen='Y('.$ObsCnt.'/'.$get3->PersObsCnt.')&nbsp;'.$get3->PersObsMaxDate;
		 }
	 }
	 return $seen;
 }
 function getDSOseen($object)
 { $seenDetails=$this->getSeen($object);
   $seen = "<a href=\"".$GLOBALS['baseURL']."index.php?indexAction=detail_objectamp;object=".urlencode($object)."\" title=\"".LangObjectNSeen."\">-</a>";
   if(substr($seenDetails,0,1)=="X")                                            // object has been seen already
     $seen = "<a href=\"".$GLOBALS['baseURL']."index.php?indexAction=result_selected_observations&amp;object=".urlencode($object)."\" title=\"".LangObjectXSeen."\">".$seenDetails."</a>";
   if(array_key_exists('deepskylog_id', $_SESSION)&&$_SESSION['deepskylog_id'])
     if (substr($seenDetails,0,1)=="Y")                                         // object has been seen by the observer logged in
       $seen = "<a href=\"".$GLOBALS['baseURL']."index.php?indexAction=result_selected_observations&amp;object=".urlencode($object)."\" title=\"".LangObjectYSeen."\">".$seenDetails."</a>";
   return $seen;
 }
 function getObjectVisibilities($obs)
 { include_once "contrast.php";
   $contrastObj = new Contrast;

	 
	 $popup='';
   $popupT = $this->prepareObjectsContrast();
   $result2=$obs;

	 $obscnt=sizeof($obs);
   if($obscnt > 0)
   { $j=0;
		 reset($obs);
     while(list($key, $value) = each($obs))
     { $contrast = "-";
       $contype = "";
		   $contrastcalc1="";
		
       if($popupT)
		     $popup=$popupT;
		   else
       { if(!array_key_exists('objectmagnitude',$result2[$j]))
			     echo "no objectmagnitude for ".$result2[$j]['objectname'];
			   $magni = $result2[$j]['objectmagnitude'];
				 $subrobj = $result2[$j]['objectsbcalc'];
         if($magni>90)
           $popup = LangContrastNoMagnitude;
         else 
		     { $diam1 = $result2[$j]['objectdiam1'];
           $diam1 = $diam1 / 60.0;

           if($diam1==0)
             $popup = LangContrastNoDiameter;
           else
           { $diam2 = $result2[$j]['objectdiam2'];
             $diam2 = $diam2 / 60.0;
             if ($diam2 == 0)
               $diam2 = $diam1;
             $contrastCalc = $contrastObj->calculateContrast($magni, $subrobj, $diam1,$diam2);
             if ($contrastCalc[0] < -0.2) 
						 $popup = $result2[$j]['showname'] . LangContrastNotVisible . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
             else if ($contrastCalc[0] < 0.1)
						 $popup = LangContrastQuestionable . $result2[$j]['showname'] . LangContrastQuestionableB . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
             else if ($contrastCalc[0] < 0.35)
						 $popup = $result2[$j]['showname'] . LangContrastDifficult . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
             else if ($contrastCalc[0] < 0.5)
						 $popup = $result2[$j]['showname'] . LangContrastQuiteDifficult . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
             else if ($contrastCalc[0] < 1.0)
						 $popup = $result2[$j]['showname'] . LangContrastEasy . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
             else
						 $popup = $result2[$j]['showname'] . LangContrastVeryEasy . addslashes($_SESSION['location']) . LangContrastPlace . addslashes($_SESSION['telescope']);
				      
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
       $result2[$j]['objectcontrast'] = $contrast;
       $result2[$j][22] = $contype;
       $result2[$j]['objectcontrasttype'] = $contype;
       $result2[$j][23] = $popup;
       $result2[$j]['objectcontrastpopup'] = $popup;
       $result2[$j][25] = $contrastcalc1;
       $result2[$j]['objectoptimalmagnification'] = $contrastcalc1;
       $j++;		
     }
   }
	 $obs = $result2;
   return $obs; 
 }
 
 function getSeenObjectDetails($obs, $seen="D")
 { global $objAtlas;
   $result2=array();
	 $obscnt=sizeof($obs);
   if($obscnt > 0)
   { $j=0;
		 reset($obs);
     while(list($key, $value) = each($obs))
     { $object=$value[1];
       $seentype = "-";
       $sql = "SELECT COUNT(observations.id) As ObsCnt FROM observations WHERE objectname = \"" . $object . "\" AND visibility != 7 ";
       $run = mysql_query($sql) or die(mysql_error());
       $get2 = mysql_fetch_object($run);
       if ($get2->ObsCnt)
       { $seentype="X";
         if(array_key_exists('deepskylog_id',$_SESSION)&&$_SESSION['deepskylog_id'])
         { $sql = "SELECT COUNT(observations.id) As PersObsCnt, MAX(observations.date) As PersObsMaxDate, MAX(observations.id) As PersObsMaxId " .
					        "FROM observations WHERE objectname = \"" . $object . "\" AND observerid = \"".$_SESSION['deepskylog_id']."\" AND visibility != 7";
           $run = mysql_query($sql) or die(mysql_error());
           $get3 = mysql_fetch_object($run);
           if ($get3->PersObsCnt>0)
				     $seentype="Y";
         }
       }
			 if(($seen == "D") ||
			   (strpos(" " . $seen, $seentype)))
		   { $result2[$j]['objectname'] = $value[1];
         $sql = "SELECT * FROM objects WHERE name = \"". $value[1] . "\"";
         $run = mysql_query($sql) or die(mysql_error());
         $get = mysql_fetch_object($run);
         if($get)
				 { $type = $get->type;
           $con = $get->con;
           $result2[$j]['objecttype'] =  $type;
           $result2[$j]['objectconstellation'] =  $con;
           $result2[$j][3]="-";
           $result2[$j]['objectseen']="-";
  	       $result2[$j]['objectlastseen']='-';
					 if($seentype == "X") $result2[$j][3] = "X(" . $get2->ObsCnt . ")";
  	       if($seentype == "X") $result2[$j]['objectseen'] = "X(" . $get2->ObsCnt . ")";
           if($seentype == "Y") $result2[$j][3] = "Y(" . $get2->ObsCnt . "/" . $get3->PersObsCnt . ")";
           if($seentype == "Y") $result2[$j]['objectseen'] = "Y(" . $get2->ObsCnt . "/" . $get3->PersObsCnt . ")";
           if($seentype == "Y") $result2[$j][28] = $get3->PersObsMaxDate; else $result2[$j][28] = '';
           if($seentype == "Y") $result2[$j]['objectlastseen'] = $get3->PersObsMaxDate; else $result2[$j]['objectlastseen'] = '';
           if($seentype == "Y") $result2[$j][29] = $get3->PersObsMaxId; else $result2[$j][29] = 0;
           if($seentype == "Y") $result2[$j]['objectlastobservationid'] = $get3->PersObsMaxId; else $result2[$j]['objectlastobservationid'] = 0;
           $result2[$j][4] =  $key;
           $result2[$j]['showname'] =  $key;
  	       $result2[$j][5] =  $get->mag;
  	       $result2[$j]['objectmagnitude'] =  $get->mag;
  	       $result2[$j][6] =  $get->subr;
  	       $result2[$j]['objectsurfacebrightness'] =  $get->subr;
  	       $result2[$j][7] =  $get->ra;
  	       $result2[$j]['objectra'] =  $get->ra;
  	       $result2[$j][8] =  $get->decl;
  	       $result2[$j]['objectdecl'] =  $get->decl;
  	       $result2[$j][18] = $get->diam1;
  	       $result2[$j]['objectdiam1'] = $get->diam1;
  	       $result2[$j][19] = $get->diam2;
  	       $result2[$j]['objectdiam2'] = $get->diam2;
  	       $result2[$j][20] = $get->pa;
  	       $result2[$j]['objectpa'] = $get->pa;
           $result2[$j][24] = $value[0]; 
           $result2[$j]['objectpositioninlist'] = $value[0]; 
           $result2[$j][26] = $get->SBObj; 
           $result2[$j]['objectsbcalc'] = $get->SBObj; 
           $result2[$j][27] = $get->description;
           $result2[$j]['objectdescription'] = $get->description;
					 if(count($value)==3)
					 { $result2[$j][30] = $value[2]; // optional description from lists
					   $result2[$j]['objectlistdescription'] = $value[2];
					 }
					 reset($objAtlas->atlasCodes);
					 while(list($key,$value)=each($objAtlas->atlasCodes))
					   $result2[$j][$key] =  $get->$key;
		     }
         $j++;		
       }
     }
	 }
	 $obs=$result2;
   $obs=$this->getObjectVisibilities($obs);
   return $obs;
 }

 function getPartOfObjects($obs)
 { $poobs=array();
   $i=0;
	 while(list($key,$value)=each($obs))
   { $poobs[]=$value;
		 $run=$GLOBALS['objDatabase']->selectRecordset("SELECT DISTINCT (objectpartof.objectname) AS name, " .
	                                                                 "CONCAT((\"" . $value['objectname'] . "\"), \"-\", (objectpartof.objectname)) As showname  " . 
	                                                 "FROM objectpartof " . 
				                                           "WHERE objectpartof.partofname = \"" . $value['objectname'] . "\";");
		 $temp=array();
		 while($get=mysql_fetch_object($run))
	     $temp[$get->showname]=array($i++,$get->name);
		 if(count($temp)>0)
     { $temp=$this->getSeenObjectDetails($temp);
       $poobs=array_merge($poobs,$temp);
     }
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
 { $obs=array();
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
   { if(substr($queries['inList'],0,7)=="Public:")
		 { $sql1 .= "JOIN observerobjectlist AS A " .
	              "ON A.objectname = objects.name ";
       $sql2 .= "JOIN observerobjectlist AS A " .
	              "ON A.objectname = objects.name ";
		   $sqland .= "AND A.listname = \"" . $queries['inList'] . "\" AND A.objectname <>\"\" ";
	   }
		 elseif(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
		 { $sql1 .= "JOIN observerobjectlist AS A " .
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
   $sqland.=(array_key_exists('type',$queries)&&$queries['type'])?" AND (objects.type=\"".$queries['type']."\")":'';
   $sqland.=(array_key_exists('con',$queries)&&$queries['con'])?" AND (objects.con=\"".$queries['con']."\")":'';
   $sqland.=(array_key_exists('minmag',$queries)&&$queries['minmag'])?" AND (objects.mag>\"".$queries["minmag"]."\" or objects.mag like \"" . $queries["minmag"] . "\")":'';
   $sqland.=(array_key_exists('maxmag',$queries)&&$queries['maxmag'])?" AND (objects.mag<\"".$queries["maxmag"]."\" or objects.mag like \"" . $queries["maxmag"] . "\")":'';
   $sqland.=(array_key_exists('minsubr',$queries)&&$queries['minsubr'])?" AND objects.subr>=\"".$queries["minsubr"]."\"":'';
   $sqland.=(array_key_exists('maxsubr',$queries)&&$queries['maxsubr'])?" AND objects.subr<=\"".$queries["maxsubr"]."\"":'';
   $sqland.=(array_key_exists('ra',$queries)&&$queries['ra'])?" AND (objects.ra > \"" . $queries["minra"] . "\" or objects.ra like \"" . $queries["minra"] . "\")":'';
   $sqland.=(array_key_exists('ra',$queries)&&$queries['ra'])?" AND (objects.ra <= \"" . $queries["maxra"] . "\" or objects.ra like \"" . $queries["maxra"] . "\")":'';
   $sqland.=(array_key_exists('mindecl',$queries)&&$queries['mindecl'])?" AND objects.decl >= \"" . $queries["mindecl"] . "\"":'';
   $sqland.=(array_key_exists('maxdecl',$queries)&&$queries['maxdecl'])?" AND objects.decl <= \"" . $queries["maxdecl"] . "\"":'';
   $sqland.=(array_key_exists('mindiam1',$queries)&&$queries['mindiam1'])?" AND (objects.diam1 > \"" . $queries["mindiam1"] . "\" or objects.diam1 like \"" . $queries["mindiam1"] . "\")":'';
   $sqland.=(array_key_exists('maxdiam1',$queries)&&$queries['maxdiam1'])?" AND (objects.diam1 <= \"" . $queries["maxdiam1"] . "\" or objects.diam1 like \"" . $queries["maxdiam1"] . "\")":'';
   $sqland.=(array_key_exists('mindiam2',$queries)&&$queries['mindiam2'])?" AND (objects.diam2 > \"" . $queries["mindiam2"] . "\" or objects.diam2 like \"" . $queries["mindiam2"] . "\")":'';
   $sqland.=(array_key_exists('maxdiam2',$queries)&&$queries['maxdiam2'])?" AND(objects.diam2 <= \"" . $queries["maxdiam2"] . "\" or objects.diam2 like \"" . $queries["maxdiam2"] . "\")":'';
   $sqland.=(array_key_exists('atlas',$queries)&&$queries['atlas']&&array_key_exists('atlasPageNumber',$queries)&&$queries["atlasPageNumber"])?" AND (objects.".$queries["atlas"]."=\"".$queries["atlasPageNumber"]."\")":'';
	 $sqland = substr($sqland, 4);
   if(trim($sqland)=='') 
	   $sqland=" (objectnames.altname like \"%\")";
   if($partof)
     $sql="(".$sql1.$sqland.") UNION (".$sql2. $sqland.")";
   else
     $sql = $sql1 . $sqland;		
//echo $sql;
	$run=$GLOBALS['objDatabase']->selectRecordset($sql);
  $i=0;
  if (array_key_exists('name',$queries)&&$queries["name"])
	{ while($get = mysql_fetch_object($run))
      if($get->showname==$get->name)
      { if(!array_key_exists($get->showname, $obs))
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
  if(array_key_exists('minContrast', $queries)&&$queries["minContrast"])
    for($new_obs=$obs,$obs=array();list($key,$value)=each($new_obs);)
      if ($value['objectcontrast']>=$queries["minContrast"])
			  $obs[]=$value;
  if(array_key_exists('maxContrast', $queries)&&$queries["maxContrast"])
    for($new_obs=$obs,$obs=array();list($key,$value)=each($new_obs);)
      if ($value['objectcontrast']<=$queries["maxContrast"])
			  $obs[]=$value;

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
	if(substr($cat,0,5)=="List:")
    if(substr($cat,5,7)=="Public:")
      $sql = "SELECT DISTINCT observerobjectlist.objectname, observerobjectlist.objectname As altname, observerobjectlist.objectplace As catindex  FROM observerobjectlist " .
	  		     "WHERE (observerobjectlist.listname = \"" . substr($cat,5) . "\")";
	  else
      $sql = "SELECT DISTINCT observerobjectlist.objectname, observerobjectlist.objectname As altname, observerobjectlist.objectplace As catindex FROM observerobjectlist " .
	  	   	   "WHERE (observerobjectlist.listname = \"" . substr($cat,5) . "\") AND (observerobjectlist.observerid = \"" . $_SESSION['deepskylog_id'] . "\")";
	else
    $sql = "SELECT DISTINCT objectnames.objectname, objectnames.catindex, objectnames.altname " .
	         "FROM objectnames WHERE objectnames.catalog = \"$cat\"";
  $run = mysql_query($sql) or die(mysql_error());
  $obs=array();
	while($get = mysql_fetch_object($run))
	  if($get->objectname)
      $obs[$get->catindex] = array($get->objectname, $get->altname);
	uksort($obs,"strnatcasecmp");
  $db->logout();
  return $obs;
 }

// getExactObject returns the exact name of an object
 function getLikeDsObject($value, $cat='', $catindex='')
 {$result=array();
  $db = new database;
  $db->login();
	$value2=trim($value);
	$value=strtoupper(trim($value));
  if($value!='')
    $sql = "SELECT objectnames.objectname FROM objectnames " .
		  	   "WHERE UPPER(altname) LIKE \"$value\" " .
					 "OR altname LIKE \"$value2\"";
	else
	{ $catindex=ucwords($catindex);
    $sql = "SELECT objectnames.objectname FROM objectnames " .
		       "WHERE CONCAT(objectnames.catalog, ' ', objectnames.catindex) LIKE \"$cat $catindex\"";
	}
	$run = mysql_query($sql) or die(mysql_error());
  $db->logout();
	while($get = mysql_fetch_object($run))
    $result[] = $get->objectname;
	return $result;
 }

 // getExactObject returns the exact name of an object
 function getExactDsObject($value, $cat='', $catindex='')
 { if($value)
    $sql = "SELECT objectnames.objectname FROM objectnames " .
		  	   "WHERE UPPER(altname) = \"".strtoupper(trim($value))."\" " .
					 "OR altname = \"".trim($value)."\"";
	 else
	 { $catindex=ucwords($catindex);
     $sql = "SELECT objectnames.objectname FROM objectnames " .
		        "WHERE objectnames.catalog=\"".$cat."\" AND objectnames.catindex=\"".$catindex."\"";
	 }
	 return $GLOBALS['objDatabase']->selectSingleValue($sql,'objectname','');
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


 function getConstellations()                                                   // getConstellations returns a list of all different constellations
 { return $GLOBALS['objDatabase']->selectSingleArray("SELECT DISTINCT con FROM objects ORDER BY con",'con');
 }
 function getCatalogues()                                                       // getCatalogues returns a list of all different catalogues
 { $ret=$GLOBALS['objDatabase']->selectSingleArray("SELECT DISTINCT objectnames.catalog FROM objectnames",'catalog');
   natcasesort($ret);
   reset($ret);
   array_unshift($ret, "M", "NGC", "Caldwell", "H400", "HII", "IC");
   return $ret;
 }
 function getCataloguesAndLists()
 { $ret=$GLOBALS['objDatabase']->selectSingleArray("SELECT DISTINCT objectnames.catalog FROM objectnames",'catalog');
   natcasesort($ret);
   reset($ret);
   array_unshift($ret, "M", "NGC", "Caldwell", "H400", "HII", "IC");
	 if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
	 { $lsts = $GLOBALS['objList']->getLists();
		 while(list($key,$value)=each($lsts))
		   $ret[]='List:'.$value; 
	 }
   return $ret;
 }
 function getDsObjectTypes()                                                    // getTypes returns a list of all different types
 { return $GLOBALS['objDatabase']->selectSingleArray("SELECT DISTINCT type FROM objects ORDER BY type",'type');
 }
 function my_array_unique($somearray)                                           // my_array_unique returns a unique array, where the keys increment.
 { $tmparr = array_unique($somearray); 
   $i=0; 
   foreach ($tmparr as $v) 
   { $newarr[$i] = $v; 
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


 // getObservedByUser returns +1 if the object is already observed by the 
 // given user, -1 if the object is not yet observed
 function getObservedbyUser($name, $observerid)
 {
  $observations = new Observations;
  $query = array("object" => "$name", "observer" => "$observerid", 
		 "instrument" => "", "location" => "", "mindate" => "", 
		 "maxdate" => "");
		
  $obs = $GLOBALS['objObservation']->getObservationFromQuery($query);

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

  $obs = $GLOBALS['objObservation']->getObservationFromQuery($query);

  $return = -1;

  if ($obs != "")
  {
   $return = count($obs);
  }

  return $return;
 }


 // setType sets a new type for the object
 function setDsObjectType($name, $type)
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

  $atlas = new Atlasses;
  
  $sql = "UPDATE objects SET ra = \"$ra\" WHERE name = \"$name\"";
  
  $run = mysql_query($sql) or die(mysql_error());

  // Calculate the pages for the atlases
  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $decl = $get->decl;

  $urano = $atlas->calculateUranometriaPage($ra, $decl);
  $uranonew = $atlas->calculateNewUranometriaPage($ra, $decl);
  $skyatlas = $atlas->calculateSkyAtlasPage($ra, $decl);
  $msa = $atlas->calculateMilleniumPage($ra, $decl);
  $taki = $atlas->calculateTakiPage($ra, $decl);
  $psa = $atlas->calculatePocketSkyAtlasPage($ra, $decl);
  $torresB = $atlas->calculateTorresBPage($ra, $decl);
  $torresBC = $atlas->calculateTorresBCPage($ra, $decl);
  $torresC = $atlas->calculateTorresCPage($ra, $decl);
  
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

  $atlas = new Atlasses;

  $sql = "UPDATE objects SET decl = \"$decl\" WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $sql = "SELECT * FROM objects WHERE name = \"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  // Calculate the pages for the atlases
  $get = mysql_fetch_object($run);

  $ra = $get->ra;

  $urano = $atlas->calculateUranometriaPage($ra, $decl);
  $uranonew = $atlas->calculateNewUranometriaPage($ra, $decl);
  $skyatlas = $atlas->calculateSkyAtlasPage($ra, $decl);
  $msa = $atlas->calculateMilleniumPage($ra, $decl);
  $taki = $atlas->calculateTakiPage($ra, $decl);
  $psa = $atlas->calculatePocketSkyAtlasPage($ra, $decl);
  $torresB = $atlas->calculateTorresBPage($ra, $decl);
  $torresBC = $atlas->calculateTorresBCPage($ra, $decl);
  $torresC = $atlas->calculateTorresCPage($ra, $decl);
  
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
 function getNumberOfObjectsInCatalogue($catalog)
 {
  $db = new database;
  $db->login();
	if(substr($catalog,0,5)=="List:")
    if(substr($catalog,5,7)=="Public:")
      $sql = "SELECT COUNT(DISTINCT observerobjectlist.objectname)-1 AS number FROM observerobjectlist WHERE observerobjectlist.listname = \"" . substr($catalog,5) . "\"";
	  else
      $sql = "SELECT COUNT(DISTINCT observerobjectlist.objectname)-1 AS number FROM observerobjectlist WHERE observerobjectlist.listname = \"" . substr($catalog,5) . "\" AND observerobjectlist.observerid = \"" . $_SESSION['deepskylog_id'] . "\"";		
	else
    $sql = "SELECT COUNT(DISTINCT catindex) AS number FROM objectnames WHERE catalog = \"$catalog\"";
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


 function showObjects($link, $_SID, $min, $max, $myList, $noShow='', $showRank=0, $ranklist='')
 { $atlas='';
   echo "<table width=\"100%\">\n";
   echo "<tr class=\"type3\">\n";
	 tableSortHeader(LangOverviewObjectsHeader1,  $link."&amp;sort=showname");
	 tableSortHeader(LangOverviewObjectsHeader2,  $link."&amp;sort=objectconstellation");
	 tableSortHeader(LangOverviewObjectsHeader3,  $link."&amp;sort=objectmagnitude");
	 tableSortHeader(LangOverviewObjectsHeader3b, $link."&amp;sort=objectsurfacebrightness");
	 tableSortHeader(LangOverviewObjectsHeader4,  $link."&amp;sort=objecttype");
   if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
	 { $atlas = $GLOBALS['objObserver']->getStandardAtlasCode($_SESSION['deepskylog_id']);
     tableSortHeader($GLOBALS['objAtlas']->atlasCodes[$atlas], $link."&amp;sort=".$atlas);
	   tableSortHeader(LangViewObjectFieldContrastReserve, $link."&amp;sort=objectcontrast");
	   tableSortHeader(LangViewObjectFieldMagnification, $link."&amp;sort=objectoptimalmagnification");
	   tableSortHeader(LangOverviewObjectsHeader7, $link."&amp;sort=objectseen");
	   tableSortHeader(LangOverviewObjectsHeader8, $link."&amp;sort=objectlastseen");
   }
   if($myList)
     echo("<td align=\"center\"><a href=\"" . $link . "&amp;min=" . $min . "&amp;addAllObjectsFromPageToList=true\" title=\"" . LangListQueryObjectsMessage1 . $_SESSION['listname'] . "\">P</a></td>");
 	 $count = $min; // counter for altering table colors
	 $countline = 0;
	 if($max>count($_SESSION[$_SID]))
		 $max=count($_SESSION[$_SID]);
	 while($count < $max)
   { if($_SESSION[$_SID][$count]['objectname']==$noShow)
  	   $typefield = "class=\"type3\"";
  	 else
	     $typefield = "class=\"type".(2-($countline%2)."\"");
     $name = $_SESSION[$_SID][$count]['objectname'];
     $showname = $_SESSION[$_SID][$count]['showname'];
     $con = $_SESSION[$_SID][$count]['objectconstellation'];
     $type = $_SESSION[$_SID][$count]['objecttype'];
     $magnitude = sprintf("%01.1f", $_SESSION[$_SID][$count]['objectmagnitude']);
     if($magnitude == 99.9)
       $magnitude = "&nbsp;&nbsp;-&nbsp;";		
     $sb = sprintf("%01.1f", $_SESSION[$_SID][$count]['objectsurfacebrightness']);
     if($sb == 99.9)
       $sb = "&nbsp;&nbsp;-&nbsp;";
     // RIGHT ASCENSION
     $ra = raToString($_SESSION[$_SID][$count]['objectra']);
     // DECLINATION
     $decl = decToStringDegMin($_SESSION[$_SID][$count]['objectdecl']);
	 // SEEN
     $seen="<a href=\"".$GLOBALS['baseURL']."index.php?indexAction=detail_object&amp;object=" . urlencode($name) . "\" title=\"" . LangObjectNSeen . "\">-</a>";
     if(substr($_SESSION[$_SID][$count][3],0,1)=="X")
       $seen = "<a href=\"".$GLOBALS['baseURL']."index.php?indexAction=result_selected_observations&amp;object=" . urlencode($name) . "\" title=\"" . LangObjectXSeen . "\">" . $_SESSION[$_SID][$count][3] . "</a>";
     if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && (substr($_SESSION[$_SID][$count][3],0,1)=="Y"))
       $seen = "<a href=\"".$GLOBALS['baseURL']."index.php?indexAction=result_selected_observations&amp;object=" . urlencode($name) . "\" title=\"" . LangObjectYSeen . "\">" . $_SESSION[$_SID][$count][3] . "</a>";
     $seendate = "<a href=\"".$GLOBALS['baseURL']."index.php?indexAction=detail_object&amp;object=" . urlencode($name) . "\" title=\"" . LangObjectNSeen . "\">-</a>";
     if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && (substr($_SESSION[$_SID][$count][3],0,1)=="Y"))
       $seendate = "<a href=\"".$GLOBALS['baseURL']."index.php?indexAction=detail_observation&amp;observation=" . $_SESSION[$_SID][$count]['objectlastobservationid'] . "\" title=\"" . LangObjectYSeen . "\">" . $_SESSION[$_SID][$count]['objectlastseen'] . "</a>";
	 echo "<tr $typefield>\n";
     echo "<td align=\"center\"><a href=\"".$GLOBALS['baseURL']."index.php?indexAction=detail_object&amp;object=" . urlencode($name) . "\">$showname</a></td>\n";
     echo "<td align=\"center\">".$GLOBALS[$_SESSION[$_SID][$count]['objectconstellation']]."</td>\n";
     echo "<td align=\"center\">$magnitude</td>\n";
     echo "<td align=\"center\">$sb</td>\n";
     echo "<td align=\"center\">".$GLOBALS[$type]."</td>\n";
     // Page number in atlas
     if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id']) 
	 { $page = $_SESSION[$_SID][$count][$atlas];
       echo "<td align=\"center\" onmouseover=\"Tip('".$GLOBALS['objAtlas']->atlasCodes[$atlas]."')\">".$page."</td>\n";
       echo "<td align=\"center\" class=\"".$_SESSION[$_SID][$count][22]."\" onmouseover=\"Tip('".$_SESSION[$_SID][$count][23]."')\">".$_SESSION[$_SID][$count][21]."</td>\n";
       echo "<td align=\"center\">".$_SESSION[$_SID][$count]['objectoptimalmagnification']."</td>\n";
       echo "<td align=\"center\" class=\"seen\">$seen</td>";
       echo "<td align=\"center\" class=\"seen\">$seendate</td>";
	 }
  	 if($myList)
  	 { echo("<td align=\"center\">");
       if($GLOBALS['objList']->checkObjectInMyActiveList($name))
         echo("<a href=\"" . $link . "&amp;min=" . $min . "&amp;removeObjectFromList=" . urlencode($name) . "\" title=\"" . $name . LangListQueryObjectsMessage3 . $_SESSION['listname'] . "\">R</a>");
       else
         echo("<a href=\"" . $link . "&amp;min=" . $min . "&amp;addObjectToList=" . urlencode($name) . "&amp;showname=" . urlencode($showname) . "\" title=\"" . $name . LangListQueryObjectsMessage2 . $_SESSION['listname'] . "\">L</a>");
      echo("</td>");
  	 }
     echo("</tr>");
     $countline++; // increase line counter

     $count++; // increase object counter
   }   
   echo "</table>\n";
 }
 
 function showObject($object, $zoom = 30)
 { include_once "contrast.php";
   $contrastObj = new Contrast;
   global $deepskylive;	
   $object = $this->getDsObjectName($object);
   $_SESSION['object']=$object;
   echo "<table width=\"100%\">";
   echo "<tr class=\"type2\">";
	 echo "<td class=\"fieldname\" align=\"right\" width=\"25%\">";
   echo LangViewObjectField1;
   echo "</td><td width=\"25%\">";
   echo "<a href=\"".$GLOBALS['baseURL']."index.php?indexAction=detail_object&amp;object=" . urlencode(stripslashes($object)) . "\">" . (stripslashes($object)) . "</a>";
   echo("</td>");
	 if(array_key_exists('deepskylog_id',$_SESSION)&&$_SESSION['deepskylog_id']&&($standardAtlasCode=$GLOBALS['objObserver']->getStandardAtlasCode($_SESSION['deepskylog_id'])))
   { echo "<td class=\"fieldname\" align=\"right\" width=\"25%\">"; 
     echo $GLOBALS['objAtlas']->atlasCodes[$standardAtlasCode].LangViewObjectField10;
     echo "</td><td width=\"25%\">";
     echo $GLOBALS['objAtlas']->getAtlasPage($standardAtlasCode, $object);
     echo"</td>" ;
   }	
   else
   { echo "<td class=\"fieldname\" align=\"right\" width=\"25%\">";
     echo "&nbsp;";
     echo "</td><td width=\"25%\">";
     echo "&nbsp;";
     echo "</td>";
   }
	echo "</tr>";
  // ALTERNATIVE NAME
  $altnames = $this->getAlternativeNames($object);
  echo "<tr class=\"type1\"><td class=\"fieldname\" align=\"right\" width=\"25%\">";
  echo LangViewObjectField2;
  echo "</td><td width=\"25%\">";
  $alt="";
	while(list($key, $value) = each($altnames)) // go through names array
  { if(trim($value)!=trim($object))
		{ if($alt)
			  $alt.="<br />".trim($value);
			else
			  $alt = trim($value);
    }
	}
	if($alt) echo $alt; else echo "-";
  echo "</td>";
  // PART OF
  $contains=$this->getContainsNames($object);
	$partof=$this->getPartOfNames($object);
  echo "<td class=\"fieldname\" align=\"right\" width=\"25%\">";
  echo LangViewObjectField2b;
  echo "</td><td width=\"25%\">";
	$containst="";
  while(list($key, $value) = each($contains)) // go through names array
  { if(trim($value)!=trim($object))
		{ if($containst)
			  $containst.="/"."(<a href=\"".$GLOBALS['baseURL']."index.php?indexAction=detail_object&amp;object=".urlencode(trim($value))."\">".trim($value)."</a>)";
			else
			  $containst="(<a href=\"".$GLOBALS['baseURL']."index.php?indexAction=detail_object&amp;object=".urlencode(trim($value))."\">".trim($value)."</a>)";
    }
  }
	if($containst=="") echo "(-)/"; else echo $containst . "/";
	$partoft = "";
  while(list($key, $value) = each($partof)) // go through names array
  { if(trim($value)!=trim($object))
		{ if($partoft)
			  $partoft .= "/" . "<a href=\"".$GLOBALS['baseURL']."index.php?indexAction=detail_object&amp;object=" . urlencode(trim($value)) . "\">" . trim($value) . "</a>";
			else
			  $partoft= "<a href=\"".$GLOBALS['baseURL']."index.php?indexAction=detail_object&amp;object=" . urlencode(trim($value)) . "\">" . trim($value) . "</a>";
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
  echo(raToString($ra));
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
  echo $GLOBALS[$const];
  echo("</td>");
  // TYPE
  echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
  echo LangViewObjectField6;
  echo("</td><td width=\"25%\">");
  $type = $this->getDsObjectType($object);
  echo $GLOBALS[$type];
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
	{ $contrast = sprintf("%.2f", $contrastCalc[0]);
		if ($contrastCalc[2] == "")
		{ $prefMag = sprintf("%d", $contrastCalc[1]) . "x";
		}
		else
		{ $prefMag = sprintf("%d", $contrastCalc[1]) . "x - " . $contrastCalc[2];
		}
  } 
	else 
	{ $contrast = "-";
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
	if(array_key_exists('listname',$_SESSION) && ($GLOBALS['objList']->checkObjectInMyActiveList($object)))
	{ if($GLOBALS['objList']->checkList($_SESSION['listname'])==2)
    { echo("<form action=\"".$GLOBALS['baseURL']."index.php?indexAction=detail_object\">");    	
      echo("<input type=\"hidden\" name=\"indexAction\" value=\"detail_object\" />");
      echo("<input type=\"hidden\" name=\"object\" value=\"" . $object . "\" />");
      echo("<input type=\"hidden\" name=\"editListObjectDescription\" value=\"editListObjectDescription\"/>");
		  echo "<td align=\"right\">";
  	  echo LangViewObjectListDescription;
      echo("<input type=\"submit\" name=\"Go\" value=\"" . 'Edit Description' . "\" />");
  	  echo "</td>";
  	  echo "<td colspan=\"3\">";
      echo("<textarea name=\"description\" class=\"listdescription\">");
		  echo $GLOBALS['objList']->getListObjectDescription($object); 
		  echo("</textarea>");
      echo("</form>");
		}
		else
		{	echo "<tr>";
  	  echo "<td align=\"right\">";
  	  echo LangViewObjectListDescription;
  	  echo "</td>";
  	  echo "<td colspan=\"3\">";
		  echo $GLOBALS['objList']->getListObjectDescription($object);
  	}
		echo "</td>";
  	echo "</tr>";
  }
	elseif($descriptionDsOject=$this->getDescriptionDsObject($object))
	{
  	echo "<tr>";
  	echo "<td align=\"right\">";
  	echo LangViewObjectNGCDescription;
  	echo "</td>";
  	echo "<td colspan=\"3\">";
  	echo $descriptionDsOject;
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
  echo("<form action=\"".$GLOBALS['baseURL']."index.php?indexAction=view_image\" method=\"post\">");
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
    echo("<form action=\"".$GLOBALS['baseURL']."index.php?indexAction=detail_object&amp;object=".urlencode($object)."&amp;zoom=" . $zoom . "\" method=\"post\">");
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
 
 function getNearbyObjects($objectname, $dist)
 { $run=$GLOBALS['objDatabase']->selectRecordset("SELECT objects.ra, objects.decl FROM objects WHERE name = \"$objectname\"");
   $get = mysql_fetch_object($run);
	 $ra = $get->ra; $decl = $get->decl;
	 $dra = 0.0011 * $dist / cos($decl/180*3.1415926535);
   $run = $GLOBALS['objDatabase']->selectRecordset("SELECT objects.name FROM objects WHERE ((objects.ra > $ra - $dra) AND (objects.ra < $ra + $dra) AND (objects.decl > $decl - ($dist/60)) AND (objects.decl < $decl + ($dist/60))) ORDER BY objects.name");
	 for($result=array(),$i=0;($get=mysql_fetch_object($run));$i++)
//	   if($get->name!=$objectname)
		   $result[$get->name] = array($i, $get->name);
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
$objObject=new Objects;
/* OBSOLETE FUNCTIONS
 function getSortedObjects($sort)                                               // getSortedObjects returns an array with the names of all objects, sorted by  the column specified in $sort
 { return $GLOBALS['objDatabase']->selectSingleArray("SELECT name FROM objects ORDER BY $name",'name');
 }
*/
?>
