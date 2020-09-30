<?php 
// observations.csv
// exports a cxv file containing the selected observations

$inIndex = true;
require_once 'common/entryexit/preludes.php';

header ("Content-Type: application/vnd.ms-excel");
header ("Content-Disposition: attachment; filename=\"observations.csv\"");

observations_csv();

function observations_csv()
{ global $objUtil;
  if(array_key_exists('Qobs',$_SESSION)&&$_SESSION['Qobs'])
    $objUtil->csvObservations($_SESSION['Qobs']);
}
?>
