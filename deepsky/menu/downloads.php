<?php
// search.php
// menu which allows the user to search the observation database 

global $inIndex,$loggedUser,$objUtil;

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else menu_downloads();

function menu_downloads()
{ global $loggedUser,$menuDownloads,$baseURL;
	$link="";
	reset($_GET);
	while(list($key,$value)=each($_GET))
	  if($key!="menuDownloads")
	    $link.="&amp;".$key."=".urlencode($value);
	echo "<div class=\"menuDiv\">";
	echo "<p   class=\"menuHead\">";
	if($menuDownloads=="collapsed")
	  echo "<a href=\"".$baseURL."index.php?menuDownloads=expanded".$link."\" title=\"".LangMenuExpand."\">+</a> ";
	else
	  echo "<a href=\"".$baseURL."index.php?menuDownloads=collapsed".$link."\" title=\"".LangMenuCollapse."\">-</a> ";
	echo LangDownloadsMenuTitle."</p>";
	if($menuDownloads=="collapsed")
	{ echo "<select name=\"view\" class=\"menuField menuDropdown\" onchange=\"{location=this.options[this.selectedIndex].value;}\">";
	  echo "<option value=\"".$baseURL."index.php\">"."&nbsp;"."</option>";
	  echo "<option value=\"".$baseURL."index.php?indexAction=downloadAstroImageCatalogs\">".LangSearchMenuItem14."</option>";
	  echo "<option value=\"".$baseURL."index.php?indexAction=view_atlaspages\">".LangSearchMenuItem13."</option>";
	  echo "</select>";
	}
	else
	{ echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=downloadAstroImageCatalogs\">".LangSearchMenuItem14."</a><br />";
	  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=view_atlaspages\">".LangSearchMenuItem13."</a><br />";
	}
	echo "</div>";
}
?>