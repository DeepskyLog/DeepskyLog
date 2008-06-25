<?php

// validate_object.php
// checks if the add new comet form is correctly filled in
// and eventually adds the comet to the database

// Version 0.1: 20050921, WDM

session_start(); // start session

include "../../lib/cometobjects.php";
include_once "../../lib/observers.php";
include_once "../../lib/setup/vars.php";
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

if ($_POST['newobject']) // pushed add new object button
{

  // check if required fields are filled in

  if (!$_POST['name'])
  {
    $_SESSION['message'] = LangValidateObjectMessage1;
    header("Location:../../common/error.php");
  }
  else // all required fields filled in
  {
    $objects = new CometObjects();
    // control if object doesn't exist yet
    $name = $_POST['name'];
    $query1 = array("name" => $name);
	  if(count($objects->getObjectFromQuery($query1, "name")) > 0) // object already exists
    {
      $_SESSION['message'] = LangValidateObjectMessage2;
      header("Location:../../common/error.php");
    }
    else
    {
    // fill database
      $id = $objects->addObject($name);
      if($_POST['icqname'])
      {
        $objects->setIcqName($id, $_POST['icqname']);
      }
      header("Location:../detail_object.php?object=" . $id);
    }
  }
}
elseif ($_POST['clearfields']) // pushed clear fields button
{
   header("Location:../add_object.php");
}
?>
