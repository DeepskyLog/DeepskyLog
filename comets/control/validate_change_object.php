<?php
// validate_change_object.php
// checks if the change oject form is correctly filled in

global $inIndex,$loggedUser,$objUtil;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else validate_change_object();

function validate_change_object()
{ global $entryMessage,
         $objObserver;
	$util = new Utils();
	$objects = new CometObjects;
	
	if(!$_POST['name'])
	{
	  // error
	  $entryMessage = _("You did not fill in a required field!");
	  $_GET['indexAction']='default_action';
	}
	else
	{
	  if($_POST['object']) // comet id given
	  {
	    // only admins may change a comet 
	
	    $role = $objObserver->getObserverProperty($loggedUser,'role',2);
	
	    if ($role == ROLEADMIN || $role == ROLECOMETADMIN)
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
}
?>
