<?php 
// instrument.php
// menu which allows the user to change its standard instrument

global $inIndex,$loggedUser,$objUtil;

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(LangExcpetion001);
elseif(!($objUtil->checkAdminOrUserID($loggedUser))) throw new Exception(LangExcpetion012);
else menu_instrument();

function menu_instrument()
{ global $baseURL,$loggedUser,
         $objInstrument,$objObject,$objObserver;
  if($loggedUser) 
	{ 
		if(array_key_exists('activeTelescopeId',$_GET) && $_GET['activeTelescopeId'])
	  { $objObserver->setObserverProperty($loggedUser,'stdtelescope', $_GET['activeTelescopeId']);
		  if(array_key_exists('Qobj',$_SESSION))
			  $_SESSION['Qobj']=$objObject->getObjectVisibilities($_SESSION['Qobj']);
	  }
		$result=$objInstrument->getSortedInstruments('name',$loggedUser,1);
	  $instr=$objObserver->getObserverProperty($loggedUser,'stdtelescope');	
		if($result) 
		{
		  echo "<li>
             <a href=\"http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] ."#\">" . $objInstrument->getInstrumentPropertyFromId($instr,'name') ."<span class=\"arrow\"></span></a>";
      echo " <ul>";
      
			$url = "http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
      if ($url == $baseURL || $url == $baseURL."#" || $url = $baseURL."index.php") {
        $url = $baseURL . "index.php?title=Home";
      }
      while(list($key, $value) = each($result)) {
        echo "  <li><a href=\"" . $url ."&amp;activeTelescopeId=" . $value . "\">".$objInstrument->getInstrumentPropertyFromId($value,'name')."</a></li>";
	    }
		}
    echo " </ul>";
    echo "</li>";
	}
}
?>
