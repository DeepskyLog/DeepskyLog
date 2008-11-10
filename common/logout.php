<?php
// logout.php
// let the user logout of deepskylog

$temp=$_SESSION['module'];
setcookie("deepskylogsec","",time()-3600,"/");	// delete cookie
session_unset(); 																// unset all session variables
session_destroy(); 															// destroy session
if(array_key_exists('deepskylog_id',$_SESSION))
  session_unregister($_SESSION['deepskylog_id']);
if(array_key_exists('admin',$_SESSION))
  session_unregister($_SESSION['admin']);
header("Location: ../".$temp."/index.php"); 		 				 // return to main entrance 
?>
