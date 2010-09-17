<?php

// validate_change_object.php
// checks if the change oject form is correctly filled in
// Version 0.1: JV, 20060428

include_once "lib/cometobjects.php";
include_once "lib/observers.php";
include_once "lib/setup/vars.php";
include_once "lib/util.php";

$util = new Utils();

$objects = new CometObjects;

if(!$_POST['name'])
{
  // error
  $entryMessage = LangValidateObservationMessage1;
  $_GET['indexAction']='default_action';
}
else
{
  if($_POST['object']) // comet id given
  {
    // only admins may change a comet 

    $role = $objObserver->getObserverProperty($loggedUser,'role',2);

    if ($role == RoleAdmin || $role == RoleCometAdmin)
    {
      $name = $_POST['name'];
      $icqname = $_POST['icqname'];
     
      $objects->setName($_POST['object'], $name);
      $objects->setIcqName($_POST['object'], $icqname);

      $_GET['object']=$_POST['object'];
      $_GET['indexAction']="default_action";
    }
    else // not logged in as admin
    {
      $_GET['object']=$_POST['object'];
      $_GET['indexAction']='default_action';
    }
  }
  else // no comet id given
  {
    $_GET['object']=$_POST['object'];
    $_GET['indexAction']='default_action';
  }
}
?>
