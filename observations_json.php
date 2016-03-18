<?php
ini_set('display_errors', '1');

header('Content-Type: application/json');

$inIndex = true;
$language = "nl";

if (! array_key_exists ( 'indexAction', $_GET ) && array_key_exists ( 'indexAction', $_POST ))
	$_GET ['indexAction'] = $_POST ['indexAction'];
	date_default_timezone_set ( 'UTC' );

require_once 'common/entryexit/globals.php'; // Includes of all classes and assistance files
require_once 'common/entryexit/preludes.php'; // Includes of all classes and assistance files
require_once 'common/entryexit/instructions.php'; // Execution of all non-layout related instructions (login, add objects to lists, etc.)
require_once 'common/entryexit/data.php'; // Get data for the form, object data, observation data, etc.
require_once 'lib/moonpic.php'; //gets the appropriate image for the moonphase at observing time

global $loggedUser;

	function getObserverImage($name){
		global $instDir;
		// Show the picture of the sender, this is crazy, should be in database
		$dir = opendir ( $instDir . 'common/observer_pics' );
		while ( FALSE !== ($file = readdir ( $dir )) ) {
			if (("." == $file) or (".." == $file))
				continue; // skip current directory and directory above
				if (fnmatch ( $name . ".gif", $file ) ||
					fnmatch ( $name . ".jpg", $file ) ||
					fnmatch ( $name . ".png", $file )) {
					return "/common/observer_pics/" . $file;
				}
		}
	};

	global $objDatabase, $objObject;

	$constellations = $objObject->getConstellations();

	//need to translate here
	//probably better to move this to the bottom part
	$whenQuery = '';
	while(list($key, $value) = each($constellations))
		$whenQuery = $whenQuery . " WHEN objects.con = '{$value}' THEN '{$GLOBALS [$value]}' ";

	$objectname = $_GET['object'];

	if ($loggedUser != null){ 
		$showInches = $objObserver->getObserverProperty ( $loggedUser, "showInches" );
	} else {
		$showInches = 0;
	}

	$query = "SELECT
				observations.id as observationid,
				observations.objectname,
				observations.observerid,
				observations.instrumentid,
				observations.locationid,
				observations.description as observationdescription,
				observations.seeing,
				observations.hasdrawing,
				IF((observations.limmag = 0 || observations.limmag IS NULL) , '-', observations.limmag) as limmag,
				IF(observations.sqm = 0, '-', observations.sqm) as sqm,
				observations.lensid,
				observations.filterid,
				observations.eyepieceid,
				observations.magnification,
				IF(observations.visibility = 0, '-', observations.visibility) as visibility,
				IF(observations.clustertype = '', '-', observations.clustertype) as clustertype,
				DATE_FORMAT(STR_TO_DATE( observations.date, '%Y%m%d'), '%e/%c/%Y') as date,
				DATE_FORMAT(STR_TO_DATE( observations.date, '%Y%m%d'), '%Y-%c-%e') as moondate,
				observations.date as sortdate,
				observations.date as observationdate,
				observations.time as time,
				IF(observations.time < 0, '-', INSERT(LPAD(observations.time, 4, '0'), 3, 0, ':')) as displaytime,
				observers.firstname,
				observers.name,
				observations.smalldiameter,
				observations.largediameter,
				observations.language as observerationlanguage,
				IF(eyepieces.id = 0, '-', CONCAT(eyepieces.name, ' (', observations.magnification, 'x)')) as eyepiecedescription,
				IF(lenses.id = 0, '-', CONCAT(lenses.name, ' (', lenses.factor, ')')) as lensdescription,
				IF(filters.id = 0, '-', filters.name) as filterdescription,
				CONCAT(observers.firstname, ' ', observers.name) as observername,
				CONCAT(observers.name, ' ', observers.firstname) as observersortname,
				instruments.name as instrumentname,
				@instrumentdiameterInch :=  ROUND(instruments.diameter*0.039370,1),
				@instrumentdiameterMm :=  ROUND(instruments.diameter),
				IF({$showInches} = 0, @instrumentdiameterMm, @instrumentdiameterInch) as instrumentdiameter,
				IF({$showInches} = 0, 'mm', 'inch') as diameterformat,
				CONCAT(instruments.diameter, ' ', instruments.name) as instrumentsort,
				instruments.id as instrumentid,
				locations.name as locationname,
				locations.id as locationid,
				locations.latitude as lat,
				locations.longitude as lon,
				locations.timezone as timezone,
				objects.con as objectconstellation,
				objects.type as objecttype,
				objects.mag as objectmagnitude,
				objects.subr as objectsurfacebrigthness,
				CASE
					{$whenQuery}
					ELSE ' ' END AS constellation
			FROM observations
			JOIN observers ON observations.observerid = observers.id
			JOIN objects ON observations.objectname = objects.name
			JOIN instruments ON observations.instrumentid = instruments.id
			JOIN locations ON observations.locationid = locations.id
			JOIN lenses ON observations.lensid = lenses.id
			JOIN filters ON observations.filterid = filters.id
			JOIN eyepieces ON observations.eyepieceid = eyepieces.id
			WHERE observations.objectname=:objectname
			OR observations.objectname = (
					SELECT objectname 
					FROM objectnames 
					WHERE altname = :objectname)";
					
	$result = $objDatabase->prepareAndSelectRecordsetArray ($query, array(':objectname'=>$objectname));
	
	$dataTablesObject = new stdClass();
	$usedLang = $objObserver->getObserverProperty ( $loggedUser, "language" );
	if ($loggedUser == ""){
		$usedLang = $_SESSION['lang'];
	};

	while(list($key, $value) = each($result)){
		while(list($k, $v) = each($value)){
			//add profilepic
			if($k == "observerid"){
				$result[$key]['observerimage'] = getObserverImage($v);
			}
		}
		//add seeing
		$seeing = $result[$key]['seeing'];
		if($seeing >= 0){
			$seeingvar = "Seeing".$seeing;
			$result[$key]['seeing'] = $$seeingvar;
		}

		//add visibility
		$visibility = $result[$key]['visibility'];
		if($visibility != '-'){
			if ($visibility != 99) {
				$visibilityvar = "Visibility".$visibility;
				$result[$key]['visibility'] = $$visibilityvar;
			}
		}

		//add clustertype
		$clustertype = $result[$key]['clustertype'];
		if($clustertype != '-'){
			$clustertypevar = "ClusterType".$clustertype;
			$result[$key]['clustertype'] = $$clustertypevar;
		}

		//add translate
		$lang = $result[$key]['observerationlanguage'];
		$result[$key]['translate'] = (($usedLang != null) && ($usedLang != $lang ));

		//add size
		if($result[$key]['largediameter'] == 0){
			$result[$key]['size'] = '-';
		} else {
			if($result[$key]['largediameter'] > 60){
				$result[$key]['size'] = number_format($result[$key]['largediameter']/60, 1)." x ".number_format($result[$key]['smalldiameter']/60, 1)." ".LangNewObjectSizeUnits1;
			} else {
				$result[$key]['size'] = number_format($result[$key]['largediameter'], 1)." x ".number_format($result[$key]['smalldiameter'], 1)." ".LangNewObjectSizeUnits2;
			}
		}

		//add moonpic
		$result[$key]['moonpic'] = getMoonPic($result[$key]['moondate'], $result[$key]['time'], $result[$key]['lat'], $result[$key]['lon'], $result[$key]['timezone']);
	}

	$dataTablesObject->data = $result;


	$_SESSION['Qobs'] = $result;



	print json_encode($dataTablesObject);
?>
