<?php
// view_lens.php
// view information of a lens 

if(!$objUtil->checkGetKey('lens')) 
  throw("No lens specified.");
if(!($name=$objLens->getLensName($_GET['lens'])))
  throw("Lens not found.");
  
$factor=$objLens->getFactor($_GET['lens']);

echo "<div id=\"main\">";
echo "<h2>".$name."</h2>";
echo "<table>";
tableFieldnameField(LangViewLensFactor,$factor);
echo "</table>";
echo "</div>";
?>
