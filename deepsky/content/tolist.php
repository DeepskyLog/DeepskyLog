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
	global $baseURL, $loggedUser, $listname, $myList, $listname_ss, $FF, $step, $objObject, $objObserver, $objPresentations, $objUtil, $objList, $instDir;
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/presentation.js\"></script>";
	echo "<div id=\"main\">";
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
		$listowner = $objList->getListOwner ();
		
		if ($myList) {
			$content1 = "<a class=\"btn btn-danger\" href=\"" . $baseURL . "index.php?indexAction=listaction&amp;removeList=removeList\"><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span>&nbsp;" . LangToListMyListsRemove . "</a>  ";
			// TODO: Make renaming the list work! We should move the changeListName modal to index.php
			$content1 .= "<button type=\"button\" title=\"" . LangChangeName . "\" class=\"btn btn-warning\" data-toggle=\"modal\" data-target=\"#changeListName" . str_replace ( ' ', '_', str_replace ( ':', '_', $listname ) ) . "\" >
							<span class=\"glyphicon glyphicon-pencil\"></span>&nbsp;" . LangToListRename . "
                      	  </button>&nbsp;";
				
			// Add a button to change from private to public or vice-versa
			// TODO: Make this button work!
			if ($objList->isPublic($listname_ss)) {
				$content1 .= "<a class=\"btn btn-warning\" href=\"" . $baseURL . "index.php?indexAction=listaction&amp;removeList=removeList\"><span class=\"glyphicon glyphicon-user\" aria-hidden=\"true\"></span>&nbsp;Make private</a>  ";
			} else {
				$content1 .= "<a class=\"btn btn-warning\" href=\"" . $baseURL . "index.php?indexAction=listaction&amp;removeList=removeList\"><span class=\"glyphicon glyphicon-share\" aria-hidden=\"true\"></span>&nbsp;Make public</a>  ";
			}
			// Add a button to create a new list
			// TODO: Make this button work!
			$content1 .= "<a class=\"btn btn-success pull-right\" href=\"" . $baseURL . "index.php?indexAction=listaction&amp;removeList=removeList\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span>&nbsp;Add new list</a>  ";
				
			$content2 = "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=import_csv_list\">" . LangToListImport . "</a>  ";
			$content2 .= "<a class=\"btn btn-warning\" href=\"" . $baseURL . "index.php?indexAction=listaction&amp;emptyList=emptyList\">" . LangToListEmpty . "</a>  ";
			$content2 .= "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=listaction&amp;addobservationstolist=longest\">" . LangToListMyListsAddLongestObsDescription . "</a>  ";
			$content2 .= "<a class=\"btn btn-danger\" href=\"" . $baseURL . "index.php?indexAction=listaction&amp;removeobservationsfromlist=all\">" . LangToListMyListsRemoveObsDescription . "</a>";
			
			echo $content1 . "<br /><br />";
			echo $content2;
		} else {
			// Show a picture of the creator of the list.
			$dir = opendir ( $instDir . 'common/observer_pics' );
			while ( FALSE !== ($file = readdir ( $dir )) ) {
				if (("." == $file) or (".." == $file))
					continue; // skip current directory and directory above
				if (fnmatch ( $listowner . ".gif", $file ) || fnmatch ( $listowner . ".jpg", $file ) || fnmatch ( $listowner . ".png", $file )) {
					echo "<img height=\"72\" src=\"" . $baseURL . "/common/observer_pics/" . $file . "\" class=\"img-rounded pull-right\">";
				}
			}
			
			// Add a link to send a message to the creator of the list.
			$name = LangToListListBy . "<a href=\"" . $baseURL . "/index.php?indexAction=new_message&receiver=" . $listowner . "\">";
			$name .= $objObserver->getObserverProperty ( $listowner, 'firstname' ) . ' ' . $objObserver->getObserverProperty ( $listowner, 'name' ) . "</a>";
			echo "(" . $name . ")";
		}
		
		echo "<br /><br /><br />";
		if (count ( $_SESSION ['Qobj'] ) > 0) { // OUTPUT RESULT
			echo "<hr />";
			$objObject->showObjects ( $link, '', 1, "removePageObjectsFromList", "tolist", true );
			echo "<hr />";
		} else {
			echo "<hr />";
			echo LangToListEmptyList;
		}
	}
	echo "</div>";
}
?>
