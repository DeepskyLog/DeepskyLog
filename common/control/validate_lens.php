<?php
// validate_lens.php
// checks if the add new lens or change lens form is correctly filled in

if (!$_POST['lensname'] || !$_POST['factor'])
{ $_SESSION['message'] = LangValidateEyepieceMessage1; 
  header("Location:error.php");
}
else
{ if(array_key_exists('add', $_POST) && $_POST['add'])
  { $id = $GLOBALS['objLens']->addLens($_POST['lensname'], $_POST['factor']);     
	  $objLens->setLensObserver($id, $_SESSION['deepskylog_id']);
    $_SESSION['message'] = LangValidateLensMessage2;
  	$_SESSION['title'] = LangValidateLensMessage3;
}
if(array_key_exists('change', $_POST) && $_POST['change'])
{ $objLens->setLensName($_POST['id'], $_POST['lensname']);
  $objLens->setFactor($_POST['id'], $_POST['factor']);
  $objLens->setLensObserver($_POST['id'], $_SESSION['deepskylog_id']);
  $_SESSION['message'] = LangValidateLensMessage5;
  $_SESSION['title'] = LangValidateLensMessage4;
}
$_GET['indexAction']='add_lens';
}
?>
