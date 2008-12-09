<?php

// setup_objects_query.php
// interface to query comets
// version 0.4: 2005/09/21, WDM

include_once "lib/cometobjects.php";
include_once "lib/util.php";

$util = new Util();
$util->checkUserInput();

$objects = new CometObjects; 

$_SESSION['result'] = "";

echo("<div id=\"main\">\n");
echo("<h2>");

echo LangQueryObjectsTitle;

echo("</h2>\n");

echo("<table width=\"490\">\n");

echo("<form action=\"".$baseURL."index.php?indexAction=comets_result_query_objects\" method=\"get\">\n");

// OBJECT NAME 

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangQueryObjectsField1;

echo("</td>\n<td colspan=\"2\">\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"40\" name=\"name\" size=\"40\" value=\"\" />");

echo("</td>\n</tr>\n");

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangNewObjectIcqname;

echo("</td>\n<td colspan=\"2\">\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"40\" name=\"icqname\" size=\"40\" value=\"\" />");

echo("</td>\n</tr>\n");

echo("<tr>\n<td>\n</td><td><input type=\"submit\" name=\"query\" value=\"" . LangQueryObjectsButton1 . "\" />\n</td>\n<td></td></tr></form><tr><td></td><td><form action=\"".$baseURL."index.php?indexAction=comets_query_objects\"><input type=\"submit\" name=\"clear\" value=\"" . LangQueryObjectsButton2 . "\" />\n</form>\n</td><td></td></tr></table>");

echo("</div>\n</div>\n</body>\n</html>");
?>
