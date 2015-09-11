<?php 
ini_set('display_errors', '1');

header('Content-Type: application/json');

global $loggedUser;
$inIndex = true;
$language = "nl";
if (! array_key_exists ( 'indexAction', $_GET ) && array_key_exists ( 'indexAction', $_POST ))
	$_GET ['indexAction'] = $_POST ['indexAction'];
	date_default_timezone_set ( 'UTC' );

require_once 'common/entryexit/globals.php'; // Includes of all classes and assistance files
require_once 'common/entryexit/preludes.php'; // Includes of all classes and assistance files
require_once 'common/entryexit/instructions.php'; // Execution of all non-layout related instructions (login, add objects to lists, etc.)
require_once 'common/entryexit/data.php'; // Get data for the form, object data, observation data, etc.


	global $objDatabase;
	
	
	$query = "SELECT 
				observations.objectname,
				observations.observerid,
				observations.instrumentid,
				observations.locationid,
				observations.description,
				observations.date,
				observers.firstname,
				observers.name,
				instruments.name as instrumentname,
				instruments.diameter as instrumentdiameter,
				objects.con
			FROM observations
			JOIN observers ON observations.observerid = observers.id
			JOIN objects ON observations.objectname = objects.name
			JOIN instruments ON observations.instrumentid = instruments.id
			WHERE observations.objectname='M 42'
			";
	$result = $objDatabase->selectRecordsetArray ($query);
	
	$dataTablesObject = new stdClass();

	$dataTablesObject->data = $result;
	
	print json_encode($dataTablesObject);	
?>