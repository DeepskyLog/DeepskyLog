<?php

// view_object.php
// view all information of one object 
// version 0.5: 2005/09/21, WDM

include_once "lib/cometobjects.php";
include_once "lib/setup/language.php";
include_once "lib/cometobservations.php";
include_once "lib/observers.php";
include_once "lib/util.php";

$util = new Util();
$util->checkUserInput();

$objects = new CometObjects; 
$observers = new Observers;

if(!$_GET['object']) // no object defined in url 
{
   header("Location: ../index.php");
}  

echo("<div id=\"main\">\n<h2>");

echo (LangViewObjectTitle . "&nbsp;-&nbsp;" . $objects->getName($_GET['object'])); 

echo("</h2>\n<table width=\"490\">\n");

// NAME

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObjectField1;

echo("</td>\n<td>");

echo($objects->getName($_GET['object']));

echo("</td>\n</tr>\n");

// ICQNAME

if ($objects->getIcqName($_GET['object']))
{
 echo("<tr>\n
       <td class=\"fieldname\">");

 echo LangNewObjectIcqname;

 echo("</td>\n<td>");

 echo($objects->getIcqName($_GET['object']));

 echo("</td>\n</tr>\n");
}

echo("</table>\n");

// LINK TO OBSERVATIONS OF OBJECT

$observations = new CometObservations();

$obs = $observations->getObservations();

$observation_found = "no";

$queries = array("object" => $objects->getName($_GET['object']));
$found_observations = $observations->getObservationFromQuery($queries);

if (count($found_observations) > 0)
{
 $observation_found = "yes";
}

if($observation_found == "yes")
{
   echo("<p><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . urlencode($_GET['object']) . 
        "\">" . LangViewObjectObservations . $objects->getName($_GET['object']) . "</a></p>");
}

# extra link to add observation of this object

if($_SESSION['deepskylog_id'])
{

   $_SESSION['observedobject'] = $_GET['object'];
   $_SESSION['result'] = $objects->getExactObject($_SESSION['observedobject']);
   //$_SESSION['observedobject'] = $_SESSION['result'][0]; // use name in database
   $_SESSION['found'] = "yes";
   $_SESSION['backlink'] = "validate_search_object.php";
   echo("<p><a href=\"".$baseURL."index.php?indexAction=comets_add_observation&amp;observedobject=" . urlencode($_GET['object']) . "\">" . LangViewObjectAddObservation . $objects->getName($_GET['object']) . "</a></p>");
}

# extra link for administrators to change comet details

$role = $objObserver->getObserverProperty($_SESSION['deepskylog_id'],'role',2);

if ($role == RoleAdmin || $role == RoleCometAdmin)
{
   echo("<p><a href=\"".$baseURL."index.php?indexAction=comets_change_object&amp;object=" . urlencode($_GET['object']) . "\">" . LangChangeObject . " " .$objects->getName($_GET['object']) . "</a></p>");
}

echo("\n</div>\n");

echo("</body>\n</html>");

?>
