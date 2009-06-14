<?php // change_observation.php - allows a user to change his observation - Version 1.0 20051206, JV
include_once "lib/icqmethod.php";
include_once "lib/icqreferencekey.php";
if(!$_GET['observation']) // no observation defined 
{ header("Location: ../index.php");
}  
echo "<div id=\"main\">";
echo "<form action=\"comets/control/validate_change_observation.php\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<form action=\"".$baseURL."index.php\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"comets_validate_observation\" />";
$objPresentations->line(array("<h4>".LangChangeObservationTitle."</h4>","<input type=\"submit\" name=\"addobservation\" value=\"".LangViewObservationButton1."\" />&nbsp;"),"LR",array(50,50),30);

echo("<div id=\"main\">\n<h2>" . LangChangeObservationTitle . "</h2>");


echo("<table width=\"490\">\n
<tr>\n
<td class=\"fieldname\" width=\"100\">\n");

echo LangQueryObjectsField1;

echo("</td>\n<td>\n<a href=\"".$baseURL."index.php?indexAction=comets_detail_object&amp;object=" . urlencode($objCometObservation->getObjectId($_GET['observation'])) . "\">");

echo($objCometObject->getName($objCometObservation->getObjectId($_GET['observation'])));

echo("</a></td></tr>");

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

if ($objObserver->getObserverProperty($_SESSION['deepskylog_id'],'UT'))
{ $date = sscanf($objCometObservation->getDate($_GET['observation']), "%4d%2d%2d");
  $time = sscanf(sprintf("%04d", $objCometObservation->getTime($_GET['observation'])), "%2d%2d");
}
else
{ $date = sscanf($objCometObservation->getLocalDate($_GET['observation']), "%4d%2d%2d");
  $time = sscanf(sprintf("%04d", $objCometObservation->getLocalTime($_GET['observation'])), "%2d%2d");
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

if ($objObserver->getObserverProperty($_SESSION['deepskylog_id'],'UT'))
{
    echo("<tr><td class=\"fieldname\">" . LangViewObservationField9 . "</td><td><input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"hours\" value=\"");
}
else
{
    echo("<tr><td class=\"fieldname\">" . LangViewObservationField9lt . "</td><td><input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"hours\" value=\"");
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

// MAGNITUDE
echo("<tr><td class=\"fieldname\">" . LangNewComet1 . "&nbsp;");

echo("</td><td colspan=\"2\"><select name=\"smaller\">");

echo("<option value=\"0\">&nbsp;</option>\n");

echo("<option value=\"1\"");

if($objCometObservation->getMagnitudeWeakerThan($_GET['observation']) == "1")
{
   echo(" selected=\"selected\"");
}

echo(">". LangNewComet3 . "</option>\n</select>");
$magnitude = $objCometObservation->getMagnitude($_GET['observation']);
if ($magnitude < -90)
{
  $magnitude = "";
  echo("&nbsp;<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"mag\" size=\"4\" value=\"" . $magnitude . "\" />");
}
else
{
  echo("&nbsp;<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"mag\" size=\"4\" value=\"" . sprintf("%01.1f", $magnitude) . "\" />");
}

echo("<input type=\"checkbox\" name=\"uncertain\" ");

if($objCometObservation->getMagnitudeUncertain($_GET['observation']) == "1")
{
  echo("checked=\"yes\" ");
}

echo ("/>" . LangNewComet2 . "</td>");

echo("</tr>");

// LOCATION

print("<tr><td class=\"fieldname\">");

echo LangViewObservationField4;

echo("</td><td>

   <select name=\"location\">");

      $locs = $objLocation->getSortedLocationsList("name");

      while(list ($key, $value) = each($locs))
      {
         $locationname = $value[1];

         if($objCometObservation->getLocationId($_GET['observation']) == $value[0])
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

echo("<tr>
   <td class=\"fieldname\">");

echo("</td>
   <td>
   </td>
   </tr>");

// INSTRUMENT

echo("<tr><td class=\"fieldname\">");

echo LangViewObservationField3;

echo("</td><td>
   <select name=\"instrument\">");

      $instr = $objInstrument->getSortedInstrumentsList("name");

      echo("<option value=\"\"></option>\n"); // include empty instrument

      while(list ($key, $value) = each($instr))
      {
         $instrumentname = $value;
         $val = $key;

         if ($instrumentname == "Naked eye")
         {
          $instrumentname = InstrumentsNakedEye;
         }

         if($objCometObservation->getInstrumentId($_GET['observation']) == $val)
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

// MAGNIFICATION

echo("<tr><td>" . LangNewComet4 . "</td>");

echo("<td>");

echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"magnification\" size=\"3\" value=\"" . $objCometObservation->getMagnification($_GET['observation']) . "\" />");

echo("</td><td></td></tr>");

// MAGNITUDE METHOD KEY

echo("<tr><td>" . LangNewComet5 . "</td>");

echo("<td>");

echo("<select name=\"icq_method\">\n");

echo("<option value=\"\"></option>\n\">"); // empty value

$ICQMETHODS = new ICQMETHOD();
$methods = $ICQMETHODS->getIds();

while(list($key, $value) = each($methods))
{
   echo("<option value=\"$value\"");

   if($objCometObservation->getMethode($_GET['observation']) == $value)
   {
      print(" selected=\"selected\"");
   }

   echo(">" . $value . " - " . $ICQMETHODS->getDescription($value) . "</option>\n");
}
echo("</select>\n");

echo("</td><td><a href=\"http://cfa-www.harvard.edu/icq/ICQKeys.html\" rel=\"external\">" . LangNewComet7 . "</a></td></tr>");

// MAGNITUDE REFERENCE KEY

echo("<tr><td>" . LangNewComet6 . "</td>");

echo("<td>");

echo("<select name=\"icq_reference_key\">\n");

echo("<option value=\"\"></option>\n\">"); // empty value

$ICQREFERENCEKEYS = new ICQREFERENCEKEY();
$methods = $ICQREFERENCEKEYS->getIds();

while(list($key, $value) = each($methods))
{
   echo("<option value=\"$value\"");

   if($objCometObservation->getChart($_GET['observation']) == $value)
   {
      print(" selected=\"selected\"");
   }

   echo(">" . $value . " - " . $ICQREFERENCEKEYS->getDescription($value) . "</option>\n");
}
echo("</select>\n");

echo("</td><td><a href=\"http://cfa-www.harvard.edu/icq/ICQRec.html\" rel=\"external\">" . LangNewComet7 . "</a></td></tr>");

// DEGREE OF CONDENSATION

echo("<tr><td>" . LangNewComet8 . "</td>");

echo("<td>");

echo("<select name=\"condensation\">");

echo("<option value=\"\"></option>");

for ($i = 0; $i <= 9; $i++) {
   echo("<option value=\"" . $i . "\"");
   if(strcmp($objCometObservation->getDc($_GET['observation']), $i) == 0)
   { echo " selected=\"selected\""; }
   echo(">" . $i . "</option>\n");
}

echo("</select>");

echo("</td><td></td></tr>");

// COMA

echo("<tr><td>" . LangNewComet9 . "</td>");

echo("<td>");
$coma = $objCometObservation->getComa($_GET['observation']);
if ($coma < -90)
{
 $coma = '';
}
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"coma\" size=\"3\" value=\"" . $coma . "\" />");

echo("</td><td>(" . LangNewComet13 . ")</td></tr>");

// TAIL LENGTH

echo("<tr><td>" . LangNewComet10 . "</td>");

echo("<td>");
$tail = $objCometObservation->getTail($_GET['observation']);
if ($tail < -90)
{
 $tail = '';
}
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"tail_length\" size=\"3\" value=\"" . $tail . "\"  />");

echo("</td><td>(" . LangNewComet13 . ")</td></tr>");

// POSITION ANGLE TAIL

echo("<tr><td>" . LangNewComet11 . "</td>");

echo("<td>");
$pa = $objCometObservation->getPa($_GET['observation']);
if ($pa < -90)
{
 $pa = '';
}
echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"position_angle\" size=\"3\"  value=\"" . $pa . "\" />");

echo("</td><td>(" . LangNewComet12 . ")</td></tr>");

// DRAWING

    echo("<tr><td class=\"fieldname\">".LangViewObservationField12."</td><td><input type=\"file\" name=\"drawing\" /></td></td></tr>");

// DESCRIPTION

    echo("<tr><td class=\"fieldname\">" . LangViewObservationField8 . "</td><td></td><td></td></tr>");
    echo("<tr><td colspan=\"3\"><textarea name=\"description\" class=\"description\">" . $objPresentations->br2nl(html_entity_decode($objCometObservation->getDescription($_GET['observation']))) . "</textarea></td></tr>");

echo("<tr><td colspan=\"2\"><input type=\"submit\" name=\"changeobservation\" value=\"".LangChangeObservationButton."\" /></td></tr>");

echo("</table><input type=\"hidden\" name=\"observationid\" value=\"" . $_GET['observation'] . "\" /></form>");

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
   echo("<p><a href=\"comets/" . $upload_dir . "/" . $_GET['observation'] . ".jpg" . "\"><img class=\"account\" src=\"comets/$upload_dir" . "/" . "$file\" alt=\"\">
         </img></a></p>");
   }
}

if(isset($_GET['new']) && $_GET['new'] == "yes")
{
echo("<p><a href=\"".$baseURL."index.php?indexAction=comets_add_observation\">" . LangViewObservationNew . "</a></p>");
}

echo("</div></div></body></html>");

?>
