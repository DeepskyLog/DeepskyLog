<?php // view_eyepiece.php - view information of an eyepiece 
if(!$loggedUser)
  throw new Exception("No logged user in view_eyepiece.php, please contact the developers with this message.");
if(!($eyepieceid=$objUtil->checkGetKey('eyepiece')))
  throw new Exception("No eyepiece specified in view_eyepiece.php, please contact the developers with this message.");
if(!$objEyepiece->getEyepiecePropertyFromId($eyepieceid,'name'))
  throw new Exception("Eyepiece not found in view_eyepiece, please contact the developers with this message:".$eyepieceid);
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
?>
