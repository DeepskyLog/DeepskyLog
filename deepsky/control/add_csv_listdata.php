<?php
// add_csv_observations.php
// adds observations from a csv file to the database

if($_FILES['csv']['tmp_name'])
   $csvfile=$_FILES['csv']['tmp_name'];
$data_array=file($csvfile);
for($i=0;$i<count($data_array);$i++ )
  $parts_array[$i]=explode(";",$data_array[$i]);
if(!is_array($parts_array))
  throw new Exception(LangInvalidCSVListFile);
else
{ $objects = array();
	$objectsMissing = array();
	for($i=0,$j=0;$i<count($parts_array);$i++)
  { if(trim($parts_array[$i][0]))
    { $objectsquery=$objObject->getExactDsObject(trim($parts_array[$i][0]));
      if(!count($objectsquery))
        $objectsMissing[$j++]=ucwords(trim($parts_array[$i][0]));
  		else
  		  if(array_key_exists(1,$parts_array[$i])
  		  && ($parts_array[$i][1]<>'')
  		  && (ucwords(trim($parts_array[$i][1]))<>$objectsquery))
				  $objects[$i] = array($objectsquery, trim($parts_array[$i][1]).' ('.$objectsquery.')');
				else
				  $objects[$i] = array($objectsquery, trim($parts_array[$i][0]));	
		}
  }
  if (count($objectsMissing) > 0)
  { $errormessage = LangCSVListError1 . "<br /> <ul><li>" . LangCSVListError2 . " : <ul>";
    for ($i = 0;$i < count($objectsMissing);$i++ )
      $errormessage = $errormessage . "<li>" . $objectsMissing[$i] . "</li>";
    $errormessage = $errormessage .  "</ul></li></ul>";
    throw new Exception($errormessage);
  }
  else
  { if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
		{ if(array_key_exists('listname',$_SESSION) && $_SESSION['listname'] && ($objList->checkList($_SESSION['listname'])==2))
			{ for ($i=0;$i<count($objects);$i++)
  			  $objList->addObjectToList($objects[$i][0],$objects[$i][1]);
				$_GET['indexAction']='listaction';
			}
			else
	  		throw new Exception(LangListImportError2);
    }
		else
		  throw new Exception(LangListImportError1);
  }
}
?>
