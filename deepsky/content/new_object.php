<?php
// new_object.php
// allows the user to add an object to the database 

echo "<div id=\"main\">";
echo "<form action=\"".$baseURL."index.php?indexAction=validate_object\" method=\"post\">";
$objPresentations->line(array("<h4>".LangNewObjectTitle."</h4>","<input type=\"submit\" name=\"newobject\" value=\"".LangNewObjectButton1."\" />&nbsp;"),"LR",array(80,20),30);
echo "<hr />";
//NAME
$objPresentations->line(array(LangViewObjectField1 . "&nbsp;*",
                              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"20\" name=\"catalog\" size=\"20\" value=\"\" />".
                              "&nbsp;&nbsp;".
                              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"20\" name=\"number\" size=\"20\" value=\"\" />"),
                        "RL",array(20,80),35,array("fieldname"));
// TYPE
$content ="<select name=\"type\" class=\"requiredField\">";
$content.="<option value=\"\">&nbsp;</option>";
$types=$objObject->getDsObjectTypes();
while(list($key,$value)=each($types))
  $stypes[$value] = $$value;
asort($stypes);
while(list($key, $value) = each($stypes))
  $content.="<option value=\"$key\">".$value."</option>";
$content.="</select>";
$objPresentations->line(array(LangViewObjectField6 . "&nbsp;*",
                              $content),                              
                        "RL",array(20,80),35,array("fieldname"));
// CONSTELLATION 
$content ="<select name=\"con\" class=\"requiredField\">";
$content.="<option value=\"\">&nbsp;</option>";
$constellations = $objObject->getConstellations();
while(list($key, $value)=each($constellations))
  $content.="<option value=\"$value\">".$GLOBALS[$value]."</option>";
$content.="</select>";
$objPresentations->line(array(LangViewObjectField5 . "&nbsp;*",
                              $content),                              
                        "RL",array(20,80),35,array("fieldname"));
// RIGHT ASCENSION
$content ="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"RAhours\" size=\"3\" value=\"\" />&nbsp;h&nbsp;";
$content.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"RAminutes\" size=\"3\" value=\"\" />&nbsp;m&nbsp;"; 
$content.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"RAseconds\" size=\"3\" value=\"\" />&nbsp;s&nbsp;";
$objPresentations->line(array(LangViewObjectField3 . "&nbsp;*",
                              $content),                              
                        "RL",array(20,80),35,array("fieldname"));
// DECLINATION
$content ="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"3\" name=\"DeclDegrees\" size=\"3\" value=\"\" />&nbsp;d&nbsp;";
$content.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"DeclMinutes\" size=\"3\" value=\"\" />&nbsp;m&nbsp;";
$content.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"DeclSeconds\" size=\"3\" value=\"\" />&nbsp;s&nbsp;";
$objPresentations->line(array(LangViewObjectField4."&nbsp;*",
                              $content),                              
                        "RL",array(20,80),35,array("fieldname"));
// MAGNITUDE
$content ="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"magnitude\" size=\"4\" value=\"\" />";
$objPresentations->line(array(LangViewObjectField7,
                              $content),                              
                        "RL",array(20,80),35,array("fieldname"));
// SURFACE BRIGHTNESS
$content ="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"sb\" size=\"4\" value=\"\" />";
$objPresentations->line(array(LangViewObjectField8,
                              $content),                              
                        "RL",array(20,80),35,array("fieldname"));
// SIZE
$content ="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"size_x\" size=\"4\" value=\"\"/>&nbsp;&nbsp;";
$content.="<select name=\"size_x_units\"> <option value=\"min\">" . LangNewObjectSizeUnits1 . "</option>
			                               <option value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>";
$content.="</select>";
$content.="&nbsp;&nbsp;X&nbsp;&nbsp;";
$content.="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"size_y\" size=\"4\" value=\"\"/>&nbsp;&nbsp;";
$content.="<select name=\"size_y_units\"> <option value=\"min\">" . LangNewObjectSizeUnits1 . "</option>
			                               <option value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>";
$content.="</select>";
$objPresentations->line(array(LangViewObjectField9,
                              $content),                              
                        "RL",array(20,80),35,array("fieldname"));
// POSITION ANGLE 
$content ="<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"posangle\" size=\"3\" value=\"\" />&deg;";
$objPresentations->line(array(LangViewObjectField12,
                              $content),                              
                        "RL",array(20,80),35,array("fieldname"));
echo "</form>";
echo "<hr />";
echo "</div>";
?>
