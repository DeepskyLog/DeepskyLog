<?php 
// validation.php
// dispalys the W3C logos

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else validationmenu();

function validationmenu()
{ global $baseURL;
	echo "<div class=\"menuDiv\">";
	echo "<p class=\"centered\">";
	echo "<a class=\"w3c\" href=\"http://validator.w3.org/check?uri=referer\">";
	echo "<img class=\"w3c\" src=\"".$baseURL."styles/images/valid-xhtml10S.png\" alt=\"Valid XHTML 1.0 Strict\" />";
	echo "</a>";
	echo "&nbsp;";
	echo "<a class=\"w3c\" href=\"http://jigsaw.w3.org/css-validator/check/referer\">";
	echo "<img class=\"w3c\" src=\"".$baseURL."styles/images/vcssS.gif\" alt=\"Valid CSS\" />";
	echo "</a>";
	echo "</p>";
	echo "</div>";
}
?>