<?php // change.php - menu which allows the user to add or change things in the database
echo "<div class=\"menuDiv\">";
echo "<p  class=\"menuHead\">".LangChangeMenuTitle."</p>";
echo "<a href=\"".$baseURL."index.php?indexAction=change_account\" class=\"mainlevel\">".LangChangeMenuItem1."</a><br />";
if($loggedUser!="admin")
{ echo "<a href=\"".$baseURL."index.php?indexAction=comets_add_observation\" class=\"mainlevel\">".LangChangeMenuItem2."</a><br />";
}
echo "<a href=\"".$baseURL."index.php?indexAction=add_instrument\" class=\"mainlevel\">".LangChangeMenuItem3."</a><br />";
echo "<a href=\"".$baseURL."index.php?indexAction=add_site\" class=\"mainlevel\">".LangChangeMenuItem4."</a><br />";
echo "<a href=\"".$baseURL."index.php?indexAction=comets_add_object\" class=\"mainlevel\">".LangChangeMenuItem5."</a><br />";
echo "</div>";
?>
