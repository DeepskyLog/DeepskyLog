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
			/*
			 * $content=LangExecuteQueryObjectsMessage4."&nbsp;"; $content.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,$listname_ss,$baseURL."objects.pdf.php?SID=Qobj",LangExecuteQueryObjectsMessage4a); $content.="&nbsp;-&nbsp;"; $content.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,$listname_ss,$baseURL."objectnames.pdf.php?SID=Qobj",LangExecuteQueryObjectsMessage4b); $content.="&nbsp;-&nbsp;"; $content.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,$listname_ss,$baseURL."objectsDetails.pdf.php?SID=Qobj&amp;sort=" . $_SESSION['QobjSort'],LangExecuteQueryObjectsMessage4c); $content.="&nbsp;-&nbsp;"; $content.="<a href=\"objects.argo?SID=Qobj\" rel=\"external\">".LangExecuteQueryObjectsMessage8."</a> &nbsp;-&nbsp;"; $content.="<a href=\"objectslist.csv?SID=Qobj\" rel=\"external\">".LangExecuteQueryObjectsMessage6."</a>"; if($loggedUser) $content.="&nbsp;-&nbsp;<a href=\"".$baseURL."index.php?indexAction=reportsLayout&amp;reportname=ReportQueryOfObjects&amp;reporttitle=ReportQueryOfObjects&amp;SID=Qobj&amp;sort=".$_SESSION['QobjSort']."&amp;pdfTitle=Test\" >".ReportLink."</a>"; $content.="&nbsp;-&nbsp;"; $content.="<a href=\"".$baseURL."index.php?indexAction=objectsSets"."\" rel=\"external\">".LangExecuteQueryObjectsMessage11."</a>"; $objPresentations->line(array($content),"L",array(),30);
			 */
		} else {
			echo "<hr />";
			echo LangToListEmptyList; 
		}
		echo "<script type=\"text/javascript\">";
		echo "
	  function pageOnKeyDownToList(event)
	  { if(event.keyCode==37)
	      if(event.shiftKey)
	        if(event.ctrlKey)
	          location=html_entity_decode('" . $link . "&amp;multiplepagenr=0" . "');    
	        else
	          location=html_entity_decode('" . $link . "&amp;multiplepagenr=" . $pageleft . "');
	    if(event.keyCode==39)
	      if(event.shiftKey) 
	        if(event.ctrlKey)
	          location=html_entity_decode('" . $link . "&amp;multiplepagenr=" . $pagemax . "');
	        else  
	          location=html_entity_decode('" . $link . "&amp;multiplepagenr=" . $pageright . "');
	  }
	  this.onKeyDownFns[this.onKeyDownFns.length] = pageOnKeyDownToList;
	  ";
		echo "</script>";
	}
	echo "</div>";
}
?>
