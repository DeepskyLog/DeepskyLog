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
	
  $exampleText = _("Lookup atlas page for M 45");
  echo"<h3>"._('DeepskyLog atlases')."</h3>";
  
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
         _("The <b>overview</b> atlases have pages of 20 degrees, and show stars to magnitude 10.") 
        . "<br />" . _("The atlases mention if the objects are already seen in Deepskylog (dotted underline) or by yourself (personalised atlases for registered users, full underline, or overline if sketched).") .
           "</p>
         </div>
        </div>
        <div class=\"item\">
         <img src=\"". $baseURL . "images/AtlasExample1.jpg\" alt=\"...\">
         <div class=\"carousel-caption\" style=\"background: gray;\"><p>" .
         _("The <b>lookup</b> atlases have pages of 12 degrees, and show stars to magnitude 12.") 
    . "<br />" . _("The atlases mention if the objects are already seen in Deepskylog (dotted underline) or by yourself (personalised atlases for registered users, full underline, or overline if sketched).") .
           "</p>
         </div>
        </div>
        <div class=\"item\">
         <img src=\"". $baseURL . "images/AtlasExample2.jpg\" alt=\"...\">
         <div class=\"carousel-caption\" style=\"background: gray;\"><p>" .
         _("The <b>detail</b> atlases have page of 6 degrees, and show stars to magnitude 15.") 
    . "<br />" . _("The atlases mention if the objects are already seen in Deepskylog (dotted underline) or by yourself (personalised atlases for registered users, full underline, or overline if sketched).") .
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
  
  
  
	echo "<h3>"._('General Atlases / Personalised Atlases')."</h3>";
    echo "<p>"._('You can either go for general atlases, or (if you are a registered user) personalised atlases.<br />- General atlases are precompiled and are ready to download as one big pdf file.<br />- Personalised atlases are generated, page per page, for you in pdf format.<br /><br />Personalised atlases take some time (one page) or much more time (complete atlas) to generate and you will have to use some software to put the pages together yourself for the complete atlas.')."</p>";

	echo "<h4>"._('First choose your page layout')."</h4>";
	echo "<hr />";
	echo "<form role=\"form\">";

	echo _("Page orientation:") . "&nbsp;";
	echo "<div class=\"btn-group\" data-toggle=\"buttons\">
			<label class=\"btn btn-default active\">";
	echo '<input type="radio" id="pageorientationportrait" name="pageorientation" value="portrait" checked="checked"/>'._("portrait");
	echo "  </label>";
	echo "  <label class=\"btn btn-default\">";
	echo '<input type="radio" id="pageorientationlandscape" name="pageorientation" value="landscape" />'._("landscape");
	echo "  </label>
	      </div>";
	echo "<br /><br />";
	echo _("Page size:") . "&nbsp;";
	echo "<div class=\"btn-group\" data-toggle=\"buttons\">
			<label class=\"btn btn-default active\">";
	echo '<input type="radio" id="pagesizea4" name="pagesize" value="a4" checked="checked"/>'.'A4';
	echo "  </label>";
	echo "  <label class=\"btn btn-default\">";
	echo '<input type="radio" id="pagesizea3" name="pagesize" value="a3" />'.'A3';
	echo "  </label>";
	echo "</div>";
	echo "<hr />";
	
	
    echo "<h3>"._('General Atlases')."</h3>";
    echo "<p>"._('Choose one of the three available formats, they will download as a pdf:')."</p>";
    echo '<input type="button" class="btn btn-default" value ="'._("Overview").'" onclick="location.href=\''.$dirAtlasses.strtoupper($_SESSION['lang']).'\'+(document.getElementById(\'pagesizea3\').checked?\'A3\':\'A4\')+\'O\'+(document.getElementById(\'pageorientationportrait\').checked?\'P\':\'L\')+\'.pdf\';" /> ';
    echo '<input type="button" class="btn btn-default" value ="'._("Lookup").'" onclick="location.href=\''.$dirAtlasses.strtoupper($_SESSION['lang']).'\'+(document.getElementById(\'pagesizea3\').checked?\'A3\':\'A4\')+\'L\'+(document.getElementById(\'pageorientationportrait\').checked?\'P\':\'L\')+\'.pdf\';" /> ';
    echo '<input type="button" class="btn btn-default" value ="'._("Detail").'" onclick="location.href=\''.$dirAtlasses.strtoupper($_SESSION['lang']).'\'+(document.getElementById(\'pagesizea3\').checked?\'A3\':\'A4\')+\'D\'+(document.getElementById(\'pageorientationportrait\').checked?\'P\':\'L\')+\'.pdf\';" /> ';

	echo "<hr />";
    echo "<h3>"._('Personalised Atlases')."</h3>";
	echo'<p><b>'._('The personalised atlases are only available in Firefox, Chrome and Opera at this time.').'</b></p>';
	echo "<p>"._("You can download an individual page, with top-left coordinates to be specified by you, or you can download all the pages of the complete helisphere.")."</p>";
	echo "<hr />";
  
  echo "<h4>"._('Generate a page')."</h4>";
	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-3 control-label\">" . _('Right ascension (h m s):') . "</label>";
	echo "<div class=\"col-sm-9 form-inline\">";
    echo '<input id="rah" name="rah" type="number" min="0" max="23" size="5" class="form-control" value="0"/>';
	echo '<input id="ram" name="ram" type="number" min="0" max="59" size="5" class="form-control" value="0"/>';
	echo '<input id="ras" name="ras" type="number" min="0" max="59" size="5" class="form-control" value="0"/>';
	echo "</div></div>";
	echo "<br />";
	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-3 control-label\">" . _('declination (h m s):') . "</label>";
	echo "<div class=\"col-sm-9 form-inline\">";
	echo '<input id="declh" name="declh" type="number" min="-89" max="89" size="5" class="form-control" value="0"/>';
	echo '<input id="declm" name="declm" type="number" min="0" max="59" size="5" class="form-control" value="0"/>';
	echo '<input id="decls" name="decls" type="number" min="0" max="59" size="5" class="form-control" value="0"/>';
	echo "</div></div>";
	echo '<br />';
  echo "<br />";
  echo '<input type="button" class="btn btn-default" value ="'._("Overview - Page").'" onclick="generateoneoverview(0,'.($MSIE?'\'true\'':'\'false\'').');" /> ';
  echo '<input type="button" class="btn btn-default" value ="'._("Lookup - Page").'" onclick="generateonelookup(0,'.($MSIE?'\'true\'':'\'false\'').');" /> ';
  echo '<input type="button" class="btn btn-default" value ="'._("Detail - Page").'" onclick="generateonedetail(0,'.($MSIE?'\'true\'':'\'false\'').');" />';
	echo "<hr />";
  echo "<h4>"._('Generate the whole atlas (not available in Internet Explorer)')."</h4>";
  echo "<p>"._("Generation times may take from 20 minutes (Overview A4) up to several hours (Lookup A3).")."</p>";
  
  if(!($MSIE))
  { echo"<p>";
    echo "<input type=\"button\" class=\"btn btn-default\" value=\""._("Overview - Whole atlas")."\" onclick=\"generateoverviewallonepass(0,".($MSIE?'true':'false').",24,180);\"/> ";
    echo "<input type=\"button\" class=\"btn btn-default\" value=\""._("Lookup - Whole atlas")."\" onclick=\"generatelookupallonepass(0,".($MSIE?'true':'false').",24,180);\"/> ";
    echo "<input type=\"button\" class=\"btn btn-default\" value=\""._("Detail - Whole atlas")."\" onclick=\"generatedetailallonepass(0,".($MSIE?'true':'false').",24,180);\"/>";
    echo "</p>";
  }
  echo "</form>";
  echo "&nbsp;"."<div id='thecounter'> &nbsp; </div>";
  echo "<hr />";
}
?>
