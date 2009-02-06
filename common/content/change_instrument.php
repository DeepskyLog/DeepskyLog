<?php
// change_instrument.php
// form which allows the administrator to change an instrument 

echo "<div id=\"main\">";
echo "<h2>";
$name=$objInstrument->getInstrumentPropertyFromId($_GET['instrument'],'name');
if($name=="Naked eye")
  echo InstrumentsNakedEye;
else
  echo $name ;
echo "</h2>";
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_instrument\" />";
echo "<input type=\"hidden\" name=\"id\" value=\"".$_GET['instrument']."\" />";
echo "<table>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddInstrumentField1."</td>";
echo "<td><input value=\"".$name."\" type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"instrumentname\" size=\"30\" /></td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddInstrumentField2."</td>";
echo "<td><input value=\"".round($objInstrument->getInstrumentPropertyFromId($_GET['instrument'],'diameter'), 0)."\" type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"diameter\" size=\"10\" />";
echo "<select name=\"diameterunits\">";
echo "<option>inch</option>";
echo "<option selected=\"selected\">mm</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddInstrumentField5."</td>";
echo "<td>";
echo $objInstrument->getInstrumentEchoListType($objInstrument->getInstrumentPropertyFromId($_GET['instrument'],'type'));
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>&nbsp;</td>";
echo "<td>&nbsp;</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddInstrumentField4."</td>";
echo "<td><input value=\"".(($fl=round($objInstrument->getInstrumentPropertyFromId($_GET['instrument'],'fd')*$objInstrument->getInstrumentPropertyFromId($_GET['instrument'],'diameter'), 0))?$fl:"")."\" type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"focallength\" size=\"10\" />";
echo "<select name=\"focallengthunits\">";
echo "<option>inch</option>";
echo "<option selected=\"selected\">mm</option>";
echo "</select>";
echo ' '.LangAddInstrumentOr.' '.LangAddInstrumentField3;
echo "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"fd\" size=\"10\" /></td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddInstrumentField6."</td>";
echo "<td>";
echo "<input value=\"".(($fm=$objInstrument->getInstrumentPropertyFromId($_GET['instrument'],'fixedMagnification'))?$fm:"")."\" type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"fixedMagnification\" size=\"10\" />";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>&nbsp;</td>";
echo "<td>&nbsp;</td>";
echo "</tr>";
echo "<tr>";
echo "<td></td>";
echo "<td><input type=\"submit\" name=\"change\" value=\"".LangChangeInstrumentButton."\" />";
echo "</td>";
echo "<td></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";
?>
