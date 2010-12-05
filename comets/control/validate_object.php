<?php
// validate_object.php
// checks if the add new comet form is correctly filled in
// and eventually adds the comet to the database

global $inIndex,$loggedUser,$objUtil;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else validate_object();

function validate_object()
{ global $entryMessage,$objUtil;
	$util = new Utils();
	
	if ($objUtil->checkPostKey('newobject')) // pushed add new object button
	{ // check if required fields are filled in
	  if (!($objUtil->checkPostKey('name')))
	  { $entryMessage = LangValidateObjectMessage1;
	    $_GET['indexAction']='default_action';
	  }
	  else // all required fields filled in
	  { $objects = new CometObjects();
	    // control if object doesn't exist yet
	    $name = $_POST['name'];
	    $query1 = array("name" => $name);
		  if(count($objects->getObjectFromQuery($query1, "name")) > 0) // object already exists
	    {
	    $entryMessage = LangValidateObjectMessage2;
	    $_GET['indexAction']='default_action';
	          }
	    else
	    { // fill database
	      $id = $objects->addObject($name);
	      if($_POST['icqname'])
	      {
	        $objects->setIcqName($id, $_POST['icqname']);
	      }
	      $_GET['indexAction']='comets_detail_object';
	      $_GET['object']=$id;
	    }
	  }
	}
	elseif ($objUtil->checkPostKey('clearfields')) // pushed clear fields button
	{ $_GET['indexAction'] = 'comets_add_object';
	}
}
?>
