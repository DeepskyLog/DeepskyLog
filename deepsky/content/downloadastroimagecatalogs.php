<?php
echo "downloads:<br />";
global $objObject;
$constellations = $objObject->getConstellations(); // should be sorted
while(list($key, $value) = each($constellations))
{  $cons[$value] = $GLOBALS[$value];
}
asort($cons);
reset($cons);
while(list($key, $value) = each($cons))
  echo "<a href=\"".$dirAstroImageCatalogs.$key.".pdf\">".$key."</a> ";
?>