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
    global $baseURL, $FF, $loggedUser, $object, $myList, $step, $objObject, $objObservation, $objSession, $objPresentations, $objUtil;
    echo '<script type="text/javascript" src="'.$baseURL.'lib/javascript/presentation.js"></script>';
    $link2 = $baseURL.'index.php?indexAction=result_selected_observations&amp;lco='.urlencode($_SESSION['lco']);
    reset($_GET);
    if (array_key_exists('sessionid', $_GET)) {
        $sessionid = $_GET['sessionid'];
        $_SESSION['Qobs'] = $objSession->getObservations($sessionid);
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
        $content1 .= '<a class="btn btn-primary" href="'.$baseURL.'observations.xml.php" rel="external"><span class="glyphicon glyphicon-download"></span> '._('<OAL>').'</span></a> ';
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
