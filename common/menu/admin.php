<?php // admin.php - menu which allows the adminstrator to perform administrator tasks
echo "<div class=\"menuDiv\">";
echo "<p class=\"menuHead\">".LangAdminMenuTitle."</p>";
echo "<select name=\"search\" class=\"menuField menuDropdown\" onchange=\"{location = this.options[this.selectedIndex].value;}\">";
if(isset($_SESSION['deepskylog_id']))
{ echo "<option ".(($objUtil->checkGetKey('indexAction')=='default_action')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=default_action\">"."&nbsp;"."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='view_observers')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=view_observers\">".LangAdminMenuItem1."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='view_locations')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=view_locations\">".LangAdminMenuItem2."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='view_instruments')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=view_instruments\">".LangAdminMenuItem3."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='view_eyepieces')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=view_eyepieces\">".LangAdminMenuItem4."</option>";                        
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='view_filters')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=view_filters\">".LangAdminMenuItem5."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='view_lenses')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=view_lenses\">".LangAdminMenuItem6."</option>";
}
echo "</select>";
echo "</div>";
?>