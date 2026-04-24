<?php 
// observations.csv
// exports a cxv file containing the selected observations

$inIndex = true;
require_once 'common/entryexit/preludes.php';

header ("Content-Type: application/vnd.ms-excel");
header ("Content-Disposition: attachment; filename=\"observations.csv\"");

observations_csv();

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

function observations_csv()
{ global $objUtil;
  $exportRows = get_export_observations();
  if ($exportRows) {
    $objUtil->csvObservations($exportRows);
  }
}
?>
