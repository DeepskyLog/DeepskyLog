<?php // location.php - menu which allows the user to change its standard location
if($loggedUser) 
{ echo "<div class=\"menuDiv\">";
	echo "<p   class=\"menuHead\">".LangLocationMenuTitle."&nbsp;-&nbsp;"."<a href=\"".$baseURL."index.php?indexAction=add_site\">".LangManage."</a>"."</p>";
  $link=$baseURL."index.php?";
	reset($_GET);
	while(list($key,$value)=each($_GET))
	  $link.=$key.'='.$value.'&amp;';
		if(array_key_exists('activeLocationId',$_GET) && $_GET['activeLocationId'])
	  { $objObserver->setObserverProperty($loggedUser,'stdlocation', $_GET['activeLocationId']);
		  if(array_key_exists('Qobj',$_SESSION))
			  $_SESSION['Qobj']=$objObject->getObjectVisibilities($_SESSION['Qobj']);
	  }
	$result=$objLocation->getSortedLocations('name',$loggedUser);
  $loc=$objObserver->getObserverProperty($loggedUser,'stdlocation');	
	echo "<select name=\"activateLocation\" class=\"menuField menuDropdown\" onchange=\"location=this.options[this.selectedIndex].value;\">";
  while(list($key, $value) = each($result))
	  echo "<option ".(($value==$loc)?"selected=\"selected\"":"")." value=\"".$link."&amp;activeLocationId=$value\">".$objLocation->getLocationPropertyFromId($value,'name')."</option>";
	echo "</select>";
	echo "</div>";
	$link="";
}
?>
