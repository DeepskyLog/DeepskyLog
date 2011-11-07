<?php 
// observations.pgf
// prints a pdf with the selected observations

$inIndex = true;
require_once 'common/entryexit/preludes.php';

observations_pdf();

function observations_pdf()
{ global $filename, $objUtil;
	$filename=str_replace(' ','_',html_entity_decode($objUtil->checkRequestKey('pdfTitle','Deepskylog_Observations')));
	if(array_key_exists('Qobs',$_SESSION)&&$_SESSION['Qobs'])
	  $objUtil->pdfObservations($_SESSION['Qobs']);
}
?>