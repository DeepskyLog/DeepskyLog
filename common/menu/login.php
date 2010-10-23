<?php 
// login.php
// menu which allows the user to log in  

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else menu_login();

function menu_login()
{ global $loggedUser,$register,$menuLogin,
         $objUtil;
	if((!($loggedUser))||($objUtil->checkGetKey('indexAction')=='logout'))
	{ echo "<div  class=\"menuDiv\">";
		reset($_GET);
		$link="";
		while(list($key,$value)=each($_GET))
		  if($key!="menuLogin")
		    $link.="&amp;".$key."=".urlencode($value);
		reset($_GET);
		echo "<p  class=\"menuHead\">";
		if($menuLogin=="collapsed")
		  echo "<a href=\"".$baseURL."index.php?menuLogin=expanded".$link."\" title=\"".LangMenuExpand."\">+</a> ";
		else
		  echo "<a href=\"".$baseURL."index.php?menuLogin=collapsed".$link."\" title=\"".LangMenuCollapse."\">-</a> ";
		echo LangLoginMenuTitle.LangLoginMenuTitle1;
	  if($register == "yes")                                                        // include register link
	    echo "<a class=\"menuLine\" href=\"".$baseURL."index.php?indexAction=subscribe&amp;title=".urlencode(LangLoginMenuRegister)."\">".LangLoginMenuRegister."</a>";
	  echo "</p>";
	  if($menuLogin=="expanded")
	  { echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
			echo "<div>";
			echo "<input type=\"hidden\" name=\"indexAction\" value=\"check_login\" />";
			echo "<input type=\"hidden\" name=\"title\"       value=\"".LangLoginMenuTitle."\" />";
			echo "<span class=\"menuInputTitle\">".LangLoginMenuItem1."</span><br />";
			echo "<input type=\"text\" class=\"inputfield menuInput\" maxlength=\"64\" name=\"deepskylog_id\" id=\"deepskylog_id\" size=\"12\" value=\"\" /><br />"; // to add : onkeydown=\"if(event.keyCode==13) {document.getElementById('password').setFocus;}\"
		  echo "<span class=\"menuInputTitle\">".LangLoginMenuItem2."</span><br />";
			echo "<input type=\"password\" class=\"inputfield menuInput\" maxlength=\"64\" name=\"passwd\" id=\"passwd\" size=\"12\" value=\"\" onkeydown=\"if(event.keyCode==13){this.form.submit();}\" />";
		  echo "</div>";
			echo "</form>";
	  }
	  echo "</div>";
	}
}
?>