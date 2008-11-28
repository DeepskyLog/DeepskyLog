<?php
// change_role.php
// allows the adminstrator to change the role of an observer

$role = $_GET['role'];
$objObserver->setRole($_SESSION['user'],$role);
$entryMessage.="Role is successfully updated!";
$_GET['indexAction"]="detail_observer";  
?>
