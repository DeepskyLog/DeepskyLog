<?php
// validate_instrument.php
// checks if the add new instrument form is correctly filled in

if(($objUtil->checkPostKey('adaption')==1)&&($objUtil->checkPostKey('stdtelescope'))&&($objUtil->checkUserID($objInstrument->getObserverFromInstrument($objUtil->checkPostKey('stdtelescope')))))
  $objObserver->setStandardTelescope($_SESSION['deepskylog_id'], $_POST['stdtelescope']);
elseif(($_POST['instrumentname']&&$_POST['diameter']&&$_POST['type'])||($_POST['type']==InstrumentBinoculars))
{ if ($_POST['fd']||$_POST['focallength']||($_POST['type']==InstrumentBinoculars||$_POST['type']== InstrumentFinderscope)) 
  { $instrumentname=htmlspecialchars($_POST['instrumentname']);
    $instrumentname=htmlspecialchars_decode($instrumentname, ENT_QUOTES);
    $type=htmlspecialchars($_POST['type']);
    $diameter=$_POST['diameter'];
    $fd=0;
    $fixedMagnification=$_POST['fixedMagnification'];
    if ($_POST['diameterunits']=="inch")
      $diameter*=25.4;
    if($_POST['focallength']&&($_POST['type']!= InstrumentBinoculars)) // focal length filled in
    { $focallength=$_POST['focallength'];
      //echo ("focal length" . $focallength);
      if(array_key_exists('focallengthunits', $_POST) && $_POST['focallengthunits'] == "inch" && !array_key_exists('fd', $_POST))
        $focallength = $focallength * 25.4;
      $fd=$focallength/$diameter;
    }
    elseif (array_key_exists('fd', $_POST)&&$_POST['fd']&& array_key_exists('type',$_POST)&&($_POST['type']!= InstrumentBinoculars))
      $_POST['id']=$_POST['fd'];
  }
  if(array_key_exists('add', $_POST) && $_POST['add']) // add instrument
  { $objInstrument->addInstrument($instrumentname, $diameter, $fd, $type, $objUtil->checkPostKey('fixedMagnification',0), $_SESSION['deepskylog_id']);
    $entryMessage=LangValidateInstrumentMessage3;
  }
  if($objUtil->checkPostKey('change')&&$objUtil->checkUserID($objInstrument->getObserverFromInstrument($objUtil->checkPostKey('id')))) // change instrument of this user
  { $id = $_POST['id'];
    $objInstrument->setInstrumentType($_POST['id'], $type);
    $objInstrument->setInstrumentName($_POST['id'], $instrumentname);
    $objInstrument->setDiameter($id, $diameter);
    $objInstrument->setFd($_POST['id'], $fd);
    $objInstrument->setFixedMagnification($_POST['id'], $objUtil->checkPostKey('fixedMagnification',0));
    $entryMessage=LangValidateInstrumentMessage4;
  }
}
$_GET['indexAction']='add_instrument'
?>
