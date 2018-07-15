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
				'multiplepagenr',
				'noShowName'
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
		echo "<a href=\"" . $link . "&amp;noShowName=noShowName\" class=\"btn btn-primary\">" . _("Switch names and alternative names") . "</a>";
		echo $addButtons;
		echo "</span><br />";

		echo "<hr />";

		$objObject->showObjects ( $link, '', 0, '', "selected_objects" );
		echo "<hr />";

		echo "</div>";
	} else 	// ========================================================================no results found
	{
		echo "<div id=\"main\">";
		echo "<h4>" . LangSelectedObjectsTitle . "</h4>";
		echo LangExecuteQueryObjectsMessage2;
		echo "<a href=\"" . $baseURL . "index.php?indexAction=query_objects\">" . LangExecuteQueryObjectsMessage2a . "</a>";
		echo "</div>";
	}
}
?>
