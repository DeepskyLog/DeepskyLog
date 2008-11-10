<?php
// change.php
// menu which allows the user to add or change things in the database

echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
echo "<tr>";
echo "<th valign=\"top\">";
echo LangChangeMenuTitle;
echo "</th>"
echo "</tr>";
//echo "<tr>";
//echo "<td>";
//echo "<table>";
tableMenuItem("deepsky/index.php?indexAction=add_observation",      LangChangeMenuItem2);
tableMenuItem("deepsky/index.php?indexAction=add_object",           LangChangeMenuItem5);
tableMenuItem("common/indexCommon.php?indexAction=account_details", LangChangeMenuItem1);
tableMenuItem("common/indexCommon.php?indexAction=add_site",        LangChangeMenuItem4);
tableMenuItem("common/indexCommon.php?indexAction=add_instrument",  LangChangeMenuItem3);
tableMenuItem("common/indexCommon.php?indexAction=add_eyepiece.php",LangChangeMenuItem6);
tableMenuItem("common/indexCommon.php?indexAction=add_filter",      LangChangeMenuItem7);
tableMenuItem("common/indexCommon.php?indexAction=add_lens.php",    LangChangeMenuItem8);
//echo "</table>";
//echo "</td>";
//echo "</tr>";
echo "</table>";
?>
