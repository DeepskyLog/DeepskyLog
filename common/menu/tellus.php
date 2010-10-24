<?php
//tellus.php
// displays the tell us section

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else tellus();

function tellus()
{ if ($_SESSION['module'] == "deepsky")
	{ echo "<div class=\"menuDiv\">";
	  echo "<p class=\"menuHead\">".LangMailtoTitle."</p>"; 
	  echo "<span class=\"menuText\">".LangMailtoLink."</span>";
	  echo "</div>";
	}
}
?>