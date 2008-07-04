<?php

// show_image.php
// shows the DSS image of an object 
// Version 0.3: 2004/12/26, JV

//Code cleanup - removed by David on 20080704
//include_once "../lib/observations.php";
//include_once "../lib/contrast.php";
//$contrastObj = new Contrast;
//include_once "../lib/instruments.php";
//$instrumentObj = new Instruments;
//include_once "../lib/locations.php";
//$locations = new Locations;
//include_once "../lib/observers.php";
//$observer = new Observers;



include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();
include_once "../lib/objects.php";
include_once "../lib/setup/language.php";
include_once "../lib/util.php";
include_once "../lib/lists.php";

$util = new Util();
$util->checkUserInput();

$objects = new Objects; 
$list = new Lists;

$_GET['object'] = $_POST['name'];

if(array_key_exists('listname',$_SESSION) && ($list->checkList($_SESSION['listname'])==2)) $myList=True; else $myList = False;
if(array_key_exists('min',$_GET) && $_GET['min']) $min=$_GET['min']; else $min=0;
if(array_key_exists('SID',$_GET) && $_GET['SID']) $_SID=$_GET['SID']; else	$_SID=time();

if(!$_GET['object']) // no object defined in url 
{
  header("Location: ../index.php");
}
if($objects->getRa($objects->getDsObjectName($_GET['object'])) != "") // check whether object exists
{
  if(array_key_exists('addObjectToList',$_GET) && $_GET['addObjectToList'] && $myList)
  {
  	$list->addObjectToList($_GET['addObjectToList'], $_GET['showname']);
    echo "The object <a href=\"deepsky/index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['addObjectToList']) . "\">" . $_GET['showname'] . "</a> is added to the list <a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">" . $_SESSION['listname'] . "</a>.";
  	echo "<HR>";
  }
  if(array_key_exists('removeObjectFromList',$_GET) && $_GET['removeObjectFromList'] && $myList)
  {
  	$list->removeObjectFromList($_GET['removeObjectFromList']);
    echo "The object <a href=\"deepsky/index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['removeObjectFromList']) . "\">" . $_GET['removeObjectFromList'] . "</a> is removed from the list <a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">" . $_SESSION['listname'] . "</a>.";
  	echo "<HR>";
  }
	if(array_key_exists('addAllObjectsFromPageToList',$_GET) && $_GET['addAllObjectsFromPageToList'] && $myList)
  {
	  $count=0;
  	while(($count<($min+25)) && ($count<count($_SESSION['QO'][$_SID])))
	  {
		  $list->addObjectToList($_SESSION['QO'][$_SID][$count][0],$_SESSION['QO'][$_SID][$count][4]);
		  $count++;
    }
	echo "The objects have been added to the list <a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">" .  $_SESSION['listname'] . "</a>.";
	echo "<HR>";
  }

	
	
  // SEEN
  $seen = "<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($_GET['object']) . "\" title=\"" . LangObjectNSeen . "\">-</a>";
  $seenDetails = $objects->getSeen($_GET['object']);
  if(substr($seenDetails,0,1)=="X") // object has been seen already
  {
    $seen = "<a href=\"deepsky/index.php?indexAction=result_selected_observations&object=" . urlencode($_GET['object']) . "\" title=\"" . LangObjectXSeen . "\">" . $seenDetails . "</a>";
  }
  if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
  {
    if (substr($seenDetails,0,1)=="Y") // object has been seen by the observer logged in
      $seen = "<a href=\"deepsky/index.php?indexAction=result_selected_observations&object=" . urlencode($_GET['object']) . "\" title=\"" . LangObjectYSeen . "\">" . $seenDetails . "</a>";
  }

  echo("<div id=\"main\"><h2>");
  echo (LangViewObjectTitle . "&nbsp;-&nbsp;" . stripslashes($_GET['object']));
  echo "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $seen;
  echo("</h2>");
	echo "<table width=\"100%\"><tr>";
	echo("<td width=\"25%\" align=\"left\">");
	if($seen!="<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($_GET['object']) . "\" title=\"" . LangObjectNSeen . "\">-</a>")
	  echo("<a href=\"deepsky/index.php?indexAction=result_selected_observations&object=" . urlencode($_GET['object']) . "\">" . LangViewObjectObservations . " " . $_GET['object']);
	echo("</td><td width=\"25%\" align=\"center\">");
  if (array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
    echo("<a href=\"deepsky/index.php?indexAction=add_observation&object=" . 
		     urlencode($_GET['object']) . 
				 "\">" . LangViewObjectAddObservation . 
				 $_GET['object'] . "</a>");
	echo("</td>");
	if($myList)
	{
    echo("<td width=\"25%\" align=\"center\">");
    if($list->checkObjectInMyActiveList($_GET['object']))
      echo("<a href=\"deepsky/index.php?indexAction=detail_object&amp;object=" . $_GET['object'] . "&amp;removeObjectFromList=" . urlencode($_GET['object']) . "\">" . $_GET['object'] . LangListQueryObjectsMessage3 . $_SESSION['listname'] . "</a>");
    else
      echo("<a href=\"deepsky/index.php?indexAction=detail_object&amp;object=" . $_GET['object'] . "&amp;addObjectToList=" . urlencode($_GET['object']) . "&amp;showname=" . $_GET['object'] . "\">" . $_GET['object'] . LangListQueryObjectsMessage2 . $_SESSION['listname'] . "</a>");
	  echo("</td>");
	}	
	echo("</tr>");
	echo("</table>");

	
	if(array_key_exists('zoom',$_GET) && $_GET['zoom']) $zoom=$_GET['zoom'];
  else $zoom=30;
	$objects->showObject($_GET['object'], $zoom);

  echo("<div id=\"main\">\n<h2>");
  
  echo LangViewDSSImageTitle . $_POST['name'];
  
  echo("&nbsp;(" . $_POST['imagesize'] . "&#39;&nbsp;x&nbsp;" . $_POST['imagesize'] . "&#39;)</h2>\n");
  
  $_SESSION['imagelink'] = ("http://archive.stsci.edu/cgi-bin/dss_search?v=poss2ukstu_red&amp;r=" . $_POST['raDSS'] . ".0&amp;d=" . $_POST['declDSS'] . "&amp;e=J2000&amp;h=" . $_POST['imagesize'] . ".0&amp;w=" . $_POST['imagesize'] . "&amp;f=gif&amp;c=none&amp;fov=NONE&amp;v3=");
  
  echo("<img src=\"" . $_SESSION['imagelink'] . "\" alt=\"" . $_POST['name'] . "\" width=\"495\" height=\"495\"></img><p>&copy;&nbsp;<a href=\"http://archive.stsci.edu/dss/index.html\">STScI Digitized Sky Survey</a></p>");
}
echo("</div>\n</div>\n</body>\n</html>");

?>
