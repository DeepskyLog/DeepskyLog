<?php // search.php - menu which allows the user to search the observation database
echo "<ul class=\"nav navbar-nav\">
			  <li class=\"dropdown\">
	       <a href=\"http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] ."#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Search title<b class=\"caret\"></b></a>";
echo " <ul class=\"dropdown-menu\">";
if($loggedUser) {
  // Add the action the method utilitiesDispatchIndexAction in lib/util.php
  echo "<li><a href=\"".$baseURL."index.php?indexAction=new_module_result_query_observations&amp;user=".urlencode($loggedUser)."\">"."New entry 1"."</a></li>";
}
// Add the action the method utilitiesDispatchIndexAction in lib/util.php 
echo "<li><a href=\"".$baseURL."index.php?indexAction=new_module_all_observations\" >"."New entry 2"."</a></li>";
echo " </ul>";
echo "</li>
			  </ul>";
?>
