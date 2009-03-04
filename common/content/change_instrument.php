<?php // change_instrument.php - form which allows the administrator to change an instrument 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
elseif(!($instrumentid=$objUtil->checkGetKey('instrument'))) throw new Exception(LangException007);
elseif(!($objUtil->checkUserID($objInstrument->getInstrumentPropertyFromId($instrumentid,'observer','')))) throw new Exception(LangExcpetion008);
else
{
echo "<div id=\"main\">";
echo "<h2>";
$name=$objInstrument->getInstrumentPropertyFromId($instrumentid,'name');
if($name=="Naked eye")
  echo InstrumentsNakedEye;
else
  echo $name ;
echo "</h2>";
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_instrument\" />";
echo "<input type=\"hidden\" name=\"id\" value=\"".$instrumentid."\" />";
echo "<table>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddInstrumentField1."</td>";
echo "<td><input value=\"".$name."\" type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"instrumentname\" size=\"30\" /></td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddInstrumentField2."</td>";
echo "<td><input value=\"".round($objInstrument->getInstrumentPropertyFromId($instrumentid,'diameter'), 0)."\" type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"diameter\" size=\"10\" />";
echo "<select name=\"diameterunits\">";
echo "<option>inch</option>";
echo "<option selected=\"selected\">mm</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddInstrumentField5."</td>";
echo "<td>";
echo $objInstrument->getInstrumentEchoListType($objInstrument->getInstrumentPropertyFromId($instrumentid,'type'));
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>&nbsp;</td>";
echo "<td>&nbsp;</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddInstrumentField4."</td>";
echo "<td><input value=\"".(($fl=round($objInstrument->getInstrumentPropertyFromId($instrumentid,'fd')*$objInstrument->getInstrumentPropertyFromId($instrumentid,'diameter'), 0))?$fl:"")."\" type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"focallength\" size=\"10\" />";
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
echo "<input value=\"".(($fm=$objInstrument->getInstrumentPropertyFromId($instrumentid,'fixedMagnification'))?$fm:"")."\" type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"fixedMagnification\" size=\"10\" />";
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
}
?>
