<?php // change.php - menu which allows the user to add or change things in the database
echo "<div class=\"menuDiv\">";
echo "<p class=\"menuHead\">".LangChangeMenuTitle."</p>";
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=change_account\">".LangChangeMenuItem1."</a><br />";
if($loggedUser!="admin")
{ echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=comets_add_observation\">".LangChangeMenuItem2."</a><br />";
}
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=add_instrument\">".LangChangeMenuItem3."</a><br />";
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=add_site\">".LangChangeMenuItem4."</a><br />";
echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=comets_add_object\" >".LangChangeMenuItem5."</a><br />";
echo "</div>";
?>
