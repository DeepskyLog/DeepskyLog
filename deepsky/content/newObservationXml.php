<?php
/**
 * GUI to add new observations from xml file to the database
 * 
 * @category Deepsky/import
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <developers@deepskylog.be>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     http://www.deepskylog.org
 */
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} else {
    newObservationXml();
}

/**
 * Show the page where a openAstronomyLog XML file can be imported.
 * 
 * @return Nothing
 */
function newObservationXml()
{
    global $baseURL, $objPresentations;
    echo "<div id=\"main\">";
    echo "<h4>" . _("Import observations from an XML file") . "</h4>"; 
    echo "<hr />";
    print _("This form gives you the possibility to add different observations at the same time using an OpenAstronomyLog XML file.") . "<br />";
    print _("This way, you can exchange in a fast and easy way observations between different applications which support the OpenAstronomyLog XML format (like <a href=\"http://observation.sourceforge.net\">Observation Manager</a>, <a href=\"http://www.eyeandtelescope.com/\">Eye&Telescope</a>, <a href=\"http://knightware.biz/dsp/\">Deep-Sky Planner</a>, <a href=\"https://skysafariastronomy.com/\">SkySafari 6+</a>, ...)") . "<br />";
    print _("Information: Only observations with your name (first name and last name) will be added. Observations which are already available in DeepskyLog will not be imported a second time.") . "<br />";
    echo "<hr />";
    echo "<form action=\"" . $baseURL . "index.php?indexAction=addXmlObservations\" enctype=\"multipart/form-data\" method=\"post\"><div>";
    echo "<input type=\"file\" name=\"xml\" /><br />";
    echo "<input class=\"btn btn-success\" type=\"submit\" name=\"change\" value=\"" . _("Import!") . "\" />";
    echo "</div></form>";
    echo "<hr />";
    echo "</div>";
}
?>
