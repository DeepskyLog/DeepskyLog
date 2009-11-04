<?php  // new_eyepiece.php - allows the user to add a new eyepiece
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
else
{
$mfl = $objUtil->checkRequestKey('maxFocalLength',-1);
if($eyepieceid=$objUtil->checkRequestKey('eyepieceid'))
  $mfl=stripslashes($objEyepiece->getEyepiecePropertyFromId($eyepieceid,'maxFocalLength'));
if($mfl<0)
  $mfl='';
echo "<div id=\"main\">";  
$objPresentations->line(array("<h4>".LangOverviewEyepieceTitle." ".$loggedUserName."</h4>"),"L",array(),30);
echo "<hr />"; 
$objEyepiece->showEyepiecesObserver();
$eyeps=$objEyepiece->getSortedEyepieces('focalLength');
echo "<form action=\"".$baseURL."index.php\" method=\"post\"><div>";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_eyepiece\" />";
$content1b= "<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
while(list($key, $value)=each($eyeps))
  $content1b.= "<option value=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;eyepieceid=".urlencode($value)."\" ".(($value==$objUtil->checkRequestKey('eyepieceid'))?" selected=\"selected\" ":'').">".trim($objEyepiece->getEyepiecePropertyFromId($value,'name'))."</option>"; 
$content1b.= "</select>&nbsp;";
$objPresentations->line(array("<h4>".LangAddEyepieceTitle."&nbsp;<span class=\"requiredField\">".LangRequiredFields."</span>"."</h4>"),"L",array(),30);
echo "<hr />";
$objPresentations->line(array(LangAddEyepieceExisting,
                              $content1b,
                              "<input type=\"submit\" name=\"add\" value=\"".LangAddEyepieceButton."\" />&nbsp;"),
                        "RLR",array(25,40,35),'',array("fieldname"));                              
$objPresentations->line(array(LangAddSiteFieldOr." ".LangAddEyepieceManually),"R",array(25),'',array("fieldname"));
$objPresentations->line(array(LangAddEyepieceField1,
                              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"eyepiecename\" size=\"30\" value=\"".stripslashes($objUtil->checkRequestKey('eyepiecename')).stripslashes($objEyepiece->getEyepiecePropertyFromId($objUtil->checkRequestKey('eyepieceid'),'name'))."\" />",
                              LangAddEyepieceField1Expl),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddEyepieceField2,
                              "<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"5\" name=\"focalLength\" size=\"5\" value=\"".stripslashes($objEyepiece->getEyepiecePropertyFromId($objUtil->checkRequestKey('eyepieceid'),'focalLength',$objUtil->checkRequestKey('focalLength')))."\" />",
                              LangAddEyepieceField2Expl),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddEyepieceField4,
                              "<input type=\"text\" class=\"inputfield centered\" maxlength=\"5\" name=\"maxFocalLength\" size=\"5\" value=\"".$mfl."\" />",
                              LangAddEyepieceField4Expl),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddEyepieceField3,
                               "<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"5\" name=\"apparentFOV\" size=\"5\" value=\"".stripslashes($objEyepiece->getEyepiecePropertyFromId($objUtil->checkRequestKey('eyepieceid'),'apparentFOV',$objUtil->checkRequestKey('apparentFOV')))."\" />",
                               LangAddEyepieceField3Expl),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));

echo "<hr />";
echo "</div></form>";
echo "</div>";
}
?>
