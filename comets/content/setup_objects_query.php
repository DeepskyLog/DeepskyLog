<?php
// setup_objects_query.php
// interface to query comets

global $inIndex,$loggedUser,$objUtil;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else setup_objects_query();

function setup_objects_query()
{ global $baseURL,
         $objPresentations;
	$_SESSION['result'] = "";
	echo "<div id=\"main\">";
	echo "<form action=\"".$baseURL."index.php\" method=\"get\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"comets_result_query_objects\" />";
	echo "<h4>"._("Search objects")."</h4>";
	echo "<input type=\"submit\" class=\"btn btn-success pull-right\" name=\"query\" value=\"" . _("Search") . "\" />";
	echo "<br /><hr />";
	// OBJECT NAME
	$content="<input type=\"text\" class=\"form-control\" maxlength=\"40\" name=\"name\" size=\"40\" value=\"\" />";
	echo "<strong>" . _("Object name") . "</strong>";
	echo $content;;
	$content="<input type=\"text\" class=\"form-control\" maxlength=\"40\" name=\"icqname\" size=\"40\" value=\"\" />";
	echo "<strong>" . _("ICQ name") . "</strong>";
	echo $content;
	echo "<hr />";
	echo "</div>";
  echo "<input type=\"submit\" class=\"btn btn-success\" name=\"query\" value=\"" . _("Search") . "\" />";
  echo "</form>";
	echo "</div>";
}
?>
