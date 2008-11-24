<?php

// view_observer.php
// shows information of an observer 
// Version 0.4: 20060108, JV

// include statements
//$$ ok

include_once "../lib/observers.php"; // observer table
include_once "../lib/locations.php"; // location table
include_once "../lib/observations.php"; // observations table
include_once "../lib/cometobservations.php"; // observations table
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

// creation of objects

$obs = new Observers; 
$locations = new Locations;
$observations = new Observations;
$cometobservations = new CometObservations;


//just for David's Windows environment
   if (!function_exists('fnmatch')) {
       function fnmatch($pattern, $string) {
           return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
       }
   }
//end of David's window environment

$user = urldecode($_GET['user']);
$user = str_replace("&amp;", "&", $user);
$user = str_replace("&amp;", "&", $user);

// test if user exists
if(!array_key_exists('user', $_SESSION) || $obs->getObserverName($user) == "") // no session variable set 
{
 echo(LangViewObserverInexistant); 
}  
else
{
 $firstname = $obs->getFirstName($_SESSION['user']);
 $name = $obs->getObserverName($_SESSION['user']);

 echo("<div id=\"main\">\n");
 echo("<h2>$firstname $name</h2>");

 $upload_dir = 'observer_pics';
 $dir = opendir($upload_dir);

 while (FALSE !== ($file = readdir($dir)))
 {
   if ("." == $file OR ".." == $file)
   {
    continue; // skip current directory and directory above
   }
   if(fnmatch($_SESSION['user']. ".gif", $file) || fnmatch($_SESSION['user']. ".jpg",
$file) || fnmatch($_SESSION['user']. ".png", $file))
   {
    echo("<p><img class=\"viewobserver\" src=\"common/$upload_dir" . "/" . "$file\" alt=\"" . $firstname . "&nbsp;" . $name . "\"></img></p>");
   }
 }

 echo("<form action=\"common/control/change_role.php\">");

 echo("<table width=\"490\">\n");

 if(array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes")) // admin logged in
 {
  echo("<tr class=\"type1\"><td class=\"fieldname\">");

  echo(LangChangeAccountField2);

  echo("</td><td colspan=\"" . sizeof($modules) . "\"><a href=\"mailto:");

  echo($obs->getEmail($_SESSION['user']));

  echo("\">");

  echo($obs->getEmail($_SESSION['user']));

  echo("</a>");

  print("</td></tr>");
 }

 echo("<tr class=\"type2\"><td class=\"fieldname\">");

 echo(LangChangeAccountField3);

 echo("</td><td colspan=\"" . sizeof($modules) . "\">");

 print($obs->getFirstName($_SESSION['user']));

 print("</td></tr><tr class=\"type1\"><td class=\"fieldname\">");

 echo(LangChangeAccountField4);

 echo("</td><td>");

 print($obs->getObserverName($_SESSION['user']));

 print("</td>
        </tr>");

 echo("<tr class=\"type2\">
       <td class=\"fieldname\">");

 echo(LangChangeAccountField7);

 echo("</td>
       <td colspan=\"" . sizeof($modules) . "\">");

 $location_id = $obs->getStandardLocation($_SESSION['user']);
 $location_name = $locations->getLocationName($location_id);


 $url = "common/detail_location.php?location=" . "$location_id";

 echo("<a href=\"$url\">$location_name</a>");

 echo("</td>
       </tr>");

 echo("<tr class=\"type1\">
       <td class=\"fieldname\">");

 echo(LangChangeAccountField8);

 echo("</td>
       <td colspan=\"" . sizeof($modules) . "\">");

 include_once "../lib/instruments.php";
 $instruments = new Instruments;

 if($instruments->getInstrumentName($obs->getStandardTelescope($_SESSION['user'])))
 {
  $instrumentname = $instruments->getInstrumentName($obs->getStandardTelescope($_SESSION['user']));
  if ($instrumentname == "Naked eye")
  {
   $instrumentname = InstrumentsNakedEye;
  }
  echo("<a href=\"common/detail_instrument.php?instrument=" . $obs->getStandardTelescope($_SESSION['user']) . "\">");
  echo $instrumentname;
  echo("</a>");
 }

 echo("</td>
       </tr>");

 if(array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes")) // admin logged in
 {
  echo("<tr class=\"type2\">
        <td class=\"fieldname\">".LangViewObserverRole."
        </td>");

  if(array_key_exists('user', $_SESSION) && ($_SESSION['user'] != "admin")) // normal user
  {

   if($obs->getRole($_SESSION['user']) != RoleWaitlist && $_SESSION['user'] != "admin") // user not in waitlist
   {
    echo("<td colspan=\"" . sizeof($modules) . "\">\n
          <select name=\"role\">
          <option ");

    if($obs->getRole($_SESSION['user']) == RoleAdmin) 
    {
     echo "selected=\"selected\"";
    }

    echo(" value=\"0\">".LangViewObserverAdmin."</option>\n<option ");

    if($obs->getRole($_SESSION['user']) == RoleUser) 
    {
     echo "selected=\"selected\"";
    }

    echo(" value=\"1\">".LangViewObserverUser."</option>\n<option ");

    if($obs->getRole($_SESSION['user']) == RoleCometAdmin)
    {
     echo "selected=\"selected\"";
    }

    echo(" value=\"4\">".LangViewObserverCometAdmin."</option>\n<option ");

    if($obs->getRole($_SESSION['user']) == RoleWaitlist)
    {
     echo "selected=\"selected\"";
    }

    echo(" value=\"2\">".LangViewObserverWaitlist."</option>\n</select>
          <input type=\"submit\" name=\"change\" value=\"".LangViewObserverChange."\" />");

   }
   elseif ($obs->getRole($_SESSION['user']) == RoleWaitlist)
   {
     echo(LangViewObserverWaitlist."</td>");
   }
  }
  else // fixed admin role
  {
   echo(LangViewObserverAdmin."</td>");
  }

  echo("</tr>");
 }

 // NUMBER OF OBSERVATIONS
 $number_of_observations = $obs->getNumberOfDsObservations($_SESSION['user']);

 $rank = $obs->getRank($_SESSION['user']);
 if ($rank == 0)
 {
  $rank = "-";
 }
 $number_of_comet_observations = $obs->getNumberOfCometObservations($_SESSION['user']);

 if($number_of_observations != 0 || $number_of_comet_observations != 0)
 {
  include_once "../lib/setup/databaseInfo.php";

  echo "<tr><td colspan=\"" . (sizeof($modules) + 1) . "\" class=\"type1\">&nbsp;</td></tr>
        <tr class=\"type3\">\n
        <td> &nbsp; </td>";

  for ($i = 0; $i < count($modules);$i++)
  {
   $mod = $modules[$i];

   print "<td>" . $$mod . "</td>\n";
  }

  $numberOfObservations = $observations->getNumberOfDsObservations();
  $numberOfObservationsThisYear = $observations->getNumberOfObservationsLastYear();
  $numberOfDifferentObjects = $observations->getNumberOfDifferentObjects();
  $observationsThisYear = $observations->getObservationsLastYear($_SESSION['user']);

  $cometrank = $obs->getCometRank($_SESSION['user']);
  if ($cometrank == 0)
  {
   $cometrank = "-";
  }
  $numberOfCometObservations = $cometobservations->getNumberOfObservations();
  $numberOfCometObservationsThisYear = $cometobservations->getNumberOfObservationsThisYear();
  $numberOfDifferentCometObjects = $cometobservations->getNumberOfDifferentObjects();
  if ($numberOfDifferentCometObjects == "")
  {
   $numberOfDifferentCometObjects = 0;
  }
  $cometobservationsThisYear = $cometobservations->getObservationsThisYear($_SESSION['user']);

  // Loop over all the modules. Put the information in an array, sorted on the 
  // modules.
  // The array has the following information :
  //  $information[$i][0] = The string describing the number of observations.
  //  $information[$i][1] = The string describing the number of observations this year.
  //  $information[$i][2] = The string describing the number of different objects.
  //  $information[$i][4] = The string describing the rank of the observer
  for ($i = 0; $i < count($modules);$i++)
  {
   if(strcmp($$modules[$i], $deepsky) == 0)
   {
    if ($numberOfObservations != 0)
    {
     $percentObservations = ($number_of_observations / $numberOfObservations) * 100;
    }
    else
    {
     $percentObservations = 0;
    }

    $information[$i][0] = $number_of_observations." / ".$numberOfObservations." (" . sprintf("%.2f", $percentObservations) . "%)";

    if ($numberOfObservationsThisYear != 0)
    {
     $percentObservations = ($observationsThisYear / $numberOfObservationsThisYear) * 100;
    }
    else
    {
     $percentObservations = 0;
    }

    $information[$i][1] = $observationsThisYear . " / ".$numberOfObservationsThisYear."&nbsp;&nbsp;&nbsp;&nbsp;(".sprintf("%.2f", $percentObservations)."%)";

   // Deepsky : Number of different objects
    $numberOfObjects = $observations->getNumberOfObjects($_SESSION['user']);

    if ($numberOfDifferentObjects != 0)
    {
     $percentObjects = ($numberOfObjects / $numberOfDifferentObjects) * 100;
    }
    else
    {
     $percentObjects = 0;
    }

    $information[$i][2] = $numberOfObjects . " / ".$numberOfDifferentObjects." (" . sprintf("%.2f", $percentObjects)."%)";

    // Deepsky : Rank
    $information[$i][4] = $rank;
   }
   if(strcmp($$modules[$i], $comets) == 0)
   {
    if ($numberOfCometObservations != 0)
    {
     $percentObservations = ($number_of_comet_observations / $numberOfCometObservations) * 100;
    }
    else
    {
     $percentObservations = 0;
    }

    $information[$i][0] = $number_of_comet_observations." / ".$numberOfCometObservations." (" . 
                          sprintf("%.2f", $percentObservations) . "%)";

    if ($numberOfCometObservationsThisYear != 0)
    {
     $percentCometObservations = ($cometobservationsThisYear / $numberOfCometObservationsThisYear) * 100;
    }
    else
    {
     $percentCometObservations = 0;
    }

    $information[$i][1] = $cometobservationsThisYear . " / ".$numberOfCometObservationsThisYear."&nbsp;(" . 
                          sprintf("%.2f", $percentCometObservations)."%)";

    // Comets : Number of different objects
    $numberOfCometObjects = $cometobservations->getNumberOfObjects($_SESSION['user']);

    if ($numberOfDifferentCometObjects != 0)
    {
     $percentObjects = ($numberOfCometObjects / $numberOfDifferentCometObjects) * 100;
    }
    else
    {
     $percentObjects = 0;
    }

    $information[$i][2] = $numberOfCometObjects . " / ".$numberOfDifferentCometObjects." (" . sprintf("%.2f", $percentObjects)."%)";

    // Comets : Rank
    $information[$i][4] = $cometrank;
   }
  }

  // Now that all the information is available in the $information array, we can print the table.
  echo "<tr class=\"type1\">
        <td class=\"fieldname\">".LangViewObserverNumberOfObservations."</td>";

  for ($i = 0;$i < count($modules);$i++)
  {
   echo "<td>" . $information[$i][0] . "</td>";
  }
  echo ("</tr>");


  echo("<tr class=\"type2\">
        <td class=\"fieldname\">".LangTopObserversHeader4."</td>");
  for ($i = 0;$i < count($modules);$i++)
  {
   echo "<td>" . $information[$i][1] . "</td>";
  }
  echo("</tr>");


  echo("<tr>
        <td class=\"type1\">".LangTopObserversHeader6."</td>");
  for ($i = 0;$i < count($modules);$i++)
  {
   echo "<td>" . $information[$i][2] . "</td>";
  }
  echo("</tr>");


  // Deepsky : Number of Messier objects. This should only be displayed if the Deepsky Module is available.
  $key = array_search("deepsky", $modules); 
  if(is_null($key))
  {
    $key = false;
  }

  if ($key !== false)
  {
    echo("<tr  class=\"type2\">");
    echo("<td>".LangTopObserversHeader5."</td>");
    for ($i = 0;$i < count($modules);$i++)
    {
      echo("<td>");
      if ($key == $i)
      {
        echo($observations->getObservedCountFromCatalogue($_SESSION['user'],"M") . " / 110");
      }
      else
      {
        echo("-");
      }
      echo("</td>");
    }
		
    echo("<tr class=\"type1\">");
    echo("<td class=\"fieldname\">".LangTopObserversHeader5b."</td>");
    for ($i = 0;$i < count($modules);$i++)
    {
      echo("<td>");
      if ($key == $i)
      {
        echo($observations->getObservedCountFromCatalogue($_SESSION['user'],"Caldwell") . " / 110");
      }
      else
      {
        echo("-");
      }
      echo("</td>");
    }

    echo("<tr class=\"type2\">");
    echo("<td class=\"fieldname\">".LangTopObserversHeader5c."</td>");
    for ($i = 0;$i < count($modules);$i++)
    {
      echo("<td>");
      if ($key == $i)
      {
        echo($observations->getObservedCountFromCatalogue($_SESSION['user'],"H400") . " / 400");
      }
      else
      {
        echo("-");
      }
      echo("</td>");
    }

    echo("<tr class=\"type1\">");
    echo("<td class=\"fieldname\">".LangTopObserversHeader5d."</td>");
    for ($i = 0;$i < count($modules);$i++)
    {
      echo("<td>");
      if ($key == $i)
      {
        echo($observations->getObservedCountFromCatalogue($_SESSION['user'],"HII") . " / 400");
      }
      else
      {
        echo("-");
      }
      echo("</td>");
    }

  echo("</tr>");
  }

  echo("<tr class=\"type2\">");
  echo("<td class=\"fieldname\">".LangViewObserverRank."</td>");
  for ($i = 0;$i < count($modules);$i++)
  {
    echo "<td>" . $information[$i][4] . "</td>";
  }

  echo("</tr>");
  echo("</tr>");
 }
 echo("<tr>
       <td></td>
       <td></td>
       </tr>
       </table></form>
       </div>");
}
?>
