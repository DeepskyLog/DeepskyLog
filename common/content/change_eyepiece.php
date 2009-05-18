<?php // change_eyepiece.php - allows the eyepiece owner to change eyepiece details 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(LangException002);
elseif(!($eyepieceid=$objUtil->checkGetKey('eyepiece'))) throw new Exception(LangException003);
elseif(!($objUtil->checkUserID($objEyepiece->getEyepiecePropertyFromId($eyepieceid,'observer','')))) throw new Exception(LangException004);
//elseif(!($objEyepiece->getEyepiecePropertyFromId($eyepieceid,'name'))) throw new Exception("Eyepiece not found in change_eyepiece.php, please contact the developers with this message:".$eyepieceid);
else
{
$eyepiece=$objEyepiece->getEyepiecePropertiesFromId($eyepieceid);
echo "<div id=\"main\">";
echo "<form action=\"".$baseURL."index.php\" method=\"post\" />";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_eyepiece\">";
echo "<input type=\"hidden\" name=\"id\"          value=\"".$eyepieceid."\" />";
$objPresentations->line(array("<h5>".stripslashes($eyepiece['name'])."</h5>","<input type=\"submit\" name=\"change\" value=\"".LangAddEyepieceButton2."\" />&nbsp;"),"LR",array(80,20),50);
echo "<hr>";
$line[]=array("<span class=\"fieldname\">".LangAddEyepieceField1."</span>",
              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"eyepiecename\" size=\"30\" value=\"".stripslashes($eyepiece['name'])."\" />",
              LangAddEyepieceField1Expl);
$line[]=array("<span class=\"fieldname\">".LangAddEyepieceField2."</span>",
              "<input type=\"text\" class=\"inputfield requiredField\" style=\"text-align:center\" maxlength=\"5\" name=\"focalLength\" size=\"5\" value=\"".stripslashes($eyepiece['focalLength'])."\" />",
              LangAddEyepieceField2Expl);
$line[]=array("<span class=\"fieldname\">".LangAddEyepieceField4."</span>",
              "<input type=\"text\" class=\"inputfield\" style=\"text-align:center\" maxlength=\"5\" name=\"maxFocalLength\" size=\"5\" value=\"".((($mfl=stripslashes($eyepiece['maxFocalLength']))<0)?"":$mfl)."\" />",
              LangAddEyepieceField4Expl);
$line[]=array("<span class=\"fieldname\">".LangAddEyepieceField3."</span>",
              "<input type=\"text\" class=\"inputfield requiredField\" style=\"text-align:center\" maxlength=\"5\" name=\"apparentFOV\" size=\"5\" value=\"".$eyepiece['apparentFOV']."\" />",
              LangAddEyepieceField3Expl);
for($i=0;$i<count($line);$i++)
  $objPresentations->line($line[$i],"RLL",array(20,40,40));
echo "<hr>";
echo "</form>";
echo "</div>";
}
?>
