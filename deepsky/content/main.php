<?php
global $baseURL;

echo "<div class=\"jumbotron\" style=\"background-image: url(" . $baseURL . "images/logo.png); background-size: 100%;\">";
echo "<div class=\"container\">";
echo "<h1>DeepskyLog ";
echo "<small>Online deepsky planning and logging tool</small></h1>";
echo "<br />";
echo "<br />";
echo "<br />";
echo "<br />";
echo "<br />";
echo "<br />";
echo "<br />";
echo "<br />";
echo "</div></div>";

// Show the icons with to search, add a new observation, download.
echo "<div class=\"row\">";
echo " <div class=\"col-sm-3 col-md-3\">";
echo "  <div class=\"thumbnail\">";
echo "   <a href=\"" . $baseURL . "index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick&myLanguages=true&object=&searchObservationsQuickPick=Zoek waarnemingen\">";
echo "    <img src=\"" . $baseURL . "images/findObservation.png\">";
echo "    <div class=\"caption\">";
echo "     <h3>" . LangSearchMenuItem3 . "</h3>";
echo "    </div>";
echo "   </a>";
echo "  </div>";
echo " </div>";
echo " <div class=\"col-sm-3 col-md-3\">";
echo "  <div class=\"thumbnail\">";
echo "   <a href=\"" . $baseURL . "index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick&myLanguages=true&object=&searchObjectQuickPickQuickPick=Zoek%C2%A0object\">";
echo "    <img src=\"" . $baseURL . "images/findObject.png\">";
echo "    <div class=\"caption\">";
echo "     <h3>" . LangSearchMenuItem5 . "</h3>";
echo "    </div>";
echo "   </a>";
echo "  </div>";
echo " </div>";
echo " <div class=\"col-sm-3 col-md-3\">";
echo "  <div class=\"thumbnail\">";
echo "   <a href=\"" . $baseURL . "index.php?indexAction=view_atlaspages\">";
echo "    <img src=\"" . $baseURL . "images/downloadAtlas.png\">";
echo "    <div class=\"caption\">";
echo "     <h3>" . LangDownloadAtlasses . "</h3>";
echo "    </div>";
echo "   </a>";
echo "  </div>";
echo " </div>";
echo " <div class=\"col-sm-3 col-md-3\">";
echo "  <div class=\"thumbnail\">";
echo "   <a href=\"" . $baseURL . "index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick&myLanguages=true&object=&newObservationQuickPick=Nieuwe%C2%A0waarneming\">";
echo "    <img src=\"" . $baseURL . "images/pencil.png\">";
echo "    <div class=\"caption\">";
echo "     <h3>" . LangChangeMenuItem2 . "</h3>";
echo "    </div>";
echo "   </a>";
echo "  </div>";
echo " </div>";
echo "</div>";

?>