<?php
// tolist.php
// manages and shows lists
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception ( LangException002 );
else
	tolist ();
function tolist() {
	global $baseURL, $loggedUser, $listname, $myList, $listname_ss, $FF, $step, $objObject, $objObserver, $objPresentations, $objUtil, $objList;
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/presentation.js\"></script>";
	echo "<div id=\"main\">";
	if ($loggedUser) {
		echo "<span class=\"form-inline\">";
		echo "<form action=\"" . $baseURL . "index.php?indexAction=listaction\"><div>";
		echo "<input type=\"hidden\" name=\"indexAction\" value=\"listaction\" />";
		$content1 = LangToListAddNew;
		$content1 .= "<input type=\"text\" class=\"form-control\" name=\"addlistname\" value=\"\" />";
		$content1 .= "&nbsp;<input type=\"checkbox\" name=\"PublicList\" value=\"" . LangToListPublic . "\" />" . LangToListPublic;
		$content1 .= "<span class=\"pull-right\">&nbsp;<input class=\"btn btn-success\" type=\"submit\" name=\"addList\" value=\"" . LangToListAdd . "\" />&nbsp;";
		if ($myList)
			$content1 .= "&nbsp;<input class=\"btn btn-success\" type=\"submit\" name=\"renameList\" value=\"" . LangToListRename . "\" />&nbsp;";
		echo "</span>";
		echo $content1;
		echo "</div></form></span>";
		echo "<hr />";
	}
	if ($listname) {
		$link = $baseURL . "index.php?indexAction=listaction";
		reset ( $_GET );
		while ( list ( $key, $value ) = each ( $_GET ) )
			if (! in_array ( $key, array (
					'addobservationstolist',
					'restoreColumns',
					'orderColumn',
					'loadLayout',
					'saveLayout',
					'removeLayout',
					'indexAction',
					'multiplepagenr',
					'sort',
					'sortdirection',
					'showPartOfs',
					'noShowName' 
			) ))
				$link .= '&amp;' . urlencode ( $key ) . '=' . urlencode ( $value );
		echo "<h4>" . LangSelectedObjectsTitle . " " . $listname_ss . "</h4>";
		$content1 = "";
		if ($myList) {
			$content1 = "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=import_csv_list\">" . LangToListImport . "</a>  ";
			$content1 .= "<a class=\"btn btn-warning\" href=\"" . $baseURL . "index.php?indexAction=listaction&amp;emptyList=emptyList\">" . LangToListEmpty . "</a>  ";
			$content1 .= "<a class=\"btn btn-danger\" href=\"" . $baseURL . "index.php?indexAction=listaction&amp;removeList=removeList\">" . LangToListMyListsRemove . "</a>  ";
			$content1 .= "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=listaction&amp;addobservationstolist=longest\">" . LangToListMyListsAddLongestObsDescription . "</a>  ";
			$content1 .= "<a class=\"btn btn-danger\" href=\"" . $baseURL . "index.php?indexAction=listaction&amp;removeobservationsfromlist=all\">" . LangToListMyListsRemoveObsDescription . "</a>";
		} else
			$content1 = "(" . LangToListListBy . $objObserver->getObserverProperty ( ($listowner = $objList->getListOwner ()), 'firstname' ) . ' ' . $objObserver->getObserverProperty ( $listowner, 'name' ) . ")";
		$content1 .= "  <a class=\"btn btn-success\" href=\"" . $link . "&amp;noShowName=noShowName\">" . LangListQueryObjectsMessage17 . "</a>";
		echo $content1;
		
		if (count ( $_SESSION ['Qobj'] ) > 0) { // OUTPUT RESULT
			echo "<hr />";
			$objObject->showObjects ( $link, '', 1, "removePageObjectsFromList", "tolist" );
			echo "<hr />";
		} else {
			echo "<hr />";
			echo LangToListEmptyList;
		}
	}
	echo "</div>";
}
?>
