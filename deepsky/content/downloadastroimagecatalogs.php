<?php
global $objObject,$baseURL;


echo "<h4>" . LangSearchMenuItem14 . "</h4>";

echo "<div id=\"carousel-example-generic\" class=\"carousel slide\" data-ride=\"carousel\"  data-interval=\"10000\">
       <!-- Indicators -->
       <ol class=\"carousel-indicators\">
        <li data-target=\"#carousel-example-generic\" data-slide-to=\"0\" class=\"active\"></li>
        <li data-target=\"#carousel-example-generic\" data-slide-to=\"1\"></li>
       </ol>

       <!-- Wrapper for slides -->
       <div class=\"carousel-inner\">
        <div class=\"item active\">
         <img src=\"". $baseURL . "images/AbellExample.png\" alt=\"...\">
         <div class=\"carousel-caption\" style=\"background: gray;\"><p>" .
         		 ImageCatalogDescription1 .
         "</p>
         </div>
        </div>
        <div class=\"item\">
         <img src=\"". $baseURL . "images/AbellExample2.png\" alt=\"...\">
         <div class=\"carousel-caption\" style=\"background: gray;\"><p>" .
         		 ImageCatalogDescription2 . ImageCatalogDescription3 .
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

echo "<br />";
echo "<table class=\"table\">
       <tr><th class=\"catalog\">" . ImageCatalogDescription4 . "</th></tr>
       <tr><td><a href=\"" . $dirAstroImageCatalogs . "Abell.pdf\">" . ImageCatalogAbell . "</a></td></tr>
      </table>";

$constellations = Array("AND", "ANT", "AQL", "AQR", "ARI", "AUR", "BOO", "CAE", "CAM", "CAP", 
                        "CAS", "CEN", "CEP", "CET", "CMA", "CMI", "CNC", "COL", "COM", "CRA", 
                        "CRB", "CRT", "CRV", "CVN", "CYG", "DEL", "DRA", "EQU", "ERI", "FOR", 
                        "GEM", "GRU", "HER", "HOR", "HYA", "LAC", "LEO", "LEP", "LIB", "LMI", 
                        "LUP", "LYN", "LYR", "MIC", "MON", "OPH", "ORI", "PEG", "PER", "PHE", 
                        "PSA", "PSC", "PUP", "PYX", "SCL", "SCO", "SCT", "SER", "SGE", "SGR", 
                        "TAU", "TRI", "UMA", "UMI", "VIR", "VUL");


// Add for constellations
echo "<br />";
echo "<table class=\"table\">
       <tr><th colspan=\"3\" class=\"catalog\">" . ImageCatalogDescription5 . "</th></tr>";

// We have to add these manually, because we don't have all the files



while(list($key, $value) = each($constellations))
{  $cons[$value] = $GLOBALS[$value];
}
asort($cons);
reset($cons);
$count = 0;
echo "<tr>";
while(list($key, $value) = each($cons)) {
  if ($count % 3 == 0) {
    print "</tr><tr>";
  }
  echo "<td class=\"catalog\"><a href=\"".$dirAstroImageCatalogs."constellations/".$key.".pdf\">".$$key."</a></td>";
  if ($count % 3 == 0) {
  }
  $count++;
}
echo "</tr>";
echo "</table>";
?>