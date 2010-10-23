<?php 
// out.php
// menu which allows the user to logout from deepskylog

logout();

function logout()
{ global $baseURL ;
  echo "<div class=\"menuDiv\">";
  echo "<p class=\"menuHead\">".LangLogoutMenuTitle."</p>";
  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=logout&amp;title=".urlencode(LangLogoutMenuItem1)."\">".LangLogoutMenuItem1."</a>";
  echo "</div>";
}
?>
