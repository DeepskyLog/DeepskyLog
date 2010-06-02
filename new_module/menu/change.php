<?php // change.php - menu which allows the user to add or change things in the database
echo "<div class=\"menuDiv\">";
echo "<p class=\"menuHead\">"."Change title"."</p>";
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=change_account\">".LangChangeMenuItem1."</a><br />";
if($loggedUser!="admin") { 
  // TODO : Add the action the method utilitiesDispatchIndexAction in lib/util.php 
  echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=new_module_add_observation\">"."Change item 1"."</a><br />";
}
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=add_instrument\">".LangChangeMenuItem3."</a><br />";
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=add_site\">".LangChangeMenuItem4."</a><br />";
// TODO : Add the action the method utilitiesDispatchIndexAction in lib/util.php 
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=comets_add_object\" >"."Change item 3"."</a><br />";
echo "</div>";
?>
