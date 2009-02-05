<?php
// change.php
// menu which allows the user to add or change things in the database

echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
echo "<tr>";
echo "<th valign=\"top\">";
echo LangChangeMenuTitle;
//echo "&nbsp;";
echo "</th>";
echo "</tr>";
echo "<tr>";
echo "<td>";
echo "<select style=\"width: 147px\" onchange=\"{location = this.options[this.selectedIndex].value;}\" name=\"search\" class=\"inputfield\">";
if(isset($_SESSION['deepskylog_id']))
{ echo "<option ".(($objUtil->checkGetKey('indexAction')=='default_action')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=default_action\">"."&nbsp;"."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='add_object')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=add_object\">".LangChangeMenuItem5."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='add_eyepiece')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=add_eyepiece\">".LangChangeMenuItem6."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='add_filter')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=add_filter\">".LangChangeMenuItem7."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='add_lens')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=add_lens\">".LangChangeMenuItem8."</option>";                        
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='change_account')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=change_account\">".LangChangeMenuItem1."</option>";
}
echo("</select>\n");
echo "</td>";
echo "</tr>";
echo "</table>";
?>
