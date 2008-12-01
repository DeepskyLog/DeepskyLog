<?php
// admin.php
// menu which allows the adminstrator to perform administrator tasks

echo("<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">");

echo("<tr>");
echo("<th valign=\"top\">");
echo(LangAdminMenuTitle);
echo("</th>");
echo("</tr>");

echo("<tr>");
echo("<td height=\"25px\">");
echo("<a href=\"".$baseURL."index.php?indexAction=view_observers\" class=\"mainlevel\">\n");
echo(LangAdminMenuItem1);
echo("</a>");
echo("</td>");
echo("</tr>");

echo("<tr align=\"left\">");
echo("<td height=\"25px\">");
echo("<a href=\"".$baseURL."index.php?indexAction=view_locations\" class=\"mainlevel\">");
echo(LangAdminMenuItem2);
echo("</a>");
echo("</td>");
echo("</tr>");

echo("<tr align=\"left\">");
echo("<td height=\"25px\">");
echo("<a href=\"".$baseURL."index.php?indexAction=view_instruments\" class=\"mainlevel\">");
echo(LangAdminMenuItem3);
echo("</a>");
echo("</td>");
echo("</tr>");

echo("<tr align=\"left\">");
echo("<td height=\"25px\">");
echo("<a href=\"".$baseURL."index.php?indexAction=view_eyepieces\" class=\"mainlevel\">");
echo(LangAdminMenuItem4);
echo("</a>");
echo("</td>");
echo("</tr>");;

echo("<tr align=\"left\">");
echo("<td height=\"25px\">");
echo("<a href=\"".$baseURL."index.php?indexAction=view_filters\" class=\"mainlevel\">");
echo(LangAdminMenuItem5);
echo("</a>");
echo("</td>");
echo("</tr>");

echo("<tr align=\"left\">");
echo("<td height=\"25px\">");
echo("<a href=\"".$baseURL."index.php?indexAction=view_lenses\" class=\"mainlevel\">");
echo(LangAdminMenuItem6);
echo("</a>");
echo("</td>");
echo("</tr>");

echo("</table>");
			
?>
