<?php
// index.php: main entrance to deepsky modules of DeepskyLog
try{
include '../common/entryexit/preludes.php';
include '../common/entryexit/preludesDS.php';
include '../common/entryexit/instructions.php';
include '../common/entryexit/instructionsDS.php';
$objUtil->utilitiesSetModuleCookie("deepsky");
include '../common/entryexit/menu.php';
include $objUtil->utiltiesDispatchIndexActionDS();	
include '../common/tail.php';
}
catch (Exception $e)
{
echo 'Report problem: ' . $e->getMessage();
}
?>
