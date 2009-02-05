<?php

// new_observation.php
// GUI to add a new observation of a comet to the database 
// Version 0.5: 2005/12/05, JV

// include statements

include_once "lib/cometobjects.php";
include_once "lib/cometobservations.php";
include_once "common/control/ra_to_hms.php";
include_once "common/control/dec_to_dm.php";
include_once "lib/ICQMETHOD.php";
include_once "lib/ICQREFERENCEKEY.php";
include_once "lib/locations.php";
include_once "lib/observers.php";
include_once "lib/instruments.php";
include_once "lib/util.php";

$util = new Util();
$util->checkUserInput();

// create comet object

$objects = new CometObjects;
$observers = new Observers();

// PAGE TITLE

echo("<div id=\"main\">\n");

echo("<h2>");

echo (LangNewObservationTitle);

echo("</h2>\n");

echo("<form action=\"".$baseURL."index.php\" method=\"post\" enctype=\"multipart/form-data\">");
echo "<input type=\"hidden\" name=\"indexAction\" value=\"comets_validate_observation\" />";

//echo("<a href=\"comets/add_csv.php\">" . LangNewObservationSubtitle1b . "</a>");

echo("<table width=\"490\" id=\"content\">\n");

// retain object id to easy input of looked up comet

$id = $objUtil->checkSessionKey('observedobject',$objUtil->checkGetKey('observedobject'));


// OBJECT NAME 

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangQueryObjectsField1;

echo("&nbsp;*</td>\n<td colspan=\"2\">\n");

echo("<select name=\"comet\">\n");

echo("<option value=\"\"></option>\n\">"); // empty value
$catalogs = $objects->getSortedObjects("name");
while(list($key, $value) = each($catalogs))
{
   if ($id && $id == $objects->getId($value[0]))
   {
    echo("<option value=\"$value[0]\" selected>$value[0]</option>\n");
   }
   else
   {
    echo("<option value=\"$value[0]\">$value[0]</option>\n");
   }
}
echo("</select>\n");

echo("</td></tr>");

// DATE

echo("<tr><td class=\"fieldname\">" . LangViewObservationField5 . "&nbsp;*</td><td>
         <input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"day\"");


if($objUtil->checkSessionKey('savedata') == "yes" && $objUtil->checkSessionKey('day') != "")
{
   echo(" value=\"" . $objUtil->checkSessionKey('day') . "\" />");
}
else
{
   echo(" value=\"\" />");
}

// action item: replace this code by loops!

echo("&nbsp;&nbsp;<select name=\"month\">");

echo ("<option value=\"\"></option>");
echo ("<option value=\"1\"");
if ($objUtil->checkSessionKey('month') == "1" && $objUtil->checkSessionKey('savedata') == "yes")
{
   echo (" selected=\"selected\">" . LangNewObservationMonth1 . "</option>");
}
else
{
   echo (">" . LangNewObservationMonth1 . "</option>");
}
echo ("<option value=\"2\"");
if ($objUtil->checkSessionKey('month') == "2" && $_SESSION['savedata'] == "yes")
{
   echo (" selected=\"selected\">" . LangNewObservationMonth2 . "</option>");
}
else
{
   echo (">" . LangNewObservationMonth2 . "</option>");
}
echo ("<option value=\"3\"");
if ($objUtil->checkSessionKey('month') == "3" && $_SESSION['savedata'] == "yes")
{
   echo (" selected=\"selected\">" . LangNewObservationMonth3 . "</option>");
}
else
{
   echo (">" . LangNewObservationMonth3 . "</option>");
}
echo ("<option value=\"4\"");
if ($objUtil->checkSessionKey('month') == "4" && $_SESSION['savedata'] == "yes")
{
   echo (" selected=\"selected\">" . LangNewObservationMonth4 . "</option>");
}
else
{
   echo (">" . LangNewObservationMonth4 . "</option>");
}
echo ("<option value=\"5\"");
if ($objUtil->checkSessionKey('month') == "5" && $_SESSION['savedata'] == "yes")
{
   echo (" selected=\"selected\">" . LangNewObservationMonth5 . "</option>");
}
else
{
   echo (">" . LangNewObservationMonth5 . "</option>");
}
echo ("<option value=\"6\"");
if ($objUtil->checkSessionKey('month') == "6" && $_SESSION['savedata'] == "yes")
{
   echo (" selected=\"selected\">" . LangNewObservationMonth6 . "</option>");
}
else
{
   echo (">" . LangNewObservationMonth6 . "</option>");
}
echo ("<option value=\"7\"");
if ($objUtil->checkSessionKey('month') == "7" && $_SESSION['savedata'] == "yes")
{
   echo (" selected=\"selected\">" . LangNewObservationMonth7 . "</option>");
}
else
{
   echo (">" . LangNewObservationMonth7 . "</option>");
}
echo ("<option value=\"8\"");
if ($objUtil->checkSessionKey('month') == "8" && $_SESSION['savedata'] == "yes")
{
   echo (" selected=\"selected\">" . LangNewObservationMonth8 . "</option>");
}
else
{
   echo (">" . LangNewObservationMonth8 . "</option>");
}
echo ("<option value=\"9\"");
if ($objUtil->checkSessionKey('month') == "9" && $_SESSION['savedata'] == "yes")
{
   echo (" selected=\"selected\">" . LangNewObservationMonth9 . "</option>");
}
else
{
   echo (">" . LangNewObservationMonth9 . "</option>");
}
echo ("<option value=\"10\"");
if ($objUtil->checkSessionKey('month') == "10" && $_SESSION['savedata'] == "yes")
{
   echo (" selected=\"selected\">" . LangNewObservationMonth10 . "</option>");
}
else
{
   echo (">" . LangNewObservationMonth10 . "</option>");
}
echo ("<option value=\"11\"");
if ($objUtil->checkSessionKey('month') == "11" && $_SESSION['savedata'] == "yes")
{
   echo (" selected=\"selected\">" . LangNewObservationMonth11 . "</option>");
}
else
{
   echo (">" . LangNewObservationMonth11 . "</option>");
}
echo ("<option value=\"12\"");
if ($objUtil->checkSessionKey('month') == "12" && $_SESSION['savedata'] == "yes")
{
   echo (" selected=\"selected\">" . LangNewObservationMonth12 . "</option>");
}
else
{
   echo (">" . LangNewObservationMonth12 . "</option>");
}

echo("</select>&nbsp;&nbsp<input type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" name=\"year\"");
if($objUtil->checkSessionKey('savedata') == "yes" && $objUtil->checkSessionKey('year') != "")
{
   echo ("value=\"" . $_SESSION['year'] . "\" />");
}
else
{
   echo ("value=\"\" />");
}
   echo("</td><td class=\"explanation\">".LangViewObservationField10."</td></tr>");

// TIME

if ($observers->getObserverProperty($_SESSION['deepskylog_id'],'UT'))
{
   echo("<tr><td class=\"fieldname\">" . LangViewObservationField9 . "&nbsp;*</td>\n");
}
else
{
   echo("<tr><td class=\"fieldname\">" . LangViewObservationField9lt . "&nbsp;*</td>\n");
}

   echo("<td><input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"hours\" value=\"\" />&nbsp;&nbsp;
              <input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"minutes\" value=\"\" /></td>
          <td class=\"explanation\">".LangViewObservationField11."</td></tr>");


// LOCATION

$locations = new Locations;

echo("<tr><td class=\"fieldname\">" . LangViewObservationField4 . "</td><td><select name=\"site\">");

      $sites = $locations->getSortedLocationsList("name", $_SESSION['deepskylog_id']);

      for ($i = 0;$i < count($sites);$i++)
      {
         $sitename = $sites[$i][1];

         if($objUtil->checkSessionKey('savedata') == "yes") // multiple observations
         {
            if($_SESSION['location'] == $sites[$i][0]) // location equals session location
            {
              print("<option selected=\"selected\" value=\"".$sites[$i][0]."\">$sitename</option>\n");
            }
            else
            {
              print("<option value=\"".$sites[$i][0]."\">$sitename</option>\n");
            }
         }
         else // first observation of session
         {
           if($objObserver->getStandardLocation($_SESSION['deepskylog_id']) == $sites[$i][0]) // location equals standard location
           {
              print("<option selected=\"selected\" value=\"".$sites[$i][0]."\">$sitename</option>\n");
           }
           else
           {
              print("<option value=\"".$sites[$i][0]."\">$sitename</option>\n");
           }
         }
      }

    echo("</select></td><td class=\"explanation\"><a href=\"".$baseURL."index.php?indexAction=add_site\">" . LangChangeAccountField7Expl ."</a></td></tr>");

// INSTRUMENT

// create object

$instruments = new Instruments;

echo("<tr><td class=\"fieldname\">" . LangViewObservationField3 . "</td><td>
      <select name=\"instrument\">\n");

echo("<option value=\"\"></option>\n"); // include empty instrument

      $instr = $objInstrument->getSortedInstrumentsList("name",$_SESSION['deepskylog_id']);

      while(list ($key, $value) = each($instr)) // go through instrument array
      {
         $instrumentname = $value;
         $val = $key;

         if($objUtil->checkSessionKey('savedata') == "yes") // multiple observations
         {
           if($val == $_SESSION['instrument']) // instrument of previous observation
           {
             print("<option selected=\"selected\" value=\"$val\">");
           }
           else
           {
             print("<option value=\"$val\">");
           }
         }
         elseif($objObserver->getStandardTelescope($_SESSION['deepskylog_id']) == $val) // not executed when previous observation
         {
           print("<option selected=\"selected\" value=\"$val\">");
         }
         else // first observation of session and not the standard instrument
         {
           print("<option value=\"$val\">");
         }
         if ($instrumentname == "Naked eye")
         {
           $instrumentname = InstrumentsNakedEye;
         }
         echo("$instrumentname</option>\n");
      }

echo("</select></td><td class=\"explanation\"><a href=\"".$baseURL."index.php?indexAction=add_instrument\">" . LangChangeAccountField8Expl . "</a>
   </td></tr>");

// MAGNIFICATION

echo("<tr><td>" . LangNewComet4 . "</td>");

echo("<td>");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"magnification\" size=\"3\" />");

echo("</td><td></td></tr>");

// MAGNITUDE METHOD KEY

$ICQMETHODS = new ICQMETHOD();
$methods = $ICQMETHODS->getIds();

echo("<tr><td>" . LangNewComet5 . "</td>");

echo("<td>");

echo("<select name=\"icq_method\">\n");

echo("<option value=\"\"></option>\n\">"); // empty value

while(list($key, $value) = each($methods))
{
   echo("<option value=\"$value\">" . $value . " - " . $ICQMETHODS->getDescription($value) . "</option>\n");
}
echo("</select>\n");

echo("</td><td><a href=\"http://cfa-www.harvard.edu/icq/ICQKeys.html\" target=\"external\">" . LangNewComet7 . "</a></td></tr>");

// MAGNITUDE REFERENCE KEY

$ICQREFERENCEKEYS = new ICQREFERENCEKEY();
$methods = $ICQREFERENCEKEYS->getIds();

echo("<tr><td>" . LangNewComet6 . "</td>");

echo("<td>");

echo("<select name=\"icq_reference_key\">\n");

echo("<option value=\"\"></option>\n\">"); // empty value

while(list($key, $value) = each($methods))
{
   echo("<option value=\"$value\">" . $value . " - " . $ICQREFERENCEKEYS->getDescription($value) . "</option>\n");
}
echo("</select>\n");

echo("</td><td><a href=\"http://cfa-www.harvard.edu/icq/ICQRec.html\" target=\"external\">" . LangNewComet7 . "</a></td></tr>");

// MAGNITUDE

echo("<tr><td class=\"fieldname\">" . LangNewComet1 . "&nbsp;");

echo("</td><td colspan=\"2\"><select name=\"smaller\">
                <option selected=\"selected\" value=\"0\">&nbsp;</option>\n
                <option value=\"1\">". LangNewComet3 . "</option>\n</select>");

echo("&nbsp;<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"mag\" size=\"4\" />
            <input type=\"checkbox\" name=\"uncertain\" />" . LangNewComet2 . "</td>");

echo("</tr>");

// DEGREE OF CONDENSATION

echo("<tr><td>" . LangNewComet8 . "</td>");

echo("<td>");

echo("<select name=\"condensation\">");

echo("<option value=\"\"></option>");

for ($i = 0; $i <= 9; $i++) {
   echo("<option value=\"" . $i . "\">" . $i . "</option>\n");
} 

echo("</select>");

echo("</td><td></td></tr>");

// COMA

echo("<tr><td>" . LangNewComet9 . "</td>");

echo("<td>");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"coma\" size=\"3\" />");

echo("</td><td>(" . LangNewComet13 . ")</td></tr>");

// TAIL LENGTH

echo("<tr><td>" . LangNewComet10 . "</td>");

echo("<td>");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"tail_length\" size=\"3\" />");

echo("</td><td>(" . LangNewComet13 . ")</td></tr>");

// POSITION ANGLE TAIL

echo("<tr><td>" . LangNewComet11 . "</td>");

echo("<td>");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"position_angle\" size=\"3\" />");

echo("</td><td>(" . LangNewComet12 . ")</td></tr>");

// DRAWING

    echo("<tr><td class=\"fieldname\">".LangViewObservationField12."</td><td><input type=\"file\" name=\"drawing\" /></td><td></td></tr>");

// DESCRIPTION

    echo("<tr><td class=\"fieldname\">" . LangViewObservationField8 . "</td><td></td><td></td></tr>");

    echo("<tr><td colspan=\"3\"><textarea name=\"description\" class=\"description\"></textarea></td></tr>");

   echo("<tr><td colspan=\"3\">
         <input type=\"submit\" name=\"addobservation\" value=\"".LangViewObservationButton1."\" />&nbsp;
         <input type=\"submit\" name=\"clearfields\" value=\"".LangViewObservationButton2."\" /></td></tr>");

   echo("</table></form>");

   echo("</div>\n</div>\n</body>\n</html>");
?>
