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
echo("<select style=\"width: 140px\" onchange=\"location = this.options[this.selectedIndex].value;\" name=\"search\" \">");
echo "<option> &nbsp; </option>";
if(isset($_SESSION['deepskylog_id']))
{ if($objObserver->getRole($_SESSION['deepskylog_id'])!="2")                    // user is not in waitlist
    if(array_key_exists('deepskylog_id',$_SESSION)&&($_SESSION['deepskylog_id']!="admin")) // admin doesn't have own observations
      echo "<option ".((($objUtil->checkGetKey('indexAction')=='result_selected_observations')&&($objUtil->checkGetKey('observer')==$_SESSION['deepskylog_id']))?"selected ":"")."value=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;observer=".urlencode($_SESSION['deepskylog_id'])."\">".LangSearchMenuItem1."</option>";
  $theDate = date('Ymd', strtotime('-1 year')) ;
  $lastMinYear = substr($theDate,0,4);
  $lastMinMonth = substr($theDate,4,2);
  $lastMinDay = substr($theDate,6,2);
  echo "<option  ".((($objUtil->checkGetKey('indexAction')=='result_selected_observations')
                   &&($objUtil->checkGetKey('catalogue')=='%')
                   &&($objUtil->checkGetKey('minyear')==$lastMinYear)
                   &&($objUtil->checkGetKey('minmonth')==$lastMinMonth)
                   &&($objUtil->checkGetKey('minday')==$lastMinDay))?"selected ":"")."value=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;catalogue=%&amp;minyear=$lastMinYear&amp;minmonth=$lastMinMonth&amp;minday=$lastMinDay\">".LangSearchMenuItem8."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='query_observations')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=query_observations\">".LangSearchMenuItem3."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='query_objects')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=query_objects\">".LangSearchMenuItem5."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='rank_observers')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=rank_observers\">".LangSearchMenuItem6."</option>";
  echo "<option ".(($objUtil->checkGetKey('indexAction')=='rank_objects')?"selected ":"")."value=\"".$baseURL."index.php?indexAction=rank_objects\">".LangSearchMenuItem7."</option>";
  echo "<option ".((($objUtil->checkGetKey('indexAction')=='result_selected_observations')&&($objUtil->checkGetKey('catalogue')=='%'))?"selected ":"")."value=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;catalogue=%\">".LangSearchMenuItem2."</option>";                                                            
}
echo("</select>\n");
echo "</td>";
echo "</tr>";
echo "</table>";
?>
