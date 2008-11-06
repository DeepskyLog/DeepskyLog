<?php

// index.php
// main entrance to DeepskyLog
// version 0.3, WDM 20050920

if(!session_id()) session_start();

include_once "lib/util.php";
$util = new Util();
$util->checkUserInput();

//include_once $instDir."lib/setup/databaseInfo.php";

include "common/loginuser.php"; 				 		 // LOGIN if cookie present 

print "<META HTTP-EQUIV=\"Refresh\"
      CONTENT=\"0; URL=".$_SESSION['module']."\">";
?>
