<?php // change.php - menu which allows the user to add or change things in the database
if($loggedUser)
{
echo "<div class=\"menuDiv\">";
reset($_GET);
$link="";
while(list($key,$value)=each($_GET))
  if($key!="menuAddChange")
    $link.="&amp;".$key."=".urlencode($value);
reset($_GET);
echo "<p  class=\"menuHead\">";
if($menuAddChange=="collapsed")
  echo "<a href=\"".$baseURL."index.php?menuAddChange=expanded".$link."\" title=\"".LangMenuExpand."\">+</a> ";
else
  echo "<a href=\"".$baseURL."index.php?menuAddChange=collapsed".$link."\" title=\"".LangMenuCollapse."\">-</a> ";
echo LangChangeMenuTitle."</p>";
if($menuAddChange=="collapsed")
{ echo "<select name=\"search\" class=\"menuField menuDropdown\" onchange=\"{location=this.options[this.selectedIndex].value;}\">";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='default_action')?"selected=\"selected\" ":"")."value=\"".$baseURL."index.php?indexAction=default_action\">"."&nbsp;"."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='add_object')?"selected=\"selected\" ":"")."value=\"".$baseURL."index.php?indexAction=add_object&amp;title=".urlencode(LangChangeMenuItem5)."\">".LangChangeMenuItem5."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='add_eyepiece')?"selected=\"selected\" ":"")."value=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;title=".urlencode(LangChangeMenuItem6)."\">".LangChangeMenuItem6."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='add_filter')?"selected=\"selected\" ":"")."value=\"".$baseURL."index.php?indexAction=add_filter&amp;title=".urlencode(LangChangeMenuItem7)."\">".LangChangeMenuItem7."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='add_lens')?"selected=\"selected\" ":"")."value=\"".$baseURL."index.php?indexAction=add_lens&amp;title=".urlencode(LangChangeMenuItem8)."\">".LangChangeMenuItem8."</option>";                        
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='change_account')?"selected=\"selected\" ":"")."value=\"".$baseURL."index.php?indexAction=change_account&amp;title=".urlencode(LangChangeMenuItem1)."\">".LangChangeMenuItem1."</option>";
  echo "</select>";
}
else
{ echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=add_object&amp;title=".urlencode(LangChangeMenuItem5)."\">".LangChangeMenuItem5."</a><br />";
  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;title=".urlencode(LangChangeMenuItem6)."\">".LangChangeMenuItem6."</a><br />";
  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=add_filter&amp;title=".urlencode(LangChangeMenuItem7)."\">".LangChangeMenuItem7."</a><br />";
  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=add_lens&amp;title=".urlencode(LangChangeMenuItem8)."\">".LangChangeMenuItem8."</a><br />";
  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=change_account&amp;title=".urlencode(LangChangeMenuItem1)."\">".LangChangeMenuItem1."</a><br />";
}
echo "</div>";
}
?>
