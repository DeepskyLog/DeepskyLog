<?php
/** 
 * Loads all libraries for further use in includeFile
 * 
 * PHP Version 7
 * 
 * @category Utilities/Common
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <developers@deepskylog.be>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     https://www.deepskylog.org
 */
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} else {
    preludesA();

    global $loginErrorText, $loginErrorCode;
    if ($loginErrorCode || $loginErrorText) {
        $entryMessage = $loginErrorCode . " " . $loginErrorText;
    }
    preludesB();
}

/**
 * First preludes.
 *
 * @return None
 */
function preludesA() 
{
    global $language, $objDatabase, $objLanguage, $objObserver, $objSession;
    global $objMessages, $objUtil;

    // Start the session
    if (! session_id()) {
        session_start();
    }

    include_once "lib/setup/databaseInfo.php";
    include_once "lib/database.php";
    $objDatabase = new Database();
    include_once "lib/util.php";
    $objUtil = new Utils();
    include_once "lib/setup/language.php";
    $objLanguage = new Language();
    include_once "lib/observers.php";
    $objObserver = new Observers();
    include_once "lib/messages.php";
    $objMessages = new Messages();
    include_once "lib/sessions.php";
    $objSession = new Sessions();
    include_once "common/control/loginuser.php";
}

/**
 * Second part of the preludes.
 *
 * @return None
 */
function preludesB()
{
    global $FF, $MSIE, $leftmenu, $topmenu, $thisYear, $thisMonth, $thisDay;
    global $DSOcatalogsLists, $DSOcatalogs, $objAstroCalc, $objAtlas, $objCatalog;
    global $objCometObject, $objCometObservation, $objConstellation, $objContrast;
    global $objDatabase, $objEyepiece, $objFilter, $objFormLayout, $objInstrument;
    global $objLanguage, $objLens, $objList, $objLocation, $objObject;
    global $objObjectOutlines, $objObservation, $objObserverQueries, $objObserver;
    global $objPresentations, $objPrintAtlas, $objReportLayout, $objStar;
    global $objAccomplishments, $objUtil, $language, $locale;

    // This is needed to use the po files for the translations
    if ($language == "nl") {
        $locale = "nl_NL";
    } else if ($language == "fr") {
        $locale = "fr_FR";
    } else if ($language == "de") {
        $locale = "de_DE";
    } else if ($language == "sv") {
        $locale = "sv_SE";
    } else if ($language == "es") {
        $locale = "es_ES";
    } else {
        $locale = "en_US";
    }

    if (defined('LC_MESSAGES')) {
        setlocale(LC_MESSAGES, $locale); // Linux
        bindtextdomain("messages", "./locale");
        textdomain("messages");
        bind_textdomain_codeset("messages", 'UTF-8');
    } else {
        putenv("LC_ALL={$locale}"); // windows
        bindtextdomain("messages", ".\locale");
    }
    
    textdomain("messages");
    
    // We can only include vars.php here, because some of the vars
    // are internationalized.
    include_once "lib/setup/vars.php";
    include_once "lib/observerqueries.php";
    $objObserverQueries = new Observerqueries();
    include_once "lib/atlasses.php";
    $objAtlas = new Atlasses();
    include_once "lib/locations.php";
    $objLocation = new Locations();
    include_once "lib/instruments.php";
    $objInstrument = new Instruments();
    include_once "lib/filters.php";
    $objFilter = new Filters();
    include_once "lib/lenses.php";
    $objLens = new Lenses();
    include_once "lib/contrast.php";
    $objContrast = new Contrast();
    include_once "lib/eyepieces.php";
    $objEyepiece = new Eyepieces();
    include_once "lib/observations.php";
    $objObservation = new Observations();
    include_once "lib/accomplishments.php";
    $objAccomplishments = new Accomplishments();
    include_once "lib/lists.php";
    $objList = new Lists();
    include_once "lib/objects.php";
    $objObject = new Objects();
    include_once "lib/objectOutlines.php";
    $objObjectOutlines = new ObjectOutlines();
    include_once "lib/astrocalc.php";
    $objAstroCalc = new AstroCalc();
    include_once "lib/stars.php";
    $objStar = new Stars();
    include_once "lib/cometobservations.php";
    $objCometObservation = new cometObservations();
    include_once "lib/cometobjects.php";
    $objCometObject = new CometObjects();
    include_once 'lib/presentation.php';
    $objPresentations = new Presentations();
    include_once 'lib/constellations.php';
    $objConstellation = new Constellation();
    include_once 'lib/formlayouts.php';
    $objFormLayout = new formLayouts();
    include_once 'lib/reportlayouts.php';
    $objReportLayout = new reportLayouts();
    include_once 'lib/catalogs.php';
    $objCatalog = new catalogs();
    include_once "lib/moonphase.inc.php";
    include_once "lib/printatlas.php";
    $objPrintAtlas = new PrintAtlas();
    include_once "lib/Cpdf.php";
    include_once "lib/Cezpdf.php";
    include_once "lib/icqmethod.php";
    include_once "lib/icqreferencekey.php";

    $browser = $objUtil->checkArrayKey($_SERVER, 'HTTP_USER_AGENT', '');
    if (strpos($browser, 'Firefox') === false) {
        $FF = false;
    } else {
        $FF = true;
    }
    if (strpos($browser, 'MSIE') === false) {
        $MSIE = false;
    } else {
        $MSIE = true;
    }

    if (array_key_exists('globalMonth', $_SESSION) && $_SESSION['globalMonth']) {
    } else {
        $_SESSION['globalYear'] = $thisYear;
        $_SESSION['globalMonth'] = $thisMonth;
        $_SESSION['globalDay'] = $thisDay;
    }
    if (array_key_exists('changeMonth', $_GET) && $_GET['changeMonth']) {
        $_SESSION['globalMonth'] = $_GET['changeMonth'];
        if (($_SESSION['globalDay'] > 28) && ($_SESSION['globalMonth'] == 2)) {
            $_SESSION['globalDay'] = 28;
        }
        if (($_SESSION['globalDay'] == 31) && (($_SESSION['globalMonth'] == 4)
            || ($_SESSION['globalMonth'] == 6) || ($_SESSION['globalMonth'] == 9) 
            || ($_SESSION['globalMonth'] == 11))
        ) {
            $_SESSION['globalDay'] = 30;
        }
        if (array_key_exists('Qobj', $_SESSION)) {
            $_SESSION['Qobj'] = $objObject->getObjectRisSetTrans($_SESSION['Qobj']);
        }
    }
    if (array_key_exists('changeYear', $_GET) && $_GET['changeYear']) {
        $_SESSION['globalYear'] = $_GET['changeYear'];
        if (array_key_exists('Qobj', $_SESSION)) {
            $_SESSION['Qobj'] = $objObject->getObjectRisSetTrans($_SESSION['Qobj']);
        }
    }
    if (array_key_exists('changeDay', $_GET) && $_GET['changeDay']) {
        $_SESSION['globalDay'] = $_GET['changeDay'];
        if (array_key_exists('Qobj', $_SESSION)) {
            $_SESSION['Qobj'] = $objObject->getObjectRisSetTrans($_SESSION['Qobj']);
        }
    }
    if (array_key_exists('leftmenu', $_GET)) {
        $leftmenu = $_GET['leftmenu'];
    } elseif (array_key_exists('leftmenu', $_COOKIE)) {
        $leftmenu = $_COOKIE['leftmenu'];
    }
    if (array_key_exists('topmenu', $_GET)) {
        $topmenu = $_GET['topmenu'];
    } elseif (array_key_exists('topmenu', $_COOKIE)) {
        $topmenu = $_COOKIE['topmenu'];
    }
    $DSOcatalogsLists = $objObject->getCatalogsAndLists();
    $DSOcatalogs = $objObject->getCatalogs();
}

function Nz($arg) 
{
    if ($arg) {
        return $arg;
    } else {
        return "";
    }
}

function Nz0($arg) 
{
    if ($arg) {
        return $arg;
    } else {
        return 0;
    }
}

function Nzx($arg, $default = "") 
{
    if ($arg) {
        return $arg;
    } else {
        return $default;
    }
}

// definition of the php fnmatch function for Windows environments
if (! function_exists('fnmatch')) { 
    function fnmatch($pattern, $string) 
    {
        return @preg_match(
            '/^' . strtr(
                addcslashes($pattern, '\\.+^$(){}=!<>|'), array(
                    '*' => '.*',
                    '?' => '.?'
                )
            ) . '$/i', $string
        );
    }
}
?>
