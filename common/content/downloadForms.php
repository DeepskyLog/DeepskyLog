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

  $exampleText = _("Example page for Abell 84");
  echo"<h3>"._('Sketch and observation forms')."</h3>";

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
         _('Compact sketch form') . "<br />" . _('A compact sketch form that folds in half so you can do two sketches on one piece of paper. <br/>This format allows you to sketch on top of a book like your PSA.') .
           "</p>
         </div>
        </div>
        <div class=\"item\" style=\"text-align:center;\">
         <img src=\"". $baseURL . "images/FormsExample1.png\" alt=\"...\">
         <div class=\"carousel-caption\" style=\"background: gray;\"><p>" .
         _('Big sketch form') . "<br />" . _('Utilizes the maximum amount of drawing space for your sketch. With a 17cm sketch circle and simplified details area.') .
           "</p>
         </div>
        </div>
        <div class=\"item\">
         <img src=\"". $baseURL . "images/FormsExample2.png\" alt=\"...\">
         <div class=\"carousel-caption\" style=\"background: gray;\"><p>" .
         _('Observation log form') . "<br />" . _('If you want to jot down multiple brief observation notes on one piece of paper.') .
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



	echo "<h3>"._('Compact sketch form')."</h3>";
	echo "<p>"._('A compact sketch form that folds in half so you can do two sketches on one piece of paper. <br/>This format allows you to sketch on top of a book like your PSA.')."</p>";
	echo "<a class=\"btn btn-success\" href=\"/downloads/Sketch.pdf\"><span class=\"glyphicon glyphicon-download\"></span>&nbsp;"._('Download')."</a>";

	echo "<h3>"._('Big sketch form')."</h3>";
	echo "<p>"._('Utilizes the maximum amount of drawing space for your sketch. With a 17cm sketch circle and simplified details area.')."</p>";
	echo "<a class=\"btn btn-success\" href=\"/downloads/Sketch big.pdf\"><span class=\"glyphicon glyphicon-download\"></span>&nbsp;"._('Download')."</a>";


	echo "<h3>"._('Observation log form')."</h3>";
	echo "<p>"._('If you want to jot down multiple brief observation notes on one piece of paper.')."</p>";
	echo "<a class=\"btn btn-success\" href=\"/downloads/Observation log.pdf\"><span class=\"glyphicon glyphicon-download\"></span>&nbsp;"._('Download')."</a>";
}
?>
