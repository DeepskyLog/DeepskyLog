<?php
// index.php
// main entrance to DeepskyLog
/* OLD INDEX
session_start();
if (isset($_COOKIE['module']))
	$_SESSION['module'] = $_COOKIE['module'];
else
  $_SESSION['module'] = 'deepsky';
print "<META HTTP-EQUIV=\"Refresh\"
      CONTENT=\"0; URL=".$_SESSION['module']."\">";
*/

try{
if(!array_key_exists('indexAction',$_GET)&&array_key_exists('indexAction',$_POST)) $_GET['indexAction']=$_POST['indexAction'];
include 'common/entryexit/preludes.php';
include 'common/entryexit/instructions.php';
include 'common/entryexit/menu.php';
if(isset($entryMessage)&&$entryMessage) echo $entryMessage.'<hr />';
include $objUtil->utiltiesDispatchIndexAction();	
include 'common/menu/tail.php';
}
catch (Exception $e)
{
echo 'Report problem: ' . $e->getMessage();
}

// DEMO

?>
