<?php
// objectSets.php
// allows the user to generate a pdf series with object data, DSS photos, DSL charts an index pages

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else atlasPages();


function atlasPages()
{ global $objObserver, $loggedUser, $baseURL, $loggedUserName, $objReportLayout, $objUtil, $MSIE,$dirAtlasses, $language;
  echo "<script type=\"text/javascript\">";
  echo "var Langpdfseriesclickok='".Langpdfseriesclickok."';";
  echo "var Langpdfserieswhenfinished='".Langpdfserieswhenfinished."';";
  echo "var LangpdfseriesGenerating='".LangpdfseriesGenerating."';";
  echo "var Langpdfserieschoselayout='".Langpdfserieschoselayout."';";
  echo "</script>";

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

	echo "<h3>".LangAtlasChoosePageLayout."</h3>";
	echo "<hr />";
	echo "<p>";
	echo LangAtlasPageOrientation;
	echo '<input type="radio" id="pageorientationportrait" name="pageorientation" value="portrait" checked="checked"/>'.LangpdfOrientationPortrait;
	echo '<input type="radio" id="pageorientationlandscape" name="pageorientation" value="landscape" />'.LangpdfOrientationLandscape;
	echo "</p>";
	echo "<p>";
	echo LangAtlasPageSize;
	echo '<input type="radio" id="pagesizea4" name="pagesize" value="a4" checked="checked"/>'.'A4';
	echo '<input type="radio" id="pagesizea3" name="pagesize" value="a3" />'.'A3';
	echo "</p>";
	echo "<hr />";
	
	
	echo "<h1>".LangAtlassesIntro04."</h1>";
	echo "<hr />";
	echo "<p>".LangAtlassesIntro05."</p>";
	echo '<input type="button" class="width200px" value ="'.LangAtlasButton1.'" onclick="location.href=\''.$dirAtlasses.strtoupper($_SESSION['lang']).'\'+(document.getElementById(\'pagesizea3\').checked?\'A3\':\'A4\')+\'O\'+(document.getElementById(\'pageorientationportrait\').checked?\'P\':\'L\')+\'.pdf\';" /> ';
  echo '<input type="button" class="width200px" value ="'.LangAtlasButton2.'" onclick="location.href=\''.$dirAtlasses.strtoupper($_SESSION['lang']).'\'+(document.getElementById(\'pagesizea3\').checked?\'A3\':\'A4\')+\'L\'+(document.getElementById(\'pageorientationportrait\').checked?\'P\':\'L\')+\'.pdf\';" /> ';
  echo '<input type="button" class="width200px" value ="'.LangAtlasButton3.'" onclick="location.href=\''.$dirAtlasses.strtoupper($_SESSION['lang']).'\'+(document.getElementById(\'pagesizea3\').checked?\'A3\':\'A4\')+\'D\'+(document.getElementById(\'pageorientationportrait\').checked?\'P\':\'L\')+\'.pdf\';" /> ';

  echo "<h1>".LangAtlassesIntro06."</h1>";
	echo'<p><b>'.LangAtlasBrowserInfo.'</b></p>';
	echo "<p>".LangAtlassesIntro07."</p>";
	echo "<hr />";
  
  echo "<h2>".LangAtlasGenerateOnePage."</h2>";
  echo LangPageGenerationra.' ';
	echo '<input id="rah" name="rah" type="text" size="5" class="centered" value="0"/>';
	echo '<input id="ram" name="ram" type="text" size="5" class="centered" value="0"/>';
	echo '<input id="ras" name="ras" type="text" size="5" class="centered" value="0"/>';
	echo ' ';
  echo LangPageGenerationdecl.' ';
	echo '<input id="declh" name="declh" type="text" size="5" class="centered" value="0"/>';
	echo '<input id="declm" name="declm" type="text" size="5" class="centered" value="0"/>';
	echo '<input id="decls" name="decls" type="text" size="5" class="centered" value="0"/>';
	echo '<br />';
  echo "<p>";
  echo '<input type="button" class="width200px" value ="'.LangAtlasButton4.'" onclick="generateoneoverview(0,'.($MSIE?'\'true\'':'\'false\'').');" /> ';
  echo '<input type="button" class="width200px" value ="'.LangAtlasButton5.'" onclick="generateonelookup(0,'.($MSIE?'\'true\'':'\'false\'').');" /> ';
  echo '<input type="button" class="width200px" value ="'.LangAtlasButton6.'" onclick="generateonedetail(0,'.($MSIE?'\'true\'':'\'false\'').');" />';
  echo "</p>";
  echo "<h2>".LangAtlasGenerateCompleteAtlas."</h2>";
  echo "<p>".LangAtlasGenerationTimes."</p>";
  
  if(!($MSIE))
  { echo"<p>";
    echo "<input type=\"button\" class=\"width200px\" value=\"".LangAtlasButton7."\" onclick=\"generateoverviewallonepass(0,".($MSIE?'true':'false').",24,180);\"/> ";
    echo "<input type=\"button\" class=\"width200px\" value=\"".LangAtlasButton8."\" onclick=\"generatelookupallonepass(0,".($MSIE?'true':'false').",24,180);\"/> ";
    echo "<input type=\"button\" class=\"width200px\" value=\"".LangAtlasButton9."\" onclick=\"generatedetailallonepass(0,".($MSIE?'true':'false').",24,180);\"/>";
    echo "</p>";
  }
  echo "&nbsp;"."<div id='thecounter'> &nbsp; </div>";
  echo "<hr />";
}
?>
