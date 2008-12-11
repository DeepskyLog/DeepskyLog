<?php
// validate_observers.php
// allows the adminstrator to validate an observer

$objObserver->validateObserver($objUtil->checkGetKey('validate'), RoleUser);
$entryMessage=LangValidateObserverMessage1.' '.LangValidateObserverMessage2;
$_GET['indexAction']='view_observers';
?>
