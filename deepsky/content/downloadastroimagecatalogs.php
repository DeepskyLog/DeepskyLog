<?php
global $objObject,$baseURL;

$exampleText = ImageCatalogExample;
echo "<a href=\"" . $baseURL . "images/AbellExample.jpg\" data-lightbox=\"image-1\" data-title=\"\">";
echo "<img class=\"floatright\" width=\"30%\" src=\"" . $baseURL . "images/AbellExample.jpg\"  alt=\"" . $exampleText . "\"/>";
echo "</a>";

echo "<h2>" . LangSearchMenuItem14 . "</h2>";
echo ImageCatalogDescription1;
echo "<br />" . ImageCatalogDescription2 . "<br />";
echo ImageCatalogDescription3;

echo "<br />";
echo "<table>
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
echo "<table>
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