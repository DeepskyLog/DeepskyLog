<?php
// new_object.php
// allows the user to add a comet to the database

global $inIndex,$loggedUser,$objUtil;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(LangException001);
else new_object();

function new_object()
{ global $baseURL,
         $objPresentations;
	echo "<div id=\"main\">";
	echo "<form action=\"".$baseURL."index.php?indexAction=comets_validate_object\" method=\"post\">";
	echo "<h4>".LangNewObjectTitle."</h4>";
  echo "<input type=\"submit\" class=\"btn btn-success pull-right\" name=\"newobject\" value=\"" . LangViewObservationButton1 . "\" />";
  echo "<br /><hr />";
	$content="<input type=\"text\" required class=\"form-control\" name=\"name\" value=\"\" />";
	echo "<strong>" . LangViewObjectField1."&nbsp;*</strong>";
	echo $content;
	$content="<input type=\"text\" required class=\"form-control\" name=\"icqname\" value=\"\" />";
	echo "<strong>" . LangNewObjectIcqname."&nbsp;*</strong>";
	echo $content;
  echo "<br /><br /><input type=\"submit\" class=\"btn btn-success\" name=\"newobject\" value=\"" . LangViewObservationButton1 . "\" />";
	echo "<hr />";
	echo "</form>";
	echo "</div>";
}
?>
