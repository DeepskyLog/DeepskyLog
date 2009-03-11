<?php // view_eyepiece.php - view information of an eyepiece 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($eyepieceid=$objUtil->checkGetKey('eyepiece'))) throw new Exception(LangExcpetion003);
else
{
$eyepieceproperties=$objEyepiece->getEyepiecePropertiesFromId($eyepieceid);
echo "<div id=\"main\">";
echo "<h2>".$objEyepiece->getEyepiecePropertyFromId($eyepieceid,'name','Unkown name')."</h2>";
echo "<table>";
tableFieldnameField(LangViewEyepieceFocalLength,$eyepieceproperties['focalLength']);
if($eyepieceproperties['maxFocalLength']>0) 
  tableFieldnameField(LangAddEyepieceField4,$eyepieceproperties['maxFocalLength']);
tableFieldnameField(LangAddEyepieceField3,$eyepieceproperties['apparentFOV']);
echo "</table>";
echo "</div>";
}
?>
