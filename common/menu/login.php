<?php // login.php -  menu which allows the user to log in  
if((!($loggedUser))||($objUtil->checkGetKey('indexAction')=='logout'))
{ echo "<div  class=\"menuDiv\">";
	echo "<p class=\"menuHead\">".LangLoginMenuTitle;
  if($register == "yes")                                                        // include register link
    echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=subscribe\">".LangLoginMenuRegister."</a>";
  echo "</p>";
	echo "<form action=\"".$baseURL."index.php\" method=\"post\" name=\"loginForm\">";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"check_login\" />";
  echo "<span class=\"menuInputTitle\">".LangLoginMenuItem1."</span><br />";
	echo "<input type=\"text\" class=\"inputfield menuInput\" maxlength=\"64\" name=\"deepskylog_id\" size=\"12\" value=\"\" /><br />";
  echo "<span class=\"menuInputTitle\">".LangLoginMenuItem2."</span><br />";
	echo "<input type=\"password\" class=\"inputfield menuInput\" maxlength=\"64\" name=\"passwd\" size=\"12\" value=\"\" onKeyDown=\"if(event.keyCode==13){this.form.submit()}\" />";
  echo "</form>";
	echo "</div>";
}
?>
