<?php 
// observations.skylist
// exports a skylist file (SkySafari observation list) containing the selected observations

$inIndex = true;
require_once 'common/entryexit/preludes.php';

header ("Content-Type: application/octet-stream");
header ("Content-Disposition: attachment; filename=\"observations.skylist\"");

observations_skylist();

function observations_skylist()
{ global $objUtil;
  if(array_key_exists('Qobs',$_SESSION)&&$_SESSION['Qobs'])
    $objUtil->skylistObservations($_SESSION['Qobs']);
}
?>
