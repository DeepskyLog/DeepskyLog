<?php
require_once 'lib/cometobservations.php';
require_once 'lib/observations.php';
require_once 'lib/setup/databaseInfo.php';
require_once 'lib/database.php';

global $objCometObservation;
global $objDeepskyObservation;
global $objDatabase;

$objDeepskyObservation=new Observations;
$objCometObservation=new CometObservations;
$objDatabase=new Database;

function cometMaxObservationId() {
	global $objCometObservation;
	return $objCometObservation->getMaxObservation();
}

function deepskyMaxObservationId() {
	global $objDeepskyObservation;
	return $objDeepskyObservation->getMaxObservation();
}

function cometObservationFromId($fromId) {
	global $objDatabase;
	$sql = "SELECT DISTINCT cometobservations.id 				  AS cometObservationId,
 									        cometobjects.name  					  AS cometObjectName,
													cometobservations.date 			  AS cometObservationDate,
													cometobservations.description AS cometObservationDescription,
													CONCAT(observers.firstname,' ',observers.name)
																									 AS observerName,
													instruments.name 				 AS instrumentName
					 FROM		 cometobservations
					 JOIN 	 cometobjects ON cometobservations.objectid=cometobjects.id
					 JOIN 	 instruments ON cometobservations.instrumentid=instruments.id
					 JOIN 	 observers ON cometobservations.observerid=observers.id
				   WHERE ((cometobservations.id=".$fromId."));";
	return json_encode($objDatabase->selectRecordsetArrayNoQuotes($sql));
}

function deepskyObservationDetailsFromId($fromId) {
	global $objDatabase;
	$sql = "SELECT DISTINCT observations.id 				 AS deepskyObservationId,
 									        observations.objectname  AS deepskyObjectName,
													observations.date 			 AS deepskyObservationDate,
													observations.time 			 AS deepskyObservationTime,
													observations.description AS deepskyObservationDescription,
													CONCAT(observers.firstname,' ',observers.name) 
																									 AS observerName,
													locations.name 				   AS locationName,
													instruments.name 				 AS instrumentName,
													filters.name 				 		 AS filterName,
													lenses.name 				 		 AS lensName,
													eyepieces.name 				 	 AS eyepieceName,
													observations.seeing			 AS seeing,
													observations.limmag			 AS limitingMagnitude,
													observations.visibility	 AS visibility,
													observations.SQM				 AS SQM,
													observations.hasDrawing	 AS hasDrawing
			FROM		 observations
			 JOIN 	 lenses		 		ON observations.lensid=lenses.id
			 JOIN 	 eyepieces 		ON observations.eyepieceid=eyepieces.id
			 JOIN 	 filters 			ON observations.filterid=filters.id
			 JOIN 	 instruments 	ON observations.instrumentid=instruments.id
			 JOIN 	 locations 		ON observations.locationid=locations.id
			 JOIN 	 observers 		ON observations.observerid=observers.id
		  WHERE ((observations.id=".$fromId."));";
//	return $sql;
	return json_encode($objDatabase->selectRecordsetArrayNoQuotes($sql));
}

function deepskyObservationFromId($fromId) {
	global $objDatabase;
	$sql = "SELECT DISTINCT observations.id 				 AS deepskyObservationId,
 									        observations.objectname  AS deepskyObjectName,
													observations.date 			 AS deepskyObservationDate,
													observations.description AS deepskyObservationDescription,
													CONCAT(observers.firstname,' ',observers.name) 
																									 AS observerName,
													instruments.name 				 AS instrumentName
					 FROM		 observations
					 JOIN 	 instruments ON observations.instrumentid=instruments.id
					 JOIN 	 observers ON observations.observerid=observers.id
				   WHERE ((observations.id=".$fromId."));";
	return json_encode($objDatabase->selectRecordsetArrayNoQuotes($sql));
}

function cometObservationsListFromIdToId($fromId,$toId) {
	global $objDatabase;
	$sql="SELECT DISTINCT cometobservations.id 				AS cometObservationId,
 								        cometobjects.name 					AS cometObjectName,
												cometobservations.date 			AS cometObservationDate,
												CONCAT(observers.firstname , ' ' , observers.name) 
																								AS observerName
				FROM cometobservations
				JOIN cometobjects ON cometobservations.objectid=cometobjects.id
				JOIN observers ON cometobservations.observerid=observers.id
				WHERE ((cometobservations.id>=".$fromId.")AND(cometobservations.id<=".$toId."));";
	return json_encode($objDatabase->selectRecordsetArrayNoQuotes($sql));
}

function deepskyObservationsListFromIdToId($fromId,$toId) {
	global $objDatabase;
	$sql="SELECT DISTINCT observations.id 				AS deepskyObservationId,
 								        observations.objectname AS deepskyObjectName,
												observations.date 			AS deepskyObservationDate,
												CONCAT(observers.firstname , ' ' , observers.name) 
																								AS observerName
				FROM 		observations
				JOIN 		observers on observations.observerid=observers.id
				WHERE ((observations.id>=".$fromId.")AND(observations.id<=".$toId."));";
	return json_encode($objDatabase->selectRecordsetArrayNoQuotes($sql));
}

function cometObservationsListFromDateToDate($fromDate,$toDate) {
	global $objDatabase;
	$sql="SELECT DISTINCT cometobservations.id 				AS cometObservationId,
 								        cometobjects.name 					AS cometObjectName,
												cometobservations.date 			AS cometObservationDate,
												CONCAT(observers.firstname , ' ' , observers.name) 
																								AS observerName
				FROM cometobservations
				JOIN cometobjects ON cometobservations.objectid=cometobjects.id
				JOIN observers ON cometobservations.observerid=observers.id
				WHERE ((cometobservations.date>=".$fromDate.")AND(cometobservations.date<=".$toDate."));";
	return json_encode($objDatabase->selectRecordsetArrayNoQuotes($sql));
}

function deepskyObservationsListFromDateToDate($fromDate,$toDate) {
	global $objDatabase;
	$sql="SELECT DISTINCT observations.id 				AS deepskyObservationId,
 								        observations.objectname AS deepskyObjectName,
												observations.date 			AS deepskyObservationDate,
												CONCAT(observers.firstname , ' ' , observers.name) 
																								AS observerName
				FROM 		observations
				JOIN 		observers on observations.observerid=observers.id
				WHERE ((observations.date>=".$fromDate.")AND(observations.date<=".$toDate."));";
	return json_encode($objDatabase->selectRecordsetArrayNoQuotes($sql));
}

function cometObservationsListDaysFromIdToId($fromId,$toId) {
	global $objDatabase;
	$sql="SELECT COUNT(cometobservations.id)  AS cometObservationsListDateCount,
			 							 cometobservations.date AS cometObservationsListDate
				FROM      	 cometobservations
				WHERE      ((cometobservations.id>=".$fromId.")AND(cometobservations.id<=".$toId."))
				GROUP BY     cometobservations.date;";
	return json_encode($objDatabase->selectRecordsetArrayNoQuotes($sql));
}

function deepskyObservationsListDaysFromIdToId($fromId,$toId) {
	global $objDatabase;
	$sql="SELECT COUNT(observations.id)  AS deepskyObservationsListDateCount,
			 							 observations.date AS deepskyObservationsListDate
				FROM      	 observations
				WHERE      ((observations.id>=".$fromId.")AND(observations.id<=".$toId."))
				GROUP BY     observations.date;";
	return json_encode($objDatabase->selectRecordsetArrayNoQuotes($sql));
}

function cometObservationsListDaysFromDateToDate($fromDate,$toDate) {
	global $objDatabase;
	$sql="SELECT COUNT(cometobservations.id)  AS cometObservationsListDateCount,
				 						 cometobservations.date AS cometObservationsListDate
				FROM      	 cometobservations
				WHERE      ((cometobservations.date>=\"".$fromDate."\")AND(cometobservations.date<=\"".$toDate."\"))
				GROUP BY     cometobservations.date;";
	return json_encode($objDatabase->selectRecordsetArrayNoQuotes($sql));
}

function deepskyObservationsListDaysFromDateToDate($fromDate,$toDate) {
	global $objDatabase;
	$sql="SELECT COUNT(observations.id)  AS deepskyObservationsListDateCount,
				 						 observations.date AS deepskyObservationsListDate
				FROM      	 observations
				WHERE      ((observations.date>=\"".$fromDate."\")AND(observations.date<=\"".$toDate."\"))
				GROUP BY     observations.date;";
	return json_encode($objDatabase->selectRecordsetArrayNoQuotes($sql));
}
















//function observationsquery() {
//	require_once 'deepsky/data/data_get_observations.php';
//	data_get_observations();
//	return json_encode($_SESSION['Qobs']);
//}

/*
 function newobservationcountfromid($fromid) {
	global $objDeepskyObservation;
	return strval(($objDeepskyObservation->getMaxObservation())-$fromid);
}
function newobservationcountsince($since) {
	global $objDatabase; 
	return $objDatabase->selectSingleValue('SELECT COUNT(observations.id) as Cnt FROM observations WHERE (date>'.$since.')','Cnt',0);
}
function observationsfromto($fromid,$toid) {
	global $objDatabase;
	$sql = "SELECT DISTINCT observations.id as observationid,
	observations.objectname as objectname,
	observations.date as observationdate,
	observations.description as observationdescription,
	CONCAT(observers.firstname , ' ' , observers.name) as observername,
	instruments.name as instrumentname
	FROM observations
	JOIN instruments on observations.instrumentid=instruments.id
	JOIN observers on observations.observerid=observers.id
	WHERE ((observations.id>=".$fromid.") AND (observations.id<=".$toid."));";
	return json_encode($objDatabase->selectRecordsetArrayNoQuotes($sql));
}
function observationdetails($observationid) {
	global $objDatabase;
	$sql = "SELECT DISTINCT observations.id as observationid,
										       observations.objectname as objectname,
													 observations.date as observationdate,
													 observations.description as observationdescription,
							  					 observers.id as observerid,
													 CONCAT(observers.firstname , ' ' , observers.name) as observername,
							  					 CONCAT(observers.name , ' ' , observers.firstname) as observersortname,
													 objects.con as objectconstellation,
													 objects.type as objecttype,
													 objects.mag as objectmagnitude,
													 objects.subr as objectsurfacebrigthness,
													 instruments.id as instrumentid,
													 instruments.name as instrumentname,
													 instruments.diameter as instrumentdiameter,
							  					 CONCAT(10000+instruments.diameter,' mm ',instruments.name) as instrumentsort
					 FROM observations
					 JOIN instruments on observations.instrumentid=instruments.id
					 JOIN objects on observations.objectname=objects.name
					 JOIN locations on observations.locationid=locations.id
					 JOIN objectnames on observations.objectname=objectnames.objectname
					 JOIN observers on observations.observerid=observers.id
				   WHERE (observations.id=".$observationid.");";
	return html_entity_decode(json_encode(html_entity_decode($objDatabase->selectRecordsetArrayNoQuotes($sql))));
}
*/
?>