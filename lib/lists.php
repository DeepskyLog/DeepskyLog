<?php
// lists.php
// code for maintance of lists
global $inIndex;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
class Lists {
	public function addObservations($thetype) {
		global $entryMessage, $myList, $objObject, $objDatabase, $loggedUser, $listname, $objPresentations;
		if (! $myList)
			return;
		if ($thetype == "longest") {
			$sql = "SELECT objectname FROM observerobjectlist " . "WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\" AND objectname <>\"\"";
			$run = $objDatabase->selectSingleArray ( $sql, 'objectname' );
			for($i = 0; $i < count ( $run ); $i ++) {
				$theobject = $run [$i];
				$sql = "SELECT observations.id, observations.description FROM observations WHERE observations.objectname=\"" . $theobject . "\";";
				$get2 = $objDatabase->selectRecordsetArray ( $sql );
				$sortarray = array ();
				while ( list ( $key, $value ) = each ( $get2 ) ) {
					$sortarray [strlen ( $value ['description'] )] = $value ['id'];
				}
				if (count ( $sortarray ) > 0) {
					ksort ( $sortarray, SORT_NUMERIC );
					$temp = array_pop ( $sortarray );
					$sql = "SELECT observations.objectname, observations.description, observers.name, observers.firstname, locations.name as location, instruments.name AS instrument " . "FROM observations " . "JOIN observers ON observations.observerid=observers.id " . "JOIN locations ON observations.locationid=locations.id " . "JOIN instruments ON observations.instrumentid=instruments.id " . "WHERE observations.id=" . $temp;
					$temp = $objDatabase->selectRecordArray ( $sql );
					$name = $temp ['objectname'];
					$description = '(' . $temp ['firstname'] . ' ' . $temp ['name'];
					$description .= '/' . $temp ['instrument'];
					$description .= '/' . $temp ['location'];
					$description .= ') ' . $objPresentations->br2nl ( $temp ['description'] );
					$get3 = $objDatabase->selectRecordArray ( "SELECT description FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\" AND objectname=\"" . $theobject . "\"" );
					if (strpos ( $get3 ['description'], $description ) === FALSE)
						$objDatabase->execSQL ( "UPDATE observerobjectlist SET description = \"" . substr ( (($get3 ['description']) ? ($get3 ['description'] . " ") : '') . $description, 0, 4096 ) . "\" WHERE observerid = \"" . $loggedUser . "\" AND listname=\"" . $listname . "\" AND objectname=\"" . $theobject . "\"" );
				}
			}
			$entryMessage .= LangToListMyListsAddedLongestObsDescription;
		}
	}
	public function removeObservations($thetype) {
		global $entryMessage, $myList, $objObject, $objDatabase, $loggedUser, $listname, $objPresentations;
		if (! $myList)
			return;
		if ($thetype == "all") {
			$sql = "UPDATE observerobjectlist " . "SET description = (SELECT objects.description FROM objects WHERE objects.name=observerobjectlist.objectname) " . "WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\" AND objectname <>\"\"";
			$run = $objDatabase->execSQL ( $sql );
			$entryMessage .= LangToListMyListsRemovedObsDescription;
		}
	}
	public function addList($name) {
		global $objDatabase, $objUtil, $loggedUser, $objObserver, $objMessages, $baseURL;
		if ($loggedUser && $name && (! ($this->checkList ( $name )))) { // Send mail when we are creating a public list
			$pos = strpos ( $name, "Public" );
			
			if ($pos !== false) {
				$username = $objObserver->getObserverProperty ( $loggedUser, "firstname" ) . " " . $objObserver->getObserverProperty ( $loggedUser, "name" );
				// Remove the public from the list
				$listname = substr ( $name, 8 );
				
				$subject = LangMessagePublicList1 . $listname . LangMessagePublicList2 . $username;
				$message = LangMessagePublicList3;
				$message = $message . LangMessagePublicList4 . "<a href=\"http://www.deepskylog.org/index.php?indexAction=listaction&amp;activateList=true&amp;listname=Public:%20" . urlencode ( $listname ) . "\">" . $listname . "</a><br /><br />";
				$message = $message . LangMessagePublicList5 . "<a href=\"http://www.deepskylog.org/index.php?indexAction=new_message&amp;receiver=" . urlencode ( $loggedUser ) . "&amp;subject=Re:%20" . urlencode ( $listname ) . "\">" . $username . "</a>";
				
				$objMessages->sendMessage ( "DeepskyLog", "all", $subject, $message );
			}
			$objDatabase->execSQL ( "INSERT INTO observerobjectlist(observerid, objectname, listname, objectplace, objectshowname) VALUES (\"" . $loggedUser . "\", \"\", \"" . $name . "\", '0', \"\")" );
			if (array_key_exists ( 'QobjParams', $_SESSION ) && array_key_exists ( 'source', $_SESSION ['QobjParams'] ) && ($_SESSION ['QobjParams'] ['source'] == 'tolist'))
				unset ( $_SESSION ['QobjParams'] );
		}
	}
	public function addObjectToList($name, $showname = '') {
		global $loggedUser, $listname, $objDatabase, $myList;
		if (! $myList)
			return;
		if (! $showname)
			$showname = $name;
		if (! ($objDatabase->selectSingleValue ( "SELECT objectplace FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\" AND objectname=\"" . $name . "\"", 'objectplace', 0 )))
			$objDatabase->execSQL ( "INSERT INTO observerobjectlist(observerid, objectname, listname, objectplace, objectshowname, description) VALUES (\"" . $loggedUser . "\", \"$name\", \"$listname\", \"" . (($objDatabase->selectSingleValue ( "SELECT MAX(objectplace) AS ObjPlace FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"$listname\"", 'ObjPlace', 0 )) + 1) . "\", \"$showname\", \"" . $objDatabase->selectSingleValue ( "SELECT description FROM objects WHERE name=\"" . $name . "\"", 'description' ) . "\")" );
		if (array_key_exists ( 'QobjParams', $_SESSION ) && array_key_exists ( 'source', $_SESSION ['QobjParams'] ) && ($_SESSION ['QobjParams'] ['source'] == 'tolist'))
			unset ( $_SESSION ['QobjParams'] );
	}
	public function addObservationToList($id) {
		global $objDatabase, $loggedUser, $listname, $myList, $objPresentations;
		$sql = "SELECT observations.objectname, observations.description, observers.name, observers.firstname, locations.name as location, instruments.name AS instrument " . "FROM observations " . "JOIN observers ON observations.observerid=observers.id " . "JOIN locations ON observations.locationid=locations.id " . "JOIN instruments ON observations.instrumentid=instruments.id " . "WHERE observations.id=" . $id;
		$get = $objDatabase->selectRecordArray ( $sql );
		if ($get) {
			$name = $get ['objectname'];
			$description = '(' . $get ['firstname'] . ' ' . $get ['name'];
			$description .= '/' . $get ['instrument'];
			$description .= '/' . $get ['location'];
			$description .= ') ' . $objPresentations->br2nl ( $get ['description'] );
			$get = $objDatabase->selectRecordArray ( "SELECT objectplace AS ObjPl, description FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\" AND objectname=\"" . $name . "\"" );
			if (! $get)
				$objDatabase->execSQL ( "INSERT INTO observerobjectlist(observerid, objectname, listname, objectplace, objectshowname, description) " . "VALUES (\"" . $loggedUser . "\", \"" . $name . "\", \"" . $listname . "\"," . " \"" . (($objDatabase->selectSingleValue ( "SELECT MAX(objectplace) AS ObjPl FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\"", 'ObjPl', 0 )) + 1) . "\", " . "\"" . $name . "\", \"" . substr ( (($tempDescription = $objDatabase->selectSingleValue ( "SELECT description FROM objects WHERE name=\"" . $name . "\"", 'description' )) ? ($tempDescription . ' \n') : '') . $description, 0, 1024 ) . "\")" );
			else
				$objDatabase->execSQL ( "UPDATE observerobjectlist SET description = \"" . substr ( (($get ['description']) ? ($get ['description'] . " ") : '') . $description, 0, 1024 ) . "\" WHERE observerid = \"" . $loggedUser . "\" AND listname=\"" . $listname . "\" AND objectname=\"" . $name . "\"" );
		}
		if (array_key_exists ( 'QobjParams', $_SESSION ) && array_key_exists ( 'source', $_SESSION ['QobjParams'] ) && ($_SESSION ['QobjParams'] ['source'] == 'tolist'))
			unset ( $_SESSION ['QobjParams'] );
	}
	public function checkList($name) {
		global $loggedUser, $objDatabase;
		$retval = 0;
		if (substr ( $name, 0, 7 ) == "Public:") {
			$sql = "SELECT listname FROM observerobjectlist WHERE listname=\"" . $name . "\"";
			$run = $objDatabase->selectRecordset ( $sql );
			if ($get = $run->fetch ( PDO::FETCH_OBJ ))
				$retval = 1;
		}
		if ($loggedUser) {
			$sql = "SELECT listname FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $name . "\"";
			$run = $objDatabase->selectRecordset ( $sql );
			if ($get = $run->fetch ( PDO::FETCH_OBJ ))
				$retval = 2;
		}
		return $retval;
	}
	public function checkObjectInMyActiveList($value) {
		global $objDatabase, $loggedUser, $listname;
		return $objDatabase->selectSingleValue ( "SELECT observerobjectlist.objectplace FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND objectname=\"" . $value . "\" AND listname=\"" . $listname . "\"", 'objectplace', 0 );
	}
	public function checkObjectMyOrPublicList($value, $list) {
		global $objDatabase, $loggedUser;
		return $objDatabase->selectSingleValue ( "SELECT observerobjectlist.objectplace FROM observerobjectlist WHERE " . ((substr ( $list, 0, 7 ) == 'Public:') ? "" : ("observerid = \"" . $loggedUser . "\" AND ")) . "objectname=\"" . $value . "\" AND listname=\"" . $list . "\"", 'objectplace', 0 );
	}
	public function emptyList($listname) {
		global $objDatabase, $loggedUser, $myList;
		if ($loggedUser && $myList) {
			$objDatabase->execSQL ( "DELETE FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\" AND objectplace<>0" );
			if (array_key_exists ( 'QobjParams', $_SESSION ) && array_key_exists ( 'source', $_SESSION ['QobjParams'] ) && ($_SESSION ['QobjParams'] ['source'] == 'tolist'))
				unset ( $_SESSION ['QobjParams'] );
		}
	}
	public function getListObjectDescription($object) {
		global $loggedUser, $listname, $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT observerobjectlist.description FROM observerobjectlist WHERE " . ((substr ( $listname, 0, 7 ) == 'Public:') ? "" : "observerid = \"" . $loggedUser . "\" AND ") . "objectname=\"" . $object . "\" AND listname=\"" . $listname . "\"", 'description', '' );
	}
	public function getListOwner() {
		global $listname, $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT observerobjectlist.observerid FROM observerobjectlist WHERE listname=\"" . $listname . "\" AND objectplace=0", 'observerid', '' );
	}
	public function getInPrivateLists($theobject) {
		global $objDatabase, $loggedUser;
		$result = '';
		$results = array ();
		if ($loggedUser) {
			$sql = 'SELECT listname FROM observerobjectlist WHERE objectname="' . $theobject . '" AND observerid="' . $loggedUser . '"';
			$results = $objDatabase->selectSingleArray ( $sql, 'listname' );
			while ( list ( $key, $value ) = each ( $results ) )
				$result .= "/" . $value;
		}
		return substr ( $result, 1 );
	}
	public function getInPublicLists($theobject) {
		global $objDatabase, $loggedUser;
		$result = '';
		$results = array ();
		$sql = 'SELECT listname FROM observerobjectlist WHERE objectname="' . $theobject . '" AND listname LIKE "Public:%"';
		$results = $objDatabase->selectSingleArray ( $sql, 'listname' );
		while ( list ( $key, $value ) = each ( $results ) )
			$result .= "/" . substr ( $value, 8 );
		return substr ( $result, 1 );
	}
	public function getLists() {
		global $objDatabase, $loggedUser;
		$result = array ();
		if (array_key_exists ( 'deepskylog_id', $_SESSION )) {
			$run = $objDatabase->selectRecordset ( "SELECT DISTINCT observerobjectlist.listname FROM observerobjectlist WHERE observerid=\"" . $loggedUser . "\" OR listname LIKE \"Public: %\" ORDER BY observerobjectlist.listname" );
			$get = $run->fetch ( PDO::FETCH_OBJ );
			if ($get) {
				$result1 = array ();
				$result2 = array ();
				while ( $get ) {
					if (substr ( $get->listname, 0, 7 ) == "Public:")
						$result2 [] = $get->listname;
					else
						$result1 [] = $get->listname;
					$get = $run->fetch ( PDO::FETCH_OBJ );
				}
				$result = array_merge ( $result1, $result2 );
			}
		}
		return $result;
	}
	public function getMyLists() {
		global $loggedUser, $objDatabase;
		return $objDatabase->selectSingleArray ( "SELECT DISTINCT observerobjectlist.listname FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\"", 'listname' );
	}
	public function showLists($public = false) {
		global $objUtil;
		
		// Get all the lists of the observer
		$lists = $this->getMyLists ();
		
		if ($public) {
			foreach ( $lists as $list ) {
				// Only add the public lists to the results
				if (substr ( $list, 0, 7 ) == "Public:") {
					$results [] = $list;
				}
			}
		} else {
			foreach ( $lists as $list ) {
				// Only add the private lists to the results
				if (substr ( $list, 0, 7 ) != "Public:") {
					$results [] = $list;
				}
			}
		}

		$tablename = "obslist";
		if ($public) {
			$tablename .= "pub";
		}
		
		echo "<table class=\"table sort-table" . $tablename . " table-condensed table-striped table-hover tablesorter custom-popup\">";
 		echo "<thead>";
		echo "<tr><th>";
		echo LangListName;
		echo "</th></tr>";
 		echo "</thead>";
 		echo "<tbody>";
		
		foreach ( $results as $listname ) {
			if ($listname != "") {
				echo "<tr>";
				echo "<td>";
				
				// Add a link to see and activate the list.
				echo "<a href=\"" . $baseURL . "index.php?indexAction=listaction&amp;activateList=true&amp;listname=" . $listname . "\">";
								
				echo $listname;
				
				echo "</a>";
				
				echo "</td>";
				
				// TODO: Add a button to change the name.
				
				// TODO: Add a button to make Public / private
				echo "</tr>";
			}
		}

		echo "</tbody>";
		echo "</table>";

		echo $objUtil->addTablePager ( $tablename );

		echo $objUtil->addTableJavascript ( $tablename );
	}
	public function getObjectsFromList($theListname) {
		global $objObject, $objDatabase, $loggedUser;
		$obs = array ();
		if (substr ( $theListname, 0, 7 ) == "Public:")
			$sql = "SELECT observerobjectlist.objectname, observerobjectlist.objectplace, observerobjectlist.objectshowname, observerobjectlist.description FROM observerobjectlist " . "JOIN objects ON observerobjectlist.objectname = objects.name " . "WHERE listname=\"" . $theListname . "\" AND objectname <>\"\"";
		else
			$sql = "SELECT observerobjectlist.objectname, observerobjectlist.objectplace, observerobjectlist.objectshowname, observerobjectlist.description FROM observerobjectlist " . "JOIN objects ON observerobjectlist.objectname = objects.name " . "WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $theListname . "\" AND objectname <>\"\"";
		$run = $objDatabase->selectRecordset ( $sql );
		while ( $get = $run->fetch ( PDO::FETCH_OBJ ) )
			if (! in_array ( $get->objectname, $obs ))
				$obs [$get->objectshowname] = array (
						$get->objectplace,
						$get->objectname,
						$get->description 
				);
		return $objObject->getSeenObjectDetails ( $obs, "A" );
	}
	public function ObjectDownInList($place) {
		global $loggedUser, $listname, $objDatabase, $myList;
		if (! $myList)
			return;
		if ($place && ($place > 1)) {
			$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectplace=-1 WHERE observerid = \"" . $loggedUser . "\" AND listname =\"" . $listname . "\" AND objectplace=" . $place );
			$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectplace=objectplace+1 WHERE observerid=\"" . $loggedUser . "\" AND listname=\"" . $listname . "\" AND objectplace=" . $place - 1 );
			$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectplace=" . ($place - 1) . " WHERE observerid=\"" . $loggedUser . "\" AND listname =\"" . $listname . "\" AND objectplace=-1" );
		}
		if (array_key_exists ( 'QobjParams', $_SESSION ) && array_key_exists ( 'source', $_SESSION ['QobjParams'] ) && ($_SESSION ['QobjParams'] ['source'] == 'tolist'))
			unset ( $_SESSION ['QobjParams'] );
	}
	public function ObjectFromToInList($from, $to) {
		global $loggedUser, $listname, $objDatabase, $myList;
		if (! ($myList))
			return '';
		$max = $objDatabase->selectSingleValue ( "SELECT MAX(objectplace) AS ObjPl FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\"", 'ObjPl' );
		if (($from > 0) && ($from <= $max) && ($to > 0) && ($to <= $max) && ($from != $to)) {
			if ($from < $to) {
				$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectplace=-1 WHERE ((observerid=\"" . $loggedUser . "\") AND (listname=\"" . $listname . "\") AND (objectplace=" . $from . "))" );
				$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectplace=objectplace-1 WHERE ((observerid=\"" . $loggedUser . "\") AND (listname=\"" . $listname . "\") AND (objectplace>" . $from . ") AND (objectplace<=" . $to . "))" );
				$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectplace=" . $to . " WHERE ((observerid=\"" . $loggedUser . "\") AND (listname=\"" . $listname . "\") AND (objectplace=-1))" );
			} else {
				$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectplace=-1 WHERE ((observerid=\"" . $loggedUser . "\") AND (listname=\"" . $listname . "\") AND (objectplace=" . $from . "))" );
				$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectplace=objectplace+1 WHERE ((observerid=\"" . $loggedUser . "\") AND (listname=\"" . $listname . "\") AND (objectplace>=" . $to . ") AND (objectplace<" . $from . "))" );
				$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectplace=" . $to . " WHERE ((observerid=\"" . $loggedUser . "\") AND (listname=\"" . $listname . "\") AND (objectplace=-1))" );
			}
			if (array_key_exists ( 'QobjParams', $_SESSION ) && array_key_exists ( 'source', $_SESSION ['QobjParams'] ) && ($_SESSION ['QobjParams'] ['source'] == 'tolist'))
				unset ( $_SESSION ['QobjParams'] );
			return LangToListMoved7 . $_GET ['ObjectToPlaceInList'] . ".";
		} else
			return '';
	}
	public function ObjectUpInList($place) {
		global $loggedUser, $listname, $objDatabase, $myList;
		if (! $myList)
			return;
		if ($place < $objDatabase->selectSingleValue ( "SELECT MAX(objectplace) AS ObjPl FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname=\"" . $listname . "\"", 'ObjPl' )) {
			$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectplace=-1 WHERE observerid=\"" . $loggedUser . "\" AND listname=\"" . $listname . "\" AND objectplace=" . $place );
			$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectplace=objectplace-1 WHERE observerid=\"" . $loggedUser . "\" AND listname=\"" . $listname . "\" AND objectplace=" . $place + 1 );
			$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectplace=" . ($place + 1) . " WHERE observerid=\"" . $loggedUser . "\" AND listname=\"" . $listname . "\" AND objectplace=-1" );
		}
		if (array_key_exists ( 'QobjParams', $_SESSION ) && array_key_exists ( 'source', $_SESSION ['QobjParams'] ) && ($_SESSION ['QobjParams'] ['source'] == 'tolist'))
			unset ( $_SESSION ['QobjParams'] );
	}
	public function removeList($name) {
		global $objDatabase, $loggedUser, $myList;
		if ($loggedUser && $myList) {
			$objDatabase->execSQL ( "DELETE FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $name . "\"" );
			if (array_key_exists ( 'QobjParams', $_SESSION ) && array_key_exists ( 'source', $_SESSION ['QobjParams'] ) && ($_SESSION ['QobjParams'] ['source'] == 'tolist'))
				unset ( $_SESSION ['QobjParams'] );
		}
	}
	public function removeObjectFromList($name) {
		global $loggedUser, $listname, $objDatabase, $myList;
		if (! $myList)
			return;
		if ($place = $objDatabase->selectSingleValue ( "SELECT objectplace AS ObjPl FROM observerobjectlist WHERE observerid=\"" . $loggedUser . "\" AND listname = \"" . $listname . "\" AND objectname=\"" . $name . "\"", 'ObjPl' )) {
			$objDatabase->execSQL ( "DELETE FROM observerobjectlist WHERE observerid=\"" . $loggedUser . "\" AND listname=\"" . $listname . "\" AND objectname=\"" . $name . "\"" );
			$objDatabase->execSQL ( "UPDATE observerobjectlist SET objectplace=objectplace-1 WHERE observerid = \"" . $loggedUser . "\" AND listname=\"" . $listname . "\" AND objectplace>" . $place );
		}
		if (array_key_exists ( 'QobjParams', $_SESSION ) && array_key_exists ( 'source', $_SESSION ['QobjParams'] ) && ($_SESSION ['QobjParams'] ['source'] == 'tolist'))
			unset ( $_SESSION ['QobjParams'] );
	}
	public function renameList($nameFrom, $nameTo) {
		global $loggedUser, $objDatabase, $myList, $objMessages, $objObserver, $baseURL;
		if ($loggedUser && $myList) { // Send mail when we are creating a public list
			$pos = strpos ( $nameTo, "Public" );
			$posOld = strpos ( $nameFrom, "Public" );
			
			if (! ($posOld !== false)) {
				if ($pos !== false) {
					$username = $objObserver->getObserverProperty ( $loggedUser, "firstname" ) . " " . $objObserver->getObserverProperty ( $loggedUser, "name" );
					// Remove the public from the list
					$listname = substr ( $nameTo, 8 );
					
					$subject = LangMessagePublicList1 . $listname . LangMessagePublicList2 . $username;
					$message = LangMessagePublicList3;
					$message = $message . LangMessagePublicList4 . "<a href=\"http://www.deepskylog.org/index.php?indexAction=listaction&amp;activateList=true&amp;listname=Public:%20" . urlencode ( $listname ) . "\">" . $listname . "</a><br /><br />";
					$message = $message . LangMessagePublicList5 . "<a href=\"http://www.deepskylog.org/index.php?indexAction=new_message&amp;receiver=" . urlencode ( $loggedUser ) . "&amp;subject=Re:%20" . urlencode ( $listname ) . "\">" . $username . "</a>";
					
					$objMessages->sendMessage ( "DeepskyLog", "all", $subject, $message );
				}
			}
			$objDatabase->execSQL ( "UPDATE observerobjectlist SET listname=\"" . $nameTo . "\" WHERE observerid=\"" . $loggedUser . "\" AND listname=\"" . $nameFrom . "\"" );
			if (array_key_exists ( 'QobjParams', $_SESSION ) && array_key_exists ( 'source', $_SESSION ['QobjParams'] ) && ($_SESSION ['QobjParams'] ['source'] == 'tolist'))
				unset ( $_SESSION ['QobjParams'] );
		}
	}
	public function setListObjectDescription($object, $description) {
		global $objDatabase, $loggedUser, $listname, $myList;
		if (! ($myList))
			return;
		$objDatabase->execSQL ( "UPDATE observerobjectlist SET description=\"" . $description . "\" WHERE observerid=\"" . $loggedUser . "\" AND objectname=\"" . $object . "\" AND listname=\"" . $listname . "\"" );
		if (array_key_exists ( 'QobjParams', $_SESSION ) && array_key_exists ( 'source', $_SESSION ['QobjParams'] ) && ($_SESSION ['QobjParams'] ['source'] == 'tolist'))
			unset ( $_SESSION ['QobjParams'] );
	}
}
?>
