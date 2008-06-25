<?php

// admin.php
// menu which allows the adminstrator to perform administrator tasks

//include_once "../lib/observers.php";
//include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

echo("<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">");

echo("<tr>");
echo("<th valign=\"top\">");
echo(LangAdminMenuTitle);
echo("</th>");
echo("</tr>");

echo("<tr>");
echo("<td>");
echo("<a href=\"common/view_observers.php\" class=\"mainlevel\">\n");
echo(LangAdminMenuItem1);
echo("</a>");
echo("</td>");
echo("</tr>");

echo("<tr align=\"left\">");
echo("<td>");
echo("<a href=\"common/view_locations.php\" class=\"mainlevel\">");
echo(LangAdminMenuItem2);
echo("</a>");
echo("</td>");
echo("</tr>");

echo("<tr align=\"left\">");
echo("<td>");
echo("<a href=\"common/view_instruments.php\" class=\"mainlevel\">");
echo(LangAdminMenuItem3);
echo("</a>");
echo("</td>");
echo("</tr>");

echo("<tr align=\"left\">");
echo("<td>");
echo("<a href=\"common/view_eyepieces.php\" class=\"mainlevel\">");
echo(LangAdminMenuItem4);
echo("</a>");
echo("</td>");
echo("</tr>");;

echo("<tr align=\"left\">");
echo("<td>");
echo("<a href=\"common/view_filters.php\" class=\"mainlevel\">");
echo(LangAdminMenuItem5);
echo("</a>");
echo("</td>");
echo("</tr>");

echo("<tr align=\"left\">");
echo("<td>");
echo("<a href=\"common/view_lenses.php\" class=\"mainlevel\">");
echo(LangAdminMenuItem6);
echo("</a>");
echo("</td>");
echo("</tr>");

echo("</table>");
			
?>
