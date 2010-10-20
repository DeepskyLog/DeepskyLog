<?php 
// getLocation.php
// prints the locations looked up into the database after filling in the commoon/content/search_locations page

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
elseif(!($locationname=$objUtil->checkPostKey('location_name'))) throw new Exception(LangException013);
elseif(!($countryname=$objUtil->checkPostKey('country'))) throw new Exception(LangException014);
else getLocation();

function getLocation()
{ global $baseURL,$locationname,$countryname,$loggedUser,
         $objLocation,$objPresentations;
  echo "<div id=\"main\">";
	$objPresentations->line(array("<h4>".LangGetLocation1."</h4>"),"L",array(),30);
	echo "<hr />";
	$count=0;
	$result=$objLocation->getLocationsFromDatabase($locationname,$countryname);
	if(($result)&&($locationname))
	{ echo "<div class=\"results\">".LangGetLocation2."<a href=\"".$baseURL."index.php?indexAction=search_sites\">".LangGetLocation2a."</a></div><br />";
	  echo "<table>";
	  echo "<tr class=\"type3\">";
	  echo "<td>".LangGetLocation3."</td>";
	  echo "<td>".LangGetLocation4."</td>";
	  echo "<td>".LangGetLocation5."</td>";
	  echo "<td>".LangGetLocation6."</td>";
	  echo "<td>".LangGetLocation7."</td>";
	  echo "</tr>";
	  while(list($key, $value)=each($result))
	  { $vars = explode("\t", $value);
	    echo "<tr class=\"type".(2-($count%2))."\">";
	    echo "<td>";
	    echo "<a href=\"".$baseURL."index.php?indexAction=add_site&amp;sitename=$vars[0]&amp;longitude=".floor($vars[1])."&amp;longitudemin=".round(60*($vars[1]-floor($vars[1])))."&amp;latitude=".floor($vars[2])."&amp;latitudemin=".round(60*($vars[2]-floor($vars[2])))."&amp;region=$vars[4]&amp;country=$vars[3]\">$vars[0]</a> </td><td>".$objPresentations->decToString($vars[1], 1) ."</td><td>".$objPresentations->decToString($vars[2], 1) ."</td><td> $vars[4] </td><td> $vars[3]";
	    echo "</td>";
	    echo "</tr>";
	    $count++;
	  }
	  echo "</table>";
		echo "<hr />";
	}
	else
	{ echo "<p>".LangGetLocation8."</p>";
	  echo "<p><a href=\"".$baseURL."index.php?indexAction=search_sites\">".LangGetLocation9."</a>";
	  echo LangGetLocation10;
	  echo "<a href=\"".$baseURL."index.php?indexAction=add_site\">".LangGetLocation11."</a>";
	  echo "<hr />";
	}
	echo "</div>";
}
?>
