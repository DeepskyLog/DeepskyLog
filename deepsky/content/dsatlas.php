<?php
echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/phpjs.js\"></script>";
echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/wz_jsgraphics.js\"></script>";
echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/atlaspage.js\"></script>";
$loadAtlasPage=1; // ---> to load the atlas js in index.php
$ra=0;
$decl=0;
$object=$objUtil->checkRequestKey('object');
if($object)
{ $ra=$objObject->getDsoProperty($object,'ra',0);
  $decl=$objObject->getDsoProperty($object,'decl',0);
}
echo "<script type=\"text/javascript\">";
echo "this.theobject='".$object."';";
echo "this.atlaspagerahr=".$ra.";this.atlaspagedecldeg=".$decl.";";
echo "this.AtlasOverviewZoom=".AtlasOverviewZoom.";";
echo "this.AtlasLookupZoom=".AtlasLookupZoom.";";
echo "this.AtlasDetailZoom=".AtlasDetailZoom.";";
while(list($name,$value)=each($atlasPageText))
  echo $name."='".$value."';";
echo "</script>";



echo "<div id=\"atlasPageDiv\" class=\"atlasPageDiv\" onmousemove=\"canvasOnMouseMove(event);\" onclick=\"canvasOnClick(event);\"  >";
echo "</div>"; 
echo "<div id=\"atlasPageDiv1\" class=\"atlasPageDiv1\" >";
echo "</div>";
echo "<div id=\"atlasPageDiv2\" class=\"atlasPageDiv2\" >";
echo LangAtlasHelp;
echo "<hr /><br />";
echo LangAtlasNavigation."<br />";
echo LangAtlasNavigation1."<br /><br />";
echo LangAtlasZoom."<br />";
echo LangAtlasZoom1."<br />";
echo LangAtlasZoom2."<br />";
echo LangAtlasZoom3."<br /><br />";
echo LangAtlasZoom4."<br />";
echo LangAtlasZoom5."<br /><br />";
echo LangAtlasGrid."<br /><br />";
echo LangAtlasLabels."<br /><br />";
echo LangAtlasMagnitude."<br />";
echo LangAtlasMagnitude1."<br /><br />";
echo LangAtlasMagnitude2."<br /><br />";
echo LangAtlasMagnitude3."<br />";
echo LangAtlasMagnitude4."<br /><br />";
echo LangAtlasMagnitude5."<br /><br />";
echo LangAtlasObjects."<br />";
echo LangAtlasObjects1."<br />";
echo LangAtlasObjects2."<br />";
echo LangAtlasObjects3."<br />";
echo LangAtlasObjects4."<br /><br />";
echo LangAtlasObjects5."<br /><br />";
echo LangAtlasCursor."<br /><br />";
echo LangAtlasPrint."<br /><br />";
echo LangAtlasCredit."<br /><br />";
echo LangAtlasWhatsnext;

echo "</div>"; 
echo "<div id=\"atlasPageDiv3\" class=\"atlasPageDiv3\" >";
echo LangAtlasAbbrev;
echo "<hr /><br />";


$types = $objObject->getDsObjectTypes();
while(list($key, $value) = each($types))
  $stypes[$value] = $$value;
asort($stypes);

$i=0;
echo "<table class=\"abbrevTable\">";
while(list($key, $value) = each($stypes))
{ if(!($i++%3))
    echo "<tr class=\"abbrevTable\">";
  echo "<td class=\"abbrevTable\">".$key . "</td><td class=\"abbrevTable\">" .$value."</td>"; 
  if(!($i%3))
    echo "</tr>";
}
if($i%3)
  echo "</tr>";
echo "</table><br /><br />";



$constellations = $objObject->getConstellations(); 
while(list($key, $value) = each($constellations))
  $cons[$value] = $$value;
//$cons=asort($cons);
$i=0;
echo "<table class=\"abbrevTable\">";
while(list($key, $value) = each($cons))
{ if(!($i++%4))
    echo "<tr class=\"abbrevTable\">";
  echo "<td class=\"abbrevTable\">".$key . "&nbsp;".$value."</td>"; 
  if(!($i%4))
    echo "</tr>";
}
if($i%4)
  echo "</tr>";
echo "</table><br /><br />";

echo LangAtlasPrint."<br /><br />";
echo LangAtlasCredit."<br /><br />";

echo "</div>"; 

?>