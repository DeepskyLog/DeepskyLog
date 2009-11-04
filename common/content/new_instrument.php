<?php // new_instrument.php - form which allows the user to add a new instrument 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
else
{
echo "<div id=\"main\">";
$objPresentations->line(array("<h4>".LangOverviewInstrumentsTitle." ".$loggedUserName."</h4>"),
                        "L",array(100),30);
echo "<hr />"; 
$objInstrument->showInstrumentsObserver();
$insts=$objInstrument->getSortedInstruments('name',"",true);
echo "<form action=\"".$baseURL."index.php\" method=\"post\"><div>";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_instrument\" />";
$content1b = "<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
$content1b.= "<option selected=\"selected\" value=\"".$baseURL."index.php?indexAction=add_instrument\"> &nbsp; </option>";
while(list($key,$value)=each($insts))
  $content1b.= "<option value=\"".$baseURL."index.php?indexAction=add_instrument&amp;instrumentid=".urlencode($value)."\" ".(($value==$objUtil->checkRequestKey('instrumentid'))?" selected=\"selected\" ":'').">" . $objInstrument->getInstrumentPropertyFromId($value,'name') . "</option>";
$content1b.= "</select>";
$objPresentations->line(array("<h4>".LangAddInstrumentTitle."&nbsp;<span class=\"requiredField\">".LangRequiredFields."</span>"."</h4>"),"L",array(),30);
echo "<hr />";
$objPresentations->line(array(LangAddInstrumentExisting,
                              $content1b,
                              "<input type=\"submit\" name=\"add\" value=\"".LangAddInstrumentAdd."\" />&nbsp;"),
                              "RLR",array(25,40,35),'',array("fieldname"));
$objPresentations->line(array(LangAddSiteFieldOr." ".LangAddInstrumentManually),"R",array(25),'',array("fieldname"));
$type=$objUtil->checkRequestKey('type');
if($instrumentid=$objUtil->checkRequestKey('instrumentid',0))
  $type=$objInstrument->getInstrumentPropertyFromId($instrumentid,'type');
$objPresentations->line(array(LangAddInstrumentField1,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"instrumentname\" size=\"30\"  value=\"".stripslashes($objUtil->checkRequestKey('instrumentname')).stripslashes($objInstrument->getInstrumentPropertyFromId($objUtil->checkRequestKey('instrumentid'),'name'))."\" />"),
                        "RLR",array(25,40,35),'',array("fieldname"));                              
$objPresentations->line(array(LangAddInstrumentField2,
                               "<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"64\" name=\"diameter\" size=\"10\" value=\"".stripslashes($objUtil->checkRequestKey('diameter')).stripslashes($objInstrument->getInstrumentPropertyFromId($objUtil->checkRequestKey('instrumentid'),'diameter'))."\" />".
                               "<select name=\"diameterunits\"> <option>inch</option> <option selected=\"selected\">mm</option> </select>",
                               ""),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddInstrumentField5,$objInstrument->getInstrumentEchoListType($type),""),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddInstrumentField4,
                               "<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"64\" name=\"focallength\" size=\"10\"  value=\"".stripslashes($objUtil->checkRequestKey('focallength')).stripslashes($objInstrument->getInstrumentPropertyFromId($objUtil->checkRequestKey('instrumentid'),'diameter')*$objInstrument->getInstrumentPropertyFromId($objUtil->checkRequestKey('instrumentid'),'fd'))."\" />".
                               "<select name=\"focallengthunits\"> <option>inch</option> <option selected=\"selected\">mm</option> </select>".
                               "&nbsp;<span class=\"normal\">".LangAddInstrumentOr."&nbsp;".
                               LangAddInstrumentField3."</span>&nbsp;".
                               "<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"64\" name=\"fd\" size=\"10\" value=\"".stripslashes($objUtil->checkRequestKey('fd')).stripslashes($objInstrument->getInstrumentPropertyFromId($objUtil->checkRequestKey('instrumentid'),'fd'))."\" />",
                               ""),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddInstrumentField6,
                               "<input type=\"text\" class=\"inputfield centered\" maxlength=\"5\" name=\"fixedMagnification\" size=\"5\" value=\"".($objUtil->checkRequestKey('fixedMagnification')).stripslashes($objInstrument->getInstrumentPropertyFromId($objUtil->checkRequestKey('instrumentid'),'fixedMagnification'))."\" />",
                               LangAddInstrumentField6Expl),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
echo "<hr />";
echo "</div></form>";
echo "</div>";
}
?>
