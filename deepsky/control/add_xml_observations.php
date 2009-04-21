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
    
    $targetArray = Array();
    
    foreach( $target as $target )
    {
      $targetInfoArray = Array();
      $targetid = $target->getAttribute("id");
      $targetInfoArray["name"] = $target->getElementsByTagName( "name" )->item(0)->nodeValue;
      $aliases = $target->getElementsByTagName( "alias" );
      
      $aliasesArray = Array();
      $cnt = 0;
      foreach ($aliases as $aliases) {
        $aliasesArray["alias".$cnt] = $aliases->nodeValue; 
        $cnt = $cnt + 1;
      }
      // Check if the datasource is defined. If this is the case, get it. Otherwise, set to OAL
      if ($target->getElementsByTagName( "datasource" )->item(0)) {
        $targetInfoArray["datasource"] = $target->getElementsByTagName( "datasource" )->item(0)->nodeValue;
      } else {
        $targetInfoArray["datasource"] = "OAL";
      }
      
      // Get the type
      $type =  $target->getAttribute("xsi:type");
      
      if ($type == "fgca:deepSkyAS" || $type == "fgca:deepSkyDS") {
        $targetInfoArray["type"] = "ASTER";
      } else if ($type == "fgca:deepSkySC" || $type == "fgca:deepSkyOC") {
        $targetInfoArray["type"] = "OPNCL";
      } else if ($type == "fgca:deepSkyGC") {
        $targetInfoArray["type"] = "GLOCL";
      } else if ($type == "fgca:deepSkyGX") {
        $targetInfoArray["type"] = "GALXY";
      } else if ($type == "fgca:deepSkyGN") {
        $targetInfoArray["type"] = "BRTNB";
      } else if ($type == "fgca:deepSkyGN") {
        $targetInfoArray["type"] = "BRTNB";
      } else if ($type == "fgca:deepSkyPN") {
        $targetInfoArray["type"] = "PLNNB";
      } else if ($type == "fgca:deepSkyQS") {
        $targetInfoArray["type"] = "QUASR";
      } else if ($type == "fgca:deepSkyDN") {
        $targetInfoArray["type"] = "DRKNB";
      } else if ($type == "fgca:deepSkyNA") {
        $targetInfoArray["type"] = "NONEX";
      }

      $cons = $targetInfoArray["constellation"] = $target->getElementsByTagName( "constellation" )->item(0)->nodeValue;
      // Convert the constellation to the 3 letter code
      if (strlen($cons) > 3) {
        $cons = strtolower($cons);
        if ($cons == "andromeda") {
          $targetInfoArray["constellation"] = "AND";
        } else if ($cons == "antlia") {
          $targetInfoArray["constellation"] = "ANT";
        } else if ($cons == "apus") {
          $targetInfoArray["constellation"] = "APS";
        } else if ($cons == "aquarius") {
          $targetInfoArray["constellation"] = "AQR";
        } else if ($cons == "aquila") {
          $targetInfoArray["constellation"] = "AQL";
        } else if ($cons == "aries") {
          $targetInfoArray["constellation"] = "ARI";
        } else if ($cons == "auriga") {
          $targetInfoArray["constellation"] = "AUR";
        } else if ($cons == "bootes") {
          $targetInfoArray["constellation"] = "BOO";
        } else if ($cons == "caelum") {
          $targetInfoArray["constellation"] = "CAE";
        } else if ($cons == "camelopardalis") {
          $targetInfoArray["constellation"] = "CAM";
        } else if ($cons == "cancer") {
          $targetInfoArray["constellation"] = "CNC";
        } else if ($cons == "canes venatici") {
          $targetInfoArray["constellation"] = "CVN";
        } else if ($cons == "canis major" || $cons == "canis maior") {
          $targetInfoArray["constellation"] = "CMA";
        } else if ($cons == "canis minor") {
          $targetInfoArray["constellation"] = "CMI";
        } else if ($cons == "capricornus") {
          $targetInfoArray["constellation"] = "CAP";
        } else if ($cons == "carina") {
          $targetInfoArray["constellation"] = "CAR";
        } else if ($cons == "cassiopeia") {
          $targetInfoArray["constellation"] = "CAS";
        } else if ($cons == "centaurus") {
          $targetInfoArray["constellation"] = "CEN";
        } else if ($cons == "cepheus") {
          $targetInfoArray["constellation"] = "CEP";
        } else if ($cons == "cetus") {
          $targetInfoArray["constellation"] = "CET";
        } else if ($cons == "chamaeleon") {
          $targetInfoArray["constellation"] = "CHA";
        } else if ($cons == "circinus") {
          $targetInfoArray["constellation"] = "CIR";
        } else if ($cons == "columba") {
          $targetInfoArray["constellation"] = "COL";
        } else if ($cons == "coma berenices") {
          $targetInfoArray["constellation"] = "COM";
        } else if ($cons == "corona australis") {
          $targetInfoArray["constellation"] = "CRA";
        } else if ($cons == "corona borealis") {
          $targetInfoArray["constellation"] = "CRB";
        } else if ($cons == "corvus") {
          $targetInfoArray["constellation"] = "CRV";
        } else if ($cons == "crater") {
          $targetInfoArray["constellation"] = "CRT";
        } else if ($cons == "crux") {
          $targetInfoArray["constellation"] = "CRU";
        } else if ($cons == "cygnus") {
          $targetInfoArray["constellation"] = "CYG";
        } else if ($cons == "delphinus") {
          $targetInfoArray["constellation"] = "DEL";
        } else if ($cons == "dorado") {
          $targetInfoArray["constellation"] = "DOR";
        } else if ($cons == "draco") {
          $targetInfoArray["constellation"] = "DRA";
        } else if ($cons == "equuleus") {
          $targetInfoArray["constellation"] = "EQU";
        } else if ($cons == "eridanus") {
          $targetInfoArray["constellation"] = "ERI";
        } else if ($cons == "fornax") {
          $targetInfoArray["constellation"] = "FOR";
        } else if ($cons == "gemini") {
          $targetInfoArray["constellation"] = "GEM";
        } else if ($cons == "grus") {
          $targetInfoArray["constellation"] = "GRU";
        } else if ($cons == "hercules") {
          $targetInfoArray["constellation"] = "HER";
        } else if ($cons == "horologium") {
          $targetInfoArray["constellation"] = "HOR";
        } else if ($cons == "hydra") {
          $targetInfoArray["constellation"] = "HYA";
        } else if ($cons == "hydrus") {
          $targetInfoArray["constellation"] = "HYI";
        } else if ($cons == "indus") {
          $targetInfoArray["constellation"] = "IND";
        } else if ($cons == "lacerta") {
          $targetInfoArray["constellation"] = "LAC";
        } else if ($cons == "leo minor") {
          $targetInfoArray["constellation"] = "LMI";
        } else if ($cons == "lepus") {
          $targetInfoArray["constellation"] = "LEP";
        } else if ($cons == "libra") {
          $targetInfoArray["constellation"] = "LIB";
        } else if ($cons == "lupus") {
          $targetInfoArray["constellation"] = "LUP";
        } else if ($cons == "lynx") {
          $targetInfoArray["constellation"] = "LYN";
        } else if ($cons == "lyra") {
          $targetInfoArray["constellation"] = "LYR";
        } else if ($cons == "mensa") {
          $targetInfoArray["constellation"] = "MEN";
        } else if ($cons == "microscopium") {
          $targetInfoArray["constellation"] = "MIC";
        } else if ($cons == "monoceros") {
          $targetInfoArray["constellation"] = "MON";
        } else if ($cons == "musca") {
          $targetInfoArray["constellation"] = "MUS";
        } else if ($cons == "norma") {
          $targetInfoArray["constellation"] = "NOR";
        } else if ($cons == "octans") {
          $targetInfoArray["constellation"] = "OCT";
        } else if ($cons == "ophiuchus") {
          $targetInfoArray["constellation"] = "OPH";
        } else if ($cons == "orion") {
          $targetInfoArray["constellation"] = "ORI";
        } else if ($cons == "pavo") {
          $targetInfoArray["constellation"] = "PAV";
        } else if ($cons == "pegasus") {
          $targetInfoArray["constellation"] = "PEG";
        } else if ($cons == "perseus") {
          $targetInfoArray["constellation"] = "PER";
        } else if ($cons == "phoenix") {
          $targetInfoArray["constellation"] = "PHE";
        } else if ($cons == "pictor") {
          $targetInfoArray["constellation"] = "PIC";
        } else if ($cons == "pisces") {
          $targetInfoArray["constellation"] = "PSC";
        } else if ($cons == "pisces austrinus") {
          $targetInfoArray["constellation"] = "PSA";
        } else if ($cons == "puppis") {
          $targetInfoArray["constellation"] = "PUP";
        } else if ($cons == "pyxis") {
          $targetInfoArray["constellation"] = "PYX";
        } else if ($cons == "reticulum") {
          $targetInfoArray["constellation"] = "RET";
        } else if ($cons == "sagitta") {
          $targetInfoArray["constellation"] = "SGE";
        } else if ($cons == "sagittarius") {
          $targetInfoArray["constellation"] = "SGR";
        } else if ($cons == "scorpius") {
          $targetInfoArray["constellation"] = "SCO";
        } else if ($cons == "sculptor") {
          $targetInfoArray["constellation"] = "SCL";
        } else if ($cons == "scutum") {
          $targetInfoArray["constellation"] = "SCT";
        } else if ($cons == "serpens") {
          $targetInfoArray["constellation"] = "SER";
        } else if ($cons == "sextans") {
          $targetInfoArray["constellation"] = "SEX";
        } else if ($cons == "taurus") {
          $targetInfoArray["constellation"] = "TAU";
        } else if ($cons == "telescopium") {
          $targetInfoArray["constellation"] = "TEL";
        } else if ($cons == "triangulum australe") {
          $targetInfoArray["constellation"] = "TRA";
        } else if ($cons == "triangulum") {
          $targetInfoArray["constellation"] = "TRI";
        } else if ($cons == "tucana") {
          $targetInfoArray["constellation"] = "TUC";
        } else if ($cons == "ursa major" || $cons == "ursa maior") {
          $targetInfoArray["constellation"] = "UMA";
        } else if ($cons == "ursa minor") {
          $targetInfoArray["constellation"] = "UMI";
        } else if ($cons == "vela") {
          $targetInfoArray["constellation"] = "VEL";
        } else if ($cons == "virgo") {
          $targetInfoArray["constellation"] = "VIR";
        } else if ($cons == "volans") {
          $targetInfoArray["constellation"] = "VOL";
        } else if ($cons == "vulpecula") {
          $targetInfoArray["constellation"] = "VUL";
        }
      } else {
        $targetInfoArray["constellation"] = strtoupper($cons);
      }
      
      // Get Ra and convert it to degrees
      $unit = $target->getElementsByTagName( "position" )->item(0)->getElementsByTagName( "ra" )->item(0)->getAttribute("unit");
      if ($unit == "deg") {
        $ra = $target->getElementsByTagName( "position" )->item(0)->getElementsByTagName( "ra" )->item(0)->nodeValue;
      } else if ($unit == "rad") {
        $ra = Rad2Deg($target->getElementsByTagName( "position" )->item(0)->getElementsByTagName( "ra" )->item(0)->nodeValue);
      } else if ($unit == "arcmin") {
        $ra = $target->getElementsByTagName( "position" )->item(0)->getElementsByTagName( "ra" )->item(0)->nodeValue / 60.0;
      } else if ($unit == "arcsec") {
        $ra = $target->getElementsByTagName( "position" )->item(0)->getElementsByTagName( "ra" )->item(0)->nodeValue / 3600.0;
      }
      $targetInfoArray["ra"] = $ra / 15.0;
      
      // Get Dec and convert it to degrees
      $unit = $target->getElementsByTagName( "position" )->item(0)->getElementsByTagName( "dec" )->item(0)->getAttribute("unit");
      if ($unit == "deg") {
        $dec = $target->getElementsByTagName( "position" )->item(0)->getElementsByTagName( "dec" )->item(0)->nodeValue;
      } else if ($unit == "rad") {
        $dec = Rad2Deg($target->getElementsByTagName( "position" )->item(0)->getElementsByTagName( "dec" )->item(0)->nodeValue);
      } else if ($unit == "arcmin") {
        $dec = $target->getElementsByTagName( "position" )->item(0)->getElementsByTagName( "dec" )->item(0)->nodeValue / 60.0;
      } else if ($unit == "arcsec") {
        $dec = $target->getElementsByTagName( "position" )->item(0)->getElementsByTagName( "dec" )->item(0)->nodeValue / 3600.0;
      }
      $targetInfoArray["dec"] = $dec;
      
      // Check if the magnitude is defined. If this is the case, get it. Otherwise, set to 99.9
      if ($target->getElementsByTagName( "visMag" )->item(0)) {
        $targetInfoArray["mag"] = $target->getElementsByTagName( "visMag" )->item(0)->nodeValue;
      } else {
        $targetInfoArray["mag"] = "99.9";
      }
      
      // Check if the surface brightness is defined. If this is the case, get it. Otherwise, set to 99.9
      if ($target->getElementsByTagName( "surfBr" )->item(0)) {
        $targetInfoArray["subr"] = $target->getElementsByTagName( "surfBr" )->item(0)->nodeValue;
      } else {
        $targetInfoArray["subr"] = "99.9";
      }
      
      // Check if the position angle is defined. If this is the case, get it. Otherwise, set to 999
      if ($target->getElementsByTagName( "pa" )->item(0)) {
        $targetInfoArray["pa"] = $target->getElementsByTagName( "pa" )->item(0)->nodeValue;
      } else {
        $targetInfoArray["pa"] = "999";
      }

      // Check if the largeDiameter is defined. If this is the case, get it. Otherwise, set to 0
      if ($target->getElementsByTagName( "largeDiameter" )->item(0)) {
        // Get Dec and convert it to arcseconds
        $unit = $target->getElementsByTagName( "largeDiameter" )->item(0)->getAttribute("unit");
        
        if ($unit == "deg") {
          $diam1 = $target->getElementsByTagName( "largeDiameter" )->item(0)->nodeValue * 3600.0;
        } else if ($unit == "rad") {
          $diam1 = Rad2Deg($target->getElementsByTagName( "largeDiameter" )->item(0)->nodeValue) * 3600.0;
        } else if ($unit == "arcmin") {
          $diam1 = $target->getElementsByTagName( "largeDiameter" )->item(0)->nodeValue * 60.0;
        } else if ($unit == "arcsec") {
          $diam1 = $target->getElementsByTagName( "largeDiameter" )->item(0)->nodeValue;
        }
        $targetInfoArray["diam1"] = $diam1;
      } else {
        $targetInfoArray["diam1"] = "0";
      }
      
      // Check if the smallDiameter is defined. If this is the case, get it. Otherwise, set to 0
      if ($target->getElementsByTagName( "smallDiameter" )->item(0)) {
        // Get Dec and convert it to arcseconds
        $unit = $target->getElementsByTagName( "smallDiameter" )->item(0)->getAttribute("unit");
        
        if ($unit == "deg") {
          $diam2 = $target->getElementsByTagName( "smallDiameter" )->item(0)->nodeValue * 3600.0;
        } else if ($unit == "rad") {
          $diam2 = Rad2Deg($target->getElementsByTagName( "smallDiameter" )->item(0)->nodeValue) * 3600.0;
        } else if ($unit == "arcmin") {
          $diam2 = $target->getElementsByTagName( "smallDiameter" )->item(0)->nodeValue * 60.0;
        } else if ($unit == "arcsec") {
          $diam2 = $target->getElementsByTagName( "smallDiameter" )->item(0)->nodeValue;
        }
        $targetInfoArray["diam2"] = $diam2;
      } else {
        $targetInfoArray["diam2"] = "0";
      }
      
      $targetInfoArray["aliases"] = $aliasesArray;
      $targetArray[$targetid] = $targetInfoArray;  
    }

    // SITES
    $sites = $dom->getElementsByTagName( "sites" ); 
    $site = $sites->item(0)->getElementsByTagName( "site" ); 
    
    $siteArray = Array();
    
    foreach( $site as $site )
    {
      $siteInfoArray = Array();
      $siteid = $site->getAttribute("id");

      $siteInfoArray["name"] = $site->getElementsByTagName( "name" )->item(0)->nodeValue;

      // Get longitude and convert it to degrees
      $unit = $site->getElementsByTagName( "longitude" )->item(0)->getAttribute("unit");
      if ($unit == "deg") {
        $longitude = $site->getElementsByTagName( "longitude" )->item(0)->nodeValue;
      } else if ($unit == "rad") {
        $longitude = Rad2Deg($site->getElementsByTagName( "longitude" )->item(0)->nodeValue);
      } else if ($unit == "arcmin") {
        $longitude = $site->getElementsByTagName( "longitude" )->item(0)->nodeValue / 60.0;
      } else if ($unit == "arcsec") {
        $longitude = $site->getElementsByTagName( "longitude" )->item(0)->nodeValue / 3600.0;
      }
      $siteInfoArray["longitude"] = $longitude;
      
      // Get latitude and convert it to degrees
      $unit = $site->getElementsByTagName( "latitude" )->item(0)->getAttribute("unit");
      if ($unit == "deg") {
        $latitude = $site->getElementsByTagName( "latitude" )->item(0)->nodeValue;
      } else if ($unit == "rad") {
        $latitude = Rad2Deg($site->getElementsByTagName( "latitude" )->item(0)->nodeValue);
      } else if ($unit == "arcmin") {
        $latitude = $site->getElementsByTagName( "latitude" )->item(0)->nodeValue / 60.0;
      } else if ($unit == "arcsec") {
        $latitude = $site->getElementsByTagName( "latitude" )->item(0)->nodeValue / 3600.0;
      }
      $siteInfoArray["latitude"] = $latitude;

      // Get the timezone
      $timezone = $site->getElementsByTagName( "timezone" )->item(0)->nodeValue;
      
      if ($timezone == 0) {
        $siteInfoArray["timezone"] = "UTC";
      } else if ($timezone > 0) {
        $siteInfoArray["timezone"] = "Etc/GMT+" . ($timezone / 60);
      } else if ($timezone < 0) {
        $siteInfoArray["timezone"] = "Etc/GMT" . ($timezone / 60);
      }
      
      $siteArray[$siteid] = $siteInfoArray;  
    }
    
    // SCOPES
    $scopes = $dom->getElementsByTagName( "scopes" ); 
    $scope = $scopes->item(0)->getElementsByTagName( "scope" ); 
    
    $scopeArray = Array();
    
    foreach( $scope as $scope )
    {
      $scopeInfoArray = Array();
      $scopeid = $scope->getAttribute("id");

      $scopeInfoArray["name"] = $scope->getElementsByTagName( "model" )->item(0)->nodeValue;
      $scopeInfoArray["diameter"] = $scope->getElementsByTagName( "aperture" )->item(0)->nodeValue;
      
      $tp =  $scope->getAttribute("xsi:type");
      
      $type = $scope->getElementsByTagName( "type" )->item(0)->nodeValue;
      if ($type == "A" || $type == "Naked Eye") {
        $typeToSave = InstrumentNakedEye;
      } else if ($type == "B" || $type == "Binoculars") {
        $typeToSave = InstrumentBinoculars;
      } else if ($type == "R" || $type == "Refractor") {
        $typeToSave = InstrumentRefractor;
      } else if ($type == "N" || $type == "Newton") {
        $typeToSave = InstrumentReflector;
      } else if ($type == "C" || $type == "Cassegrain") {
        $typeToSave = InstrumentCassegrain;
      } else if ($type == "K" || $type == "Kutter") {
        $typeToSave = InstrumentKutter;
      } else if ($type == "M" || $type == "Maksutov") {
        $typeToSave = InstrumentMaksutov;
      } else if ($type == "S" || $type == "Schmidt-Cassegrain") {
        $typeToSave = InstrumentSchmidtCassegrain;
      } else {
        $typeToSave = InstrumentOther;
      }
      $scopeInfoArray["type"] = $typeToSave;

      // Check if the focal length exists. If so, we are using a telescope, else a binocular
      if ($scope->getElementsByTagName( "focalLength" )->item(0)) {
        $fl = $scope->getElementsByTagName( "focalLength" )->item(0)->nodeValue;
        $scopeInfoArray["fd"] = $fl / $scopeInfoArray["diameter"];
        $scopeInfoArray["fixedMagnification"] = 0;
      } else {
        $scopeInfoArray["fd"] = 0;
        $scopeInfoArray["fixedMagnification"] = $scope->getElementsByTagName( "magnification" )->item(0)->nodeValue;
      }  
      
      $scopeArray[$scopeid] = $scopeInfoArray;  
    }

    // EYEPIECES
    $eyepieces = $dom->getElementsByTagName( "eyepieces" ); 
    $eyepiece = $eyepieces->item(0)->getElementsByTagName( "eyepiece" ); 
    
    $eyepieceArray = Array();
    
    foreach( $eyepiece as $eyepiece )
    {
      $eyepieceInfoArray = Array();
      $eyepieceid = $eyepiece->getAttribute("id");

      
      $eyepieceInfoArray["name"] = $eyepiece->getElementsByTagName( "model" )->item(0)->nodeValue;
      $eyepieceInfoArray["focalLength"] = $eyepiece->getElementsByTagName( "focalLength" )->item(0)->nodeValue;
      
      // Check if the maximal focal length exists. If so, we are using a zoom eyepiece
      if ($eyepiece->getElementsByTagName( "maxFocalLength" )->item(0)) {
        $eyepieceInfoArray["maxFocalLength"] = $scope->getElementsByTagName( "maxFocalLength" )->item(0)->nodeValue;
      } else {
        $eyepieceInfoArray["maxFocalLength"] = -1;
      }  
      
      // Get focal length and convert it to degrees
      $unit = $eyepiece->getElementsByTagName( "apparentFOV" )->item(0)->getAttribute("unit");
      if ($unit == "deg") {
        $fov = $eyepiece->getElementsByTagName( "apparentFOV" )->item(0)->nodeValue;
      } else if ($unit == "rad") {
        $fov = Rad2Deg($eyepiece->getElementsByTagName( "apparentFOV" )->item(0)->nodeValue);
      } else if ($unit == "arcmin") {
        $fov = $eyepiece->getElementsByTagName( "apparentFOV" )->item(0)->nodeValue / 60.0;
      } else if ($unit == "arcsec") {
        $fov = $eyepiece->getElementsByTagName( "apparentFOV" )->item(0)->nodeValue / 3600.0;
      }
      $eyepieceInfoArray["apparentFOV"] = $fov;
      
      $eyepieceArray[$eyepieceid] = $eyepieceInfoArray;  
    }
    
    // Check if there are observations for the given observer
    $searchNode = $dom->getElementsByTagName( "observations" ); 
    $observation = $searchNode->item(0)->getElementsByTagName( "observation" ); 
    foreach( $observation as $observation )
    {
      $observerid = $observation->getElementsByTagName( "observer" )->item(0)->nodeValue;
      if ($observerid == $id) {
        // Check if the observation already exists in DeepskyLog (target and begin should tell this)
        print "Date and time : " . $observation->getElementsByTagName( "begin" )->item(0)->nodeValue . ", ";
        print "Target : " . $targetArray[$observation->getElementsByTagName( "target" )->item(0)->nodeValue]["name"] . ", ";
        print "Site : " . $siteArray[$observation->getElementsByTagName( "site" )->item(0)->nodeValue]["name"] . ", ";
        print "Scope : " . $scopeArray[$observation->getElementsByTagName( "scope" )->item(0)->nodeValue]["name"] . ", ";
        // Eyepiece is not mandatory
        print "Eyepiece : " . $eyepieceArray[$observation->getElementsByTagName( "eyepiece" )->item(0)->nodeValue]["name"] . "<br />";
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
