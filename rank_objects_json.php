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

	$query = "select objectname,COUNT(*) as count from observations";
			

	if(isset($_GET['type']) && ($_GET['type'] == 'sketched')){
		$query = $query . " WHERE hasDrawing=\"1\"";
	}
		
	$query = $query . " group by objectname order by count DESC;";
	
	$result = $objDatabase->selectRecordsetArray ($query);
	
	$dataTablesObject = new stdClass();
	$dataTablesObject->data = $result;
	
	print json_encode($dataTablesObject);	
?>