<?php
// index.php: main entrance to deepsky modules of DeepskyLog
try{
include '../common/entryexit/preludes.php';
$objUtil->utilitiesSetModuleCookie("deepsky");
include '../common/entryexit/menus.php';
include $objUtil->utiltiesDispatchIndexActionDS();	
include("../common/tail.php");
}
catch (Exception $e)
{
echo "Report problem: " . $e->getMessage();
}
?>
