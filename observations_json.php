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
	$whenQuery = '';
	while(list($key, $value) = each($constellations))
		$whenQuery = $whenQuery . " WHEN objects.con = '{$value}' THEN '{$GLOBALS [$value]}' ";

	$query = "SELECT 
				observations.objectname,
				observations.observerid,
				observations.instrumentid,
				observations.locationid,
				observations.description,
				observations.seeing,
				observations.limmag,
				observations.sqm,
				observations.lensid,
				observations.filterid,
				observations.eyepieceid,
				observations.magnification,
				observations.visibility,
				observations.clustertype,
				DATE_FORMAT(STR_TO_DATE( observations.date, '%Y%m%d'), '%e/%c/%Y') as date,
				observations.date as sortdate,
				observers.firstname,
				observers.name,
				IF(observations.smalldiameter = 0, '-',  CONCAT(observations.smalldiameter, ' x ', observations.largediameter)) as size,
				IF(eyepieces.id = 0, '-', CONCAT(eyepieces.name, ' (', observations.magnification, 'x)')) as eyepiecedescription,
				IF(lenses.id = 0, '-', CONCAT(lenses.name, ' (', lenses.factor, ')')) as lensdescription,
				IF(filters.id = 0, '-', filters.name) as filterdescription,
				CONCAT(observers.firstname, ' ', observers.name) as observername,
				instruments.name as instrumentname,
				instruments.diameter as instrumentdiameter,
				locations.name as locationname,
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
			WHERE observations.objectname='{$_GET['object']}'
			";
					
	$result = $objDatabase->selectRecordsetArray ($query);
	
	$dataTablesObject = new stdClass();
	
	while(list($key, $value) = each($result)){
		while(list($k, $v) = each($value)){
			if($k == "observername"){
				$result[$key]['observerimage'] = getObserverImage($v);
			}
		}
	}

	$dataTablesObject->data = $result;
	
	print json_encode($dataTablesObject);	
?>