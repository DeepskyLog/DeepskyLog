<?php 
// admin.php
// menu which allows the adminstrator to perform administrator tasks

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
elseif($_SESSION['admin']!="yes") throw new Exception(LangException001);
else admin();

function admin()
{ global $baseURL,menuAdmin,
         $objUtil;
	echo "<div class=\"menuDiv\">";
	reset($_GET);
	$link="";
	while(list($key,$value)=each($_GET))
	  if($key!="menuAdmin")
	    $link.="&amp;".$key."=".urlencode($value);
	reset($_GET);
	echo "<p  class=\"menuHead\">";
	if($menuAdmin=="collapsed")
	  echo "<a href=\"".$baseURL."index.php?menuAdmin=expanded".$link."\" title=\"".LangMenuExpand."\">+</a> ";
	else
	  echo "<a href=\"".$baseURL."index.php?menuAdmin=collapsed".$link."\" title=\"".LangMenuCollapse."\">-</a> ";
	echo LangAdminMenuTitle."</p>";
	if($menuAdmin=="collapsed")
	{ echo "<select name=\"search\" class=\"menuField menuDropdown\" onchange=\"{location = this.options[this.selectedIndex].value;}\">";
		echo "<option ".(($objUtil->checkGetKey('indexAction')=='default_action')?"selected=\"selected\" ":"")."value=\"".$baseURL."index.php?indexAction=default_action\">"."&nbsp;"."</option>";
		echo "<option ".(($objUtil->checkGetKey('indexAction')=='view_observers')?"selected=\"selected\" ":"")."value=\"".$baseURL."index.php?indexAction=view_observers\">".LangAdminMenuItem1."</option>";
	  echo "<option ".(($objUtil->checkGetKey('indexAction')=='view_locations')?"selected=\"selected\" ":"")."value=\"".$baseURL."index.php?indexAction=view_locations\">".LangAdminMenuItem2."</option>";
	  echo "<option ".(($objUtil->checkGetKey('indexAction')=='view_instruments')?"selected=\"selected\" ":"")."value=\"".$baseURL."index.php?indexAction=view_instruments\">".LangAdminMenuItem3."</option>";
	  echo "<option ".(($objUtil->checkGetKey('indexAction')=='view_eyepieces')?"selected=\"selected\" ":"")."value=\"".$baseURL."index.php?indexAction=view_eyepieces\">".LangAdminMenuItem4."</option>";                        
	  echo "<option ".(($objUtil->checkGetKey('indexAction')=='view_filters')?"selected=\"selected\" ":"")."value=\"".$baseURL."index.php?indexAction=view_filters\">".LangAdminMenuItem5."</option>";
	  echo "<option ".(($objUtil->checkGetKey('indexAction')=='view_lenses')?"selected=\"selected\" ":"")."value=\"".$baseURL."index.php?indexAction=view_lenses\">".LangAdminMenuItem6."</option>";
	  echo "<option ".(($objUtil->checkGetKey('indexAction')=='admin_check_objects')?"selected=\"selected\" ":"")."value=\"".$baseURL."index.php?indexAction=admin_check_objects\">"."Check Objects"."</option>";
	  echo "</select>";
	}
	else
	{ echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=view_observers\">".LangAdminMenuItem1."</a><br />";
	  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=view_locations\">".LangAdminMenuItem2."</a><br />";
	  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=view_instruments\">".LangAdminMenuItem3."</a><br />";
	  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=view_eyepieces\">".LangAdminMenuItem4."</a><br />";
	  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=view_filters\">".LangAdminMenuItem5."</a><br />";
	  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=view_lenses\">".LangAdminMenuItem6."</a><br />";
	}
	echo "</div>";
}
?>