<?php
// login.php
// menu which allows the user to log in  

if(!(array_key_exists('deepskylog_id', $_SESSION)&&$_SESSION['deepskylog_id']))
{ echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
  echo "<tr>";
	echo "<th valign=\"top\">";
  echo LangLoginMenuTitle;
  echo "</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>";
	echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
	echo "<form action=\"../".$_SESSION['module']."/index.php?indexAction=check_login\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"logtime\" value=".$_SESSION['logtime']."></input>";
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
  echo "<br></br>";
	echo "<input type=\"password\" class=\"inputfield\" maxlength=\"64\" name=\"passwd\" size=\"12\" value=\"\"></input>";
	echo "</p><p>";
	echo "<input type=\"submit\" name=\"submit\" value=\"".LangLoginMenuButton."\"/>";
  echo "</td>";
	echo "</tr>";
  if($register == "yes")                                                        // include register link
  { echo "<tr align=\"left\">";
	  echo "<td>";
	  echo "<a class=\"mainlevel\" href=\"common/indexCommon.php?indexAction=subscribe\">";
    echo LangLoginMenuRegister;
    echo "</a>";
		echo "</td>";
		echo "</tr>";
  }
  echo "</form>";
  echo "</table>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
}
?>
