<?php

// search.php
// menu which allows the user to search the observation database 


$obs = new Observers;

echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">\n
      <tr>\n
      <th valign=\"top\">\n";

echo (LangSearchMenuTitle);

echo "</th>\n</tr>\n<tr>\n<td>\n
      <table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";

if($_SESSION['deepskylog_id']) // logged in
{
   if($obs->getRole($_SESSION['deepskylog_id']) != "2") // user is not in waitlist
   {
      if($_SESSION['deepskylog_id'] != "admin") // admin doesn't have own observations
      {
      echo "<tr align=\"left\">\n<td>\n<a class=\"mainlevel\" href=\"comets/result_query_observations.php?user=" . $_SESSION['deepskylog_id'] . "\">";
      echo (LangSearchMenuItem1);
      echo "</a>\n</td>\n</tr>\n";
      }
   } 
}


echo "<tr align=\"left\">\n<td>\n<a href=\"comets/all_observations.php\" class=\"mainlevel\">";

echo (LangSearchMenuItem2);

echo "</a>\n</td>\n</tr>\n";

echo "<tr align=\"left\">\n<td>\n<a href=\"comets/query_observations.php\" class=\"mainlevel\">";

echo (LangSearchMenuItem3);

echo "</a>\n</td>\n</tr>\n";

echo "<tr align=\"left\">\n<td>\n<a href=\"comets/view_objects.php\" class=\"mainlevel\">";

echo (LangSearchMenuItem4);

echo "</a>\n</td>\n</tr>\n";

echo "<tr align=\"left\">\n<td>\n<a href=\"comets/query_objects.php\" class=\"mainlevel\">";

echo (LangSearchMenuItem5);

echo "</a>\n</td>\n</tr>\n";

echo "<tr align=\"left\">\n<td>\n<a href=\"comets/rank_observers.php\" class=\"mainlevel\">";

echo (LangSearchMenuItem6);

echo "</a>\n</td>\n</tr>\n";


echo "<tr align=\"left\">\n<td>\n<a href=\"comets/rank_objects.php\" class=\"mainlevel\">";

echo (LangSearchMenuItem7);

echo "</table>\n</td>\n
      </tr>\n
      </table>\n";

?>
