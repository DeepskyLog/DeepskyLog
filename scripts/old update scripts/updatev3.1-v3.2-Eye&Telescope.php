<?php
 include_once "../lib/database.php";
 include_once "../lib/objects.php";

 $object = new Objects;

 // Reading the position angles of the galaxies
 $filename = "extraGalaxyData";
 $fh = fopen($filename, "r") or die("Could not open the Eye&Telescope database");

 while (!feof($fh))
 {
	$data = fgets($fh);
  $vars = explode("|", $data);

  if (count($vars) != 1)
  {
		if (substr($vars[2], 0, -1) != "")
		{
			$galpa[$vars[0]] = substr($vars[2], 0, -1);
		} else {
			$galpa[$vars[0]] = 999;
		}
 	}
 }

 fclose($fh);

 // Reading the position angles and types of the galactic nebula
 $filename = "extraGNData";
 $fh = fopen($filename, "r") or die("Could not open the Eye&Telescope database");

 while (!feof($fh))
 {
	$data = fgets($fh);
  $vars = explode("|", $data);

  if (count($vars) != 1)
  {
  	if (substr($vars[2], 0, -1) != "")
		{
  		$gnpa[$vars[0]] = substr($vars[2], 0, -1);
		} else {
			$gnpa[$vars[0]] = 999;
		}

	  if ($vars[1] == "\"EN\"") {
  	  $gntype[$vars[0]] = "EMINB";
		} else if ($vars[1] == "\"EN+OCL\"") {
    	$gntype[$vars[0]] = "CLANB";
		} else if ($vars[1] == "\"RN\"") {
  	  $gntype[$vars[0]] = "REFNB";
		} else if ($vars[1] == "\"SNR\"") {
    	$gntype[$vars[0]] = "SNREM";
		} else if ($vars[1] == "\"SNR?\"") {
  	  $gntype[$vars[0]] = "SNREM";
		} else if ($vars[1] == "\"SNR/H-I\"") {
  	  $gntype[$vars[0]] = "SNREM";
		} else if ($vars[1] == "\"EN+RN\"") {
			$gntype[$vars[0]] = "ENRNN";
		} else if ($vars[1] == "\"EN+*\"") {
    	$gntype[$vars[0]] = "ENSTR";
		} else if ($vars[1] == "\"H-II\"") {
    	$gntype[$vars[0]] = "HII";
		} else if ($vars[1] == "\"H-II?\"") {
    	$gntype[$vars[0]] = "HII";
		} else if ($vars[1] == "\"H-III\"") {
    	$gntype[$vars[0]] = "HII";
		} else if ($vars[1] == "\"RN/H-II\"") {
    	$gntype[$vars[0]] = "RNHII";
		} else if ($vars[1] == "\"H-II/RN\"") {
    	$gntype[$vars[0]] = "RNHII";
		} else if ($vars[1] == "\"BN\"") {
    	$gntype[$vars[0]] = "BRTNB";
		} else if ($vars[1] == "\"ComN\"") {
    	$gntype[$vars[0]] = "BRTNB";
		} else if ($vars[1] == "\"StN\"") {
    	$gntype[$vars[0]] = "STNEB";
		} else if ($vars[1] == "\"WRN\"") {
    	$gntype[$vars[0]] = "WRNEB";
  	} else {
    	$gntype[$vars[0]] = $vars[1];
		}
	}
 }

 fclose($fh);

 // Reading the file with the data of the objects
 $filename = "etdata";
 $fh = fopen($filename, "r") or die("Could not open the Eye&Telescope database");

 $db = new database;
 $dbid = $db->login();

 while (!feof($fh))
 {
  $data = fgets($fh);
  $vars = explode("|", $data);

  if (count($vars) > 2)
  {
	  $name = substr($vars[1], 1, -1);
		$newName = "";

		if (substr($vars[2], 1, -1) == "DS") {
			$newName = "";
		} else if (substr($name, 0, 3) == "NGC") {
	  	$newName = "NGC " . substr($name, 3);
		} else if (substr($name, 0, 5) == "ALTER") {
	  	$newName = "";
		} else if (substr($name, 0, 5) == "AUNER") {
	  	$newName = "Auner " . substr($name, 5);
		} else if (substr($name, 0, 8) == "ANTALOVA") {
	  	$newName = "Antalova " . substr($name, 8);
		} else if (substr($name, 0, 12) == "AVENI-HUNTER") {
	  	$newName = "Aveni-Hunter " . substr($name, 12);
		} else if (substr($name, 0, 2) == "AS") {
	  	$newName = "As " . substr($name, 2);
		} else if (substr($name, 0, 2) == "AO") {
	  	$newName = "Ao " . substr($name, 2);
		} else if (substr($name, 0, 1) == "A") {
			$number = substr($name, 1);
			if ($number[0] == " ")
      {
	  		$newName = "A " . substr($name, 2);
			} else {
	  		$newName = "A " . substr($name, 1);
			}
		} else if (substr($name, 0, 7) == "BARNARD") {
	  	$newName = "Barnard " . substr($name, 7);
		} else if (substr($name, 0, 6) == "BLANCO") {
	  	$newName = "Blanco " . substr($name, 6);
		} else if (substr($name, 0, 5) == "BLLAC") {
	  	$newName = "Bl Lac ";
		} else if (substr($name, 0, 3) == "BAS") {
	  	$newName = "Basel " . substr($name, 3);
		} else if (substr($name, 0, 3) == "BAR") {
	  	$newName = "Bark " . substr($name, 3);
		} else if (substr($name, 0, 2) == "BE") {
	  	$newName = "Be " . substr($name, 2);
		} else if (substr($name, 0, 2) == "BN") {
	  	$newName = "BN " . substr($name, 2);
		} else if (substr($name, 0, 2) == "BO") {
	  	$newName = "Bochum " . substr($name, 2);
		} else if (substr($name, 0, 2) == "BI") {
	  	$newName = "Biur " . substr($name, 2);
		} else if (substr($name, 0, 4) == "CGCG") {
	  	$newName = "CGCG " . substr($name, 4);
		} else if (substr($name, 0, 4) == "CTSS") {
	  	$newName = "CTSS " . substr($name, 4);
		} else if (substr($name, 0, 7) == "CYG SNR") {
	  	$newName = "";
		} else if (substr($name, 0, 3) == "CED") {
	  	$newName = "Ced " . substr($name, 3);
		} else if (substr($name, 0, 3) == "CTB") {
	  	$newName = "Ctb " . substr($name, 3);
		} else if (substr($name, 0, 2) == "CZ") {
	  	$newName = "Czernik " . substr($name, 2);
		} else if (substr($name, 0, 2) == "CR") {
	  	$newName = "Cr " . substr($name, 2);
		} else if (substr($name, 0, 2) == "CG") {
	  	$newName = "Cg " . substr($name, 2);
		} else if (substr($name, 0, 2) == "CL") {
	  	$newName = "Cl " . substr($name, 2);
		} else if (substr($name, 0, 5) == "DANKS") {
	  	$newName = "Danks " . substr($name, 5);
		} else if (substr($name, 0, 4) == "DODZ") {
	  	$newName = "DoDz " . substr($name, 4);
		} else if (substr($name, 0, 4) == "DDDM") {
	  	$newName = "Dddm " . substr($name, 4);
		} else if (substr($name, 0, 4) == "DEHT") {
	  	$newName = "Deht " . substr($name, 4);
		} else if (substr($name, 0, 4) == "DURE") {
	  	$newName = "Dure " . substr($name, 4);
		} else if (substr($name, 0, 3) == "DWB") {
	  	$newName = "Dwb " . substr($name, 3);
		} else if (substr($name, 0, 2) == "DO") {
	  	$newName = "Do " . substr($name, 2);
		} else if (substr($name, 0, 2) == "DD") {
	  	$newName = "Dd " . substr($name, 2);
		} else if (substr($name, 0, 2) == "DG") {
	  	$newName = "Dg " . substr($name, 2);
		} else if (substr($name, 0, 3) == "ESO") {
//	  	$newName = "Eso " . substr($name, 3);
	  	$newName = "";
		} else if (substr($name, 0, 3) == "EGB") {
	  	$newName = "Egb " . substr($name, 3);
		} else if (substr($name, 0, 9) == "FEINSTEIN") {
	  	$newName = "Feinstein " . substr($name, 9);
		} else if (substr($name, 0, 4) == "FAIR") {
	  	$newName = "Fair " . substr($name, 4);
		} else if (substr($name, 0, 9) == "GRASDALEN") {
	  	$newName = "Grasdalen " . substr($name, 9);
		} else if (substr($name, 0, 6) == "GRAHAM") {
	  	$newName = "Graham " . substr($name, 6);
		} else if (substr($name, 0, 5) == "GRAFF") {
	  	$newName = "Graff " . substr($name, 5);
		} else if (substr($name, 0, 3) == "GUM") {
	  	$newName = "Gum " . substr($name, 3);
		} else if (substr($name, 0, 2) == "GM") {
	  	$newName = "Gm " . substr($name, 2);
		} else if (substr($name, 0, 13) == "HAVLEN-MOFFAT") {
	  	$newName = "Havlen-Moffat " . substr($name, 13);
		} else if (substr($name, 0, 4) == "HICK") {
	  	$newName = "Hickson " . substr($name, 4);
		} else if (substr($name, 0, 4) == "HUBI") {
	  	$newName = "Hubi " . substr($name, 4);
		} else if (substr($name, 0, 4) == "HAWE") {
	  	$newName = "Hawe " . substr($name, 4);
		} else if (substr($name, 0, 4) == "HATR") {
	  	$newName = "Hatr " . substr($name, 4);
		} else if (substr($name, 0, 4) == "HUDO") {
	  	$newName = "Hudo " . substr($name, 4);
		} else if (substr($name, 0, 3) == "HAF") {
	  	$newName = "Haffner " . substr($name, 3);
		} else if (substr($name, 0, 3) == "HDW") {
	  	$newName = "Hdw " . substr($name, 3);
		} else if (substr($name, 0, 3) == "HBC") {
	  	$newName = "Hbc " . substr($name, 3);
		} else if (substr($name, 0, 2) == "HP") {
	  	$newName = "HP " . substr($name, 2);
		} else if (substr($name, 0, 2) == "HE") {
	  	$newName = "He " . substr($name, 2);
		} else if (substr($name, 0, 2) == "HO") {
	  	$newName = "Ho " . substr($name, 2);
		} else if (substr($name, 0, 2) == "HH") {
	  	$newName = "HH " . substr($name, 2);
		} else if (substr($name, 0, 1) == "H") {
	  	$newName = "H " . substr($name, 1);
		} else if (substr($name, 0, 10) == "ISKUDARIAN") {
	  	$newName = "Iskudarian " . substr($name, 10);
		} else if (substr($name, 0, 4) == "IRAS") {
//	  	$newName = "IRAS " . substr($name, 4);
	  	$newName = "";
		} else if (substr($name, 0, 2) == "IC") {
	  	$newName = "IC " . substr($name, 2);
		} else if (substr($name, 0, 2) == "IR") {
	  	$newName = "IR " . substr($name, 2);
		} else if (substr($name, 0, 4) == "KARA") {
	  	$newName = "KARA " . substr($name, 4);
		} else if (substr($name, 0, 3) == "KAZ") {
	  	$newName = "KAZ " . substr($name, 3);
		} else if (substr($name, 0, 3) == "KFL") {
	  	$newName = "KFL " . substr($name, 3);
		} else if (substr($name, 0, 3) == "KUG") {
	  	$newName = "KUG " . substr($name, 3);
		} else if (substr($name, 0, 3) == "KUV") {
	  	$newName = "KUV " . substr($name, 3);
		} else if (substr($name, 0, 1) == "K") {
			if (substr($vars[2], 1, -1) == "OC") {
				$newName = "King " . substr($name, 1);
			} else {
				$newName = "K " . substr($name, 1);
			}
		} else if (substr($name, 0, 7) == "LATYSEV") {
	  	$newName = "Latysev " . substr($name, 7);
		} else if (substr($name, 0, 3) == "LBN") {
	  	$newName = "LBN " . substr($name, 3);
		} else if (substr($name, 0, 3) == "LDN") {
	  	$newName = "LDN " . substr($name, 3);
		} else if (substr($name, 0, 2) == "LY") {
	  	$newName = "Lynga " . substr($name, 2);
		} else if (substr($name, 0, 2) == "LO") {
	  	$newName = "Loden " . substr($name, 2);
		} else if (substr($name, 0, 2) == "MK") {
	  	$newName = "MK " . substr($name, 2);
		} else if (substr($name, 0, 6) == "MUZZIO") {
	  	$newName = "Muzzio " . substr($name, 6);
		} else if (substr($name, 0, 6) == "MOFFAT") {
	  	$newName = "Moffat " . substr($name, 6);
		} else if (substr($name, 0, 5) == "MAYER") {
	  	$newName = "Mayer " . substr($name, 5);
		} else if (substr($name, 0, 3) == "MCG") {
	  	$newName = "";
//	  	$newName = "MCG " . substr($name, 3);
		} else if (substr($name, 0, 3) == "MAC") {
	  	$newName = "MAC " . substr($name, 3);
		} else if (substr($name, 0, 2) == "MI") {
	  	$newName = "Mi " . substr($name, 2);
		} else if (substr($name, 0, 4) == "MRMG") {
	  	$newName = "Mrmg " . substr($name, 4);
		} else if (substr($name, 0, 3) == "MWC") {
	  	$newName = "Mwc " . substr($name, 3);
		} else if (substr($name, 0, 3) == "MOL") {
	  	$newName = "Mol " . substr($name, 3);
		} else if (substr($name, 0, 3) == "MCW") {
	  	$newName = "Mcw " . substr($name, 3);
		} else if (substr($name, 0, 3) == "MGP") {
	  	$newName = "MGP " . substr($name, 3);
		} else if (substr($name, 0, 3) == "MRK") {
	  	$newName = "Mrk " . substr($name, 3);
		} else if (substr($name, 0, 2) == "MS") {
	  	$newName = "Ms " . substr($name, 2);
		} else if (substr($name, 0, 2) == "MR") {
	  	$newName = "Mr " . substr($name, 2);
		} else if (substr($name, 0, 1) == "M") {
	  	$newName = "M " . substr($name, 1);
		} else if (substr($name, 0, 2) == "NS") {
	  	$newName = "Ns " . substr($name, 2);
		} else if (substr($name, 0, 2) == "OF") {
	  	$newName = "OF " . substr($name, 2);
		} else if (substr($name, 0, 2) == "OI") {
	  	$newName = "OI " . substr($name, 2);
		} else if (substr($name, 0, 2) == "OJ") {
	  	$newName = "OJ " . substr($name, 2);
		} else if (substr($name, 0, 6) == "PISMIS") {
	  	$newName = "Pismis " . substr($name, 6);
		} else if (substr($name, 0, 4) == "PBOZ") {
	  	$newName = "PBOZ " . substr($name, 4);
		} else if (substr($name, 0, 3) == "PAL") {
	  	$newName = "Pal " . substr($name, 3);
		} else if (substr($name, 0, 3) == "PGC") {
        $newName = "";
//	  	$newName = "PGC " . substr($name, 3);
		} else if (substr($name, 0, 2) == "PI") {
	  	$newName = "Pismis " . substr($name, 2);
		} else if (substr($name, 0, 2) == "PK") {
	  	$newName = "PK " . substr($name, 2);
		} else if (substr($name, 0, 2) == "PM") {
	  	$newName = "PM " . substr($name, 2);
		} else if (substr($name, 0, 2) == "PG") {
	  	$newName = "PG " . substr($name, 2);
		} else if (substr($name, 0, 7) == "ROSLUND") {
	  	$newName = "Roslund " . substr($name, 7);
		} else if (substr($name, 0, 3) == "RCW") {
	  	$newName = "RCW " . substr($name, 3);
		} else if (substr($name, 0, 3) == "RXJ") {
	  	$newName = "RXJ " . substr($name, 3);
		} else if (substr($name, 0, 2) == "RU") {
	  	$newName = "Ru " . substr($name, 2);
		} else if (substr($name, 0, 8) == "SCHUSTER") {
	  	$newName = "Schuster " . substr($name, 8);
		} else if (substr($name, 0, 4) == "SHER") {
	  	$newName = "Sher " . substr($name, 4);
		} else if (substr($name, 0, 2) == "ST") {
	  	$newName = "Stock " . substr($name, 2);
		} else if (substr($name, 0, 2) == "SH") {
	  	$newName = "Sh " . substr($name, 2);
		} else if (substr($name, 0, 2) == "SA") {
	  	$newName = "Sa " . substr($name, 2);
		} else if (substr($name, 0, 1) == "S") {
			if (substr($vars[2], 1, -1) == "GN") {
				$newName = "Simeis " . substr($name, 1);
			} else {
				$newName = "S " . substr($name, 1);
			}
		} else if (substr($name, 0, 6) == "TERZAN") {
	  	$newName = "Terzan " . substr($name, 6);
		} else if (substr($name, 0, 4) == "TEJU") {
	  	$newName = "Teju " . substr($name, 4);
		} else if (substr($name, 0, 3) == "TER") {
	  	$newName = "Ter " . substr($name, 3);
		} else if (substr($name, 0, 3) == "TON") {
	  	$newName = "Ton " . substr($name, 3);
		} else if (substr($name, 0, 3) == "TOM") {
	  	$newName = "Tombaugh " . substr($name, 3);
		} else if (substr($name, 0, 2) == "TR") {
	  	$newName = "Tr " . substr($name, 2);
		} else if (substr($name, 0, 6) == "UPGREN") {
	  	$newName = "Upgren " . substr($name, 6);
		} else if (substr($name, 0, 4) == "UGCA") {
	  	$newName = "UGCA " . substr($name, 4);
		} else if (substr($name, 0, 3) == "UGC") {
	  	$newName = "UGC " . substr($name, 3);
		} else if (substr($name, 0, 6) == "VDB-HA") {
	  	$newName = "vdB-Ha " . substr($name, 6);
		} else if (substr($name, 0, 4) == "VDBH") {
	  	$newName = "vdBH " . substr($name, 4);
		} else if (substr($name, 0, 3) == "VDB") {
	  	$newName = "vdB " . substr($name, 3);
		} else if (substr($name, 0, 3) == "VMT") {
	  	$newName = "Vmt " . substr($name, 3);
		} else if (substr($name, 0, 4) == "WRAY") {
	  	$newName = "WRay " . substr($name, 4);
		} else if (substr($name, 0, 2) == "WA") {
	  	$newName = "Waterloo " . substr($name, 2);
		} else if (substr($name, 0, 2) == "WE") {
	  	$newName = "Westr " . substr($name, 2);
		}	

		if ($newName != "") 
		{
		  $type = substr($vars[2], 1, -1);

		  if ($type == "DN")
		  {
	  		$type = "DRKNB";
		  } else if ($type == "GC") {
		  	$type = "GLOCL";
		  } else if ($type == "GN") {
		  		$type = $gntype[$vars[0]];
		  } else if ($type == "GX") {
		  	$type = "GALXY";
		  } else if ($type == "OC") {
				$type = "OPNCL";
			} else if ($type == "PN") {
				$type = "PLNNB";
			} else if ($type == "QS") {
				$type = "QUASR";
			}

			// CONSTELLATION
			$con = strtoupper(substr($vars[6], 1, -1));

			// Right ascension and declination
			$ra = $vars[3];
			$decl = $vars[4];

			// Magnitude and surface brightness
			$mag = $vars[11];
			if ($mag == "")
			{
				$mag = 99.9;
			}

    	$subr = $vars[12];
			if ($subr == "")
			{
				$subr = 99.9;
			}

			// Position Angle... merge 2 files...
			$pa = 999;
			if ($type == "GALXY")
			{
				if (array_key_exists($vars[0], $galpa))
				{
					$pa = $galpa[$vars[0]];
				}
			} else if (substr($vars[2], 1, -1) == "GN")
			{
				$pa = $gnpa[$vars[0]];
			}

			// diameter1 and diameter2
			$diam1 = $vars[10] * 60.0;
			$diam2 = $vars[9] * 60.0;

			// datasource
			$datasource = "E&T 2.5";

			// Calculate the SBObj
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

			$urano = $object->calculateUranometriaPage($ra, $decl);
			$uranonew = $object->calculateNewUranometriaPage($ra, $decl);
			$skyatlas = $object->calculateSkyAtlasPage($ra, $decl);
			$millenium = $object->calculateMilleniumPage($ra, $decl);
			$taki = $object->calculateTakiPage($ra, $decl);

      $sql = "select objectname from objectnames where altname = \"" . $newName . "\"";
			$run = mysql_query($sql) or die(mysql_error());
			$get = mysql_fetch_object($run);

			if (!$get)
			{
				print "Inserting " . $newName . "\n";
				$sql = "INSERT INTO objects (name) values (\"$newName\")";
				$run = mysql_query($sql) or die(mysql_error());

				$names = explode(" ", $newName);
				
			  $sql= "INSERT INTO objectnames (objectname, catalog, catindex, altname) VALUES (\"$newName\", \"$names[0]\", \"$names[1]\", \"$newName\")";
			  mysql_query($sql) or die(mysql_error());	
			} else {
				print "Updating " . $newName . "\n";
			}
			$sql = "UPDATE objects SET type = \"$type\" where name = \"" . $get->objectname . "\"";
			$run = mysql_query($sql) or die(mysql_error());
			$sql = "UPDATE objects SET con = \"$con\" where name = \"" . $get->objectname . "\"";
			$run = mysql_query($sql) or die(mysql_error());
			$sql = "UPDATE objects SET ra = \"$ra\" where name = \"" . $get->objectname . "\"";
			$run = mysql_query($sql) or die(mysql_error());
			$sql = "UPDATE objects SET decl = \"$decl\" where name = \"" . $get->objectname . "\"";
			$run = mysql_query($sql) or die(mysql_error());
			$sql = "UPDATE objects SET mag = \"$mag\" where name = \"" . $get->objectname . "\"";
			$run = mysql_query($sql) or die(mysql_error());
			$sql = "UPDATE objects SET subr = \"$subr\" where name = \"" . $get->objectname . "\"";
			$run = mysql_query($sql) or die(mysql_error());
			$sql = "UPDATE objects SET diam1 = \"$diam1\" where name = \"" . $get->objectname . "\"";
			$run = mysql_query($sql) or die(mysql_error());
			$sql = "UPDATE objects SET diam2 = \"$diam2\" where name = \"" . $get->objectname . "\"";
			$run = mysql_query($sql) or die(mysql_error());
			$sql = "UPDATE objects SET pa = \"$pa\" where name = \"" . $get->objectname . "\"";
			$run = mysql_query($sql) or die(mysql_error());
			$sql = "UPDATE objects SET datasource = \"$datasource\" where name = \"" . $get->objectname . "\"";
			$run = mysql_query($sql) or die(mysql_error());
			$sql = "UPDATE objects SET urano = \"$urano\" where name = \"" . $get->objectname . "\"";
			$run = mysql_query($sql) or die(mysql_error());
			$sql = "UPDATE objects SET urano_new = \"$uranonew\" where name = \"" . $get->objectname . "\"";
			$run = mysql_query($sql) or die(mysql_error());
			$sql = "UPDATE objects SET sky = \"$skyatlas\" where name = \"" . $get->objectname . "\"";
			$run = mysql_query($sql) or die(mysql_error());
			$sql = "UPDATE objects SET millenium = \"$millenium\" where name = \"" . $get->objectname . "\"";
			$run = mysql_query($sql) or die(mysql_error());
			$sql = "UPDATE objects SET taki = \"$taki\" where name = \"" . $get->objectname . "\"";
			$run = mysql_query($sql) or die(mysql_error());
			$sql = "UPDATE objects SET SBObj = \"$SBObj\" where name = \"" . $get->objectname . "\";";
			$run = mysql_query($sql) or die(mysql_error());
		}
	}
 }

 // Drop column catalogs...
 $sql = "ALTER TABLE objects DROP COLUMN catalogs";
 $run = mysql_query($sql) or die(mysql_error());

 $db->logout();

 fclose($fh);

 print "Database was updated successfully!\n";
 // Dubbele objecten bij Cr

?>
