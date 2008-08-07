<?php

// message.php
// displays message 

include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

if(!$_SESSION['module'])
{
   $_SESSION['module'] = $modules[0];
}

include("head.php"); // HTML head

$head = new head();
$head->printHeader($browsertitle);
$head->printMenu();
$head->printMeta("DeepskyLog");

include("menu/headmenu.php"); // HEAD MENU

menu($title); // SUBTITLE

include("menu/login.php");


include_once("../".$_SESSION['module']."/menu/search.php"); // SEARCH MENU

if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']))
{
    include_once("../".$_SESSION['module']."/menu/change.php"); // CHANGE MENU
    include("../common/menu/help.php"); // HELP MENU
    if(array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes"))
    {
       include("menu/admin.php"); // ADMINISTRATION MENU
    }
    include("menu/out.php"); // LOG OUT MENU
}
else
{
    include("../common/menu/help.php"); // HELP MENU
    include("menu/languagemenu.php"); // LOG OUT MENU
}

include("menu/endmenu.php"); // END MENU

// PRINT MESSAGE AS CONTENT

echo("<div id=\"main\">\n
            <h2>");

echo($_SESSION['title']);
   
echo("</h2>\n<p>");
            
echo($_SESSION['message']);

include("tail.php"); // HTML END CODE
?>
