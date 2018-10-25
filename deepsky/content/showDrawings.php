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
    global $objObservation, $baseURL, $objInstrument, $objLocation, $locale;
    global $objObserver;

    $observations = $objObservation->getUserDrawings($_GET['user']);
    $numberOfDrawings = count($observations);

    print '<h1>' 
        . sprintf(
            _("Drawings of %s"), 
            $objObserver->getFullName($_GET['user'])
        ) . '</h1>';

    print '<div class="row">';
    
    if ($numberOfDrawings == 0) {
        print '<h2>' . _("No drawings found") . '</h2>';
    }
    $cnt = 0;
    for ($i = $numberOfDrawings - 1;$i >= 0;$i--) {
        $cnt++;
        print '<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">';
        print ' <div class="thumbnail">';

        $datetime = new DateTime($observations[$i]['date']);
        $dateFormatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE
        );

        // TODO: Add link to the observation or to the drawing?
        // TODO: Add translations
        print '<a href="#">
                    <img class="lazyload" data-src="' . $baseURL . 'deepsky/drawings/' 
            . $observations[$i]['id'] 
            . '.jpg"' . ' alt="' . $i . '">
               </a>
               <div class="caption">
                <h4>' . $observations[$i]['objectname'] . ' - ' 
            . $dateFormatter->format($datetime) . '</h4>' 
            . $objInstrument->getInstrumentPropertyFromId($observations[$i]['instrumentid'], 'name') 
            . ', ' . $objLocation->getLocationPropertyFromId($observations[$i]['locationid'], 'name') . '
               </div>
              </div>
            </div>';

        if ($cnt == 4) {
            $cnt = 0;
            print '</div><div class="row">';
        }
    }
    print '   </div>';

    echo '<script type="text/javascript">
            lazyload();
          </script>';
}
?>