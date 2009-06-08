<?php
// add_xml_observations.php
// adds observations from an OpenAstronomyLog xml file to the database
global $objDatabase;

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

if ($version != "2.0") {
  throw new Exception(LangXMLError1);
}

// Use the correct schema definition to check the xml file. Does not work with 2.0 yet (as
// there is no final scheme for 2.0 yet)
$xmlschema = str_replace(' ', '/', $searchNode->item(0)->getAttribute("xsi:schemaLocation"));

// TODO : Use the schema definition from the site!
$xmlschema = "http://localhost/deepskylog/xml/oal20.xsd";

//Validate the XML file against the schema
if ($dom->schemaValidate($xmlschema)) {
  // The XML file is valid. Let's start reading in the file.
  // Only 2.0 files!

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

    $observerid = $observer->getElementsByTagName ( "account" );
    $obsid = "";
    foreach ($observerid as $observerid) {
      if ($observerid->getAttribute("name") == "www.deepskylog.org") {
        $obsid = $observerid->nodeValue;
      }
    }

    // Get the name of the observer which is logged in in DeepskyLog
    $deepskylog_username=$objObserver->getObserverProperty($_SESSION['deepskylog_id'],'firstname'). " ".$objObserver->getObserverProperty($_SESSION['deepskylog_id'],'name');

    if ($obsid != "") {
      if ($obsid == $_SESSION['deepskylog_id']) {
        $id = $comastid;
      }
    } else if ($deepskylog_username == $name . " " . $surname) {
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

    $next = 1;

    if ($type == "oal:deepSkyAS" || $type == "oal:deepSkyDS") {
      $targetInfoArray["type"] = "ASTER";
    } else if ($type == "oal:deepSkySC" || $type == "oal:deepSkyOC") {
      $targetInfoArray["type"] = "OPNCL";
    } else if ($type == "oal:deepSkyGC") {
      $targetInfoArray["type"] = "GLOCL";
    } else if ($type == "oal:deepSkyGX") {
      $targetInfoArray["type"] = "GALXY";
    } else if ($type == "oal:deepSkyCG") {
      $targetInfoArray["type"] = "GALCL";
    } else if ($type == "oal:deepSkyGN") {
      $targetInfoArray["type"] = "BRTNB";
    } else if ($type == "oal:deepSkyGN") {
      $targetInfoArray["type"] = "BRTNB";
    } else if ($type == "oal:deepSkyPN") {
      $targetInfoArray["type"] = "PLNNB";
    } else if ($type == "oal:deepSkyQS") {
      $targetInfoArray["type"] = "QUASR";
    } else if ($type == "oal:deepSkyDN") {
      $targetInfoArray["type"] = "DRKNB";
    } else if ($type == "oal:deepSkyNA") {
      $targetInfoArray["type"] = "NONEX";
    } else {
      $next = 0;
    }

    $targetInfoArray["known"] = $next;

    if ($next == 1) {
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
        // Get surface brightness and convert it
        $unit = $target->getElementsByTagName( "surfBr" )->item(0)->getAttribute("unit");

        if ($unit == "mags-per-squarearcmin") {
          $subr = $target->getElementsByTagName( "surfBr" )->item(0)->nodeValue;
        } else {
          $subr = $target->getElementsByTagName( "surfBr" )->item(0)->nodeValue - 8.89;
        }

        $targetInfoArray["subr"] = $subr;
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
      $eyepieceInfoArray["maxFocalLength"] = $eyepiece->getElementsByTagName( "maxFocalLength" )->item(0)->nodeValue;
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
      // Check if the site already exists in DeepskyLog
      $site = $siteArray[$observation->getElementsByTagName( "site" )->item(0)->nodeValue]["name"];

      $sa = $siteArray[$observation->getElementsByTagName( "site" )->item(0)->nodeValue];
      if (count($objDatabase->selectRecordArray("SELECT * from locations where observer = \"" . $_SESSION['deepskylog_id'] . "\" and name = \"" . $site . "\";")) > 0) {
        // Update the coordinates
        $locId = $objLocation->getLocationId($sa["name"], $_SESSION['deepskylog_id']);
        $objLocation->setLocationProperty($locId, "longitude", $sa["longitude"]);
        $objLocation->setLocationProperty($locId, "latitude", $sa["latitude"]);
        $objLocation->setLocationProperty($locId, "timezone", $sa["timezone"]);
      } else {
        // Add the new site!
        $locId = $objLocation->addLocation($sa["name"], $sa["longitude"], $sa["latitude"], "", "", $sa["timezone"]);
        $objDatabase->execSQL("update locations set observer = \"" . $_SESSION['deepskylog_id'] . "\" where id = \"" . $locId . "\";");
      }

      // Check if the instrument already exists in DeepskyLog
      $instrument = $scopeArray[$observation->getElementsByTagName( "scope" )->item(0)->nodeValue]["name"];

      $ia = $scopeArray[$observation->getElementsByTagName( "scope" )->item(0)->nodeValue];
      if (count($objDatabase->selectRecordArray("SELECT * from instruments where observer = \"" . $_SESSION['deepskylog_id'] . "\" and name = \"" . utf8_encode(htmlentities($instrument)) . "\";")) > 0) {
        // Update
        $instId = $objInstrument->getInstrumentId($ia["name"], $_SESSION['deepskylog_id']);
        $objInstrument->setInstrumentProperty($instId, "name", utf8_encode(htmlentities($ia["name"])));
        $objInstrument->setInstrumentProperty($instId, "diameter", $ia["diameter"]);
        $objInstrument->setInstrumentProperty($instId, "fd", $ia["fd"]);
        $objInstrument->setInstrumentProperty($instId, "type", $ia["type"]);
        $objInstrument->setInstrumentProperty($instId, "fixedMagnification", $ia["fixedMagnification"]);
      } else {
        // Add the new instrument!
        $instId = $objInstrument->addInstrument($ia["name"], $ia["diameter"], $ia["fd"], $ia["type"], $ia["fixedMagnification"], $_SESSION['deepskylog_id']);
      }

      // Filter is not mandatory
      if ($observation->getElementsByTagName( "filter" )->item(0)) {
        // Check if the filter already exists in DeepskyLog
        $filter = $filterArray[$observation->getElementsByTagName( "filter" )->item(0)->nodeValue]["name"];

        $fa = $filterArray[$observation->getElementsByTagName( "filter" )->item(0)->nodeValue];
        if (count($objDatabase->selectRecordArray("SELECT * from filters where observer = \"" . $_SESSION['deepskylog_id'] . "\" and name = \"" . utf8_encode(htmlentities($filter)) . "\";")) > 0) {
          // Update the filter
          $filtId = $objFilter->getFilterId($fa["name"], $_SESSION['deepskylog_id']);
          $objFilter->setFilterProperty($filtId, "name", utf8_encode(htmlentities($fa["name"])));
          $objFilter->setFilterProperty($filtId, "type", $fa["type"]);
          $objFilter->setFilterProperty($filtId, "color", $fa["color"]);
          $objFilter->setFilterProperty($filtId, "wratten", $fa["wratten"]);
          $objFilter->setFilterProperty($filtId, "schott", $fa["schott"]);
        } else {
          // Add the new filter!
          $filtId = $objFilter->addFilter($fa["name"], $fa["type"], $fa["color"], $fa["wratten"], $fa["schott"]);
          $objDatabase->execSQL("update filters set observer = \"" . $_SESSION['deepskylog_id'] . "\" where id = \"" . $filtId . "\";");
        }
      }

      // Eyepiece is not mandatory
      if ($observation->getElementsByTagName( "eyepiece" )->item(0)) {
        // Check if the eyepiece already exists in DeepskyLog
        $eyepiece = $eyepieceArray[$observation->getElementsByTagName( "eyepiece" )->item(0)->nodeValue]["name"];

        $ea = $eyepieceArray[$observation->getElementsByTagName( "eyepiece" )->item(0)->nodeValue];
        if (count($objDatabase->selectRecordArray("SELECT * from eyepieces where observer = \"" . $_SESSION['deepskylog_id'] . "\" and name = \"" . utf8_encode(htmlentities($eyepiece)) . "\";")) > 0) {
          // Update the eyepiece
          $eyepId = $objEyepiece->getEyepieceId($ea["name"], $_SESSION['deepskylog_id']);
          $objEyepiece->setEyepieceProperty($eyepId, "name", utf8_encode(htmlentities($ea["name"])));
          $objEyepiece->setEyepieceProperty($eyepId, "focalLength", $ea["focalLength"]);
          $objEyepiece->setEyepieceProperty($eyepId, "apparentFOV", $ea["apparentFOV"]);
          $objEyepiece->setEyepieceProperty($eyepId, "maxFocalLength", $ea["maxFocalLength"]);
        } else {
          // Add the new eyepiece!
          $eyepId = $objEyepiece->addEyepiece($ea["name"], $ea["focalLength"], $ea["apparentFOV"]);
          $objDatabase->execSQL("update eyepieces set observer = \"" . $_SESSION['deepskylog_id'] . "\" where id = \"" . $eyepId . "\";");
          $objEyepiece->setEyepieceProperty($eyepId, "maxFocalLength", $ea["maxFocalLength"]);
        }
      }

      // Lens is not mandatory
      if ($observation->getElementsByTagName( "lens" )->item(0)) {
        // Check if the eyepiece already exists in DeepskyLog
        $lens = $lensArray[$observation->getElementsByTagName( "lens" )->item(0)->nodeValue]["name"];

        $la = $lensArray[$observation->getElementsByTagName( "lens" )->item(0)->nodeValue];
        if (count($objDatabase->selectRecordArray("SELECT * from lenses where observer = \"" . $_SESSION['deepskylog_id'] . "\" and name = \"" . utf8_encode(htmlentities($lens)) . "\";")) > 0) {
          // Update the lens
          $lensId = $objLens->getLensId($la["name"], $_SESSION['deepskylog_id']);
          $objLens->setLensProperty($lensId, "name", utf8_encode(htmlentities($la["name"])));
          $objLens->setLensProperty($lensId, "factor", $la["factor"]);
        } else {
          // Add the new lens!
          $lensId = $objLens->addLens($la["name"], $la["factor"]);
          $objDatabase->execSQL("update lenses set observer = \"" . $_SESSION['deepskylog_id'] . "\" where id = \"" . $lensId . "\";");
        }
      }

      // Object!!!
      $target = $targetArray[$observation->getElementsByTagName( "target" )->item(0)->nodeValue]["name"];
      $ta = $targetArray[$observation->getElementsByTagName( "target" )->item(0)->nodeValue];

      if ($ta["known"] == 1) {
        $pattern = '/([A-Za-z]+)([\d\D\w]+)/';
        $targetName = preg_replace($pattern, '${1} ${2}', $target);
        $targetName = str_replace("  ", " ", $targetName);

        $objeId = -1;
        // Check if the object with the given name exists. If this is the case, set the objeId, else check the alternative names
        if (count($objDatabase->selectRecordArray("SELECT objectnames.objectname FROM objectnames WHERE (objectnames.altname = \"" . $targetName . "\");")) > 0) {
          $objeId = $objObject->getDsObjectName($targetName);
        } else {
          for ($i = 0; $i < sizeof($ta["aliases"]);$i++) {
            $targetName = preg_replace($pattern, '${1} ${2}', $ta["aliases"]["alias" . $i]);
            $targetName = str_replace("  ", " ", $targetName);
            if (count($objDatabase->selectRecordArray("SELECT objectnames.objectname FROM objectnames WHERE (objectnames.altname = \"" . $targetName . "\")")) > 0) {
              $objeId = $objObject->getDsObjectName($targetName);
            }
          }
          if ($objeId == -1) {
            $targetName = preg_replace($pattern, '${1} ${2}', $target);
            $names = explode(" ", $targetName);
            $targetName = str_replace("  ", " ", $targetName);

            $objObject->addDSObject($names[0]." ".$names[2], $names[0], $names[2], $ta["type"], $ta["constellation"], $ta["ra"], $ta["dec"], $ta["mag"], $ta["subr"], $ta["diam1"], $ta["diam2"], $ta["pa"], "", $ta["datasource"]);
            for ($i = 0; $i < sizeof($ta["aliases"]);$i++) {
              $aliasName = preg_replace($pattern, '${1} ${2}', $ta["aliases"]["alias" . $i]);
              $aliasNames = explode(" ", $aliasName);
              $objObject->newAltName($names[0]." ".$names[2], $aliasNames[0], $aliasNames[2]);
            }
            $objeId = $objObject->getDsObjectName($targetName);
          }
        }

        // Check if the observation already exists!
        $dateArray = sscanf($observation->getElementsByTagName( "begin" )->item(0)->nodeValue, "%4d-%2d-%2dT%2d:%2d:%2d%c%02d:%02d");
        $date = mktime($dateArray[3], $dateArray[4], 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        if ($dateArray[6] == "-") {
          $timeDiff = -($dateArray[7] * 60 + $dateArray[8]) * 60.0;
        } else {
          $timeDiff = ($dateArray[7] * 60 + $dateArray[8]) * 60.0;
        }
        // Get the time and date in UT.
        $date = $date - $timeDiff;

        $dateStr = date("Ymd", $date);
        $timeStr = date("Hi", $date);

        // Check if the observation does already exist
        $obsId = $objDatabase->selectRecordArray("SELECT id from observations WHERE objectname = \"" . $objeId . "\" and date = \"" . $dateStr . "\" and instrumentid = \"" . $instId . "\" and locationId = \"" . $locId . "\" and observerid = \"" . $_SESSION['deepskylog_id'] . "\";");

        if (count($obsId) > 0) {
          // TODO : Adapt observation
        } else {
          // New observation
          $resultNode = $observation->getElementsByTagName("result")->item(0);
          if ($resultNode->getElementsByTagName( "description" )->item(0)) {
            $description = $resultNode->getElementsByTagName( "description" )->item(0)->nodeValue;
          } else {
            $description = "";
          }

          // Seeing is not mandatory
          if ($observation->getElementsByTagName( "seeing" )->item(0)) {
            $seeing = $observation->getElementsByTagName( "seeing" )->item(0)->nodeValue;
          } else {
            $seeing ="-1";
          }

          // Limiting magnitude is not mandatory
          if ($observation->getElementsByTagName( "faintestStar" )->item(0)) {
            $limmag = $observation->getElementsByTagName( "faintestStar" )->item(0)->nodeValue;
          } else {
            $limmag = "";
          }

          if ($resultNode->hasAttribute("lang")) {
            $language = $resultNode->getAttribute("lang");
          } else {
            $language = "en";
          }

          // Rating is not mandatory
          if ($resultNode->getElementsByTagName( "rating" )->item(0)) {
            $visibility = $resultNode->getElementsByTagName( "rating" )->item(0)->nodeValue;
          }

          $obsId = $objObservation->addDSObservation($objeId, $_SESSION['deepskylog_id'], $instId, $locId, $dateStr, $timeStr, $description, $seeing, $limmag, $visibility, $language);

          // Magnification is not mandatory
          if ($observation->getElementsByTagName( "magnification" )->item(0)) {
            $objObservation->setDsObservationProperty($obsId, "magnification", $observation->getElementsByTagName( "magnification" )->item(0)->nodeValue);
          }
          // Sqm is not mandatory
          if ($observation->getElementsByTagName( "sky-quality" )->item(0)) {
            // Get sqm value and convert it
            $unit = $observation->getElementsByTagName( "sky-quality" )->item(0)->getAttribute("unit");

            if ($unit == "mags-per-squarearcmin") {
              $sqm = $observation->getElementsByTagName( "sky-quality" )->item(0)->nodeValue + 8.89;
            } else {
              $sqm = $observation->getElementsByTagName( "sky-quality" )->item(0)->nodeValue;
            }

            $objObservation->setDsObservationProperty($obsId, "SQM", $sqm);
          }

          // The result of the observation!
          $resultNode = $observation->getElementsByTagName( "result" )->item(0);
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
          $objObservation->setDsObservationProperty($obsId, "colorContrasts", $colorContrast);

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
          $objObservation->setDsObservationProperty($obsId, "extended", $extended);

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
          $objObservation->setDsObservationProperty($obsId, "mottled", $mottled);

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
          $objObservation->setDsObservationProperty($obsId, "resolved", $resolved);

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
          $objObservation->setDsObservationProperty($obsId, "stellar", $stellar);

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
          $objObservation->setDsObservationProperty($obsId, "unusualShape", $unusualShape);

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
          $objObservation->setDsObservationProperty($obsId, "partlyUnresolved", $partlyUnresolved);

          // Character is not mandatory
          if ($resultNode->getElementsByTagName( "character" )->item(0)) {
            $objObservation->setDsObservationProperty($obsId, "clusterType", $resultNode->getElementsByTagName( "character" )->item(0)->nodeValue);
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
            $objObservation->setDsObservationProperty($obsId, "smallDiameter", $smallDiameter);
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
            $objObservation->setDsObservationProperty($obsId, "largeDiameter", $largeDiameter);
          }

          if ($observation->getElementsByTagName( "eyepiece" )->item(0)) {
            $objObservation->setDsObservationProperty($obsId, "eyepieceid", $eyepId);
          }

          if ($observation->getElementsByTagName( "filter" )->item(0)) {
            $objObservation->setDsObservationProperty($obsId, "filterid", $filtId);
          }

          if ($observation->getElementsByTagName( "lens" )->item(0)) {
            $objObservation->setDsObservationProperty($obsId, "lensid", $lensId);
          }
          if ($observation->getElementsByTagName( "magnification" )->item(0)) {
            $objObservation->setDsObservationProperty($obsId, "magnification", $observation->getElementsByTagName( "magnification" )->item(0)->nodeValue);
          }
        }
      }
    }
  }
} else {
  throw new Exception(LangXMLError3);
}
?>
