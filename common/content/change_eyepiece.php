<?php 
// change_eyepiece.php
// allows the eyepiece owner or an admin to an eyepiece 
// or another user to view the eyepiece details

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($eyepieceid=$objUtil->checkGetKey('eyepiece'))) throw new Exception(LangException003);
elseif(!($objEyepiece->getEyepiecePropertyFromId($eyepieceid,'name'))) throw new Exception("Eyepiece not found in change_eyepiece.php, please contact the developers with this message:".$eyepieceid);
else change_eyepiece();

function change_eyepiece()
{ global $baseURL,$loggedUser,$eyepieceid,
         $objEyepiece,$objPresentations,$objUtil;
  $disabled=" disabled=\"disabled\"";
	if(($loggedUser) &&
	   ($objUtil->checkAdminOrUserID($objEyepiece->getEyepiecePropertyFromId($eyepieceid,'observer',''))))
	  $disabled="";
	$eyepiece=$objEyepiece->getEyepiecePropertiesFromId($eyepieceid);
	echo "<div id=\"main\">";
	echo "<form action=\"".$baseURL."index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_eyepiece\" />";
	echo "<input type=\"hidden\" name=\"id\"          value=\"".$eyepieceid."\" />";
	$content=($disabled?"":"<input type=\"submit\" name=\"change\" value=\"".LangAddEyepieceButton2."\" />&nbsp;");
	$objPresentations->line(array("<h4>".stripslashes($eyepiece['name'])."</h4>",$content),"LR",array(80,20),30);
	echo "<hr />";
	$line[]=array(LangAddEyepieceField1,
	              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"eyepiecename\" size=\"30\" value=\"".stripslashes($eyepiece['name'])."\" ".$disabled."/>",
	              LangAddEyepieceField1Expl);
	$line[]=array(LangAddEyepieceField2,
	              "<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"5\" name=\"focalLength\" size=\"5\" value=\"".stripslashes($eyepiece['focalLength'])."\" ".$disabled."/>",
	              LangAddEyepieceField2Expl);
	$line[]=array(LangAddEyepieceField4,
	              "<input type=\"text\" class=\"inputfield centered\" maxlength=\"5\" name=\"maxFocalLength\" size=\"5\" value=\"".((($mfl=stripslashes($eyepiece['maxFocalLength']))<0)?"":$mfl)."\" ".$disabled."/>",
	              LangAddEyepieceField4Expl);
	$line[]=array(LangAddEyepieceField3,
	              "<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"5\" name=\"apparentFOV\" size=\"5\" value=\"".$eyepiece['apparentFOV']."\" ".$disabled."/>",
	              LangAddEyepieceField3Expl);
	for($i=0;$i<count($line);$i++)
	  $objPresentations->line($line[$i],"RLL",array(20,40,40),'',array("fieldname","fieldvalue","fieldexplanation"));
	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
