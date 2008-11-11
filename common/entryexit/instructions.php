<?php
if(array_key_exists('indexAction',$_REQUEST)&&($_REQUEST['indexAction']=="check_login"))
  require_once "../common/control/check_login.php";
if(array_key_exists('indexAction',$_REQUEST)&&($_REQUEST['indexAction']=="logout"))
  require_once "../common/control/logout.php";

?>
