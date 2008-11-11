<?php
// index.php
// main entrance to DeepskyLog

if (isset($_COOKIE['module']))
	$_SESSION['module'] = $_COOKIE['module'];
else
  $_SESSION['module'] = 'deepsky';
print "<META HTTP-EQUIV=\"Refresh\"
      CONTENT=\"0; URL=".$_SESSION['module']."\">";
?>
