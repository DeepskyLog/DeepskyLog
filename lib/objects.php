<?php
// objects.php
// The objects class collects all functions needed to enter, retrieve and adapt object data from the database and functions to display the data.
global $inIndex;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
class Objects {
	public function addDSObject($name, $cat, $catindex, $type, $con, $ra, $dec, $mag, $subr, $diam1, $diam2, $pa, $datasource) // addObject adds a new object to the database. The name, alternative name, type, constellation, right ascension, declination, magnitude, surface brightness, diam1, diam2, position angle and info about the catalogs should be given as parameters. The chart numbers for different atlasses are put in the database. $datasource describes where the data comes from eg : SAC7.2, DeepskyLogUser or E&T 2.5
{
		global $objDatabase;
		$array = array (
				"INSERT INTO objects (name, type, con, ra, decl, mag, subr, diam1, diam2, pa, datasource, urano, urano_new, sky, millenium, taki, psa, torresB, torresBC, torresC, milleniumbase)
	                  VALUES (\"$name\", \"$type\", \"$con\", \"$ra\", \"$dec\", \"$mag\", \"$subr\", \"$diam1\", \"$diam2\", \"$pa\", \"$datasource\", \"0\", \"0\", \"0\", \"0\", \"0\", \"0\", \"0\", \"0\", \"0\", \"0\")"
		);
		$sql = implode ( "", $array );
		$objDatabase->execSQL ( $sql );
		$newcatindex = ucwords ( trim ( $catindex ) );
		$objDatabase->execSQL ( "INSERT INTO objectnames (objectname, catalog, catindex, altname) VALUES (\"$name\", \"$cat\", \"$catindex\", TRIM(CONCAT(\"$cat\", \" \", \"$newcatindex\")))" );
		$this->setDsObjectAtlasPages ( $name );
		$this->setDsObjectSBObj ( ($name) );
	}
	private function calcContrastAndVisibility($object, $showname, $magnitude, $SBobj, $diam1, $diam2, &$contrast, &$contype, &$popup, &$prefMag) {
		global $objContrast;
		$contrast = "-";
		$prefMag = array (
				"-",
				""
		);
		;
		$popupT = "";
		$contrastCalc = "";
		$magni = $magnitude;
		if ($popup == LangContrastNotLoggedIn)
			return;
		if (($magnitude == 99.9) || ($magnitude == ""))
			$popup = LangContrastNoMagnitude;
		else {
			$diam1 = $diam1 / 60.0;
			if ($diam1 == 0)
				$popup = LangContrastNoDiameter;
			else {
				$diam2 = $diam2 / 60.0;
				if ($diam2 == 0)
					$diam2 = $diam1;
				$contrastCalc = $objContrast->calculateContrast ( $magni, $SBobj, $diam1, $diam2 );
				if ($contrastCalc [0] < - 0.2)
					$popup = $showname . LangContrastNotVisible . $_SESSION ['location'] . LangContrastPlace . $_SESSION ['telescope'];
				else if ($contrastCalc [0] < 0.1)
					$popup = LangContrastQuestionable . $showname . LangContrastQuestionableB . $_SESSION ['location'] . LangContrastPlace . $_SESSION ['telescope'];
				else if ($contrastCalc [0] < 0.35)
					$popup = $showname . LangContrastDifficult . $_SESSION ['location'] . LangContrastPlace . $_SESSION ['telescope'];
				else if ($contrastCalc [0] < 0.5)
					$popup = $showname . LangContrastQuiteDifficult . $_SESSION ['location'] . LangContrastPlace . $_SESSION ['telescope'];
				else if ($contrastCalc [0] < 1.0)
					$popup = $showname . LangContrastEasy . $_SESSION ['location'] . LangContrastPlace . $_SESSION ['telescope'];
				else
					$popup = $showname . LangContrastVeryEasy . $_SESSION ['location'] . LangContrastPlace . $_SESSION ['telescope'];
				$contrast = $contrastCalc [0];
			}
		}
		if ($contrast == "-")
			$contype = "";
		else if ($contrast < - 0.2)
			$contype = "typeNotVisible";
		else if ($contrast < 0.1)
			$contype = "typeQuestionable";
		else if ($contrast < 0.35)
			$contype = "typeDifficult";
		else if ($contrast < 0.5)
			$contype = "typeQuiteDifficult";
		else if ($contrast < 1.0)
			$contype = "typeEasy";
		else
			$contype = "typeVeryEasy";
		if ($contrastCalc) {
			$contrast = sprintf ( "%.2f", $contrastCalc [0] );
			if ($contrastCalc [2] == "")
				$prefMag = array (
						sprintf ( "%d", $contrastCalc [1] ) . "x",
						""
				);
			else
				$prefMag = array (
						sprintf ( "%d", $contrastCalc [1] ) . "x",
						" - " . $contrastCalc [2]
				);
		}
	}
	private function calculateSize($diam1, $diam2) // Construct a string from the sizes
{
		$size = "";
		if ($diam1 != 0.0) {
			if ($diam1 >= 40.0) {
				if (round ( $diam1 / 60.0 ) == ($diam1 / 60.0))
					if (($diam1 / 60.0) > 30.0)
						$size = sprintf ( "%.0f'", $diam1 / 60.0 );
					else
						$size = sprintf ( "%.1f'", $diam1 / 60.0 );
				else
					$size = sprintf ( "%.1f'", $diam1 / 60.0 );
				if ($diam2 != 0.0) {
					if (round ( $diam2 / 60.0 ) == ($diam2 / 60.0))
						if (($diam2 / 60.0) > 30.0)
							$size = $size . sprintf ( "x%.0f'", $diam2 / 60.0 );
						else
							$size = $size . sprintf ( "x%.1f'", $diam2 / 60.0 );
					else
						$size = $size . sprintf ( "x%.1f'", $diam2 / 60.0 );
				}
			} else {
				$size = sprintf ( "%.1f''", $diam1 );
				if ($diam2 != 0.0) {
					$size = $size . sprintf ( "x%.1f''", $diam2 );
				}
			}
		}
		return $size;
	}
	public function getAllInfoDsObject($name) // returns all information of an object
{
		global $objDatabase, $loggedUser;
		$object = $objDatabase->selectRecordArray ( "SELECT * FROM objects WHERE name = \"" . $name . "\"" );
		$object ["size"] = $this->calculateSize ( $object ['diam1'], $object ['diam2'] );
		$object ["seen"] = "-";
		if ($see = $objDatabase->selectSingleValue ( "SELECT COUNT(id) As CountId FROM observations WHERE objectname = \"" . $name . "\"", 'CountId', 0 )) {
			$object ["seen"] = "X (" . $see . ")";
			if ($loggedUser && ($get = $objDatabase->selectRecordArray ( "SELECT COUNT(observerid) As seenCnt, MAX(date) seenLastDate FROM observations WHERE objectname = \"" . $name . "\" AND observerid = \"" . $loggedUser . "\"" )))
				$object ["seen"] = "Y (" . $get ['seenCnt'] . " - " . $get ['seenLastDate'] . ")";
		}
		$run = $objDatabase->selectRecordset ( "SELECT altname FROM objectnames WHERE objectnames.objectname = \"" . $name . "\"" );
		$object ["altname"] = "";
		while ( $get = $run->fetch ( PDO::FETCH_OBJ ) )
			if ($get->altname != $name)
				if ($object ["altname"])
					$object ["altname"] .= "/" . $get->altname;
				else
					$object ["altname"] = $get->altname;
		return $object;
	}
	public function getAlternativeNames($name) {
		global $objDatabase;
		return $objDatabase->selectSingleArray ( "SELECT CONCAT(objectnames.catalog, \" \", objectnames.catindex) AS altnames FROM objectnames WHERE objectnames.objectname = \"" . $name . "\"", 'altnames' );
	}
	public function getCatalogs() // returns a list of all different catalogs
{
		global $objDatabase;
		$ret = $objDatabase->selectSingleArray ( "SELECT DISTINCT objectnames.catalog FROM objectnames WHERE objectnames.catalog NOT IN (\"M\",\"NGC\",\"Caldwell\",\"H400\",\"HII\",\"IC\",\"\")", 'catalog' );
		natcasesort ( $ret );
		reset ( $ret );
		array_unshift ( $ret, "M", "NGC", "Caldwell", "H400", "HII", "IC" );
		return $ret;
	}
	public function getCatalogsAndLists() {
		global $objDatabase, $loggedUser, $objList;
		$ret = $objDatabase->selectSingleArray ( "SELECT DISTINCT objectnames.catalog FROM objectnames WHERE objectnames.catalog NOT IN (\"M\",\"NGC\",\"Caldwell\",\"H400\",\"HII\",\"IC\")", 'catalog' );
		natcasesort ( $ret );
		reset ( $ret );
		array_unshift ( $ret, "M", "NGC", "Caldwell", "H400", "HII", "IC" );
		if (array_key_exists ( 'deepskylog_id', $_SESSION ) && $loggedUser) {
			$lsts = $objList->getLists ();
			while ( list ( $key, $value ) = each ( $lsts ) )
				$ret [] = 'List:' . $value;
		}
		return $ret;
	}
	private function getContainsNames($name) {
		global $objDatabase;
		return $objDatabase->selectSingleArray ( "SELECT objectpartof.objectname FROM objectpartof WHERE objectpartof.partofname = \"" . $name . "\"", 'objectname' );
	}
	public function getDsObjectName($name) // returns the name when the original or alternative name is given.
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT objectnames.objectname FROM objectnames WHERE (objectnames.altname = \"" . $name . "\")", 'objectname' );
	}
	public function getDsObjectTypes() // returns a list of all different types
{
		global $objDatabase;
		return $objDatabase->selectSingleArray ( "SELECT DISTINCT type FROM objects ORDER BY type", 'type' );
	}
	public function getConstellations() // returns a list of all different constellations
{
		global $objDatabase;
		return $objDatabase->selectSingleArray ( "SELECT DISTINCT con FROM objects ORDER BY con", 'con' );
	}
	public function getDsoProperty($theObject, $theProperty, $default = '') // returns the propperty of the object, or default if not found
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT objects." . $theProperty . " FROM objects WHERE name=\"" . $theObject . "\"", $theProperty, $default );
	}
	public function getDSOseenLink($object) // Returns the getSeen result, encoded to a href that shows the seen observations
{
		global $baseURL, $loggedUser;
		$seenDetails = $this->getSeen ( $object );
		$seen = "<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $object ) . "\" title=\"" . LangObjectNSeen . "\">-</a>";
		if (substr ( $seenDetails, 0, 1 ) == "X") // object has been seen already
			$seen = "<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode ( $object ) . "\" title=\"" . LangObjectXSeen . "\">" . $seenDetails . "</a>";
		if ($loggedUser) {
			if (substr ( $seenDetails, 0, 1 ) == "Y") {
				// object has been seen by the observer logged in
				$obj = preg_split ( "/ /", $object );
				$cat = $obj [0];
				$number = $obj [1];
				$seen = "<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;catalog=" . $cat . "&amp;number=" . rawurlencode($number) . "&amp;observer=" . urlencode ( $loggedUser ) . "\" title=\"" . LangObjectYSeen . "\">" . $seenDetails . "</a>";
			}
		}
		return $seen;
	}
	public function getExactDsObject($value, $cat = '', $catindex = '') // returns the exact name of an object
{
		global $objDatabase, $objCatalog;
		if ($value) {
			$value = $objCatalog->checkObject ( $value );
			$sql = "SELECT objectnames.objectname FROM objectnames " . "WHERE UPPER(altname) = \"" . strtoupper ( trim ( $value ) ) . "\" " . "OR altname = \"" . trim ( $value ) . "\"";
		} else {
			$catandindex = $objCatalog->checkObject ( $cat . ' ' . ucwords ( trim ( $catindex ) ) );
			$sql = "SELECT objectnames.objectname FROM objectnames " . "WHERE UPPER(altname) = \"" . strtoupper ( trim ( $catandindex ) ) . "\" " . "OR altname = \"" . trim ( $catandindex ) . "\"";
		}
		if ((! ($object = $objDatabase->selectSingleValue ( $sql, 'objectname', '' ))) && $value) {
			$value = $objCatalog->checkObject ( $value );
			$sql = "SELECT objectnames.objectname FROM objectnames " . "WHERE CONCAT(UPPER(objectnames.catalog),UPPER(objectnames.catindex))=\"" . strtoupper ( str_replace ( ' ', '', $value ) ) . "\"";
			$object = $objDatabase->selectSingleValue ( $sql, 'objectname', '' );
		}
		return $object;
	}
	public function getLikeDsObject($value, $cat = '', $catindex = '') // returns the exact name of an object
{
		global $objDatabase, $objCatalog;
		$value2 = $objCatalog->checkObject ( trim ( $value ) );
		$value = strtoupper ( $objCatalog->checkObject ( trim ( $value ) ) );
		if ($value)
			$sql = "SELECT objectnames.objectname FROM objectnames " . "WHERE UPPER(altname) LIKE \"$value\" " . "OR altname LIKE \"$value2\"";
		else {
			$catindex = ucwords ( $catindex );
			$sql = "SELECT objectnames.objectname FROM objectnames " . "WHERE CONCAT(objectnames.catalog, ' ', objectnames.catindex) LIKE \"" . $objCatalog->checkObject ( $cat . ' ' . $catindex ) . "\"";
		}
		return $objDatabase->selectSingleArray ( $sql, 'objectname' );
	}
	public function getNearbyObjects($objectname, $dist, $ra = 0, $decl = 0) {
		global $objDatabase;
		if ($objectname) {
			$run = $objDatabase->selectRecordset ( "SELECT objects.ra, objects.decl FROM objects WHERE name = \"$objectname\"" );
			$get = $run->fetch ( PDO::FETCH_OBJ );
			$ra = $get->ra;
			$decl = $get->decl;
		}
		$dra = 0.0011 * $dist / cos ( $decl / 180 * 3.1415926535 );
		$run = $objDatabase->selectRecordset ( "SELECT objects.name FROM objects WHERE ((objects.ra > $ra - $dra) AND (objects.ra < $ra + $dra) AND (objects.decl > $decl - ($dist/60)) AND (objects.decl < $decl + ($dist/60))) ORDER BY objects.name" );
		for($result = array (), $i = 0; ($get = $run->fetch ( PDO::FETCH_OBJ )); $i ++)
			$result [$get->name] = array (
					$i,
					$get->name
			);
		return $result;
	}
	public function getNearbyObjectsForCheck($ra = 0, $decl = 0, $dist) {
		global $objDatabase;
		$dra = 0.0011 * $dist / cos ( $decl / 180 * 3.1415926535 );
		return $objDatabase->selectRecordsetArray ( "SELECT objects.name,objects.type,objects.ra,decl FROM objects WHERE ((objects.ra > $ra - $dra) AND (objects.ra < $ra + $dra) AND (objects.decl > $decl - ($dist/60)) AND (objects.decl < $decl + ($dist/60))) ORDER BY objects.name" );
	}
	public function getNumberOfObjectsInCatalog($catalog) // returns the number of objects in the catalog given as a parameter
{
		global $objDatabase, $loggedUser;
		if (substr ( $catalog, 0, 5 ) == "List:")
			$sql = "SELECT COUNT(DISTINCT observerobjectlist.objectname)-1 AS number FROM observerobjectlist WHERE observerobjectlist.listname = \"" . substr ( $catalog, 5 ) . "\"";
		else
			$sql = "SELECT COUNT(DISTINCT catindex) AS number FROM objectnames WHERE catalog = \"$catalog\"";
		return $objDatabase->selectSingleValue ( $sql, 'number', 0 );
	}
	public function getObjectFromQuery($queries, $exact = 0, $seen = "A", $partof = 0) { // getObjectFromQuery returns an array with the names of all objects where
	                                                                                     // the queries are defined in an array.
	                                                                                     // An example of an array :
	                                                                                     // $q = array("name" => "NGC", "type" => "GALXY", "constellation" => "AND",
	                                                                                     // "minmag" => "12.0", "maxmag" => "14.0", "minsubr" => "13.0",
	                                                                                     // "maxsubr" => "14.0", "minra" => "0.3", "maxra" => "0.9",
	                                                                                     // "mindecl" => "24.0", "maxdecl" => "30.0", "urano" => "111",
	                                                                                     // "uranonew" => "111", "sky" => "11", "msa" => "222",
	                                                                                     // "taki" => "11", "psa" => "12", "torresB" => "11", "torresBC" => "13",
	                                                                                     // "torresC" => "31", "mindiam1" => "12.2", "maxdiam1" => "13.2",
	                                                                                     // "mindiam2" => "11.1", "maxdiam2" => "22.2", "inList" => "Edge-ons", "notInList" => "My observed Edge-ons");
		global $loggedUser, $objDatabase, $objCatalog;
		$obs = array ();
		$sql = "";
		$sqland = "";
		$sql1 = "SELECT DISTINCT (objectnames.objectname) AS name, " . "(objectnames.altname) AS showname " . "FROM objectnames " . "JOIN objects ON objects.name = objectnames.objectname ";
		$sql2 = "SELECT DISTINCT (objectpartof.objectname) AS name, " . "CONCAT((objectnames.altname), \"-\", (objectpartof.objectname)) As showname  " . "FROM objectpartof " . "JOIN objects ON (objects.name = objectpartof.objectname) " . "JOIN objectnames ON (objectnames.objectname = objectpartof.partofname) ";
		if (array_key_exists ( 'inList', $queries ) && $queries ['inList']) {
			$sql1 .= "JOIN observerobjectlist AS A " . "ON A.objectname = objects.name ";
			$sql2 .= "JOIN observerobjectlist AS A " . "ON A.objectname = objects.name ";
			$sqland .= "AND A.listname = \"" . $queries ['inList'] . "\" AND A.objectname <>\"\" ";
		}

		if (array_key_exists ( 'notInList', $queries ) && $queries ['notInList']) {
			$sqland .= " AND (objectnames.objectname NOT IN (SELECT objectname FROM observerobjectlist WHERE listname = \"" . $queries ['notInList'] . "\" ) ) ";
		}

		$sql1 .= "WHERE ";
		$sql2 .= "WHERE ";
		if (array_key_exists ( 'name', $queries ) && $queries ["name"] != "")
			if ($exact == 0)
				$sqland = $sqland . " AND (objectnames.catalog = \"" . $queries ["name"] . "\")";
			elseif ($exact == 1)
				$sqland = $sqland . " AND (UPPER(objectnames.altname) like \"" . strtoupper ( $objCatalog->checkObject ( $queries ["name"] ) ) . "\")";
			// $sqland = $sqland . " AND (CONCAT(UPPER(objectnames.catalog),UPPER(objectnames.catindex)) like \"" . strtoupper(str_replace(' ','',$queries["name"])) . "\") ";
		$sqland .= (array_key_exists ( 'type', $queries ) && $queries ['type']) ? " AND (objects.type=\"" . $queries ['type'] . "\")" : '';
		$sqland .= (array_key_exists ( 'con', $queries ) && $queries ['con']) ? " AND (objects.con>=\"" . $queries ['con'] . "\") AND (objects.con<=\"" . $queries ['conto'] . "\")" : '';
		$sqland .= (array_key_exists ( 'minmag', $queries ) && $queries ['minmag']) ? " AND (objects.mag>\"" . $queries ["minmag"] . "\" or objects.mag like \"" . $queries ["minmag"] . "\")" : '';
		$sqland .= (array_key_exists ( 'maxmag', $queries ) && $queries ['maxmag']) ? " AND (objects.mag<\"" . $queries ["maxmag"] . "\" or objects.mag like \"" . $queries ["maxmag"] . "\")" : '';
		$sqland .= (array_key_exists ( 'minsubr', $queries ) && $queries ['minsubr']) ? " AND objects.subr>=\"" . $queries ["minsubr"] . "\"" : '';
		$sqland .= (array_key_exists ( 'maxsubr', $queries ) && $queries ['maxsubr']) ? " AND objects.subr<=\"" . $queries ["maxsubr"] . "\"" : '';
		if ((array_key_exists ( 'minra', $queries ) && ($queries ['minra'] !== '')) && (array_key_exists ( 'maxra', $queries ) && ($queries ['maxra'] !== '')) && ($queries ['minra'] > $queries ['maxra']))
			$sqland .= (array_key_exists ( 'minra', $queries ) && ($queries ['minra'] !== '')) ? " AND ((objects.ra >= \"" . $queries ["minra"] . "\") OR (objects.ra <= \"" . $queries ["maxra"] . "\"))" : "";
		else {
			$sqland .= (array_key_exists ( 'minra', $queries ) && ($queries ['minra'] !== '')) ? " AND (objects.ra >= \"" . $queries ["minra"] . "\")" : "";
			$sqland .= (array_key_exists ( 'maxra', $queries ) && ($queries ['maxra'] !== '')) ? " AND (objects.ra <= \"" . $queries ["maxra"] . "\")" : '';
		}
		$sqland .= (array_key_exists ( 'mindecl', $queries ) && ($queries ['mindecl'] !== '')) ? " AND (objects.decl >= \"" . $queries ["mindecl"] . "\")" : '';
		$sqland .= (array_key_exists ( 'maxdecl', $queries ) && ($queries ['maxdecl'] !== '')) ? " AND (objects.decl <= \"" . $queries ["maxdecl"] . "\")" : '';
		$sqland .= (array_key_exists ( 'mindiam1', $queries ) && $queries ['mindiam1']) ? " AND (objects.diam1 > \"" . $queries ["mindiam1"] . "\" or objects.diam1 like \"" . $queries ["mindiam1"] . "\")" : '';
		$sqland .= (array_key_exists ( 'maxdiam1', $queries ) && $queries ['maxdiam1']) ? " AND (objects.diam1 <= \"" . $queries ["maxdiam1"] . "\" or objects.diam1 like \"" . $queries ["maxdiam1"] . "\")" : '';
		$sqland .= (array_key_exists ( 'mindiam2', $queries ) && $queries ['mindiam2']) ? " AND (objects.diam2 > \"" . $queries ["mindiam2"] . "\" or objects.diam2 like \"" . $queries ["mindiam2"] . "\")" : '';
		$sqland .= (array_key_exists ( 'maxdiam2', $queries ) && $queries ['maxdiam2']) ? " AND(objects.diam2 <= \"" . $queries ["maxdiam2"] . "\" or objects.diam2 like \"" . $queries ["maxdiam2"] . "\")" : '';
		$sqland .= (array_key_exists ( 'descriptioncontains', $queries ) && $queries ['descriptioncontains']) ? " AND(objects.description LIKE \"%" . $queries ["descriptioncontains"] . "%\")" : '';
		$sqland .= (array_key_exists ( 'atlas', $queries ) && $queries ['atlas'] && array_key_exists ( 'atlasPageNumber', $queries ) && $queries ["atlasPageNumber"]) ? " AND (objects." . $queries ["atlas"] . "=\"" . $queries ["atlasPageNumber"] . "\")" : '';
		// if(array_key_exists('excl',$queries)&&($excl=$queries['excl']))
		// { while(list($key,$value)=each($excl))
		// $sqland.=" AND (objects.name NOT LIKE '".$value." %')";
		// }
		$sqland = substr ( $sqland, 4 );
		if (trim ( $sqland ) == '')
			$sqland = " (objectnames.altname like \"%\")";
		if ($partof)
			$sql = "(" . $sql1 . $sqland . ") UNION (" . $sql2 . $sqland . ")";
		else
			$sql = $sql1 . $sqland;
			// $sql.=" LIMIT 0,10000";
			// echo $sql."<p>&nbsp;</p>"; return;
		$run = $objDatabase->selectRecordset ( $sql );
		$i = 0;
		if (array_key_exists ( 'name', $queries ) && $queries ["name"]) {
			while ( $get = $run->fetch ( PDO::FETCH_OBJ ) )
				if ($get->showname == $get->name) {
					if (! array_key_exists ( $get->showname, $obs ))
						$obs [$get->showname] = array (
								$i ++,
								$get->name
						);
				} else if (! array_key_exists ( $get->showname . " (" . $get->name . ")", $obs ))
					$obs [$get->showname . " (" . $get->name . ")"] = array (
							$i ++,
							$get->name
					);
		} else
			while ( $get = $run->fetch ( PDO::FETCH_OBJ ) )
				if (! array_key_exists ( $get->name, $obs ))
					$obs [$get->name] = array (
							$i ++,
							$get->name
					);
		if (round ( count ( $obs ) * 0.005 ) > 30)
			set_time_limit ( round ( count ( $obs ) * 0.005 ) );
		$obs = $this->getSeenObjectDetails ( $obs, $seen );
		if (array_key_exists ( 'minContrast', $queries ) && $queries ["minContrast"])
			for($new_obs = $obs, $obs = array (); list ( $key, $value ) = each ( $new_obs );)
				if ($value ['objectcontrast'] >= $queries ["minContrast"])
					$obs [] = $value;
		if (array_key_exists ( 'maxContrast', $queries ) && $queries ["maxContrast"])
			for($new_obs = $obs, $obs = array (); list ( $key, $value ) = each ( $new_obs );)
				if ($value ['objectcontrast'] <= $queries ["maxContrast"])
					$obs [] = $value;
		if (array_key_exists ( 'exclexceptseen', $queries ) && ($queries ['exclexceptseen'] == 'on')) {
			if (array_key_exists ( 'excl', $queries ) && ($excl = $queries ['excl'])) {
				for($new_obs = $obs, $obs = array (); list ( $key, $value ) = each ( $new_obs );)
					if (($value ['objectseen'] != '-') || (! (in_array ( substr ( $value ['objectname'], 0, strpos ( $value ['objectname'], ' ' ) ), $excl ))))
						$obs [] = $value;
			}
		} else {
			if (array_key_exists ( 'excl', $queries ) && ($excl = $queries ['excl']))
				for($new_obs = $obs, $obs = array (); list ( $key, $value ) = each ( $new_obs );)
					if (! (in_array ( substr ( $value ['objectname'], 0, strpos ( $value ['objectname'], ' ' ) ), $excl )))
						$obs [] = $value;
		}
		return $obs;
	}
	public function getObjectsFromCatalog($cat) {
		global $objDatabase, $loggedUser;
		if (substr ( $cat, 0, 5 ) == "List:")
			$sql = "SELECT DISTINCT observerobjectlist.objectname, observerobjectlist.objectname As altname, observerobjectlist.objectplace As catindex  FROM observerobjectlist " . "WHERE (observerobjectlist.listname = \"" . substr ( $cat, 5 ) . "\")";
		else
			$sql = "SELECT DISTINCT objectnames.objectname, objectnames.catindex, objectnames.altname " . "FROM objectnames WHERE objectnames.catalog = \"$cat\"";
		$run = $objDatabase->selectRecordset ( $sql );
		$obs = array ();
		while ( $get = $run->fetch ( PDO::FETCH_OBJ ) )
			if ($get->objectname)
				$obs [$get->catindex] = array (
						$get->objectname,
						$get->altname
				);
		uksort ( $obs, "strnatcasecmp" );
		return $obs;
	}
	public function getObjects($lLhr, $rLhr, $dDdeg, $uDdeg, $mag) // returns an array containing all objects data between the specified coordinates
{
		global $objDatabase;
		$objects = array ();
		if ($lLhr < $rLhr) { // $sql="SELECT * FROM objects WHERE (ra>".$rLhr.") AND (decl>".$dDdeg.") AND (decl<".$uDdeg.") AND ((mag<=".$mag.") OR (mag>99)) ORDER BY mag;";
		                     // $objects=$objDatabase->selectRecordsetArray($sql);
		                     // $sql="SELECT * FROM objects WHERE (ra<".$lLhr.") AND (decl>".$dDdeg.") AND (decl<".$uDdeg.") AND ((mag<=".$mag.") OR (mag>99)) ORDER BY mag;";
		                     // $objects=array_merge($objects,$objDatabase->selectRecordsetArray($sql));
			$sql = "SELECT * FROM objects WHERE (ra>" . $rLhr . ") AND (decl>" . $dDdeg . ") AND (decl<" . $uDdeg . ") AND ((mag<=" . $mag . ")) ORDER BY mag;";
			$objects = $objDatabase->selectRecordsetArray ( $sql );
			$sql = "SELECT * FROM objects WHERE (ra<" . $lLhr . ") AND (decl>" . $dDdeg . ") AND (decl<" . $uDdeg . ") AND ((mag<=" . $mag . ")) ORDER BY mag;";
			$objects = array_merge ( $objects, array_merge ( $objects, $objDatabase->selectRecordsetArray ( $sql ) ) );
			$sql = "SELECT * FROM objects WHERE (ra>" . $rLhr . ") AND (decl>" . $dDdeg . ") AND (decl<" . $uDdeg . ") AND ((mag>99) AND (diam1>" . (60 * (15 - $mag)) . ")) ORDER BY mag;";
			$objects = array_merge ( $objects, $objDatabase->selectRecordsetArray ( $sql ) );
			$sql = "SELECT * FROM objects WHERE (ra<" . $lLhr . ") AND (decl>" . $dDdeg . ") AND (decl<" . $uDdeg . ") AND ((mag>99) AND (diam1>" . (60 * (15 - $mag)) . ")) ORDER BY mag;";
			$objects = array_merge ( $objects, $objDatabase->selectRecordsetArray ( $sql ) );
		} else { // $sql="SELECT * FROM objects WHERE (ra<".$lLhr.") AND (ra>".$rLhr.") AND (decl>".$dDdeg.") AND (decl<".$uDdeg.") AND ((mag<=".$mag.") OR (mag>99)) ORDER BY mag;";
		         // $objects=$objDatabase->selectRecordsetArray($sql);
			$sql = "SELECT * FROM objects WHERE (ra<" . $lLhr . ") AND (ra>" . $rLhr . ") AND (decl>" . $dDdeg . ") AND (decl<" . $uDdeg . ") AND ((mag<=" . $mag . ")) ORDER BY mag;";
			$objects = $objDatabase->selectRecordsetArray ( $sql );
			$sql = "SELECT * FROM objects WHERE (ra<" . $lLhr . ") AND (ra>" . $rLhr . ") AND (decl>" . $dDdeg . ") AND (decl<" . $uDdeg . ") AND ((mag>90) AND (diam1>" . (60 * (15 - $mag)) . ")) ORDER BY mag;";
			$objects = array_merge ( $objects, $objDatabase->selectRecordsetArray ( $sql ) );
		}
		for($i = 0; $i < count ( $objects ); $i ++)
			$objects [$i] ['seen'] = $this->getSeen ( $objects [$i] ['name'] );
		return $objects;
	}
	public function getObjectsMag($lLhr, $rLhr, $dDdeg, $uDdeg, $frommag, $tomag, $theobject = '') // returns an array containing all objects data between the specified coordinates
{
		global $objDatabase;
		$objects = array ();
		if ($lLhr < $rLhr) { // $sql="SELECT * FROM objects WHERE (ra>".$rLhr.") AND (decl>".$dDdeg.") AND (decl<".$uDdeg.") AND ((mag<=".$mag.") OR (mag>99)) ORDER BY mag;";
		                     // $objects=$objDatabase->selectRecordsetArray($sql);
		                     // $sql="SELECT * FROM objects WHERE (ra<".$lLhr.") AND (decl>".$dDdeg.") AND (decl<".$uDdeg.") AND ((mag<=".$mag.") OR (mag>99)) ORDER BY mag;";
		                     // $objects=array_merge($objects,$objDatabase->selectRecordsetArray($sql));
			$sql = "SELECT * FROM objects WHERE ((ra>" . $rLhr . ") AND (decl>" . $dDdeg . ") AND (decl<" . $uDdeg . ") AND (mag>" . $frommag . ") AND (mag<=" . $tomag . ")) OR name='" . addslashes ( $theobject ) . "' ORDER BY name;";
			$objects = $objDatabase->selectRecordsetArray ( $sql );
			$sql = "SELECT * FROM objects WHERE ((ra<" . $lLhr . ") AND (decl>" . $dDdeg . ") AND (decl<" . $uDdeg . ") AND (mag>" . $frommag . ") AND (mag<=" . $tomag . ")) OR name='" . addslashes ( $theobject ) . "' ORDER BY name;";
			$objects = array_merge ( $objects, array_merge ( $objects, $objDatabase->selectRecordsetArray ( $sql ) ) );
			$sql = "SELECT * FROM objects WHERE ((ra>" . $rLhr . ") AND (decl>" . $dDdeg . ") AND (decl<" . $uDdeg . ") AND ((mag>99) AND (diam1<" . (60 * (15 - $frommag)) . ") AND (diam1>=" . (60 * (15 - $tomag)) . "))) OR name='" . addslashes ( $theobject ) . "' ORDER BY name;";
			$objects = array_merge ( $objects, $objDatabase->selectRecordsetArray ( $sql ) );
			$sql = "SELECT * FROM objects WHERE ((ra<" . $lLhr . ") AND (decl>" . $dDdeg . ") AND (decl<" . $uDdeg . ") AND ((mag>99) AND (diam1<" . (60 * (15 - $frommag)) . ") AND (diam1>=" . (60 * (15 - $tomag)) . "))) OR name='" . addslashes ( $theobject ) . "' ORDER BY name;";
			$objects = array_merge ( $objects, $objDatabase->selectRecordsetArray ( $sql ) );
		} else { // $sql="SELECT * FROM objects WHERE (ra<".$lLhr.") AND (ra>".$rLhr.") AND (decl>".$dDdeg.") AND (decl<".$uDdeg.") AND ((mag<=".$mag.") OR (mag>99)) ORDER BY mag;";
		         // $objects=$objDatabase->selectRecordsetArray($sql);
			$sql = "SELECT * FROM objects WHERE ((ra<" . $lLhr . ") AND (ra>" . $rLhr . ") AND (decl>" . $dDdeg . ") AND (decl<" . $uDdeg . ") AND (mag<=" . $tomag . ") AND (mag>" . $frommag . ")) OR name='" . addslashes ( $theobject ) . "' ORDER BY name;";
			$objects = $objDatabase->selectRecordsetArray ( $sql );
			$sql = "SELECT * FROM objects WHERE ((ra<" . $lLhr . ") AND (ra>" . $rLhr . ") AND (decl>" . $dDdeg . ") AND (decl<" . $uDdeg . ") AND ((mag>99) AND (diam1>=" . (60 * (15 - $tomag)) . ") AND (diam1<" . (60 * (15 - $frommag)) . "))) OR name='" . addslashes ( $theobject ) . "' ORDER BY name;";
			$objects = array_merge ( $objects, $objDatabase->selectRecordsetArray ( $sql ) );
		}
		for($i = 0; $i < count ( $objects ); $i ++)
			$objects [$i] ['seen'] = $this->getSeen ( $objects [$i] ['name'] );
		$theobjects = array ();
		$theresult = array ();
		for($i = 0; $i < count ( $objects ); $i ++)
			if (! (in_array ( $objects [$i] ['name'], $theobjects ))) {
				$theobjects [] = $objects [$i] ['name'];
				$theresult [] = $objects [$i];
			}
		return $theresult;
	}
	public function getObject($theobject = '') // returns an array containing all objects data between the specified coordinates
{
		global $objDatabase;
		$objects = array ();
		$sql = "SELECT * FROM objects WHERE name='" . addslashes ( $theobject ) . "';";
		$objects = $objDatabase->selectRecordsetArray ( $sql );
		for($i = 0; $i < count ( $objects ); $i ++)
			$objects [$i] ['seen'] = $this->getSeen ( $objects [$i] ['name'] );
		return $objects;
	}
	public function getObjectVisibilities($obs) {
		global $objPresentations;
		$popupT = $this->prepareObjectsContrast ();
		if ($popupT)
			for($j = 0; $j < count ( $obs ); $j ++) {
				$obs [$j] ['objectcontrast'] = '-';
				$obs [$j] ['objectcontrasttype'] = '-';
				$obs [$j] ['objectcontrastpopup'] = $popupT;
				$obs [$j] ['objectoptimalmagnification'] = '-';
				$obs [$j] ['objectoptimalmagnificationvalue'] = '-';
			}
		else
			for($j = 0; $j < count ( $obs ); $j ++) {
				$this->calcContrastAndVisibility ( $obs [$j] ['objectname'], $obs [$j] ['showname'], $obs [$j] ['objectmagnitude'], $obs [$j] ['objectsbcalc'], $obs [$j] ['objectdiam1'], $obs [$j] ['objectdiam2'], $contrast, $contype, $popup, $contrastcalc1 );
				$obs [$j] ['objectcontrast'] = $objPresentations->presentationInt1 ( $contrast, '', '' );
				$obs [$j] ['objectcontrasttype'] = $contype;
				$obs [$j] ['objectcontrastpopup'] = $popup;
				$obs [$j] ['objectoptimalmagnification'] = $contrastcalc1 [0] . $contrastcalc1 [1];
				$obs [$j] ['objectoptimalmagnificationvalue'] = $contrastcalc1 [0];
			}
		$obs = $this->getObjectRisSetTrans ( $obs );

		return $obs;
	}
	public function getObjectRisSetTrans($obs) {
		global $loggedUser, $objObserver, $objLocation, $objAstroCalc, $dateformat, $globalMonth, $objUtil;
		if ($loggedUser && $objObserver->getObserverProperty ( $loggedUser, 'stdLocation' )) {
			$theYear = $objUtil->checkSessionKey ( 'globalYear', date ( 'Y' ) );
			$theMonth = $objUtil->checkSessionKey ( 'globalMonth', date ( 'n' ) );
			$theDay = $objUtil->checkSessionKey ( 'globalDay', date ( 'j' ) );
			// 2) Get the julian day of today...
			$jd = gregoriantojd ( $theMonth, $theDay, $theYear );

			// 3) Get the standard location of the observer
			$longitude = $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdLocation' ), 'longitude' );
			$latitude = $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdLocation' ), 'latitude' );

			$timezone = $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdLocation' ), 'timezone' );

			$dateTimeZone = new DateTimeZone ( $timezone );
			$datestr = sprintf ( "%02d", $theMonth ) . "/" . sprintf ( "%02d", $theDay ) . "/" . $theYear;
			$dateTime = new DateTime ( $datestr, $dateTimeZone );
			// Geeft tijdsverschil terug in seconden
			$timedifference = $dateTimeZone->getOffset ( $dateTime );
			$timedifference = $timedifference / 3600.0;
			$dateTimeText = date ( $dateformat, mktime ( 0, 0, 0, $theMonth, $theDay, $theYear ) );
			$location = $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdLocation' ), 'name' );
			if (strncmp ( $timezone, "Etc/GMT", 7 ) == 0) {
				$timedifference = - $timedifference;
			}
			for($j = 0; $j < count ( $obs ); $j ++) {
				$ra = $obs [$j] ["objectra"];
				$dec = $obs [$j] ["objectdecl"];
				$ristraset = $objAstroCalc->calculateRiseTransitSettingTime ( $longitude, $latitude, $ra, $dec, $jd, $timedifference );
				if ($ristraset [0] == "-" && strncmp ( $ristraset [3], "-", 1 ) == 0) {
					$popup1 = $obs [$j] ["objectname"] . LangDoesntrise;
				} else if ($ristraset [0] == "-") {
					$popup1 = $obs [$j] ["objectname"] . LangCircumpolar;
				} else {
					$popup1 = $obs [$j] ["objectname"] . LangRise . $ristraset [0] . LangRistrasetOn . $dateTimeText . LangRistrasetIn . addslashes ( $location );
				}
				$popup2 = $obs [$j] ["objectname"] . LangTransitPopup . $ristraset [1] . LangRistrasetOn . $dateTimeText . LangRistrasetIn . addslashes ( $location );
				if ($ristraset [2] == "-" && strncmp ( $ristraset [3], "-", 1 ) == 0) {
					$popup3 = $obs [$j] ["objectname"] . LangDoesntrise;
				} else if ($ristraset [2] == "-") {
					$popup3 = $obs [$j] ["objectname"] . LangCircumpolar;
				} else {
					$popup3 = $obs [$j] ["objectname"] . LangSet . $ristraset [2] . LangRistrasetOn . $dateTimeText . LangRistrasetIn . addslashes ( $location );
				}
				if ($ristraset [3] == "-") {
					$popup4 = $obs [$j] ["objectname"] . LangDoesntrise;
				} else {
					$popup4 = $obs [$j] ["objectname"] . LangAltitude . $ristraset [3] . LangRistrasetIn . addslashes ( $location );
				}
				$obs [$j] ['objectrise'] = $ristraset [0];
				$obs [$j] ['objectriseorder'] = ($ristraset [0] != '-' ? ($ristraset [0] < 10 ? (substr ( $ristraset [0], 0, 1 ) * 100) + 2400 + substr ( $ristraset [0], 2, 2 ) : ($ristraset [0] < 12 ? (substr ( $ristraset [0], 0, 2 ) * 100) + 2400 + substr ( $ristraset [0], 3, 2 ) : (substr ( $ristraset [0], 0, 2 ) * 100) + substr ( $ristraset [0], 3, 2 ))) : 9999);
				$obs [$j] ['objecttransit'] = $ristraset [1];
				$obs [$j] ['objecttransitorder'] = ($ristraset [1] != '-' ? ($ristraset [1] < 10 ? (substr ( $ristraset [1], 0, 1 ) * 100) + 2400 + substr ( $ristraset [1], 2, 2 ) : ($ristraset [1] < 12 ? (substr ( $ristraset [1], 0, 2 ) * 100) + 2400 + substr ( $ristraset [1], 3, 2 ) : (substr ( $ristraset [1], 0, 2 ) * 100) + substr ( $ristraset [1], 3, 2 ))) : 9999);
				$obs [$j] ['objectset'] = $ristraset [2];
				$obs [$j] ['objectsetorder'] = ($ristraset [2] != '-' ? ($ristraset [2] < 10 ? (substr ( $ristraset [2], 0, 1 ) * 100) + 2400 + substr ( $ristraset [2], 2, 2 ) : ($ristraset [2] < 12 ? (substr ( $ristraset [2], 0, 2 ) * 100) + 2400 + substr ( $ristraset [2], 3, 2 ) : (substr ( $ristraset [2], 0, 2 ) * 100) + substr ( $ristraset [2], 3, 2 ))) : 9999);
				$obs [$j] ['objectbest'] = $ristraset [4];
				$obs [$j] ['objectbestorder'] = ($ristraset [4] != '-' ? ($ristraset [4] < 10 ? (substr ( $ristraset [4], 0, 1 ) * 1000000) + 24000000 + (substr ( $ristraset [4], 2, 2 ) * 10000) : ($ristraset [4] < 12 ? (substr ( $ristraset [4], 0, 2 ) * 1000000) + 24000000 + (substr ( $ristraset [4], 3, 2 ) * 10000) : (substr ( $ristraset [4], 0, 2 ) * 1000000) + (substr ( $ristraset [4], 3, 2 ) * 10000) + ($obs [$j] ['objectsetorder']))) : 99999999);
				$obs [$j] ['objectmaxaltitude'] = $ristraset [3];
				$obs [$j] ['objectrisepopup'] = $popup1;
				$obs [$j] ['objecttransitpopup'] = $popup2;
				$obs [$j] ['objectsetpopup'] = $popup3;
				$obs [$j] ['objectmaxaltitudepopup'] = $popup4;
			}
		} else {
			for($j = 0; $j < count ( $obs ); $j ++) {
				$obs [$j] ['objectrise'] = "-";
				$obs [$j] ['objecttransit'] = "-";
				$obs [$j] ['objectset'] = "-";
				$obs [$j] ['objectbest'] = "-";
				$obs [$j] ['objectbestorder'] = "99";
				$obs [$j] ['objectmaxaltitude'] = "-";
				$obs [$j] ['objectrisepopup'] = "-";
				$obs [$j] ['objecttransitpopup'] = "-";
				$obs [$j] ['objectsetpopup'] = "-";
				$obs [$j] ['objectmaxaltitudepopup'] = "-";
			}
		}
		return $obs;
	}
	private function getPartOfNames($name) {
		global $objDatabase;
		return $objDatabase->selectSingleArray ( "SELECT objectpartof.partofname FROM objectpartof WHERE objectpartof.objectname = \"" . $name . "\"", 'partofname' );
	}
	public function getSeen($object) {
		// Returns -, X(totalnr) or Y(totalnr/personalnr) depending on the seen-degree of the objects
		global $loggedUser, $objDatabase;
		$seen = '-';
		if ($ObsCnt = $objDatabase->selectSingleValue ( "SELECT COUNT(observations.id) As ObsCnt FROM observations WHERE objectname = \"" . $object . "\" AND visibility != 7 ", 'ObsCnt' )) {
			$seen = 'X(' . $ObsCnt . ')';
			if ($loggedUser) {
				$run3 = $objDatabase->selectRecordset ( "SELECT COUNT(observations.id) As PersObsCnt, MAX(observations.date) As PersObsMaxDate FROM observations WHERE objectname = \"" . $object . "\" AND observerid = \"" . $loggedUser . "\" AND visibility != 7" );
				$get3 = $run3->fetch ( PDO::FETCH_OBJ );
				if ($get3->PersObsCnt > 0) {
					$run4 = $objDatabase->selectRecordset ( "SELECT COUNT(observations.id) As PersObsCnt FROM observations WHERE objectname = \"" . $object . "\" AND observerid = \"" . $loggedUser . "\" AND visibility != 7 AND hasDrawing=1" );
					if ($run4->fetch ( PDO::FETCH_OBJ )->PersObsCnt > 0) {
						$seen = 'YD(' . $ObsCnt . '/' . $get3->PersObsCnt . ')&nbsp;' . $get3->PersObsMaxDate;
					} else {
						$seen = 'Y(' . $ObsCnt . '/' . $get3->PersObsCnt . ')&nbsp;' . $get3->PersObsMaxDate;
					}
				}
			}
		}
		return $seen;
	}
	public function getSeenComprehensive($object) {
		// Returns -, X(totalnr) or Y(totalnr/personalnr) depending on the seen-degree of the objects
		global $loggedUser, $objDatabase, $baseURL, $dateformat;

		$ObsCnt = $objDatabase->selectSingleValue ( "SELECT COUNT(observations.id) As ObsCnt FROM observations WHERE objectname = \"" . $object . "\" AND visibility != 7 ", 'ObsCnt' );

		echo "<div class=\"row\">
		       <div class=\"col-md-6\">";
		echo "<table class=\"table table-bordered table-condensed table-striped table-hover table-responsive\">";
		echo " <tr>";
		echo "  <td>" . LangViewObserverNumberOfObservations . "</td><td><a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode ( $object ) . "\" title=\"" . LangObjectYSeen . "\">" . $ObsCnt . "</a></td>";
		echo " </tr>";
		echo " <tr>";
		echo "  <td>" . LangTopObserversHeader7 . "</td>";
		$run4 = $objDatabase->selectRecordset ( "SELECT COUNT(observations.id) As totDraw FROM observations WHERE objectname = \"" . $object . "\" AND visibility != 7 AND hasDrawing=1" );
		$totDraw = $run4->fetch ( PDO::FETCH_OBJ )->totDraw;
		if ($totDraw > 0) {
			$obj = preg_split ( "/ /", $object );
			$cat = $obj [0];
			$number = $obj [1];
			echo "  <td><a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&query=Submit+Query&seen=A&catalog=" . $cat . "&number=" . rawurlencode($number) . "&drawings=on\">" . $totDraw . "</a></td>";
		} else {
			echo "  <td>0</td>";
		}
		echo " </tr>";

		if ($loggedUser) {
			$run3 = $objDatabase->selectRecordset ( "SELECT COUNT(observations.id) As PersObsCnt, MAX(observations.date) As PersObsMaxDate FROM observations WHERE objectname = \"" . $object . "\" AND observerid = \"" . $loggedUser . "\" AND visibility != 7" );
			$get3 = $run3->fetch ( PDO::FETCH_OBJ );
			if ($get3->PersObsCnt > 0) {
				// The number of personal observations of this object.
				echo " <tr>";
				echo "  <td>" . LangPersonalObservations . "</td>";
				echo "  <td><a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&query=Submit+Query&seen=A&catalog=" . $cat . "&number=" . rawurlencode($number) . "&observer=" . $loggedUser . "\">" . $get3->PersObsCnt . "</a></td>";
				echo " </tr>";

				// The date of observer's last observation of this object.
				echo " <tr>";
				echo "  <td>" . LangLastPersonalObservation . "</td>";
				$date = $get3->PersObsMaxDate;
				$dateArray = sscanf ( $date, "%4d%2d%2d" );
				echo "  <td>" . date ( $dateformat, mktime ( 0, 0, 0, $dateArray [1], $dateArray [2], $dateArray [0] ) ) . "</td>";
				echo " </tr>";

				// The number of drawings the observer has made.
				echo " <tr>";
				echo "  <td>" . LangNumberOfPersonalDrawings . "</td>";
				$run4 = $objDatabase->selectRecordset ( "SELECT COUNT(observations.id) As PersObsCnt FROM observations WHERE objectname = \"" . $object . "\" AND observerid = \"" . $loggedUser . "\" AND visibility != 7 AND hasDrawing=1" );
				$persDrawings = $run4->fetch ( PDO::FETCH_OBJ )->PersObsCnt;
				if ($persDrawings > 0) {
					echo "  <td><a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&query=Search&seen=A&catalog=" . $cat . "&number=" . rawurlencode($number) . "&observer=" . $loggedUser . "&drawings=on\">" . $persDrawings . "</a></td>";
				} else {
					echo "<td>" . $persDrawings . "</td>";
				}
				echo " </tr>";
			}
		}
		echo "</table>";
		echo "</div>";
	}
	public function getPartOfs($objects) {
		global $objDatabase;
		$i = 0;
		$objectPartOfs = array ();
		while ( list ( $key, $value ) = each ( $objects ) ) {
			$objectsPartOfs [$key] = $value;
			$partofs = $objDatabase->selectSingleArray ( "SELECT objectname FROM objectpartof WHERE partofname=\"" . $value [1] . "\"", "objectname" );
			while ( list ( $key2, $value2 ) = each ( $partofs ) )
				$objectsPartOfs [$value2] = array (
						$i ++,
						$value2
				);
		}
		return $objectsPartOfs;
	}
	private function getSeenLastseenLink($object, &$seen, &$seenlink, &$lastseen, &$lastseenlink) {
		global $baseURL, $objDatabase, $loggedUser;
		$seen = "-";
		$seenlink = "<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $object ) . "\" title=\"" . LangObjectNSeen . "\" >-</a>";
		$lastseenlink = "-";
		$lastseenlink = "-";
		$ObsCnt = $objDatabase->selectSingleValue ( "SELECT COUNT(observations.id) As ObsCnt FROM observations WHERE objectname = \"" . $object . "\" AND visibility != 7 ", 'ObsCnt' );
		$DrwCnt = $objDatabase->selectSingleValue ( "SELECT COUNT(observations.id) As DrwCnt FROM observations WHERE objectname = \"" . $object . "\" AND visibility != 7 AND hasDrawing=1", 'DrwCnt' );
		if ($ObsCnt) {
			$seen = 'X' . ($DrwCnt ? 'S' : 'Z') . '(' . $ObsCnt . '/' . $DrwCnt . ')';
			$seenlink = "<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode ( $object ) . "\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . LangObjectXSeen . "\" >" . 'X' . ($DrwCnt ? 'S' : 'Z') . '(' . $ObsCnt . '/' . $DrwCnt . ')' . "</a>";
			if ($loggedUser) {
				$run3 = $objDatabase->selectRecordset ( "SELECT COUNT(observations.id) As PersObsCnt, MAX(observations.date) As PersObsMaxDate FROM observations WHERE objectname = \"" . $object . "\" AND observerid = \"" . $loggedUser . "\" AND visibility != 7" );
				$get3 = $run3->fetch ( PDO::FETCH_OBJ );
				if ($get3->PersObsCnt > 0) {
					$run4 = $objDatabase->selectRecordset ( "SELECT COUNT(observations.id) As PersObsCnt FROM observations WHERE objectname = \"" . $object . "\" AND observerid = \"" . $loggedUser . "\" AND visibility != 7 AND hasDrawing=1" );
					if ($run4->fetch ( PDO::FETCH_OBJ )->PersObsCnt > 0) {
						$seen = 'YD(' . $ObsCnt . '/' . $get3->PersObsCnt . ')';
						$seenlink = "<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode ( $object ) . "\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . LangObjectYDSeen . "\" >" . 'YD(' . $ObsCnt . '/' . $get3->PersObsCnt . ')' . "</a>";
					} else {
						$seen = 'Y' . ($DrwCnt ? 'S' : 'Z') . '(' . $ObsCnt . '/' . $get3->PersObsCnt . ')';
						$seenlink = "<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode ( $object ) . "\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . LangObjectYSeen . "\" >" . 'Y' . ($DrwCnt ? 'S' : 'Z') . '(' . $ObsCnt . '/' . $get3->PersObsCnt . ')' . "</a>";
					}
					$lastseen = $get3->PersObsMaxDate;
					$lastseenlink = "<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;observer=" . urlencode ( $loggedUser ) . "&amp;sort=observationdate&amp;sortdirection=desc&amp;object=" . urlencode ( $object ) . "\"  data-toggle=\"tooltip\" data-placement=\"bottom\"  title=\"" . LangObjectYSeen . "\" >" . $get3->PersObsMaxDate . "</a>";
				}
			}
		}
		return;
	}
	public function getSeenObjectDetails($obs, $seen = "A") {
		global $loggedUser, $objAtlas, $objLocation, $objDatabase, $objPresentations, $objObserver;
		$result2 = array ();
		$obscnt = sizeof ( $obs );
		if ($obscnt > 0) {
			$j = 0;
			reset ( $obs );
			while ( list ( $key, $value ) = each ( $obs ) ) {
				if (array_key_exists ( 'name', $value ))
					$object = $value ['name'];
				else
					$object = $value [1];
				$seentype = "-";
				$objectseen = '';
				$objectseenlink = '';
				$objectlastseen = '';
				$objectlastseenlink = '';
				$this->getSeenLastseenLink ( $object, $objectseen, $objectseenlink, $objectlastseen, $objectlastseenlink );
				if (($seen == "A") || (strlen ( $seen ) == 1 ? (strpos ( " " . $objectseen, substr ( $seen, 0, 1 ) )) : (strlen ( $seen ) == 2 ? strpos ( " " . $objectseen, substr ( $seen, 0, 1 ) ) || strpos ( " " . $objectseen, substr ( $seen, 1, 1 ) ) : (strpos ( " " . $objectseen, substr ( $seen, 0, 1 ) )) || (strpos ( " " . $objectseen, substr ( $seen, 1, 1 ) )) || (strpos ( " " . $objectseen, substr ( $seen, 2, 1 ) ))))) {
					$result2 [$j] ['objectname'] = $object;
					$get = $objDatabase->selectRecordset ( "SELECT * FROM objects WHERE name = \"" . $object . "\"" )->fetch ( PDO::FETCH_OBJ );
					$result2 [$j] ['objecttype'] = $get->type;
					$result2 [$j] ['objecttypefull'] = $GLOBALS [$get->type];
					$altnames = $this->getAlternativeNames ( $object );
					$alt = "";
					while ( list ( $keyaltnames, $valuealtnames ) = each ( $altnames ) )
						if (trim ( $valuealtnames ) != trim ( $object ))
							$alt .= ($alt ? "/" : "") . (trim ( $valuealtnames ));
					$result2 [$j] ['altname'] = $alt;
					$result2 [$j] ['objectconstellation'] = $get->con;
					$result2 [$j] ['objectconstellationfull'] = $GLOBALS [$get->con];
					$result2 [$j] ['objectseen'] = $objectseen;
					$result2 [$j] ['objectlastseen'] = $objectlastseen;
					$result2 [$j] ['objectseenlink'] = $objectseenlink;
					$result2 [$j] ['objectlastseenlink'] = $objectlastseenlink;
					if (is_numeric ( $key ))
						$result2 [$j] ['showname'] = $object;
					else
						$result2 [$j] ['showname'] = $key;
					$result2 [$j] ['objectmagnitude'] = ($get->mag == 99.9 ? '' : round ( $get->mag, 1 ));
					$result2 [$j] ['objectsurfacebrightness'] = ($get->subr == 99.9 ? '' : round ( $get->subr, 1 ));
					$result2 [$j] ['objectra'] = $get->ra;
					$result2 [$j] ['objectrahms'] = $objPresentations->raToStringHMS ( $result2 [$j] ['objectra'] );
					$result2 [$j] ['objectdecl'] = $get->decl;
					$result2 [$j] ['objectdecldms'] = $objPresentations->decToStringDegMinSec ( $result2 [$j] ['objectdecl'], 0 );
					$result2 [$j] ['objectradecl'] = $objPresentations->raToStringHMS ( $result2 [$j] ['objectra'] ) . ' ' . $objPresentations->decToStringDegMinSec ( $result2 [$j] ['objectdecl'], 0 );
					$result2 [$j] ['objectdiam1'] = $get->diam1;
					$result2 [$j] ['objectdiam2'] = $get->diam2;
					$result2 [$j] ['objectsize'] = $this->calculateSize ( $get->diam1, $get->diam2 );
					$result2 [$j] ['objectpa'] = $get->pa;
					$result2 [$j] ['objectsizepa'] = ($result2 [$j] ['objectsize'] ? ($result2 [$j] ['objectsize'] . '/' . $objPresentations->presentationInt ( $result2 [$j] ['objectpa'], 999, "-" )) : '-');
					if (array_key_exists ( 'name', $value ))
						$result2 [$j] ['objectpositioninlist'] = $j;
					else
						$result2 [$j] ['objectpositioninlist'] = $value [0];
					$result2 [$j] ['objectsbcalc'] = $get->SBObj;
					$result2 [$j] ['objectdescription'] = $get->description;
					if (is_array ( $value ) && count ( $value ) == 3)
						$result2 [$j] ['objectlistdescription'] = $value [2];
					reset ( $objAtlas->atlasCodes );
					while ( list ( $key, $value ) = each ( $objAtlas->atlasCodes ) )
						$result2 [$j] [$key] = $get->$key;
					$result2 [$j] ['objectcontrast'] = '-';
					$result2 [$j] ['objectcontrasttype'] = '-';
					$result2 [$j] ['objectcontrastpopup'] = '';
					$result2 [$j] ['objectoptimalmagnification'] = '-';

					$result2 [$j] ['objectmaxalt'] = "-";
					$result2 [$j] ['objectmaxaltstart'] = "-";
					$result2 [$j] ['objectmaxaltend'] = "-";

					if ($loggedUser && $objObserver->getObserverProperty ( $loggedUser, 'stdLocation' )) {
						$theLocation = $objObserver->getObserverProperty ( $loggedUser, 'stdLocation' );
						$longitude = 1.0 * $objLocation->getLocationPropertyFromId ( $theLocation, 'longitude' );
						$latitude = 1.0 * $objLocation->getLocationPropertyFromId ( $theLocation, 'latitude' );
						$timezone = $objLocation->getLocationPropertyFromId ( $theLocation, 'timezone' );
						$dateTimeZone = new DateTimeZone ( $timezone );
						$maxalt = '-';
						$maxaltstart = '-';
						$maxaltend = '-';
						for($i = 1; $i < 13; $i ++) {
							$datestr = sprintf ( "%02d", $i ) . "/" . sprintf ( "%02d", 1 ) . "/" . $_SESSION ['globalYear'];
							$dateTime = new DateTime ( $datestr, $dateTimeZone );
							$timedifference = $dateTimeZone->getOffset ( $dateTime );
							if (strncmp ( $timezone, "Etc/GMT", 7 ) == 0)
							$timedifference = - $timedifference;
							date_default_timezone_set ( "UTC" );
							$theTimeDifference1 [$i] = $timedifference;
							$theEphemerides1 [$i] = $this->getEphemerides ( $object, 1, $i, 2010 );
							$theNightEphemerides1 [$i] = date_sun_info ( strtotime ( "2010" . "-" . $i . "-" . "1" ), $latitude, $longitude );
							$datestr = sprintf ( "%02d", $i ) . "/" . sprintf ( "%02d", 1 ) . "/" . $_SESSION ['globalYear'];
							$dateTime = new DateTime ( $datestr, $dateTimeZone );
							$timedifference = $dateTimeZone->getOffset ( $dateTime );
							if (strncmp ( $timezone, "Etc/GMT", 7 ) == 0)
							$timedifference = - $timedifference;
							date_default_timezone_set ( "UTC" );
							$theTimeDifference15 [$i] = $timedifference;
							$theEphemerides15 [$i] = $this->getEphemerides ( $object, 15, $i, 2010 );
							$theNightEphemerides15 [$i] = date_sun_info ( strtotime ( "2010" . "-" . $i . "-" . "15" ), $latitude, $longitude );
						}
						for($i = 1; $i < 13; $i ++) {
							if ($i == 1) {
								if (($theEphemerides1 [$i] ['altitude'] != '-') && (($theEphemerides1 [$i] ['altitude'] == $theEphemerides15 [$i] ['altitude']) || ($theEphemerides1 [$i] ['altitude'] == $theEphemerides15 [12] ['altitude']))) {
									$maxalt = $theEphemerides1 [$i] ['altitude'];
									if ($theEphemerides1 [$i] ['altitude'] > $theEphemerides15 [12] ['altitude'])
									$maxaltstart = 0;
									else if ($theEphemerides1 [$i] ['altitude'] > $theEphemerides15 [$i] ['altitude'])
									$maxaltend = 0;
								}
							} else {
								if (($theEphemerides1 [$i] ['altitude'] != '-') && (($theEphemerides1 [$i] ['altitude'] == $theEphemerides15 [$i] ['altitude']) || ($theEphemerides1 [$i] ['altitude'] == $theEphemerides15 [$i - 1] ['altitude']))) {
									$maxalt = $theEphemerides1 [$i] ['altitude'];
									if ($theEphemerides1 [$i] ['altitude'] > $theEphemerides15 [$i - 1] ['altitude'])
									$maxaltstart = $i - 1;
									else if ($theEphemerides1 [$i] ['altitude'] > $theEphemerides15 [$i] ['altitude'])
									$maxaltend = $i - 1;
								}
							}
							if ($i == 12) {
								if (($theEphemerides15 [$i] ['altitude'] != '-') && (($theEphemerides15 [$i] ['altitude'] == $theEphemerides1 [$i] ['altitude']) || ($theEphemerides15 [$i] ['altitude'] == $theEphemerides1 [1] ['altitude']))) {
									$maxalt = $theEphemerides15 [$i] ['altitude'];
									if ($theEphemerides15 [$i] ['altitude'] > $theEphemerides1 [$i] ['altitude'])
									$maxaltstart = $i + .5 - 1;
									else if ($theEphemerides15 [$i] ['altitude'] > $theEphemerides1 [1] ['altitude'])
									$maxaltend = $i + .5 - 1;
								}
							} else {
								if (($theEphemerides15 [$i] ['altitude'] != '-') && (($theEphemerides15 [$i] ['altitude'] == $theEphemerides1 [$i] ['altitude']) || ($theEphemerides15 [$i] ['altitude'] == $theEphemerides1 [$i + 1] ['altitude']))) {
									$maxalt = $theEphemerides15 [$i] ['altitude'];
									if ($theEphemerides15 [$i] ['altitude'] > $theEphemerides1 [$i] ['altitude'])
									$maxaltstart = $i + .5 - 1;
									else if ($theEphemerides15 [$i] ['altitude'] > $theEphemerides1 [$i + 1] ['altitude'])
									$maxaltend = $i + .5 - 1;
								}
							}
						}
						$result2 [$j] ['objectmaxalt'] = $maxalt;
						$result2 [$j] ['objectmaxaltstart'] = '-';
						$result2 [$j] ['objectmaxaltend'] = '-';
						$result2 [$j] ['objectmaxaltmid'] = '-';
						$result2 [$j] ['objectmaxaltstarttext'] = "-";
						$result2 [$j] ['objectmaxaltmidtext'] = "-";
						$result2 [$j] ['objectmaxaltendtext'] = "-";
						if ($maxalt != '-') {
							$result2 [$j] ['objectmaxaltstart'] = 100 * ($maxaltstart + 1);
							$result2 [$j] ['objectmaxaltend'] = 100 * ($maxaltend + 1);
							if ($maxaltstart > $maxaltend)
								$maxaltmid = ($maxaltstart + 12 + $maxaltend) / 2;
							else
								$maxaltmid = ($maxaltstart + $maxaltend) / 2;
							if ($maxaltmid >= 12)
								$maxaltmid -= 12;
							$result2 [$j] ['objectmaxaltmid'] = 100 * ($maxaltmid + 1);
							$a = floor ( $maxaltstart + 1 );
							$b = $maxaltstart + 1 - $a;
							if ($b == 0.75)
								$c = LangMonthEnd;
							else if ($b == 0.5)
								$c = LangMonthMid;
							else if ($b == 0.25)
								$c = LangMonthStart;
							else
								$c = LangMonthTransit;
							$result2 [$j] ['objectmaxaltstarttext'] = $c . ' ' . $GLOBALS ['Month' . $a . 'Short'];
							$a = floor ( $maxaltend + 1 );
							$b = $maxaltend + 1 - $a;
							if ($b == 0.75)
								$c = LangMonthEnd;
							else if ($b == 0.5)
								$c = LangMonthMid;
							else if ($b == 0.25)
								$c = LangMonthStart;
							else
								$c = LangMonthTransit;
							$result2 [$j] ['objectmaxaltendtext'] = $c . ' ' . $GLOBALS ['Month' . $a . 'Short'];
							$a = floor ( $maxaltmid + 1 );
							$b = $maxaltmid + 1 - $a;
							if ($b == 0.75)
								$c = LangMonthEnd;
							else if ($b == 0.5)
								$c = LangMonthMid;
							else if ($b == 0.25)
								$c = LangMonthStart;
							else
								$c = LangMonthTransit;
							$result2 [$j] ['objectmaxaltmidtext'] = $c . ' ' . $GLOBALS ['Month' . $a . 'Short'];
						}
					}

					$j ++;
				}
			}
		}
		$obs = $this->getObjectVisibilities ( $result2 );
		return $obs;
	}
	private function getSize($name) // getSize returns the size of the object
{
		global $objDatabase;
		$sql = "SELECT diam1, diam2 FROM objects WHERE name = \"$name\"";
		$run = $objDatabase->selectRecordset ( $sql );
		$get = $run->fetch ( PDO::FETCH_OBJ );
		return $this->calculateSize ( $get->diam1, $get->diam2 );
	}
	public function newAltName($name, $cat, $catindex) {
		global $objDatabase;
		return $objDatabase->execSQL ( "INSERT INTO objectnames (objectname, catalog, catindex, altname) VALUES (\"$name\", \"$cat\", \"$catindex\", TRIM(CONCAT(\"$cat\", \" \", \"" . ucwords ( trim ( $catindex ) ) . "\")))" );
	}
	public function newName($name, $cat, $catindex) {
		$newname = trim ( $cat . " " . ucwords ( trim ( $catindex ) ) );
		$newcatindex = ucwords ( trim ( $catindex ) );
		global $objDatabase;
		$objDatabase->execSQL ( "UPDATE objects SET name=\"$newname\" WHERE name = \"$name\"" );
		$objDatabase->execSQL ( "UPDATE objectnames SET catalog=\"$cat\", catindex=\"$newcatindex\", altname=TRIM(CONCAT(\"$cat\", \" \", \"$newcatindex\")) WHERE objectname = \"$name\" AND altname = \"$name\"" );
		$objDatabase->execSQL ( "UPDATE objectnames SET objectname=\"$newname\" WHERE objectname = \"$name\"" );
		$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectshowname=\"$newname\" WHERE objectname = \"$name\"" );
		$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectname=\"$newname\" WHERE objectname = \"$name\"" );
		$objDatabase->execSQL ( "UPDATE observations SET objectname=\"$newname\" WHERE objectname = \"$name\"" );
		$objDatabase->execSQL ( "UPDATE objectpartof SET objectname=\"$newname\" WHERE objectname = \"$name\"" );
		$objDatabase->execSQL ( "UPDATE objectpartof SET partofname=\"$newname\" WHERE partofname = \"$name\"" );
	}
	public function newPartOf($name, $cat, $catindex) {
		global $objDatabase;
		return $objDatabase->execSQL ( "INSERT INTO objectpartof (objectname, partofname) VALUES (\"$name\", \"" . trim ( $cat . " " . ucwords ( trim ( $catindex ) ) ) . "\")" );
	}
	public function prepareObjectsContrast() // internal procedure to speed up contrast calculations
{
		global $objContrast, $loggedUser, $objDatabase;
		;
		if (! array_key_exists ( 'LTC', $_SESSION ) || (! $_SESSION ['LTC']))
			$_SESSION ['LTC'] = array (
					array (
							4,
							- 0.3769,
							- 1.8064,
							- 2.3368,
							- 2.4601,
							- 2.5469,
							- 2.5610,
							- 2.5660
					),
					array (
							5,
							- 0.3315,
							- 1.7747,
							- 2.3337,
							- 2.4608,
							- 2.5465,
							- 2.5607,
							- 2.5658
					),
					array (
							6,
							- 0.2682,
							- 1.7345,
							- 2.3310,
							- 2.4605,
							- 2.5467,
							- 2.5608,
							- 2.5658
					),
					array (
							7,
							- 0.1982,
							- 1.6851,
							- 2.3140,
							- 2.4572,
							- 2.5481,
							- 2.5615,
							- 2.5665
					),
					array (
							8,
							- 0.1238,
							- 1.6252,
							- 2.2791,
							- 2.4462,
							- 2.5463,
							- 2.5597,
							- 2.5646
					),
					array (
							9,
							- 0.0424,
							- 1.5529,
							- 2.2297,
							- 2.4214,
							- 2.5343,
							- 2.5501,
							- 2.5552
					),
					array (
							10,
							0.0498,
							- 1.4655,
							- 2.1659,
							- 2.3763,
							- 2.5047,
							- 2.5269,
							- 2.5333
					),
					array (
							11,
							0.1596,
							- 1.3581,
							- 2.0810,
							- 2.3036,
							- 2.4499,
							- 2.4823,
							- 2.4937
					),
					array (
							12,
							0.2934,
							- 1.2256,
							- 1.9674,
							- 2.1965,
							- 2.3631,
							- 2.4092,
							- 2.4318
					),
					array (
							13,
							0.4557,
							- 1.0673,
							- 1.8186,
							- 2.0531,
							- 2.2445,
							- 2.3083,
							- 2.3491
					),
					array (
							14,
							0.6500,
							- 0.8841,
							- 1.6292,
							- 1.8741,
							- 2.0989,
							- 2.1848,
							- 2.2505
					),
					array (
							15,
							0.8808,
							- 0.6687,
							- 1.3967,
							- 1.6611,
							- 1.9284,
							- 2.0411,
							- 2.1375
					),
					array (
							16,
							1.1558,
							- 0.3952,
							- 1.1264,
							- 1.4176,
							- 1.7300,
							- 1.8727,
							- 2.0034
					),
					array (
							17,
							1.4822,
							- 0.0419,
							- 0.8243,
							- 1.1475,
							- 1.5021,
							- 1.6768,
							- 1.8420
					),
					array (
							18,
							1.8559,
							0.3458,
							- 0.4924,
							- 0.8561,
							- 1.2661,
							- 1.4721,
							- 1.6624
					),
					array (
							19,
							2.2669,
							0.6960,
							- 0.1315,
							- 0.5510,
							- 1.0562,
							- 1.2892,
							- 1.4827
					),
					array (
							20,
							2.6760,
							1.0880,
							0.2060,
							- 0.3210,
							- 0.8800,
							- 1.1370,
							- 1.3620
					),
					array (
							21,
							2.7766,
							1.2065,
							0.3467,
							- 0.1377,
							- 0.7361,
							- 0.9964,
							- 1.2439
					),
					array (
							22,
							2.9304,
							1.3821,
							0.5353,
							0.0328,
							- 0.5605,
							- 0.8606,
							- 1.1187
					),
					array (
							23,
							3.1634,
							1.6107,
							0.7708,
							0.2531,
							- 0.3895,
							- 0.7030,
							- 0.9681
					),
					array (
							24,
							3.4643,
							1.9034,
							1.0338,
							0.4943,
							- 0.2033,
							- 0.5259,
							- 0.8288
					),
					array (
							25,
							3.8211,
							2.2564,
							1.3265,
							0.7605,
							0.0172,
							- 0.2992,
							- 0.6394
					),
					array (
							26,
							4.2210,
							2.6320,
							1.6990,
							1.1320,
							0.2860,
							- 0.0510,
							- 0.4080
					),
					array (
							27,
							4.6100,
							3.0660,
							2.1320,
							1.5850,
							0.6520,
							0.2410,
							- 0.1210
					)
			);

		if (! array_key_exists ( 'LTCSize', $_SESSION ) || (! $_SESSION ['LTCSize']))
			$_SESSION ['LTCSize'] = 24;
		if (! array_key_exists ( 'angleSize', $_SESSION ) || (! $_SESSION ['angleSize']))
			$_SESSION ['angleSize'] = 7;
		if (! array_key_exists ( 'angle', $_SESSION ) || (! $_SESSION ['angle']))
			$_SESSION ['angle'] = array (
					- 0.2255,
					0.5563,
					0.9859,
					1.260,
					1.742,
					2.083,
					2.556
			);
		$popup = "";
		$magnificationsName = '';
		$fov = '';
		if (! ($loggedUser))
			$popup = LangContrastNotLoggedIn;
		else {
			$sql5 = "SELECT stdlocation, stdtelescope from observers where id = \"" . $loggedUser . "\"";
			$run5 = $objDatabase->selectRecordset ( $sql5 );
			$get5 = $run5->fetch ( PDO::FETCH_OBJ );
			if ($get5->stdlocation == 0)
				$popup = LangContrastNoStandardLocation;
			elseif ($get5->stdtelescope == 0)
				$popup = LangContrastNoStandardInstrument;
			else { // Check for eyepieces or a fixed magnification
				$sql6 = "SELECT fixedMagnification, diameter, fd from instruments where id = \"" . $get5->stdtelescope . "\"";
				$run6 = $objDatabase->selectRecordset ( $sql6 );
				$get6 = $run6->fetch ( PDO::FETCH_OBJ );
				if ($get6->fd == 0 && $get6->fixedMagnification == 0) { // We are not setting $magnifications
					$magnifications = array ();
				} else if ($get6->fixedMagnification == 0) {
					$sql7 = "SELECT focalLength, name, apparentFOV, maxFocalLength from eyepieces where observer = \"" . $loggedUser . "\" AND eyepieceactive=1";
					$run7 = $objDatabase->selectRecordset ( $sql7 );
					while ( $get7 = $run7->fetch ( PDO::FETCH_OBJ ) ) {
						if ($get7->maxFocalLength > 0.0) {
							$fRange = $get7->maxFocalLength - $get7->focalLength;
							for($i = 0; $i < 5; $i ++) {
								$focalLengthEyepiece = $get7->focalLength + $i * $fRange / 5.0;
								$magnifications [] = $get6->diameter * $get6->fd / $focalLengthEyepiece;
								$magnificationsName [] = $get7->name . " - " . $focalLengthEyepiece . "mm";
								$fov [] = 1.0 / ($get6->diameter * $get6->fd / $focalLengthEyepiece) * 60.0 * $get7->apparentFOV;
							}
						} else {
							$magnifications [] = $get6->diameter * $get6->fd / $get7->focalLength;
							$magnificationsName [] = $get7->name;
							$fov [] = 1.0 / ($get6->diameter * $get6->fd / $get7->focalLength) * 60.0 * $get7->apparentFOV;
						}
					}
					$sql8 = "SELECT name, factor from lenses where observer = \"" . $loggedUser . "\"  AND lensactive=1";
					$run8 = $objDatabase->selectRecordset ( $sql8 );
					$origmagnifications = $magnifications;
					$origmagnificationsName = $magnificationsName;
					$origfov = $fov;
					while ( $get8 = $run8->fetch ( PDO::FETCH_OBJ ) ) {
						$name = $get8->name;
						$factor = $get8->factor;
						for($i = 0; $i < count ( $origmagnifications ); $i ++) {
							$magnifications [] = $origmagnifications [$i] * $factor;
							$magnificationsName [] = $origmagnificationsName [$i] . ", " . $name;
							$fov [] = $fov [$i] / $factor;
						}
					}
				} else {
					$magnifications [] = $get6->fixedMagnification;
					$magnificationsName [] = "";
					$fov [] = "";
				}
				$_SESSION ['magnifications'] = $magnifications;
				$_SESSION ['magnificationsName'] = $magnificationsName;
				$_SESSION ['fov'] = $fov;
				if (count ( $magnifications ) == 0) {
					$popup = LangContrastNoEyepiece;
				} else {
					$sql6 = "SELECT limitingMagnitude, skyBackground, name from locations where id = \"" . $get5->stdlocation . "\"";
					$run6 = $objDatabase->selectRecordset ( $sql6 );
					$get6 = $run6->fetch ( PDO::FETCH_OBJ );
					// Get the fst offset
					$sql6bis = "SELECT fstOffset from observers where id = \"" . $loggedUser . "\"";
					$run6bis = $objDatabase->selectRecordset ( $sql6bis );
					$get6bis = $run6bis->fetch ( PDO::FETCH_OBJ );
					$fstOffset = $get6bis->fstOffset;

					if (($get6->limitingMagnitude < - 900) && ($get6->skyBackground < - 900))
						$popup = LangContrastNoLimMag;
					else {
						if ($get6->skyBackground < - 900)
							$limmag = $get6->limitingMagnitude + $fstOffset;
						else {
							$limmag = $objContrast->calculateLimitingMagnitudeFromSkyBackground ( $get6->skyBackground ) + $fstOffset;
						}
						$sqm = $objContrast->calculateSkyBackgroundFromLimitingMagnitude ( $limmag );
						$_SESSION ['initBB'] = $sqm;

						$sql7 = "SELECT diameter, name from instruments where id = \"" . $get5->stdtelescope . "\"";
						$run7 = $objDatabase->selectRecordset ( $sql7 );
						$get7 = $run7->fetch ( PDO::FETCH_OBJ );
						$_SESSION ['aperMm'] = $get7->diameter;
						$_SESSION ['aperIn'] = $_SESSION ['aperMm'] / 25.4;
						// $scopeTrans = 0.8;
						// $pupil = 7.5;
						// $nakedEyeMag = 8.5;
						// Faintest star
						// $limitMag = $nakedEyeMag + 2.5 * log10( $_SESSION['aperMm'] * $_SESSION['aperMm'] * $scopeTrans / ($pupil * $pupil));
						// Minimum useful magnification
						$_SESSION ['minX'] = $_SESSION ['aperIn'] * 3.375 + 0.5;
						$_SESSION ['SBB1'] = $_SESSION ['initBB'] - (5 * log10 ( 2.833 * $_SESSION ['aperIn'] ));
						$_SESSION ['SBB2'] = - 2.5 * log10 ( (2.833 * $_SESSION ['aperIn']) * (2.833 * $_SESSION ['aperIn']) );
						$_SESSION ['telescope'] = $get7->name;
						$_SESSION ['location'] = $get6->name;
					}
				}
			}
		}
		return $popup;
	}
	public function removeAndReplaceObjectBy($name, $cat, $catindex) {
		$newname = trim ( $cat . " " . ucwords ( trim ( $catindex ) ) );
		$newcatindex = ucwords ( trim ( $catindex ) );
		global $objDatabase;
		$objDatabase->execSQL ( "UPDATE observations SET objectname=\"$newname\" WHERE objectname=\"$name\"" );
		$objDatabase->execSQL ( "UPDATE observations SET objectname=\"$newname\" WHERE objectname=\"$name\"" );
		$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectname=\"$newname\" WHERE objectname=\"$name\"" );
		$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectshowname=\"$newname\" WHERE objectname=\"$name\"" );
		$objDatabase->execSQL ( "DELETE objectnames.* FROM objectnames WHERE objectname = \"$name\"" );
		$objDatabase->execSQL ( "DELETE objectpartof.* FROM objectpartof WHERE objectname=\"$name\" OR partofname = \"$name\"" );
		$objDatabase->execSQL ( "DELETE objects.* FROM objects WHERE name = \"$name\"" );
	}
	public function removeAltName($name, $cat, $catindex) {
		global $objDatabase;
		return $objDatabase->execSQL ( "DELETE objectnames.* FROM objectnames WHERE objectname = \"$name\" AND catalog = \"$cat\" AND catindex=\"" . ucwords ( trim ( $catindex ) ) . "\"" );
	}
	public function removePartOf($name, $cat, $catindex) {
		global $objDatabase;
		return $objDatabase->execSQL ( "DELETE objectpartof.* FROM objectpartof WHERE objectname = \"$name\" AND partofname = \"" . trim ( $cat . " " . ucwords ( trim ( $catindex ) ) ) . "\"" );
	}
	public function setDsObjectAtlasPages($name) {
		global $objDatabase, $objAtlas;
		$result = $objDatabase->selectRecordArray ( "SELECT ra, decl FROM objects WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET urano          = \"" . $objAtlas->calculateAtlasPage ( 'urano', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET urano_new      = \"" . $objAtlas->calculateAtlasPage ( 'urano_new', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET sky            = \"" . $objAtlas->calculateAtlasPage ( 'sky', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET milleniumbase  = \"" . $objAtlas->calculateAtlasPage ( 'milleniumbase', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET taki           = \"" . $objAtlas->calculateAtlasPage ( 'taki', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET psa            = \"" . $objAtlas->calculateAtlasPage ( 'psa', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET torresB        = \"" . $objAtlas->calculateAtlasPage ( 'torresB', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET torresBC       = \"" . $objAtlas->calculateAtlasPage ( 'torresBC', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET torresC        = \"" . $objAtlas->calculateAtlasPage ( 'torresC', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET DSLDL          = \"" . $objAtlas->calculateAtlasPage ( 'DSLDL', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET DSLDP          = \"" . $objAtlas->calculateAtlasPage ( 'DSLDP', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET DSLLL          = \"" . $objAtlas->calculateAtlasPage ( 'DSLLL', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET DSLLP          = \"" . $objAtlas->calculateAtlasPage ( 'DSLLP', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET DSLOL          = \"" . $objAtlas->calculateAtlasPage ( 'DSLOL', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET DSLOP          = \"" . $objAtlas->calculateAtlasPage ( 'DSLOP', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET DeepskyHunter  = \"" . $objAtlas->calculateAtlasPage ( 'DeepskyHunter', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
		$objDatabase->execSQL ( "UPDATE objects SET Interstellarum = \"" . $objAtlas->calculateAtlasPage ( 'Interstellarum', $result ['ra'], $result ['decl'] ) . "\" WHERE name = \"" . $name . "\"" );
	}
	public function setDsObjectSBObj($name) {
		global $objDatabase, $objUtil;
		$result = $objDatabase->selectRecordArray ( "SELECT diam1, diam2, mag FROM objects WHERE name=\"" . $name . "\"" );
		if (($result ['mag'] != 99.9) && ($result ['mag'] != '') && (($result ['diam1'] != 0) || ($result ['diam2'] != 0))) {
			if (($result ['diam1'] != 0) && ($result ['diam2'] == 0))
				$result ['diam2'] = $result ['diam1'];
			elseif (($result ['diam2'] != 0) && ($result ['diam1'] == 0))
				$result ['diam1'] = $result ['diam2'];
			$SBObj = ($result ['mag'] + (2.5 * log10 ( 2827.0 * ($result ['diam1'] / 60) * ($result ['diam2'] / 60) )));
		} else
			$SBObj = - 999;
		$objDatabase->execSQL ( "UPDATE objects SET SBObj=\"" . $SBObj . "\" WHERE name=\"" . $name . "\";" );
	}
	public function setDsoProperty($name, $property, $propertyValue) // sets the property to the specified value for the given object
{
		global $objDatabase;
		return $objDatabase->execSQL ( "UPDATE objects SET " . $property . " = \"" . $propertyValue . "\" WHERE name = \"" . $name . "\"" );
		if (($property == "ra") || ($property == "decl"))
			$this->setDsObjectAtlasPages ( $name );
	}
	public function showObject($object) {
		global $theMonth, $theDay, $theYear, $objPresentations, $objLocation, $objAstroCalc, $objAtlas, $objContrast, $loggedUser, $baseURL, $objUtil, $objList, $listname, $myList, $baseURL, $objPresentations, $objObserver, $dateformat;
		$object = $this->getDsObjectName ( $object );
		$_SESSION ['object'] = $object;
		$altnames = $this->getAlternativeNames ( $object );
		$alt = "";
		while ( list ( $key, $value ) = each ( $altnames ) )
			if (trim ( $value ) != trim ( $object )) {
				$alt .= ($alt ? "/" : "") . (trim ( $value ));
			}
		$contains = $this->getContainsNames ( $object );
		$partof = $this->getPartOfNames ( $object );
		$containst = "";
		$partoft = "";
		$containstip = "";
		$partoftip = "";
		while ( list ( $key, $value ) = each ( $contains ) )
			if (trim ( $value ) != trim ( $object )) {
				$containst .= ($containst ? "/" : "") . "(<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( trim ( $value ) ) . "\">" . trim ( $value ) . "</a>)";
				$containstip .= ($containstip ? "/" : "") . addslashes ( trim ( $value ) );
			}
		while ( (count ( $partof )) && (list ( $key, $value ) = each ( $partof )) )
			if (trim ( $value ) != trim ( $object )) {
				$partoft .= ($partoft ? "/" : "") . "<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( trim ( $value ) ) . "\">" . trim ( $value ) . "</a>";
				$partoftip .= ($partoftip ? "/" : "") . trim ( $value );
			}
		$raDSS = $objPresentations->raToStringDSS ( $this->getDsoProperty ( $object, 'ra' ) );
		$declDSS = $objPresentations->decToStringDSS ( $this->getDsoProperty ( $object, 'decl' ) );
		$magnitude = sprintf ( "%01.1f", $this->getDsoProperty ( $object, 'mag' ) );
		$sb = sprintf ( "%01.1f", $this->getDSOProperty ( $object, 'subr' ) );
		$popup = $this->prepareObjectsContrast ();
		if ($popup) {
			$prefMagDetails = array (
					"-",
					""
			);
			$contype = '-';
			$contrast = '-';
		} else {
			$prefMagDetails = array (
					"-",
					""
			);
			$this->calcContrastAndVisibility ( $object, $object, $this->getDsoProperty ( $object, 'mag' ), $this->getDsoProperty ( $object, 'SBObj' ), $this->getDsoProperty ( $object, 'diam1' ), $this->getDsoProperty ( $object, 'diam2' ), $contrast, $contype, $popup, $prefMagDetails );
		}
		$prefMag = $prefMagDetails [0] . $prefMagDetails [1];
		echo "<form role=\"form\" action=\"" . $baseURL . "index.php?indexAction=detail_object\"><div>";
		echo "<input type=\"hidden\" name=\"indexAction\" value=\"detail_object\" />";
		echo "<input type=\"hidden\" name=\"object\" value=\"" . $object . "\" />";
		echo "<input type=\"hidden\" name=\"editListObjectDescription\" value=\"editListObjectDescription\"/>";
		echo "<table class=\"table table-condensed table-striped table-hover tablesorter custom-popup\">";
		echo "<tr>";
		echo "<td colspan=\"3\">" . LangViewObjectField1 . "</td>";
		echo "<td colspan=\"3\">" . "<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( stripslashes ( $object ) ) . "\">" . (stripslashes ( $object )) . "</a>" . "</td>";
		if ($loggedUser && ($standardAtlasCode = $objObserver->getObserverProperty ( $loggedUser, 'standardAtlasCode', 'urano' ))) {
			echo "<td colspan=\"3\"><span class=\"pull-right\">" . $objAtlas->atlasCodes [$standardAtlasCode] . LangViewObjectField10 . "</span></td>";
			echo "<td colspan=\"3\">" . $this->getDsoProperty ( $object, $standardAtlasCode ) . "</td>";
		} else {
			echo "<td colspan=\"3\">&nbsp;</td>";
			echo "<td colspan=\"3\">&nbsp;</td>";
		}
		echo "</tr>";

		echo "<tr>";
		echo "<td colspan=\"3\">" . LangViewObjectField3 . "</td>";
		echo "<td colspan=\"3\">" . $objPresentations->raToString ( $this->getDsoProperty ( $object, 'ra' ) ) . "</td>";
		echo "<td colspan=\"3\"><span class=\"pull-right\">" . LangViewObjectField4 . "</span></td>";
		echo "<td colspan=\"3\">" . $objPresentations->decToStringDegMinSec ( $this->getDsoProperty ( $object, 'decl' ) ) . "</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td colspan=\"3\">" . LangViewObjectField5 . "</td>";
		echo "<td colspan=\"3\">" . $GLOBALS [$this->getDsoProperty ( $object, 'con' )] . "</td>";
		echo "<td colspan=\"3\"><span class=\"pull-right\">" . LangViewObjectField6 . "</span></td>";
		echo "<td colspan=\"3\">" . $GLOBALS [$this->getDsoProperty ( $object, 'type' )] . "</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td colspan=\"3\">" . LangViewObjectField7 . "</td>";
		echo "<td colspan=\"3\">" . ((($magnitude == 99.9) || ($magnitude == "")) ? $magnitude = "-" : $magnitude) . "</td>";
		echo "<td colspan=\"3\"><span class=\"pull-right\">" . LangViewObjectField8 . "</span></td>";
		echo "<td colspan=\"3\">" . ((($sb == 99.9) || ($sb == "")) ? "-" : $sb) . "</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td colspan=\"3\">" . LangViewObjectField9 . "</td>";
		echo "<td colspan=\"3\">" . (($size = $this->getSize ( $object )) ? $size : "-") . "</td>";
		echo "<td colspan=\"3\"><span class=\"pull-right\">" . LangViewObjectField12 . "</span></td>";
		echo "<td colspan=\"3\">" . (($this->getDsoProperty ( $object, 'pa' ) != 999) ? ($this->getDsoProperty ( $object, 'pa' ) . "&deg;") : "-") . "</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td colspan=\"3\">" . LangViewObjectFieldContrastReserve . "</td>";
		echo '<td colspan="3">
					  <span class="' . $contype . '" data-toggle="tooltip" data-placement="bottom" title="' . $popup . '">' .
							$contrast .
						'</span>
					</td>';
		echo "<td colspan=\"3\"><span class=\"pull-right\">" . LangViewObjectFieldOptimumDetectionMagnification . "</span></td>";
		echo "<td colspan=\"3\">" . $prefMag . "</td>";
		echo "</tr>";

		if ($alt) {
			echo "<tr>";
			echo "<td colspan=\"3\">" . LangViewObjectField2 . "</td>";
			echo '<td colspan="9">' . $alt . '</td>';
			echo "</tr>";
		}

		if ($partoft || $containst) {
			echo "<tr>";
			echo "<td colspan=\"3\">" . LangViewObjectField2b . "</td>";
			echo '<td colspan="9">' .
							($containst ? $containst . "/" : "(-)/") . ($partoft ? $partoft : "-") . "</td>";
			echo "</tr>";
		}

		if ($listname && ($objList->checkObjectInMyActiveList ( $object ))) {
			echo "<tr>";
			echo "<td colspan=\"3\">" . LangViewObjectListDescription . ' (' . "<a href=\"" . DreyerDescriptionLink . "\" rel=\"external\">" . LangViewObjectDreyerDescription . "</a>)" . "</td>";
			if ($myList) {
				echo "<td colspan=\"9\">" . "<textarea name=\"description\" class=\"form-control\" onchange=\"submit()\">" . $objList->getListObjectDescription ( $object ) . "</textarea>" . "</td>";
			} else {
				echo "<td colspan=\"9\">" . $objList->getListObjectDescription ( $object ) . "</td>";
			}
			echo "</tr>";
		} elseif ($descriptionDsOject = $this->getDsoProperty ( $object, 'description' )) {
			echo "<tr>";
			echo "<td colspan=\"3\">" . LangViewObjectNGCDescription . ' (' . "<a href=\"" . DreyerDescriptionLink . "\" rel=\"external\">" . LangViewObjectDreyerDescription . "</a>" . ')' . "</td>";
			echo "<td colspan=\"9\">" . htmlentities ( $descriptionDsOject ) . "</td>";
			echo "</tr>";
		} else {
			echo "<tr>";
			echo "<td colspan=\"3\">" . LangViewObjectNGCDescription . ' (' . "<a href=\"" . DreyerDescriptionLink . "\" rel=\"external\">" . LangViewObjectDreyerDescription . "</a>" . ')' . "</td>";
			echo "<td colspan=\"9\">" . htmlentities ( $descriptionDsOject ) . "</td>";
			echo "</tr>";
		}

		$inlists = $objList->getInPrivateLists ( $object );
		if ($inlists != "") {
			echo "<tr>";
			echo "<td colspan=\"3\">" . LangInPrivateLists . "</td>";
			echo "<td colspan=\"9\">" . $inlists . "</td>";
			echo "</tr>";
		}

		$inlists = $objList->getInPublicLists ( $object );
		if ($inlists != "") {
			echo "<tr>";
			echo "<td colspan=\"3\">" . LangInPublicLists . "</td>";
			echo "<td colspan=\"9\">" . $inlists . "</td>";
			echo "</tr>";
		}

		if ($loggedUser && $objObserver->getObserverProperty ( $loggedUser, 'stdLocation' )) {
			$theYear = $_SESSION ['globalYear'];
			$theMonth = $_SESSION ['globalMonth'];
			$theDay = $_SESSION ['globalDay'];

			// 2) Get the julian day of today...
			$jd = gregoriantojd ( $theMonth, $theDay, $theYear );

			// 3) Get the standard location of the observer
			$longitude = $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdLocation' ), 'longitude' );
			$latitude = $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdLocation' ), 'latitude' );

			$timezone = $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdLocation' ), 'timezone' );

			$dateTimeZone = new DateTimeZone ( $timezone );
			$datestr = sprintf ( "%02d", $theMonth ) . "/" . sprintf ( "%02d", $theDay ) . "/" . $theYear;
			$dateTime = new DateTime ( $datestr, $dateTimeZone );
			// Geeft tijdsverschil terug in seconden
			$timedifference = $dateTimeZone->getOffset ( $dateTime );
			$timedifference = $timedifference / 3600.0;
			if (strncmp ( $timezone, "Etc/GMT", 7 ) == 0) {
				$timedifference = - $timedifference;
			}

			$ra = $this->getDsoProperty ( $object, 'ra' );
			$dec = $this->getDsoProperty ( $object, 'decl' );
			$ristraset = $objAstroCalc->calculateRiseTransitSettingTime ( $longitude, $latitude, $ra, $dec, $jd, $timedifference );

			$dateTimeText = date ( $dateformat, mktime ( 0, 0, 0, $theMonth, $theDay, $theYear ) );

			$location = $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdLocation' ), 'name' );

			if ($ristraset [0] == "-" && strncmp ( $ristraset [3], "-", 1 ) == 0) {
				$popup1 = $object . LangDoesntrise;
			} else if ($ristraset [0] == "-") {
				$popup1 = $object . LangCircumpolar;
			} else {
				$popup1 = $object . LangRise . $ristraset [0] . LangRistrasetOn . $dateTimeText . LangRistrasetIn . addslashes ( $location );
			}
			$popup2 = $object . LangTransitPopup . $ristraset [1] . LangRistrasetOn . $dateTimeText . LangRistrasetIn . addslashes ( $location );
			if ($ristraset [2] == "-" && strncmp ( $ristraset [3], "-", 1 ) == 0) {
				$popup3 = $object . LangDoesntrise;
			} else if ($ristraset [2] == "-") {
				$popup3 = $object . LangCircumpolar;
			} else {
				$popup3 = $object . LangSet . $ristraset [2] . LangRistrasetOn . $dateTimeText . LangRistrasetIn . addslashes ( $location );
			}
			if ($ristraset [3] == "-") {
				$popup4 = $object . LangDoesntrise;
			} else {
				$popup4 = $object . LangAltitude . $ristraset [3] . LangRistrasetIn . addslashes ( $location );
			}

			echo "<tr>";
			echo "<td>" . LangDate . "</td>";
			echo "<td>" . date ( $dateformat, mktime ( 0, 0, 0, $theMonth, $theDay, $theYear ) ) . "</td>";
			echo "<td>" . LangMoonRise . "</td>";

			echo '<td>
							<span data-toggle="tooltip" data-placement="bottom" title="' . $popup1 . '">' . $ristraset [0] . '</span>
						</td>';
			echo "<td>" . LangTransit . "</td>";
			echo '<td>
							<span data-toggle="tooltip" data-placement="bottom" title="' . $popup2 . '">' . $ristraset [1] . '</span>
						</td>';
			echo "<td>" . LangMoonSet . "</td>";
			echo '<td>
							<span data-toggle="tooltip" data-placement="bottom" title="' . $popup3 . '">' . $ristraset [2] . '</span>
						</td>';
			echo "<td>" . LangBest . "</td>";
			echo "<td>" . $ristraset [4] . "</td>";
			echo "<td>" . LangMaxAltitude . "</td>";
			echo '<td>
							<span data-toggle="tooltip" data-placement="bottom" title="' . $popup4 . '">' . $ristraset [3] . '</span>
						</td>';
			echo "</tr>";
		}
		echo "</table>";
		echo "</div></form>";
		echo "<hr />";
		echo '<script>
						$(document).ready(function(){
    					$(\'[data-toggle="tooltip"]\').tooltip();
						});
					</script>';
	}
	public function getEphemerides($theObject, $theDay, $theMonth, $theYear, $theLocation = "") {
		global $objAstroCalc, $objObserver, $loggedUser, $objLocation;
		$thejd = gregoriantojd ( $theMonth, $theDay, $theYear );
		if (! ($theLocation))
			$theLocation = $objObserver->getObserverProperty ( $loggedUser, 'stdLocation' );
		$longitude = $objLocation->getLocationPropertyFromId ( $theLocation, 'longitude' );
		$latitude = $objLocation->getLocationPropertyFromId ( $theLocation, 'latitude' );
		$timezone = $objLocation->getLocationPropertyFromId ( $theLocation, 'timezone' );
		$dateTimeZone = new DateTimeZone ( $timezone );
		$datestr = sprintf ( "%02d", $theMonth ) . "/" . sprintf ( "%02d", $theDay ) . "/" . $theYear;
		$dateTime = new DateTime ( $datestr, $dateTimeZone );
		// Geeft tijdsverschil terug in seconden
		$timedifference = $dateTimeZone->getOffset ( $dateTime );
		$timedifference = $timedifference / 3600.0;
		if (strncmp ( $timezone, "Etc/GMT", 7 ) == 0) {
			$timedifference = - $timedifference;
		}
		$ra = $this->getDsoProperty ( $theObject, 'ra' );
		$dec = $this->getDsoProperty ( $theObject, 'decl' );
		$ristraset = $objAstroCalc->calculateRiseTransitSettingTime ( $longitude, $latitude, $ra, $dec, $thejd, $timedifference );
		$theEphemerides ['rise'] = $ristraset [0];
		$theEphemerides ['transit'] = $ristraset [1];
		$theEphemerides ['set'] = $ristraset [2];
		$theEphemerides ['altitude'] = $ristraset [3];
		return $theEphemerides;
	}
	public function showObjects($link, $ownShow = '', $showRank = 0, $pageListAction = "addAllObjectsFromPageToList", $columnSource = "", $observingList = false)
	// ownShow => object to show in a different color (type3) in the list showRank = 0 for normal operation, 1 for List show, 2 for top objects
	{
		global $objFormLayout, $objAtlas, $objObserver, $objLocation, $myList, $listname, $listname_ss, $loggedUser, $baseURL, $objUtil, $objPresentations, $objList;
		$atlas = '';
		$c = 0;

		// Add the button to select which columns to show
		$objUtil->addTableColumSelector ();

		$nameLocation = 0;
		if ($loggedUser) {
			if ($showRank) {
				$nameLocation = 0;
			} else {
				if (($myList) && ($pageListAction == "addAllObjectsFromPageToList"))
					$nameLocation ++;
				elseif (($myList) && ($pageListAction == "removePageObjectsFromList"))
					$nameLocation ++;
				elseif ($myList)
					$nameLocation ++;
			}
		}

		if ($observingList) {
			echo "  <a class=\"btn btn-success\" href=\"" . $link . "&amp;noShowName=noShowName\">" . LangListQueryObjectsMessage17 . "</a>";
		}
		echo "<table class=\"table sort-tablenearobjectlist table-condensed table-striped table-hover custom-popup\"  data-sortlist=\"[[" . $nameLocation . ",0]]\">";
		echo "<thead>";
		echo "<tr>";
		if ($showRank)
			echo "<th id=\"objectpositioninlist\">#</th>";
		if ($loggedUser) {
			if (($myList) && ($pageListAction == "addAllObjectsFromPageToList"))
				echo ("<th data-priority=\"1\" class=\"filter-false columnSelector-disable\" data-sorter=\"false\"><a href=\"" . $link . "&amp;addAllObjectsFromPageToList=true\" title=\"" . LangListQueryObjectsMessage1 . $listname_ss . "\">&nbsp;P&nbsp;</a></td>");
			elseif (($myList) && ($pageListAction == "removePageObjectsFromList"))
				echo ("<th data-priority=\"1\" class=\"filter-false columnSelector-disable\" data-sorter=\"false\"><a href=\"" . $link . "&amp;removePageObjectsFromList=true\" title=\"" . LangListQueryObjectsMessage1b . $listname_ss . "\">&nbsp;&nbsp;</a></td>");
			elseif ($myList)
				echo ("<th data-priority=\"1\" class=\"filter-false columnSelector-disable\" data-sorter=\"false\">" . LangList . "</td>");
		}

		echo "<th data-priority=\"critical\" id=\"showname\">" . LangOverviewObjectsHeader1 . "</th>";
		echo "<th data-priority=\"5\" id=\"objectconstellationfull\">" . LangOverviewObjectsHeader2 . "</th>";
		echo "<th data-priority=\"9\" class=\"columnSelector-false\" id=\"objectconstellationfull\">" . LangOverviewObjectsHeader2Short . "</th>";
		echo "<th data-priority=\"7\" id=\"objectmagnitude\">" . LangOverviewObjectsHeader3 . "</th>";
		echo "<th data-priority=\"7\" class=\"columnSelector-false\"  id=\"objectsurfacebrightness\">" . LangOverviewObjectsHeader3b . "</th>";
		echo "<th data-priority=\"6\" id=\"objecttypefull\">" . LangOverviewObjectsHeader4 . "</th>";
		echo "<th data-priority=\"9\" class=\"columnSelector-false\" id=\"objecttypefull\">" . LangOverviewObjectsHeader4Short . "</th>";
		echo "<th data-priority=\"6\" class=\"columnSelector-false\" id=\"objectsizepa\">" . LangOverviewObjectsHeader10 . "</th>";
		echo "<th data-priority=\"6\" class=\"columnSelector-false\" id=\"objectradecl\">" . LangOverviewObjectsHeader5 . "</th>";
		echo "<th data-priority=\"6\" id=\"objectdecl\" class=\"columnSelector-false sorter-digit\">" . LangOverviewObjectsHeader6 . "</th>";
		if ($loggedUser) {
			$atlas = $objObserver->getObserverProperty ( $loggedUser, 'standardAtlasCode', 'urano' );
			echo "<th data-priority=\"6\" id=\"" . $atlas . "\">" . $objAtlas->atlasCodes [$atlas] . "</th>";
			echo "<th data-priority=\"7\" id=\"objectcontrast\">" . LangViewObjectFieldContrastReserve . "</th>";
			echo "<th data-priority=\"6\" id=\"objectoptimalmagnification\">" . LangViewObjectFieldMagnificationHeader . "</th>";
			echo "<th data-priority=\"6\" id=\"objectriseorder\" class=\"columnSelector-false sorter-astrotime\">" . LangMoonRise . "</th>";
			echo "<th data-priority=\"6\" id=\"objecttransitorder\" class=\"columnSelector-false sorter-astrotime\">" . LangTransit . "</th>";
			echo "<th data-priority=\"6\" id=\"objectsetorder\" class=\"columnSelector-false sorter-astrotime\">" . LangMoonSet . "</th>";
			echo "<th data-priority=\"5\" id=\"objectbestorder\" class=\"sorter-astrotime\">" . LangBest . "</th>";
			echo "<th data-priority=\"6\" id=\"objectmaxaltitude\" class=\"string-max sorter-degrees\">" . LangMaxAltitude . "</th>";
			echo "<th data-priority=\"3\" id=\"objectseen\">" . LangOverviewObjectsHeader7 . "</th>";
			echo "<th data-priority=\"4\" id=\"objectlastseen\">" . LangOverviewObjectsHeader8 . "</th>";
		}
		if ($loggedUser && $objObserver->getObserverProperty ( $loggedUser, 'stdLocation' )) {
			echo "<th data-priority=\"6\" id=\"objectmaxalt\" class=\"sorter-degrees\">" . LangObjectHighestAlt . "</th>";
			echo "<th data-priority=\"6\" id=\"objectmaxaltstart\" class=\"columnSelector-false sorter-months\">" . LangObjectHighestFrom . "</th>";
			echo "<th data-priority=\"6\" id=\"objectmaxaltend\" class=\"columnSelector-false sorter-months\">" . LangObjectHighestTo . "</th>";
			echo "<th data-priority=\"7\" id=\"objectmaxaltmid\" class=\"sorter-months\">" . LangObjectHighestAround . "</th>";
		}
		echo "</tr>";
		echo "</thead><tbody>";
		$filteron = $objUtil->checkRequestKey ( 'filteron' );
		$filteron1 = $objUtil->checkRequestKey ( 'filteron1' );
		$locationdecl = 0;
		if ($filteron == 'location') {
			if ($loggedUser && ($location = $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' )))
				$locationdecl = $objLocation->getLocationPropertyFromId ( $location, 'latitude', 0 );
		}
		$count = 0;
		while ( $count < sizeof ( $_SESSION ['Qobj'] ) ) {
			$c = 0;
			$specialclass = "";
			if (($filteron == 'location') && ((($_SESSION ['Qobj'] [$count] ['objectdecl'] + 90.0) < $locationdecl) || (($_SESSION ['Qobj'] [$count] ['objectdecl'] - 90.0) > $locationdecl)))
				$specialclass = "strikethrough";
			if (($filteron1 == 'time') && ($_SESSION ['Qobj'] [$count] ['objectmaxaltitude'] == "-"))
				$specialclass = "strikethrough";

			echo "<tr " . (($_SESSION ['Qobj'] [$count] ['objectname'] == $ownShow) ? "class=\"type3\"" : "") . ">";
			if (($showRank == 1) && $myList)
				echo "<td><a href=\"#\" data-toggle=\"tooltip\" data-placement=\"bottom\" onclick=\"theplace = prompt('" . LangNewPlaceInList . "','" . $_SESSION ['Qobj'] [$count] ['objectpositioninlist'] . "'); location.href='" . $link . "&amp;ObjectFromPlaceInList=" . $_SESSION ['Qobj'] [$count] ['objectpositioninlist'] . "&amp;ObjectToPlaceInList='+theplace; return false;\" title=\"" . LangToListMoved6 . "\">" . $_SESSION ['Qobj'] [$count] ['objectpositioninlist'] . "</a></td>";
			elseif ($showRank)
				echo "<td>" . $_SESSION ['Qobj'] [$count] ['objectpositioninlist'] . "</td>";
			if ($myList) {
				echo ("<td>");
				if ($objList->checkObjectInMyActiveList ( $_SESSION ['Qobj'] [$count] ['objectname'] )) {
					echo "<a href=\"" . $link . "&amp;removeObjectFromList=" . urlencode ( $_SESSION ['Qobj'] [$count] ['objectname'] ) . "&amp;sort=" . $objUtil->checkGetKey ( 'sort' ) . "&amp;previous=" . $objUtil->checkGetKey ( 'previous' ) . "\"  data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . $_SESSION ['Qobj'] [$count] ['objectname'] . LangListQueryObjectsMessage3 . $listname_ss . "\">
							<span class=\"glyphicon glyphicon-trash\" aria-hidden=\"true\"></span>
							</a>";
				} else {
					echo "<a href=\"" . $link . "&amp;addObjectToList=" . urlencode ( $_SESSION ['Qobj'] [$count] ['objectname'] ) . "&amp;showname=" . urlencode ( $_SESSION ['Qobj'] [$count] ['showname'] ) . "&amp;sort=" . $objUtil->checkGetKey ( 'sort' ) . "&amp;previous=" . $objUtil->checkGetKey ( 'previous' ) . "\"  data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . $_SESSION ['Qobj'] [$count] ['objectname'] . LangListQueryObjectsMessage2 . $listname_ss . "\">
							<span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span>
							</a>";
				}
				echo "</td>";
			}
			echo "<td class=\"" . $specialclass . "\"><a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_SESSION ['Qobj'] [$count] ['objectname'] ) . "\" >" . $_SESSION ['Qobj'] [$count] ['showname'] . "</a></td>";
			echo "<td>" . $GLOBALS [$_SESSION ['Qobj'] [$count] ['objectconstellation']] . "</td>";
			echo "<td><span data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . LangOverviewObjectsHeader2 . ": " . $GLOBALS [$_SESSION ['Qobj'] [$count] ['objectconstellation']] . "\">" . $_SESSION ['Qobj'] [$count] ['objectconstellation'] . "</span></td>";
			echo "<td>" . (($_SESSION ['Qobj'] [$count] ['objectmagnitude'] == 99.9) || ($_SESSION ['Qobj'] [$count] ['objectmagnitude'] == '') ? "&nbsp;&nbsp;-&nbsp;" : sprintf ( "%01.1f", $_SESSION ['Qobj'] [$count] ['objectmagnitude'] )) . "</td>";
			echo "<td>" . (($_SESSION ['Qobj'] [$count] ['objectsurfacebrightness'] == 99.9) || ($_SESSION ['Qobj'] [$count] ['objectsurfacebrightness'] == '') ? "&nbsp;&nbsp;-&nbsp;" : sprintf ( "%01.1f", $_SESSION ['Qobj'] [$count] ['objectsurfacebrightness'] )) . "</td>";
			echo "<td>" . $GLOBALS [$_SESSION ['Qobj'] [$count] ['objecttype']] . "</td>";
			echo "<td><span data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . LangOverviewObjectsHeader4 . ": " . $GLOBALS [$_SESSION ['Qobj'] [$count] ['objecttype']] . "\">" . $_SESSION ['Qobj'] [$count] ['objecttype'] . "</span></td>";
			echo "<td>" . $_SESSION ['Qobj'] [$count] ['objectsizepa'] . "</td>";
			echo "<td>" . $_SESSION ['Qobj'] [$count] ['objectrahms'] . "</td>";
			echo "<td>" . $_SESSION ['Qobj'] [$count] ['objectdecldms'] . "</td>";
			if ($loggedUser) {
				$page = $_SESSION ['Qobj'] [$count] [$atlas];
				if (substr ( $_SESSION ['Qobj'] [$count] ['objectseen'], 0, 2 ) == "YD")
					$seenclass = "seenYD";
				elseif (substr ( $_SESSION ['Qobj'] [$count] ['objectseen'], 0, 1 ) == "Y")
					$seenclass = "seenY";
				elseif (substr ( $_SESSION ['Qobj'] [$count] ['objectseen'], 0, 1 ) == "X")
					$seenclass = "seenX";
				else
					$seenclass = "seenN";
				echo "<td>" . $page . "</td>";

				$contrast = $_SESSION ['Qobj'] [$count] ['objectcontrast'];
				if (strcmp($_SESSION ['Qobj'] [$count] ['objectcontrasttype'], "") === 0) {
					$contrast = "-";
				}

				echo "<td><span data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . $_SESSION ['Qobj'] [$count] ['objectcontrastpopup'] . "\" class=\"" . $_SESSION ['Qobj'] [$count] ['objectcontrasttype'] . "\" >" . $contrast . "</span></td>";
				echo "<td><span data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . LangViewObjectFieldMagnification . ": " . $_SESSION ['Qobj'] [$count] ['objectoptimalmagnification'] . "\">" . $_SESSION ['Qobj'] [$count] ['objectoptimalmagnificationvalue'] . "</span></td>";
				echo "<td><span data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . $_SESSION ['Qobj'] [$count] ['objectrisepopup'] . "\">" . $_SESSION ['Qobj'] [$count] ['objectrise'] . "</span></td>";
				echo "<td><span data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . $_SESSION ['Qobj'] [$count] ['objecttransitpopup'] . "\">" . $_SESSION ['Qobj'] [$count] ['objecttransit'] . "</span></td>";
				echo "<td><span data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . $_SESSION ['Qobj'] [$count] ['objectsetpopup'] . "\">" . $_SESSION ['Qobj'] [$count] ['objectset'] . "</span></td>";
				echo "<td><span data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . LangBest . ": " . $_SESSION ['Qobj'] [$count] ['objectbest'] . "\">" . $_SESSION ['Qobj'] [$count] ['objectbest'] . "</span></td>";
				echo "<td><span data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . $_SESSION ['Qobj'] [$count] ['objectmaxaltitudepopup'] . "\">" . $_SESSION ['Qobj'] [$count] ['objectmaxaltitude'] . "<span></td>";
				echo "<td><span class=\"" . $seenclass . "\">" . $_SESSION ['Qobj'] [$count] ['objectseenlink'] . "</span></td>";
				echo "<td><span class=\"" . $seenclass . "\">" . $_SESSION ['Qobj'] [$count] ['objectlastseenlink'] . "</span></td>";
			}
			if ($loggedUser && $objObserver->getObserverProperty ( $loggedUser, 'stdLocation' )) {
				echo "<td>" . $_SESSION ['Qobj'] [$count] ['objectmaxalt'] . "</td>";
				echo "<td>" . $_SESSION ['Qobj'] [$count] ['objectmaxaltstarttext'] . "</td>";
				echo "<td>" . $_SESSION ['Qobj'] [$count] ['objectmaxaltendtext'] . "</td>";
				echo "<td>" . $_SESSION ['Qobj'] [$count] ['objectmaxaltmidtext'] . "</td>";
			}
			echo "</tr>";

			$count ++;
		}
		echo "</tbody></table>";
		echo '<script>
						$(document).ready(function(){
    					$(\'[data-toggle="tooltip"]\').tooltip();
						});
					</script>';
		$objUtil->addPager ( "nearobjectlist", $count );

		if ($loggedUser) {
			$content1 = LangObjectsFilter . ": <a href=\"" . (($objUtil->checkRequestKey ( 'filteron' ) == 'location') ? $objUtil->removeFromLink ( $link, 'filteron=location' ) . "\" title=\"" . LangObjectsFilterLocationOffExpl . "\"" : $link . "&amp;filteron=location" . "\" title=\"" . LangObjectsFilterLocationExpl . "\"") . " class=\"btn btn-primary\">" . LangObjectsFilterLocation . "</a>" . "&nbsp;";
			$content1 .= "<a href=\"" . (($objUtil->checkRequestKey ( 'filteron1' ) == 'time') ? $objUtil->removeFromLink ( $link, 'filteron1=time' ) . "\" title=\"" . LangObjectsFilterDateTimeOffExpl . "\"" : $link . "&amp;filteron1=time" . "\" title=\"" . LangObjectsFilterDateTimeExpl . "\"") . " class=\"btn btn-primary\">" . LangObjectsFilterDateTime . "</a>";

			$content = $objPresentations->promptWithLinkText ( LangListQueryObjectsMessage14, LangListQueryObjectsMessage15, $baseURL . "objects.pdf.php?SID=Qobj", LangExecuteQueryObjectsMessage4a ) . "&nbsp;";
			$content .= $objPresentations->promptWithLinkText ( LangListQueryObjectsMessage14, LangListQueryObjectsMessage15, $baseURL . "objectnames.pdf.php?SID=Qobj", LangExecuteQueryObjectsMessage4b ) . "&nbsp;";
			$content .= $objPresentations->promptWithLinkText ( LangListQueryObjectsMessage14, LangListQueryObjectsMessage15, $baseURL . "objectsDetails.pdf.php?SID=Qobj", LangExecuteQueryObjectsMessage4c ) . "&nbsp;";
			$content .= "<a href=\"" . $baseURL . "objects.argo?SID=Qobj\" class=\"btn btn-primary\"><span class=\"glyphicon glyphicon-download\"></span> " . LangExecuteQueryObjectsMessage8 . "</a>&nbsp;";
			$content .= "<a href=\"" . $baseURL . "objects.csv?SID=Qobj\" class=\"btn btn-primary\"><span class=\"glyphicon glyphicon-download\"></span> " . LangExecuteQueryObjectsMessage6 . "</a>";

			$content .= "&nbsp;<a href=\"" . $baseURL . "index.php?indexAction=reportsLayout&amp;reportname=ReportQueryOfObjects&amp;reporttitle=ReportQueryOfObjects&amp;SID=Qobj&amp;pdfTitle=Test\" class=\"btn btn-primary\"><span class=\"glyphicon glyphicon-download\"></span> " . ReportLink . "</a>&nbsp;";
			$content .= "<a href=\"" . $baseURL . "index.php?indexAction=objectsSets" . "\" rel=\"external\" class=\"btn btn-primary\"><span class=\"glyphicon glyphicon-download\"></span> " . LangExecuteQueryObjectsMessage11 . "</a>";

			echo $content1 . "<br /><br />";
			echo $content;
		}
	}
	public function showObjectsFields($link, $min, $max, $ownShow = '', $showRank = 0, $fields = array("showname","objectconstellation","objectmagnitude"), $pageListAction = "addAllObjectsFromPageToList") // ownShow => object to show in a different color (type3) in the list showRank = 0 for normal operation, 1 for List show, 2 for top objects
{
		global $objAtlas, $objObserver, $myList, $listname, $listname_ss, $loggedUser, $baseURL, $objUtil, $objPresentations, $objList;
		$atlas = '';
		echo "<table class=\"table sort-table table-condensed table-striped table-hover tablesorter custom-popup\">";
		echo "<thead>";
		echo "<tr>";
		while ( list ( $key, $value ) = each ( $fields ) ) {
			if ($value == "showrank")
				echo "<th>" . LangOverviewObjectsHeader9 . "</th>";
			if ($value == "showownrank")
				echo "<th>" . LangOverviewObjectsHeader9 . "</th>";
			if ($value == "showname")
				echo "<th>" . LangOverviewObjectsHeader1 . "</th>";
			if ($value == "objectconstellation")
				echo "<th>" . LangOverviewObjectsHeader2 . "</th>";
			if ($value == "objectmagnitude")
				echo "<th>" . LangOverviewObjectsHeader3 . "</th>";
			if ($value == "objectsurfacebrightness")
				echo "<th>" . LangOverviewObjectsHeader3b . "</th>";
			if ($value == "objectra")
				echo "<th>" . LangOverviewObjectsHeader5 . "</th>";
			if ($value == "objectdecl")
				echo "<th>" . LangOverviewObjectsHeader6 . "</th>";
			if ($value == "objectdiam")
				echo "<th>" . LangOverviewObjectsHeader10 . "</th>";
			if ($value == "objecttype")
				echo "<th>" . LangOverviewObjectsHeader4 . "</th>";
		}
		echo "</tr>";
		echo "</thead>";

		$count = 0;
		if ($max > count ( $_SESSION ['Qobj'] ))
			$max = count ( $_SESSION ['Qobj'] );
		while ( $max && ($count < $max) ) {
			echo "<tr>";
			reset ( $fields );
			while ( list ( $key, $value ) = each ( $fields ) ) {
				if (($value == "showownrank") && $myList)
					echo "<td><a href=\"#\" onclick=\"theplace = prompt('" . LangNewPlaceInList . "','" . $_SESSION ['Qobj'] [$count] ['objectpositioninlist'] . "'); location.href='" . $link . "&amp;ObjectFromPlaceInList=" . $_SESSION ['Qobj'] [$count] ['objectpositioninlist'] . "&amp;ObjectToPlaceInList='+theplace+'&amp;min=" . $min . "'; return false;\" title=\"" . LangToListMoved6 . "\">" . $_SESSION ['Qobj'] [$count] ['objectpositioninlist'] . "</a></td>";
				if ($value == "showrank")
					echo "<td>" . $_SESSION ['Qobj'] [$count] ['objectpositioninlist'] . "</td>";
				if ($value == "showname")
					echo "<td><a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_SESSION ['Qobj'] [$count] ['objectname'] ) . "\" >" . $_SESSION ['Qobj'] [$count] ['showname'] . "</a></td>";
				if ($value == "objectconstellation")
					echo "<td>" . $GLOBALS [$_SESSION ['Qobj'] [$count] ['objectconstellation']] . "</td>";
				if ($value == "objectmagnitude")
					echo "<td>" . (($_SESSION ['Qobj'] [$count] ['objectmagnitude'] == 99.9) || ($_SESSION ['Qobj'] [$count] ['objectmagnitude'] == 99.9) ? "&nbsp;&nbsp;-&nbsp;" : sprintf ( "%01.1f", $_SESSION ['Qobj'] [$count] ['objectmagnitude'] )) . "</td>";
				if ($value == "objectsurfacebrightness")
					echo "<td>" . (($_SESSION ['Qobj'] [$count] ['objectsurfacebrightness'] == 99.9) || ($_SESSION ['Qobj'] [$count] ['objectsurfacebrightness'] == '') ? "&nbsp;&nbsp;-&nbsp;" : sprintf ( "%01.1f", $_SESSION ['Qobj'] [$count] ['objectsurfacebrightness'] )) . "</td>";
				if ($value == "objecttype")
					echo "<td>" . $GLOBALS [$_SESSION ['Qobj'] [$count] ['objecttype']] . "</td>";
				if ($value == "objectra")
					echo "<td>" . $objPresentations->raToString ( $_SESSION ['Qobj'] [$count] ['objectra'] ) . "</td>";
				if ($value == "objectdecl")
					echo "<td>" . $objPresentations->decToStringDegMin ( $_SESSION ['Qobj'] [$count] ['objectdecl'] ) . "</td>";
				if ($value == "objectdiam")
					echo "<td>" . $this->getSize ( $_SESSION ['Qobj'] [$count] ['objectname'] ) . "</td>";
			}
			echo ("</tr>");
			$count ++;
		}
		echo "</table>";

		$objUtil->addPager ( "", $count );
	}
	public function validateObject() // checks if the add new object form is correctly filled in and eventually adds the object to the database
{
		global $objUtil, $objObject, $objObserver, $entryMessage, $loggedUser, $developversion, $mailTo, $mailFrom, $objMessage;
		if (! ($loggedUser))
			new Exception ( LangException002c );
		if ($objUtil->checkPostKey ( 'newobject' )) {
			$check = true;
			$ra = $objUtil->checkPostKey ( 'RAhours', 0 ) + ($objUtil->checkPostKey ( 'RAminutes', 0 ) / 60) + ($objUtil->checkPostKey ( 'RAseconds', 0 ) / 3600);
			if (array_key_exists ( 'DeclDegrees', $_POST ) && (($_POST ['DeclDegrees'] < 0) || (strcmp ( $_POST ['DeclDegrees'], '-0' ) == 0)))
				$declination = $objUtil->checkPostKey ( 'DeclDegrees', 0 ) - ($objUtil->checkPostKey ( 'DeclMinutes', 0 ) / 60) - ($objUtil->checkPostKey ( 'DeclSeconds', 0 ) / 3600);
			else
				$declination = $objUtil->checkPostKey ( 'DeclDegrees', 0 ) + ($objUtil->checkPostKey ( 'DeclMinutes', 0 ) / 60) + ($objUtil->checkPostKey ( 'DeclSeconds', 0 ) / 3600);
			if (! $objUtil->checkPostKey ( 'number' ) || ! $objUtil->checkPostKey ( 'type' ) || ! $objUtil->checkPostKey ( 'con' ) || ($ra == 0.0) || ($declination == 0.0)) {
				$entryMessage = LangValidateObjectMessage1; // check if required fields are filled in
				$_GET ['indexAction'] = 'add_object';
			}
			if ($check) // check name
{
				$catalog = trim ( $_POST ['catalog'] );
				$catalogs = $objObject->getCatalogs ();
				$foundcatalog = "";
				while ( (list ( $key, $value ) = each ( $catalogs )) && (! $foundcatalog) )
					if (strtoupper ( $value ) == strtoupper ( $catalog ))
						$foundcatalog = $value;
				if ($foundcatalog)
					$catalog = $foundcatalog;
				$name = trim ( $catalog . " " . ucwords ( trim ( $_POST ['number'] ) ) );
				$query1 = array (
						"name" => $name
				);
				if ($objObject->getObjectFromQuery ( $query1, 1 )) // object already exists
{
					$entryMessage = LangValidateObjectMessage2;
					$_GET ['object'] = $name;
					$_GET ['indexAction'] = 'detail_object';
					$check = false;
				}
			}
			if ($check) // calculate right ascension
				if ((! $objUtil->checkLimitsInclusive ( $objUtil->checkPostKey ( 'RAhours', - 1 ), 0, 23 )) || (! $objUtil->checkLimitsInclusive ( $objUtil->checkPostKey ( 'RAminutes', - 1 ), 0, 59 )) || (! $objUtil->checkLimitsInclusive ( $objUtil->checkPostKey ( 'RAseconds', - 1 ), 0, 59 ))) {
					$entryMessage = LangValidateObjectMessage4;
					$_GET ['indexAction'] = 'add_object';
					$check = false;
				}
			if ($check) // calculate declination
				if ((! $objUtil->checkLimitsInclusive ( $objUtil->checkPostKey ( 'DeclDegrees', - 100 ), - 90, 90 )) || (! $objUtil->checkLimitsInclusive ( $objUtil->checkPostKey ( 'DeclMinutes', - 1 ), 0, 59 )) || (! $objUtil->checkLimitsInclusive ( $objUtil->checkPostKey ( 'DeclSeconds', - 1 ), 0, 59 ))) {
					$entryMessage = LangValidateObjectMessage5;
					$_GET ['indexAction'] = 'add_object';
					$check = false;
				}
			if ($check) // magnitude
{
				$magnitude = "99.9";
				if ($objUtil->checkPostKey ( 'magnitude' ) && (! (preg_match ( '/^([0-9]{1,2})[.,]{0,1}([0-9]{0,1})$/', abs ( $_POST ['magnitude'] ), $matches )))) {
					$entryMessage = LangValidateObjectMessage8;
					$_GET ['indexAction'] = 'add_object';
					$check = false;
				} elseif ($objUtil->checkPostKey ( 'magnitude' )) {
					$magnitude = $matches [1] . ".";
					if ($matches [2] != "")
						$magnitude = $magnitude . $matches [2];
					else
						$magnitude = $magnitude . "0";
					if ($_POST ['magnitude'] < 0.0) {
						$magnitude = - $magnitude;
					}
				}
			}
			if ($check) // position angle
{
				$posangle = "999";
				if (! $objUtil->checkLimitsInclusive ( 'posangle', 0, 359 )) {
					$entryMessage = LangValidateObjectMessage6;
					$_GET ['indexAction'] = 'add_object';
					$check = false;
				} elseif ($objUtil->checkPostKey ( 'posangle' ))
					$posangle = $_POST ['posangle'];
			}
			if ($check) // surface brightness
{
				$sb = "99.9";
				if ($_POST ['sb'] && preg_match ( '/^([0-9]{1,2})[.,]{0,1}([0-9]{0,1})$/', $_POST ['sb'], $matches )) {
					$sb = "" . $matches [1] . ".";
					if ($matches [2] != "")
						$sb = $sb . $matches [2];
					else
						$sb = $sb . "0";
				}
			}
			if ($check) // check diam1
{
				$diam1 = 0.0;
				if ($objUtil->checkPostKey ( 'size_x' ) && $objUtil->checkPostKey ( 'size_x_units' )) {
					if ($objUtil->checkPostKey ( 'size_x_units' ) == "min")
						$diam1 = $objUtil->checkPostKey ( 'size_x' ) * 60.0;
					elseif ($objUtil->checkPostKey ( 'size_x_units' ) == "sec")
						$diam1 = $objUtil->checkPostKey ( 'size_x' );
					else {
						$entryMessage = LangValidateObjectMessage7;
						$_GET ['indexAction'] = 'add_object';
						$check = false;
					}
				}
			}
			if ($check) // check diam2
{
				$diam2 = 0.0;
				if ($objUtil->checkPostKey ( 'size_y' ) && $objUtil->checkPostKey ( 'size_y_units' )) {
					if ($objUtil->checkPostKey ( 'size_y_units' ) == "min")
						$diam2 = $objUtil->checkPostKey ( 'size_y', 0 ) * 60.0;
					elseif ($objUtil->checkPostKey ( 'size_y_units' ) == "sec")
						$diam2 = $objUtil->checkPostKey ( 'size_y', 0 );
					else {
						$entryMessage = LangValidateObjectMessage7;
						$_GET ['indexAction'] = 'add_object';
						$check = false;
					}
				}
			}
			if ($check) // fill database
{
				$objObject->addDSObject ( $name, $catalog, ucwords ( trim ( $_POST ['number'] ) ), $_POST ['type'], $_POST ['con'], $ra, $declination, $magnitude, $sb, $diam1, $diam2, $posangle, "DeepskyLogUser " . $loggedUser . " " . date ( 'Ymd' ) );
				$body = LangValidateAccountEmailTitleObject . " <a href=\"www.deepskylog.org/index.php?indexAction=detail_object&object=" . urlencode ( $name ) . "\">" . $name . "</a> " .
								 LangValidateAccountEmailTitleObjectObserver . " " . "<a href=\"www.deepskylog.org/index.php?indexAction=detail_observer&user=" . urlencode ( $loggedUser ) . "\">" .
								 $objObserver->getObserverProperty ( $loggedUser, 'firstname' ) . " " . $objObserver->getObserverProperty ( $loggedUser, 'name' ) . "</a><br /><br />";

				if (isset ( $developversion ) && ($developversion == 1))
					$entryMessage .= "On the live server, a mail would be sent with the subject: " . LangValidateAccountEmailTitleObject . " " . $name . ".<br />";
				else
					$objMessage->sendEmail(LangValidateAccountEmailTitleObject . " " . $name, $body, "developers");

				$_GET ['indexAction'] = 'detail_object';
				$_GET ['object'] = $name;
			}
		} elseif ($objUtil->checkPostKey ( 'clearfields' )) // pushed clear fields button
			$_GET ['indexAction'] = "add_object";
		else
			throw new Exception ( LangException000 );
	}
	public function checknames() {
		global $objDatabase, $objCatalog;
		$theobjects = $objDatabase->selectSingleArray ( 'SELECT name FROM objects', 'name' );
		while ( list ( $key, $theobject ) = each ( $theobjects ) ) {
			$thenewobject = $objCatalog->checkObject ( $theobject );
			if ($thenewobject != $theobject) {
				$firstspace = strpos ( $thenewobject, ' ', 0 );
				$thecatalog = substr ( $thenewobject, 0, $firstspace );
				$theindex = substr ( $thenewobject, $firstspace + 1 );
				$this->newName ( $theobject, $thecatalog, $theindex );
				echo "Changed object name: " . $thecatalog . ' ' . $theindex . " <= " . $theobject . "\n";
			}
		}

		$theobjects = $objDatabase->selectSingleArray ( 'SELECT altname FROM objectnames;', 'altname' );
		while ( list ( $key, $theobject ) = each ( $theobjects ) ) {
			$thenewobject = $objCatalog->checkObject ( $theobject );
			if ($thenewobject != $theobject) {
				$firstspace = strpos ( $thenewobject, ' ', 0 );
				$thecatalog = substr ( $thenewobject, 0, $firstspace );
				$theindex = substr ( $thenewobject, $firstspace + 1 );
				$sql = "UPDATE objectnames SET catalog='" . addslashes ( $thecatalog ) . "', catindex='" . addslashes ( $theindex ) . "', altname='" . addslashes ( $thecatalog ) . " " . addslashes ( $theindex ) . "' WHERE altname='" . addslashes ( $theobject ) . "';";
				echo "Changed altname: " . $thecatalog . ' ' . $theindex . " <= " . $theobject . "\n";
				$objDatabase->execSQL ( $sql );
			}
		}
	}
}
class contrastcompare {
	var $_reverse;
	function contrastCompare($reverse) {
		$this->_reverse = $reverse;
	}
	function compare($a, $b) {
		$a = explode ( '/', $a );
		$b = explode ( '/', $b );
		$a = $a [0];
		$b = $b [0];
		if ($a == $b)
			return 0;
		if ($this->_reverse)
			return ($b > $a) ? - 1 : 1;
		else
			return ($a > $b) ? - 1 : 1;
	}
}
?>
