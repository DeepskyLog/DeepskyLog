<?php
// validate_eyepiece.php
// checks if the add new eyepiece or change eyepiece form is correctly filled in

if($objUtil->checkPostKey('eyepiecename')&&$objUtil->checkPostKey('focalLength')&&$objUtil->checkPostKey('apparentFOV')&&$objUtil->checkPostKey('add'))
{ $id=$eyepieces->addEyepiece($_POST['eyepiecename'],$_POST['focalLength'],$_POST['apparentFOV']);
  $eyepieces->setEyepieceObserver($id, $_SESSION['deepskylog_id']);
	if ($_POST['maxFocalLength']) 
	  $eyepieces->setMaxFocalLength($id, $_POST['maxFocalLength']);
	else 
	  $eyepieces->setMaxFocalLength($id, -1);
  $entryMessage=LangValidateEyepieceMessage2.' '.LangValidateEyepieceMessage3;
}
if($objUtil->checkPostKey('id')&&$objUtil->checkPostKey('eyepiecename')&&$objUtil->checkPostKey('focalLength')&&$objUtil->checkPostKey('apparentFOV')&&$objUtil->checkPostKey('change')&&
   ((array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes")) || 
    (array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] == $objEyepiece->getObserverFromEyepiece($_POST['id']))))
{ $eyepieces->setEyepieceName($_POST['id'], $_POST['eyepiecename']);
  $eyepieces->setEyepieceFocalLength($_POST['id'], $_POST['focalLength']);
  $eyepieces->setApparentFOV($_POST['id'], $_POST['apparentFOV']);
  $eyepieces->setEyepieceObserver($_POST['id'], $_SESSION['deepskylog_id']);
  if ($_POST['maxFocalLength'])
    $eyepieces->setMaxFocalLength($_POST['id'], $_POST['maxFocalLength']);
	else
		$eyepieces->setMaxFocalLength($_POST['id'], -1);
	$entryMessage=LangValidateEyepieceMessage5.' '.LangValidateEyepieceMessage4;
}
$_GET['indexAction']='add_eyepiece';
?>
