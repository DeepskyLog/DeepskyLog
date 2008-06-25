<?php

// setLanguage.php
// allows a non-registrated user to change the language

session_start(); // start session
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

$_SESSION['lang'] = $_POST['language']; // set session variable
header("Location: " . $_SERVER['HTTP_REFERER']); // return to referring page 
?>
