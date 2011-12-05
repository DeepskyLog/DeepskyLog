<?php
// objectSets.php
// allows the user to generate a pdf series with object data, DSS photos, DSL charts an index pages

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else atlasPages();


function atlasPages()
{ global $objObserver, $loggedUser, $baseURL, $loggedUserName, $objReportLayout, $objUtil, $MSIE,$dirAstroImageCatalogs;
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
	
  echo"<h1>".LangAtlassesIntro00."</h1>";
	echo "<p>".LangAtlassesIntro01."</p>";
	echo "<h2>".LangAtlassesIntro02."</h2>";
	echo "<p>".LangAtlassesIntro03."</p>";
	echo "<h1>".LangAtlassesIntro04."</h1>";
	echo "<hr />";
	echo "<p>".LangAtlassesIntro05."</p>";
  echo '<input type="button" class="width125px" value ="Overview" onclick="location.href=\''.$dirAstroImageCatalogs.'OverviewAtlas.pdf\';" />';
  echo '<input type="button" class="width125px" value ="Lookup" onclick="alert(\'wordt geïmplementeerd\');" />';
  echo '<input type="button" class="width125px" value ="Detail" onclick="alert(\'wordt geïmplementeerd\');" />';
	echo "<h1>".LangAtlassesIntro06."</h1>";
	echo "<hr />";
	echo "<p>".LangAtlassesIntro07."</p>";
  echo "<p>";
  echo LangpdfOrientation;
  echo '<input type="radio" id="pageorientationlandscape" name="pageorientation" value="landscape" />'.LangpdfOrientationLandscape;
  echo '<input type="radio" id="pageorientationportrait" name="pageorientation" value="portrait" />'.LangpdfOrientationPortrait;
  echo "</p>";
	
  echo "<h2>"."Generate one page:"."</h2>";
  echo 'Generation ra: ';
	echo '<input id="ra" name="ra" type="text" value="" size="5" class="centered"/>';
  echo ' ';
  echo 'decl: ';
	echo '<input id="decl" name="decl" type="text" value="" size="5" class="centered"/>';
  echo '<br />';
  echo "<p>";
  echo '<input type="button" class="width125px" value ="Overview" onclick="generateoneoverview(0,'.($MSIE?'\'true\'':'\'false\'').');" />';
  echo '<input type="button" class="width125px" value ="Lookup" onclick="generateonelookup(0,'.($MSIE?'\'true\'':'\'false\'').');" />';
  echo '<input type="button" class="width125px" value ="Detail" onclick="generateonedetail(0,'.($MSIE?'\'true\'':'\'false\'').');" />';
  echo "</p>";
  echo "<h2>"."Generate complete altas (not available in Internet Explorer)"."</h2>";
  if(!($MSIE))
  { echo"<p>";
    echo "<input type=\"button\" class=\"width125px\" value=\"Overview ".LangpdfseriesButton."\" onclick=\"generateoverviewallonepass(0,".($MSIE?'true':'false').",24,180);\"/>";
    echo "<input type=\"button\" class=\"width125px\" value=\"Lookup ".LangpdfseriesButton."\" onclick=\"generatelookupallonepass(0,".($MSIE?'true':'false').",24,180);\"/>";
    echo "<input type=\"button\" class=\"width125px\" value=\"Detail ".LangpdfseriesButton."\" onclick=\"generatedetailallonepass(0,".($MSIE?'true':'false').",24,180);\"/>";
    echo "</p>";
  }
  echo "&nbsp;"."<div id='thecounter'> &nbsp; </div>";
  echo "<hr />";
  $declfrom=10;
  $declto=15;
  $rafrom=0;
  $rato=24;
  //echo 'Declination from: ';
	echo '<input id="declfrom" name="declfrom" type="text" value="80" size="5" class="centered" />';
	//echo '<br />';
	//echo 'Declination to: ';
	echo '<input id="declto" name="declto" type="text" value="87" size="5" class="centered" />';
	//echo '<br />';
	//echo 'RA from: ';
	echo '<input id="rafrom" name="rafrom" type="text" value="0" size="5" class="centered" />';
	//echo '<br />';
	//echo 'RA to: ';
	echo '<input id="rato" name="rato" type="text" value="24" size="5" class="centered" />';
  //echo '<br />';
	//echo 'Stars to magnitude: ';
	echo '<input id="stars" name="stars" type="text" value="10" size="5" class="centered" />';
  //echo '<br />';
	//echo 'Objects to magnitude: ';
	echo '<input id="dsos" name="dsos" type="text" value="13" size="5" class="centered" />';
  //echo '<br />';
	//echo 'Overlap: ';
	echo '<input id="theoverlap" name="theoverlap" type="text" value="0.15" size="5" class="centered" />';
  //echo '<br />';
	//echo 'Zoom: ';
	echo '<input id="zoom" name="zoom" type="text" value="16" size="5" class="centered" />';
  //echo '<br />';
  
}
?>
