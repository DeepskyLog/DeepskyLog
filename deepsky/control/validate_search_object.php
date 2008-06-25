<?php

// validate_search_object.php
// checks if the add new observation form is correctly filled in

session_start(); // start session

include "../../lib/objects.php";
include_once "../../lib/observers.php";
include_once "../../lib/setup/vars.php";
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

$objects = new Objects;

if (!$_POST['number'])
{
  $_SESSION['message'] = LangValidateSearchObjectMessage1;
  $_SESSION['backlink'] = "../../deepsky/index.php?indexAction=add_observation";
  header("Location:../../common/error.php");
}
else // all fields filled in
{
  $_SESSION['observedobject'] = $_POST['catalogue'] . " " . $_POST['number'];
	$_SESSION['found'] = "no";
  $_SESSION['result'] = $objects->getExactObject('',$_POST['catalogue'], $_POST['number']);
  if(array_key_exists('result', $_SESSION) && $_SESSION['result']) // object found
  {
    $_SESSION['observedobject'] = $_SESSION['result'][0]; // use name in database
    $_SESSION['found'] = "yes";
    $_SESSION['backlink'] = "validate_search_object.php";
    header("Location:../../deepsky/index.php?indexAction=add_observation&object=" . urlencode($_SESSION['observedobject']));
  }
  else
  {
   $_SESSION['message'] = LangValidateSearchObjectMessage2 .
                           "<p><a href=\"deepsky/index.php?indexAction=add_observation\">" . 
                           LangValidateSearchObjectMessage3 . 
                           "</a> " . 
                           LangObservationOR . 
                           " <a href=\"deepsky/index.php?indexAction=add_object\">" .
                           LangNewObjectTitle .
                           "</a></p>";
    $_SESSION['title'] = LangValidateSearchObjectTitle1;
    $_SESSION['backlink'] = "validate_search_object.php";
    header("Location:../../common/message.php");
  }
}
?>
