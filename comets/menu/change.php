<?php
// change.php
// menu which allows the user to add or change things in the database

global $inIndex,$loggedUser,$objUtil;

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(LangException001);
elseif(!($objUtil->checkAdminOrUserID($loggedUser))) throw new Exception(LangException012);
else comets_menu_change();

function comets_menu_change()
{ global $baseURL,$loggedUser;
	echo "<ul class=\"nav navbar-nav\">
			  <li class=\"dropdown\">
	       <a href=\"http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] ."#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">" . LangChangeMenuTitle ."<b class=\"caret\"></b></a>";
	echo " <ul class=\"dropdown-menu\">";
	if($loggedUser!="admin")
	{ echo "  <li><a href=\"".$baseURL."index.php?indexAction=comets_add_observation\">".LangChangeMenuItem2."</a></li>";
	}
	echo "  <li><a href=\"".$baseURL."index.php?indexAction=add_instrument\">".LangChangeMenuItem3."</a></li>";
	echo "  <li><a href=\"".$baseURL."index.php?indexAction=add_site\">".LangChangeMenuItem4."</a></li>";
	echo "  <li><a href=\"".$baseURL."index.php?indexAction=comets_add_object\" >".LangChangeMenuItem5."</a></li>";
	echo " </ul>";
	echo "</li>
			  </ul>";
}
?>
