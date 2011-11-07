<?php 
$filename='cometobservations';
$inIndex = true;
include 'common/entryexit/preludes.php';
$_SESSION['module'] = "comets";
$_SESSION['pdfname'] = "observations"; // necessary for class.pdf.php
$result = $_SESSION['observation_query'];

if (!empty($result))
{ $objUtil->pdfCometObservations($result);
}
?>