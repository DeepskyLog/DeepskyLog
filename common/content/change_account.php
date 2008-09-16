<?php

// change_account.php
// allows the user to view and change his account's details

if (!function_exists('fnmatch'))
{
   function fnmatch($pattern, $string) 
	 {
      return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
   }
}

include_once "../lib/observers.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

$obs = new Observers; 

// if(!$_SESSION['deepskylog_id']) {header("Location: ../index.php");}

	echo("<div id=\"main\">
\n<h2>" . LangChangeAccountTitle . "</h2>");

$upload_dir = 'observer_pics';
$dir = opendir($upload_dir);

while (FALSE !== ($file = readdir($dir)))
{
   if ("." == $file OR ".." == $file)
   {
   continue; // skip current directory and directory above
   }
   if(fnmatch($_SESSION['deepskylog_id']. ".gif", $file) || fnmatch($_SESSION['deepskylog_id']. ".jpg",
$file) || fnmatch($_SESSION['deepskylog_id']. ".png", $file))
   {
   echo("<p><img class=\"account\" src=\"common/$upload_dir" . "/" . "$file\" alt=\"" . $_SESSION
['deepskylog_id'] . "\"></img></p>");
   }
}

echo("<form class=\"content\" action=\"common/control/validate_account.php\" enctype=\"multipart/form-data\" method=\"post\">
<table width=\"490\">
<tr>
<td class=\"fieldname\">");

echo(LangChangeAccountField1);

echo("	</td>
	   <td>");

print($_SESSION['deepskylog_id']);

print("</td>
   <td class=\"explanation\">");

echo(LangChangeAccountField1Expl);

print("</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangChangeAccountField2."&nbsp;*");

echo("</td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"email\" size=\"25\" value=\"");

print($obs->getEmail($_SESSION['deepskylog_id']));

print("\" /></td>
   <td class=\"explanation\">");

echo(LangChangeAccountField2Expl);

echo("</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangChangeAccountField3."&nbsp;*");

echo("</td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"firstname\" size=\"25\" value=\"");

print($obs->getFirstName($_SESSION['deepskylog_id']));

print("\" /></td>
   <td class=\"explanation\">");

echo(LangChangeAccountField3Expl);

echo("</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangChangeAccountField4."&nbsp;*");

echo("</td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"name\" size=\"25\" value=\"");

print($obs->getObserverName($_SESSION['deepskylog_id']));

print("\" /></td>
   <td class=\"explanation\">");

echo(LangChangeAccountField4Expl);

echo("</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangChangeAccountField5."&nbsp;*");

echo("</td>
   <td><input type=\"password\" class=\"inputfield\" maxlength=\"64\" name=\"passwd\" size=\"25\" value=\"\" /></td>
   <td class=\"explanation\">");

echo(LangChangeAccountField5Expl);

echo("</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangChangeAccountField6."&nbsp;*");

echo("</td>
   <td><input type=\"password\" class=\"inputfield\" maxlength=\"64\" name=\"passwd_again\" size=\"25\" value=\"\" />");

echo(LangChangeAccountField6Expl);

echo("</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangChangeAccountField11."&nbsp;*");

echo("</td>
   <td><input type=\"checkbox\" class=\"inputfield\" name=\"local_time\"");

if ($obs->getUseLocal($_SESSION['deepskylog_id']))
{
  echo " checked";
}

echo(" />");
echo("</td><td>");

echo(LangChangeAccountField11Expl);

echo("</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangChangeAccountField10);

echo("</td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"icq_name\" size=\"5\" value=\"");

print($obs->getIcqName($_SESSION['deepskylog_id']));

print("\" /></td><td class=\"explanation\">");

echo(LangChangeAccountField10Expl);

echo("</td>
   <td class=\"explanation\"></td>
   </tr>
   <tr>
   <td>&nbsp;</td>
   <td>&nbsp;</td>
   <td>&nbsp;</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangChangeAccountField7);

echo("</td>
   <td>");

include_once "../lib/locations.php";
$locations = new Locations;

echo("<select name=\"site\">");

      $sites = $locations->getSortedLocations("name", $_SESSION['deepskylog_id']);

      // If there are locations with the same name, the province should alse 
      // be shown
      $previous = "fskfskf";

      for ($i = 0;$i < count($sites);$i++)
      {
       $adapt[$i] = 0;

       if ($locations->getLocationName($sites[$i]) == $previous)
       {
        $adapt[$i] = 1;
        $adapt[$i - 1] = 1;
       }
       $previous = $locations->getLocationName($sites[$i]);
      }

      for ($i = 0;$i < count($sites);$i++)
//      while(list ($key, $value) = each($sites))
      {
         if ($adapt[$i])
         {
          $sitename = $locations->getLocationName($sites[$i])." (".$locations->getRegion($sites[$i]).")";
         }
         else
         {
          $sitename = $locations->getLocationName($sites[$i]);
         }

         if($obs->getStandardLocation($_SESSION['deepskylog_id']) == $sites[$i])
         {
            print("<option selected=\"selected\" value=\"$sites[$i]\">$sitename</option>\n");
         }
         else
         {
            print("<option value=\"$sites[$i]\">$sitename</option>\n");
         }
      }


print("
   </select>
   </td>
   <td class=\"explanation\"><a href=\"common/add_site.php\">");

echo(LangChangeAccountField7Expl);

include_once "../lib/instruments.php";
$instruments = new Instruments;

echo("</a></td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangChangeAccountField8);

echo("</td>
   <td>
   <select name=\"instrument\">");

      $instr = $instruments->getSortedInstruments("name", $_SESSION['deepskylog_id']);
 
		  $noStd = false;
      while(list ($key, $value) = each($instr))
      {
         $instrumentname = $instruments->getInstrumentName($value);
         if ($instrumentname == "Naked eye")
         {
          $instrumentname = InstrumentsNakedEye;
         }

         if($obs->getStandardTelescope($_SESSION['deepskylog_id']) == "0")
         {
          $noStd = 1;
         }

         if($obs->getStandardTelescope($_SESSION['deepskylog_id']) == $value)
         {
            print("<option selected=\"selected\" value=\"$value\">$instrumentname</option>\n");
         }
         else
         {
            if ($noStd && $value == "1")
            {
             print("<option selected=\"selected\" value=\"$value\">$instrumentname</option>\n");
            }
            else
            {
             print("<option value=\"$value\">$instrumentname</option>\n");
            }
         }
      }

echo("</select>
   </td>
   <td class=\"explanation\"><a href=\"common/add_instrument.php\">");

echo(LangChangeAccountField8Expl);

echo("</a></td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangChangeAccountField9);
echo("</td>");

echo("<td>");
echo("<select name=\"atlas\">");
 $theKey=$obs->getStandardAtlas($_SESSION['deepskylog_id']);
 while(list ($key, $value) = each($atlasses))
 { if ($key == $theKey) print("<option selected=\"selected\" value=\"$key\">" . $value . "</option>\n");
   else print("<option value=\"$key\">" . $value . "</option>\n");
 }
echo("</select>");
echo("</td>");

echo("</tr>");

echo("<tr>
   <td class=\"fieldname\">");

echo(LangChangeAccountPicture);

echo("</td>
   <td colspan=\"2\">
   <input type=\"file\" name=\"picture\" />
   </td>
   </tr>
   <tr>
   <td>&nbsp;</td>   
   <td>&nbsp;</td>
   <td>&nbsp;</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

if ($languageMenu == 1)
{
 echo(LangChangeAccountLanguage);

 echo("</td>
    <td>   <select name=\"language\">");
 

      $language = new Language(); 
      $languages = $language->getLanguages(); 

      while(list ($key, $value) = each($languages))
      {
         if($obs->getLanguage($_SESSION['deepskylog_id']) == $key)
         {
            print("<option value=\"" . $key . "\" selected=\"selected\">$value</option>\n");
         }
         else 
         {
            print("<option value=\"" . $key . "\">$value</option>\n");
         }
      }

 print(" 
    </select>
    </td>   <td class=\"explanation\">");

 echo(LangChangeAccountLanguageExpl);
}

 echo("</td></tr><tr><td>");
 echo(LangChangeAccountObservationLanguage);

 echo("</td><td>");

 $allLanguages = $language->getAllLanguages($obs->getLanguage($_SESSION['deepskylog_id']));

 echo("<select name=\"description_language\">");

 while(list ($key, $value) = each($allLanguages))
 {
   if($obs->getObservationLanguage($_SESSION['deepskylog_id']) == $key)
   {
     print("<option value=\"".$key."\" selected=\"selected\">".$value."</option>\n");
   }
   else 
   {
     print("<option value=\"".$key."\">".$value."</option>\n");
   }
 }

 print(" 
    </select>
    </td>   <td class=\"explanation\">");

 echo(LangChangeAccountObservationLanguageExpl);


echo("</td>
      </tr>");
echo("<tr>
   <td class=\"fieldname\">");

echo(LangChangeVisibleLanguages);

 echo("</td><td>");

 $allLanguages = $language->getAllLanguages($obs->getLanguage($_SESSION['deepskylog_id']));
 $_SESSION['alllanguages'] = $allLanguages; 

 $usedLanguages = $obs->getUsedLanguages($_SESSION['deepskylog_id']);

 while(list ($key, $value) = each($allLanguages))
 {
   echo("<input type=\"checkbox\" ");

   for ($i = 0;$i < count($usedLanguages);$i++)
   {
     if ($key == $usedLanguages[$i])
     {
       echo("checked ");
     }
   }
   echo ("name=\"" . $key . "\" value=\"" . $key . "\" />". $value . "<br />\n");
 }
 print("</td>   <td class=\"explanation\">");

 echo(LangChangeVisibleLanguagesExpl);

echo("</td>
   </tr>
   <tr>
   <td>&nbsp;</td>
   <td>&nbsp;</td>
   <td>&nbsp;</td>
   </tr>
   <tr>
   <td></td>
   <td><input type=\"submit\" name=\"change\" value=\"".LangChangeAccountButton."\" /></td>
   <td></td>
   </tr>
   </table>
   </form>
</div></div></body></html>");

?>
