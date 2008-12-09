<?php
// login.php
// menu which allows the user to log in  

if(!(array_key_exists('deepskylog_id', $_SESSION)&&$_SESSION['deepskylog_id']))
{ echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\" width=\"100%\" border=\"0\">";
  echo "<tr>";
	echo "<th valign=\"top\">";
  echo LangLoginMenuTitle;
  echo "</th>";
	echo "</tr>";
	echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"check_login\">";
  echo "<tr align=\"left\">";
	echo "<td>";
  echo LangLoginMenuItem1;
  echo "<br></br>";
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"deepskylog_id\" size=\"12\" value=\"\"></input>";
  echo "</td>";
	echo "</tr>";
  echo "<tr align=\"left\">";
	echo "<td>";
  echo LangLoginMenuItem2;
  echo "<br />";
	echo "<input type=\"password\" class=\"inputfield\" maxlength=\"64\" name=\"passwd\" size=\"12\" value=\"\"></input>";
	echo "<p />";
	echo "<input type=\"submit\" name=\"submit\" value=\"".LangLoginMenuButton."\"/>";
  echo "</td>";
	echo "</tr>";
  if($register == "yes")                                                        // include register link
  { echo "<tr align=\"left\">";
	  echo "<td>";
	  echo "<a class=\"mainlevel\" href=\"".$baseURL."index.php?indexAction=subscribe\">";
    echo LangLoginMenuRegister;
    echo "</a>";
		echo "</td>";
		echo "</tr>";
  }
  echo "</form>";
	echo "</table>";
}
?>
