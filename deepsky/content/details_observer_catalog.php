<?php

// view_observer.php
// shows information of number of Messier objects 
// seen by an observer 
// version 0.1: 20060319, JV

//just for David's Windows environment
   if (!function_exists('fnmatch')) {
       function fnmatch($pattern, $string) {
           return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
       }
   }
//end of David's window environment

include_once "../lib/observers.php"; // observer table
$obs = new Observers; 
include_once "../lib/objects.php"; // objects table
$objects = new Objects;
include_once "../lib/observations.php"; // observations table
$observations = new Observations;

$firstname = $obs->getFirstName($_GET['user']);
$name = $obs->getName($_GET['user']);

$partof=0;
if(array_key_exists('partof', $_GET))
  $partof = $_GET['partof'];

echo("<div id=\"main\">\n");
echo("<h2>$firstname $name</h2>");

  $upload_dir = '../common/observer_pics';
  $dir = opendir($upload_dir);

  while (FALSE !== ($file = readdir($dir)))
  {
    if ("." == $file OR ".." == $file)
    {
      continue; // skip current directory and directory above
    }
    if(fnmatch($_GET['user']. ".gif", $file) || fnmatch($_GET['user']. ".jpg", $file) || fnmatch($_GET['user']. ".png", $file))
    {
      echo("<p><img class=\"viewobserver\" src=\"$upload_dir" . "/" . "$file\" alt=\"" . $firstname . "&nbsp;" . $name . "\"></img></p>");
    }
  }
  echo("<table width=\"490\">\n");
  // NUMBER OF OBSERVATIONS
  $cat = $_GET['catalog']; // name of the catalogue
  $observedObjectsFromCatalogue = $observations->getObservedFromCatalogue($_GET['user'], $cat); // number of objects observed by this observer
  if($partof)
    $observedObjectsFromCataloguePartOf = $observations->getObservedFromCataloguePartOf($_GET['user'], $cat); // number of objects observed by this observer	
  $numberOfObjects = $objects->getNumberOfObjectsInCatalogue($cat); // number of objects in catalogue

  echo"<tr>" .
       "<td class=\"fieldname\"><p><b>" . LangTopObserversMessierHeader2 . " " . $cat . " " . LangTopObserversMessierHeader3;
  if($partof)
    echo " of deelobjecten"; 			
  else
    echo " (geen deelobjecten)";			
	echo "</b></p></td>" . 
       "<td><b>" . $observations->getObservedCountFromCatalogue($_GET['user'], $cat) . " / " . $numberOfObjects .  
       "</b></td></tr>";
  echo("<tr>
        <td>");
	echo("</td>
        <td></td>
        </tr>
   </table>
</div>");

$resultarray = $objects->getObjectsFromCatalog($cat);
echo "<table>";
for ($i = 1; $i <= $numberOfObjects; $i++) 
{
  if((($i - 1) % 100) == 0)
  {
    echo  "<tr>";
    echo  "<td>";
    echo  "&nbsp;";
    echo  "</td>";
    for ($j = 1; $j <= 5; $j++) 
    {
        echo  "<td align=\"center\">";
    	  echo  "$j";
    		echo  "</td>";
    }
    echo  "<td>";
    echo  "&nbsp;";
    echo  "</td>";
    for ($j = 6; $j <= 10; $j++) 
    {
        echo  "<td align=\"center\">";
    	  echo  "$j";
    		echo  "</td>";
    }
    echo  "</tr>";
  } 
  if((($i - 1) % 10) == 0)
  {
    echo  "<tr>";
    echo  "<td style=\"background: #FFFFFF; padding: 5px 5px 5px 5px; text-align: right;\">";
	  echo  $i;
		echo  '-';
		echo  $i+9;
		echo  "</td>";
  } 
	elseif((($i-1) % 5) == 0)
  {
    echo  "<td> &nbsp; </td>";
  } 
  $index = key($resultarray);
	list($object, $altname) = current($resultarray); 
	if(($cat . " " . $index) != $object)
	  $ref = $cat . " " . $index;
	else
	  $ref = ""; 
	if (in_array($object, $observedObjectsFromCatalogue)) 
    echo '<td style="background: #33FF00; padding: 5px 5px 5px 5px; text-align: center;">
		      <a title="' . $object . '" href="deepsky/index.php?indexAction=result_selected_observations&object=' . 
		      urlencode($object) . '&observer=' . $_GET['user'] . '" style="color: #000000;">' . $altname . '</a></td>';
  else
	  if ($partof && in_array($object, $observedObjectsFromCataloguePartOf)) 
  		echo '<td style="background: #FFFF00; padding: 5px 5px 5px 5px; text-align: center;\"><a title="' . $object . '" href="deepsky/index.php?indexAction=detail_object&object=' . 
  	     urlencode($object) . '" style="color: #000000;">' . $altname . '</td>'; 
		else
  		echo '<td style="background: #FF0000; padding: 5px 5px 5px 5px; text-align: center;\"><a title="' . $object . '" href="deepsky/index.php?indexAction=detail_object&object=' . 
  	     urlencode($object) . '" style="color: #000000;">' . $altname . '</td>';
  if(($i % 10) == 0)
    echo  "</tr>";
	next($resultarray);
}
echo "</table>";

if($partof)
  echo "<a href=\"deepsky/index.php?indexAction=view_observer_catalog&catalog=" . $cat . "&user=" . $_GET['user'] . "&partof=0\">Toon zonder deelobjecten</a> "; 			
else
  echo "<a href=\"deepsky/index.php?indexAction=view_observer_catalog&catalog=" . $cat . "&user=" . $_GET['user'] . "&partof=1\">Toon met deelobjecten</a> ";			


?>
