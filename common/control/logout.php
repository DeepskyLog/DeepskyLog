<?php // logout.php - let the user logout of deepskylog
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else logout();

function logout()
{ setcookie("deepskylogsec","",time()-3600,"/");// delete cookie
	//setcookie("PHPSESSID","",time()-3600,"/");// delete cookie
	/*
	session_unregister($_SESSION['deepskylog_id']);
	unset($_SESSION['deepskylog_id']);
	session_unregister($_SESSION['admin']);
	unset($_SESSION['admin']);
	reset($_SESSION);
	while(list($key,$value)=each($_SESSION))
	{ session_unregister($_SESSION[$key]);
	  unset($_SESSION[$key]);
	}
	session_unset();
	session_destroy(); 
	if(array_key_exists('deepskylog_id',$_SESSION))
	{ 
	  session_unregister($_SESSION['deepskylog_id']);
	}
	if(array_key_exists('admin',$_SESSION))
	{ 
	  session_unregister($_SESSION['admin']);
	}
	*/
	$_SESSION['admin']='no';
	$_SESSION['deepskylog_id']='';												// destroy session
	$_SESSION['lco']='L';
	$cookietime = time() + 365 * 24 * 60 * 60;            // 1 year
	setcookie("lco","L",$cookietime, "/");
}
?>
