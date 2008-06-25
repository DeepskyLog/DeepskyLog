<?php

// change.php
// menu which allows the user to add or change things in the database
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

include_once "../lib/observers.php";

echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">\n
      <tr>\n
      <th valign=\"top\">\n";

echo (LangChangeMenuTitle);

echo "</th>\n</tr>\n<tr>\n<td>\n
      <table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";

echo "<tr align=\"left\">\n<td>\n<a href=\"common/account_details.php\" class=\"mainlevel\">\n";

echo (LangChangeMenuItem1);

echo "</a>\n</td>\n</tr>\n";

//if($_SESSION['deepskylog_id'] != "admin") // admin doesn't have to add a new observation
//{
//   echo "<tr align=\"left\">\n<td>\n<a href=\"deepsky/add_observation.php\" class=\"mainlevel\">";
//
//   echo (LangChangeMenuItem2);
//
//   echo "</a>\n</td>\n</tr>\n";
//}

echo "<tr align=\"left\">\n<td>\n<a href=\"common/add_instrument.php\" class=\"mainlevel\">";

echo (LangChangeMenuItem3);

echo "</a>\n</td>\n</tr>\n";

echo "<tr align=\"left\">\n<td>\n<a href=\"common/add_site.php\" class=\"mainlevel\">";

echo (LangChangeMenuItem4);

echo "</a>\n</td>\n</tr>\n";

//echo "<tr align=\"left\">\n<td>\n<a href=\"deepsky/add_object.php\" class=\"mainlevel\">";
//
//echo (LangChangeMenuItem5);
//
//echo "</a>\n</td>\n</tr>\n";

echo "</table>\n</td>\n
      </tr>\n
      </table>\n";

?>
