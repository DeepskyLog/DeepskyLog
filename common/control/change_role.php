<?php
// change_role.php
// allows the adminstrator to change the role of an observer

if( ($_SESSION['admin']=="yes")
&& ($objUtil->checkGetKey('user')))
{  $role=$objUtil->checkGetKey('role',2);
   $objObserver->setRole($_GET['user'],$role);
   $entryMessage.="Role is successfully updated!";
}
$_GET['indexAction']="detail_observer";  
?>
