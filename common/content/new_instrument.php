<?php // new_instrument.php - form which allows the user to add a new instrument 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
else
{
echo "<div id=\"main\">";
$objInstrument->showInstrumentsObserver();
$insts=$objInstrument->getSortedInstruments('name',"",true);
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_instrument\" />";
$content1b = "<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
$content1b.= "<option selected=\"selected\" value=\"".$baseURL."index.php?indexAction=add_instrument\"> &nbsp; </option>";
while(list($key,$value)=each($insts))
  $content1b.= "<option value=\"".$baseURL."index.php?indexAction=add_instrument&amp;instrumentid=".urlencode($value)."\" ".(($value==$objUtil->checkGetKey('instrumentid'))?' selected=\"selected\" ':'').">" . $objInstrument->getInstrumentPropertyFromId($value,'name') . "</option>";
$content1b.= "</select>";
$objPresentations->line(array("<h5>".LangAddInstrumentTitle."</h5>"),"L",array(),50);
echo "<hr />";
$objPresentations->line(array(LangAddInstrumentExisting,
                              $content1b,
                              "<input type=\"submit\" name=\"add\" value=\"".LangAddInstrumentAdd."\" />&nbsp;"),
                              "RLR",array(25,40,35),'',array("fieldname"));
$objPresentations->line(array(LangAddSiteFieldOr." ".LangAddInstrumentManually),"R",array(25),'',array("fieldname"));
$type=$objUtil->checkGetKey('type');
if($instrumentid=$objUtil->checkGetKey('instrumentid',0))
  $type=$objInstrument->getInstrumentPropertyFromId($instrumentid,'type');
$objPresentations->line(array(LangAddInstrumentField1,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"instrumentname\" size=\"30\"  value=\"".stripslashes($objUtil->checkGetKey('instrumentname')).stripslashes($objInstrument->getInstrumentPropertyFromId($objUtil->checkGetKey('instrumentid'),'name'))."\" />"),
                        "RLR",array(25,40,35),'',array("fieldname"));                              
$objPresentations->line(array(LangAddInstrumentField2,
                               "<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"64\" name=\"diameter\" size=\"10\" value=\"".stripslashes($objUtil->checkGetKey('diameter')).stripslashes($objInstrument->getInstrumentPropertyFromId($objUtil->checkGetKey('instrumentid'),'diameter'))."\" />".
                               "<select name=\"diameterunits\"> <option>inch</option> <option selected=\"selected\">mm</option> </select>",
                               ""),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddInstrumentField5,$objInstrument->getInstrumentEchoListType($type),""),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddInstrumentField4,
                               "<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"64\" name=\"focallength\" size=\"10\"  value=\"".stripslashes($objUtil->checkGetKey('focallength')).stripslashes($objInstrument->getInstrumentPropertyFromId($objUtil->checkGetKey('instrumentid'),'diameter')*$objInstrument->getInstrumentPropertyFromId($objUtil->checkGetKey('instrumentid'),'fd'))."\" />".
                               "<select name=\"focallengthunits\"> <option>inch</option> <option selected=\"selected\">mm</option> </select>".
                               "&nbsp;<span style=\"font-style:normal\">".LangAddInstrumentOr."&nbsp;".
                               LangAddInstrumentField3."</span>&nbsp;".
                               "<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"64\" name=\"fd\" size=\"10\" value=\"".stripslashes($objUtil->checkGetKey('fd')).stripslashes($objInstrument->getInstrumentPropertyFromId($objUtil->checkGetKey('instrumentid'),'fd'))."\" />",
                               ""),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddInstrumentField6,
                               "<input type=\"text\" class=\"inputfield centered\" maxlength=\"5\" name=\"fixedMagnification\" size=\"5\" value=\"".($objUtil->checkGetKey('fixedMagnification')).stripslashes($objInstrument->getInstrumentPropertyFromId($objUtil->checkGetKey('instrumentid'),'fixedMagnification'))."\" />",
                               LangAddInstrumentField6Expl),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
echo "<hr />";
echo "</form>";
echo "</div>";
}
?>
