<?php
// validate_lens.php
// checks if the add new lens or change lens form is correctly filled in

if($objUtil->checkPostKey('add')
&& $objUtil->checkPostKey('lensname')
&& $objUtil->checkPostKey('factor')
&& $objUtil->checkSessionKey('deepskylog_id'))
{ $id = $GLOBALS['objLens']->addLens($_POST['lensname'], $_POST['factor']);     
	$objLens->setLensObserver($id, $_SESSION['deepskylog_id']);
  $entryMessage=LangValidateLensMessage2.' '.LangValidateLensMessage3;
}
if($objUtil->checkPostKey('change')
&& $objUtil->checkUserID($objLens->getObserverFromLens($objUtil->checkPostKey('id')))
&& $objUtil->checkPostKey('lensname')
&& $objUtil->checkPostKey('factor'))
{ $objLens->setLensName($_POST['id'], $_POST['lensname']);
  $objLens->setFactor($_POST['id'], $_POST['factor']);
  $objLens->setLensObserver($_POST['id'], $_SESSION['deepskylog_id']);
  $entryMessage=LangValidateLensMessage5.' '.LangValidateLensMessage4;
}
$_GET['indexAction']='add_lens';
?>
