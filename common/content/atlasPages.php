<?php
// objectSets.php
// allows the user to generate a pdf series with object data, DSS photos, DSL charts an index pages

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else atlasPages();


function atlasPages()
{ global $objObserver, $loggedUser, $baseURL, $loggedUserName, $objReportLayout, $objUtil, $MSIE,$dirAtlasses;
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
	
  $exampleText = AtlasExample;
  echo "<a href=\"" . $baseURL . "images/AtlasExample.jpg\" rel=\"prettyPhoto\" title=\"\">";
  echo "<img class=\"floatright\" width=\"30%\" src=\"" . $baseURL . "images/AtlasExample.jpg\"  alt=\"" . $exampleText . "\"/>";
  echo "</a>";
	echo"<h1>".LangAtlassesIntro00."</h1>";
	echo "<p>".LangAtlassesIntro01."</p>";
	echo "<h2>".LangAtlassesIntro02."</h2>";
	echo "<p>".LangAtlassesIntro03."</p>";
	echo "<h1>".LangAtlassesIntro04."</h1>";
	echo "<hr />";
	echo "<p>".LangAtlassesIntro05."</p>";
  echo '<input type="button" class="width200px" value ="Overview" onclick="location.href=\''.$dirAtlasses.'OverviewAtlas.pdf\';" /> ';
  echo '<input type="button" class="width200px" value ="Lookup" onclick="location.href=\''.$dirAtlasses.'LookupAtlas.pdf\');" /> ';
  echo '<input type="button" class="width200px" value ="Detail" onclick="alert(\'wordt ge&iuml;mplementeerd\');" />';
	echo "<h1>".LangAtlassesIntro06."</h1>";
	echo "<hr />";
	echo "<p>".LangAtlassesIntro07."</p>";
  echo "<p>";
  echo LangpdfOrientation;
  echo '<input type="radio" id="pageorientationportrait" name="pageorientation" value="portrait" checked="checked"/>'.LangpdfOrientationPortrait;
  echo '<input type="radio" id="pageorientationlandscape" name="pageorientation" value="landscape" />'.LangpdfOrientationLandscape;
  echo "</p>";
  echo "<p>";
  echo 'Page size: ';
  echo '<input type="radio" id="pagesizea4" name="pagesize" value="a4" checked="checked"/>'.'A4';
  echo '<input type="radio" id="pagesizea3" name="pagesize" value="a3" />'.'A3';
  echo "</p>";
  
  echo "<h2>"."Generate one page:"."</h2>";
  echo 'Generation ra: ';
	echo '<input id="rah" name="rah" type="text" size="5" class="centered" value="0"/>';
	echo '<input id="ram" name="ram" type="text" size="5" class="centered" value="0"/>';
	echo '<input id="ras" name="ras" type="text" size="5" class="centered" value="0"/>';
	echo ' ';
  echo 'decl: ';
	echo '<input id="declh" name="declh" type="text" size="5" class="centered" value="0"/>';
	echo '<input id="declm" name="declm" type="text" size="5" class="centered" value="0"/>';
	echo '<input id="decls" name="decls" type="text" size="5" class="centered" value="0"/>';
	echo '<br />';
  echo "<p>";
  echo '<input type="button" class="width200px" value ="Overview" onclick="generateoneoverview(0,'.($MSIE?'\'true\'':'\'false\'').');" /> ';
  echo '<input type="button" class="width200px" value ="Lookup" onclick="generateonelookup(0,'.($MSIE?'\'true\'':'\'false\'').');" /> ';
  echo '<input type="button" class="width200px" value ="Detail" onclick="generateonedetail(0,'.($MSIE?'\'true\'':'\'false\'').');" />';
  echo "</p>";
  echo "<h2>"."Generate complete altas (not available in Internet Explorer)"."</h2>";
  if(!($MSIE))
  { echo"<p>";
    echo "<input type=\"button\" class=\"width200px\" value=\"Overview ".LangpdfseriesButton."\" onclick=\"generateoverviewallonepass(0,".($MSIE?'true':'false').",24,180);\"/> ";
    echo "<input type=\"button\" class=\"width200px\" value=\"Lookup ".LangpdfseriesButton."\" onclick=\"generatelookupallonepass(0,".($MSIE?'true':'false').",24,180);\"/> ";
    echo "<input type=\"button\" class=\"width200px\" value=\"Detail ".LangpdfseriesButton."\" onclick=\"generatedetailallonepass(0,".($MSIE?'true':'false').",24,180);\"/>";
    echo "</p>";
  }
  echo "&nbsp;"."<div id='thecounter'> &nbsp; </div>";
  echo "<hr />";
}
?>
