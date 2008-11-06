<?php
// login.php
// menu which allows the user to log in  
// version 3.1, DE 20061102, 20061124

//include_once "../lib/observers.php";
//include_once "../lib/setup/vars.php";
//include_once "../lib/setup/databaseInfo.php";
//include_once "../lib/util.php";

if ((!(array_key_exists('deepskylog_id', $_SESSION))) ||
		(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']=="")))
{
//  include_once "../lib/observers.php";            // language setup
  echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">\n
      <tr>\n
      <th valign=\"top\">\n";
  echo (LangLoginMenuTitle);
  echo "</th>\n</tr>\n<tr>\n<td>\n
      <table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
  echo "<form action=\"../common/control/check_login.php\" method=\"post\">";
  echo "<tr align=\"left\">\n<td>";
  echo (LangLoginMenuItem1);
  echo ("\n<br></br>\n
     <input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"deepskylog_id\" size=\"12\" value=\"\"></input>\n");
  echo "</td>\n</tr>\n";
  echo "<tr align=\"left\">\n<td>";
  echo (LangLoginMenuItem2);
  echo ("\n<br></br>\n
     <input type=\"password\" class=\"inputfield\" maxlength=\"64\" name=\"passwd\" size=\"12\" value=\"\"></input>\n
     </p><p>\n<input type=\"submit\" name=\"submit\" value=\"");
  echo (LangLoginMenuButton);
  echo ("\" />");
  echo "</td>\n</tr>\n";
  if($register == "yes")                                // include register link
  {
    echo ("<tr align=\"left\">\n<td>\n<a class=\"mainlevel\" href=\"common/subscribe.php\">");
    echo (LangLoginMenuRegister);
    echo ("</a></td>\n</tr>\n");
  }
  echo ("</form>\n");
  echo "</table>\n</td>\n
      </tr>\n
      </table>\n";
}
?>
