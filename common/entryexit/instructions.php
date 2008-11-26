<?php
if(array_key_exists('indexAction',$_REQUEST)&&($_REQUEST['indexAction']=="common_control_validate_account"))
  require_once $instDir."/common/control/validate_account.php";
if(array_key_exists('indexAction',$_REQUEST)&&($_REQUEST['indexAction']=="logout"))
  require_once $instDir."/common/control/logout.php";
if(array_key_exists('indexAction',$_REQUEST)&&($_REQUEST['indexAction']=="validate_lens"))
  require_once $instDir."/common/control/validate_lens.php";
?>
