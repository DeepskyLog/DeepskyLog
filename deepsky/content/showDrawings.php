<?php

/** 
 * Shows a page with all drawings of the observer.
 * 
 * PHP Version 7
 * 
 * @category Drawings
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <developers@deepskylog.be>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     http://www.deepskylog.org
 */
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} else {
    showDrawings();
}

/** 
 * Shows a page with all drawings of the observer.
 * 
 * @return None
 */
function showDrawings()
{
    global $objObservation;

    $observations = $objObservation->getUserDrawings($_GET['user']);
    $numberOfDrawings = count($observations);
    print "Number of drawings of " . $_GET['user'] . " : " . $numberOfDrawings;
    print "<br /><br />Show slideshow";
    print "<br />Options 1: Also show info: Telescope, eyepiece, date, location";
    print "<br />Options 2: Only show drawings that are selected " 
        . "(make it possible to select all)";

    print '<div class="row">
            <div class="col-xs-6 col-md-3">';
    
    for ($i = $numberOfDrawings - 1;$i >= 0;$i--) {
        print '<a href="#" class="thumbnail">
                    <img src="' . $i . '" alt="' . $i . ' ' . $observations[$i]['id'] . '">
               </a>';
    }
    print '   </div>
            </div>';
    //print_r($observations);
    
}
?>