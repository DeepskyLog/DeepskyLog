<?php
// instrument.php
// menu which allows the user to change its standard instrument

global $inIndex,$loggedUser,$objUtil;

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(_("You need to be logged in as an administrator to execute these operations."));
elseif(!($objUtil->checkAdminOrUserID($loggedUser))) throw new Exception(_("You need to be logged in to execute these operations."));
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
	    echo "<ul class=\"nav navbar-nav\">
			      <li class=\"dropdown\">
	           <a href=\"http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] ."#\" class=\"dropdown-toggle navbar-btn\" data-toggle=\"dropdown\">" . $objInstrument->getInstrumentPropertyFromId($instr,'name')."<b class=\"caret\"></b></a>";
	    echo " <ul class=\"dropdown-menu\">";

			$url = "http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
      if ($url == $baseURL || $url == $baseURL."#" || $url = $baseURL."index.php") {
        $url = $baseURL . "index.php?title=Home";
      }
      foreach ($result as $key=>$value) {
        echo "  <li><a href=\"" . $url ."&amp;activeTelescopeId=" . $value . "\">".$objInstrument->getInstrumentPropertyFromId($value,'name')."</a></li>";
	    }

	    echo " </ul>";
	    echo "</li>
			      </ul>";
	  }
	}
}
?>
