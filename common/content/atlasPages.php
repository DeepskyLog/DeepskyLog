<?php
// objectSets.php
// allows the user to generate a pdf series with object data, DSS photos, DSL charts an index pages

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else atlasPages();


function atlasPages()
{ global $objObserver, $loggedUser, $baseURL, $loggedUserName, $objReportLayout, $objUtil, $MSIE;
  echo "<script type=\"text/javascript\">";
  echo "var Langpdfseriesclickok='".Langpdfseriesclickok."';";
  echo "var Langpdfserieswhenfinished='".Langpdfserieswhenfinished."';";
  echo "var LangpdfseriesGenerating='".LangpdfseriesGenerating."';";
  echo "var Langpdfserieschoselayout='".Langpdfserieschoselayout."';";
  echo "</script>";
  $fovo=$objObserver->getObserverProperty($loggedUser,'overviewFoV','');
  $fovl=$objObserver->getObserverProperty($loggedUser,'lookupFoV','');
  $fovd=$objObserver->getObserverProperty($loggedUser,'detailFoV','');
  $dsoso=$objObserver->getObserverProperty($loggedUser,'overviewdsos','');
  $dsosl=$objObserver->getObserverProperty($loggedUser,'lookupdsos','');
  $dsosd=$objObserver->getObserverProperty($loggedUser,'detaildsos','');
  $starso=$objObserver->getObserverProperty($loggedUser,'overviewstars','');
  $starsl=$objObserver->getObserverProperty($loggedUser,'lookupstars','');
  $starsd=$objObserver->getObserverProperty($loggedUser,'detailstars','');
  $foto1=$objObserver->getObserverProperty($loggedUser,'photosize1','');
  $foto2=$objObserver->getObserverProperty($loggedUser,'photosize2','');

  echo "<script type=\"text/javascript\" src=\"".$baseURL."common/content/atlasPages.js\"></script>";
	echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/phpjs.js\"></script>";
	
	echo 'Declination from: ';
	echo '<input id="declfrom" name="declfrom" type="text" value="80" size="5" class="centered"/>';
	echo '<br />';
	echo 'Declination to: ';
	echo '<input id="declto" name="declto" type="text" value="87" size="5" class="centered"/>';
	echo '<br />';
	echo 'RA from: ';
	echo '<input id="rafrom" name="rafrom" type="text" value="0" size="5" class="centered"/>';
	echo '<br />';
	echo 'RA to: ';
	echo '<input id="rato" name="rato" type="text" value="24" size="5" class="centered"/>';
  echo '<br />';
	echo 'Stars to magnitude: ';
	echo '<input id="stars" name="stars" type="text" value="10" size="5" class="centered"/>';
  echo '<br />';
	echo 'Objects to magnitude: ';
	echo '<input id="dsos" name="dsos" type="text" value="13" size="5" class="centered"/>';
  echo '<br />';
	echo 'Overlap: ';
	echo '<input id="theoverlap" name="theoverlap" type="text" value="0.1" size="5" class="centered"/>';
  echo '<br />';
	echo 'Zoom: ';
	echo '<input id="zoom" name="zoom" type="text" value="16" size="5" class="centered"/>';
  echo '<br />';
  echo 'Generation ra: ';
	echo '<input id="ra" name="ra" type="text" value="" size="5" class="centered"/>';
  echo ' ';
  echo 'decl: ';
	echo '<input id="decl" name="decl" type="text" value="" size="5" class="centered"/>';
  echo '<br />';
  
  
  if(!($MSIE))
	  echo "<input type=\"button\" value=\"".LangpdfseriesButton."\" onclick=\"generateallonepass(0,".($MSIE?'true':'false').",24,180);\"/>";
	echo "&nbsp;"."<div id='thecounter'> &nbsp; </div>";
	echo '<br />';	
  echo "<hr />";
  $declfrom=10;
  $declto=15;
  $rafrom=0;
  $rato=24;
  
  
}
?>
