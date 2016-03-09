<?php
// tolist.php
// manages and shows lists
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
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
			// Add a button to remove the list.
			$content1 = "<a class=\"btn btn-danger\" href=\"" . $baseURL . "index.php?indexAction=listaction&amp;removeList=removeList\"><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span>&nbsp;" . LangToListMyListsRemove . "</a>  ";
			// Add a button to rename the list.
			$content1 .= "<button type=\"button\" title=\"" . LangChangeName . "\" class=\"btn btn-warning\" data-toggle=\"modal\" data-target=\"#changeListName" . str_replace ( ' ', '_', str_replace ( ':', '_', $listname ) ) . "\" >
							<span class=\"glyphicon glyphicon-pencil\"></span>&nbsp;" . LangToListRename . "
                      	  </button>&nbsp;";

			// Add a button to change from private to public or vice-versa
			if ($objList->isPublic ( $listname )) {
				$content1 .= "<a class=\"btn btn-warning\" href=\"" . $baseURL . "index.php?indexAction=listaction&amp;switchPublicPrivateList=switchPublicPrivateList&amp;listname=" . $listname . "\"><span class=\"glyphicon glyphicon-user\" aria-hidden=\"true\"></span>&nbsp;" . LangMakePrivate . "</a>  ";
			} else {
				$content1 .= "<a class=\"btn btn-warning\" href=\"" . $baseURL . "index.php?indexAction=listaction&amp;switchPublicPrivateList=switchPublicPrivateList&amp;listname=" . $listname . "\"><span class=\"glyphicon glyphicon-share\" aria-hidden=\"true\"></span>&nbsp;" . LangMakePublic . "</a>  ";

			}

			// Add a button to create a new list
			$content1 .= "<button type=\"button\" title=\"" . LangChangeName . "\" class=\"btn btn-success pull-right\" data-toggle=\"modal\" data-target=\"#addList\" >
							<span class=\"glyphicon glyphicon-plus\"></span>&nbsp;" . LangNewList . "
                      	  </button>&nbsp;";

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

		echo "<div class=\"modal fade\" id=\"changeListName" . str_replace ( ' ', '_', str_replace ( ':', '_', $listname ) ) . "\">
                       <div class=\"modal-dialog\">
                        <div class=\"modal-content\">
                         <div class=\"modal-header\">
                          <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>
                          <h4 class=\"modal-title\">" . LangChangeName . "</h4>
                         </div>
                         <div class=\"modal-body\">
                          <!-- Ask for the new name of the list. -->
                          <h1 class=\"text-center login-title\">" . LangNewNameList . "</h1>
                          <form action=\"" . $baseURL . "index.php?indexAction=listaction\">
                           <input type=\"hidden\" name=\"indexAction\" value=\"listaction\" />
													 <input type=\"hidden\" name=\"listnamefrom\" value=\"" . $listname . "\" />";

		$publicList = $objList->isPublic ( $listname );
		$listToPrint = $listname;
		echo "     <input type=\"text\" name=\"addlistname\" class=\"form-control\" required autofocus value=\"" . $listToPrint . "\">
                           <br /><br />
                           <input type=\"checkbox\" ";
		if ($publicList) {
			echo "checked ";
		}
		echo "    name=\"PublicList\" value=\"1\" />&nbsp;" . LangToListPublic . "
                          </div>
                          <div class=\"modal-footer\">
                           <button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Close</button>
                           <input class=\"btn btn-success\" type=\"submit\" name=\"renameList\" value=\"" . LangToListRename . "\" /></button>
   		                  </form>
                         </div>
                        </div><!-- /.modal-content -->
                       </div><!-- /.modal-dialog -->
                      </div><!-- /.modal -->";
	}
	echo "</div>";
}
?>
