<?php 
$filename='cometobservations';
$inIndex = true;
include 'common/entryexit/preludes.php';
$_SESSION['module'] = "comets";
$_SESSION['pdfname'] = "observations"; // necessary for class.pdf.php
$result = (isset($objUtil) && method_exists($objUtil, 'checkSessionKey')) ? $objUtil->checkSessionKey('observation_query', array()) : (isset($_SESSION['observation_query']) ? $_SESSION['observation_query'] : array());

if (!empty($result))
{ $objUtil->pdfCometObservations($result);
}
?>