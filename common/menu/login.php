<?php
// login.php
// menu which allows the user to log in  

if(!(array_key_exists('deepskylog_id', $_SESSION)&&$_SESSION['deepskylog_id']))
{ echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\" width=\"100%\" border=\"0\">";
  echo "<tr>";
	echo "<th valign=\"top\">";
  echo LangLoginMenuTitle;
  if($register == "yes")                                                        // include register link
    echo "<a class=\"mainlevel\" href=\"".$baseURL."index.php?indexAction=subscribe\">".LangLoginMenuRegister."</a>";
  echo "</th>";
	echo "</tr>";
	echo "<form action=\"".$baseURL."index.php\" method=\"post\" name=\"loginForm\">";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"check_login\">";
  echo "<tr align=\"left\">";
	echo "<td>";
  echo LangLoginMenuItem1;
  echo "<br />";
	echo "<input type=\"text\" style=\"width: 143px\" class=\"inputfield\" maxlength=\"64\" name=\"deepskylog_id\" size=\"12\" value=\"\"></input>";
  echo "</td>";
	echo "</tr>";
  echo "<tr align=\"left\">";
	echo "<td>";
  echo LangLoginMenuItem2;
  echo "<br />";
	echo "<input type=\"password\" style=\"width: 143px\" class=\"inputfield\" maxlength=\"64\" name=\"passwd\" size=\"12\" value=\"\" onKeyDown=\"if(event.keyCode==13){this.form.submit()}\"></input>";

 // echo "<input type=\"submit\" style=\"width: 0px\" name=\"submit\" value=\"\"/>";

	echo "</td>";
	echo "</tr>";
  echo "</form>";
	echo "</table>";
}
?>
