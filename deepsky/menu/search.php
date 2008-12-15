<?php
// search.php
// menu which allows the user to search the observation database 

echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
echo "<tr>";
echo "<th valign=\"top\">";
echo LangSearchMenuTitle;
echo "</th>";
echo "</tr>";
echo "<tr>";
echo "<td>";
echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
if(isset($_SESSION['deepskylog_id']))
{ if($objObserver->getRole($_SESSION['deepskylog_id'])!="2")                    // user is not in waitlist
    if(array_key_exists('deepskylog_id',$_SESSION)&&($_SESSION['deepskylog_id']!="admin")) // admin doesn't have own observations
    { echo "<tr align=\"left\"  height=\"25px\">";
      echo "<td>";
      echo "<a class=\"mainlevel\" href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;observer=".urlencode($_SESSION['deepskylog_id'])."\">".LangSearchMenuItem1."</a>";
      echo "</td>";
      echo "</tr>";
    } 
}
$theDate = date('Ymd', strtotime('-1 year')) ;
$lastMinYear = substr($theDate,0,4);
$lastMinMonth = substr($theDate,4,2);
$lastMinDay = substr($theDate,6,2);
echo "<tr align=\"left\" height=\"25px\">";
echo "<td>";
echo "<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;catalog=%&amp;minyear=$lastMinYear&amp;minmonth=$lastMinMonth&amp;minday=$lastMinDay\" class=\"mainlevel\">".LangSearchMenuItem8."</a>";
echo "</td>";
echo "</tr>";
echo "<tr align=\"left\" height=\"25px\">";
echo "<td>";
echo "<a href=\"".$baseURL."index.php?indexAction=query_observations\" class=\"mainlevel\">".LangSearchMenuItem3."</a>";
echo "</td>";
echo "</tr>";
echo "<tr align=\"left\" height=\"25px\">";
echo "<td>";
echo "<a href=\"".$baseURL."index.php?indexAction=query_objects\" class=\"mainlevel\">".LangSearchMenuItem5."</a>";
echo "</td>";
echo "</tr>";
echo "<tr align=\"left\" height=\"25px\">";
echo "<td>";
echo "<a href=\"".$baseURL."index.php?indexAction=rank_observers\" class=\"mainlevel\">".LangSearchMenuItem6."</a>";
echo "</td>";
echo "</tr>";
echo "<tr align=\"left\" height=\"25px\">";
echo "<td>";
echo "<a href=\"".$baseURL."index.php?indexAction=rank_objects\" class=\"mainlevel\">".LangSearchMenuItem7."</a>";
echo "<tr align=\"left\" height=\"25px\">";
echo "<td>";
echo "<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;catalog=%\" class=\"mainlevel\">".LangSearchMenuItem2."</a>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</td>";
echo "</tr>";
echo "</table>";
?>
