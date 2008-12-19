<?php
// new_object.php
// allows the user to add an object to the database 

echo "<div id=\"main\">";
echo "<h2>".LangNewObjectTitle."</h2>";
echo "<table width=\"100%\">";
echo "<form action=\"".$baseURL."index.php?indexAction=validate_object\" method=\"post\">";
// NAME
echo "<tr>";
echo "<td class=\"fieldname\">".LangViewObjectField1 . "&nbsp;*"."</td>";
echo "<td><input type=\"text\" class=\"inputfield requiredField\" maxlength=\"20\" name=\"catalog\" size=\"20\" value=\"\" />";
echo "&nbsp;&nbsp;";
echo "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"20\" name=\"number\" size=\"20\" value=\"\" />";
echo "</td>";
echo "</tr>";
// TYPE
echo "<tr>";
echo "<td class=\"fieldname\">".LangViewObjectField6."&nbsp;*"."</td>";
echo "<td>";
echo "<select name=\"type\" class=\"requiredField\">";
echo "<option value=\"\"></option>";
$types=$objObject->getDsObjectTypes();
while(list($key,$value)=each($types))
  $stypes[$value] = $$value;
asort($stypes);
while(list($key, $value) = each($stypes))
  echo("<option value=\"$key\">".$value."</option>\n");
echo "</select>";
echo "</td>";
echo "<td>";
echo "</td>";
echo "</tr>";
// CONSTELLATION 
echo "<tr>";
echo "<td class=\"fieldname\">".LangViewObjectField5."&nbsp;*"."</td>";
echo "<td>";
echo "<select name=\"con\" class=\"requiredField\">";
echo "<option value=\"\"></option>";
$constellations = $objObject->getConstellations();
while(list($key, $value)=each($constellations))
  echo "<option value=\"$value\">".$GLOBALS[$value]."</option>";
echo "</select>";
echo "</td>";
echo "<td>";
echo "</td>";
echo "</tr>";
// RIGHT ASCENSION
echo "<tr>";
echo "<td class=\"fieldname\">".LangViewObjectField3."&nbsp;*"."</td>";
echo "<td colspan=\"2\">";
echo "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"2\" name=\"RAhours\" size=\"2\" value=\"\" />&nbsp;h&nbsp;";
echo "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"2\" name=\"RAminutes\" size=\"2\" value=\"\" />&nbsp;m&nbsp;"; 
echo "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"2\" name=\"RAseconds\" size=\"2\" value=\"\" />&nbsp;s&nbsp;";
// DECLINATION
echo "<tr>";
echo "<td class=\"fieldname\">".LangViewObjectField4."&nbsp;*"."</td>";
echo "<td colspan=\"2\">";
echo "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"3\" name=\"DeclDegrees\" size=\"3\" value=\"\" />&nbsp;&deg;&nbsp;";
echo "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"2\" name=\"DeclMinutes\" size=\"2\" value=\"\" />&nbsp;&#39;&nbsp;";
echo "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"2\" name=\"DeclSeconds\" size=\"2\" value=\"\" />&nbsp;&quot;&nbsp;";
echo "</td>";
echo "</tr>";
// MAGNITUDE
echo "<tr>";
echo "<td class=\"fieldname\">".LangViewObjectField7."</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"magnitude\" size=\"4\" value=\"\" />";
echo "</td>";
echo "<td></td>";
echo "</tr>";
// SURFACE BRIGHTNESS
echo "<tr>";
echo "<td class=\"fieldname\">".LangViewObjectField8."</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"sb\" size=\"4\" value=\"\" />";
echo "</td>";
echo "<td></td>";
echo "</tr>";
// SIZE
echo "<tr>";
echo "<td class=\"fieldname\">".LangViewObjectField9."</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"size_x\" size=\"4\" value=\"\"/>&nbsp;&nbsp;";
echo "<select name=\"size_x_units\"> <option value=\"min\">" . LangNewObjectSizeUnits1 . "</option>
			                               <option value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>";
echo "</select>";
echo "&nbsp;&nbsp;X&nbsp;&nbsp;";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"size_y\" size=\"4\" value=\"\"/>&nbsp;&nbsp;";
echo "<select name=\"size_y_units\"> <option value=\"min\">" . LangNewObjectSizeUnits1 . "</option>
			                               <option value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>";
echo "</select>";
echo "</td>";
echo "<td></td>";
echo "</tr>";
// POSITION ANGLE 
echo "<tr>";
echo "<td class=\"fieldname\">";LangViewObjectField12;"</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"posangle\" size=\"3\" value=\"\" />&deg;";
echo "</td>";
echo "<td></td>";
echo "</tr>";
echo "<tr>";
echo "<td></td>";
echo "<td><input type=\"submit\" name=\"newobject\" value=\"".LangNewObjectButton1."\" /></td>";
echo "<td></td>";
echo "</tr>";
echo "<tr>";
echo "<td></td>";
echo "<td><input type=\"submit\" name=\"clearfields\" value=\"".LangQueryObjectsButton2."\" /></td>";
echo "<td></td>";
echo "</tr>";
echo "</form>";
echo "</table>";
echo "</div>";

?>
