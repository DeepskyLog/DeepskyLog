<?php
// instructions.php
// treats all commands for changing data in the database or setting program parameters
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	instructions ();
function instructions() {
	global $baseURL, $loggedUser, $myList, $lastReadObservation, $theDate, $modules, $menuView, $menuAddChange, $menuAdmin, $menuLogin, $menuSearch, $menuDownloads, $menuMoon, $listname_ss, $listname, $entryMessage, $toastMessage, $step, $objSession, $objAccomplishments, $objEyepiece, $objFilter, $objLens, $objInstrument, $objLocation, $objMessages, $objObject, $objObserver, $objObservation, $objFormLayout, $objUtil, $objList;
	if ($objUtil->checkGetKey ( 'saveLayout' )) {
		$objFormLayout->saveLayout ( $objUtil->checkGetKey ( 'formName', 'NoFormName' ), $objUtil->checkGetKey ( 'layoutName', 'layoutName' ), $objUtil->checkGetKey ( 'restoreColumns', '' ), $objUtil->checkGetKey ( 'orderColumns', '' ) );
	}
	if ($objUtil->checkGetKey ( 'removeLayout' )) {
		$objFormLayout->removeLayout ( $objUtil->checkGetKey ( 'formName', 'NoFormName' ), $objUtil->checkGetKey ( 'layoutName', 'layoutName' ) );
	}
	if (($markAsRead = $objUtil->checkGetKey ( 'markAsRead', 0 )) === "All")
		$objObserver->markAllAsRead ();
	elseif ($markAsRead)
		$objObserver->markAsRead ( $markAsRead );
	$theDate = date ( 'Ymd', strtotime ( '-1 year' ) );
	if (($objUtil->checkGetKey ( 'indexAction', 'x' ) == 'x') || (($objUtil->checkGetKey ( 'catalog', 'x' ) == '%') && ($objUtil->checkGetKey ( 'minyear', 'x' ) == substr ( $theDate, 0, 4 )) && ($objUtil->checkGetKey ( 'minmonth', 'x' ) == substr ( $theDate, 4, 2 )) && ($objUtil->checkGetKey ( 'minday', 'x' ) == substr ( $theDate, 6, 2 )) && (($objUtil->checkGetKey ( 'sort', 'x' ) == 'x') || (($objUtil->checkGetKey ( 'sort', 'x' ) == 'observationid') && ($objUtil->checkGetKey ( 'sortdirection', 'x' ) == 'desc')))))
		$lastReadObservation = ($loggedUser ? $objObserver->getLastReadObservation ( $loggedUser ) : - 1);
	else
		$lastReadObservation = - 1;

	if ($objUtil->checkGetKey ( 'indexAction' ) == "removeToken") {
		require_once $instDir . "common/control/removeToken.php";
	}
	if ($objUtil->checkGetKey ( 'indexAction' ) == "logout") // logout
		require_once $instDir . "common/control/logout.php";
		// listnames
	$myList = False;
	$listname = '';
	if (array_key_exists ( 'listname', $_SESSION ) && ($_SESSION ['listname'] != "----------"))
		$listname = $_SESSION ['listname'];
	$listname_ss = stripslashes ( $listname );
	if (array_key_exists ( 'listname', $_SESSION ) && $objList->checkList ( $_SESSION ['listname'] ) == 2)
		$myList = True;
		// LCO for viewing observation lists in list, compact or last-own compact
	if (array_key_exists ( 'lco', $_GET ) && (($_GET ['lco'] == "L") || ($_GET ['lco'] == "C") || ($_GET ['lco'] == "O"))) // lco = List, Compact or compactlO;
{
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		$_SESSION ['lco'] = $_GET ['lco'];
		setcookie ( "lco", $_SESSION ['lco'], $cookietime, "/" );
	} elseif (array_key_exists ( 'lco', $_COOKIE ) && (($_COOKIE ['lco'] == "L") || ($_COOKIE ['lco'] == "C") || ($_COOKIE ['lco'] == "O"))) // lco = List, Compact or compactlO;
		$_SESSION ['lco'] = $_COOKIE ['lco'];
	elseif ((! array_key_exists ( 'lco', $_SESSION )) || (! (($_SESSION ['lco'] == "L") || ($_SESSION ['lco'] == "C") || ($_SESSION ['lco'] == "O")))) {
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "lco", "L", $cookietime, "/" );
		$_SESSION ['lco'] = "L";
	}
	if (($_SESSION ['lco'] == "O") && (! $loggedUser))
		$_SESSION ['lco'] = "L";
		// pagenumbers ================================================================================================================================================================
	if (! array_key_exists ( 'steps', $_SESSION )) {
		if (array_key_exists ( 'steps', $_COOKIE )) {
			$stepsbase = explode ( ";", $_COOKIE ['steps'] );
			foreach ($stepsbase as $key=>$value) {
				if ($value) {
					$stepsbaseitems = explode ( ":", $value );
					$_SESSION ['steps'] [$stepsbaseitems [0]] = $stepsbaseitems [1];
				}
			}
		}
	}
	$step = 25;
	if (array_key_exists ( 'multiplepagenr', $_GET ))
		$min = ($_GET ['multiplepagenr'] - 1) * $step;
	elseif (array_key_exists ( 'multiplepagenr', $_POST ))
		$min = ($_POST ['multiplepagenr'] - 1) * $step;
	elseif (array_key_exists ( 'min', $_GET ))
		$min = $_GET ['min'];
	else
		$min = 0;
	if ($stepsType = $objUtil->checkGetKey ( 'stepsCommand' )) {
		$steps = ( int ) $objUtil->checkGetKey ( 'stepsValue', 25 );
		if ((! ($steps)) || ($steps < 1))
			$steps = 25;
		$_SESSION ['steps'] [$stepsType] = $steps;
		reset ( $_SESSION ['steps'] );
		$stepscookie = "";
		foreach ($_SESSION['steps'] as $key=>$value)
			$stepscookie .= $key . ":" . $value . ";";
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "steps", $stepscookie, $cookietime, "/" );
		reset ( $_SESSION ['steps'] );
	}
	// collapsed menus ================================================================================================================================================================
	if (array_key_exists ( 'menuDownloads', $_GET )) {
		$menuDownloads = $_GET ['menuDownloads'];
		$_SESSION ['menus'] ['menuDownloads'] = $menuDownloads;
		$menuscookie = "";
		while ( list ( $key, $value ) = each ( $_SESSION ['menus'] ) )
			$menuscookie .= $key . ":" . $value . ";";
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "menus", $menuscookie, $cookietime, "/" );
	} elseif (array_key_exists ( 'menuDownloads', $_POST )) {
		$menuDownloads = $_POST ['menuDownloads'];
		$_SESSION ['menus'] ['menuDownloads'] = $menuDownloads;
		$menuscookie = "";
		foreach ($_SESSION['menus'] as $key=>$value)
			$menuscookie .= $key . ":" . $value . ";";
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "menus", $menuscookie, $cookietime, "/" );
	} elseif (array_key_exists ( 'menus', $_SESSION ) && array_key_exists ( 'menuDownloads', $_SESSION ['menus'] ))
		$menuDownloads = $_SESSION ['menus'] ['menuDownloads'];
	elseif (array_key_exists ( 'menus', $_COOKIE )) {
		$menubase = explode ( ";", $_COOKIE ['menus'] );
		foreach ($menubase as $key=>$value) {
			if ($value) {
				$menubaseitems = explode ( ":", $value );
				$_SESSION ['menus'] [$menubaseitems [0]] = $menubaseitems [1];
			}
		}
		if (array_key_exists ( 'menus', $_SESSION ) && array_key_exists ( 'menuDownloads', $_SESSION ['menus'] ))
			$menuDownloads = $_SESSION ['menus'] ['menuDownloads'];
	}
	if (array_key_exists ( 'menuView', $_GET )) {
		$menuView = $_GET ['menuView'];
		$_SESSION ['menus'] ['menuView'] = $menuView;
		$menuscookie = "";
		foreach ($_SESSION['menus'] as $key=>$value)
			$menuscookie .= $key . ":" . $value . ";";
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "menus", $menuscookie, $cookietime, "/" );
	} elseif (array_key_exists ( 'menuView', $_POST )) {
		$menuView = $_POST ['menuView'];
		$_SESSION ['menus'] ['menuView'] = $menuView;
		$menuscookie = "";
		foreach ($_SESSION ['menus'] as $key=>$value)
			$menuscookie .= $key . ":" . $value . ";";
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "menus", $menuscookie, $cookietime, "/" );
	} elseif (array_key_exists ( 'menus', $_SESSION ) && array_key_exists ( 'menuView', $_SESSION ['menus'] ))
		$menuView = $_SESSION ['menus'] ['menuView'];
	elseif (array_key_exists ( 'menus', $_COOKIE )) {
		$menubase = explode ( ";", $_COOKIE ['menus'] );
		foreach ($menubase as $key=>$value) {
			if ($value) {
				$menubaseitems = explode ( ":", $value );
				$_SESSION ['menus'] [$menubaseitems [0]] = $menubaseitems [1];
			}
		}
		if (array_key_exists ( 'menus', $_SESSION ) && array_key_exists ( 'menuView', $_SESSION ['menus'] ))
			$menuView = $_SESSION ['menus'] ['menuView'];
	}
	if (array_key_exists ( 'menuAddChange', $_GET )) {
		$menuAddChange = $_GET ['menuAddChange'];
		$_SESSION ['menus'] ['menuAddChange'] = $menuAddChange;
		$menuscookie = "";
		foreach ($_SESSION['menus'] as $key=>$value)
			$menuscookie .= $key . ":" . $value . ";";
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "menus", $menuscookie, $cookietime, "/" );
	} elseif (array_key_exists ( 'menuAddChange', $_POST )) {
		$menuAddChange = $_POST ['menuAddChange'];
		$_SESSION ['menus'] ['menuAddChange'] = $menuAddChange;
		$menuscookie = "";
		foreach ($_SESSION['menus'] as $key=>$value)
			$menuscookie .= $key . ":" . $value . ";";
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "menus", $menuscookie, $cookietime, "/" );
	} elseif (array_key_exists ( 'menus', $_SESSION ) && array_key_exists ( 'menuAddChange', $_SESSION ['menus'] )) {
		$menuAddChange = $_SESSION ['menus'] ['menuAddChange'];
	} elseif (array_key_exists ( 'menus', $_COOKIE )) {
		$menubase = explode ( ";", $_COOKIE ['menus'] );
		foreach ($menubase as $key=>$value) {
			if ($value) {
				$menubaseitems = explode ( ":", $value );
				$_SESSION ['menus'] [$menubaseitems [0]] = $menubaseitems [1];
			}
		}
		if (array_key_exists ( 'menus', $_SESSION ) && array_key_exists ( 'menuAddChange', $_SESSION ['menus'] ))
			$menuAddChange = $_SESSION ['menus'] ['menuAddChange'];
	}
	if (array_key_exists ( 'menuMoon', $_GET )) {
		$menuMoon = $_GET ['menuMoon'];
		$_SESSION ['menus'] ['menuMoon'] = $menuMoon;
		$menuscookie = "";
		foreach ($_SESSION['menus'] as $key=>$value)
			$menuscookie .= $key . ":" . $value . ";";
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "menus", $menuscookie, $cookietime, "/" );
	} elseif (array_key_exists ( 'menuMoon', $_POST )) {
		$menuMoon = $_POST ['menuMoon'];
		$_SESSION ['menus'] ['menuMoon'] = $menuMoon;
		$menuscookie = "";
		foreach ($_SESSION['menus'] as $key=>$value)
			$menuscookie .= $key . ":" . $value . ";";
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "menus", $menuscookie, $cookietime, "/" );
	} elseif (array_key_exists ( 'menus', $_SESSION ) && array_key_exists ( 'menuMoon', $_SESSION ['menus'] )) {
		$menuMoon = $_SESSION ['menus'] ['menuMoon'];
	} elseif (array_key_exists ( 'menus', $_COOKIE )) {
		$menubase = explode ( ";", $_COOKIE ['menus'] );
		foreach ($menubase as $key=>$value) {
			if ($value) {
				$menubaseitems = explode ( ":", $value );
				$_SESSION ['menus'] [$menubaseitems [0]] = $menubaseitems [1];
			}
		}
		if (array_key_exists ( 'menus', $_SESSION ) && array_key_exists ( 'menuMoon', $_SESSION ['menus'] ))
			$menuMoon = $_SESSION ['menus'] ['menuMoon'];
	}
	if ($objUtil->checkGetKey ( 'topmenu' ) == 'hidden') {
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "topmenu", 'hidden', $cookietime, "/" );
	}
	if ($objUtil->checkGetKey ( 'topmenu' ) == 'show') {
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "topmenu", 'show', $cookietime, "/" );
	}
	if ($objUtil->checkGetKey ( 'leftmenu' ) == 'hidden') {
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "leftmenu", 'hidden', $cookietime, "/" );
	}
	if ($objUtil->checkGetKey ( 'leftmenu' ) == 'show') {
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "leftmenu", 'show', $cookietime, "/" );
	}
	if ($objUtil->checkGetKey ( 'viewobjectextrainfo' ) == 'hidden') {
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "viewobjectextrainfo", 'hidden', $cookietime, "/" );
	}
	if ($objUtil->checkGetKey ( 'viewobjectextrainfo' ) == 'show') {
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "viewobjectextrainfo", 'show', $cookietime, "/" );
	}
	if ($objUtil->checkGetKey ( 'viewobjectdetails' ) == 'hidden') {
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "viewobjectdetails", 'hidden', $cookietime, "/" );
	}
	if ($objUtil->checkGetKey ( 'viewobjectdetails' ) == 'show') {
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "viewobjectdetails", 'show', $cookietime, "/" );
	}
	if ($objUtil->checkGetKey ( 'viewobjectephemerides' ) == 'hidden') {
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "viewobjectephemerides", 'hidden', $cookietime, "/" );
	}
	if ($objUtil->checkGetKey ( 'viewobjectephemerides' ) == 'show') {
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "viewobjectephemerides", 'show', $cookietime, "/" );
	}
	if ($objUtil->checkGetKey ( 'viewobjectobjectsnearby' ) == 'hidden') {
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "viewobjectobjectsnearby", 'hidden', $cookietime, "/" );
	}
	if ($objUtil->checkGetKey ( 'viewobjectobjectsnearby' ) == 'show') {
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "viewobjectobjectsnearby", 'show', $cookietime, "/" );
	}
	if ($objUtil->checkGetKey ( 'viewobjectobservations' ) == 'hidden') {
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "viewobjectobservations", 'hidden', $cookietime, "/" );
	}
	if ($objUtil->checkGetKey ( 'viewobjectobservations' ) == 'show') {
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "viewobjectobservations", 'show', $cookietime, "/" );
	}
	if (array_key_exists ( 'menuAdmin', $_GET )) {
		$menuAdmin = $_GET ['menuAdmin'];
		$_SESSION ['menus'] ['menuAdmin'] = $menuAdmin;
		$menuscookie = "";
		foreach ($_SESSION['menus'] as $key=>$value)
			$menuscookie .= $key . ":" . $value . ";";
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "menus", $menuscookie, $cookietime, "/" );
	} elseif (array_key_exists ( 'menuAdmin', $_POST )) {
		$menuAdmin = $_POST ['menuAdmin'];
		$_SESSION ['menus'] ['menuAdmin'] = $menuAdmin;
		$menuscookie = "";
		foreach ($_SESSION['menus'] as $key=>$value)
			$menuscookie .= $key . ":" . $value . ";";
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "menus", $menuscookie, $cookietime, "/" );
	} elseif (array_key_exists ( 'menus', $_SESSION ) && array_key_exists ( 'menuAdmin', $_SESSION ['menus'] )) {
		$menuAdmin = $_SESSION ['menus'] ['menuAdmin'];
	} elseif (array_key_exists ( 'menus', $_COOKIE )) {
		$menubase = explode ( ";", $_COOKIE ['menus'] );
		foreach ($menubase as $key=>$value) {
			if ($value) {
				$menubaseitems = explode ( ":", $value );
				$_SESSION ['menus'] [$menubaseitems [0]] = $menubaseitems [1];
			}
		}
		if (array_key_exists ( 'menus', $_SESSION ) && array_key_exists ( 'menuAdmin', $_SESSION ['menus'] ))
			$menuAdmin = $_SESSION ['menus'] ['menuAdmin'];
	}
	if (array_key_exists ( 'menuLogin', $_GET )) {
		$menuLogin = $_GET ['menuLogin'];
		$_SESSION ['menus'] ['menuLogin'] = $menuLogin;
		$menuscookie = "";
		foreach ($_SESSION['menus'] as $key=>$value)
			$menuscookie .= $key . ":" . $value . ";";
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "menus", $menuscookie, $cookietime, "/" );
	} elseif (array_key_exists ( 'menuLogin', $_POST )) {
		$menuLogin = $_POST ['menuLogin'];
		$_SESSION ['menus'] ['menuLogin'] = $menuLogin;
		$menuscookie = "";
		foreach ($_SESSION['menus'] as $key=>$value)
			$menuscookie .= $key . ":" . $value . ";";
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "menus", $menuscookie, $cookietime, "/" );
	} elseif (array_key_exists ( 'menus', $_SESSION ) && array_key_exists ( 'menuLogin', $_SESSION ['menus'] )) {
		$menuLogin = $_SESSION ['menus'] ['menuLogin'];
	} elseif (array_key_exists ( 'menus', $_COOKIE )) {
		$menubase = explode ( ";", $_COOKIE ['menus'] );
		foreach ($menubase as $key=>$value) {
			if ($value) {
				$menubaseitems = explode ( ":", $value );
				$_SESSION ['menus'] [$menubaseitems [0]] = $menubaseitems [1];
			}
		}
		if (array_key_exists ( 'menus', $_SESSION ) && array_key_exists ( 'menuLogin', $_SESSION ['menus'] ))
			$menuLogin = $_SESSION ['menus'] ['menuLogin'];
	}
	if (array_key_exists ( 'menuSearch', $_GET )) {
		$menuSearch = $_GET ['menuSearch'];
		$_SESSION ['menus'] ['menuSearch'] = $menuSearch;
		$menuscookie = "";
		foreach ($_SESSION['menus'] as $key=>$value)
			$menuscookie .= $key . ":" . $value . ";";
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "menus", $menuscookie, $cookietime, "/" );
	} elseif (array_key_exists ( 'menuSearch', $_POST )) {
		$menuSearch = $_POST ['menuSearch'];
		$_SESSION ['menus'] ['menuSearch'] = $menuSearch;
		$menuscookie = "";
		foreach  ($_SESSION['menus'] as $key=>$value)
			$menuscookie .= $key . ":" . $value . ";";
		$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
		setcookie ( "menus", $menuscookie, $cookietime, "/" );
	} elseif (array_key_exists ( 'menus', $_SESSION ) && array_key_exists ( 'menuSearch', $_SESSION ['menus'] )) {
		$menuSearch = $_SESSION ['menus'] ['menuSearch'];
	} elseif (array_key_exists ( 'menus', $_COOKIE )) {
		$menubase = explode ( ";", $_COOKIE ['menus'] );
		foreach ($menubase as $key=>$value) {
			if ($value) {
				$menubaseitems = explode ( ":", $value );
				$_SESSION ['menus'] [$menubaseitems [0]] = $menubaseitems [1];
			}
		}
		if (array_key_exists ( 'menus', $_SESSION ) && array_key_exists ( 'menuSearch', $_SESSION ['menus'] ))
			$menuSearch = $_SESSION ['menus'] ['menuSearch'];
	}
	// ============================================================================== COMMON INSTRUCTIONS
	foreach ($modules as $key=>$value) // change module
		if ($objUtil->checkGetKey ( 'indexAction' ) == 'module' . $value) {
			$_SESSION ['module'] = $value;
			setcookie ( "module", $value, time () + (365 * 24 * 60 * 60), "/" );
		}
	if ($objUtil->checkGetKey ( 'indexAction' ) == "validate_delete_eyepiece") // delete eyepiece
{
		$entryMessage .= $objEyepiece->validateDeleteEyepiece ();
		if ($_SESSION ['admin'] == 'yes')
			$_GET ['indexAction'] = 'overview_eyepieces';
		else
			$_GET ['indexAction'] = 'view_eyepieces';
	}
	if ($objUtil->checkGetKey ( 'indexAction' ) == "validate_delete_existingsession") // delete existing session
{
		$entryMessage .= $objSession->validateDeleteSession ();
		$_GET ['indexAction'] = 'result_my_sessions';
	}
	if ($objUtil->checkGetKey ( 'indexAction' ) == "change_session") // change existing session
{
		$entryMessage .= $objSession->validateChangeSession ();
		$_GET ['indexAction'] = 'result_my_sessions';
	}
	if ($objUtil->checkGetKey ( 'indexAction' ) == "validate_delete_filter") // delete filter
{
		$entryMessage .= $objFilter->validateDeleteFilter ();
		if ($_SESSION ['admin'] == 'yes')
			$_GET ['indexAction'] = 'overview_filters';
		else
			$_GET ['indexAction'] = 'view_filters';
	}
	if ($objUtil->checkGetKey ( 'indexAction' ) == "validate_delete_instrument") // delete instrument
{
		$entryMessage .= $objInstrument->validateDeleteInstrument ();
		if ($_SESSION ['admin'] == 'yes')
			$_GET ['indexAction'] = 'overview_instruments';
		else
			$_GET ['indexAction'] = 'view_instruments';
	}
	if ($objUtil->checkGetKey ( 'indexAction' ) == "validate_delete_lens") // delete lens
{
		$entryMessage .= $objLens->validateDeleteLens ();
		if ($_SESSION ['admin'] == 'yes')
			$_GET ['indexAction'] = 'view_lenses';
		else
			$_GET ['indexAction'] = "view_lenses";
	}
	if ($objUtil->checkGetKey ( 'indexAction' ) == "validate_delete_location") // delete location
{
		$entryMessage .= $objLocation->validateDeleteLocation ();
		if ($_SESSION ['admin'] == 'yes')
			$_GET ['indexAction'] = 'view_locations';
		else
			$_GET ['indexAction'] = 'view_sites';
	}
	if ($objUtil->checkGetKey ( 'indexAction' ) == "validate_delete_message") // delete message
{
		$entryMessage .= $objMessages->validateDeleteMessage ();
		$_GET ['indexAction'] = 'show_messages';
	}
	if ($objUtil->checkGetKey ( 'indexAction' ) == "validate_account") // validate account
{
		$objObserver->valideAccount ();
		// $entryMessage is set in the validateAccount() function;
		// $_GET['indexAction'] is set in the validateAccount() function
	}
	if ($objUtil->checkGetKey ( 'indexAction' ) == "requestPassword") {
		$objObserver->requestNewPassword ();
	}
	if ($objUtil->checkGetKey ( 'indexAction' ) == "validate_eyepiece") // validate eyepiece
	{
		$entryMessage .= $objEyepiece->validateSaveEyepiece ();
		$_GET ['indexAction'] = 'view_eyepieces';
	}
	if ($objUtil->checkGetKey ( 'indexAction' ) == "validate_filter") // validate filter
{
		$entryMessage .= $objFilter->validateSaveFilter ();
		$_GET ['indexAction'] = 'view_filters';
	}
	if ($objUtil->checkGetKey ( 'indexAction' ) == "validate_instrument") // validate instrument
{
		$entryMessage .= $objInstrument->validateSaveInstrument ();
		$_GET ['indexAction'] = 'view_instruments';
	}
	if ($objUtil->checkGetKey ( 'indexAction' ) == "validate_lens") // validate lens
{
		$entryMessage .= $objLens->validateSaveLens ();
		$_GET ['indexAction'] = 'view_lenses';
	}
	if ($objUtil->checkPostKey ( 'indexAction' ) == "validate_site") // validate location
    {
		$entryMessage .= $objLocation->validateSaveLocation();
		$_GET['indexAction'] = "view_sites";
	}
	// ============================================================================== DEEPSKY INSTRUCTIONS
	$object = $objUtil->checkPostKey ( 'object', $objUtil->checkGetKey ( 'object' ) );
	if (($objUtil->checkGetKey ( 'indexAction' ) == 'quickpick') && // ========================================================= New Observation From quickpick
($objUtil->checkGetKey ( 'object' )) && ($objObject->getExactDsObject ( $_GET ['object'] )) && (array_key_exists ( 'newObservationQuickPick', $_GET ))) {
		$_POST ['year'] = $objUtil->checkPostKey ( 'year', $objUtil->checkArrayKey ( $_SESSION, 'newObsYear' ) );
		$_POST ['month'] = $objUtil->checkPostKey ( 'month', $objUtil->checkArrayKey ( $_SESSION, 'newObsMonth' ) );
		$_POST ['day'] = $objUtil->checkPostKey ( 'day', $objUtil->checkArrayKey ( $_SESSION, 'newObsDay' ) );
		$_POST ['instrument'] = $objUtil->checkPostKey ( 'instrument', $objUtil->checkArrayKey ( $_SESSION, 'newObsInstrument', $objObserver->getObserverProperty ( $loggedUser, 'stdtelescope' ) ) );
		$_POST ['site'] = $objUtil->checkPostKey ( 'site', $objUtil->checkArrayKey ( $_SESSION, 'newObsLocation', $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' ) ) );
		$_POST ['limit'] = $objUtil->checkPostKey ( 'limit', $objUtil->checkArrayKey ( $_SESSION, 'newObsLimit' ) );
		$_POST ['sqm'] = $objUtil->checkPostKey ( 'sqm', $objUtil->checkArrayKey ( $_SESSION, 'newObsSQM' ) );
		$_POST ['seeing'] = $objUtil->checkPostKey ( 'seeing', $objUtil->checkArrayKey ( $_SESSION, 'newObsSeeing' ) );
		$_POST ['description_language'] = $objUtil->checkPostKey ( 'description_language', $objUtil->checkArrayKey ( $_SESSION, 'newObsLanguage' ) );
		$_POST ['timestamp'] = time ();
		$_SESSION ['addObs'] = $_POST ['timestamp'];
	}
	if ($objUtil->checkGetKey ( 'indexAction' ) == "add_observation") {
		if (array_key_exists ( 'number', $_POST ) && (! $_POST ['number']))
			$_GET ['indexAction'] = "query_objects";
		elseif (array_key_exists ( 'number', $_POST ) && (! ($_GET ['object'] = $objObject->getExactDsObject ( trim ( $objUtil->checkPostKey ( 'catalog' ) ) . ' ' . trim ( $objUtil->checkPostKey ( 'number' ) ), '', '' )))) {
			$entryMessage .= LangInstructionsNoObjectFound . $objUtil->checkPostKey ( 'catalog' ) . " " . $objUtil->checkPostKey ( 'number' );
			$_GET ['indexAction'] = "query_objects";
		} else {
			$_POST ['year'] = $objUtil->checkPostKey ( 'year', $objUtil->checkArrayKey ( $_SESSION, 'newObsYear' ) );
			$_POST ['month'] = $objUtil->checkPostKey ( 'month', $objUtil->checkArrayKey ( $_SESSION, 'newObsMonth' ) );
			$_POST ['day'] = $objUtil->checkPostKey ( 'day', $objUtil->checkArrayKey ( $_SESSION, 'newObsDay' ) );
			$_POST ['instrument'] = $objUtil->checkPostKey ( 'instrument', $objUtil->checkSessionKey ( 'newObsInstrument', $objObserver->getObserverProperty ( $loggedUser, 'stdtelescope' ) ) );
			$_POST ['site'] = $objUtil->checkPostKey ( 'site', $objUtil->checkSessionKey ( 'newObsLocation', $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' ) ) );
			$_POST ['limit'] = $objUtil->checkPostKey ( 'limit', $objUtil->checkSessionKey ( 'newObsLimit', $objLocation->getLocationPropertyFromId ( 'limitingMagnitude', - 999 ) ) );
			$_POST ['sqm'] = $objUtil->checkPostKey ( 'sqm', $objUtil->checkArrayKey ( $_SESSION, 'newObsSQM' ) );
			$_POST ['seeing'] = $objUtil->checkPostKey ( 'seeing', $objUtil->checkArrayKey ( $_SESSION, 'newObsSeeing' ) );
			$_POST ['description_language'] = $objUtil->checkPostKey ( 'description_language', $objUtil->checkArrayKey ( $_SESSION, 'newObsLanguage' ) );
			$_POST ['timestamp'] = time ();
			$_SESSION ['addObs'] = $_POST ['timestamp'];
		}
	}
	if (array_key_exists ( 'indexAction', $_POST ) && $_POST ['indexAction'] == "validate_observation")
		$objObservation->validateObservation ();
	if (array_key_exists ( 'indexAction', $_POST ) && $_POST ['indexAction'] == "validate_message")
		$objMessages->validateMessage ();
	if (array_key_exists ( 'indexAction', $_POST ) && $_POST ['indexAction'] == "validate_session")
		$objSession->validateSession ();
	if ($objUtil->checkRequestKey ( ('phase1') ))
		$_REQUEST ['phase'] = 1;
	if ($objUtil->checkRequestKey ( ('phase2') ))
		$_REQUEST ['phase'] = 2;
	if (array_key_exists ( 'indexAction', $_GET ) && $_GET ['indexAction'] == "validate_object")
		$objObject->validateObject ();
	if (array_key_exists ( 'indexAction', $_GET ) && $_GET ['indexAction'] == "validate_delete_observation") {
		$entryMessage .= $objObservation->validateDeleteDSObservation ();
		$_GET ['indexAction'] = 'default_action';
		unset ( $_GET ['validate_delete_observation'] );
	}
	if (array_key_exists ( 'indexAction', $_GET ) && $_GET ['indexAction'] == "manage_csv_objects")
		include_once "deepsky/control/manage_csv_objects.php";
	if (array_key_exists ( 'indexAction', $_GET ) && $_GET ['indexAction'] == "add_csv_observations")
		$entryMessage .= $objObservation->addCSVobservations ();
	if (array_key_exists ( 'indexAction', $_GET ) && $_GET ['indexAction'] == "add_csv_listdata")
		include_once "deepsky/control/add_csv_listdata.php";
	if (array_key_exists ( 'indexAction', $_GET ) && $_GET ['indexAction'] == "addXmlObservations")
		include_once "deepsky/control/addXmlObservations.php";
	if (array_key_exists ( 'noShowName', $_GET ) && (array_key_exists ( "Qobj", $_SESSION ))) {
		foreach ($_SESSION['Qobj'] as $key=>$value)
			if (strpos ( $_SESSION ["Qobj"] [$key] ["showname"], "(" ))
				$_SESSION ["Qobj"] [$key] ["showname"] = substr ( $_SESSION ["Qobj"] [$key] ["showname"], strpos ( $_SESSION ["Qobj"] [$key] ["showname"], "(" ) + 1, strpos ( $_SESSION ["Qobj"] [$key] ["showname"], ")" ) - strpos ( $_SESSION ["Qobj"] [$key] ["showname"], "(" ) - 1 ) . " (" . substr ( $_SESSION ["Qobj"] [$key] ["showname"], 0, strpos ( $_SESSION ["Qobj"] [$key] ["showname"], "(" ) - 1 ) . ")";
		reset ( $_SESSION ["Qobj"] );
	}

	// ============================================================================ LIST COMMANDS
	if ($objUtil->checkGetKey ( 'emptyList' ) && $myList) {
		$objList->emptyList ( $listname );
		$entryMessage .= LangToListEmptied . $listname_ss . ".";
		unset ( $_SESSION ['QobjParams'] );
		unset ( $_GET ['emptyList'] );
	}
	if ($objUtil->checkGetKey ( 'ObjectDownInList' ) && $myList) {
		$objList->ObjectDownInList ( $_GET ['ObjectDownInList'] );
		unset ( $_SESSION ['QobjParams'] );
		$entryMessage .= LangToListMoved1 . $_GET ['ObjectDownInList'] . LangToListMoved3 . "<a href=\"" . $baseURL . "index.php?indexAction=listaction&amp;manage=manage\">" . $listname_ss . "</a>.";
		unset ( $_GET ['ObjectDownInList'] );
	}
	if ($objUtil->checkGetKey ( 'ObjectUpInList' ) && $myList) {
		$objList->ObjectUpInList ( $_GET ['ObjectUpInList'] );
		unset ( $_SESSION ['QobjParams'] );
		$entryMessage .= LangToListMoved1 . $_GET ['ObjectUpInList'] . LangToListMoved2 . "<a href=\"" . $baseURL . "index.php?indexAction=listaction&amp;manage=manage\">" . $listname_ss . "</a>.";
		unset ( $_GET ['ObjectUpInList'] );
	}

	if ($objUtil->checkGetKey ( 'ObjectToPlaceInList' ) && $myList) {
		$entryMessage .= $objList->ObjectFromToInList ( $_GET ['ObjectFromPlaceInList'], $_GET ['ObjectToPlaceInList'] );
		unset ( $_SESSION ['QobjParams'] );
		unset ( $_GET ['ObjectToPlaceInList'] );
	}
	if ($objUtil->checkGetKey ( 'removePageObjectsFromList' ) && $myList) {
		if (count ( $_SESSION ['Qobj'] ) > 0) {
			$count = $objUtil->checkRequestKey ( 'min' );
			while ( ($count < ($objUtil->checkRequestKey ( 'max' ))) && ($count < count ( $_SESSION ['Qobj'] )) ) {
				$objList->removeObjectFromList ( $_SESSION ['Qobj'] [$count] ['objectname'], $_SESSION ['Qobj'] [$count] ['showname'] );
				$count ++;
			}
			unset ( $_SESSION ['QobjParams'] );
			$entryMessage .= LangToListPageRemoved;
		}
		unset ( $_GET ['removePageObjectsFromList'] );
	}
	if (($objUtil->checkSessionKey ( 'admin' ) == 'yes') && $objUtil->checkPostKey ( 'objectToDelete' ) && $objectname = $objUtil->checkPostKey ('object')) {
		$objObject->deleteObject($objectname);
		unset ( $_POST ['objectToDelete'] );
		unset ( $_POST ['object'] );
		$entryMessage .= LangDeleteSuccess . " <strong>" . $objectname . "</strong>";
	}
	if ($objUtil->checkGetKey ( 'addList' ) && ($listnameToAdd = $objUtil->checkGetKey ( 'addlistname' ))) {
		unset ( $_SESSION ['QobjParams'] );
		if ($objList->checkList ( $listnameToAdd ) != 0) {
			$entryMessage .= LangToListList . stripslashes ( $listnameToAdd ) . LangToListExists;
		} else {
			$objList->addList ( $listnameToAdd, $objUtil->checkGetKey ( "PublicList", false ) );
			$_SESSION ['listname'] = $listnameToAdd;
			$listname = $_SESSION ['listname'];
			$listname_ss = stripslashes ( $listname );
			$myList = true;
			$entryMessage .= LangToListList . $listname_ss . LangToListAdded;
		}
		unset ( $_GET ['addList'] );
	}
	if ($objUtil->checkGetKey ( 'switchPublicPrivate' ) && ($listname = $objUtil->checkGetKey ( 'listname' ))) {
		$objList->switchPublicPrivate ( $listname );
		$_GET ['indexAction'] = 'view_lists';
	}
	if ($objUtil->checkGetKey ( 'switchPublicPrivateList' ) && ($listname = $objUtil->checkGetKey ( 'listname' ))) {
		$objList->switchPublicPrivate ( $listname );
		$_GET ['indexAction'] = 'listaction';
		$_GET ['listname'] = $listname;
		unset ( $_GET ['switchPublicPrivateList'] );
	}
	if ($objUtil->checkPostKey ( 'changePassword' )) {
		$login = $objUtil->checkPostKey( 'userid' ); // get password from form and encrypt
		$passwd = md5 ( $objUtil->checkPostKey( 'currentPassword' ) );

		$newPassword =  md5 ( $objUtil->checkPostKey( 'newPassword' ) );
		$confirmNewPassword =  md5 ( $objUtil->checkPostKey( 'confirmPassword' ) );

		$objObserver->updatePassword($login, $passwd, $newPassword, $confirmNewPassword);
	}
	if ($objUtil->checkPostKey ( 'changePasswordToken' )) {
		$login = $objUtil->checkPostKey( 'userid' );
		$token = $objUtil->checkPostKey( 'token' );
		$newPassword =  md5 ( $objUtil->checkPostKey( 'newPassword' ) );
		$confirmNewPassword =  md5 ( $objUtil->checkPostKey( 'confirmPassword' ) );

		$objObserver->updatePasswordToken($login, $newPassword, $confirmNewPassword);

		// Remove token
		include_once $instDir . "lib/password.php";
		$password = new Password();

		$password->removeToken($token);
	}
	if ($objUtil->checkGetKey ( 'renameList' ) && ($listnameToAdd = $objUtil->checkGetKey ( 'addlistname' ))) {
		unset ( $_SESSION ['QobjParams'] );
		$listNameFrom = $_GET ['listnamefrom'];
		$listnameTo = $_GET ['addlistname'];

		if ($objList->checkList ( $listnameTo ) != 0 && $objUtil->checkGetKey ( "PublicList", false ) === $objList->isPublic ( $listNameFrom, $loggedUser )) {
			$entryMessage .= LangToListList . stripslashes ( $listnameTo ) . LangToListExists;
		} else {
			$objList->renameList ( $listNameFrom, $listnameTo, $objUtil->checkGetKey ( "PublicList", false ) );
			$_SESSION ['listname'] = $listnameTo;
			$listname = $_SESSION ['listname'];
			$listname_ss = stripslashes ( $listname );
			$myList = true;
			$entryMessage .= LangToListRenamed . $listname_ss . "\".";
		}
		unset ( $_GET ['renameList'] );
	}
	if ($objUtil->checkGetKey ( 'removeList' )) {
		unset ( $_SESSION ['QobjParams'] );
		if ($objUtil->checkGetKey ( 'listname' ) != "" && $objUtil->checkGetKey ( 'listname' ) != "----------") {
			$listname = $objUtil->checkGetKey ( 'listname' );
			$objList->removeList ( $listname );
			$entryMessage .= LangToListRemoved . stripslashes ( $listname ) . ".";
		} else {
			$objList->removeList ( $listname );
			$listname = '';
			$listname_ss = '';
			$myList = False;
			$entryMessage .= LangToListRemoved . stripslashes ( $_SESSION ['listname'] ) . ".";
			$_SESSION ['listname'] = "----------";
		}
		unset ( $_GET ['removeList'] );
		$_GET ['indexAction'] = 'view_lists';
	}
	if ($objUtil->checkGetKey ( 'addobservationstolist' ) && $myList) {
		$objList->addObservations ( $objUtil->checkGetKey ( 'addobservationstolist' ) );
	}
	if ($objUtil->checkGetKey ( 'removeobservationsfromlist' ) && $myList) {
		$objList->removeObservations ( $objUtil->checkGetKey ( 'removeobservationsfromlist' ) );
	}
	if ($objUtil->checkGetKey ( 'activateList' ) && $objUtil->checkGetKey ( 'listname' ) && ($objUtil->checkGetKey ( 'listname' ) != $objUtil->checkSessionKey ( 'listname' ))) {
		$_SESSION ['listname'] = $_GET ['listname'];
		$listname = $_SESSION ['listname'];
		$listname_ss = stripslashes ( $listname );
		$myList = False;
		if (array_key_exists ( 'listname', $_SESSION ) && $objList->checkList ( $_SESSION ['listname'] ) == 2)
			$myList = True;
		if ($_GET ['listname'] != "----------") {
			if ($myList)
				$toastMessage = LangToListList . $listname_ss . LangToListActivation1;
		} else
			$_GET ['indexAction'] = "defaultAction";
		unset ( $_GET ['activateList'] );
	}
	if ($objUtil->checkGetKey ( 'addObjectToList' ) && $listname && $myList) {
		$objList->addObjectToList ( $_GET ['addObjectToList'], $objUtil->checkGetKey ( 'showname', $_GET ['addObjectToList'] ) );

		// Show a toast message.
		$toastMessage = LangListQueryObjectsMessage8 . $_GET ['showname'] . LangListQueryObjectsMessage6 . $listname_ss;
		unset ( $_GET ['addObjectToList'] );
	}
	if ($objUtil->checkGetKey ( 'addObservationToList' ) && $myList) {
		$objList->addObservationToList ( $_GET ['addObservationToList'] );
		$toastMessage = LangListQueryObjectsMessage16 . LangListQueryObjectsMessage6 . $listname_ss;
		unset ( $_GET ['addObservationToList'] );
	}
	if (array_key_exists ( 'removeObjectFromList', $_GET ) && $_GET ['removeObjectFromList'] && $myList) {
		$objList->removeObjectFromList ( $_GET ['removeObjectFromList'] );
		$toastMessage = LangListQueryObjectsMessage8 . $_GET ['removeObjectFromList'] . LangListQueryObjectsMessage7 . $listname_ss;
		unset ( $_GET ['removeObjectFromList'] );
	}

	if (array_key_exists ( 'addAllObjectsFromPageToList', $_GET ) && $_GET ['addAllObjectsFromPageToList'] && $myList) {
		$count = $objUtil->checkRequestKey ( 'min' );
		while ( ($count < $objUtil->checkRequestKey ( 'max' )) && ($count < count ( $_SESSION ['Qobj'] )) ) {
			$objList->addObjectToList ( $_SESSION ['Qobj'] [$count] ['objectname'], $_SESSION ['Qobj'] [$count] ['showname'] );
			$count ++;
		}
		$toastMessage = LangListQueryObjectsMessage9 . $listname_ss;
		unset ( $_GET ['addAllObjectsFromPageToList'] );
	}
	if (array_key_exists ( 'addAllObjectsFromQueryToList', $_GET ) && $_GET ['addAllObjectsFromQueryToList'] && $myList) {
		$count = 0;
		while ( $count < count ( $_SESSION ['Qobj'] ) ) {
			$objList->addObjectToList ( $_SESSION ['Qobj'] [$count] ['objectname'], $_SESSION ['Qobj'] [$count] ['showname'] );
			$count ++;
		}
		$toastMessage = LangListQueryObjectsMessage9 . $_SESSION ['listname'];
		unset ( $_GET ['addAllObjectsFromQueryToList'] );
	}
	if (array_key_exists ( 'editListObjectDescription', $_GET ) && $_GET ['editListObjectDescription'] && array_key_exists ( 'object', $_GET ) && $_GET ['object'] && array_key_exists ( 'description', $_GET ) && $myList) {
		$objList->setListObjectDescription ( $_GET ['object'], $_GET ['description'] );
		unset ( $_GET ['addAllObjectsFromPageToList'] );
	}

	// =========================================================================== COMET COMMANDS
	if (array_key_exists ( 'indexAction', $_GET ) && $_GET ['indexAction'] == "comets_validate_change_observation")
		include_once 'comets/control/validate_change_observation.php';
	if (array_key_exists ( 'indexAction', $_GET ) && $_GET ['indexAction'] == "comets_validate_delete_observation")
		include_once 'comets/control/validate_delete_observation.php';
	if (array_key_exists ( 'indexAction', $_GET ) && $_GET ['indexAction'] == "comets_validate_observation")
		include_once 'comets/control/validate_observation.php';
	if (array_key_exists ( 'indexAction', $_GET ) && $_GET ['indexAction'] == "comets_validate_object")
		include_once 'comets/control/validate_object.php';
	if (array_key_exists ( 'indexAction', $_GET ) && $_GET ['indexAction'] == "comets_validate_change_object")
		include_once 'comets/control/validate_change_object.php';
		// ============================================================================ ADMIN COMMANDS
	if (($objUtil->checkSessionKey ( 'admin' ) == 'yes') && ($objUtil->checkGetKey ( 'indexAction' ) == "validate_observer")) // validate observer
{
		$entryMessage .= $objObserver->validateObserver ();
		$_GET ['indexAction'] = 'view_observers';
	}
	if (($objUtil->checkSessionKey ( 'admin' ) == 'yes') && ($objUtil->checkGetKey ( 'indexAction' ) == "validate_delete_observer")) // validate observer
{
		$entryMessage .= $objObserver->validateDeleteObserver ();
		unset ( $_SESSION ['observersArr'] );
		$_GET ['indexAction'] = 'view_observers';
	}
	if (($objUtil->checkSessionKey ( 'admin' ) == 'yes') && ($objUtil->checkGetKey ( 'indexAction' ) == "change_role")) {
		if (($_SESSION ['admin'] == "yes") && ($objUtil->checkGetKey ( 'user' ))) {
			$role = $objUtil->checkGetKey ( 'role', 2 );
			$objObserver->setObserverProperty ( $_GET ['user'], 'role', $role );
			$entryMessage .= "Role is successfully updated!";
			unset ( $_SESSION ['observersArr'] );
		}
		$_GET ['indexAction'] = "detail_observer";
	}

	if (($objUtil->checkSessionKey ( 'admin' ) == 'yes') && ($objUtil->checkGetKey ( 'indexAction' ) == "change_emailNameFirstname_Password")) {
		if (($_SESSION ['admin'] == "yes") && ($theuser = $objUtil->checkGetKey ( 'user' )) && $objUtil->checkGetKey ( 'change_email_name_firstname' )) {
			$email = $objUtil->checkGetKey ( 'email', 2 );
			$objObserver->setObserverProperty ( $theuser, 'email', $email );
			$name = $objUtil->checkGetKey ( 'name', 2 );
			$objObserver->setObserverProperty ( $theuser, 'name', $name );
			$firstname = $objUtil->checkGetKey ( 'firstname', 2 );
			$objObserver->setObserverProperty ( $theuser, 'firstname', $firstname );
			$entryMessage .= "Email, name and firstname are successfully updated!";
			unset ( $_SESSION ['observersArr'] );
		}
		if (($_SESSION ['admin'] == "yes") && ($theuser = $objUtil->checkGetKey ( 'user' )) && $objUtil->checkGetKey ( 'change_password' )) {
			if ($password = $objUtil->checkGetKey ( 'password', '' ))
				$objObserver->setObserverProperty ( $theuser, 'password', md5 ( $password ) );
			$entryMessage .= "Password successfully updated!";
			unset ( $_SESSION ['observersArr'] );
		}
		$_GET ['indexAction'] = "detail_observer";
	}

	if (array_key_exists ( 'admin', $_SESSION ) && $_SESSION ['admin'] == "yes") {
		if (array_key_exists ( "newaction", $_GET )) {
			if ($_GET ['newaction'] == "NewName") {
				$objObject->newName ( $_GET ['object'], $_GET ['newcatalog'], $_GET ['newnumber'] );
				$_GET ['object'] = trim ( $_GET ['newcatalog'] . " " . ucwords ( trim ( $_GET ['newnumber'] ) ) );
			}
			if ($_GET ['newaction'] == "NewAltName")
				$objObject->newAltName ( $_GET ['object'], $_GET ['newcatalog'], $_GET ['newnumber'] );
			if ($_GET ['newaction'] == "RemoveAltNameName")
				$objObject->removeAltName ( $_GET ['object'], $_GET ['newcatalog'], $_GET ['newnumber'] );
			if ($_GET ['newaction'] == "NewPartOf")
				$objObject->newPartOf ( $_GET ['object'], $_GET ['newcatalog'], $_GET ['newnumber'] );
			if ($_GET ['newaction'] == "RemovePartOf")
				$objObject->removePartOf ( $_GET ['object'], $_GET ['newcatalog'], $_GET ['newnumber'] );
			if ($_GET ['newaction'] == "RemoveAndReplaceObjectBy") {
				$objObject->removeAndReplaceObjectBy ( $_GET ['object'], $_GET ['newcatalog'], $_GET ['newnumber'] );
				$_GET ['object'] = trim ( $_GET ['newcatalog'] . " " . ucwords ( trim ( $_GET ['newnumber'] ) ) );
			}
			if ($_GET ['newaction'] == "LangObjectSetRA") {
				$objObject->setDsoProperty ( $_GET ['object'], 'ra', $_GET ['newnumber'] );
				$objObject->setDsObjectAtlasPages ( $_GET ['object'] );
			}
			if ($_GET ['newaction'] == "LangObjectSetDECL") {
				$objObject->setDsoProperty ( $_GET ['object'], 'decl', $_GET ['newnumber'] );
				$objObject->setDsObjectAtlasPages ( $_GET ['object'] );
			}
			if ($_GET ['newaction'] == "LangObjectSetCon")
				$objObject->setDsoProperty ( $_GET ['object'], 'con', $_GET ['newnumber'] );
			if ($_GET ['newaction'] == "LangObjectSetType")
				$objObject->setDsoProperty ( $_GET ['object'], 'type', $_GET ['newnumber'] );
			if ($_GET ['newaction'] == "LangObjectSetMag") {
				$objObject->setDsoProperty ( $_GET ['object'], 'mag', $_GET ['newnumber'] );
				$objObject->setDsObjectSBObj ( $_GET ['object'] );
			}
			if ($_GET ['newaction'] == "LangObjectSetSUBR")
				$objObject->setDsoProperty ( $_GET ['object'], 'subr', $_GET ['newnumber'] );
			if ($_GET ['newaction'] == "LangObjectSetDiam1") {
				$objObject->setDsoProperty ( $_GET ['object'], 'diam1', $_GET ['newnumber'] );
				$objObject->setDsObjectSBObj ( $_GET ['object'] );
			}
			if ($_GET ['newaction'] == "LangObjectSetDiam2") {
				$objObject->setDsoProperty ( $_GET ['object'], 'diam2', $_GET ['newnumber'] );
				$objObject->setDsObjectSBObj ( $_GET ['object'] );
			}
			if ($_GET ['newaction'] == "LangObjectSetPA")
				$objObject->setDsoProperty ( $_GET ['object'], 'pa', $_GET ['newnumber'] );
			if ($_GET ['newaction'] == "LangObjectSetDESC")
				$objObject->setDsoProperty ( $_GET ['object'], 'description', $_GET ['newnumber'] );
		}
	}
}
?>
