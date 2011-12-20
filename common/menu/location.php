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
  { 
		if(array_key_exists('activeLocationId',$_GET) && $_GET['activeLocationId'])
	  { $objObserver->setObserverProperty($loggedUser,'stdlocation', $_GET['activeLocationId']);
		  if(array_key_exists('Qobj',$_SESSION))
			  $_SESSION['Qobj']=$objObject->getObjectVisibilities($_SESSION['Qobj']);
	  }
		$result=$objLocation->getSortedLocations('name',$loggedUser,1);
	  $loc=$objObserver->getObserverProperty($loggedUser,'stdlocation');

	  if($result)
	  {
	    echo "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>";
      echo "<li>
             <a href=\"http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] ."#\">" . $objLocation->getLocationPropertyFromId($loc,'name') ."<span class=\"arrow\"></span></a>";
      echo " <ul>";
      
	    while(list($key, $value) = each($result)) {
        echo "  <li><a href=\"http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] ."&amp;activeLocationId=" . $value . "\">".$objLocation->getLocationPropertyFromId($value,'name')."</a></li>";
	    }
		}
    echo " </ul>";
    echo "</li>";
	}
}
?>
