<?php
// view_eyepiece.php
// view information of an eyepiece 

if(!$objUtil->checkGetKey('eyepiece'))
  throw("No eyepiece specified");
if(!($name=$objEyepiece->getEyepieceName($_GET['eyepiece'])))
  throw("Eyepiece not found");
  
$focalLength = $objEyepiece->getFocalLength($_GET['eyepiece']);
$maxFocalLength=$objEyepiece->getMaxFocalLength($_GET['eyepiece']);
$fov=$objEyepiece->getApparentFOV($_GET['eyepiece']);

echo "<div id=\"main\">";
echo "<h2>".$name."</h2>";
echo "<table>";
tableFieldnameField(LangViewEyepieceFocalLength,$focalLength);
if ($maxFocalLength>0) 
  tableFieldnameField(LangAddEyepieceField4,$maxFocalLength);
tableFieldnameField(LangAddEyepieceField3,$fov);
echo "</table>";
echo "</div>";

?>
