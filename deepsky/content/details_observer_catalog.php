<?php
// view_observer.php
// shows information of number of Messier objects 

$firstname = $GLOBALS['objObserver']->getFirstName(html_entity_decode($_GET['user']));
$name = $GLOBALS['objObserver']->getObserverName(html_entity_decode($_GET['user']));
$partof=0;
if(array_key_exists('partof', $_GET))
  $partof = $_GET['partof'];
echo "<div id=\"main\">";
echo "<h2>".$firstname."&nbsp;".$name."</h2>";
$upload_dir=$GLOBALS['instDir'].'common/observer_pics';
$dir=opendir($upload_dir);
while(FALSE!==($file=readdir($dir)))
{ if ("." == $file OR ".." == $file)
  { continue; // skip current directory and directory above
  }
  if(fnmatch(html_entity_decode($_GET['user']). ".gif", $file) || fnmatch(html_entity_decode($_GET['user']). ".jpg", $file) || fnmatch(html_entity_decode($_GET['user']). ".png", $file))
  { echo("<p><img class=\"viewobserver\" src=\"$upload_dir" . "/" . "$file\" alt=\"" . $firstname . "&nbsp;" . $name . "\"></img></p>");
  }
}
$cat = $_GET['catalog']; // name of the catalogue
$observedObjectsFromCatalogue = $GLOBALS['objObservation']->getObservedFromCatalogue(html_entity_decode($_GET['user']), $cat); // number of objects observed by this observer
if($partof)
  $observedObjectsFromCataloguePartOf = $GLOBALS['objObservation']->getObservedFromCataloguePartOf(html_entity_decode($_GET['user']), $cat); // number of objects observed by this observer	
$numberOfObjects = $GLOBALS['objObject']->getNumberOfObjectsInCatalogue($cat); // number of objects in catalogue
echo("<table width=\"490\">\n");
echo"<tr>" .                                                                    // NUMBER OF OBSERVATIONS
     "<td class=\"fieldname\"><p><b>" . LangTopObserversMessierHeader2 . " " . $cat . " " . LangTopObserversMessierHeader3;
if($partof)
  echo " of deelobjecten "; 			
else
  echo " (geen deelobjecten)";			
echo "</b>";
echo "</p>";
echo "</td>";
echo "<td>";
echo "<b>" . count($observedObjectsFromCatalogue) . " / " . $numberOfObjects .  
     "</b>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>";
echo "</td>";
echo "<td>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</div>";
$resultarray=$GLOBALS['objObject']->getObjectsFromCatalog($cat);
echo "<table>";
for ($i = 1; $i <= $numberOfObjects; $i++) 
{ if((($i - 1) % 100) == 0)
  { echo  "<tr>";
    echo  "<td>";
    echo  "&nbsp;";
    echo  "</td>";
    for ($j = 1; $j <= 5; $j++) 
    {   echo  "<td align=\"center\">";
    	  echo  "$j";
    		echo  "</td>";
    }
    echo  "<td>";
    echo  "&nbsp;";
    echo  "</td>";
    for ($j = 6; $j <= 10; $j++) 
    {   echo  "<td align=\"center\">";
    	  echo  "$j";
    		echo  "</td>";
    }
    echo  "</tr>";
  } 
  if((($i - 1) % 10) == 0)
  { echo  "<tr>";
    echo  "<td style=\"background: #FFFFFF; padding: 5px 5px 5px 5px; text-align: right;\">";
	  echo  $i;
		echo  '-';
		echo  $i+9;
		echo  "</td>";
  } 
	elseif((($i-1) % 5) == 0)
  { echo  "<td> &nbsp; </td>";
  } 
  $index = key($resultarray);
	list($object, $altname) = current($resultarray); 
	if(($cat . " " . $index) != $object)
	  $ref = $cat . " " . $index;
	else
	  $ref = $object; 
	if (in_array($object, $observedObjectsFromCatalogue)) 
  { echo "<td style=\"background: #33FF00; padding: 5px 5px 5px 5px; text-align: center;\">";
		echo "<a title=\"".$ref."\" href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;object=".urlencode($object)."&amp;observer=".urlencode($_GET['user'])."\" style=\"color: #000000;\">".$object."</a>";
		echo "</td>";
  }
	else
	  if ($partof && in_array($object, $observedObjectsFromCataloguePartOf)) 
  	{	echo "<td style=\"background: #FFFF00; padding: 5px 5px 5px 5px; text-align: center;\">";
		  echo "<a title=\"".$ref."\" href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($object)."\" style=\"color: #000000;\">".$object."</a>";
			echo "</td>"; 
		}
		else
  	{	echo "<td style=\"background: #FF0000; padding: 5px 5px 5px 5px; text-align: center;\">";
		  echo "<a title=\"".$ref."\" href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($object)."\" style=\"color: #000000;\">".$object."</a>";
			echo "</td>";
		}
  if(($i % 10) == 0)
    echo  "</tr>";
	next($resultarray);
}
echo "</table>";
echo "<P>";

if($partof)
  echo "<a href=\"".$baseURL."index.php?indexAction=view_observer_catalog&amp;catalog=".urlencode($cat)."&amp;user=".urlencode($_GET['user'])."&amp;partof=0\">Toon zonder deelobjecten</a>"; 			
else
  echo "<a href=\"".$baseURL."index.php?indexAction=view_observer_catalog&amp;catalog=".urlencode($cat)."&amp;user=".urlencode($_GET['user'])."&partof=1\">Toon met deelobjecten</a>";			
?>
