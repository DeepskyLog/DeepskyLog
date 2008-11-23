<?php
// index.php: main entrance to deepsky modules of DeepskyLog
try
{
  include '../common/entryexit/preludes.php';
  include '../common/entryexit/instructions.php';
  include '../common/entryexit/preludesDS.php';
  include '../common/entryexit/instructionsDS.php';
  $objUtil->utilitiesSetModuleCookie("deepsky");
  include '../common/entryexit/menu.php';
  if(isset($entryMessage)) echo $entryMessage.'<hr />';
  include $objUtil->utiltiesDispatchIndexActionDS();	
  include '../common/tail.php';
}
catch (Exception $e)
{ // TEMPORARY SOLUTION: WILL BE EXPANDED IN FURTHER DETAIL
  echo 'Report problem: ' . $e->getMessage();
	//$_GET['indexAction']='default_action';
	//include_once '../lib/util.php';
  //include $objUtil->utiltiesDispatchIndexActionDS();	
}
?>
