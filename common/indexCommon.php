<?php
// index.php: main entrance to common modules of DeepskyLog
try{
include '../common/entryexit/preludes.php';
include '../common/entryexit/instructions.php';
include '../common/entryexit/menu.php';
include $objUtil->utiltiesDispatchIndexActionCommon();	
include '../common/tail.php';
}
catch (Exception $e)
{
echo 'Report problem: ' . $e->getMessage();
}
?>
