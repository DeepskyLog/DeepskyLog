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
elseif($ajaxInstruction=="getReportLayouts")
  echo(json_encode($objReportLayout->getLayoutListJavascript($objUtil->checkRequestKey('reportname'))));
elseif($ajaxInstruction=="saveReportLayout")
  echo json_encode($objReportLayout->saveLayout($objUtil->checkRequestKey('reportname'),$objUtil->checkRequestKey('reportlayout'),stripslashes($objUtil->checkRequestKey('thedata'))));
elseif($ajaxInstruction=="deleteReportLayout")
  echo json_encode($objReportLayout->deleteLayout($objUtil->checkRequestKey('reportname'),$objUtil->checkRequestKey('reportlayout')));
elseif($ajaxInstruction=="getObserverQueries")
  echo json_encode($objDatabase->selectRecordsetArray("SELECT * FROM observerqueries WHERE observerid=\"".$loggedUser."\" AND observerquerytype=\"".$objUtil->checkGetKey('observerquerytype')."\";"));
elseif($ajaxInstruction=="removeObserverQuery")
{ $sql="DELETE FROM observerqueries WHERE (observerid=\"".$loggedUser."\" AND 
                                     observerquerytype=\"".$objUtil->checkGetKey('observerquerytype')."\" AND
                                     observerqueryname=\"".$objUtil->checkGetKey('observerqueryname')."\");";
  $objDatabase->execSQL($sql);
	echo json_encode($objDatabase->selectRecordsetArray("SELECT * FROM observerqueries WHERE observerid=\"".$loggedUser."\" AND observerquerytype=\"".$objUtil->checkGetKey('observerquerytype')."\";"));
}
elseif($ajaxInstruction=="saveObserverQuery")
{ reset($_GET);
  $temp="";
  while(list($key,$value)=each($_GET))
    $temp=$temp.$key."=".$value."&";
  $sql="INSERT INTO observerqueries(observerid,observerquerytype,observerqueryname,observerquery) 
                             VALUES (\"".$loggedUser."\", 
                                     \"".$objUtil->checkGetKey('observerquerytype')."\",
                                     \"".$objUtil->checkGetKey('observerqueryname')."\",
                                     \"".($temp)."\");";
  $objDatabase->execSQL($sql);
	echo json_encode($objDatabase->selectRecordsetArray("SELECT * FROM observerqueries WHERE observerid=\"".$loggedUser."\" AND observerquerytype=\"".$objUtil->checkGetKey('observerquerytype')."\";"));
}
else
  echo "No result.";  
?>