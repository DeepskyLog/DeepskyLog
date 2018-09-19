<?php
// add_csv_listdata.php
// adds objects to a list from a csv file to the database

global $inIndex,$loggedUser;

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(_("You need to be logged in to change your locations or equipment."));
else add_csv_listdata();

function add_csv_listdata()
{ global $myList,$entryMessage,
         $objList,$objObject;
  $_GET['indexAction']='listaction';
	if($_FILES['csv']['tmp_name'])
	   $csvfile=$_FILES['csv']['tmp_name'];
	$data_array=file($csvfile);
	for($i=0;$i<count($data_array);$i++ )
	  $parts_array[$i]=explode(";",$data_array[$i]);
	if(!is_array($parts_array))
	  throw new Exception(_("Invalid list data."));
	else
	{ $objects = array();
		$objectsMissing = array();
		for($i=0,$j=0;$i<count($parts_array);$i++)
	  { if(trim($parts_array[$i][0]))
	    { $objectsquery=$objObject->getExactDsObject(trim($parts_array[$i][0]));
	      if(!($objectsquery))
	        $objectsMissing[$j++]=ucwords(trim($parts_array[$i][0]));
	  		else
	  		  if(array_key_exists(1,$parts_array[$i])
	  		  && ($parts_array[$i][1]!='')
	  		  && (ucwords(trim($parts_array[$i][1]))!=$objectsquery))
					  $objects[$i] = array($objectsquery, trim($parts_array[$i][1]).' ('.$objectsquery.')');
					else
					  $objects[$i] = array($objectsquery, trim($parts_array[$i][0]));	
			}
	  }
	  if(count($objectsMissing) > 0)
      { $errormessage = _("Errors have occured during the import.") . 
            "<br /> <ul><li>" . _("Unknown objects:") . "<ul>";
	    for ($i = 0;$i < count($objectsMissing);$i++ )
	      $errormessage = $errormessage . "<li>" . $objectsMissing[$i] . "</li>";
	    $errormessage = $errormessage .  "</ul></li></ul>";
	    $entryMessage=$errormessage;
	    //throw new Exception($errormessage);
	  }
	  else
	  { if($myList)
			{ for ($i=0;$i<count($objects);$i++)
	  	    $objList->addObjectToList($objects[$i][0],$objects[$i][1]);
				$_GET['indexAction']='listaction';
			}
			else
		 		throw new Exception(_("You have to select a list of your own if you want to import into it."));
	  }
	}
}
?>
