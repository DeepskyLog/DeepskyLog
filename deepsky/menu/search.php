<?php // search.php - menu which allows the user to search the observation database 
$theDate      = date('Ymd', strtotime('-1 year')) ;
$lastMinYear  = substr($theDate,0,4);
$lastMinMonth = substr($theDate,4,2);
$lastMinDay   = substr($theDate,6,2);
$link="";
reset($_GET);
while(list($key,$value)=each($_GET))
  if($key!="menuView")
    $link.="&amp;".$key."=".urlencode($value);
echo "<div class=\"menuDiv\">";
echo "<p   class=\"menuHead\">";
if($menuView=="collapsed")
  echo "<a href=\"".$baseURL."index.php?menuView=expanded".$link."\">+</a> ";
else
  echo "<a href=\"".$baseURL."index.php?menuView=collapsed".$link."\">-</a> ";
echo LangSearchMenuTitle."</p>";
if($menuView=="collapsed")
{ echo "<select name=\"view\" class=\"menuField menuDropdown\" onchange=\"{location=this.options[this.selectedIndex].value;}\">";
  echo "<option value=\"".$baseURL."index.php\">"."&nbsp;"."</option>";
  if($loggedUser
  && ($objObserver->getObserverProperty($loggedUser,'role',2)!="2")                    // user is not in waitlist
  &&($loggedUser!="admin"))                                                            // admin doesn't have own observations
    echo "<option value=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;observer=".urlencode($loggedUser)."\">".LangSearchMenuItem1."</option>";
  echo "<option value=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;myLanguages=true&amp;catalog=%&amp;minyear=$lastMinYear&amp;minmonth=$lastMinMonth&amp;minday=$lastMinDay\">".LangSearchMenuItem8."</option>";
  echo "<option value=\"".$baseURL."index.php?indexAction=rank_observers\">".LangSearchMenuItem6."</option>";
  echo "<option value=\"".$baseURL."index.php?indexAction=rank_objects\">".LangSearchMenuItem7."</option>";
  echo "<option value=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;catalog=%\">".LangSearchMenuItem2."</option>";
  echo "</select>";
}
else
{ if((isset($_SESSION['deepskylog_id']))
  && ($objObserver->getObserverProperty($_SESSION['deepskylog_id'],'role',2)!="2")                    // user is not in waitlist
  && (array_key_exists('deepskylog_id',$_SESSION)&&($_SESSION['deepskylog_id']!="admin")))           // admin doesn't have own observations
    echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;observer=".urlencode($_SESSION['deepskylog_id'])."\">".LangSearchMenuItem1."</a><br />";
  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;myLanguages=true&amp;catalog=%&amp;minyear=$lastMinYear&amp;minmonth=$lastMinMonth&amp;minday=$lastMinDay\">".LangSearchMenuItem8."</a><br />";
  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=rank_observers\">".LangSearchMenuItem6."</a><br />";
  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=rank_objects\">".LangSearchMenuItem7."</a><br />";
  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;catalog=%\">".LangSearchMenuItem2."</a><br />";
}
echo "</div>";
?>
