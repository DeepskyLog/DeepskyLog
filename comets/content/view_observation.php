<?php

// view_observation.php
// view information of observation 
// version 0.5: 20051205, JV

// start session

// include statements and creation of objects

include_once "lib/icqmethod.php";
$ICQMETHODS = new ICQMETHOD();

include_once "lib/icqreferencekey.php";
$ICQREFERENCEKEYS = new ICQREFERENCEKEY;

if(!$_GET['observation']) // no observation defined 
{
   header("Location: ".$baseURL."index.php");
}  

if($objCometObservation->getObjectId($_GET['observation'])) // check if observation exists
{
echo("<div id=\"main\">\n<h2>" . LangViewObservationTitle . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");

if($_SESSION['observation_query']) // array of observations
{
   $arrayIndex = array_search($_GET['observation'],$_SESSION['observation_query']);
   $previousIndex = $arrayIndex + 1;
   @$previousObservation = $_SESSION['observation_query'][$previousIndex];
   $nextIndex = $arrayIndex - 1;
   @$nextObservation = $_SESSION['observation_query'][$nextIndex];

   if ($previousObservation != "")
   {
    echo "<a href=\"".$baseURL."index.php?indexAction=comets_detail_observation&amp;observation=" . $previousObservation . "\">&lt</a>&nbsp;&nbsp;&nbsp;";
   }

   if ($nextObservation != "")
   {
    echo "<a href=\"".$baseURL."index.php?indexAction=comets_detail_observation&amp;observation=" . $nextObservation . "\">&gt;</a> ";
   }

}

echo("</h2><table width=\"490\">\n");

// OBJECTNAME

echo("<tr>\n
<td class=\"fieldname\">\n");

echo LangViewObservationField1;

echo("</td>\n<td>\n<a href=\"".$baseURL."index.php?indexAction=comets_detail_object&amp;object=" . urlencode($objCometObservation->getObjectId($_GET['observation'])) . "\">");

echo($objCometObject->getName($objCometObservation->getObjectId($_GET['observation'])));

echo("</a></td></tr>");

// OBSERVER

echo("<tr><td class=\"fieldname\">");

echo LangViewObservationField2;

echo("</td><td><a href=\"".$baseURL."index.php?indexAction=detail_observer&amp;user=" . urlencode($objCometObservation->getObserverId($_GET['observation'])) . "\">");

echo($objObserver->getObserverProperty($objCometObservation->getObserverId($_GET['observation']),'firstname') . "&nbsp;" . $objObserver->getObserverProperty($objCometObservation->getObserverId($_GET['observation']),'name'));

print("</a></td></tr>");

// DATE

print("<tr>
   <td class=\"fieldname\">");

echo LangViewObservationField5;

echo("</td>
   <td>");

$date = sscanf($objCometObservation->getDate($_GET['observation']), "%4d%2d%2d");

if($objCometObservation->getTime($_GET['observation']) >= 0)
{
  if(!($objObserver->getObserverProperty($_SESSION['deepskylog_id'],'UT')))
  {
    $date = sscanf($objCometObservation->getLocalDate($_GET['observation']), "%4d%2d%2d");
  }
}

echo date ($dateformat, mktime (0,0,0,$date[1],$date[2],$date[0]));

echo("</td></tr>");

// TIME

if($objCometObservation->getTime($_GET['observation']) >= 0)
{
  if(!($objObserver->getObserverProperty($_SESSION['deepskylog_id'],'UT')))
  {
    echo("<tr><td class=\"fieldname\">" . LangViewObservationField9lt . "</td><td>");
    $time = $objCometObservation->getLocalTime($_GET['observation']);
  }
  else
  {
    echo("<tr><td class=\"fieldname\">" . LangViewObservationField9 . "</td><td>");
    $time = $objCometObservation->getTime($_GET['observation']);
  }

  $time = sscanf(sprintf("%04d", $time), "%2d%2d");

  echo ($time[0] . ":");

  printf("%02d", $time[1]);
  echo("</td></tr>");
}

// LOCATION

// inconsistency empty location == (0 | 1)
// empty instrument == 0

if ($objCometObservation->getLocationId($_GET['observation']) != 0 && $objCometObservation->getLocationId($_GET['observation']) != 1)
{
 print ("<tr><td class=\"fieldname\">");

 echo LangViewObservationField4;

 echo("</td><td>");

 echo("<a href=\"".$baseURL."index.php?indexAction=detail_location&amp;location=" . urlencode($objCometObservation->getLocationId($_GET['observation'])) . "\">" . $objLocation->getLocationPropertyFromId($objCometObservation->getLocationId($_GET['observation']),'name') . "</a>");

 print("</td>
        </tr>");
}

// INSTRUMENT & MAGNIFICATION

if ($objCometObservation->getInstrumentId($_GET['observation']) != 0)
{
 echo("<tr><td class=\"fieldname\">");

 echo LangViewObservationField3;

 echo("</td><td>");

 $inst =  $objInstrument->getInstrumentPropertyFromId($objCometObservation->getInstrumentId($_GET['observation']),'name');

 if ($objCometObservation->getMagnification($_GET['observation']) != 0)
 {
  $inst = $inst." (".$objCometObservation->getMagnification($_GET['observation'])."x)";
 }

 if (strcmp($objInstrument->getInstrumentPropertyFromId($objCometObservation->getInstrumentId($_GET['observation']),'name'), "Naked eye") == 0)
 {
  $inst = InstrumentsNakedEye;
 }

 echo("<a href=\"".$baseURL."index.php?indexAction=detail_instrument&amp;instrument=" . urlencode($objCometObservation->getInstrumentId($_GET['observation'])) . "\">" . $inst . "</a>");

 print("</td></tr>");
}

// USED METHOD

if ($objCometObservation->getMethode($_GET['observation']) != "")
{
 print ("<tr><td class=\"fieldname\">");

 echo LangViewObservationField15;

 echo("</td><td>");

 $descr = $ICQMETHODS->getDescription($objCometObservation->getMethode($_GET['observation']));

 echo($objCometObservation->getMethode($_GET['observation']) . " - " . $descr);

 print("</td>
        </tr>");
}

// USED CHART

if ($objCometObservation->getChart($_GET['observation']) != "")
{
 print ("<tr><td class=\"fieldname\">");

 echo LangViewObservationField17;

 echo("</td><td>");

 $descr = $ICQREFERENCEKEYS->getDescription($objCometObservation->getChart($_GET['observation']));

 echo($objCometObservation->getChart($_GET['observation']) . " - " . $descr);

 print("</td>
        </tr>");
}

// ESTIMATED MAGNITUDE

if ($objCometObservation->getMagnitude($_GET['observation']) > -90)
{
 print ("<tr><td class=\"fieldname\">");

 echo LangViewObservationField16;

 echo("</td><td>");

 if($objCometObservation->getMagnitudeWeakerThan($_GET['observation']) == "1")
 {
    echo (LangNewComet3 . "&nbsp;");
 }

 echo($magnitude = sprintf("%01.1f", $objCometObservation->getMagnitude($_GET['observation'])));

 if($objCometObservation->getMagnitudeUncertain($_GET['observation']) == "1")
 {
    echo ("&nbsp;(" . LangNewComet2 . ")");
 }

 print("</td>
        </tr>");
}

// DEGREE OF CONDENSATION

if ($objCometObservation->getDc($_GET['observation']) != '')
{
 print ("<tr><td class=\"fieldname\">");

 echo LangViewObservationField18;

 echo("</td><td>");

 echo($objCometObservation->getDc($_GET['observation']));

 print("</td>
        </tr>");
}

// COMA

if ($objCometObservation->getComa($_GET['observation']) > -90)
{
 print ("<tr><td class=\"fieldname\">");

 echo LangViewObservationField19;

 echo("</td><td>");

 echo($objCometObservation->getComa($_GET['observation'])."'");

 print("</td>
        </tr>");
}

// TAIL

if ($objCometObservation->getTail($_GET['observation']) > -90)
{
 print ("<tr><td class=\"fieldname\">");

 echo LangViewObservationField20;

 echo("</td><td>");

 echo($objCometObservation->getTail($_GET['observation'])."'");

 print("</td>
        </tr>");
}

// PHASE ANGLE

if ($objCometObservation->getPa($_GET['observation']) > -90)
{
 print ("<tr><td class=\"fieldname\">");

 echo LangViewObservationField21;

 echo("</td><td>");

 echo($objCometObservation->getPa($_GET['observation'])."&deg;");

 print("</td>
        </tr>");
}

// DESCRIPTION

$description = $objCometObservation->getDescription($_GET['observation']);

if ($description != "")
{
 echo("<tr>
       <td class=\"fieldname\">");

 echo LangViewObservationField8;

 echo("</td>
       <td>");

 echo $description;

 echo("</td></tr>");
}

// DRAWING

echo("<tr><td colspan=\"2\">");

$upload_dir = 'cometdrawings';
$dir = opendir($instDir.'comets/'.$upload_dir);

while (FALSE !== ($file = readdir($dir)))
{
   if ("." == $file OR ".." == $file)
   {
   continue; // skip current directory and directory above
   }
   if(fnmatch($_GET['observation'] . "_resized.gif", $file) || fnmatch($_GET['observation'] . "_resized.jpg", $file) || fnmatch($_GET['observation']. "_resized.png", $file))
   {
   echo $baseURL."comets/" . $upload_dir . "/" . $_GET['observation'] . ".jpg";
   echo("<p><a href=\"".$baseURL."comets/" . $upload_dir . "/" . $_GET['observation'] . ".jpg" . "\"><img class=\"account\" src=\"".$baseURL."comets/$upload_dir" . "/" . "$file\" alt=\"\">
         </img></a></p>");
   }
}

echo("</td></tr>");

echo("</table>");

$role = $objObserver->getObserverProperty($_SESSION['deepskylog_id'],'role',2);

if ($role == RoleAdmin || $role == RoleCometAdmin)
{
echo("<p><a href=\"".$baseURL."index.php?indexAction=comets_adapt_observation&amp;observation=" . $_GET['observation'] . "\">" . LangChangeObservationTitle . "</a></p>");

//echo("<p><a href=\"comets/control/validate_delete_observation.php?observationid=" . $_GET['observation'] . "\">" . LangDeleteObservation . "</a></p>");
}

}
echo("</div></body></html>");

?>
