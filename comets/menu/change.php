<?php 
// change.php
// menu which allows the user to add or change things in the database

global $inIndex,$loggedUser,$objUtil;

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(LangExcpetion001);
elseif(!($objUtil->checkAdminOrUserID($loggedUser))) throw new Exception(LangExcpetion012);
else comets_menu_change();

function comets_menu_change()
{ global $baseURL,$loggedUser;
	echo "<div class=\"menuDiv\">";
	echo "<p class=\"menuHead\">".LangChangeMenuTitle."</p>";
	echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=change_account\">".LangChangeMenuItem1."</a><br />";
	if($loggedUser!="admin")
	{ echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=comets_add_observation\">".LangChangeMenuItem2."</a><br />";
	}
	echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=add_instrument\">".LangChangeMenuItem3."</a><br />";
	echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=add_site\">".LangChangeMenuItem4."</a><br />";
	echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=comets_add_object\" >".LangChangeMenuItem5."</a><br />";
	echo "</div>";
	}
?>
