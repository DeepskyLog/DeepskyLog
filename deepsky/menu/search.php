<?php // search.php - menu which allows the user to search the observation database 
$theDate      = date('Ymd', strtotime('-1 year')) ;
$lastMinYear  = substr($theDate,0,4);
$lastMinMonth = substr($theDate,4,2);
$lastMinDay   = substr($theDate,6,2);
echo "<div class=\"menuDiv\">";
echo "<p   class=\"menuHead\">".LangSearchMenuTitle."</p>";
if((isset($_SESSION['deepskylog_id']))
&& ($objObserver->getObserverProperty($_SESSION['deepskylog_id'],'role',2)!="2")                    // user is not in waitlist
&& (array_key_exists('deepskylog_id',$_SESSION)&&($_SESSION['deepskylog_id']!="admin")))           // admin doesn't have own observations
  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;observer=".urlencode($_SESSION['deepskylog_id'])."\">".LangSearchMenuItem1."</a><br />";
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;myLanguages=true&amp;catalog=%&amp;minyear=$lastMinYear&amp;minmonth=$lastMinMonth&amp;minday=$lastMinDay\">".LangSearchMenuItem8."</a><br />";
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=rank_observers\">".LangSearchMenuItem6."</a><br />";
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=rank_objects\">".LangSearchMenuItem7."</a><br />";
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;catalog=%\">".LangSearchMenuItem2."</a><br />";
echo "</div>";
?>
