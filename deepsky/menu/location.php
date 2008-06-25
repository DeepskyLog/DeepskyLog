<?php
// location.php
// menu which allows the user to change its standard location
include_once "../lib/locations.php";
include_once "../lib/objects.php";
include_once "../lib/observers.php";
$observer=new Observers;
$location=new Locations;
$object=new Objects;
echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";

echo "<tr>";
echo "<th valign=\"top\">\n";
echo LangLocationMenuTitle;
echo "</th>";
echo "</tr>";

echo "<tr>";
echo "<td>";
if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id'] != "admin")) // admin doesn't have to add a new observation
{
  $link = $baseURL . "deepsky/index.php?";
	reset($_GET);
	while(list($key,$value)=each($_GET))
	  $link .= $key . '=' . $value . '&amp;';
	if(array_key_exists('activeLocationId',$_GET) && $_GET['activeLocationId'])
  {
		$observer->setStandardLocation($_SESSION['deepskylog_id'], $_GET['activeLocationId']);
	  if(array_key_exists('QO',$_SESSION))
		  $_SESSION['QO']=$object->getObjectVisibilities($_SESSION['QO']);
	  if(array_key_exists('QOP',$_SESSION))
		  $_SESSION['QOP']=$object->getObjectVisibilities($_SESSION['QOP']);
	  if(array_key_exists('QOL',$_SESSION))
		  $_SESSION['QOL']=$object->getObjectVisibilities($_SESSION['QOP']);
  }
	$result=$location->getSortedLocations('name',$_SESSION['deepskylog_id']);
  $loc=$observer->getStandardLocation($_SESSION['deepskylog_id']);	
	echo("<select style=\"width: 140px\" onchange=\"location = this.options[this.selectedIndex].value;\" name=\"activateLocation\">\n");
  while(list($key, $value) = each($result))
    if($value==$loc)
		  echo("<option selected value=\""  . $link . "&amp;activeLocationId=$value\">" . $location->getName($value) . "</option>\n");
    else
		  echo("<option value=\""  . $link . "activeLocationId=$value\">" . $location->getName($value) . "</option>\n");
	echo("</select>\n");
}
echo "</td>";
echo "</tr>";
echo "</table>";
?>
