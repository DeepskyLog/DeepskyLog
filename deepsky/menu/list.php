<?php
// list.php
// shows the lists available to the user
global $inIndex, $loggedUser, $objUtil;

if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! ($objUtil->checkAdminOrUserID ( $loggedUser )))
	throw new Exception(_("You need to be logged in to execute these operations."));
else
	menu_list ();
function menu_list() {
	global $baseURL, $loggedUser, $myList, $objDatabase, $objList;
	echo "<form id=\"listsSelectForm\" class=\"nav navbar-nav form-inline\">";
	echo "<div class=\"form-group\">";
	echo "<p class=\"navbar-text\">" . _("List");
	if ($loggedUser)
		echo "&nbsp;-&nbsp;" . "<a href=\"" . $baseURL . "index.php?indexAction=listaction\">" . _("Manage") . "</a>";
	$result1 = array ();
	$result2 = array ();
	$result1 = $objList->getMyLists();
	// Query the underlying table directly instead of the observerobjectlist view:
	// the view UNIONs in observing_list_items (tens of thousands of rows) which is
	// unnecessary here since every list already has exactly one row in observing_lists.
	$sql = "SELECT DISTINCT name AS listname " . "FROM observing_lists " . "WHERE public=\"1\" ORDER BY name";
	$run = $objDatabase->selectRecordset ( $sql );
	$get = $run->fetch ( PDO::FETCH_OBJ );

	echo "&nbsp;&nbsp;";
	$publicLists = array ();
	while ( $get ) {
		$publicLists [] = $get->listname;
		if (!in_array($get->listname, $result1, true)) {
			$result2 [] = $get->listname;
		}
		$get = $run->fetch ( PDO::FETCH_OBJ );
	}

	$result1 [] = '----------';
	$result = array_merge ( $result1, $result2 );
	if (count ( $result ) > 0) {
		echo "<select class=\"form-control\" name=\"activatelist\" onchange=\"location=this.options[this.selectedIndex].value;\">";
		if ((! array_key_exists ( 'listname', $_SESSION )) || (! $_SESSION ['listname']))
			$_SESSION ['listname'] = "----------";
		foreach ($result as $key=>$value) {
			// If the list is a Public list, we add 'Public: ' to the name of the list.
 			if (in_array($value, $publicLists)) {
 				$listname = _("Public: ") . $value;
				$public = 1;
 			} else {
				$listname = $value;
				$public = 0;
			}
			if ((($value == $_SESSION ['listname']) && $myList) || ((! $myList) && ($value == "----------")))
				echo ("<option selected=\"selected\" value=\"" . $baseURL . "index.php?indexAction=listaction&amp;activateList=true&amp;public=" . $public . "&amp;listname=" . $value . "\">" . $listname . "</option>");
			elseif (! (array_key_exists ( 'removeList', $_GET ) && ($_SESSION ['listname'] == $value)))
				echo ("<option value=\"" . $baseURL . "index.php?indexAction=listaction&amp;activateList=true&amp;public=" . $public . "&amp;listname=" . $value . "\">" . $listname . "</option>");
		}
		echo "</select>";
	}
	echo "</p></div></form>";
}
?>
