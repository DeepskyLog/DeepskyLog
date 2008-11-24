<?php
// index.php: main entrance to deepsky modules of DeepskyLog
try
{ $instDir="d:/accounts/DSLTrunk";
  include $instDir.'/common/entryexit/preludes.php';
  $objUtil->utilitiesSetModuleCookie("deepsky");
  include $instDir.'/common/entryexit/instructions.php';
  include $instDir.'/common/entryexit/preludesDS.php';
  include $instDir.'/common/entryexit/instructionsDS.php';
  include $instDir.'/common/entryexit/menu.php';
  if(isset($entryMessage)&&$entryMessage) echo $entryMessage.'<hr />';
//	echo $objUtil->utiltiesDispatchIndexActionDS();
  include $instDir.$objUtil->utiltiesDispatchIndexActionDS();	
  include $instDir.'/common/tail.php';
}
catch (Exception $e)
{ // TEMPORARY SOLUTION: WILL BE EXPANDED IN FURTHER DETAIL
  echo 'Report problem: ' . $e->getMessage();
	//$_GET['indexAction']='default_action';
	//include_once '../lib/util.php';
  //include $objUtil->utiltiesDispatchIndexActionDS();	
}
?>
