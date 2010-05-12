<?php //ajaxinterface.php accepts ajax requests and dispatches the calls. Upon receiving teh result, an xml response is send back
$inIndex=true;
include 'common/entryexit/preludes.php';                                                                // Includes of all classes and assistance files
$ajaxInstruction=$objUtil->checkRequestKey('instruction');
//echo ($objUtil->checkRequestKey('thedata'));
if($ajaxInstruction=="getObjectsMagnitudeJSON")
  echo(json_encode($objObject->getObjectsMag($objUtil->checkGetKey('lLhr',0),$objUtil->checkGetKey('rLhr',0),$objUtil->checkGetKey('dDdeg',0),$objUtil->checkGetKey('uDdeg',0),$objUtil->checkGetKey('frommag',0),$objUtil->checkGetKey('tomag',10),$objUtil->checkGetKey('theobject'))));
elseif($ajaxInstruction=="getStarsMagnitudeJSON")
  echo(json_encode($objStar->getStarsMagnitude($objUtil->checkGetKey('lLhr',0),$objUtil->checkGetKey('rLhr',0),$objUtil->checkGetKey('dDdeg',0),$objUtil->checkGetKey('uDdeg',0),$objUtil->checkGetKey(('frommag'),0),$objUtil->checkGetKey(('tomag'),0))));
elseif($ajaxInstruction=="getConstellationBoundriesJSON")
  echo(json_encode($objConstellation->getAllBoundries()));
elseif($ajaxInstruction=="getReportLayout")
  echo(json_encode($objReportLayout->getReportAll($objUtil->checkRequestKey('reportuser'),$objUtil->checkRequestKey('reportname'),$objUtil->checkRequestKey('reportlayout'))));
elseif($ajaxInstruction=="saveReportLayout")
  echo json_encode($objReportLayout->saveLayout($objUtil->checkRequestKey('reportname'),$objUtil->checkRequestKey('reportlayout'),stripslashes($objUtil->checkRequestKey('thedata'))));
elseif($ajaxInstruction=="deleteReportLayout")
  echo json_encode($objReportLayout->deleteLayout($objUtil->checkRequestKey('reportname'),$objUtil->checkRequestKey('reportlayout')));
else
  echo "No result.";  
?>