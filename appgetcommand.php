<?php
// DEVELOP: TO DO - check get parameters for hacking
function appgetcommand() {
	if((!array_key_exists('userName',$_GET))||(!array_key_exists('password',$_GET))||($_GET['userName']=="")||($_GET['password']==""))
		 checkUserToSession("","");
	else
		checkusertosession($_GET['userName'],$_GET['password']);
	if((!array_key_exists('command',$_GET))||($_GET['command']=="")) 
	  return packResult("no command","");
	$command=$_GET['command'];
	if($command=="checkUser") {
		if((!array_key_exists('userName',$_GET))||(!array_key_exists('password',$_GET))||($_GET['userName']=="")||($_GET['password']==""))
		  return packResult($command,"invalid credentials");
		else {
			return packResult($command,checkUser($_GET['userName'],$_GET['password']));
		}
	}	
	if($command=="alive")
	  return packResult($command,"alive");
	if($command=="deepskyObservationMaxId") {
			require_once "appgetcommands/appobservations.php";
			return packResult($command,deepskyMaxObservationId());
	}
	if($command=="cometObservationMaxId") {
			require_once "appgetcommands/appobservations.php";
			return packResult($command,cometMaxObservationId());
	}
	if($command=="deepskyObservationFromId") {
		if((array_key_exists('fromId',$_GET))&&($_GET['fromId']!="")) {
			require_once "appgetcommands/appobservations.php";
			return "<deepskyObservationId>".$_GET['fromId']."</deepskyObservationId>".
						 packResult($command,deepskyObservationFromId($_GET['fromId']));
		}
	}
	if($command=="deepskyObservationDetailsFromId") {
		if((array_key_exists('fromId',$_GET))&&($_GET['fromId']!="")) {
			require_once "appgetcommands/appobservations.php";
			return "<deepskyObservationId>".$_GET['fromId']."</deepskyObservationId>".
						 packResult($command,deepskyObservationDetailsFromId($_GET['fromId']));
		}
	}
	if($command=="cometObservationFromId") {
		if((array_key_exists('fromId',$_GET))&&($_GET['fromId']!="")) {
			require_once "appgetcommands/appobservations.php";
			return "<cometObservationId>".$_GET['fromId']."</cometObservationId>".
						 packResult($command,cometObservationFromId($_GET['fromId']));
		}
	}
	if($command=="cometObservationsListFromIdToId") {
		if((array_key_exists('fromId',$_GET))&&($_GET['fromId']!="")&&(array_key_exists('toId',$_GET))&&($_GET['toId']!="")) {
			require_once "appgetcommands/appobservations.php";
			return "<fromId>".$_GET['fromId']."</fromDate>".
						 "<toId>".$_GET['toId']."</toDate>".
						 packResult($command,cometObservationsListFromIdToId($_GET['fromId'],$_GET['toId']));
		}
	}
	if($command=="deepskyObservationsListFromIdToId") {
		if((array_key_exists('fromId',$_GET))&&($_GET['fromId']!="")&&(array_key_exists('toId',$_GET))&&($_GET['toId']!="")) {
			require_once "appgetcommands/appobservations.php";
			return "<fromId>".$_GET['fromId']."</fromDate>".
						 "<toId>".$_GET['toId']."</toDate>".
						 packResult($command,deepskyObservationsListFromIdToId($_GET['fromId'],$_GET['toId']));
		}
	}
	if($command=="cometObservationsListFromDateToDate") {
		if((array_key_exists('fromDate',$_GET))&&($_GET['fromDate']!="")&&(array_key_exists('toDate',$_GET))&&($_GET['toDate']!="")) {
			require_once "appgetcommands/appobservations.php";
			return "<fromDate>".$_GET['fromDate']."</fromDate>".
						 "<toDate>".$_GET['toDate']."</toDate>".
						 packResult($command,cometObservationsListFromDateToDate($_GET['fromDate'],$_GET['toDate']));
		}
	}
	if($command=="deepskyObservationsListFromDateToDate") {
		if((array_key_exists('fromDate',$_GET))&&($_GET['fromDate']!="")&&(array_key_exists('toDate',$_GET))&&($_GET['toDate']!="")) {
			require_once "appgetcommands/appobservations.php";
			return "<fromDate>".$_GET['fromDate']."</fromDate>".
						 "<toDate>".$_GET['toDate']."</toDate>".
						 packResult($command,deepskyObservationsListFromDateToDate($_GET['fromDate'],$_GET['toDate']));
		}
	}
	if($command=="cometObservationsListDaysFromIdToId") {
		if((array_key_exists('fromId',$_GET))&&($_GET['fromId']!="")&&(array_key_exists('toId',$_GET))&&($_GET['toId']!="")) {
			require_once "appgetcommands/appobservations.php";
			return "<fromId>".$_GET['fromId']."</fromDate>".
						 "<toId>".$_GET['toId']."</toDate>".
						 packResult($command,cometObservationsListDaysFromIdToId($_GET['fromId'],$_GET['toId']));
		}
	}
	if($command=="deepskyObservationsListDaysFromIdToId") {
		if((array_key_exists('fromId',$_GET))&&($_GET['fromId']!="")&&(array_key_exists('toId',$_GET))&&($_GET['toId']!="")) {
			require_once "appgetcommands/appobservations.php";
			return "<fromId>".$_GET['fromId']."</fromDate>".
						 "<toId>".$_GET['toId']."</toDate>".
						 packResult($command,deepskyObservationsListDaysFromIdToId($_GET['fromId'],$_GET['toId']));
		}
	}
	if($command=="cometObservationsListDaysFromDateToDate") {
		if((array_key_exists('fromDate',$_GET))&&($_GET['fromDate']!="")&&(array_key_exists('toDate',$_GET))&&($_GET['toDate']!="")) {
			require_once "appgetcommands/appobservations.php";
			return "<fromDate>".$_GET['fromDate']."</fromDate>".
						 "<toDate>".$_GET['toDate']."</toDate>".
						 packResult($command,cometObservationsListDaysFromDateToDate($_GET['fromDate'],$_GET['toDate']));
		}
	}
	if($command=="deepskyObservationsListDaysFromDateToDate") {
		if((array_key_exists('fromDate',$_GET))&&($_GET['fromDate']!="")&&(array_key_exists('toDate',$_GET))&&($_GET['toDate']!="")) {
			require_once "appgetcommands/appobservations.php";
			return "<fromDate>".$_GET['fromDate']."</fromDate>".
					"<toDate>".$_GET['toDate']."</toDate>".
					packResult($command,deepskyObservationsListDaysFromDateToDate($_GET['fromDate'],$_GET['toDate']));
		}
	}
	
	
	
	

//	if($command=="deepskyObservationsQuery") {
//			require_once "appgetcommands/appobservations.php";
//			return packResult($command,observationsquery());
//	}
//	if($command=="deepskyObservationsFromTo") {
//			if((array_key_exists('from',$_GET))&&($_GET['from']!="")&&
//		     (array_key_exists('to',$_GET))&&($_GET['to']!="")) {
//			require_once "appgetcommands/appobservations.php";
//			return packResult($command,observationsfromto($_GET['from'],$_GET['to']));
//		}
//	}

//	if($command=="deepskyObservationDetails") {
//			if((array_key_exists('observationid',$_GET))&&($_GET['observationid']!="")) {
//			require_once "appgetcommands/appobservations.php";
//			return packResult($command,observationdetails($_GET['observationid']));
//		}
//	}
/*	if($command=="newDeepskyObservationCount") {
		if((array_key_exists('since',$_GET))&&($_GET['since']!="")) {
			require_once "appgetcommands/appobservations.php";
			return packResult($command,newobservationcountsince($_GET['since']));
		}
		else if ((array_key_exists('fromid',$_GET))&&($_GET['fromid']!="")){
			require_once "appgetcommands/appobservations.php";
			return packResult($command,newobservationcountfromid($_GET['fromid']));
		}
		else 
		  return packResult($command,"invalid observation count parameters");
	}	
*/
	return packResult("unknown command",$command);
}

function packResult($thecommand,$theresult) {
	$theresult="<command>".$thecommand."</command>".
						 "<result>".$theresult."</result>";
	if((array_key_exists('onResultClass',$_GET))&&($_GET['onResultClass']!=""))
		$theresult="<onResultClass>".$_GET['onResultClass']."</onResultClass>".$theresult;
	if((array_key_exists('onResultMethod',$_GET))&&($_GET['onResultMethod']!=""))
		$theresult="<onResultMethod>".$_GET['onResultMethod']."</onResultMethod>".$theresult;
	return $theresult;
}

$inIndex=true;
$language="NL";
require_once 'common/entryexit/globals.php';                                                                // Includes of all classes and assistance files
require_once 'common/entryexit/preludes.php';                                                                // Includes of all classes and assistance files
require_once "appgetcommands/appuser.php";
echo appgetcommand();

?>