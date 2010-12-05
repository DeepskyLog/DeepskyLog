<?php
// location.php
// menu which allows the user to change its standard location

global $inIndex,$loggedUser,$objUtil;

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(LangExcpetion001);
elseif(!($objUtil->checkAdminOrUserID($loggedUser))) throw new Exception(LangExcpetion012);
else menu_location();

function menu_location()
{ global $baseURL,$loggedUser, 
         $objLocation,$objObject,$objObserver;
  if($loggedUser)
  { echo "<div class=\"menuDivExtended\">";
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
		$result=$objLocation->getSortedLocations('name',$loggedUser,1);
	  $loc=$objObserver->getObserverProperty($loggedUser,'stdlocation');	
		if($result)
		{ echo "<select name=\"activateLocation\" class=\"menuField menuDropdown\" onchange=\"location=this.options[this.selectedIndex].value;\">";
	    while(list($key, $value) = each($result))
		    echo "<option ".(($value==$loc)?"selected=\"selected\"":"")." value=\"".$link."&amp;activeLocationId=$value\">".$objLocation->getLocationPropertyFromId($value,'name')."</option>";
		  echo "</select>";
		}
		echo "</div>";
		$link="";
	}
}
?>