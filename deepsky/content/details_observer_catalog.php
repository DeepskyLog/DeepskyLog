<?php // details_observer_catalog.php - shows information of number of catalog objects seen by user

if(!$objUtil->checkGetKey('user'))
  throw new Exception("No user supplied in GET in details_observer_catalog.");
$firstname = $objObserver->getObserverProperty($_GET['user'],'firstname');
$name = $objObserver->getObserverProperty($_GET['user'],'name');
$partof=$objUtil->checkGetKey('partof',0);

echo "<div id=\"main\">";
$objPresentations->line(array("<h4>".$firstname."&nbsp;".$name."</h4>"),"L",array(100),30);
echo "<hr />";
$upload_dir='common/observer_pics';
$dir=opendir($instDir.$upload_dir);
while(FALSE!==($file=readdir($dir)))
{ if(("."==$file)OR(".."==$file))
   continue; // skip current directory and directory above
  if(fnmatch(html_entity_decode($_GET['user']). ".gif", $file)||fnmatch(html_entity_decode($_GET['user']). ".jpg", $file)||fnmatch(html_entity_decode($_GET['user']).".png", $file))
    echo "<p><img class=\"viewobserver\" src=\"".$baseURL.$upload_dir."/".$file."\" alt=\"".$firstname."&nbsp;".$name."\"></img></p>";
}
$cat=$objUtil->checkGetKey('catalog','M');
$observedObjectsFromCatalog = $objObservation->getObservedFromCatalog($_GET['user'], $cat); // number of objects observed by this observer
if($partof)
  $observedObjectsFromCatalogPartOf=$objObservation->getObservedFromCatalogPartOf(html_entity_decode($_GET['user']), $cat); // number of objects observed by this observer	
$numberOfObjects=$objObject->getNumberOfObjectsInCatalog($cat); // number of objects in catalog
$objPresentations->line(array(LangTopObserversMessierHeader2." ".$cat ." ".LangTopObserversMessierHeader3.(($partof)?LangOrPartOfs:LangNoPartOfsBrackets).":&nbsp;".count($observedObjectsFromCatalog) . " / " . $numberOfObjects),
                        "L",array(100),50);
if($partof)
  $content="<a href=\"".$baseURL."index.php?indexAction=view_observer_catalog&amp;catalog=".urlencode($cat)."&amp;user=".urlencode($_GET['user'])."&amp;partof=0\">".LangShowWithoutPartOfs."</a>"; 			
else
  $content="<a href=\"".$baseURL."index.php?indexAction=view_observer_catalog&amp;catalog=".urlencode($cat)."&amp;user=".urlencode($_GET['user'])."&amp;partof=1\">".LangShowWithPartOfs."</a>";			
$objPresentations->line(array($content),"L",array(100),50);
$resultarray=$objObject->getObjectsFromCatalog($cat);
echo "<table style=\"width:100%\">";
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
	if (in_array($object, $observedObjectsFromCatalog)) 
  { echo "<td style=\"background: #33FF00; padding: 5px 5px 5px 5px; text-align: center;\">";
		echo "<a title=\"".$ref."\" href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;object=".urlencode($object)."&amp;observer=".urlencode($_GET['user'])."\" style=\"color: #000000;\">".$object."</a>";
		echo "</td>";
  }
	else
	  if ($partof && in_array($object, $observedObjectsFromCatalogPartOf)) 
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
echo "</div>";

?>
