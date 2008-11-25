<?php
// location.php
// menu which allows the user to change its standard location

echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
echo "<tr>";
echo "<th valign=\"top\">\n";
echo LangLocationMenuTitle;
echo "</th>";
echo "</tr>";
echo "<tr>";
echo "<td>";
if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id'] != "admin")) // admin doesn't have to add a new observation
{ $link=$baseURL."deepsky/index.php?";
	reset($_GET);
	while(list($key,$value)=each($_GET))
	  $link.=$key.'='.$value.'&amp;';
	if(array_key_exists('activeLocationId',$_GET) && $_GET['activeLocationId'])
  { $objObserver->setStandardLocation($_SESSION['deepskylog_id'], $_GET['activeLocationId']);
	  if(array_key_exists('QO',$_SESSION))
		  $_SESSION['QO']=$objObject->getObjectVisibilities($_SESSION['QO']);
	  if(array_key_exists('QOP',$_SESSION))
		  $_SESSION['QOP']=$objObject->getObjectVisibilities($_SESSION['QOP']);
	  if(array_key_exists('QOL',$_SESSION))
		  $_SESSION['QOL']=$objObject->getObjectVisibilities($_SESSION['QOL']);
  }
	$result=$objLocation->getSortedLocations('name',$_SESSION['deepskylog_id']);
  $loc=$objObserver->getStandardLocation($_SESSION['deepskylog_id']);	
	echo "<select style=\"width: 140px\" onchange=\"location=this.options[this.selectedIndex].value;\" name=\"activateLocation\">";
  while(list($key, $value) = each($result))
	  echo "<option ".(($value==$loc)?"selected":"")." value=\"".$link."&amp;activeLocationId=$value\">".$objLocation->getLocationName($value)."</option>";
	echo "</select>";
}
echo "</td>";
echo "</tr>";
echo "</table>";
?>
