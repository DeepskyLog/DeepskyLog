<?php

// change_role.php
// allows the adminstrator to change the role of an observer

session_start(); // start session

include "lib/observers.php";
include "lib/setup/vars.php";
include_once "lib/util.php";

$util = new Util();
$util->checkUserInput();
$obs = new Observers;

$role = $_GET['role'];

$obs->setRole($_SESSION['user'], $role);

$_SESSION['message'] = "Role is successfully updated!";
$_SESSION['title'] = "Changed role";

header("Location: message.php");  

?>
