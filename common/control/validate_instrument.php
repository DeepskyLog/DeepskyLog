<?php
// validate_instrument.php
// checks if the add new instrument form is correctly filled in

if(($objUtil->checkPostKey('adaption')==1)
&& $objUtil->checkPostKey('stdtelescope')
&& $objUtil->checkUserID($objInstrument->getObserverFromInstrument($objUtil->checkPostKey('stdtelescope'))))
  $objObserver->setStandardTelescope($_SESSION['deepskylog_id'], $_POST['stdtelescope']);
if($objUtil->checkPostKey('instrumentname')
&& $objUtil->checkPostKey('diameter')
&& $objUtil->checkPostKey('type'))
{ $instrumentname=htmlspecialchars($_POST['instrumentname']);
  $instrumentname=htmlspecialchars_decode($instrumentname, ENT_QUOTES);
  $type=htmlspecialchars($_POST['type']);
  $diameter=$_POST['diameter'];
  if($objUtil->checkPostKey('fd')
  || $objUtil->checkPostKey('focallength')
  ||($objUtil->checkPostKey('type')==InstrumentBinoculars||$objUtil->checkPostKey('type')==InstrumentFinderscope)) 
  { $fd=0;
    $fixedMagnification=$objUtil->checkPostKey('fixedMagnification');
    if($objUtil->checkPostKey('diameterunits')=="inch")
      $diameter*=25.4;
    if($_POST['focallength']&&($_POST['type']!= InstrumentBinoculars)) // focal length filled in
    { $focallength=$_POST['focallength'];
      if(array_key_exists('focallengthunits', $_POST) 
      && $_POST['focallengthunits'] == "inch" 
      && !array_key_exists('fd', $_POST))
        $focallength = $focallength * 25.4;
      if($diameter>0)
        $fd=$focallength/$diameter;
    }
    elseif (array_key_exists('fd', $_POST)
         && $_POST['fd']
         && array_key_exists('type',$_POST)
         && ($_POST['type']!= InstrumentBinoculars))
      $fd=$objUtil->checkPostKey('fd',1.0);
  }
  if($objUtil->checkPostKey('add'))
  { $objInstrument->addInstrument($instrumentname, $diameter, $fd, $type, $objUtil->checkPostKey('fixedMagnification',0), $_SESSION['deepskylog_id']);
    $entryMessage=LangValidateInstrumentMessage3;
  }
  if($objUtil->checkPostKey('change')
  && $objUtil->checkUserID($objInstrument->getObserverFromInstrument($objUtil->checkPostKey('id')))) // change instrument of this user
  { $id = $_POST['id'];
    $objInstrument->setInstrumentType($_POST['id'], $type);
    $objInstrument->setInstrumentName($_POST['id'], $instrumentname);
    $objInstrument->setDiameter($id, $diameter);
    $objInstrument->setFd($_POST['id'], $fd);
    $objInstrument->setFixedMagnification($_POST['id'], $objUtil->checkPostKey('fixedMagnification'));
    $entryMessage=LangValidateInstrumentMessage4;
  }
}
$_GET['indexAction']='add_instrument'
?>
