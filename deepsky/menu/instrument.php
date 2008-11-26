<?php
// instrument.php
// menu which allows the user to change its standard instrument

echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
echo "<tr>";
echo "<th valign=\"top\">\n";
echo LangInstrumentMenuTitle;
echo "</th>";
echo "</tr>";
echo "<tr>";
echo "<td>";
if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && ($_SESSION['module']=='deepsky')) 
{ $link=$baseURL."index.php?";
	reset($_GET);
	while(list($key,$value)=each($_GET))
	  $link.=$key.'='.$value.'&amp;';
	if(array_key_exists('activeTelescopeId',$_GET) && $_GET['activeTelescopeId'])
  { $objObserver->setStandardTelescope($_SESSION['deepskylog_id'], $_GET['activeTelescopeId']);
	  if(array_key_exists('QO',$_SESSION))
		  $_SESSION['QO']=$objObject->getObjectVisibilities($_SESSION['QO']);
	  if(array_key_exists('QOP',$_SESSION))
		  $_SESSION['QOP']=$objObject->getObjectVisibilities($_SESSION['QOP']);
	  if(array_key_exists('QOL',$_SESSION))
		  $_SESSION['QOL']=$objObject->getObjectVisibilities($_SESSION['QOL']);
  }
	$result=$objInstrument->getSortedInstruments('name',$_SESSION['deepskylog_id']);
  $instr=$objObserver->getStandardTelescope($_SESSION['deepskylog_id']);	
	echo("<select style=\"width: 140px\" onchange=\"location = this.options[this.selectedIndex].value;\" name=\"activateTelescope\">\n");
  while(list($key, $value) = each($result))
		echo("<option ".(($value==$instr)?"selected":"")." value=\""  . $link . "&amp;activeTelescopeId=$value\">" . $objInstrument->getInstrumentName($value) . "</option>\n");
	echo("</select>\n");
}
echo "</td>";
echo "</tr>";
echo "</table>";
?>
