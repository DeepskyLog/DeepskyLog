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
  echo"<h3>".LangAtlassesIntro00."</h3>";
  
  echo "<div id=\"carousel-example-generic\" class=\"carousel slide\" data-ride=\"carousel\"  data-interval=\"5000\">
       <!-- Indicators -->
       <ol class=\"carousel-indicators\">
        <li data-target=\"#carousel-example-generic\" data-slide-to=\"0\" class=\"active\"></li>
        <li data-target=\"#carousel-example-generic\" data-slide-to=\"1\"></li>
        <li data-target=\"#carousel-example-generic\" data-slide-to=\"2\"></li>
  		</ol>
  
       <!-- Wrapper for slides -->
       <div class=\"carousel-inner\">
        <div class=\"item active\">
         <img src=\"". $baseURL . "images/AtlasExample.jpg\" alt=\"...\">
         <div class=\"carousel-caption\" style=\"background: gray;\"><p>" .
           LangAtlassesIntro01 . "<br />" . LangAtlassesIntro01c .
           "</p>
         </div>
        </div>
        <div class=\"item\">
         <img src=\"". $baseURL . "images/AtlasExample1.jpg\" alt=\"...\">
         <div class=\"carousel-caption\" style=\"background: gray;\"><p>" .
           LangAtlassesIntro01a . "<br />" . LangAtlassesIntro01c .
           "</p>
         </div>
        </div>
        <div class=\"item\">
         <img src=\"". $baseURL . "images/AtlasExample2.jpg\" alt=\"...\">
         <div class=\"carousel-caption\" style=\"background: gray;\"><p>" .
           LangAtlassesIntro01b . "<br />" . LangAtlassesIntro01c .
           "</p>
         </div>
        </div>
       </div>
  
      	<!-- Controls -->
        <a class=\"left carousel-control\" href=\"#carousel-example-generic\" data-slide=\"prev\">
         <span class=\"glyphicon glyphicon-chevron-left\"></span>
        </a>
        <a class=\"right carousel-control\" href=\"#carousel-example-generic\" data-slide=\"next\">
         <span class=\"glyphicon glyphicon-chevron-right\"></span>
        </a>";
  echo "</div>";
  
  
  
	echo "<h3>".LangAtlassesIntro02."</h3>";
	echo "<p>".LangAtlassesIntro03."</p>";

	echo "<h4>".LangAtlasChoosePageLayout."</h4>";
	echo "<hr />";
	echo "<form role=\"form\">";

	echo LangAtlasPageOrientation . "&nbsp;";
	echo "<div class=\"btn-group\" data-toggle=\"buttons\">
			<label class=\"btn btn-default active\">";
	echo '<input type="radio" id="pageorientationportrait" name="pageorientation" value="portrait" checked="checked"/>'.LangpdfOrientationPortrait;
	echo "  </label>";
	echo "  <label class=\"btn btn-default\">";
	echo '<input type="radio" id="pageorientationlandscape" name="pageorientation" value="landscape" />'.LangpdfOrientationLandscape;
	echo "  </label>
	      </div>";
	echo "<br /><br />";
	echo LangAtlasPageSize . "&nbsp;";
	echo "<div class=\"btn-group\" data-toggle=\"buttons\">
			<label class=\"btn btn-default active\">";
	echo '<input type="radio" id="pagesizea4" name="pagesize" value="a4" checked="checked"/>'.'A4';
	echo "  </label>";
	echo "  <label class=\"btn btn-default\">";
	echo '<input type="radio" id="pagesizea3" name="pagesize" value="a3" />'.'A3';
	echo "  </label>";
	echo "</div>";
	echo "<hr />";
	
	
    echo "<h3>".LangAtlassesIntro04."</h3>";
    echo "<p>".LangAtlassesIntro05."</p>";
    echo '<input type="button" class="btn btn-default" value ="'.LangAtlasButton1.'" onclick="location.href=\''.$dirAtlasses.strtoupper($_SESSION['lang']).'\'+(document.getElementById(\'pagesizea3\').checked?\'A3\':\'A4\')+\'O\'+(document.getElementById(\'pageorientationportrait\').checked?\'P\':\'L\')+\'.pdf\';" /> ';
    echo '<input type="button" class="btn btn-default" value ="'.LangAtlasButton2.'" onclick="location.href=\''.$dirAtlasses.strtoupper($_SESSION['lang']).'\'+(document.getElementById(\'pagesizea3\').checked?\'A3\':\'A4\')+\'L\'+(document.getElementById(\'pageorientationportrait\').checked?\'P\':\'L\')+\'.pdf\';" /> ';
    echo '<input type="button" class="btn btn-default" value ="'.LangAtlasButton3.'" onclick="location.href=\''.$dirAtlasses.strtoupper($_SESSION['lang']).'\'+(document.getElementById(\'pagesizea3\').checked?\'A3\':\'A4\')+\'D\'+(document.getElementById(\'pageorientationportrait\').checked?\'P\':\'L\')+\'.pdf\';" /> ';

	echo "<hr />";
    echo "<h3>".LangAtlassesIntro06."</h3>";
	echo'<p><b>'.LangAtlasBrowserInfo.'</b></p>';
	echo "<p>".LangAtlassesIntro07."</p>";
	echo "<hr />";
  
  echo "<h4>".LangAtlasGenerateOnePage."</h4>";
	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-3 control-label\">" . LangPageGenerationra . "</label>";
	echo "<div class=\"col-sm-9 form-inline\">";
    echo '<input id="rah" name="rah" type="number" min="0" max="23" size="5" class="form-control" value="0"/>';
	echo '<input id="ram" name="ram" type="number" min="0" max="59" size="5" class="form-control" value="0"/>';
	echo '<input id="ras" name="ras" type="number" min="0" max="59" size="5" class="form-control" value="0"/>';
	echo "</div></div>";
	echo "<br />";
	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-3 control-label\">" . LangPageGenerationdecl . "</label>";
	echo "<div class=\"col-sm-9 form-inline\">";
	echo '<input id="declh" name="declh" type="number" min="-89" max="89" size="5" class="form-control" value="0"/>';
	echo '<input id="declm" name="declm" type="number" min="0" max="59" size="5" class="form-control" value="0"/>';
	echo '<input id="decls" name="decls" type="number" min="0" max="59" size="5" class="form-control" value="0"/>';
	echo "</div></div>";
	echo '<br />';
  echo "<br />";
  echo '<input type="button" class="btn btn-default" value ="'.LangAtlasButton4.'" onclick="generateoneoverview(0,'.($MSIE?'\'true\'':'\'false\'').');" /> ';
  echo '<input type="button" class="btn btn-default" value ="'.LangAtlasButton5.'" onclick="generateonelookup(0,'.($MSIE?'\'true\'':'\'false\'').');" /> ';
  echo '<input type="button" class="btn btn-default" value ="'.LangAtlasButton6.'" onclick="generateonedetail(0,'.($MSIE?'\'true\'':'\'false\'').');" />';
	echo "<hr />";
  echo "<h4>".LangAtlasGenerateCompleteAtlas."</h4>";
  echo "<p>".LangAtlasGenerationTimes."</p>";
  
  if(!($MSIE))
  { echo"<p>";
    echo "<input type=\"button\" class=\"btn btn-default\" value=\"".LangAtlasButton7."\" onclick=\"generateoverviewallonepass(0,".($MSIE?'true':'false').",24,180);\"/> ";
    echo "<input type=\"button\" class=\"btn btn-default\" value=\"".LangAtlasButton8."\" onclick=\"generatelookupallonepass(0,".($MSIE?'true':'false').",24,180);\"/> ";
    echo "<input type=\"button\" class=\"btn btn-default\" value=\"".LangAtlasButton9."\" onclick=\"generatedetailallonepass(0,".($MSIE?'true':'false').",24,180);\"/>";
    echo "</p>";
  }
  echo "</form>";
  echo "&nbsp;"."<div id='thecounter'> &nbsp; </div>";
  echo "<hr />";
}
?>
