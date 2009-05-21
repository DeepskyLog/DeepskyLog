<?php // instrument.php - menu which allows the user to change its standard instrument
if($loggedUser) 
{ echo "<div class=\"menuDiv\">";
	echo "<p   class=\"menuHead\">".LangInstrumentMenuTitle."&nbsp;-&nbsp;"."<a href=\"".$baseURL."index.php?indexAction=add_instrument\">".LangManage."</a>"."</p>";
  $link=$baseURL."index.php?";
	reset($_GET);
	while(list($key,$value)=each($_GET))
	  $link.=$key.'='.$value.'&amp;';
	if(array_key_exists('activeTelescopeId',$_GET) && $_GET['activeTelescopeId'])
  { $objObserver->setObserverProperty($_SESSION['deepskylog_id'],'stdtelescope', $_GET['activeTelescopeId']);
	  if(array_key_exists('Qobj',$_SESSION))
		  $_SESSION['Qobj']=$objObject->getObjectVisibilities($_SESSION['Qobj']);
  }
	$result=$objInstrument->getSortedInstruments('name',$_SESSION['deepskylog_id']);
  $instr=$objObserver->getObserverProperty($_SESSION['deepskylog_id'],'stdtelescope');	
	echo "<select name=\"activateTelescope\" class=\"menuField menuDropdown\" onchange=\"location=this.options[this.selectedIndex].value;\">";
  while(list($key, $value) = each($result))
		echo("<option ".(($value==$instr)?"selected=\"selected\"":"")." value=\""  . $link . "&amp;activeTelescopeId=$value\">" . $objInstrument->getInstrumentPropertyFromId($value,'name') . "</option>\n");
	echo "</select>";
	echo "</div>";
	$link="";
}
?>
