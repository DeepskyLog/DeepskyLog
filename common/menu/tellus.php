<?php
//tellus.php
// displays the tell us section

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else tellus();

function tellus()
{ if ($_SESSION['module'] == "deepsky")
	{ echo "<div class=\"menuDiv\">";
	  echo "<p class=\"menuHead\">"._("Tell us!")."</p>"; 
	  echo "<span class=\"menuText\"><a href=\"mailto:&#100;&#101;v&#101;lop&#101;rs&#64;&#100;&#101;&#101;pskylog.&#98;&#101;\">" . _("Ask question") . "</a></span>";
	  echo "</div>";
	}
}
?>