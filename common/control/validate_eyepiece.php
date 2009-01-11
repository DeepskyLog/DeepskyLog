<?php
// validate_eyepiece.php
// checks if the add new eyepiece or change eyepiece form is correctly filled in

if($objUtil->checkPostKey('eyepiecename')
&& $objUtil->checkSessionKey('deepskylog_id')
&& $objUtil->checkPostKey('focalLength')
&& $objUtil->checkPostKey('apparentFOV')
&& $objUtil->checkPostKey('add'))
{
  $id=$objEyepiece->addEyepiece($_POST['eyepiecename'],$_POST['focalLength'],$_POST['apparentFOV']);
  $objEyepiece->setEyepieceObserver($id, $_SESSION['deepskylog_id']);
	if ($_POST['maxFocalLength']) 
	  $objEyepiece->setMaxFocalLength($id, $_POST['maxFocalLength']);
	else 
	  $objEyepiece->setMaxFocalLength($id, -1);
  $entryMessage=LangValidateEyepieceMessage2;
}
if($objUtil->checkPostKey('id')
&& $objUtil->checkPostKey('eyepiecename')
&& $objUtil->checkPostKey('focalLength')
&& $objUtil->checkPostKey('apparentFOV')
&& $objUtil->checkPostKey('change')
&& $objUtil->checkAdminOrUserID($objEyepiece->getObserverFromEyepiece($_POST['id'])))
{ 
  $objEyepiece->setEyepieceName($_POST['id'], $_POST['eyepiecename']);
  $objEyepiece->setEyepieceFocalLength($_POST['id'], $_POST['focalLength']);
  $objEyepiece->setApparentFOV($_POST['id'], $_POST['apparentFOV']);
  $objEyepiece->setEyepieceObserver($_POST['id'], $_SESSION['deepskylog_id']);
  if ($_POST['maxFocalLength'])
    $objEyepiece->setMaxFocalLength($_POST['id'], $_POST['maxFocalLength']);
	else
		$objEyepiece->setMaxFocalLength($_POST['id'], -1);
	$entryMessage=LangValidateEyepieceMessage5.' '.LangValidateEyepieceMessage4;
}
$_GET['indexAction']='add_eyepiece';
?>
