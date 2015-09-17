<?php
// objectSets.php
// allows the user to generate a pdf series with object data, DSS photos, DSL charts an index pages

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else downloadForms();


function downloadForms()
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
  echo"<h3>".LangDownloadFormsIntro00."</h3>";
  
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
         <img src=\"". $baseURL . "images/FormsExample.png\" alt=\"...\">
         <div class=\"carousel-caption\" style=\"background: gray;\"><p>" .
           LangDownloadFormsIntro02 . "<br />" . LangDownloadFormsIntro03 .
           "</p>
         </div>
        </div>
        <div class=\"item\" style=\"text-align:center;\">
         <img src=\"". $baseURL . "images/FormsExample1.png\" alt=\"...\">
         <div class=\"carousel-caption\" style=\"background: gray;\"><p>" .
           LangDownloadFormsIntro04 . "<br />" . LangDownloadFormsIntro05 .
           "</p>
         </div>
        </div>
        <div class=\"item\">
         <img src=\"". $baseURL . "images/FormsExample2.png\" alt=\"...\">
         <div class=\"carousel-caption\" style=\"background: gray;\"><p>" .
           LangDownloadFormsIntro06 . "<br />" . LangDownloadFormsIntro07 .
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
  
  
  
	echo "<h3>".LangDownloadFormsIntro02."</h3>";
	echo "<p>".LangDownloadFormsIntro03."</p>";
	echo "<a class=\"btn btn-success\" href=\"/downloads/Sketch.pdf\"><span class=\"glyphicon glyphicon-download\"></span>".LangDownloadFormsDownload."</a>";
	
	echo "<h3>".LangDownloadFormsIntro04."</h3>";
	echo "<p>".LangDownloadFormsIntro05."</p>";
	echo "<a class=\"btn btn-success\" href=\"/downloads/Sketch big.pdf\"><span class=\"glyphicon glyphicon-download\"></span>".LangDownloadFormsDownload."</a>";
	
	
	echo "<h3>".LangDownloadFormsIntro06."</h3>";
	echo "<p>".LangDownloadFormsIntro07."</p>";
	echo "<a class=\"btn btn-success\" href=\"/downloads/Observation log.pdf\"><span class=\"glyphicon glyphicon-download\"></span>".LangDownloadFormsDownload."</a>";
}
?>
