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

	global $objDatabase, $objObject;

	$query = "SELECT objectname, COUNT(*) as count FROM observations";
			
	if(isset($_GET['type']) && ($_GET['type'] == 'sketched')){
		$query = $query . " WHERE hasDrawing=\"1\"";
	}
		
	$query = $query . " GROUP BY objectname ORDER BY count DESC;";
	
	$result = $objDatabase->selectRecordsetArray ($query);
	
	$dataTablesObject = new stdClass();
	$dataTablesObject->data = $result;
	
	print json_encode($dataTablesObject);	
?>