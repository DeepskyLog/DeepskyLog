<?php
// validate_filter.php
// checks if the add new eyepiece or change eyepiece form is correctly filled in

if($objUtil->checkPostKey('add')&&$objUtil->checkSessionKey('deepskylogid')&&$objUtil->checkPostKey('filtername')&&$objUtil->checkPostKey('type'))
{ $id=$filters->addFilter($objUtil->checkPostKey('filtername'), $objUtil->checkPostKey('type'), $objUtil->checkPostKey('color'), $objUtil->checkPostKey('wratten'), $objUtil->checkPostKey('schott'));
  $filters->setFilterObserver($id,$_SESSION['deepskylog_id']);
  $entryMessage=LangValidateEyepieceMessage2.' '.LangValidateEyepieceMessage3;
}
if($objUtil->checkPostKey('change')&&$objUtil->checkPostKey('id')&&$objUtil->checkPostKey('filtername')&&$objUtil->checkPostKey('type')&&$objUtil->checkAdminOrUserID($objFilter->getObserverFromFilter($_POST['id'])))
{ $filters->setFilterName($_POST['id'], $objUtil->checkPostKey('filtername'));
  $filters->setFilterType($_POST['id'], $objUtil->checkPostKey('type'));
  $filters->setFilterColor($_POST['id'], $objUtil->checkPostKey('color'));
  $filters->setWratten($_POST['id'], $objUtil->checkPostKey('wratten'));
  $filters->setSchott($_POST['id'], $objUtil->checkPostKey('schott'));
  $filters->setFilterObserver($_POST['id'], $_SESSION['deepskylog_id']);
  $entryMessage=LangValidateEyepieceMessage5.' '.LangValidateEyepieceMessage4;
}
$_GET['indexAction']='add_filter';
?>
