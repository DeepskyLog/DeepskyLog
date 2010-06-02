<?php // search.php - menu which allows the user to search the observation database 
echo "<div class=\"menuDiv\">";
echo "<p  class=\"menuHead\">"."Search title"."</p>";
if($loggedUser) {
  // TODO : Add the action the method utilitiesDispatchIndexAction in lib/util.php 
  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=new_module_result_query_observations&amp;user=".urlencode($loggedUser)."\">"."New entry 1"."</a><br />";
}
// TODO : Add the action the method utilitiesDispatchIndexAction in lib/util.php 
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=new_module_all_observations\" >"."New entry 2"."</a><br />";
echo "</div>";
?>
