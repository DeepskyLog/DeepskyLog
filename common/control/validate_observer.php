<?php

// validate_observers.php
// allows the adminstrator to validate an observer

session_start(); // start session

include "../../lib/observers.php";
include_once "../../lib/setup/vars.php";
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

$obs = new Observers;

$obs->validateObserver($_GET['validate'], RoleUser);

$_SESSION['message'] = LangValidateObserverMessage1;
$_SESSION['title'] = LangValidateObserverMessage2;
header("Location:../message.php");
//header("Location: ../view_observers.php"); // return to view_observers.php 

?>
