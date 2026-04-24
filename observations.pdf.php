<?php 
// observations.pgf
// prints a pdf with the selected observations

$inIndex = true;
require_once 'common/entryexit/preludes.php';

observations_pdf();

function get_export_observations()
{
	global $objObservation, $objUtil;
	if (array_key_exists('QobsParams', $_SESSION) && is_array($_SESSION['QobsParams'])) {
		$queries = $_SESSION['QobsParams'];
		unset($queries['offset'], $queries['limit'], $queries['countquery']);
		$queries['lightweight'] = 1;
		$seen = array_key_exists('seen', $queries) ? $queries['seen'] : $objUtil->checkGetKey('seen', 'A');
		$exact = array_key_exists('exactinstrumentlocation', $queries)
			? (bool)$queries['exactinstrumentlocation']
			: (bool)$objUtil->checkGetKey('exactinstrumentlocation', 0);
		$result = $objObservation->getObservationFromQuery($queries, $seen, $exact);
		if (is_array($result)) {
			return $result;
		}
	}
	return (array_key_exists('Qobs', $_SESSION) && $_SESSION['Qobs']) ? $_SESSION['Qobs'] : array();
}

function observations_pdf()
{ global $filename, $objUtil;
	$filename=str_replace(' ','_',html_entity_decode($objUtil->checkRequestKey('pdfTitle','Deepskylog_Observations')));
	$exportRows = get_export_observations();
	if ($exportRows) {
	  $objUtil->pdfObservations($exportRows);
	}
}
?>