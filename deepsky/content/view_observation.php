<?php

// view_observation.php
// view information of observation 
// version 0.3: JV 20041228
// version 3.1, DE 20061119

// Code cleanup - removed by David on 20080704
//include_once "../lib/observers.php"; // observers table
//$observer = new Observers;
//include_once "../lib/locations.php"; // locations table
//$locations = new Locations;
//include_once "../lib/filters.php"; // filters table
//$filters = new Filters;
//include_once "../lib/eyepieces.php"; // eyepieces table
//$eyepieces = new Eyepieces;
//include_once "../common/control/ra_to_hms.php";
//include_once "../common/control/dec_to_dm.php";
//include_once "../lib/instruments.php"; // instruments table
//$instruments = new Instruments;


include_once "../lib/setup/databaseInfo.php";
include_once "../lib/observations.php"; // observation table
$observations = new Observations;
include_once "../lib/objects.php"; // objects table
$objects = new Objects;
include_once "../lib/util.php";
$util = new Util();
$util->checkUserInput();
include_once "../lib/lists.php";
$list = new Lists;
$myList = False;
if(array_key_exists('listname',$_SESSION) && $list->checkList($_SESSION['listname'])==2)
  $myList=True;


if (!function_exists('fnmatch'))
{
   function fnmatch($pattern, $string) 
	 {
      return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
   }
}
if(!$_GET['observation']) // no observation defined 
   header("Location: ../index.php");

if($observations->getObjectId($_GET['observation'])) // check if observation exists
{
  $object = $observations->getObjectId($_GET['observation']);
  echo("<div id=\"main\">\n");
	echo("<h2>" . LangViewObservationTitle);

	 
  // SEEN
  $seen = "<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($object) . "\" title=\"" . LangObjectNSeen . "\">-</a>";
  $seenDetails = $objects->getSeen($object);
  if(substr($seenDetails,0,1)=="X") // object has been seen already
  {
    $seen = "<a href=\"deepsky/index.php?indexAction=result_selected_observations&object=" . urlencode($object) . "\" title=\"" . LangObjectXSeen . "\">" . $seenDetails . "</a>";
  }
  if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
  {
    if (substr($seenDetails,0,1)=="Y") // object has been seen by the observer logged in
      $seen = "<a href=\"deepsky/index.php?indexAction=result_selected_observations&object=" . urlencode($object) . "\" title=\"" . LangObjectYSeen . "\">" . $seenDetails . "</a>";
  }
  echo ("&nbsp;-&nbsp;" . stripslashes($object));
  echo "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $seen;
  echo("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
  if(array_key_exists('observation_query', $_SESSION) && $_SESSION['observation_query']) // array of observations
  {
    $arrayIndex = array_search($_GET['observation'],$_SESSION['observation_query']);
    $previousIndex = $arrayIndex + 1;
	  if (array_key_exists($previousIndex, $_SESSION['observation_query']))
      $previousObservation = $_SESSION['observation_query'][$previousIndex];
 	  else
      $previousObservation = "";
	  $nextIndex = $arrayIndex - 1;
    if ($nextIndex < 0) 
    $nextObservation = "";
    else 
	  $nextObservation = $_SESSION['observation_query'][$nextIndex];
    if ($previousObservation != "") echo "<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $previousObservation . "&dalm=" . $_GET['dalm'] . "\" title=\"" . LangPreviousObservation . "\">&lt</a>&nbsp;&nbsp;&nbsp;";
    if ($nextObservation != "") echo "<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $nextObservation . "&dalm=" . $_GET['dalm'] . "\" title=\"" . LangNextObservation . "\">&gt;</a> ";
  }
  echo("</h2>");

	echo "<table width=\"100%\"><tr>";
	echo("<td width=\"25%\" align=\"left\">");
  echo("<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($object) . "\">" . LangViewObjectViewNearbyObject . " " . $object);
	echo("</td><td width=\"25%\" align=\"center\">");
  if (array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
    echo("<a href=\"deepsky/index.php?indexAction=add_observation&object=" . urlencode($object) . "\">" . LangViewObjectAddObservation . $object . "</a>");
	echo("</td>");
	if($myList)
	{
    echo("<td width=\"25%\" align=\"center\">");
    if($list->checkObjectInMyActiveList($object))
      echo("<a href=\"deepsky/index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;removeObjectFromList=" . urlencode($object) . "\">" . $object . LangListQueryObjectsMessage3 . $_SESSION['listname'] . "</a>");
    else
      echo("<a href=\"deepsky/index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;addObjectToList=" . urlencode($object) . "&amp;showname=" . urlencode($object) . "\">" . $object . LangListQueryObjectsMessage2 . $_SESSION['listname'] . "</a>");
	  echo("</td>");
	}	
	echo("</tr>");
	echo("</table>");

  $objects->showObject($object);
  if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'])                  // LOGGED IN
  {
  	if($_GET['dalm']!="D")
  	{
  		echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $_GET['observation'] . "&dalm=D\" title=\"" . LangDetail . "\">");
        echo(LangDetailText); 
  	  echo("</a>");
  	  echo("&nbsp;");
  	}
  	if($_GET["dalm"]!="AO")
  	{
  	  echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $_GET['observation'] . "&dalm=AO\" title=\"" . LangAO . "\">");
        echo(LangAOText); 
  	  echo("</a>");
  	  echo("&nbsp;");
  	}
 		if ($observations->getObservationsUserObject($_SESSION['deepskylog_id'], $object)>0)
		{
 			if($_GET['dalm']!="MO")
			{
			  echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $_GET['observation'] . "&dalm=MO\" title=\"" . LangMO . "\">");
          echo(LangMOText); 
	      echo("</a>&nbsp;");
	    }
			if($_GET['dalm']!="LO")
			{
			  echo("<a href=\"deepsky/index.php?indexAction=detail_observation&observation=" . $_GET['observation'] . "&dalm=LO\" title=\"" . LangLO . "\">");
          echo(LangLOText); 
	      echo("</a>&nbsp;");
      }
	  }
	  echo(LangOverviewObservationsHeader5a);
	  echo "<hr>";
  }
	
  $observations->showObservation($_GET['observation']);
	
  if($_GET['dalm']=="AO") $AOid = $observations->getAOObservationsId($object, $_GET['observation']);
  elseif($_GET['dalm']=="MO") $AOid = $observations->getMOObservationsId($object, $_SESSION['deepskylog_id'], $_GET['observation']);
  elseif($_GET['dalm']=="LO") $AOid = $observations->getLOObservationId($object, $_SESSION['deepskylog_id'], $_GET['observation']);
	else $AOid=array();
	while(list($key, $LOid) = each($AOid)) 
	  $observations->showObservation($LOid);
}
echo("</div></body></html>");

?>
