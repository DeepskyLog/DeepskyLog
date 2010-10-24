<?php 
// help.php

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else help();

function help()
{ echo "<div class=\"menuDiv\">";
	if($_SESSION['lang']=="nl")
	{ echo"<p class=\"menuHead\">Help</p>";
		echo "<a href=\"http://www.deepskylog.org/wiki/bin/view/Main/DeepskylogManualNL39\" rel=\"external\">Handleiding</a>";
	}
	echo "</div>";
}
?>
