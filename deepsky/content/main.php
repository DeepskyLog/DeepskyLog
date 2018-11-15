<?php
/** 
 * The main deepsky page.
 * 
 * PHP Version 7
 * 
 * @category Deepsky
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <developers@deepskylog.be>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     http://www.deepskylog.org
 */

global $baseURL, $objDatabase, $objObservations, $objObserver, $objUtil;

echo "<div class=\"container-fluid\">";
if ($loggedUser) {
    // Check if the version in the database is the same as the 
    // version of DeepskyLog. If not, we show the logo.
    if ($objObserver->getLastVersion($loggedUser) != VERSIONINFO) {
        echo "<a title=\"" . _("What's new in DeepskyLog since version ") 
            . $objObserver->getLastVersion($loggedUser) 
            . " \" href=\"https://github.com/DeepskyLog/DeepskyLog/" 
            . "wiki/What's-New-in-DeepskyLog\">";
        echo "<img class=\"img-responsive img-rounded\" src=\"" 
            . $baseURL . "images/logo.png\">
                </a>";
        $objDatabase->execSQL(
            "UPDATE observers SET version=\"" . VERSIONINFO 
            . "\" WHERE id=\"" . $loggedUser . "\""
        );
    }
} else {
    echo "<a title=\"" . _("What's new in DeepskyLog") 
        . " \" href=\"https://github.com/DeepskyLog/DeepskyLog/" 
        . "wiki/What's-New-in-DeepskyLog\">";
    echo "<img class=\"img-responsive img-rounded\" src=\"" 
        . $baseURL . "images/logo.png\">
        </a>";
}
echo "</div>";
echo "<br />";

if (!$loggedUser) {
    echo _("<h2>Welcome to DeepskyLog!</h2>
DeepskyLog is an extended, comprehensive and free database for deepsky objects and has been developed by the Deepsky section of the Astronomical Society of Belgium (<a href=\"http://www.vvs.be\">Vereniging Voor Sterrenkunde (VVS)</a>).
The database is open for consultation and already contains tens of thousands observations and thousands of sketches and drawings made by amateur astronomers around the world.") 
        . "<br /><br />" 
        . sprintf(
            _("To start recording your observations and share your observations with other observers, you are kindly requested to %sregister%s to DeepskyLog."),
            "<a href='" . $baseURL . "/index.php?indexAction=subscribe&title=Register'>", "</a>"
        ) 
        . sprintf(
            _("Your personal data will be handled in accordance with our %sprivacy policy%s. "), 
            "<a href='" . $baseURL . "/index.php?indexAction=privacy'>", "</a>"
        )
        . _("This registration allows access to a variety of useful tools, including information on the objects observed and sketches made. You can consult and create observing lists with different deepsky objects and see suggestions for objects visible in your instruments and from your observation sites. 
You can create your own file with maps and DSS images of the objects and have access to an interactive and detailed free star atlas."
        ) . "<br /><br />"
        . sprintf(
            _("Please contact the %sDeepskyLog developers%s if you encounter problems or have questions."),
            '<a href="mailto:developers@deepskylog.be">', '</a>'
        );
    echo "<br /><br />";
}

// Show the icons.
// Add icon for 'Search observations'
echo "<div class=\"row\">";
echo " <div class=\"col-xs-4 col-sm-2 col-md-2\">";
echo "   <a class=\"thumbnail thumbnail-no-border\" href=\"" . $baseURL 
    . "index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick" 
    . "&myLanguages=true&object=" 
    . "&searchObservationsQuickPick=Zoek%C2%A0waarnemingen\">";
echo "    <img title=\"" . _("Search observations") . "\" src=\"" . $baseURL 
    . "images/findObservation.png\">";
echo "    <div class=\"caption\">";
echo "     <h4 class=\"text-center\">" . _("Search observations") . "</h4>";
echo "    </div>";
echo "   </a>";
echo " </div>";
// Add icon for 'Search objects'
echo " <div class=\"col-xs-4 col-sm-2 col-md-2\">";
echo "   <a class=\"thumbnail thumbnail-no-border\" href=\"" . $baseURL 
    . "index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick" 
    . "&myLanguages=true&object=&searchObjectQuickPickQuickPick=Zoek%C2%A0object\">";
echo "    <img title=\"" . _("Search objects") . "\" src=\"" . $baseURL 
    . "images/findObject.png\">";
echo "    <div class=\"caption\">";
echo "     <h4 class=\"text-center\">" . _("Search objects") . "</h4>";
echo "    </div>";
echo "   </a>";
echo " </div>";
// Add icon for 'See new observations'
echo " <div class=\"col-xs-4 col-sm-2 col-md-2\">";

$theDate = date('Ymd', strtotime('-1 year'));
$lastMinYear = substr($theDate, 0, 4);
$lastMinMonth = substr($theDate, 4, 2);
$lastMinDay = substr($theDate, 6, 2);
echo "   <a class=\"thumbnail thumbnail-no-border\" href=\"" . $baseURL 
    . "index.php?indexAction=result_selected_observations&amp;myLanguages=true" 
    . "&amp;catalog=%&amp;minyear=$lastMinYear&amp;minmonth=$lastMinMonth" 
    . "&amp;minday=$lastMinDay&amp;newobservations=true\">";
echo "    <img title=\"" . _("Latest observations") . "\" src=\"" . $baseURL 
    . "images/new_badge.png\">";
echo "    <div class=\"caption\">";
echo "     <h4 class=\"text-center\">" . _("Latest observations") . "</h4>";
echo "    </div>";
echo "   </a>";
echo " </div>";
// Add icon for 'Download atlases'
echo " <div class=\"col-xs-4 col-sm-2 col-md-2\">";
echo "   <a class=\"thumbnail thumbnail-no-border\" href=\"" . $baseURL 
    . "index.php?indexAction=view_atlaspages\">";
echo "    <img title=\"" . _("Download atlases") . "\" src=\"" . $baseURL 
    . "images/downloadAtlas.png\">";
echo "    <div class=\"caption\">";
echo "     <h4 class=\"text-center\">" . _("Download atlases") . "</h4>";
echo "    </div>";
echo "   </a>";
echo " </div>";
if ($loggedUser) {
    // Add icon for 'Add observation'
    echo " <div class=\"col-xs-4 col-sm-2 col-md-2\">";
    echo "   <a class=\"thumbnail thumbnail-no-border\" href=\"" . $baseURL 
        . "index.php?indexAction=quickpick" 
        . "&titleobjectaction=Zoeken&source=quickpick" 
        . "&myLanguages=true&object=" 
        . "&newObservationQuickPick=Nieuwe%C2%A0waarneming\">";
    echo "    <img title=\"" . _("Add observation") . "\" src=\"" . $baseURL 
        . "images/pencil.png\">";
    echo "    <div class=\"caption\">";
    echo "     <h4 class=\"text-center\">" . _("Add observation") . "</h4>";
    echo "    </div>";
    echo "   </a>";
    echo " </div>";

    // Add icon for 'Create list'
    echo " <div class=\"col-xs-4 col-sm-2 col-md-2\">";
    echo "   <a class=\"thumbnail thumbnail-no-border\" data-toggle=\"modal\"" 
        . " data-target=\"#addList\">";
    echo "    <img title=\"" . _("Create list") . "\" src=\"" . $baseURL 
        . "images/clipboard.png\">";
    echo "    <div class=\"caption\">";
    echo "     <h4 class=\"text-center\">" . _("Create list") . "</h4>";
    echo "    </div>";
    echo "   </a>";
    echo " </div>";
}
echo "</div>";

echo "<h2>" . _("New drawings") . "</h2>";
$drawings = $objObservation->getLastObservationsWithDrawing();
echo "<div class=\"row\">";
foreach ($drawings as $drawing => $key) {
    echo " <div class=\"col-xs-3 col-sm-3 col-md-3\">";
    echo "  <div class=\"thumbnail\">";
    echo "   <a href=\"" . $baseURL 
        . "index.php?indexAction=detail_observation&observation=" 
        . $key["id"] . "&amp;dalm=D\">";
    echo "    <img class=\"img-rounded\" src=\"" . $baseURL . "deepsky/drawings/" 
        . $key["id"] . ".jpg\">";
    echo "    <div class=\"caption\">";
    echo "     " . $objObserver->getFullName($key["observerid"]) . "<br />";
    echo "     " . $key["objectname"] . "<br />";
    echo "     " . $objUtil->getLocalizedDate($key["date"]);
    echo "    </div>";
    echo "   </a>";
    echo "  </div>";
    echo " </div>";
}
echo "</div>";

echo "<h2>Tweets</h2>";
echo '<script>window.twttr = (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0],
      t = window.twttr || {};
    if (d.getElementById(id)) return t;
    js = d.createElement(s);
    js.id = id;
    js.src = "https://platform.twitter.com/widgets.js";
    fjs.parentNode.insertBefore(js, fjs);
  
    t._e = [];
    t.ready = function(f) {
      t._e.push(f);
    };
  
    return t;
  }(document, "script", "twitter-wjs"));</script>';
echo '<a class="twitter-timeline"
  href="https://twitter.com/DeepskyLog"
  data-width="500"
  data-height="300"
  data-tweet-limit="3">
Tweets by @DeepskyLog
</a>';
?>
