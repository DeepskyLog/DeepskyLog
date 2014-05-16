<?php 
// date.php
// menu which allows the user to change the date

global $inIndex,$loggedUser,$objUtil;

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(LangExcpetion001);
elseif(!($objUtil->checkAdminOrUserID($loggedUser))) throw new Exception(LangExcpetion012);
else menu_date();

function menu_date()
{ global $baseURL,$loggedUser,$thisDay,$thisMonth,$thisYear;
	if($loggedUser) 
	{ echo "  <script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/CalendarPopupCC.js\"></script>";
	  echo "<div class=\"menuDivExtended\">";
	  $link=$baseURL."index.php?";
	  reset($_GET);
	  while(list($key,$value)=each($_GET))
	    if(!(in_array($key,array('changeDay','changeMonth','changeYear'))))
	      $link.=$key.'='.urlencode($value).'&amp;';
	  $link2="index.php?";
	  reset($_GET);
	  while(list($key,$value)=each($_GET))
	    if(!(in_array($key,array('changeDay','changeMonth','changeYear'))))
	      $link2.=$key.'='.urlencode($value).'&';
	  $link2=substr($link2,0,strlen($link2)-1);
	  
	  echo "  <script type=\"text/javascript\" >";
	  echo "  /* <![CDATA[ */";
	  echo "  var cal = new CalendarPopup();";
	  echo "  function SetDate(y,m,d)";
	  echo "  { thelocation=\"".($link2)."\";
	            thelocation=thelocation+'&changeDay='+d;
	            thelocation=thelocation+'&changeMonth='+m;
	            thelocation=thelocation+'&changeYear='+y;
	            location.href=thelocation;";                   
	  echo "  }";
	  echo "  /* ]]> */";
	  echo "  </script>";
	     
	  
	  $DateCalender =  "<a href=\"#\" 
	                       onclick=\"cal.showNavigationDropdowns();
	                                 cal.setReturnFunction('SetDate');
	                                 cal.showCalendar('DateAnchor');
	                                 return false;\" 
	                       name=\"DateAnchor\" 
	                       id=\"DateAnchor\">" . LangDate . "</a>";
	
	  $today = "<a href=\"" . $link . "&amp;changeDay=". $thisDay . "&amp;changeMonth=" . $thisMonth . "&amp;changeYear=" . $thisYear ."\">" . LangToday . "</a>";
	  echo "<p class=\"menuHead\">" . $DateCalender . " - " . $today . "</p>";
	
	  
	  echo "<select name=\"riseday\" style=\"width:50px;\" id=\"riseday\" class=\"inputfield menuField menuDropdown\" onchange=\"location=this.options[this.selectedIndex].value;\">";
	  $numberOfDays = 31;
	  if ($_SESSION['globalMonth'] == 2 && $_SESSION['globalYear'] % 4 != 0) {
	    $numberOfDays = 28;
	  } else if ($_SESSION['globalMonth'] == 2 && $_SESSION['globalYear'] % 4 == 0) {
	    $numberOfDays = 29;
	  } else if ($_SESSION['globalMonth'] == 4 || $_SESSION['globalMonth'] == 6 || $_SESSION['globalMonth'] == 9 || $_SESSION['globalMonth'] == 11) {
	    $numberOfDays = 30;
	  }
	  for($i= 1;$i<=$numberOfDays;$i++)
	    echo "<option ".(($i==$_SESSION['globalDay'])?"selected=\"selected\"":"")." value=\"".$link."&amp;changeDay=$i\">".$i."</option>";
	  echo "</select>";
	  echo "<select name=\"risemonth\" style=\"width:95px;\" id=\"risemonth\" class=\"inputfield menuField menuDropdown\" onchange=\"location=this.options[this.selectedIndex].value;\">";
	  for($i= 1;$i<13;$i++)
	    echo "<option ".(($i==$_SESSION['globalMonth'])?"selected=\"selected\"":"")." onclick=\"setDays($i)\" value=\"".$link."&amp;changeMonth=$i\">".$GLOBALS['Month'.$i]."</option>";
	  echo "</select>";
	  echo "<select name=\"riseyear\" style=\"width:70px;\" id=\"riseyear\" class=\"inputfield menuField menuDropdown\" onchange=\"location=this.options[this.selectedIndex].value;\">";
	  for($i= $thisYear;$i<$thisYear + 10;$i++)
	    echo "<option ".(($i==$_SESSION['globalYear'])?"selected=\"selected\"":"")." value=\"".$link."&amp;changeYear=$i\">".$i."</option>";
	  echo "</select>";
	  
	  echo "</div>";
	  $link="";
	}
}
?>
