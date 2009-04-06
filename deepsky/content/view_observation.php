<?php
// view_observation.php
// view information of observation 

if(!array_key_exists('observation',$_GET)||!$_GET['observation']) //  
   throw new Exception ("No observation defined in view_observation.php");
if(!($object=$GLOBALS['objObservation']->getDsObservationProperty($_GET['observation'],'objectname')))    // check if observation exists
   throw new Exception ("No observed object found in view_observation.php");
echo "<div id=\"main\">";
echo "<table width=\"100%\">";
echo "<tr>";
echo "<td>"."<h2>".LangViewObservationTitle."&nbsp;-&nbsp;".stripslashes($object)."&nbsp;-&nbsp;".LangOverviewObjectsHeader7.":&nbsp;".$objObject->getDSOseenLink($object)."</h2>"."</td>";
echo "<td align=\"right\">";
if(array_key_exists('Qobs',$_SESSION)&&count($_SESSION['Qobs'])&&array_key_exists('QobsKey',$_GET))                // array of observations
{ if($_GET['QobsKey']>0)
    echo "&nbsp;<a href=\"".$baseURL."index.php?indexAction=detail_observation&amp;observation=".$_SESSION['Qobs'][$_GET['QobsKey']-1]['observationid']."&amp;QobsKey=".($_GET['QobsKey']-1)."&amp;dalm=".$_GET['dalm']."\" title=\"".LangPreviousObservation."\">"."<img src=\"".$baseURL."styles/images/left20.gif\" border=\"0\">"."</a>&nbsp;&nbsp;";
  if($_GET['QobsKey']<(count($_SESSION['Qobs'])-1))
    echo "&nbsp;<a href=\"".$baseURL."index.php?indexAction=detail_observation&amp;observation=".$_SESSION['Qobs'][$_GET['QobsKey']+1]['observationid']."&amp;QobsKey=".($_GET['QobsKey']+1)."&amp;dalm=".$_GET['dalm']."\" title=\"".LangNextObservation."\">"."<img src=\"".$baseURL."styles/images/right20.gif\" border=\"0\">"."</a>";
}
echo "</td>";
echo "</tr>";
echo "</table>";

echo "<table width=\"100%\"><tr>";
echo "<td width=\"25%\" align=\"left\">"."<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($object)."\">".LangViewObjectViewNearbyObject." ".$object."</td>";
echo "<td width=\"25%\" align=\"center\">".($loggedUser?("<a href=\"".$baseURL."index.php?indexAction=add_observation&amp;object=" . urlencode($object) . "\">" . LangViewObjectAddObservation . $object . "</a>"):"")."</td>";
if($myList)
{ echo "<td width=\"25%\" align=\"center\">";
  if($objList->checkObjectInMyActiveList($object))
    echo "<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;removeObjectFromList=" . urlencode($object) . "\">" . $object . LangListQueryObjectsMessage3 . $_SESSION['listname'] . "</a>";
  else
    echo "<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;addObjectToList=" . urlencode($object) . "&amp;showname=" . urlencode($object) . "\">" . $object . LangListQueryObjectsMessage2 . $_SESSION['listname'] . "</a>";
 echo "</td>";
}	
echo("</tr>");
echo("</table>");
$objObject->showObject($object);
if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'])                  // LOGGED IN
{ if($_GET['dalm']!="D")
	{ echo("<a href=\"".$baseURL."index.php?indexAction=detail_observation&amp;observation=" . $_GET['observation'] . "&amp;dalm=D\" title=\"" . LangDetail . "\">");
      echo(LangDetailText); 
	  echo("</a>");
	  echo("&nbsp;");
	}
	if($_GET["dalm"]!="AO")
	{
	  echo("<a href=\"".$baseURL."index.php?indexAction=detail_observation&amp;observation=" . $_GET['observation'] . "&amp;dalm=AO\" title=\"" . LangAO . "\">");
      echo(LangAOText); 
	  echo("</a>");
	  echo("&nbsp;");
	}
	if ($GLOBALS['objObservation']->getObservationsUserObject($_SESSION['deepskylog_id'], $object)>0)
{
		if($_GET['dalm']!="MO")
	{
	  echo("<a href=\"".$baseURL."index.php?indexAction=detail_observation&amp;observation=" . $_GET['observation'] . "&amp;dalm=MO\" title=\"" . LangMO . "\">");
        echo(LangMOText); 
     echo("</a>&nbsp;");
   }
	if($_GET['dalm']!="LO")
	{
	  echo("<a href=\"".$baseURL."index.php?indexAction=detail_observation&amp;observation=" . $_GET['observation'] . "&amp;dalm=LO\" title=\"" . LangLO . "\">");
        echo(LangLOText); 
     echo("</a>&nbsp;");
    }
 }
 echo(LangOverviewObservationsHeader5a);
 echo "<hr />";
}
$objObservation->showObservation($_GET['observation']);
if($_GET['dalm']=="AO") $AOid = $GLOBALS['objObservation']->getAOObservationsId($object, $_GET['observation']);
elseif($_GET['dalm']=="MO") $AOid = $GLOBALS['objObservation']->getMOObservationsId($object, $_SESSION['deepskylog_id'], $_GET['observation']);
elseif($_GET['dalm']=="LO") $AOid = array($GLOBALS['objObservation']->getLOObservationId($object, $_SESSION['deepskylog_id'], $_GET['observation']));
else $AOid=array();
while(list($key, $LOid) = each($AOid)) 
  $objObservation->showObservation($LOid);
echo "</div>";
?>
