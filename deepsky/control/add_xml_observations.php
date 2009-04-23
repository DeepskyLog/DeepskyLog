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
    
    // LENSES
    $lenses = $dom->getElementsByTagName( "lenses" ); 
    $lens = $lenses->item(0)->getElementsByTagName( "lens" ); 
    
    $lensArray = Array();
    
    foreach( $lens as $lens )
    {
      $lensInfoArray = Array();
      $lensid = $lens->getAttribute("id");

      
      $lensInfoArray["name"] = $lens->getElementsByTagName( "model" )->item(0)->nodeValue;
      $lensInfoArray["factor"] = $lens->getElementsByTagName( "factor" )->item(0)->nodeValue;
      
      $lensArray[$lensid] = $lensInfoArray;  
    }
    
    // FILTERS
    $filters = $dom->getElementsByTagName( "filters" ); 
    $filter = $filters->item(0)->getElementsByTagName( "filter" ); 
    
    $filterArray = Array();
    
    foreach( $filter as $filter )
    {
      $filterInfoArray = Array();
      $filterid = $filter->getAttribute("id");

      $filterInfoArray["name"] = $filter->getElementsByTagName( "model" )->item(0)->nodeValue;
      $type = $filter->getElementsByTagName( "type" )->item(0)->nodeValue;
      
      if ($type == "other") {
        $typeInfo = 0;
      } else if ($type == "broad band") {
        $typeInfo = 1;
      } else if ($type == "narrow band") {
        $typeInfo = 2;
      } else if ($type == "O-III") {
        $typeInfo = 3;
      } else if ($type == "H-beta") {
        $typeInfo = 4;
      } else if ($type == "H-alpha") {
        $typeInfo = 5;
      } else if ($type == "color") {
        $typeInfo = 6;
      } else if ($type == "neutral") {
        $typeInfo = 7;
      } else if ($type == "corrective") {
        $typeInfo = 8;
      }

      $filterInfoArray["type"] = $typeInfo;
      
      if ($filter->getElementsByTagName( "wratten" )->item(0)) {
        $filterInfoArray["wratten"] = $filter->getElementsByTagName( "wratten" )->item(0)->nodeValue;
      } else {
        $filterInfoArray["wratten"] = "";
      }
      
      if ($filter->getElementsByTagName( "schott" )->item(0)) {
        $filterInfoArray["schott"] = $filter->getElementsByTagName( "schott" )->item(0)->nodeValue;
      } else {
        $filterInfoArray["schott"] = "";
      }

      if ($filter->getElementsByTagName( "color" )->item(0)) {
        $color = $filter->getElementsByTagName( "color" )->item(0)->nodeValue;
        
        if ($color == "light red") {
          $filterInfoArray["color"] = 1;
        } else if ($color == "red") {
          $filterInfoArray["color"] = 2;
        } else if ($color == "deep red") {
          $filterInfoArray["color"] = 3;
        } else if ($color == "orange") {
          $filterInfoArray["color"] = 4;
        } else if ($color == "light yellow") {
          $filterInfoArray["color"] = 5;
        } else if ($color == "deep yellow") {
          $filterInfoArray["color"] = 6;
        } else if ($color == "yellow") {
          $filterInfoArray["color"] = 7;
        } else if ($color == "yellow-green") {
          $filterInfoArray["color"] = 8;
        } else if ($color == "light green") {
          $filterInfoArray["color"] = 9;
        } else if ($color == "green") {
          $filterInfoArray["color"] = 10;
        } else if ($color == "medium blue") {
          $filterInfoArray["color"] = 11;
        } else if ($color == "pale blue") {
          $filterInfoArray["color"] = 12;
        } else if ($color == "blue") {
          $filterInfoArray["color"] = 13;
        } else if ($color == "deep blue") {
          $filterInfoArray["color"] = 14;
        } else if ($color == "voilet") {
          $filterInfoArray["color"] = 15;
        } else {
          $filterInfoArray["color"] = 0;
        }
      } else {
        $filterInfoArray["color"] = 0;
      }
      
      $filterArray[$filterid] = $filterInfoArray;  
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
        if ($observation->getElementsByTagName( "eyepiece" )->item(0)) {
          print "Eyepiece : " . $eyepieceArray[$observation->getElementsByTagName( "eyepiece" )->item(0)->nodeValue]["name"] . ", ";
        }
        // Lens is not mandatory
        if ($observation->getElementsByTagName( "lens" )->item(0)) {
          print "Lens : " . $lensArray[$observation->getElementsByTagName( "lens" )->item(0)->nodeValue]["name"] . ", ";
        }
        // Filter is not mandatory
        if ($observation->getElementsByTagName( "filter" )->item(0)) {
          print "Filter : " . $filterArray[$observation->getElementsByTagName( "filter" )->item(0)->nodeValue]["name"] . ", ";
        }
        // Limiting magnitude is not mandatory
        if ($observation->getElementsByTagName( "faintestStar" )->item(0)) {
          print "Limiting magnitude : " . $observation->getElementsByTagName( "faintestStar" )->item(0)->nodeValue . ", ";
        }
        // Seeing is not mandatory
        if ($observation->getElementsByTagName( "seeing" )->item(0)) {
          print "Seeing : " . $observation->getElementsByTagName( "seeing" )->item(0)->nodeValue . ", ";
        }
        // Magnification is not mandatory
        if ($observation->getElementsByTagName( "magnification" )->item(0)) {
          print "Magnification : " . $observation->getElementsByTagName( "magnification" )->item(0)->nodeValue . ", ";
        }
        // Sqm is not mandatory
        if ($observation->getElementsByTagName( "sqm" )->item(0)) {
          print "SQM : " . $observation->getElementsByTagName( "sqm" )->item(0)->nodeValue . ", ";
        }
        
        // The result of the observation!
        $resultNode = $observation->getElementsByTagName( "result" )->item(0);
        // Language is not mandatory
        if ($resultNode->hasAttribute("lang")) {
          print "Language : " . $resultNode->getAttribute("lang") . ", ";
        }
        // colorContrasts is not mandatory
        if ($resultNode->hasAttribute("colorContrasts")) {
          if ($resultNode->getAttribute("colorContrasts") == "true") {
            $colorContrast = 1;
          } else {
            $colorContrast = 0;
          }
        } else {
          $colorContrast = -1;
        }
        print "Color Contrasts : " . $colorContrast . ", ";

        // extended is not mandatory
        if ($resultNode->hasAttribute("extended")) {
          if ($resultNode->getAttribute("extended") == "true") {
            $extended = 1;
          } else {
            $extended = 0;
          }
        } else {
          $extended = -1;
        }
        print "Extended : " . $extended . ", ";
        
        // mottled is not mandatory
        if ($resultNode->hasAttribute("mottled")) {
          if ($resultNode->getAttribute("mottled") == "true") {
            $mottled = 1;
          } else {
            $mottled = 0;
          }
        } else {
          $mottled = -1;
        }
        print "Mottled : " . $mottled . ", ";
        
        // resolved is not mandatory
        if ($resultNode->hasAttribute("resolved")) {
          if ($resultNode->getAttribute("resolved") == "true") {
            $resolved = 1;
          } else {
            $resolved = 0;
          }
        } else {
          $resolved = -1;
        }
        print "Resolved : " . $resolved . ", ";
        
        // stellar is not mandatory
        if ($resultNode->hasAttribute("stellar")) {
          if ($resultNode->getAttribute("stellar") == "true") {
            $stellar = 1;
          } else {
            $stellar = 0;
          }
        } else {
          $stellar = -1;
        }
        print "Stellar : " . $stellar . ", ";

        // unusualShape is not mandatory
        if ($resultNode->hasAttribute("unusualShape")) {
          if ($resultNode->getAttribute("unusualShape") == "true") {
            $unusualShape = 1;
          } else {
            $unusualShape = 0;
          }
        } else {
          $unusualShape = -1;
        }
        print "Unusual Shape : " . $unusualShape . ", ";

        // partlyUnresolved is not mandatory
        if ($resultNode->hasAttribute("partlyUnresolved")) {
          if ($resultNode->getAttribute("partlyUnresolved") == "true") {
            $partlyUnresolved = 1;
          } else {
            $partlyUnresolved = 0;
          }
        } else {
          $partlyUnresolved = -1;
        }
        print "Partly Unresolved : " . $partlyUnresolved . ", ";

        // Character is not mandatory
        if ($resultNode->getElementsByTagName( "character" )->item(0)) {
          print  "Character : " . $resultNode->getElementsByTagName( "character" )->item(0)->nodeValue . ", ";
        }
        // Rating is not mandatory
        if ($resultNode->getElementsByTagName( "rating" )->item(0)) {
          print  "Rating : " . $resultNode->getElementsByTagName( "rating" )->item(0)->nodeValue . ", ";
        }
        // smallDiameter is not mandatory
        if ($resultNode->getElementsByTagName( "smallDiameter" )->item(0)) {
          $unit = $resultNode->getElementsByTagName( "smallDiameter" )->item(0)->getAttribute("unit");
          if ($unit == "deg") {
            $smallDiameter = $resultNode->getElementsByTagName( "smallDiameter" )->item(0)->nodeValue * 3600.0;
          } else if ($unit == "rad") {
            $smallDiameter = Rad2Deg($resultNode->getElementsByTagName( "smallDiameter" )->item(0)->nodeValue) * 3600.0;
          } else if ($unit == "arcmin") {
            $smallDiameter = $resultNode->getElementsByTagName( "smallDiameter" )->item(0)->nodeValue * 60.0;
          } else if ($unit == "arcsec") {
            $smallDiameter = $resultNode->getElementsByTagName( "smallDiameter" )->item(0)->nodeValue;
          }
          print  "Small Diameter : " . $smallDiameter . ", ";
        }
        // largeDiameter is not mandatory
        if ($resultNode->getElementsByTagName( "largeDiameter" )->item(0)) {
          $unit = $resultNode->getElementsByTagName( "largeDiameter" )->item(0)->getAttribute("unit");
          if ($unit == "deg") {
            $largeDiameter = $resultNode->getElementsByTagName( "largeDiameter" )->item(0)->nodeValue * 3600.0;
          } else if ($unit == "rad") {
            $largeDiameter = Rad2Deg($resultNode->getElementsByTagName( "largeDiameter" )->item(0)->nodeValue) * 3600.0;
          } else if ($unit == "arcmin") {
            $largeDiameter = $resultNode->getElementsByTagName( "largeDiameter" )->item(0)->nodeValue * 60.0;
          } else if ($unit == "arcsec") {
            $largeDiameter = $resultNode->getElementsByTagName( "largeDiameter" )->item(0)->nodeValue;
          }
          print  "Large Diameter : " . $largeDiameter . ", ";
        }
        // Description is not mandatory
        if ($resultNode->getElementsByTagName( "description" )->item(0)) {
          print  "Description : " . $resultNode->getElementsByTagName( "description" )->item(0)->nodeValue;
        } 
        print "<br />";
      }
    }
    
  } else {
    throw new Exception(LangXMLError3);
  }
  
exit;
?>
