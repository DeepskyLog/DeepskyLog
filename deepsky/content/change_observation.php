<?php

// adapt_observation.php
// allows a user to change his observation 

if (!function_exists('fnmatch')) 
{
   function fnmatch($pattern, $string)
	 {
      return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
   }
}

include_once "../lib/observations.php"; // observation table
$observations = new Observations;

include_once "../lib/instruments.php"; // instruments table
$instruments = new Instruments;

include_once "../lib/locations.php"; // locations table
$locations = new Locations;

include_once "../lib/observers.php"; // observers table
$observers = new Observers;

include_once "../lib/util.php";
$util = new Util;
$util->checkUserInput();

if(!$_GET['observation']) // no observation defined 
{
   header("Location: ../index.php");
}  


echo("<div id=\"main\">\n<h2>" . LangChangeObservationTitle . "</h2>");

echo("<form action=\"deepsky/control/validate_change_observation.php\" method=\"post\" enctype=\"multipart/form-data\">");

echo("<table width=\"490\">\n
<tr>\n
<td class=\"fieldname\" width=\"100\">\n");

echo LangViewObservationField1;

echo("</td>\n<td>\n<a href=\"deepsky/index?indexAction=detail_object&object=" . $observations->getObjectId($_GET['observation']) . "\">");

echo($observations->getObjectId($_GET['observation']));

echo("</a></td></tr>");

echo("<tr><td class=\"fieldname\">");

echo LangViewObservationField2;

echo("</td><td><a href=\"common/detail_observer.php?user=" . $observations->getObserverId($_GET['observation']) . "\">");

echo($observers->getFirstName($observations->getObserverId($_GET['observation'])) . "&nbsp;" . $observers->getObserverName($observations->getObserverId($_GET['observation'])));

print("</a></td></tr>");

print ("<tr>
   <td class=\"fieldname\">");

echo LangViewObservationField5;

echo("</td>
   <td>");

if ($observers->getUseLocal($_SESSION['deepskylog_id']))
{
  $date = sscanf($observations->getDsObservationLocalDate($_GET['observation']), "%4d%2d%2d");
  $timestr = $observations->getDsObservationLocalTime($_GET['observation']);
}
else
{
  $date = sscanf($observations->getDateDsObservation($_GET['observation']), "%4d%2d%2d");
  $timestr = $observations->getTime($_GET['observation']);
}

if ($timestr >= 0)
{
 $time = sscanf(sprintf("%04d", $timestr), "%2d%2d");
}
else
{
 $time[0] = -9;
 $time[1] = -9;
}

   echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"day\" value=\"" . $date[2] . "\" />&nbsp;&nbsp;<select name=\"month\">

             <option value=\"\"></option>");

echo("<option value=\"1\" ");

if($date[1] == 1)
{
   echo("selected=\"selected\"");
}
echo(">" . LangNewObservationMonth1 . "</option>");
echo("<option value=\"2\" ");
if($date[1] == 2)
{
   echo("selected=\"selected\"");
}
echo(">" . LangNewObservationMonth2 . "</option>");
echo("<option value=\"3\" ");
if($date[1] == 3)
{
   echo("selected=\"selected\"");
}
echo(">" . LangNewObservationMonth3 . "</option>");
echo("<option value=\"4\" ");
if($date[1] == 4)
{
   echo("selected=\"selected\"");
}
echo(">" . LangNewObservationMonth4 . "</option>");
echo("<option value=\"5\" ");
if($date[1] == 5)
{
   echo("selected=\"selected\"");
}
echo(">" . LangNewObservationMonth5 . "</option>");
echo("<option value=\"6\" ");
if($date[1] == 6)
{
   echo("selected=\"selected\"");
}
echo(">" . LangNewObservationMonth6 . "</option>");
echo("<option value=\"7\" ");
if($date[1] == 7)
{
   echo("selected=\"selected\"");
}
echo(">" . LangNewObservationMonth7 . "</option>");
echo("<option value=\"8\" ");
if($date[1] == 8)
{
   echo("selected=\"selected\"");
}
echo(">" . LangNewObservationMonth8 . "</option>");
echo("<option value=\"9\" ");
if($date[1] == 9)
{
   echo("selected=\"selected\"");
}
echo(">" . LangNewObservationMonth9 . "</option>");
echo("<option value=\"10\" ");
if($date[1] == 10)
{
   echo("selected=\"selected\"");
}
echo(">" . LangNewObservationMonth10 . "</option>");
echo("<option value=\"11\" ");
if($date[1] == 11)
{
   echo("selected=\"selected\"");
}
echo(">" . LangNewObservationMonth11 . "</option>");
echo("<option value=\"12\" ");
if($date[1] == 12)
{
   echo("selected=\"selected\"");
}
echo(">" . LangNewObservationMonth12 . "</option>");

// loop to be worked out
// should replace rubbish code above

/*
for($i = 1; $i < 13; $i++)
{ 
$month = "LangNewObservationMonth" . $i; 
echo("<option value=\"" . $i . "\"");
if($date[1] == $i)
{
   echo (" selected=\"selected\">" . $month . "</option>");
}
else
{
   echo (">" . LangNewObservationMonth . $i . "</option>");
}
}
*/
  
echo("</select>&nbsp;&nbsp<input type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" name=\"year\" value=\"" . $date[0] . "\" /></td></tr>");

if ($observers->getUseLocal($_SESSION['deepskylog_id']))
{
    echo("<tr><td class=\"fieldname\">" . LangViewObservationField9lt . "</td><td><input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"hours\" value=\"");
}
else
{
    echo("<tr><td class=\"fieldname\">" . LangViewObservationField9 . "</td><td><input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"hours\" value=\"");
}

if($time[0] > 0)
{
   echo $time[0];
}
else if ($time[0] == 0)
{
   echo "0";
}

echo("\" />&nbsp;&nbsp;<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"minutes\" value=\"");

if($time[1] > 0)
{
   echo $time[1];
}
else if ($time[1] == 0)
{
   echo "0";
}

echo("\" />");

echo("</td>
   </tr>");

echo ("<tr><td class=\"fieldname\">");

echo LangViewObservationField4;
// LOCATION
echo("</td><td>

   <select name=\"location\">");

      $locs = $locations->getSortedLocationsList("name", $_SESSION['deepskylog_id']);

      while(list ($key, $value) = each($locs))
      {
         $locationname = $value[1];

         if($observations->getDsObservationLocationId($_GET['observation']) == $value[0])
         {
            print("<option selected=\"selected\" value=\"".$value[0]."\">$locationname</option>\n");
         }
         else
         {
            print("<option value=\"".$value[0]."\">$locationname</option>\n");
         }
      }

echo("</select>");

print("</td>
   </tr>");

// INSTRUMENTS
echo("<tr><td class=\"fieldname\">");

echo LangViewObservationField3;

echo("</td><td>
   <select name=\"instrument\">");

      $instr = $instruments->getSortedInstrumentsList("name", $_SESSION['deepskylog_id'], false, InstrumentsNakedEye);

 
      while(list ($key, $value) = each($instr))
      {
         $instrumentname = $value[1];
         $val = $value[0];

         if ($instrumentname == "Naked eye")
         {
          $instrumentname = InstrumentsNakedEye;
         }

         if($observations->getDsObservationInstrumentId($_GET['observation']) == $val)
         {
            print("<option selected=\"selected\" value=\"$val\">$instrumentname</option>\n");
         }
         else
         {
            print("<option value=\"$val\">$instrumentname</option>\n");
         }
      }

echo("</select>");

print("</td></tr>");

  // EYEPIECE
  // create object
  include_once "../lib/eyepieces.php";
  $eyepieces = new Eyepieces;
  echo("<td class=\"fieldname\">" . LangViewObservationField30 . "&nbsp;</td><td>
        <select name=\"eyepiece\">\n");
  echo("<option value=\"\"></option>\n"); // include empty instrument
  $eyeps = $eyepieces->getSortedEyepiecesList("name", $_SESSION['deepskylog_id'], false);

  while(list ($key, $value) = each($eyeps)) // go through eyepiece array
  {

    $eyepiecename = $eyepieces->getEyepieceName($value);
    $val = $value;
    if($observations->getDsObservationEyepieceId($_GET['observation']) == $val)
    {
      print("<option selected=\"selected\" value=\"$val\">");
    }
    else
    {
      print("<option value=\"$val\">");
    }

    echo("$eyepiecename</option>\n");
  }
  echo("</select></td></tr>");

  // FILTER
  // create object
  include_once "../lib/filters.php";
  $filters = new Filters;
  echo("<td class=\"fieldname\">" . LangViewObservationField31 . "&nbsp;</td><td>
        <select name=\"filter\">\n");
  echo("<option value=\"\"></option>\n"); // include empty filter
  $filts = $filters->getSortedFiltersList("name", $_SESSION['deepskylog_id'], false);

  while(list ($key, $value) = each($filts)) // go through instrument array
  {

    $filtername = $filters->getFilterName($value);
    $val = $value;
    if($observations->getDsObservationFilterId($_GET['observation']) == $value)
    {
      print("<option selected=\"selected\" value=\"$val\">");
    }
    else
    {
      print("<option value=\"$val\">");
    }

    echo("$filtername</option>\n");
  }
  echo("</select></td></tr>");

  // LENS
  // create object
  include_once "../lib/lenses.php";
  $lenses = new Lenses;
  echo("<td class=\"fieldname\">" . LangViewObservationField32 . "&nbsp;</td><td>
        <select name=\"lens\">\n");
  echo("<option value=\"\"></option>\n"); // include empty lens
  $lns = $lenses->getSortedLensesList("name", $_SESSION['deepskylog_id'], false);

  while(list ($key, $value) = each($lns)) // go through instrument array
  {

    $lensname = $lenses->getLensName($value);
    $val = $value;
    if($observations->getDsObservationLensId($_GET['observation']) == $value)
    {
      print("<option selected=\"selected\" value=\"$val\">");
    }
    else
    {
      print("<option value=\"$val\">");
    }

    echo("$lensname</option>\n");
  }
  echo("</select></td></tr>");

 // SEEING
$seeing = $observations->getSeeing($_GET['observation']);

echo("<tr><td class=\"fieldname\">" . LangViewObservationField6 . "</td><td>");

echo("<select name=\"seeing\">"); 

echo("<option value=\"\"></option>");

echo("<option value=\"1\" ");
  
   if($seeing == 1)
   {
   echo(" selected=\"selected\">" . SeeingExcellent . "</option>");
   }
   else
   {
   echo(">" . SeeingExcellent . "</option>");
   }
echo("<option value=\"2\" ");

   if($seeing == 2)
   {
   echo(" selected=\"selected\">" . SeeingGood . "</option>");
   }
   else
   {
   echo(">" . SeeingGood . "</option>");
   }
echo("<option value=\"3\" ");

   if($seeing == 3)
   {
   echo(" selected=\"selected\">" . SeeingModerate . "</option>");
   }
   else
   {
   echo(">" . SeeingModerate . "</option>");
   }
echo("<option value=\"4\" ");

   if($seeing == 4)
   {
   echo(" selected=\"selected\">" . SeeingPoor . "</option>");
   }
   else
   {
   echo(">" . SeeingPoor . "</option>");
   }
   echo("<option value=\"5\" ");

   if($seeing == 5)
   {
   echo(" selected=\"selected\">" . SeeingBad . "</option>");
   }
   else
   {
   echo(">" . SeeingBad . "</option>");
   }
   echo("</select></td>
         </tr>");

   echo("<tr><td class=\"fieldname\">".LangViewObservationField12."</td><td><input type=\"file\" name=\"drawing\" /></td><td></td></tr>");

    echo("<tr><td class=\"fieldname\">" . LangViewObservationField7 . "</td><td><input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"limit\" size=\"3\" value=\"");

if($observations->getLimitingMagnitude($_GET['observation']) != NULL)
{
   echo(sprintf("%1.1f", $observations->getLimitingMagnitude($_GET['observation'])));
}
echo ("\" /></td></tr>");


// Visibility

    $visibility = $observations->getVisibility($_GET['observation']);

    echo("<tr><td class=\"fieldname\">" . LangViewObservationField22 . "</td><td><select name=\"visibility\"><option value=\"0\"></option>");

    // Very simple, prominent object

    echo("<option value=\"1\"");
    if($visibility == 1)
    {
       echo " selected=\"selected\"";
    }
    echo (">".LangVisibility1."</option>");

    // Object easily percepted with direct vision

    echo("<option value=\"2\"");
    if($visibility == 2)
    {
       echo " selected=\"selected\"";
    }
    echo (">".LangVisibility2."</option>");

    // Object perceptable with direct vision

    echo("<option value=\"3\"");
    if($visibility == 3)
    {
       echo " selected=\"selected\"";
    }
    echo (">".LangVisibility3."</option>");

    // Averted vision required to percept object

    echo("<option value=\"4\"");
    if($visibility == 4)
    {
       echo " selected=\"selected\"";
    }
    echo (">".LangVisibility4."</option>");

    // Object barely perceptable with averted vision

    echo("<option value=\"5\"");
    if($visibility == 5)
    {
       echo " selected=\"selected\"";
    }
    echo (">".LangVisibility5."</option>");

    // Perception of object is very questionable

    echo("<option value=\"6\"");
    if($visibility == 6)
    {
       echo " selected=\"selected\"";
    }
    echo (">".LangVisibility6."</option>");

    // Object definitely not seen

    echo("<option value=\"7\"");
    if($visibility == 7)
    {
       echo " selected=\"selected\"";
    }
    echo (">".LangVisibility7."</option>");

    echo("</select></td><td></td></tr>");

   // Language of observation
   $lang = $observations->getDsObservationLanguage($_GET['observation']);

   echo("<td class=\"fieldname\">" . LangViewObservationField29 . "&nbsp;*</td><td>");

   $language = new Language(); 
   $allLanguages = $language->getAllLanguages($obs->getLanguage($_SESSION['deepskylog_id']));

   echo("<select name=\"description_language\">");

   while(list ($key, $value) = each($allLanguages))
   {
     if($lang == $key)
     {
       print("<option value=\"".$key."\" selected=\"selected\">".$value."</option>\n");
     }
     else 
     {
       print("<option value=\"".$key."\">".$value."</option>\n");
     }
   }

   echo("</select></td><td></td></tr>");



echo("<tr>
   <td class=\"fieldname\">");

echo LangViewObservationField8;

echo("</td></tr><tr><td colspan=\"2\"><textarea name=\"description\" class=\"description\">" . $util->br2nl(html_entity_decode($observations->getDescriptionDsObservation($_GET['observation']))) . "</textarea></td></tr>");

echo("</td></tr>");

echo("<tr><td colspan=\"2\"><input type=\"submit\" name=\"changeobservation\" value=\"".LangChangeObservationButton."\" /></td></tr>");

echo("</table><input type=\"hidden\" name=\"observationid\" value=\"" . $_GET['observation'] . "\"></input></form>");

$upload_dir = 'drawings';
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
   echo("<p><a href=\"deepsky/" . $upload_dir . "/" . $_GET['observation'] . ".jpg" . "\"><img class=\"account\" src=\"deepsky/$upload_dir" . "/" . "$file\">
         </img></a></p>");
   }
}

echo("</div></div></body></html>");

?>
