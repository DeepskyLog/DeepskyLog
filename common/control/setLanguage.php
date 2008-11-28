<?php
// setLanguage.php
// allows a non-registrated user to change the language
$_SESSION['lang']=$_POST['language'];                                           // set session variable
header("Location: " . $_SERVER['HTTP_REFERER']);                                // return to referring page 
?>
