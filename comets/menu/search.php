<?php // search.php - menu which allows the user to search the observation database 
echo "<div class=\"menuDiv\">";
echo "<p  class=\"menuHead\">".LangSearchMenuTitle."</p>";
if($loggedUser)
  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=".urlencode($loggedUser)."\">".LangSearchMenuItem1."</a><br />";
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=comets_all_observations\" >".LangSearchMenuItem2."</a><br />";
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=comets_query_observations\" >".LangSearchMenuItem3."</a><br />";
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=comets_view_objects\" >".LangSearchMenuItem4."</a><br />";
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=comets_query_objects\" >".LangSearchMenuItem5."</a><br />";
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=comets_rank_observers\" >".LangSearchMenuItem6."</a><br />";
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=comets_rank_objects\" >".LangSearchMenuItem7."</a><br />";
echo "</div>";
?>
