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
In close collaboration with the Astronomical Society of Belgium (<a href=\"http://www.vvs.be\">Vereniging Voor Sterrenkunde (VVS)</a>) we are glad to offer a comprehensive and free database for deepsky objects. The database is developed by the deepsky-section of the VVS. The database already contains tens of thousands observations and thousands of sketches and drawings made by amateur astronomers around the world. After you have registered for DeepskyLog, you get access to a variety of useful tools:<br /><br />
<ul><li>Information on the observations you made, the objects observed and sketches made,</li>
    <li>observing lists with different deepsky objects,</li>
    <li>you can share your observations with other observers,</li>
    <li>translate function to read observations in other languages,</li>
    <li>free atlases,</li>
    <li>create your own file with maps and DSS images of the objects,</li>
    <li>interactive star atlas down to magnitude 16,</li>
    <li>suggestions for objects visible in your instruments,</li>
    <li>information about the objects that are visible from your observation sites.</li>
</ul>
To start recording your observations, you need an account, which you can get after registration. Please contact the <a href=\"mailto:developers@deepskylog.be\">DeepskyLog developers</a> if you encounter problems or have questions.");
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
echo "    <img title=\"" . LangSearchMenuItem3 . "\" src=\"" . $baseURL 
    . "images/findObservation.png\">";
echo "    <div class=\"caption\">";
echo "     <h4 class=\"text-center\">" . LangSearchMenuItem3 . "</h4>";
echo "    </div>";
echo "   </a>";
echo " </div>";
// Add icon for 'Search objects'
echo " <div class=\"col-xs-4 col-sm-2 col-md-2\">";
echo "   <a class=\"thumbnail thumbnail-no-border\" href=\"" . $baseURL 
    . "index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick" 
    . "&myLanguages=true&object=&searchObjectQuickPickQuickPick=Zoek%C2%A0object\">";
echo "    <img title=\"" . LangSearchMenuItem5 . "\" src=\"" . $baseURL 
    . "images/findObject.png\">";
echo "    <div class=\"caption\">";
echo "     <h4 class=\"text-center\">" . LangSearchMenuItem5 . "</h4>";
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
echo "    <img title=\"" . LangSearchMenuItem9 . "\" src=\"" . $baseURL 
    . "images/new_badge.png\">";
echo "    <div class=\"caption\">";
echo "     <h4 class=\"text-center\">" . LangSearchMenuItem9 . "</h4>";
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
    echo "    <img title=\"" . LangViewObservationButton1 . "\" src=\"" . $baseURL 
        . "images/pencil.png\">";
    echo "    <div class=\"caption\">";
    echo "     <h4 class=\"text-center\">" . LangViewObservationButton1 . "</h4>";
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
