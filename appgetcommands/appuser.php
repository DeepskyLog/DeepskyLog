<?php
require_once 'lib/observers.php';
require_once 'lib/setup/databaseInfo.php';
require_once 'lib/database.php';

global $objObserver;
global $objDatabase;

$objObserver=new Observers;
$objDatabase=new Database;

function checkUser($login, $passwd) {
	global $objObserver;
	if($login=="visitor")
		return "visitor";
	else if($objObserver->getObserverPropertyCS($login,'password')==md5($passwd))
		return "loggedUser:".$objObserver->getObserverPropertyCS($login,"firstname","")." ".$objObserver->getObserverPropertyCS($login,"name","");
	else
		return "invalidUser";
}

function checkUserToSession($login, $passwd) {
	global $objObserver,$loggedUser,$loggedUserName,$objUtil;
	$loggedUser='';
  $loggedUserName='';
  $_SESSION['admin']="no";
  if(($login!="")&&($passwd!="")&&($objObserver->getObserverProperty($login,'password')==md5($passwd))) {
		$_SESSION['lang']=$objUtil->checkGetKey('language',$objObserver->getObserverProperty($login,'language'));
		$_SESSION['deepskylog_id']=$login;
		$loggedUser=$_SESSION['deepskylog_id'];
	}
	else {
		$_SESSION['lang']="EN";
		$_SESSION['deepskylog_id']='';
  	$loggedUser="";
  }
}
?>