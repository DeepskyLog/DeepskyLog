<?php // view_lens.php - view information of a lens 
if(!$objUtil->checkGetKey('lens')) 
  throw new Exception("No lens specified.");
if(!($name=$objLens->getLensPropertyFromId($_GET['lens'],'name')))
  throw new Exception("Lens not found.");
echo "<div id=\"main\">";
echo "<h2>".$name."</h2>";
echo "<table>";
tableFieldnameField(LangViewLensFactor,$objLens->getLensPropertyFromId($_GET['lens'],'factor'));
echo "</table>";
echo "</div>";
?>
