<?php // change.php - menu which allows the user to add or change things in the database
echo "<div class=\"menuDiv\">";
echo "<p class=\"menuHead\">".LangChangeMenuTitle."</p>";
echo "<select name=\"search\" class=\"menuField menuDropdown\" onchange=\"{location=this.options[this.selectedIndex].value;}\">";
if(isset($_SESSION['deepskylog_id']))
{ echo "<option ".(($objUtil->checkGetKey('indexAction')=='default_action')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=default_action\">"."&nbsp;"."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='add_object')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=add_object\">".LangChangeMenuItem5."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='add_eyepiece')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=add_eyepiece\">".LangChangeMenuItem6."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='add_filter')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=add_filter\">".LangChangeMenuItem7."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='add_lens')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=add_lens\">".LangChangeMenuItem8."</option>";                        
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='change_account')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=change_account\">".LangChangeMenuItem1."</option>";
}
echo "</select>";
echo "</div>";
?>
