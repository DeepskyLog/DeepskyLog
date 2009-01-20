<?php

// setup_observations_query.php
// interface to query observations
// version 0.5, WDM 20051121

include_once "lib/cometobservations.php";
include_once "lib/cometobjects.php";
include_once "lib/observers.php";
include_once "lib/instruments.php";
include_once "lib/locations.php";
include_once "lib/util.php";
include_once "lib/ICQMETHOD.php";
include_once "lib/ICQREFERENCEKEY.php";

$objects = new CometObjects; 
$observations = new CometObservations;
$observers = new Observers;
$instruments = new Instruments;
$locations = new Locations;
$util = new util;
$util->checkUserInput();

$_SESSION['result'] = "";

echo("<div id=\"main\">\n");
echo("<h2>");

echo LangQueryObservationsTitle;

echo("</h2>\n");

echo("<table width=\"490\">\n");

echo("<form action=\"".$baseURL."index.php\" method=\"get\">\n");
echo "<input type=\"hidden\" name=\"indexAction\" value=\"comets_result_selected_observations\" />";
$id = $objUtil->checkSessionKey('observedobject',$objUtil->checkGetKey('observedobject'));

// OBJECT NAME

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangQueryObjectsField1;

echo("</td>\n<td colspan=\"2\">\n");

echo("<select name=\"object\">\n");

echo("<option value=\"\"></option>\n"); // empty value

$catalogs = $objects->getSortedObjects("name");

while(list($key, $value) = each($catalogs))
{
   if ($id && $id == $objects->getId($value))
   {
    echo("<option value=\"".$value[0]."\" selected>$value[0]</option>\n");
   }
   else
   {
    echo("<option value=\"".$value[0]."\">$value[0]</option>\n");
   }
}
echo("</select>\n");

echo("</td></tr>");

/*

// OBJECT NAME 

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObservationField1;

echo("</td>\n<td colspan=\"2\">\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"40\" name=\"number\" size=\"20\" value=\"\" />");

echo("</td>\n</tr>\n");

*/

// OBSERVER 

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObservationField2;

echo("</td>\n<td>\n");

echo("<select name=\"observer\">\n");

echo("<option value=\"\"></option>"); // empty field

$obs = $observers->getSortedObservers('name'); 
$obs = $observations->getPopularObservers();

while(list($key,$value) = each($obs))
{
 $sortobs[$value] = $observers->getObserverName($value)." ".$observers->getFirstName($value);
}
natcasesort($sortobs);

while(list($value, $key) = each($sortobs))
{
   echo("<option value=\"$value\">".$key."</option>\n");
}

echo("</select>\n");

echo("</td>\n<td>\n</td>\n</tr>\n");

// INSTRUMENT 

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObservationField3;

echo("</td>\n<td>\n");

echo("<select name=\"instrument\">\n");

echo("<option value=\"\"></option>"); // empty field

$inst = $instruments->getSortedInstrumentsList("name");

while(list($key, $value) = each($inst))
{
   echo("<option value=\"$key\">".$value."</option>\n");
}

echo("</select>\n");

echo("</td>\n<td>\n</td>\n</tr>\n");

// MINIMUM DIAMETER

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObservationField13;

echo("</td>\n
      <td>\n
      <input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"mindiameter\" size=\"10\" />
      <select name=\"mindiameterunits\"><option selected=\"selected\"></option><option>inch</option><option>mm</option></select>
      </td>
      </tr>");

// MAXIMUM DIAMETER

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObservationField14;

echo("</td>\n
      <td>\n
      <input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"maxdiameter\" size=\"10\" />
      <select name=\"maxdiameterunits\"><option selected=\"selected\"></option><option>inch</option><option>mm</option></select>
      </td>
      </tr>");

// SITE 

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObservationField4;

echo("</td>\n<td>\n");

echo("<select name=\"site\">\n");

echo("<option value=\"\"></option>"); // empty field

$sites = $locations->getSortedLocations("name", "", true);

while(list($key, $value) = each($sites))
{
   if($key != 0) // remove empty location in database
   {
      echo("<option value=\"$value\">".$locations->getLocationName($value)."</option>\n");
   }
}

echo("</select>\n");

echo("</td>\n<td>\n</td>\n</tr>\n");

// MINIMUM DATE

echo("<tr>\n
      <td class=\"fieldname\">" 
      . LangFromDate . "</td><td><input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"minday\" value=\"\" />&nbsp;&nbsp;<select name=\"minmonth\">

             <option value=\"\"></option>
             <option value=\"1\">" . LangNewObservationMonth1 . "</option>
             <option value=\"2\">" . LangNewObservationMonth2 . "</option>
             <option value=\"3\">" . LangNewObservationMonth3 . "</option>
             <option value=\"4\">" . LangNewObservationMonth4 . "</option>
             <option value=\"5\">" . LangNewObservationMonth5 . "</option>
             <option value=\"6\">" . LangNewObservationMonth6 . "</option>
             <option value=\"7\">" . LangNewObservationMonth7 . "</option>
             <option value=\"8\">" . LangNewObservationMonth8 . "</option>
             <option value=\"9\">" . LangNewObservationMonth9 . "</option>
             <option value=\"10\">" . LangNewObservationMonth10 . "</option>
             <option value=\"11\">" . LangNewObservationMonth11 . "</option>
             <option value=\"12\">" . LangNewObservationMonth12 . "</option>
             </select>&nbsp;&nbsp;<input type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" name=\"minyear\" value=\"\" />
             </td>\n</tr>\n");

// MAXIMUM DATE

echo("<tr>\n
      <td class=\"fieldname\">"
      . LangTillDate . "</td><td><input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"maxday\" value=\"\" />&nbsp;&nbsp;<select name=\"maxmonth\">

             <option value=\"\"></option>
             <option value=\"1\">" . LangNewObservationMonth1 . "</option>
             <option value=\"2\">" . LangNewObservationMonth2 . "</option>
             <option value=\"3\">" . LangNewObservationMonth3 . "</option>
             <option value=\"4\">" . LangNewObservationMonth4 . "</option>
             <option value=\"5\">" . LangNewObservationMonth5 . "</option>
             <option value=\"6\">" . LangNewObservationMonth6 . "</option>
             <option value=\"7\">" . LangNewObservationMonth7 . "</option>
             <option value=\"8\">" . LangNewObservationMonth8 . "</option>
             <option value=\"9\">" . LangNewObservationMonth9 . "</option>
             <option value=\"10\">" . LangNewObservationMonth10 . "</option>
             <option value=\"11\">" . LangNewObservationMonth11 . "</option>
             <option value=\"12\">" . LangNewObservationMonth12 . "</option>
             </select>&nbsp;&nbsp;<input type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" name=\"maxyear\" value=\"\" />
             </td>\n</tr>\n");

// DESCRIPTION
echo("<tr>\n
      <td class=\"fieldname\">". LangQueryObservationsMessage2 . "</td><td>
      <input type=\"text\" class=\"inputfield\" maxlength=\"40\" name=\"description\" size=\"35\" value=\"\" />&nbsp;
      </td>\n</tr>\n");


// MAXIMUM MAGNITUDE

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangQueryObjectsField4;

echo("</td>\n<td>\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxmag\" size=\"4\" value=\"\" />");

echo("</td>\n<td class=\"explanation\">" . LangQueryObjectsField4Explanation . "</td>\n</tr>\n");

// MINIMUM MAGNITUDE

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangQueryObjectsField3;

echo("</td>\n<td>\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"minmag\" size=\"4\" value=\"\" />");

echo("</td>\n<td>\n</td>\n</tr>\n");

/*

// Magnitude method

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObservationField15;

echo("</td>\n<td>\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"1\" name=\"methode\" size=\"1\" value=\"\" />");

echo("</td>\n</tr>\n");

// Magnitude reference chart

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangViewObservationField17;

echo("</td>\n<td>\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"chart\" size=\"2\" value=\"\" />");

echo("</td>\n</tr>\n");

// MINIMUM MAGNIFICATION

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangQueryCometObjectsField1;

echo("</td>\n<td>\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"minmagnification\" size=\"4\" value=\"\" />");

echo("&nbsp;&nbsp;<td class=\"explanation\"></td></tr>\n");

// MAXIMUM MAGNIFICATION

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangQueryCometObjectsField2;

echo("</td>\n<td>\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxmagnification\" size=\"4\" value=\"\" />");

echo("&nbsp;&nbsp;<td class=\"explanation\"></td></tr>\n");

// MAGNITUDE METHOD KEY

$ICQMETHODS = new ICQMETHOD();
$methods = $ICQMETHODS->getIds();

echo("<tr><td>" . LangNewComet5 . "</td>");

echo("<td>");

echo("<select name=\"methode\">\n");

echo("<option value=\"\"></option>\n\">"); // empty value

while(list($key, $value) = each($methods))
{
   echo("<option value=\"$value\">" . $value . " - " . $ICQMETHODS->getDescription($value) . "</option>\n");
}
echo("</select>\n");

echo("</td></tr>");

// MAGNITUDE REFERENCE KEY

$ICQREFERENCEKEYS = new ICQREFERENCEKEY();
$methods = $ICQREFERENCEKEYS->getIds();

echo("<tr><td>" . LangNewComet6 . "</td>");

echo("<td>");

echo("<select name=\"chart\">\n");

echo("<option value=\"\"></option>\n\">"); // empty value

while(list($key, $value) = each($methods))
{
   echo("<option value=\"$value\">" . $value . " - " . $ICQREFERENCEKEYS->getDescription($value) . "</option>\n");
}
echo("</select>\n");

echo("</td></tr>");

*/

// MINIMUM DC

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangQueryCometObjectsField3;

echo("</td>\n<td>\n");

echo("<select name=\"mindc\">");

echo("<option value=\"\"></option>");

for ($i = 1; $i <= 9; $i++) {
   echo("<option value=\"" . $i . "\">" . $i . "</option>\n");
}

echo("</select>");

echo("&nbsp;&nbsp;<td class=\"explanation\"></td></tr>\n");

// MAXIMUM DC

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangQueryCometObjectsField4;

echo("</td>\n<td>\n");

echo("<select name=\"maxdc\">");

echo("<option value=\"\"></option>");

for ($i = 1; $i <= 9; $i++) {
   echo("<option value=\"" . $i . "\">" . $i . "</option>\n");
}

echo("</select>");

echo("&nbsp;&nbsp;<td class=\"explanation\"></td></tr>\n");

// MINIMUM COMA

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangQueryCometObjectsField5;

echo("</td>\n<td>\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"mincoma\" size=\"4\" value=\"\" />");

echo("&nbsp;&nbsp;<td class=\"explanation\"></td></tr>\n");

// MAXIMUM COMA

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangQueryCometObjectsField6;

echo("</td>\n<td>\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxcoma\" size=\"4\" value=\"\" />");

echo("&nbsp;&nbsp;<td class=\"explanation\"></td></tr>\n");

// MINIMUM TAIL

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangQueryCometObjectsField7;

echo("</td>\n<td>\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"mintail\" size=\"4\" value=\"\" />");

echo("&nbsp;&nbsp;<td class=\"explanation\"></td></tr>\n");

// MAXIMUM TAIL

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangQueryCometObjectsField8;

echo("</td>\n<td>\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxtail\" size=\"4\" value=\"\" />");

echo("&nbsp;&nbsp;<td class=\"explanation\"></td></tr>\n");

/*

// MINIMUM PA

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangQueryCometObjectsField9;

echo("</td>\n<td>\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"minpa\" size=\"4\" value=\"\" />");

echo("&nbsp;&nbsp;<td class=\"explanation\"></td></tr>\n");

// MAXIMUM PA

echo("<tr>\n
      <td class=\"fieldname\">");

echo LangQueryCometObjectsField10;

echo("</td>\n<td>\n");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"maxpa\" size=\"4\" value=\"\" />");

echo("&nbsp;&nbsp;<td class=\"explanation\"></td></tr>\n");

*/

echo("<tr>\n<td>\n</td><td><input type=\"submit\" name=\"query\" value=\"" . LangQueryObservationsTitle . "\" />\n</td>\n<td></td></tr></form><form action=\"".$baseURL."index.php?indexAction=comets_query_observations\"><tr><td></td><td><input type=\"submit\" name=\"clear\" value=\"" . LangQueryObjectsButton2 . "\" /></td><td></td></tr></table>");

echo("\n</div>\n</body>\n</html>");
?>
