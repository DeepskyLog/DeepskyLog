<?php // change.php - menu which allows the user to add or change things in the database
	echo "<ul class=\"nav navbar-nav\">
			  <li class=\"dropdown\">
	       <a href=\"http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] ."#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">" . LangChangeMenuTitle ."<b class=\"caret\"></b></a>";
	echo " <ul class=\"dropdown-menu\">";
if($loggedUser!="admin") {
  // TODO : Add the action the method utilitiesDispatchIndexAction in lib/util.php
  echo "<li><a href=\"".$baseURL."index.php?indexAction=new_module_add_observation\">"."Change item 1"."</a></li>";
}
echo "<li><a href=\"".$baseURL."index.php?indexAction=add_instrument\">".LangChangeMenuItem3."</a></li>";
echo "<li><a href=\"".$baseURL."index.php?indexAction=add_location\">".LangChangeMenuItem4."</a></li>";
// TODO : Add the action the method utilitiesDispatchIndexAction in lib/util.php
echo "<li><a href=\"".$baseURL."index.php?indexAction=comets_add_object\" >"."Change item 3"."</a></li>";
echo " </ul>";
echo "</li>
			  </ul>";

?>
