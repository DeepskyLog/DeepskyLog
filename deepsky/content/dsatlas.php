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



echo "<div id=\"atlasPageDiv\" class=\"atlasPageDiv\" onmousemove=\"canvasOnMouseMove(event);\" onclick=\"canvasOnClick(event);\" onkeydown=\"canvasOnKeyDown(event);\">";
echo "</div>"; 
echo "<div id=\"atlasPageDiv1\" class=\"atlasPageDiv1\"  onkeydown=\"canvasOnKeyDown(event);\" >";
echo "</div>";
echo "<div id=\"atlasPageDiv2\" class=\"atlasPageDiv2\"  onkeydown=\"canvasOnKeyDown(event);\" >";
echo "Help Page";
echo "<hr />";
echo "</div>"; 



//echo "<div id=\"gridDiv\"  style=\"position:absolute;top:120px;left:170px;height:400px;width:400px;cursor:crosshair;background-color:#ffFF00;\">&nbsp;</div>";
?>