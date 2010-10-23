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
	{ echo "<div class=\"menuDivExtended\">";
		echo "<p   class=\"menuHead\">".LangInstrumentMenuTitle."&nbsp;-&nbsp;"."<a href=\"".$baseURL."index.php?indexAction=add_instrument\">".LangManage."</a>"."</p>";
	  $link=$baseURL."index.php?";
		reset($_GET);
		while(list($key,$value)=each($_GET))
		  $link.=$key.'='.$value.'&amp;';
		if(array_key_exists('activeTelescopeId',$_GET) && $_GET['activeTelescopeId'])
	  { $objObserver->setObserverProperty($loggedUser,'stdtelescope', $_GET['activeTelescopeId']);
		  if(array_key_exists('Qobj',$_SESSION))
			  $_SESSION['Qobj']=$objObject->getObjectVisibilities($_SESSION['Qobj']);
	  }
		$result=$objInstrument->getSortedInstruments('name',$loggedUser,1);
	  $instr=$objObserver->getObserverProperty($loggedUser,'stdtelescope');	
		if($result)
		{ echo "<select name=\"activateTelescope\" class=\"menuField menuDropdown\" onchange=\"location=this.options[this.selectedIndex].value;\">";
	    while(list($key, $value) = each($result))
			  echo("<option ".(($value==$instr)?"selected=\"selected\"":"")." value=\""  . $link . "&amp;activeTelescopeId=$value\">" . $objInstrument->getInstrumentPropertyFromId($value,'name') . "</option>");
		  echo "</select>";
		}
		echo "</div>";
		$link="";
	}
}
?>
