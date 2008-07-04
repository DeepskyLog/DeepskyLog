<?php

// setup_observations_query.php
// interface to query observations
// version 0.4, JV 20050203 
// version 3.1, DE 20061119

echo "	<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"" . $baseURL . "lib/javascript/CalendarPopupCC.js\"></SCRIPT>";
echo "	<SCRIPT LANGUAGE=\"JavaScript\">";
echo "	var cal = new CalendarPopup();";
echo "  function SetMultipleValuesFromDate(y,m,d)";
echo "  {";
echo "    document.forms['ObservationsQueryForm'].minday.value = d;";
echo "    document.forms['ObservationsQueryForm'].minmonth.value = m;";
echo "    document.forms['ObservationsQueryForm'].minyear.value = y;";													 
echo "	}";
echo "  function SetMultipleValuesTillDate(y,m,d)";
echo "  {";
echo "    document.forms['ObservationsQueryForm'].maxday.value = d;";
echo "    document.forms['ObservationsQueryForm'].maxmonth.value = m;";
echo "    document.forms['ObservationsQueryForm'].maxyear.value = y;";													 
echo "	}";
echo "	</SCRIPT>";

include_once "../lib/observations.php";
include_once "../lib/objects.php";
include_once "../lib/observers.php";
include_once "../lib/instruments.php";
include_once "../lib/locations.php";
include_once "../lib/util.php";

$objects = new Objects; 
$observations = new Observations;
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


echo("<table width=\"100%\">\n");

echo("<tr>");
echo("<td>");
echo("<form action=\"deepsky/index.php\">
      <input type=\"hidden\" name=\"indexAction\" value=\"query_observations\">
      <input type=\"submit\" name=\"clear\" value=\"" . LangQueryObservationsButton2 . "\" />");
echo("</form>");
echo("</td><td>");
echo("<form action=\"deepsky/index.php\" method=\"get\" name=\"ObservationsQueryForm\">\n");
echo("<input type=\"hidden\" name=\"indexAction\" value=\"result_selected_observations\">");
echo("<td align=\"right\" width=\"25%\">" . LangSeen . "</td><td width=\"25%\">");
echo("<select name=\"seen\">");
echo("<option selected value=\"D\">" . LangSeenDontCare . "</option>");
if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
{
  echo("<option value=\"X\">" . LangSeenSomeoneElse . "</option>".
		   "<option value=\"Y\">" . LangSeenByMe . "</option>");
}
echo("</select>");
echo("</td>");
echo("<td align=\"centre\" width=\"25%\"><input type=\"submit\" name=\"query\" value=\"" . LangQueryObservationsButton1 . "\" />\n</td>");
echo("<td>");
echo("</tr>");

echo("</table>");
echo("<hr>");
echo("<table width=\"100%\">");

echo("<tr>");
// OBJECT NAME 
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangViewObservationField1;
echo("</td>\n<td width=\"25%\">\n");
echo("<select name=\"catalogue\">\n");
echo("<option value=\"\"></option>"); // empty field
$catalogs = $objects->getCatalogues(); // should be sorted
while(list($key, $value) = each($catalogs))
  echo("<option value=\"$value\">$value</option>\n");
echo("</select>\n");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"255\" name=\"number\" size=\"40\" value=\"\" />");
echo("</td>\n");
// ATLAS PAGE NUMBER
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangQueryObjectsField12;
echo("</td>\n<td>\n");
echo("<select name=\"atlas\">\n");
echo("<option value=\"\"></option>"); // empty field
echo("<option value=\"msa\">".LangQueryObjectsMsa."</option>\n");
echo("<option value=\"sky\">".LangQueryObjectsSkyAtlas."</option>\n");
echo("<option value=\"taki\">".LangQueryObjectsTaki."</option>\n");
echo("<option value=\"urano\">".LangQueryObjectsUrano."</option>\n");
echo("<option value=\"uranonew\">".LangQueryObjectsUranonew."</option>\n");
echo("</select>\n");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"page\" size=\"4\" value=\"\" />");
echo("</td>");
echo("</tr>\n");

echo("<tr>");
// OBJECT CONSTELLATION
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangQueryObjectsField2;
echo("</td>\n<td>\n");
echo("<select name=\"con\">\n");
echo("<option value=\"\"></option>"); // empty field
$constellations = $objects->getConstellations(); // should be sorted
while(list($key, $value) = each($constellations))
  $cons[$value] = $$value;
asort($cons);
reset($cons);
while(list($key, $value) = each($cons))
  echo("<option value=\"$key\">".$value."</option>\n");
echo("</select>\n");
echo("</td>");
// MINIMUM DECLINATION
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangQueryObjectsField9;
echo("</td>\n<td>\n");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"minDeclDegrees\" size=\"3\" value=\"\" />&nbsp;&deg;&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minDeclMinutes\" size=\"2\" value=\"\" />&nbsp;&#39;&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minDeclSeconds\" size=\"2\" value=\"\" />&nbsp;&quot;&nbsp;");
echo("</td>");
echo("</tr>");

echo("<tr>");
// OBJECT TYPE
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangQueryObjectsField11;
echo("</td>\n<td>\n");
echo("<select name=\"type\">\n");
echo("<option value=\"\"></option>"); // empty field
$types = $objects->getTypes();
while(list($key, $value) = each($types))
  $stypes[$value] = $$value;
asort($stypes);
while(list($key, $value) = each($stypes))
  echo("<option value=\"$key\">".$value."</option>\n");
echo("</select>\n");
echo("</td>");
// MAXIMUM DECLINATION
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangQueryObjectsField10;
echo("</td>\n<td>\n");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"maxDeclDegrees\" size=\"3\" value=\"\" />&nbsp;&deg;&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxDeclMinutes\" size=\"2\" value=\"\" />&nbsp;&#39;&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxDeclSeconds\" size=\"2\" value=\"\" />&nbsp;&quot;&nbsp;");
echo("</td>");echo("</tr>");



echo("<tr>");
// MAXIMUM MAGNITUDE
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangQueryObjectsField4;
echo("</td>\n<td width=\"25%\">\n");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxmag\" size=\"4\" value=\"\" />");
echo("</td>");
// MINIMUM RIGHT ASCENSION
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangQueryObjectsField7;
echo("</td>\n<td>\n");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minRAhours\" size=\"2\" value=\"\" />&nbsp;h&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minRAminutes\" size=\"2\" value=\"\" />&nbsp;m&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minRAseconds\" size=\"2\" value=\"\" />&nbsp;s&nbsp;");
echo("</td>");
echo("</tr>");

echo("<tr>");
// MINIMUM MAGNITUDE
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangQueryObjectsField3;
echo("</td>\n<td width=\"25%\">\n");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"minmag\" size=\"4\" value=\"\" />");
echo("</td>");
// MAXIMUM RIGHT ASCENSION
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangQueryObjectsField8;
echo("</td>\n<td>\n");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxRAhours\" size=\"2\" value=\"\" />&nbsp;h&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxRAminutes\" size=\"2\" value=\"\" />&nbsp;m&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxRAseconds\" size=\"2\" value=\"\" />&nbsp;s&nbsp;");
echo("</td>");
echo("</tr>");

echo("<tr>");
// MINIMIM SURFACE BRIGHTNESS
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangQueryObjectsField5;
echo("</td>\n<td>\n");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"minsb\" size=\"4\" value=\"\" />");
echo("</td>");

// MINIMIM SIZE
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangQueryObjectsField13;
echo("</td>\n<td>\n");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"minsize\" size=\"4\" value=\"\" />");
echo("&nbsp;&nbsp;<select name=\"size_min_units\"><option></option><option value=\"min\">" . LangNewObjectSizeUnits1 . "</option><option value=\"sec\">" . LangNewObjectSizeUnits2 . "</option></select>\n</td>");
echo("</tr>\n");

echo("<tr>");
// MAXIMUM SURFACE BRIGHTNESS
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangQueryObjectsField6;
echo("</td>\n<td>\n");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxsb\" size=\"4\" value=\"\" />");
echo("</td>");
// MAXIMUM SIZE
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangQueryObjectsField14;
echo("</td>\n<td>\n");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxsize\" size=\"4\" value=\"\" />");
echo("&nbsp;&nbsp;<select name=\"size_max_units\"><option></option><option value=\"min\">" . LangNewObjectSizeUnits1 . "</option><option value=\"sec\">" . LangNewObjectSizeUnits2 . "</option></select>\n</td>");
echo("</tr>\n");

echo("</table>");
echo("<hr>");
echo("<table width=\"100%\">");

echo("<tr>");
// OBSERVER 
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangViewObservationField2;
echo("</td>\n<td width=\"25%\">\n");
echo("<select name=\"observer\">\n");
echo("<option value=\"\"></option>"); // empty field
//$obs = $observers->getSortedObservers('name'); 
$obs = $observations->getPopularObservers();
while(list($key) = each($obs))
  $sortobs[$key] = $observers->getObserverName($key)." ".$observers->getFirstName($key);
natcasesort($sortobs);
while(list($value, $key) = each($sortobs))
   echo("<option value=\"$value\">".$key."</option>\n");
echo("</select>\n");
echo("</td>");
// INSTRUMENT 
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangViewObservationField3;
echo("</td>\n<td>\n");
echo("<select name=\"instrument\">\n");
echo("<option value=\"\"></option>"); // empty field
$inst = $instruments->getSortedInstrumentsList('name', '', true, InstrumentsNakedEye);
while(list($key, $value) = each($inst))
  echo("<option value=\"".$value[0]."\">".$value[1]."</option>\n");
echo("</select>\n");
echo("</td>");
echo("</tr>");

echo("<tr>");
// MINIMUM DATE
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo("<a href=\"#\" onclick=\"cal.showNavigationDropdowns();
                             cal.setReturnFunction('SetMultipleValuesFromDate');
														 cal.showCalendar('FromDateAnchor');
                             return false;\" 
									 name=\"FromDateAnchor\" 
									 id=\"FromDateAnchor\">" . LangFromDate . "</a>"); 
echo("</td>");
echo("<td>");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"minday\" value=\"\" />");
echo("&nbsp;");
echo("<select name=\"minmonth\">
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
             </select>");
echo("&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" name=\"minyear\" value=\"\" />");
echo("</td>");
// MINIMUM DIAMETER
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangViewObservationField13;
echo("</td>\n
      <td>\n
      <input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"mindiameter\" size=\"10\" />
      <select name=\"mindiameterunits\"><option selected=\"selected\"></option><option>inch</option><option>mm</option></select>
      </td>");
echo("</tr>");

echo("<tr>");
// MAXIMUM DATE
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo("<a href=\"#\" onclick=\"cal.showNavigationDropdowns();
                             cal.setReturnFunction('SetMultipleValuesTillDate');
														 cal.showCalendar('TillDateAnchor');
                             return false;\" 
									 name=\"TillDateAnchor\" 
									 id=\"TillDateAnchor\">" . LangTillDate . "</a>"); 
echo("</td>");
echo("<td>");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"maxday\" value=\"\" />");
echo("&nbsp;");
echo("<select name=\"maxmonth\">
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
             </select>");
echo("&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" name=\"maxyear\" value=\"\" />");
echo("</td>");
// MAXIMUM DIAMETER
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangViewObservationField14;
echo("</td>\n
      <td>\n
      <input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"maxdiameter\" size=\"10\" />
      <select name=\"maxdiameterunits\"><option selected=\"selected\"></option><option>inch</option><option>mm</option></select>
      </td>");
echo("</tr>");

echo("</table>");
echo("<hr>");
echo("<table width=\"100%\">");


echo("<tr>");
// SITE 
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangViewObservationField4;
echo("</td>\n<td width=\"25%\">\n");
echo("<select name=\"site\">\n");
echo("<option value=\"\"></option>"); // empty field
$sites = $locations->getSortedLocations('name', '', true);
while(list($key, $value) = each($sites))
  if($key != 0) // remove empty location in database
    echo("<option value=\"$value\">".$locations->getLocationName($value)."</option>\n");
echo("</select>\n");
echo("</td>");
echo("<td width=\"25%\"> &nbsp </td> <td width=\"25%\"> &nbsp</td>"); 
echo("</tr>");

echo("<tr>");
// MINIMUM Latitude
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangQueryObjectsField15;
echo("</td>\n<td width=\"25%\">\n");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"minLatDegrees\" size=\"3\" value=\"\" />&nbsp;&deg;&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minLatMinutes\" size=\"2\" value=\"\" />&nbsp;&#39;&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minLatSeconds\" size=\"2\" value=\"\" />&nbsp;&quot;&nbsp;");
echo("</td>");
// MINIMUM LIMITING MAGNITUDE
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangViewObservationField25;
echo("</td>\n<td width=\"25%\">\n");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"minlimmag\" size=\"4\" value=\"\" />");
echo("</td>");
echo("</tr>");

echo("<tr>");
// MAXIMUM latitude
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangQueryObjectsField16;
echo("</td>\n<td width=\"25%\">\n");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"maxLatDegrees\" size=\"3\" value=\"\" />&nbsp;&deg;&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxLatMinutes\" size=\"2\" value=\"\" />&nbsp;&#39;&nbsp;");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxLatSeconds\" size=\"2\" value=\"\" />&nbsp;&quot;&nbsp;");
echo("</td>");
// MAXIMUM LIMITING MAGNITUDE
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangViewObservationField26;
echo("</td>\n<td width=\"25%\">\n");
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"maxlimmag\" size=\"4\" value=\"\" />");
echo("</td>");
echo("</tr>");

echo("<tr>");
// MINIMUM SEEING
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangViewObservationField27;
echo("</td>\n<td width=\"25%\">\n");
echo("<select name=\"minseeing\"><option value=\"\"></option>");
// EXCELLENT
echo("<option value=\"1\">".SeeingExcellent."</option>");
// GOOD
echo("<option value=\"2\">".SeeingGood."</option>");
// MODERATE
echo("<option value=\"3\">".SeeingModerate."</option>");
// POOR
echo("<option value=\"4\">".SeeingPoor."</option>");
// BAD
echo("<option value=\"5\">".SeeingBad."</option>");
echo("</select></td>");
// MAXIMUM SEEING
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangViewObservationField28;
echo("</td>\n<td width=\"25%\">\n");
echo("<select name=\"maxseeing\"><option value=\"\"></option>");
// EXCELLENT
echo("<option value=\"1\">".SeeingExcellent."</option>");
// GOOD
echo("<option value=\"2\">".SeeingGood."</option>");
// MODERATE
echo("<option value=\"3\">".SeeingModerate."</option>");
// POOR
echo("<option value=\"4\">".SeeingPoor."</option>");
// BAD
echo("<option value=\"5\">".SeeingBad."</option>");
echo("</select></td>");
echo("</tr>");

echo("</table>");
echo("<hr>");
echo("<table width=\"100%\">");

// DRAWINGS
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">". LangQueryObservationsMessage1 . "</td>");
echo("<td width=\"25%\"><input type=\"checkbox\" class=\"inputfield\" name=\"drawings\" /></input></td>");
// MINIMUM VISIBILITY
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangViewObservationField23;
echo("</td>\n
      <td>\n
      <select name=\"minvisibility\"><option value=\"\"></option>");
// Very simple, prominent object
echo("<option value=\"1\">".LangVisibility1."</option>");
// Object easily percepted with direct vision
echo("<option value=\"2\">".LangVisibility2."</option>");
// Object perceptable with direct vision
echo("<option value=\"3\">".LangVisibility3."</option>");
// Averted vision required to percept object
echo("<option value=\"4\">".LangVisibility4."</option>");
// Object barely perceptable with averted vision
echo("<option value=\"5\">".LangVisibility5."</option>");
// Perception of object is very questionable
echo("<option value=\"6\">".LangVisibility6."</option>");
// Object definitely not seen
echo("<option value=\"7\">".LangVisibility7."</option>");
echo("</select></td>");
echo("</tr>");

echo("<tr>");
// DESCRIPTION
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">". LangQueryObservationsMessage2 . "</td><td width=\"25%\">
      <input type=\"text\" class=\"inputfield\" maxlength=\"40\" name=\"description\" size=\"35\" value=\"\" />&nbsp;
      </td>");
// MAXIMUM VISIBILITY
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo LangViewObservationField24;
echo("</td>\n
      <td>\n
      <select name=\"maxvisibility\"><option value=\"\"></option>");
// Very simple, prominent object
echo("<option value=\"1\">".LangVisibility1."</option>");
// Object easily percepted with direct vision
echo("<option value=\"2\">".LangVisibility2."</option>");

// Object perceptable with direct vision
echo("<option value=\"3\">".LangVisibility3."</option>");
// Averted vision required to percept object
echo("<option value=\"4\">".LangVisibility4."</option>");
// Object barely perceptable with averted vision
echo("<option value=\"5\">".LangVisibility5."</option>");
// Perception of object is very questionable
echo("<option value=\"6\">".LangVisibility6."</option>");
// Object definitely not seen
echo("<option value=\"7\">".LangVisibility7."</option>");
echo("</select></td><td></td>");
echo("</tr>");

echo("<tr>");
echo("<td class=\"fieldname\" align=\"right\" width=\"25%\">");
echo(LangChangeVisibleLanguages);
echo("</td>");

 $language = new Language;
 $obs = new Observers;

 if (array_key_exists('deepskylog_id',$_SESSION) && ($_SESSION['deepskylog_id']))
 {
  $allLanguages = $language->getAllLanguages($obs->getLanguage($_SESSION['deepskylog_id']));
  $_SESSION['alllanguages'] = $allLanguages; 
  $usedLanguages = $obs->getUsedLanguages($_SESSION['deepskylog_id']);
 }
 else
 {
  $allLanguages = $language->getAllLanguages($_SESSION['lang']);
  $_SESSION['alllanguages'] = $allLanguages; 
  $usedLanguages = $language->getLanguageKeys($_SESSION['lang']);
 }

 $j=0;
 while(list ($key, $value) = each($allLanguages))
 {
   echo("<td><input type=\"checkbox\" ");
   for ($i = 0;$i < count($usedLanguages);$i++)
   {
     if ($key == $usedLanguages[$i])
     {
       echo("checked ");
     }
   }
   echo ("name=\"" . $key . "\" value=\"" . $key . "\" />". $value);
	 $j++;
	 if ($j==3)
	 {
	   $j=0;
	   echo("</tr><tr><td></td>");
	 } 
 }
 print("</tr>");

echo("</table>");
echo("\n</div>\n</body>\n</html>");
?>
