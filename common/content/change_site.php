<?php

// change_site.php
// allows the administrator to change site details 
// version 0.1: JV 20041126

include_once "../lib/observers.php";
include_once "../lib/locations.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

$locations = new Locations();

echo("<div id=\"main\">
   \n<h2>");

echo stripslashes($locations->getName($_GET['location']));

echo("</h2>

   <form action=\"common/control/validate_site.php\" method=\"post\">
   <table>
   <tr>
   <td class=\"fieldname\">");

echo(LangAddSiteField1);

echo("</td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"sitename\" size=\"30\" value=\"");

echo stripslashes($locations->getName($_GET['location']));

echo("\" /></td>
   <td class=\"explanation\"></td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangAddSiteField2);

echo("</td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"region\" size=\"30\" value=\"");

echo stripslashes($locations->getRegion($_GET['location']));

echo("\" /></td>
   <td class=\"explanation\">");

echo(LangAddSiteField2Expl);

echo("</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangAddSiteField3);

echo("</td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"country\" size=\"30\" value=\"");

echo $locations->getCountry($_GET['location']);

echo("\" /></td>
   <td class=\"explanation\">");

echo(LangAddSiteField3Expl);

echo("</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangAddSiteField4);

echo("</td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"latitude\" size=\"3\" value=\"");

$latitudestr = $locations->getLatitude($_GET['location']);
$latitudedeg = (int)($latitudestr);
$latitudemin = round(((float)($latitudestr) - (int)($latitudestr)) * 60);

echo $latitudedeg . "\" />&deg;<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"latitudemin\" size=\"2\" value=\"";
echo $latitudemin . "\" />&#39;</td>";

echo("</td>
   <td class=\"explanation\">");

echo(LangAddSiteField4Expl);

echo("</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangAddSiteField5);

echo("</td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"longitude\" size=\"4\" value=\"");


$longitudestr = $locations->getLongitude($_GET['location']);
$longitudedeg = (int)($longitudestr);
$longitudemin = round(((float)($longitudestr) - (int)($longitudestr)) * 60);

echo $longitudedeg . "\" />&deg;<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"longitudemin\" size=\"2\" value=\"";
echo $longitudemin . "\" />&#39;</td>";

echo("</td>
   <td class=\"explanation\">");

echo(LangAddSiteField5Expl);

echo("</td>
   </tr>
   <tr>
   <td class=\"fieldname\">" . LangAddSiteField6 . "</td>
   <td>");

$timezone_identifiers = DateTimeZone::listIdentifiers();

echo("<select name=\"timezone\">");

while(list ($key, $value) = each($timezone_identifiers))
{
  if ($value == $locations->getTimezone($_GET['location']))
  {
    echo("<option value=\"$value\" selected>$value</option>\n");
  }
  else
  {
    echo("<option value=\"$value\">$value</option>\n");
  }
}

echo("</select>");

$lm = $locations->getLimitingMagnitude($_GET['location']);
$sb = $locations->getSkyBackground($_GET['location']);

echo("</td>
   </tr>

   <tr>
   <td class=\"fieldname\">" . LangAddSiteField7 . "
   </td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"lm\" size=\"5\" value=\"");

   if ($lm > -900) 
   {
      echo ($lm);
   } else {
      echo ("");
   }
   echo ("\">
   </td>
   <td class=\"explanation\">" . LangAddSiteField7Expl . "</td>
   </tr>

   <tr>
   <td class=\"fieldname\">" . LangAddSiteField8 . "</td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"sb\" size=\"5\" value=\"");

   if ($sb > -900) 
   {
      echo ($sb);
   } else {
      echo ("");
   }
   echo ("\">
   </td>
   <td class=\"explanation\">" . LangAddSiteField8Expl . "</td>
   </tr>

   <tr>
   <td></td>
   <td><input type=\"submit\" name=\"change\" value=\"");

echo (LangAddSiteButton2);

echo("\" /><input type=\"hidden\" name=\"id\" value=\"");

echo ($_GET['location']);

echo("\"></input></td>
   <td></td>
   </tr>
   </table>
   </form>");

echo("</div>
</div>
</body>
</html>");

?>
