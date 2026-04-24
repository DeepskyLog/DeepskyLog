<?php

// selected_observations.php
// generates an overview of selected observations in the database
if ((!isset($inIndex)) || (!$inIndex)) {
    include '../../redirect.php';
} else {
    selected_observations();
}
function selected_observations()
{
    global $baseURL, $FF, $loggedUser, $object, $myList, $step, $min, $objObject, $objObservation, $objSession, $objPresentations, $objUtil;
    echo '<script type="text/javascript" src="'.$baseURL.'lib/javascript/presentation.js"></script>';
    $link2 = $baseURL.'index.php?indexAction=result_selected_observations&amp;lco='.urlencode($_SESSION['lco']);
    reset($_GET);
    if (array_key_exists('sessionid', $_GET)) {
        $sessionid = $_GET['sessionid'];
        $_SESSION['Qobs'] = $objSession->getObservations($sessionid);
    }
    // If an observer is requested directly via GET, fetch observations
    // for that observer. If an object/catalog+number is provided as well,
    // include those filters so both observer and object selection are applied.
    if (array_key_exists('observer', $_GET) && ($_GET['observer'] != '')) {
        $sortField = $objUtil->checkGetKey('sort', (array_key_exists('QobsSort', $_SESSION) ? $_SESSION['QobsSort'] : 'observationid'));
        $sortDirection = strtolower($objUtil->checkGetKey('sortdirection', (array_key_exists('QobsSortDirection', $_SESSION) ? $_SESSION['QobsSortDirection'] : 'desc')));
        if (($sortDirection != 'asc') && ($sortDirection != 'desc')) {
            $sortDirection = 'desc';
        }
        $pageSize = (isset($step) && ((int)$step > 0)) ? (int)$step : 25;
        $offset = (isset($min) && ((int)$min > 0)) ? (int)$min : 0;
        $queries = array(
            'observer' => $objUtil->checkGetKey('observer'),
            'lightweight' => 1,
            'sqlSorted' => 1,
            'sort' => $sortField,
            'sortdirection' => $sortDirection,
            'offset' => $offset,
            'limit' => $pageSize,
            'hasDrawing' => $objUtil->checkGetKey('drawings', 'off'),
            'hasNoDrawing' => $objUtil->checkGetKey('nodrawings', 'off')
        );
        // Clear any cached query results to ensure filters like drawings are applied
        if (isset($_SESSION['Qobs'])) {
            unset($_SESSION['Qobs']);
        }
        if (isset($_SESSION['QobsParams'])) {
            unset($_SESSION['QobsParams']);
        }
        if (isset($_SESSION['QobsTotal'])) {
            unset($_SESSION['QobsTotal']);
        }
        if (array_key_exists('object', $_GET) && ($_GET['object'] != '')) {
            $queries['object'] = $objUtil->checkGetKey('object');
        } else {
            $cat = $objUtil->checkGetKey('catalog');
            $num = $objUtil->checkGetKey('number');
            if ($cat || $num) {
                $queries['catalog'] = $cat;
                $queries['number'] = $num;
            }
        }
        $_SESSION['Qobs'] = $objObservation->getObservationFromQuery(
            $queries,
            $objUtil->checkGetKey('seen', 'A'),
            (bool)$objUtil->checkGetKey('exactinstrumentlocation', 0)
        );
        $_SESSION['QobsSort'] = $sortField;
        $_SESSION['QobsSortDirection'] = $sortDirection;
        $countQueries = $queries;
        $countQueries['countquery'] = 'true';
        $_SESSION['QobsTotal'] = $objObservation->getObservationFromQuery(
            $countQueries,
            $objUtil->checkGetKey('seen', 'A'),
            (bool)$objUtil->checkGetKey('exactinstrumentlocation', 0)
        );
        
    }
    // If a full query was submitted (from object links), run the query builder
    // to populate $_SESSION['Qobs'] (this honors the drawings filter).
    if (array_key_exists('query', $_GET) && ($_GET['query'] != '')) {
        include_once __DIR__ . '/../data/data_get_observations.php';
    }
    foreach ($_GET as $key => $value) {
        if (!in_array($key, [
                'indexAction',
                'lco',
                'sessionid',
                'myLanguages',
                'collapsed',
        ])) {
            $link2 .= '&amp;'.$key.'='.urlencode($value);
        }
    }
    $link = $link2;

    // ====================== the remainder of the pages formats the page output and calls showObject (if necessary) and showObservations
    // =============================================== IF IT CONCERNS THE OBSERVATIONS OF 1 SPECIFIC OBJECT, SHOW THE OBJECT BEFORE SHOWING ITS OBSERVATIONS =====================================================================================
    if ($object && $objObject->getExactDsObject($object)) {
        $object_ss = stripslashes($object);
        $seen = $objObject->getDSOseenLink($object);
        $collapsedtext = '<a href="'.$link2.'&amp;collapsed=collapsed" title="'._('Hide object details').'">-</a>&nbsp;';
        $collapsed = false;
        if ($objUtil->checkRequestKey('collapsed') == 'collapsed') {
            $collapsedtext = '<a href="'.$link2.'" title="'._('Show object details').'">+</a>&nbsp;';
            $collapsed = true;
            $link .= '&amp;collapsed=collapsed';
        }
        if (!($collapsed)) {
            echo '<h4>'.$collapsedtext._('Object details').'&nbsp;-&nbsp;'.$object_ss.'&nbsp;-&nbsp;'._('Seen').'&nbsp;:&nbsp;'.$seen.'</h4>';
            echo $objPresentations->getDSSDeepskyLiveLinks1($object);
            $topline = '&nbsp;-&nbsp;'.'<a href="'.$baseURL.'index.php?indexAction=detail_object&amp;object='.urlencode($object).'">'._('Nearby objects').'</a>';
        }
        if (!($collapsed)) {
            if ($myList) {
                if ($objList->checkObjectInMyActiveList($object)) {
                    $topline .= '&nbsp;-&nbsp;'.'<a href="'.$baseURL.'index.php?indexAction=result_selected_observations&amp;object='.urlencode($object).'&amp;removeObjectFromList='.urlencode($object).'">'.sprintf(_('%s to remove from the list %s'), $object_ss, $listname_ss).'</a>';
                } else {
                    $topline .= '&nbsp;-&nbsp;'.'<a href="'.$baseURL.'index.php?indexAction=result_selected_observations&amp;object='.urlencode($object).'&amp;addObjectToList='.urlencode($object).'&amp;showname='.urlencode($object).'">'.sprintf(_('%s to add to the list %s'), $object_ss, $listname_ss).'</a>';
                }
            }
            $topline .= '&nbsp;-&nbsp;'.'<a href="'.$baseURL.'index.php?indexAction=atlaspage&amp;object='.urlencode($object).'">'._('Interactive Atlas').'</a>';
            echo substr($topline, 13);
            echo $objPresentations->getDSSDeepskyLiveLinks2($object);
            echo '<hr />';
            $objObject->showObject($object);
        }
    }
    if ((!(array_key_exists('Qobs', $_SESSION))) || count($_SESSION['Qobs']) == 0) { 	// ================================================================================================== no reult present =======================================================================================
        echo _('Sorry, no observations found!').(($objUtil->checkGetKey('myLanguages')) ? (' ('._('selected languages').')') : (' ('._('all languages').')')).'</h4>';
        if ($objUtil->checkGetKey('myLanguages')) {
            echo '<p>'.'<a href="'.$link2.'">'._('Look again, using all languages.').'</a>&nbsp;</p>';
        }
        echo '<p>'.'<a href="'.$baseURL.'index.php?indexAction=query_observations">'._('Set up a detailed search.').'</a>'.'</p>';
    } else { // =============================================================================================== START OBSERVATION PAGE OUTPUT =====================================================================================
        // If an observer GET parameter was provided, filter the fetched
        // observations to only include that observer (by id or name).
        if (array_key_exists('observer', $_GET) && ($_GET['observer'] != '') && array_key_exists('Qobs', $_SESSION) && count($_SESSION['Qobs']) > 0) {
            $requestedObserver = $objUtil->checkGetKey('observer');
            $filtered = array();
            foreach ($_SESSION['Qobs'] as $qo) {
                if ((isset($qo['observerid']) && ($qo['observerid'] == $requestedObserver))
                    || (isset($qo['observername']) && (stripos($qo['observername'], $requestedObserver) !== false))) {
                    $filtered[] = $qo;
                }
            }
            $_SESSION['Qobs'] = $filtered;
        }
        echo '<div id="main">';
        $theDate = date('Ymd', strtotime('-1 year'));
        $content1 = '<h4>';
        if (array_key_exists('minyear', $_GET) && ($_GET['minyear'] == substr($theDate, 0, 4)) && array_key_exists('minmonth', $_GET) && ($_GET['minmonth'] == substr($theDate, 4, 2)) && array_key_exists('minday', $_GET) && ($_GET['minday'] == substr($theDate, 6, 2))) {
            $content1 .= _("Overview of last year's observations");
        } elseif ($object) {
            $content1 .= sprintf(_('Overview of all observations of %s'), $object);
        } else {
            $content1 .= _('Overview selected observations');
        }
        $content1 .= '</h4>';
        $link3 = $link;
        $content3 = '<h4>';
        if ($objUtil->checkGetKey('myLanguages')) {
            $content3 .= ' ('._('selected languages only').')';
            $link .= '&amp;myLanguages=true';
            $link2 .= '&amp;myLanguages=true';
        } else {
            $content3 .= ' ('._('all languages').')';
        }
        $content3 .= '</h4>';
        echo $content1;
        $content5 = '<span class="pull-right">';
        if (($objUtil->checkSessionKey('lco', '') != 'L')) {
            $content5 .= '&nbsp;&nbsp;<a class="btn btn-success" href="'.$link.'&amp;lco=L" title="'.
                _('Overview with the basic information on one line per observation').'">'._('List').'</a>';
        }
        if (($objUtil->checkSessionKey('lco', '') != 'C')) {
            $content5 .= '&nbsp;&nbsp;<a class="btn btn-success" href="'.$link.'&amp;lco=C" title="'.
                _('Overview with an information line and a description per observation').'">'._('Compact').'</a>';
        }
        if ($loggedUser && ($objUtil->checkSessionKey('lco', '') != 'O')) {
            $content5 .= '&nbsp;&nbsp;<a class="btn btn-success" href="'.$link.'&amp;lco=O" title="'.
                _('Overview with an information line, a description and your last observation').'">'._('Compare').'</a>';
        }
        if ($loggedUser && $objUtil->checkSessionKey('lco', '') == 'L') {
            $toAdd = '&nbsp;&nbsp;'.'<a class="btn btn-success" href="'.$link.'&amp;noOwnColor=no">'
                ._('Highlight own observations').'</a>';
            if ($objUtil->checkGetKey('noOwnColor')) {
                if ($objUtil->checkGetKey('noOwnColor') == 'no') {
                    $toAdd = '&nbsp;&nbsp;'.'<a class="btn btn-success" href="'.$link.'&amp;noOwnColor=yes">'
                        ._("Don't highlight own observations").'</a>';
                }
            }
            $content5 .= $toAdd;
        }
        $content5 .= '</span>';

        if ($objUtil->checkGetKey('myLanguages')) {
            $content6 = '<a class="btn btn-success" href="'.$link3.'">'._('Show all languages').'</a>';
        } elseif ($loggedUser) {
            $content6 = '<a class="btn btn-success" href="'.$link3.'&amp;myLanguages=true">'._('Only show the observations from my profile languages').'</a>';
        } else {
            $content6 = '<a class="btn btn-success" href="'.$link3.'&amp;myLanguages=true">'._('Only show observations in English').'</a>';
        }
        echo $content5;
        echo $content6;

        $totalObs = (int)(array_key_exists('QobsTotal', $_SESSION) ? $_SESSION['QobsTotal'] : count($_SESSION['Qobs']));
        $pageSize = (isset($step) && ((int)$step > 0)) ? (int)$step : 25;
        $currentPage = (int)floor(((isset($min) ? (int)$min : 0) / $pageSize)) + 1;
        $totalPages = max(1, (int)ceil($totalObs / $pageSize));
        $basePageLink = preg_replace('/&amp;multiplepagenr=[0-9]+/', '', $link2);
        $basePageLink = preg_replace('/&amp;min=[0-9]+/', '', $basePageLink);
        if ($currentPage > 1) {
            echo '&nbsp;<a class="btn btn-default" href="' . $basePageLink . '&amp;multiplepagenr=' . ($currentPage - 1) . '">' . _('Previous') . '</a>';
        }
        echo '&nbsp;<span class="btn btn-default disabled">' . sprintf(_('Page %d of %d (%d observations)'), $currentPage, $totalPages, $totalObs) . '</span>';
        if ($currentPage < $totalPages) {
            echo '&nbsp;<a class="btn btn-default" href="' . $basePageLink . '&amp;multiplepagenr=' . ($currentPage + 1) . '">' . _('Next') . '</a>';
        }
        echo '<hr />';

        $objObservation->showListObservation($link, $_SESSION['lco']);
        echo '<hr />';
        if ($_SESSION['lco'] == 'O') {
            echo _('(*) All Observations(AO), My observations(MO), my Last observations(LO) of this object');
            echo '<br /><br />';
        }
        $content1 = '<a class="btn btn-primary" href="'.$baseURL.'index.php?indexAction=query_objects&amp;source=observation_query">'._('Filter objects').'</a> ';
        $content1 .= $objPresentations->promptWithLinkText(
            _('Please enter a title'), _('DeepskyLog observations'),
            $baseURL.'observations.pdf.php?SID=Qobs', _('pdf')
        );
        $content1 .= '  ';
        $content1 .= '<a class="btn btn-primary" href="'.$baseURL.'observations.csv.php" rel="external"><span class="glyphicon glyphicon-download"></span> '._('CSV').'</span></a> ';
        $content1 .= '<a class="btn btn-primary" href="'.$baseURL.'observations.xml.php" rel="external"><span class="glyphicon glyphicon-download"></span> '.'&lt;OAL&gt;'.'</span></a> ';
        $content1 .= '<a class="btn btn-primary" href="'.$baseURL.'observations.skylist.php" rel="external"><span class="glyphicon glyphicon-download"></span> '._('skylist').'</span></a>';
        echo $content1;
        echo '<hr />';
        echo '</div>';

        if (($object && $objObject->getExactDsObject($object)) && ($collapsed)) {
            echo '<h4>'.$collapsedtext._('Object details').'&nbsp;-&nbsp;'.$object_ss.'&nbsp;-&nbsp;'._('Seen').'&nbsp;:&nbsp;'.$seen.'</h4>';
            echo $objPresentations->getDSSDeepskyLiveLinks1($object);
            $topline = '&nbsp;-&nbsp;'.'<a href="'.$baseURL.'index.php?indexAction=detail_object&amp;object='.urlencode($object).'">'._('Nearby objects').'</a>';
        }
    }
}
