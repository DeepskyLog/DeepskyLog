<?php // instrument.php - menu which allows the user to change its standard instrument
if($loggedUser) 
{ echo "  <script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/CalendarPopupCC.js\"></script>";
  echo "  <script type=\"text/javascript\" >";
  echo "  var cal = new CalendarPopup();";
  echo "  function SetDate(y,m,d)";
  echo "  {";
  echo "    document.getElementById('day').value = d;";
  echo "    document.getElementById('month').value = m;";
  echo "    document.getElementById('year').value = y;";                           
  echo "  }";
  echo "  </script>";
  echo "<div class=\"menuDivExtended\">";
  echo "<p   class=\"menuHead\"><a href=\"#\" onclick=\"cal.showNavigationDropdowns();
                             cal.setReturnFunction('SetDate');
                             cal.showCalendar('DateAnchor');
                             return false;\" 
                    name=\"DateAnchor\" 
                    id=\"DateAnchor\">" . LangDate . "</a></p>";

  $today=date('Ymd',strtotime('today'));
  $theYear=substr($today,0,4);
  $theMonth=substr($today,4,2);
  $theDay=substr($today,6,2);
  
  $dateTimeText=date($dateformat, mktime(0, 0, 0, $theMonth, $theDay, $theYear));
  
  echo $dateTimeText;

  echo "</div>";
  
/*  $link=$baseURL."index.php?";
	reset($_GET);
	while(list($key,$value)=each($_GET)) {
	  $link.=$key.'='.$value.'&amp;';
	  print "TEST 1 " . $link;
	}

	  
	if(array_key_exists('activeTelescopeId',$_GET) && $_GET['activeTelescopeId'])
  { $objObserver->setObserverProperty($loggedUser,'stdtelescope', $_GET['activeTelescopeId']);
	  if(array_key_exists('Qobj',$_SESSION))
		  $_SESSION['Qobj']=$objObject->getObjectVisibilities($_SESSION['Qobj']);
  }
	$result=$objInstrument->getSortedInstruments('name',$loggedUser);
  $instr=$objObserver->getObserverProperty($loggedUser,'stdtelescope');	
	if($result)
	{ echo "<select name=\"activateTelescope\" class=\"menuField menuDropdown\" onchange=\"location=this.options[this.selectedIndex].value;\">";
    while(list($key, $value) = each($result))
		  echo("<option ".(($value==$instr)?"selected=\"selected\"":"")." value=\""  . $link . "&amp;activeTelescopeId=$value\">" . $objInstrument->getInstrumentPropertyFromId($value,'name') . "</option>");
	  echo "</select>";
	}
	echo "</div>";
	$link="";
*/
}
?>
