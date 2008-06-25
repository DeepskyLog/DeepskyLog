<?php

// new_object.php
// allows the user to add a comet to the database 
// Version 0.1: 2005/09/21, WDM

include_once "../lib/cometobjects.php";
include_once "../lib/setup/language.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

$objects = new CometObjects; 

echo("<div id=\"main\">\n<h2>");

echo (LangNewObjectTitle); 

echo("</h2>\n<table width=\"490\">\n");

echo("<form action=\"comets/control/validate_object.php\" method=\"post\">");

// NAME

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObjectField1 . "&nbsp;*";

echo("</td>\n<td>");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"40\" name=\"name\" size=\"40\" value=\"\" />");


echo("</td>\n</tr>\n");

// ICQNAME

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangNewObjectIcqname . "&nbsp;";

echo("</td>\n<td>");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"40\" name=\"icqname\" size=\"40\" value=\"\" />");


echo("</td>\n</tr>\n");

echo("<tr>\n<td>\n</td><td><input type=\"submit\" name=\"newobject\" value=\"" . LangNewObjectButton1 . "\" />\n</td><td></td></tr><tr><td></td><td><input type=\"submit\" name=\"clearfields\" value=\"" . LangQueryObjectsButton2 . "\" />\n</td><td></td></tr></form></table>");

echo("</div>\n</div>\n</body>\n</html>");

?>
