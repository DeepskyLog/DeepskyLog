<?php
// dsatlas.php
// displays the interactive atlas on the screen

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else dsatlas();

function dsatlas()
{ global $baseURL,$loadAtlasPage,
         $objObject,$objUtil;
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
	echo "this.ATLASOVERVIEWZOOM=".ATLASOVERVIEWZOOM.";";
	echo "this.ATLASLOOKUPZOOM=".ATLASLOOKUPZOOM.";";
	echo "this.ATLASDETAILZOOM=".ATLASDETAILZOOM.";";
    echo "atlasPageUpBtnTxt='"._("Page North")."';";
    echo "atlasPageSmallUpBtnTxt='"._("Section North")."';";
    echo "atlasPageDownBtnTxt'='"._("Page South")."';";
    echo "atlasPageSmallDownBtnTxt'='"._("Section South")."';";
    echo "atlasPageLeftBtnTxt'='"._("Page East")."';";
    echo "atlasPageSmallLeftBtnTxt'='"._('Section East')."';";
    echo "atlasPageRightBtnTxt'='"._('Page West')."';";
    echo "atlasPageSmallRightBtnTxt'='"._('Section West')."';";
    echo "atlasPageZoomInBtnTxt'='"._('Zoom In')."';";
    echo "atlasPageZoom1BtnTxt'='"._('Zoom to 1°')."';";
    echo "atlasPageZoom2BtnTxt'='"._('Zoom to 2°')."';";
    echo "atlasPageZoomOutBtnTxt'='"._('Zoom out')."';";
    echo "atlasPageObjectTxt'='"._('Object')."';";
    echo "atlasPageTypeTxt'='"._('Type')."';";
    echo "atlasPageConsTxt'='"._('Constellation')."';";
    echo "atlasPageSeenTxt'='"._('Seen')."';";
    echo "atlasPageMagnTxt'='"._('Magn.')."';";
    echo "atlasPageSubrTxt'='"._('Surf.Br.')."';";
    echo "atlasPageDiamTxt'='"._('Dimensions')."';";
    echo "atlasPageDone'='"._('Press H for help - A for abbreviations - P for printing - O for Overview chart - Z for Lookup chart - D for Detail chart')."';";
    echo "atlasPageObjectsFetching'='"._('Fetching object data to magnitude ...')."';";
    echo "atlasPageStarsFetching'='"._('Fetching star data to magnitude ...')."';";
    echo "atlasPageFoV'='"._('FoV:')."';";
    echo "atlasPageDSLM'='"._('Objects to mag:')."';";
    echo "atlasPageStarLM'='"._('Stars to mag:')."';";

	echo "</script>";	
	echo "<div id=\"div5\">";
	echo "<div id=\"atlasPageDiv\" class=\"atlasPageDiv\" onmousemove=\"canvasOnMouseMove(event);\" onclick=\"canvasOnClick(event);\"  >";
	echo "</div>"; 
	echo "<div id=\"atlasPageDiv1\" class=\"atlasPageDiv1\" >";
	echo "</div>";
	echo "<div id=\"atlasPageDiv2\" class=\"atlasPageDiv2\" >";
	echo _("Help page - use H to turn it on or off");
	echo "<hr /><br />";
	echo _("Navigation: use mouseclick to center page on a specific place.")."<br />";
    echo _("            or use the arrows of the keyboard.\nExtra keys: Ctrl (minimal) - Shift (maximal displacement).")."<br /><br />";
	echo _("Zoom:       The field of view is mentioned at the bottom of the page (FoV) in arc degrees or arc minutes.")."<br />";
	echo _("            use the mouse wheel to zoom in or out.")."<br />";
	echo _("            or use the arrows on the keyboard:\nCtrl + Shift + up or down arrow.")."<br />";
	echo _("            or use the number keys 0 to 9 to zoom between 1 and 40 degrees.")."<br /><br />";
	echo _("NOTICE:     some browsers use some of these combinations for other purposes, or have different key mappings,")."<br />";
	echo _("            and do not support this operation. Use the mouse instead.")."<br /><br />";
	echo _("Grid:       use G to turn the grid on or off.")."<br /><br />";
	echo _("Labels:     use L to turn the object labels on or off.")."<br /><br />";
	echo _("Magnitude:  The maximal magnitude of the shown objects is mentioned at the bottom of the page.")."<br />";
	echo _("            Use Shift + M to show more and dimmer objects, use m to show less and only brighter objects.")."<br /><br />";
	echo _("            Use Ctrl + M for maximal magnitude.")."<br /><br />";
	echo _("            The maximal magnitude of the shown stars is mentioned at the bottom of the page.")."<br />";
	echo _("            Use Shift + S to show more and dimmer stars, use s to show less and only brighter stars.")."<br /><br />";
	echo _("            The star magnitudes are mentioned at the top of the page.")."<br /><br />";
	echo _("Objects:    The labels next to the objects reflect their most common name.")."<br />";
	echo _("            You can click on it to go that object's page in DeepskyLog.")."<br />";
	echo _("            Their color shows you if they have been observed: red = not seen, yellow = seen by others, green = seen by you.")."<br />";
	echo _("            The lines give you comparable information: no line = not seen, dotted line = seen by others,")."<br />";
	echo _("            line below = seen by you, line above = seen and sketched by you.")."<br /><br />";
	echo _("            Just keeping the mouse over the label shows you additional information on the object.")."<br /><br />";
	echo _("Cursor:     The cursor coordinates are mentioned at the bottom of the page.")."<br /><br />";
	echo _("Print:      By clicking p you can get a pdf print of the page.")."<br /><br />";
	echo _("Credit:     Star data made available by Tycho2+")."<br /><br />";
	
	echo "</div>"; 
	echo "<div id=\"atlasPageDiv3\" class=\"atlasPageDiv3\" >";
	echo _("Abbreviations page - use A to turn it on or off");
	echo "<hr /><br />";
	
	
	$types = $objObject->getDsObjectTypes();
	foreach ($types as $key=>$value) {
      $stypes[$value] = $GLOBALS[$value];
    }
	asort($stypes);
	
	$i=0;
	echo "<table class=\"abbrevTable\">";
	foreach ($types as $key=>$value)
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
	foreach ($constellations as $key=>$value) {
      $cons[$value] = $GLOBALS[$value];
    }
	//$cons=asort($cons);
	$i=0;
	echo "<table class=\"abbrevTable\">";
	foreach ($cons as $key=>$value)
	{ if(!($i++%4))
	    echo "<tr class=\"abbrevTable\">";
	  echo "<td class=\"abbrevTable\">".$key . "&nbsp;".$value."</td>"; 
	  if(!($i%4))
	    echo "</tr>";
	}
	if($i%4)
	  echo "</tr>";
	echo "</table><br /><br />";
	
	echo _("Print:      By clicking p you can get a pdf print of the page.")."<br /><br />";
	echo _("Credit:     Star data made available by Tycho2+")."<br /><br />";
	
	echo "</div>"; 
	echo "</div>";
}
?>