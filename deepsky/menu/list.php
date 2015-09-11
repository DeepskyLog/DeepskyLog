<?php
// list.php
// shows the lists available to the user
global $inIndex, $loggedUser, $objUtil;

if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! ($loggedUser))
	throw new Exception ( LangExcpetion001 );
elseif (! ($objUtil->checkAdminOrUserID ( $loggedUser )))
	throw new Exception ( LangExcpetion012 );
else
	menu_list ();
function menu_list() {
	global $baseURL, $loggedUser, $myList, $objDatabase, $objList;
	echo "<form id=\"listsSelectForm\" class=\"nav navbar-nav form-inline\">";
	echo "<div class=\"form-group\">";
	echo "<p class=\"navbar-text\">" . LangListsTitle;
	if ($loggedUser)
		echo "&nbsp;-&nbsp;" . "<a href=\"" . $baseURL . "index.php?indexAction=listaction\">" . LangManage . "</a>";
	$result1 = array ();
	$result2 = array ();
	$sql = "SELECT DISTINCT observerobjectlist.listname " . "FROM observerobjectlist " . "WHERE observerid = \"" . $loggedUser . "\" ORDER BY observerobjectlist.listname";
	$run = $objDatabase->selectRecordset ( $sql );
	$get = $run->fetch ( PDO::FETCH_OBJ );
	while ( $get ) {
		$result1 [] = $get->listname;
		$get = $run->fetch ( PDO::FETCH_OBJ );
	}
	$sql = "SELECT DISTINCT observerobjectlist.listname " . "FROM observerobjectlist " . "WHERE observerid <> \"" . $loggedUser . "\"" . "AND public=\"1\" ORDER BY observerobjectlist.listname";
	$run = $objDatabase->selectRecordset ( $sql );
	$get = $run->fetch ( PDO::FETCH_OBJ );
	
	echo "&nbsp;&nbsp;";
	while ( $get ) {
		$result2 [] = $get->listname;
		$get = $run->fetch ( PDO::FETCH_OBJ );
	}
	
	$sql = "SELECT DISTINCT observerobjectlist.listname " . "FROM observerobjectlist " . "WHERE public=\"1\"";
	$run = $objDatabase->selectRecordset ( $sql );
	$get = $run->fetch ( PDO::FETCH_OBJ );
	$publicLists = array ();
	while ( $get ) {
		$publicLists [] = $get->listname;
		$get = $run->fetch ( PDO::FETCH_OBJ );
	}	
	
	$result1 [] = '----------';
	$result = array_merge ( $result1, $result2 );
	if (count ( $result ) > 0) {
		echo "<select class=\"form-control\" name=\"activatelist\" onchange=\"location=this.options[this.selectedIndex].value;\">";
		if ((! array_key_exists ( 'listname', $_SESSION )) || (! $_SESSION ['listname']))
			$_SESSION ['listname'] = "----------";
		while ( list ( $key, $value ) = each ( $result ) ) {
			// If the list is a Public list, we add 'Public: ' to the name of the list.
 			if (in_array($value, $publicLists)) {
 				$listname = LangPublicList . $value;
 			} else {
				$listname = $value;
			}
			if ((($value == $_SESSION ['listname']) && $myList) || ((! $myList) && ($value == "----------")))
				echo ("<option selected=\"selected\" value=\"" . $baseURL . "index.php?indexAction=listaction&amp;activateList=true&amp;listname=" . $value . "\">" . $listname . "</option>");
			elseif (! (array_key_exists ( 'removeList', $_GET ) && ($_SESSION ['listname'] == $value)))
				echo ("<option value=\"" . $baseURL . "index.php?indexAction=listaction&amp;activateList=true&amp;listname=" . $value . "\">" . $listname . "</option>");
		}
		echo "</select>";
	}
	echo "</p></div></form>";
}
?>