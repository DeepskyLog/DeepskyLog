<?php 
// observations.xml
// exports an xml file containing the selected observations

$inIndex = true;
require_once 'common/entryexit/preludes.php';

header ("Content-Type: text/xml");
header ("Content-Disposition: attachment; filename=\"observations.xml\"");

observation_xml();

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

function observation_xml()
{ global $objUtil;
  $exportRows = get_export_observations();
  if ($exportRows) {
    $objUtil->comastObservations($exportRows);
  }
}
?>
