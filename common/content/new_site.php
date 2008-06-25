<?php

// new_site.php
// allows the user to add a new site

include_once "../lib/observers.php";
include_once "../lib/locations.php";
include_once "../lib/util.php";
include_once "../lib/cometobservations.php";

$locations = new locations;
$util = new util;
$util->checkUserInput();
$observations = new observations;
$cometobservations = new CometObservations;
$observers = new observers;

// sort

if(isset($_GET['sort']))
{
  $sort = $_GET['sort']; // field to sort on
}
else
{
  $sort = "name"; // standard sort on location name
}

// minimum

if(isset($_GET['min']))
{
  $min = $_GET['min'];
}
else
{
  $min = 0;
}

// the code below looks very strange but it works

if((isset($_GET['previous'])))
{
  $orig_previous = $_GET['previous'];
}
else
{
  $orig_previous = "";
}

$sites = $locations->getSortedLocations($sort, $_SESSION['deepskylog_id']);
$locs = $observers->getListOfLocations();

if((isset($_GET['sort'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
{
  if ($_GET['sort'] == "name")
  {
    $sites = array_reverse($sites, true);
  }
  else
  {
    krsort($sites);
    reset($sites);
  }
    $previous = ""; // reset previous field to sort on
}
else
{
  $previous = $sort;
}

$step = 25;

echo("<div id=\"main\">\n<h2>".LangOverviewSiteTitle."</h2>");

$link = "common/add_site.php?sort=" . $sort . "&amp;previous=" . $orig_previous;

list($min, $max) = $util->printListHeader($sites, $link, $min, $step, "");

echo "<table>
      <tr class=\"type3\">
      <td><a href=\"common/add_site.php?sort=name&amp;previous=$previous\">".LangViewLocationLocation."</a></td>
      <td><a href=\"common/add_site.php?sort=region&amp;previous=$previous\">".LangViewLocationProvince."</a></td>
      <td><a href=\"common/add_site.php?sort=country&amp;previous=$previous\">".LangViewLocationCountry."</a></td>";

echo "<td><a href=\"common/add_site.php?sort=longitude&amp;previous=$previous\">".LangViewLocationLongitude."</a></td>";

echo "<td><a href=\"common/add_site.php?sort=latitude&amp;previous=$previous\">".LangViewLocationLatitude."</a></td>";
echo "<td><a href=\"common/add_site.php?sort=timezone&amp;previous=$previous\">".LangAddSiteField6."</a></td>";
echo "<td><a href=\"common/add_site.php?sort=limitingMagnitude&amp;previous=$previous\">".LangViewLocationLimMag."</a></td>";
echo "<td><a href=\"common/add_site.php?sort=skyBackground&amp;previous=$previous\">".LangViewLocationSB."</a></td>";
echo "<td>".LangViewLocationStd."</td>";
echo "<td></td>";
echo "</tr>";
echo "<form action=\"common/control/validate_site.php\" method=\"post\">";

$count = 0;

if ($sites != null)
{
 while(list ($key, $value) = each($sites))
 {
  if($count >= $min && $count < $max) // selection
  {
   if ($count % 2)
   {
    $type = "class=\"type1\"";
   }
   else
   {
    $type = "class=\"type2\"";
   }

   $sitename = stripslashes($locations->getName($value));
   $region = stripslashes($locations->getRegion($value));
   $country = $locations->getCountry($value);
   if($locations->getLongitude($value) > 0)
   {
      $longitude = "&nbsp;" . $util->decToString($locations->getLongitude($value));
   }
   else
   {
      $longitude = $util->decToString($locations->getLongitude($value));
   }
   if($locations->getLatitude($value) > 0)
   {
      $latitude = "&nbsp;" . $util->decToString($locations->getLatitude($value));
   }
   else
   {
      $latitude = $util->decToString($locations->getLatitude($value));
   }
   $timezone = $locations->getTimezone($value);
   $observer = $locations->getObserver($value);
   $limmag = $locations->getLimitingMagnitude($value);
   if ($limmag < -900)
   {
     $limmag = "&nbsp;";
   }
   $sb = $locations->getSkyBackground($value);
   if ($sb < -900)
   {
     $sb = "&nbsp;";
   }

   if ($value != "1")
   {
    print("<tr $type>
           <td><a href=\"common/adapt_site.php?location=$value\">$sitename</a></td>\n
           <td>$region</td>\n
           <td>$country</td>\n
            <td>");
           echo ($longitude);
           echo("</td><td>");
           echo ($latitude);
           echo("</td><td>");
           echo ($timezone);
           echo("</td><td>");
           echo ($limmag);
           echo("</td><td>");
           echo ($sb);
           echo("</td><td>");

					 // Radio button for the standard instrument

					 if ($value == $observers->getStandardLocation($_SESSION['deepskylog_id']))
					 {
					   echo("<input type=\"radio\" name=\"stdlocation\" value=\"". $value ."\" checked>&nbsp;<br>");
					 } else {
					   echo("<input type=\"radio\" name=\"stdlocation\" value=\"". $value ."\">&nbsp;<br>");
					 }
  				 echo("</td>\n<td>\n");
	
           // check if there are no observations made from this location
           $queries = array("location" => $value, "observer" => $_SESSION['deepskylog_id']);
           $obs = $observations->getObservationFromQuery($queries, "", "1", "False", "D", "1");
           $comobs = $cometobservations->getObservationFromQuery($queries, "", "1", "False");

           if(!sizeof($obs) > 0 && !array_search($value, $locs) && !sizeof($comobs) > 0) // no observations from location yet
           {
              echo("<a href=\"common/control/validate_delete_location.php?locationid=" . $value . "\">" . LangRemove . "</a>");
           }

           echo("</td>\n</tr>");

   }
  }
 }
 $count++;
}
  echo "</table>";

  echo("<input type=\"hidden\" name=\"adaption\" value=\"1\">");
  echo("<input type=\"submit\" name=\"adapt\" value=\"" . LangAddSiteStdLocation . "\" />");
  echo "</form>";

  list($min, $max) = $util->printListHeader($sites, $link, $min, $step, "");

  echo "</div></div>";

echo("<h2>");
echo(LangAddSiteTitle); ?>

</h2>

<ol>
<li value="1">
<?php echo (LangAddSiteExisting);
?>

<?php echo("<table width=\"100%\">");
      echo("<tr>");
      echo("<td width=\"25%\">\n");
      echo("<form name=\"overviewform\">\n ");		
      echo("<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalogue\">\n");

  $sites = $locations->getSortedLocations('name', "", true);
  while(list($key, $value) = each($sites))
  {
		  echo("<option value=\"" . $baseURL . "common/add_site.php?locationid=$value\">" . $locations->getName($value) . "</option>\n");
  }
  echo("</select>\n");
  echo("</form>");
  echo("</td>");
  echo("</tr>");
  echo("</table>");
?>
</li>
</ol>
<p><?php echo (LangAddSiteFieldOr); ?></p>
<ol>
<li value="2"><a href="common/search_sites.php"><?php echo (LangAddSiteFieldSearchDatabase); ?></a></li>
</ol>
<p><?php echo (LangAddSiteFieldOr); ?></p>
<ol>
<li value="3"><?php echo (LangAddSiteFieldManually); ?></li>
</ol>
 
   <form action="common/control/validate_site.php" method="post">
   <table>
   <tr>
   <td class="fieldname">
	     <?php 
			 echo(LangAddSiteField1); 
			 ?></td>
   <td><input type="text" class="inputfield" maxlength="64" name="sitename" size="30" value="<?php 
			 if(array_key_exists('sitename',$_GET) && $_GET['sitename'])
       {
			    echo stripslashes($_GET['sitename']);
			 } 
			 if(array_key_exists('locationid',$_GET) && $_GET['locationid'])
       {
			    echo stripslashes($locations->getName($_GET['locationid']));
			 } 
			 ?>" /></td>
   <td class="explanation"></td>
   </tr>
   <tr>
   <td class="fieldname">
	     <?php 
			 echo(LangAddSiteField2); 
			 ?></td>
   <td><input type="text" class="inputfield" maxlength="64" name="region" size="30" value="<?php 
			 if(array_key_exists('region',$_GET) && $_GET['region']) 
       {
			    echo stripslashes($_GET['region']);
			 } 
			 if(array_key_exists('locationid',$_GET) && $_GET['locationid'])
       {
			    echo stripslashes($locations->getRegion($_GET['locationid']));
			 } 
			 ?>" /></td>
   <td class="explanation"><?php echo(LangAddSiteField2Expl); ?></td>
   </tr>
   <tr>
   <td class="fieldname"><?php echo(LangAddSiteField3); ?></td>
   <td>
   <?php 
	 echo("<select name=\"country\">");
   $countries = $locations->getCountries();

   echo "<option value=\"\"></option>"; // empty field

   while(list ($key, $value) = each($countries))
   {
      if(array_key_exists('country',$_GET) && ($_GET['country'] == $value))
      { 
         echo("<option selected=\"selected\" value=\"$value\">$value</option>\n");
      }
      else if(array_key_exists('locationid',$_GET) && $locations->getCountry($_GET['locationid']) == $value)
      {
         echo("<option selected=\"selected\" value=\"$value\">$value</option>\n");
      }
      else
      {
         echo("<option value=\"$value\">$value</option>\n");
      }
   }
   ?>
   </select>

   <?php 
   if(array_key_exists('latitude',$_GET) && $_GET['latitude'] || array_key_exists('locationid',$_GET) && $_GET['locationid'])
   {
      if (array_key_exists('latitude',$_GET))
      {
       $latitudestr = $util->decToString($_GET['latitude'], 1);
      } else
      {
       $latitudestr = $util->decToString($locations->getLatitude($_GET['locationid']), 1);
      } 
      $latarray = explode("&deg;", $latitudestr);
      $latitudedeg = $latarray[0];
      $latitudemin = $latarray[1];
   }
   if(array_key_exists('longitude',$_GET) && $_GET['longitude'] || array_key_exists('locationid',$_GET) && $_GET['locationid'])
   {
      if (array_key_exists('latitude',$_GET))
      {
       $longitudestr = $util->decToString($_GET['longitude'], 1);
      } else
      {
       $longitudestr = $util->decToString($locations->getLongitude($_GET['locationid']), 1);
      } 
      $longarray = explode("&deg;", $longitudestr);
      $longitudedeg = $longarray[0];
      $longitudemin = $longarray[1];
   }
   ?>

</td>
   </tr>
   <tr>
   <td class="fieldname"><?php echo(LangAddSiteField4); ?></td>
   <td><input type="text" class="inputfield" maxlength="3" name="latitude" size="3" value="<?php 
	 if(array_key_exists('latitude',$_GET) && $_GET['latitude'] || array_key_exists('locationid',$_GET) && $_GET['locationid']) 
   {
	    echo $latitudedeg;
			
	 } ?>" />&deg;&nbsp;<input type="text" class="inputfield" maxlength="2" name="latitudemin" size="2" value="<?php 
	 if(array_key_exists('latitude',$_GET) && $_GET['latitude'] || array_key_exists('locationid',$_GET) && $_GET['locationid']) 
   {
	    echo $latitudemin;
	 } ?>" />&#39;</td>
   <td class="explanation"><?php echo(LangAddSiteField4Expl); ?></td>
   </tr>
   <tr>
   <td class="fieldname"><?php echo(LangAddSiteField5); ?></td>
   <td><input type="text" class="inputfield" maxlength="4" name="longitude" size="4" value="<?php 
	 if(array_key_exists('longitude',$_GET) && $_GET['longitude'] || array_key_exists('locationid',$_GET) && $_GET['locationid'])
   {
	    echo $longitudedeg;
	 } ?>" />&deg;&nbsp;<input type="text" class="inputfield" maxlength="2" name="longitudemin" size="2" value="<?php 
	 if(array_key_exists('longitude',$_GET) && $_GET['longitude'] || array_key_exists('locationid',$_GET) && $_GET['locationid']) 
   {
	    echo $longitudemin;
	 } ?>" />&#39;</td>
   <td class="explanation"><?php echo(LangAddSiteField5Expl); ?></td>
   </tr>

   <tr>
   <td class="fieldname">
   <?php 
     echo(LangAddSiteField6);
   ?></td><td>
<?php 
   $timezone_identifiers = DateTimeZone::listIdentifiers();

   echo("<select name=\"timezone\">");

   while(list ($key, $value) = each($timezone_identifiers))
   {
     if (array_key_exists('locationid',$_GET) && $_GET['locationid'])
     {
       if ($value == $locations->getTimeZone($_GET['locationid'])) 
       {
        echo("<option value=\"$value\" selected>$value</option>\n");
       }
       else
       {
        echo("<option value=\"$value\">$value</option>\n");
       }
     }
     else if ($value == "UTC")
     {
       echo("<option value=\"$value\" selected>$value</option>\n");
     }
     else
     {
       echo("<option value=\"$value\">$value</option>\n");
     }
   }

   echo("</select>");
?>
</td>
   </tr>
   <tr>
   <td class="fieldname">
   <?php 
     echo(LangAddSiteField7);
   ?></td>
   <td><input type="text" class="inputfield" maxlength="5" name="lm" size="5" value="<?php
     if(array_key_exists('locationid',$_GET) && $_GET['locationid'])
     {
       if ($locations->getLimitingMagnitude($_GET['locationid']) > -900)
       {
        echo $locations->getLimitingMagnitude($_GET['locationid']);
       }
     }
     ?>" /></td>
   <td class="explanation"><?php echo(LangAddSiteField7Expl); ?></td>
   </tr>

   <tr>
   <td class="fieldname">
   <?php 
     echo(LangAddSiteField8);
   ?></td>
   <td><input type="text" class="inputfield" maxlength="5" name="sb" size="5" value="<?php
     if(array_key_exists('locationid',$_GET) && $_GET['locationid'])
     {
       if ($locations->getSkyBackground($_GET['locationid']) > -900)
       {
        echo $locations->getSkyBackground($_GET['locationid']);
       }
     }
     ?>" />
   </td>
   <td class="explanation"><?php echo(LangAddSiteField8Expl); ?></td>
   </tr>

   <tr>
   <td></td>
   <td><input type="submit" name="add" value="<?php echo (LangAddSiteButton); ?>" /></td>
   <td></td>
   </tr>
   </table>
   </form>
</div>
</div>
</body>
</html>

