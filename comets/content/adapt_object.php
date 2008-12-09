<?php

// adapt_object.php
// allows the administrator to change a comet in the database 

session_start(); // start session

include_once "lib/cometobjects.php";
include_once "lib/setup/language.php";
include_once "lib/util.php";

$util = new Util();
$util->checkUserInput();

$objects = new CometObjects; 

echo("<div id=\"main\">\n<h2>");

echo (LangChangeObject . " " . $objects->getName($_GET['object'])); 

echo("</h2>\n<table width=\"490\">\n");

echo("<form action=\"".$baseURL."index.php\" method=\"post\">");
echo "<input type=\"hidden\" name=\"indexAction\" value=\"comets_validate_change_object\" />";

// NAME

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObjectField1 . "&nbsp;*";

echo("</td>\n<td>");

echo("<input type=\"hidden\" name=\"object\" value=\"" . $_GET['object'] . "\" />");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"40\" name=\"name\" size=\"40\" value=\"" . $objects->getName($_GET['object']) . "\" />");


echo("</td>\n</tr>\n");

// ICQNAME

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangNewObjectIcqname . "&nbsp;";

echo("</td>\n<td>");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"40\" name=\"icqname\" size=\"40\" value=\"" . $objects->getIcqName($_GET['object']) . "\" />");


echo("</td>\n</tr>\n");

echo("<tr>\n<td>\n</td><td><input type=\"submit\" name=\"newobject\" value=\"" . LangChangeObject . "\" />\n</td><td></td></tr></form></table>");

echo("</div>\n</div>\n</body>\n</html>");

?>
