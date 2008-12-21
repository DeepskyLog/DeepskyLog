<?php
// validate_filter.php
// checks if the add new eyepiece or change eyepiece form is correctly filled in

if($objUtil->checkPostKey('add')
&& $objUtil->checkSessionKey('deepskylog_id')
&& $objUtil->checkPostKey('filtername')
&& $objUtil->checkPostKey('type'))
{ $id=$objFilter->addFilter($objUtil->checkPostKey('filtername'), $objUtil->checkPostKey('type'), $objUtil->checkPostKey('color',0), $objUtil->checkPostKey('wratten'), $objUtil->checkPostKey('schott'));
  $objFilter->setFilterObserver($id,$_SESSION['deepskylog_id']);
  $entryMessage=LangValidateFilterMessage2;
}
if($objUtil->checkPostKey('change')
&& $objUtil->checkPostKey('id')
&& $objUtil->checkPostKey('filtername')
&& $objUtil->checkPostKey('type')
&& $objUtil->checkAdminOrUserID($objFilter->getObserverFromFilter($_POST['id'])))
{ $objFilter->setFilterName($_POST['id'], $objUtil->checkPostKey('filtername'));
  $objFilter->setFilterType($_POST['id'], $objUtil->checkPostKey('type'));
  $objFilter->setFilterColor($_POST['id'], $objUtil->checkPostKey('color',0));
  $objFilter->setWratten($_POST['id'], $objUtil->checkPostKey('wratten'));
  $objFilter->setSchott($_POST['id'], $objUtil->checkPostKey('schott'));
  $objFilter->setFilterObserver($_POST['id'], $_SESSION['deepskylog_id']);
  $entryMessage=LangValidateEyepieceMessage5;
}
$_GET['indexAction']='add_filter';
?>
