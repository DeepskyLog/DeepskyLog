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

elseif($ajaxInstruction=="seteyepieceactivation")
{ $theset='';
  if($objEyepiece->getEyepiecePropertyFromId($objUtil->checkGetKey('id',0),'observer',-1)==$loggedUser)
  { $theset=($objUtil->checkGetKey('eyepieceactive',true)=='true'?1:0);
  	$objEyepiece->setEyepieceProperty($objUtil->checkGetKey('id',-1),'eyepieceactive',$theset);
  }
  echo $theset;
}

elseif($ajaxInstruction=="setlocationactivation")
{ $theset='';
  if($objLocation->getLocationPropertyFromId($objUtil->checkGetKey('id',0),'observer',-1)==$loggedUser)
  { $theset=($objUtil->checkGetKey('locationactive',true)=='true'?1:0);
  	$objLocation->setLocationProperty($objUtil->checkGetKey('id',-1),'locationactive',$theset);
  }
  echo $theset;
}

elseif($ajaxInstruction=="getReportLayout")
  echo(json_encode($objReportLayout->getReportAll($objUtil->checkRequestKey('reportuser'),$objUtil->checkRequestKey('reportname'),$objUtil->checkRequestKey('reportlayout'))));
elseif($ajaxInstruction=="getReportLayouts")
  echo(json_encode($objReportLayout->getLayoutListJavascript($objUtil->checkRequestKey('reportname'))));
elseif($ajaxInstruction=="saveReportLayout")
  echo json_encode($objReportLayout->saveLayout($objUtil->checkRequestKey('reportname'),$objUtil->checkRequestKey('reportlayout'),stripslashes($objUtil->checkRequestKey('thedata'))));
elseif($ajaxInstruction=="deleteReportLayout")
  echo json_encode($objReportLayout->deleteLayout($objUtil->checkRequestKey('reportname'),$objUtil->checkRequestKey('reportlayout')));

elseif($ajaxInstruction=="getObserverQueries")
  echo json_encode($objObserverQueries->getObserverQueries($objUtil->checkGetKey('observerquerytype')));
elseif($ajaxInstruction=="removeObserverQuery")
  echo json_encode($objObserverQueries->removeObserverQuery($objUtil->checkGetKey('observerquerytype'),$objUtil->checkGetKey('observerqueryname')));
elseif($ajaxInstruction=="saveObserverQuery")
  echo json_encode($objObserverQueries->saveObserverQuery($objUtil->checkGetKey('observerquerytype'),$objUtil->checkGetKey('observerqueryname')));
  
elseif($ajaxInstruction=="allonepass")
{ $filename=$_SESSION['Qobj'][$objUtil->checkGetKey('item')]['objectname'];
	$theSet=array();
	$theParam=$objUtil->checkGetKey('theSet');
	while($thepos=strpos($theParam,' '))
	{ $theSet[]=substr($theParam,0,$thepos);
	  $theParam=substr($theParam,$thepos+1);
	}
	$theSet[]=$theParam;
	$thedsos=array();
	$theParam=$objUtil->checkGetKey('thedsos');
	while($thepos=strpos($theParam,' '))
	{ $thedsos[]=substr($theParam,0,$thepos);
	  $theParam=substr($theParam,$thepos+1);
	}
	$thedsos[]=$theParam;
	$thestars=array();
	$theParam=$objUtil->checkGetKey('thestars');
	while($thepos=strpos($theParam,' '))
	{ $thestars[]=substr($theParam,0,$thepos);
	  $theParam=substr($theParam,$thepos+1);
	}
	$thestars[]=$theParam;
	$thephotos=array();
	$theParam=$objUtil->checkGetKey('thephotos');
	while($thepos=strpos($theParam,' '))
	{ $thephotos[]=substr($theParam,0,$thepos);
	  $theParam=substr($theParam,$thepos+1);
	}
	$thephotos[]=$theParam;
	$datapage=$objUtil->checkGetKey('datapage');
	$ephemerides=$objUtil->checkGetKey('ephemerides');
	$yearephemerides=$objUtil->checkGetKey('yearephemerides');
	$reportlayoutselect=$objUtil->checkGetKey('reportlayoutselect');
  echo $objPrintAtlas->pdfAtlasObjectSets($objUtil->checkgetkey('item'),$theSet, $thedsos,$thestars, $thephotos,$datapage,$reportlayoutselect,$ephemerides,$yearephemerides);
}
else
  echo "No result.";
?>