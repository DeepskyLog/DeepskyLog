<?php
// search.php
// menu which allows the user to search the observation database 
// version 3.1, DE 20061119
//include_once "../lib/setup/vars.php";
//include_once "../lib/util.php";
//$util = new Util();
//$util->checkUserInput();
echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">\n
      <tr>\n
      <th valign=\"top\">\n";
echo (LangSearchMenuTitle);
echo "</th>\n</tr>\n<tr>\n<td>\n
      <table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
if(isset($_SESSION['deepskylog_id'])) // logged in
{
  include_once "../lib/observers.php";
  $obs = new Observers;
  if($obs->getRole($_SESSION['deepskylog_id']) != "2") // user is not in waitlist
  {
    if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id'] != "admin")) // admin doesn't have own observations
    {
      echo "<tr align=\"left\"  height=\"25px\">\n<td>\n<a class=\"mainlevel\" href=\"deepsky/index.php?indexAction=result_selected_observations&observer=" . $_SESSION['deepskylog_id'] . "\">";
      echo (LangSearchMenuItem1);
      echo "</a>\n</td>\n</tr>\n";
    }
  } 
}
$theDate = date('Ymd', strtotime('-1 month')) ;
$lastMinYear = substr($theDate,0,4);
$lastMinMonth = substr($theDate,4,2);
$lastMinDay = substr($theDate,6,2);
echo "<tr align=\"left\" height=\"25px\">\n<td>\n<a href=\"deepsky/index.php?indexAction=result_selected_observations&catalogue=*&amp;minyear=$lastMinYear&amp;minmonth=$lastMinMonth&amp;minday=$lastMinDay\" class=\"mainlevel\">";
echo (LangSearchMenuItem8);
echo "</a>\n</td>\n</tr>\n";
echo "<tr align=\"left\" height=\"25px\">\n<td>\n<a href=\"deepsky/index.php?indexAction=query_observations\" class=\"mainlevel\">";
echo (LangSearchMenuItem3);
echo "</a>\n</td>\n</tr>\n";
echo "<tr align=\"left\" height=\"25px\">\n<td>\n<a href=\"deepsky/index.php?indexAction=query_objects\" class=\"mainlevel\">";
echo (LangSearchMenuItem5);
echo "</a>\n</td>\n</tr>\n";
echo "<tr align=\"left\" height=\"25px\">\n<td>\n<a href=\"deepsky/index.php?indexAction=rank_observers\" class=\"mainlevel\">";
echo (LangSearchMenuItem6);
echo "</a>\n</td>\n</tr>\n";
echo "<tr align=\"left\" height=\"25px\">\n<td>\n<a href=\"deepsky/index.php?indexAction=rank_objects\" class=\"mainlevel\">";
echo (LangSearchMenuItem7);
echo "<tr align=\"left\" height=\"25px\">\n<td>\n<a href=\"deepsky/index.php?indexAction=result_selected_observations&catalogue=*\" class=\"mainlevel\">";
echo (LangSearchMenuItem2);
echo "</a>\n</td>\n</tr>\n";
echo "</table>\n</td>\n
      </tr>\n
      </table>\n";
?>
