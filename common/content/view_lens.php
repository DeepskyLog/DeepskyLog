<?php // view_lens.php - view information of a lens 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($lensid=$objUtil->checkGetKey('lens'))) throw new Exception(LangException009b);
else
{
$name=$objLens->getLensPropertyFromId($_GET['lens'],'name');
echo "<div id=\"main\">";
echo "<h2>".$name."</h2>";
echo "<table>";
tableFieldnameField(LangViewLensFactor,$objLens->getLensPropertyFromId($lensid,'factor'));
echo "</table>";
echo "</div>";
}
?>
