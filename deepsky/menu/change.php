<?php
// change.php
// menu which allows the user to add or change things in the database

echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
echo "<tr>";
echo "<th valign=\"top\">";
echo LangChangeMenuTitle;
echo "</th>";
echo "</tr>";
//echo "<tr>";
//echo "<td>";
//echo "<table>";
tableMenuItem($baseURL."deepsky/index.php?indexAction=add_observation",      LangChangeMenuItem2);
tableMenuItem($baseURL."deepsky/index.php?indexAction=add_object",           LangChangeMenuItem5);
tableMenuItem($baseURL."common/indexCommon.php?indexAction=account_details", LangChangeMenuItem1);
tableMenuItem($baseURL."common/indexCommon.php?indexAction=add_site",        LangChangeMenuItem4);
tableMenuItem($baseURL."common/indexCommon.php?indexAction=add_instrument",  LangChangeMenuItem3);
tableMenuItem($baseURL."common/indexCommon.php?indexAction=add_eyepiece.php",LangChangeMenuItem6);
tableMenuItem($baseURL."common/indexCommon.php?indexAction=add_filter",      LangChangeMenuItem7);
tableMenuItem($baseURL."common/indexCommon.php?indexAction=add_lens",        LangChangeMenuItem8);
//echo "</table>";
//echo "</td>";
//echo "</tr>";
echo "</table>";
?>
