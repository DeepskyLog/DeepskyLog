<?php
global $objObject,$baseURL;


echo "<h4>" . _("Image Catalogs") . "</h4>";

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
         <div class=\"carousel-caption\" style=\"background: gray;\"><p>"
        . _("DeepskyLog is a very powerful tool, where you can create personalized atlases and image catalogs.") .
         "</p>
         </div>
        </div>
        <div class=\"item\">
         <img src=\"". $baseURL . "images/AbellExample2.png\" alt=\"...\">
         <div class=\"carousel-caption\" style=\"background: gray;\"><p>"
        . _("However, making your own image catalog is time consuming. To help you, we created some interesting image catalogs and made them available for download.")
        . _("They are very useful for telescopes with a goto system, where the goto system guides you to the neighbourhood of the object. Using the images, it is very easy to find the final object.")
        . "</p>
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
       <tr><th class=\"catalog\">" . _("Catalogs sorted by name") . "</th></tr>
       <tr><td><a href=\"" . $dirAstroImageCatalogs . "Abell.pdf\">" 
    . _("The Abell Planetary Nebula Catalog") . "</a></td></tr>
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
       <tr><th colspan=\"3\" class=\"catalog\">" . _("Catalogs sorted by constellation") . "</th></tr>";

// We have to add these manually, because we don't have all the files



foreach ($constellations as $key=>$value)
{  $cons[$value] = $GLOBALS[$value];
}
asort($cons);
reset($cons);
$count = 0;
echo "<tr>";
foreach ($cons as $key=>$value) {
  if ($count % 3 == 0) {
    print "</tr><tr>";
  }
  echo "<td class=\"catalog\"><a href=\"".$dirAstroImageCatalogs."constellations/".$key.".pdf\">".${$key}."</a></td>";
  if ($count % 3 == 0) {
  }
  $count++;
}
echo "</tr>";
echo "</table>";
?>
