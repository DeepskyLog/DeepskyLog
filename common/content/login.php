<?php
// Log in

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else loginForm();

function loginForm()
{ 
  global $baseURL;
  
  echo "<div id=\"main\">";
  echo "<h1>" . LangLoginMenuTitle . "</h1>";
  echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
  echo "<input type=\"hidden\" name=\"indexAction\" value=\"check_login\" />";
  echo "<input type=\"hidden\" name=\"title\"       value=\"".LangLoginMenuTitle."\" />";
  echo LangLoginMenuItem1."<br />";
  echo "<input type=\"text\" class=\"inputfield menuInput\" maxlength=\"64\" name=\"deepskylog_id\" id=\"deepskylog_id\" size=\"12\" value=\"\" /><br />"; // to add : onkeydown=\"if(event.keyCode==13) {document.getElementById('password').setFocus;}\"
  echo LangLoginMenuItem2."<br />";
  echo "<input type=\"password\" class=\"inputfield menuInput\" maxlength=\"64\" name=\"passwd\" id=\"passwd\" size=\"12\" value=\"\" onkeydown=\"if(event.keyCode==13){this.form.submit();}\" />";
  echo "</form>";  
  echo "</div>";
}
?>