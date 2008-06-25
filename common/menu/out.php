<?php
// out.php
// menu which allows the user to logout from deepskylog
//include_once "../lib/observers.php";
//include_once "../lib/util.php";
//$util = new Util();
//$util->checkUserInput();
echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">\n
      <tr>\n
      <th valign=\"top\">\n";
echo (LangLogoutMenuTitle);
echo "</th>\n</tr>\n<tr>\n<td>\n
      <table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
echo "<tr align=\"left\">\n<td>\n<a href=\"common/logout.php\" class=\"mainlevel\">\n";
echo (LangLogoutMenuItem1);
echo "</a>\n</td>\n</tr>\n";
?>
