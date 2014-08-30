<?php
// selected_objects.php
// executes the object query passed by setup_query_objects.php
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	selected_objects ();
function selected_objects() {
	global $baseURL, $showPartOfs, $myList, $listname_ss, $FF, $objObject, $objPresentations, $objUtil;

	$link = $baseURL . "index.php?indexAction=query_objects";
	reset ( $_GET );
	while ( list ( $key, $value ) = each ( $_GET ) )
		if (! (in_array ( $key, array (
				'formName',
				'layoutName',
				'restoreColumns',
				'orderColumns',
				'loadLayout',
				'saveLayout',
				'removeLayout',
				'sort',
				'soretorder',
				'multiplepagenr',
				'noShowName',
				'sortdirection' 
		) )))
			$link .= '&amp;' . urlencode ( $key ) . '=' . urlencode ( $value );
	if (count ( $_SESSION ['Qobj'] ) > 1) 	// =============================================== valid result, multiple objects found
	{
		echo "<div>";
		$title = "<h4>" . LangSelectedObjectsTitle;
		if ($showPartOfs)
			$title .= LangListQueryObjectsMessage10;
		else
			$title .= LangListQueryObjectsMessage11;
		$title .= "</h4>";
		
		echo $title;
		
		if ($myList) {
			$addButtons = "&nbsp;<a href=\"" . $link . "&amp;addAllObjectsFromQueryToList=true\" title=\"" . LangListQueryObjectsMessage5 . $listname_ss . "\" class=\"btn btn-primary\">" . LangListQueryObjectsMessage4 . "</a>";
		} else {
			$addButtons = "";
		}
		echo "<span class=\"pull-right\">";
		if ($showPartOfs) {
			echo "<a href=\"" . $link . "&amp;showPartOfs=0\" class=\"btn btn-primary\">" . LangListQueryObjectsMessage12 . "</a>&nbsp;";
		} else {
			echo "<a href=\"" . $link . "&amp;showPartOfs=1\" class=\"btn btn-primary\">" . LangListQueryObjectsMessage13 . "</a>&nbsp;";
		}
		echo "<a href=\"" . $link . "&amp;noShowName=noShowName\" class=\"btn btn-primary\">" . LangListQueryObjectsMessage17 . "</a>";
		echo $addButtons;
		echo "</span><br />";
		
		echo "<hr />";

		$objObject->showObjects ( $link, '', 0, '', "selected_objects" );
		echo "<hr />";
		/*
		 * $content1 =LangExecuteQueryObjectsMessage4."&nbsp;"; $content1.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objects.pdf.php?SID=Qobj",LangExecuteQueryObjectsMessage4a); $content1.="&nbsp;-&nbsp;"; $content1.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objectnames.pdf.php?SID=Qobj",LangExecuteQueryObjectsMessage4b); $content1.="&nbsp;-&nbsp;"; $content1.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objectsDetails.pdf.php?SID=Qobj&amp;sort=".$_SESSION['QobjSort'],LangExecuteQueryObjectsMessage4c); $content1.="&nbsp;-&nbsp;"; $content1.="<a href=\"".$baseURL."objects.argo?SID=Qobj\" rel=\"external\">".LangExecuteQueryObjectsMessage8."</a>"; $content1.="&nbsp;-&nbsp;"; if(array_key_exists('listname',$_SESSION)&&$_SESSION['listname']&&$myList) $content1.="<a href=\"".$link."&amp;min=".$min."&amp;addAllObjectsFromQueryToList=true\" title=\"".LangListQueryObjectsMessage5.$_SESSION['listname']."\">".LangListQueryObjectsMessage4."</a>"."&nbsp;-&nbsp;"; $content1.="<a href=\"".$baseURL."objects.csv?SID=Qobj\" rel=\"external\">".LangExecuteQueryObjectsMessage6."</a>"; if($loggedUser) $content1.="&nbsp;-&nbsp;<a href=\"".$baseURL."index.php?indexAction=reportsLayout&amp;reportname=ReportQueryOfObjects&amp;reporttitle=ReportQueryOfObjects&amp;SID=Qobj&amp;sort=".$_SESSION['QobjSort']."&amp;pdfTitle=Test\" >".ReportLink."</a>"; $content1.="&nbsp;-&nbsp;<a href=\"".$baseURL."index.php?indexAction=objectsSets"."\" rel=\"external\">".LangExecuteQueryObjectsMessage11."</a>"; $objPresentations->line(array($content1),"L",array(100),20);
		 */
		
		echo "</div>";
	} else 	// ========================================================================no results found
	{
		echo "<div id=\"main\">";
		$objPresentations->line ( "<h4>" . LangSelectedObjectsTitle . "</h4>", "L", array (), 30 );
		$objPresentations->line ( array (
				LangExecuteQueryObjectsMessage2 
		), "L" );
		$objPresentations->line ( array (
				"<a href=\"" . $baseURL . "index.php?indexAction=query_objects\">" . LangExecuteQueryObjectsMessage2a . "</a>" 
		), "L" );
		echo "</div>";
	}
}
?>
