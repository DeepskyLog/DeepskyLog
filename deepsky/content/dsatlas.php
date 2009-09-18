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
echo "atlaspagerahr=".$ra.";atlaspagedecldeg=".$decl.";";
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
echo LangAtlasZoom3."<br />";
echo LangAtlasZoom4."<br />";
echo LangAtlasZoom5."<br /><br />";
echo LangAtlasGrid."<br /><br />";
echo LangAtlasLabels."<br /><br />";
echo LangAtlasMagnitude."<br />";
echo LangAtlasMagnitude1."<br /><br />";
echo LangAtlasMagnitude2."<br />";
echo LangAtlasMagnitude3."<br /><br />";
echo LangAtlasMagnitude4."<br /><br />";
echo LangAtlasObjects."<br />";
echo LangAtlasObjects1."<br />";
echo LangAtlasObjects2."<br />";
echo LangAtlasObjects3."<br />";
echo LangAtlasObjects4."<br /><br />";
echo LangAtlasObjects5."<br /><br />";
echo LangAtlasCursor."<br /><br />";
echo LangAtlasPrint."<br /><br />";
echo LangAtlasCredit."<br /><br />";

echo "</div>"; 

//echo "<div id=\"gridDiv\"  style=\"position:absolute;top:120px;left:170px;height:400px;width:400px;cursor:crosshair;background-color:#ffFF00;\">&nbsp;</div>";
?>