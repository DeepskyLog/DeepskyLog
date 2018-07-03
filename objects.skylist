<?php 
// objects.skylist
// downloads a skylist (SkySafari) list of the selected objects

$inIndex = true;
require_once 'common/entryexit/preludes.php';

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"objects.skylist\"");

objects_skylist();

function objects_skylist()
{ global $objUtil;
	if(array_key_exists('SID', $_GET)&&$_GET['SID']&&array_key_exists($_GET['SID'],$_SESSION)&&$_SESSION[$_GET['SID']]) {
	    $objUtil->skylistObjects($_SESSION[$_GET['SID']]);
    }
}
?>