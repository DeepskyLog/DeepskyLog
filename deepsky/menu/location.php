<?php
// location.php
// menu which allows the user to change its standard location

if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id']) 
{ echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
	echo "<tr>";
	echo "<th valign=\"top\">".LangLocationMenuTitle."&nbsp;-&nbsp;"."<a href=\"".$baseURL."index.php?indexAction=add_site\">".LangManage."</a>"."</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>";
  $link=$baseURL."index.php?";
	reset($_GET);
	while(list($key,$value)=each($_GET))
	  $link.=$key.'='.$value.'&amp;';
		if(array_key_exists('activeLocationId',$_GET) && $_GET['activeLocationId'])
	  { $objObserver->setObserverProperty($_SESSION['deepskylog_id'],'stdlocation', $_GET['activeLocationId']);
		  if(array_key_exists('Qobj',$_SESSION))
			  $_SESSION['Qobj']=$objObject->getObjectVisibilities($_SESSION['Qobj']);
	  }
	$result=$objLocation->getSortedLocations('name',$_SESSION['deepskylog_id']);
  $loc=$objObserver->getObserverProperty($_SESSION['deepskylog_id'],'stdlocation');	
	echo "<select onchange=\"location=this.options[this.selectedIndex].value;\" name=\"activateLocation\" class=\"inputfield\">";
  while(list($key, $value) = each($result))
	  echo "<option ".(($value==$loc)?"selected":"")." value=\"".$link."&amp;activeLocationId=$value\">".$objLocation->getLocationPropertyFromId($value,'name')."</option>";
	echo "</select>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	$link="";
}
?>
