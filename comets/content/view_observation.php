<?php

// view_observation.php
// view information of observation 
// version 0.5: 20051205, JV

// start session

// include statements and creation of objects

include_once "../lib/setup/databaseInfo.php";

include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

include_once "../lib/cometobservations.php"; // observation table
$observations = new CometObservations;

include_once "../lib/instruments.php"; // instruments table
$instruments = new Instruments;

include_once "../lib/locations.php"; // locations table
$locations = new Locations;

include_once "../lib/observers.php"; // observers table
$observers = new Observers;

include_once "../lib/cometobjects.php"; // objects table
$objects = new CometObjects;

include_once "../lib/ICQMETHOD.php";
$ICQMETHODS = new ICQMETHOD();

include_once "../lib/ICQREFERENCEKEY.php";
$ICQREFERENCEKEYS = new ICQREFERENCEKEY;

if(!$_GET['observation']) // no observation defined 
{
   header("Location: ../index.php");
}  

if($observations->getObjectId($_GET['observation'])) // check if observation exists
{
echo("<div id=\"main\">\n<h2>" . LangViewObservationTitle . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");

if($_SESSION['observation_query']) // array of observations
{
   $arrayIndex = array_search($_GET['observation'],$_SESSION['observation_query']);
   $previousIndex = $arrayIndex + 1;
   $previousObservation = $_SESSION['observation_query'][$previousIndex];
   $nextIndex = $arrayIndex - 1;
   $nextObservation = $_SESSION['observation_query'][$nextIndex];

   if ($previousObservation != "")
   {
    echo "<a href=\"comets/detail_observation.php?observation=" . $previousObservation . "\">&lt</a>&nbsp;&nbsp;&nbsp;";
   }

   if ($nextObservation != "")
   {
    echo "<a href=\"comets/detail_observation.php?observation=" . $nextObservation . "\">&gt;</a> ";
   }

}

echo("</h2><table width=\"490\">\n");

// OBJECTNAME

echo("<tr>\n
<td class=\"fieldname\">\n");

echo LangViewObservationField1;

echo("</td>\n<td>\n<a href=\"comets/detail_object.php?object=" . $observations->getObjectId($_GET['observation']) . "\">");

echo($objects->getName($observations->getObjectId($_GET['observation'])));

echo("</a></td></tr>");

// OBSERVER

echo("<tr><td class=\"fieldname\">");

echo LangViewObservationField2;

echo("</td><td><a href=\"common/detail_observer.php?user=" . $observations->getObserverId($_GET['observation']) . "&amp;back=detail_observation.php\">");

echo($observers->getFirstName($observations->getObserverId($_GET['observation'])) . "&nbsp;" . $observers->getName($observations->getObserverId($_GET['observation'])));

print("</a></td></tr>");

// DATE

print("<tr>
   <td class=\"fieldname\">");

echo LangViewObservationField5;

echo("</td>
   <td>");

$date = sscanf($observations->getDate($_GET['observation']), "%4d%2d%2d");

if($observations->getTime($_GET['observation']) >= 0)
{
  if ($observers->getUseLocal($_SESSION['deepskylog_id']))
  {
    $date = sscanf($observations->getLocalDate($_GET['observation']), "%4d%2d%2d");
  }
}

echo date ($dateformat, mktime (0,0,0,$date[1],$date[2],$date[0]));

echo("</td></tr>");

// TIME

if($observations->getTime($_GET['observation']) >= 0)
{
  if ($observers->getUseLocal($_SESSION['deepskylog_id']))
  {
    echo("<tr><td class=\"fieldname\">" . LangViewObservationField9lt . "</td><td>");
    $time = $observations->getLocalTime($_GET['observation']);
  }
  else
  {
    echo("<tr><td class=\"fieldname\">" . LangViewObservationField9 . "</td><td>");
    $time = $observations->getTime($_GET['observation']);
  }

  $time = sscanf(sprintf("%04d", $time), "%2d%2d");

  echo ($time[0] . ":");

  printf("%02d", $time[1]);
  echo("</td></tr>");
}

// LOCATION

// inconsistency empty location == (0 | 1)
// empty instrument == 0

if ($observations->getLocationId($_GET['observation']) != 0 && $observations->getLocationId($_GET['observation']) != 1)
{
 print ("<tr><td class=\"fieldname\">");

 echo LangViewObservationField4;

 echo("</td><td>");

 echo("<a href=\"common/detail_location.php?location=" . $observations->getLocationId($_GET['observation']) . "\">" . $locations->getName($observations->getLocationId($_GET['observation'])) . "</a>");

 print("</td>
        </tr>");
}

// INSTRUMENT & MAGNIFICATION

if ($observations->getInstrumentId($_GET['observation']) != 0)
{
 echo("<tr><td class=\"fieldname\">");

 echo LangViewObservationField3;

 echo("</td><td>");

 $inst =  $instruments->getName($observations->getInstrumentId($_GET['observation']));

 if ($observations->getMagnification($_GET['observation']) != 0)
 {
  $inst = $inst." (".$observations->getMagnification($_GET['observation'])."x)";
 }

 if (strcmp($instruments->getName($observations->getInstrumentId($_GET['observation'])), "Naked eye") == 0)
 {
  $inst = InstrumentsNakedEye;
 }

 echo("<a href=\"common/detail_instrument.php?instrument=" . $observations->getInstrumentId($_GET['observation']) . "\">" . $inst . "</a>");

 print("</td></tr>");
}

// USED METHOD

if ($observations->getMethode($_GET['observation']) != "")
{
 print ("<tr><td class=\"fieldname\">");

 echo LangViewObservationField15;

 echo("</td><td>");

 $descr = $ICQMETHODS->getDescription($observations->getMethode($_GET['observation']));

 echo($observations->getMethode($_GET['observation']) . " - " . $descr);

 print("</td>
        </tr>");
}

// USED CHART

if ($observations->getChart($_GET['observation']) != "")
{
 print ("<tr><td class=\"fieldname\">");

 echo LangViewObservationField17;

 echo("</td><td>");

 $descr = $ICQREFERENCEKEYS->getDescription($observations->getChart($_GET['observation']));

 echo($observations->getChart($_GET['observation']) . " - " . $descr);

 print("</td>
        </tr>");
}

// ESTIMATED MAGNITUDE

if ($observations->getMagnitude($_GET['observation']) > -90)
{
 print ("<tr><td class=\"fieldname\">");

 echo LangViewObservationField16;

 echo("</td><td>");

 if($observations->getMagnitudeWeakerThan($_GET['observation']) == "1")
 {
    echo (LangNewComet3 . "&nbsp;");
 }

 echo($magnitude = sprintf("%01.1f", $observations->getMagnitude($_GET['observation'])));

 if($observations->getMagnitudeUncertain($_GET['observation']) == "1")
 {
    echo ("&nbsp;(" . LangNewComet2 . ")");
 }

 print("</td>
        </tr>");
}

// DEGREE OF CONDENSATION

if ($observations->getDc($_GET['observation']) != '')
{
 print ("<tr><td class=\"fieldname\">");

 echo LangViewObservationField18;

 echo("</td><td>");

 echo($observations->getDc($_GET['observation']));

 print("</td>
        </tr>");
}

// COMA

if ($observations->getComa($_GET['observation']) > -90)
{
 print ("<tr><td class=\"fieldname\">");

 echo LangViewObservationField19;

 echo("</td><td>");

 echo($observations->getComa($_GET['observation'])."'");

 print("</td>
        </tr>");
}

// TAIL

if ($observations->getTail($_GET['observation']) > -90)
{
 print ("<tr><td class=\"fieldname\">");

 echo LangViewObservationField20;

 echo("</td><td>");

 echo($observations->getTail($_GET['observation'])."'");

 print("</td>
        </tr>");
}

// PHASE ANGLE

if ($observations->getPa($_GET['observation']) > -90)
{
 print ("<tr><td class=\"fieldname\">");

 echo LangViewObservationField21;

 echo("</td><td>");

 echo($observations->getPa($_GET['observation'])."&deg;");

 print("</td>
        </tr>");
}

// DESCRIPTION

$description = $observations->getDescription($_GET['observation']);

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
$dir = opendir($upload_dir);

while (FALSE !== ($file = readdir($dir)))
{
   if ("." == $file OR ".." == $file)
   {
   continue; // skip current directory and directory above
   }
   if(fnmatch($_GET['observation'] . "_resized.gif", $file) || fnmatch($_GET['observation'] . "_resized.jpg",
$file) || fnmatch($_GET['observation']. "_resized.png", $file))
   {
   echo("<p><a href=\"comets/" . $upload_dir . "/" . $_GET['observation'] . ".jpg" . "\"><img class=\"account\" src=\"comets/$upload_dir" . "/" . "$file\">
         </img></a></p>");
   }
}

echo("</td></tr>");

echo("</table>");

$role = $obs->getRole($_SESSION['deepskylog_id']);

if ($role == RoleAdmin || $role == RoleCometAdmin)
{
echo("<p><a href=\"comets/adapt_observation.php?observation=" . $_GET['observation'] . "\">" . LangChangeObservationTitle . "</a></p>");

//echo("<p><a href=\"comets/control/validate_delete_observation.php?observationid=" . $_GET['observation'] . "\">" . LangDeleteObservation . "</a></p>");
}

}
echo("</div></body></html>");

?>
