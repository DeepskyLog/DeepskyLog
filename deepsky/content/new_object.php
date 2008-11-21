<?php

// new_object.php
// allows the user to add an object to the database 
// Version 0.1: 2004/09/05, JV
//$$ ok

include_once "../lib/objects.php";
include_once "../lib/setup/language.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();
$objects = new Objects; 
echo("<div id=\"main\">\n<h2>");
echo (LangNewObjectTitle); 
echo("</h2>\n<table width=\"100%\">\n");
echo("<form action=\"deepsky/index.php?indexAction=validate_object\" method=\"post\">");
// NAME

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObjectField1 . "&nbsp;*";

echo("</td>\n<td>");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"20\" name=\"catalogue\" size=\"20\" value=\"\" />");

echo("&nbsp;&nbsp;<input type=\"text\" class=\"inputfield\" maxlength=\"20\" name=\"number\" size=\"20\" value=\"\" />");

echo("</td>\n</tr>\n");

// TYPE

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObjectField6 . "&nbsp;*";

echo("</td>\n<td>\n");

echo("<select name=\"type\">\n");

echo("<option value=\"\"></option>"); // empty field

$types = $objects->getDsObjectTypes();

while(list($key, $value) = each($types))
{
 $stypes[$value] = $$value;
}

asort($stypes);

while(list($key, $value) = each($stypes))
{
   echo("<option value=\"$key\">".$value."</option>\n");
}

echo("</select>\n");

echo("</td>\n<td>\n</td>\n</tr>\n");

// CONSTELLATION 

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObjectField5 . "&nbsp;*";

echo("</td>\n<td>\n");

echo("<select name=\"con\">\n");

echo("<option value=\"\"></option>"); // empty field

$constellations = $objects->getConstellations(); // should be sorted

while(list($key, $value) = each($constellations))
{
   echo("<option value=\"$value\">".$$value."</option>\n");
}

echo("</select>\n");

echo("</td>\n<td>\n</td>\n</tr>\n");

// RIGHT ASCENSION

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObjectField3 . "&nbsp;*";

echo("</td>\n<td colspan=\"2\">\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"RAhours\" size=\"2\" value=\"\" />&nbsp;h&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"RAminutes\" size=\"2\" value=\"\" />&nbsp;m&nbsp;"); 
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"RAseconds\" size=\"2\" value=\"\" />&nbsp;s&nbsp;");

// DECLINATION

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObjectField4 . "&nbsp;*";

echo("</td>\n<td colspan=\"2\">\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"DeclDegrees\" size=\"3\"
 value=\"\" />&nbsp;&deg;&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"DeclMinutes\" size=\"2
\" value=\"\" />&nbsp;&#39;&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"DeclSeconds\" size=\"2\" value=\"\" />&nbsp;&quot;&nbsp;");

echo("</td>\n</tr>\n");

// MAGNITUDE

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObjectField7;

echo("</td>\n<td>\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"magnitude\" size=\"4\" value=\"\" />");

echo("</td>\n<td>\n</td>\n</tr>\n");

// SURFACE BRIGHTNESS

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObjectField8;

echo("</td>\n<td>\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"sb\" size=\"4\" value=\"\" />");

echo("</td>\n<td></td>\n</tr>\n");

// SIZE

if(array_key_exists('object',$_GET) && ($objects->getSize($_GET['object']) != ""))
{
   echo("<tr>\n
         <td class=\"fieldname\">");

   echo LangViewObjectField9; 
 
   echo("</td>\n<td>");
 
   echo($objects->getSize($_GET['object']));
 
   echo("</td>\n</tr>\n"); 
}

// SIZE

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObjectField9;

echo("</td>\n<td>\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"size_x\" size=\"4\" value=\"\"/>&nbsp;&nbsp;
      <select name=\"size_x_units\"> <option value=\"min\">" . LangNewObjectSizeUnits1 . "</option>
			                               <option value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>
			</select>
			&nbsp;&nbsp;X&nbsp;&nbsp;
			<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"size_y\" size=\"4\" value=\"\"/>&nbsp;&nbsp;
			<select name=\"size_y_units\"> <option value=\"min\">" . LangNewObjectSizeUnits1 . "</option>
			                               <option value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>
			</select>");
echo("</td>\n<td>\n</td>\n</tr>\n");

// POSITION ANGLE 

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObjectField12;

echo("</td>\n<td>\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"posangle\" size=\"3\" value=\"\" />&deg;");

echo("</td>\n<td>\n</td>\n</tr>\n");

echo("<tr>\n<td>\n</td><td><input type=\"submit\" name=\"newobject\" value=\"" . LangNewObjectButton1 . "\" />\n</td><td></td></tr><tr><td></td><td><input type=\"submit\" name=\"clearfields\" value=\"" . LangQueryObjectsButton2 . "\" />\n</td><td></td></tr></form></table>");

echo("</div>\n</div>\n</body>\n</html>");

?>
