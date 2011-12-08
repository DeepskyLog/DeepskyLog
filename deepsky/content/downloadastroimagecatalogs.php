<?php
global $objObject,$baseURL;

$exampleText = "Example page for Abell84";
echo "<a href=\"" . $baseURL . "images/AbellExample.jpg\" rel=\"prettyPhoto\" title=\"\">";
echo "<img class=\"floatright\" width=\"30%\" src=\"" . $baseURL . "images/AbellExample.jpg\"  alt=\"" . $exampleText . "\"/>";
echo "</a>";

echo "DeepskyLog is a very powerful tool, where you can create a personalized atlasses and image catalogs.";
echo "<br />However, making your own image catalog is time consuming. To help you, we created some interesting image catalogs and make them available for download.";
echo "You can click the example to get a preview of how the image catalogs look like. They are very useful for telescopes with a goto system, where the goto system guides you to the neighbourhood of the object. Using the images, it is very easy to find the final object.";

echo "<br />";
echo "<table>
       <tr><th class=\"catalog\">Catalogs sorted by name</th></tr>
       <tr><td><a href=\"" . $dirAstroImageCatalogs . "Abell.pdf\">The Abell Planetary Nebula Catalog</a></td></tr>
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
echo "<table>
       <tr><th colspan=\"3\" class=\"catalog\">Catalogs sorted by constellation</th></tr>";

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