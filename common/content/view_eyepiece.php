<?php // view_eyepiece.php - view information of an eyepiece 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(LangExcpetion002);
elseif(!($eyepieceid=$objUtil->checkGetKey('eyepiece'))) throw new Exception(LangExcpetion003);
elseif(!($objUtil->checkUserID($objEyepiece->getEyepiecePropertyFromId($eyepieceid,'observer','')))) throw new Exception(LangExcpetion004);
else
{
$eyepieceproperties=$objEyepiece->getEyepiecePropertiesFromId($eyepieceid);
echo "<div id=\"main\">";
echo "<h2>".$name."</h2>";
echo "<table>";
tableFieldnameField(LangViewEyepieceFocalLength,$eyepieceproperties['focalLength']);
if($eyepieceproperties['maxFocalLength']>0) 
  tableFieldnameField(LangAddEyepieceField4,$eyepieceproperties['maxFocalLength']);
tableFieldnameField(LangAddEyepieceField3,$eyepieceproperties['fov']);
echo "</table>";
echo "</div>";
}
?>
