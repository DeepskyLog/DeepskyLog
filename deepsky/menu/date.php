<?php // date.php - menu which allows the user to change the date

if($loggedUser) 
{ echo "  <script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/CalendarPopupCC.js\"></script>";
  echo "<div class=\"menuDivExtended\">";
  $link=$baseURL."index.php?";
  $link2="index.php?";
  reset($_GET);
  while(list($key,$value)=each($_GET))
    if(!(in_array($key,array('changeDay','changeMonth','changeYear'))))
      $link.=$key.'='.$value.'&';
  echo "  <script type=\"text/javascript\" >";
  echo "  var cal = new CalendarPopup();";
  echo "  function SetDate(y,m,d)";
  echo "  { thelocation=\"".($link2)."\";
            thelocation=thelocation+'changeDay='+d;
            thelocation=thelocation+'&changeMonth='+m;
            thelocation=thelocation+'&changeYear='+y;
            window.location=thelocation;";                   
  echo "  }";
  echo "  </script>";
      
  $today=date('Ymd',strtotime('today'));
  /*
  $thisYear=substr($today,0,4);
  $thisMonth=substr($today,4,2);
  $thisDay=substr($today,6,2);
  */
  //temp suggestion by David to allow some testing to continue on trunk
  $thisYear=date("Y");
  $thisMonth=date("n");
  $thisDay=date("j");

  $DateCalender =  "<a href=\"#\" 
                       onclick=\"cal.showNavigationDropdowns();
                                 cal.setReturnFunction('SetDate');
                                 cal.showCalendar('DateAnchor');
                                 return false;\" 
                       name=\"DateAnchor\" 
                       id=\"DateAnchor\">" . LangDate . "</a>";

  $today = "<a href=\"" . $link . "&amp;changeDay=". $thisDay . "&amp;changeMonth=" . $thisMonth . "&amp;changeYear=" . $thisYear ."\">" . LangToday . "</a>";
  echo "<p class=\"menuHead\">" . $DateCalender . " - " . $today . "</p>";

  if (array_key_exists('globalMonth',$_SESSION) && $_SESSION['globalMonth']) {
  } else {
    $_SESSION['globalYear']=$thisYear;
    $_SESSION['globalMonth']=$thisMonth;
    $_SESSION['globalDay']=$thisDay;
  }
  if(array_key_exists('changeMonth',$_GET) && $_GET['changeMonth'])
  { $_SESSION['globalMonth'] = $_GET['changeMonth'];
    if(array_key_exists('Qobj',$_SESSION))
      $_SESSION['Qobj']=$objObject->getObjectRisSetTrans($_SESSION['Qobj']);
  }
  if(array_key_exists('changeYear',$_GET) && $_GET['changeYear'])
  { $_SESSION['globalYear'] = $_GET['changeYear'];
    if(array_key_exists('Qobj',$_SESSION))
      $_SESSION['Qobj']=$objObject->getObjectRisSetTrans($_SESSION['Qobj']);
  }
  if(array_key_exists('changeDay',$_GET) && $_GET['changeDay'])
  { $_SESSION['globalDay'] = $_GET['changeDay'];
    if(array_key_exists('Qobj',$_SESSION))
      $_SESSION['Qobj']=$objObject->getObjectRisSetTrans($_SESSION['Qobj']);
  }
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
?>
