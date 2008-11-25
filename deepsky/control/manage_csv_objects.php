<?php
// manage_csv_objects.php
// manage objects from a csv file to the database

if($_FILES['csv']['tmp_name'] != "")
  $csvfile = $_FILES['csv']['tmp_name'];
$data_array=file($csvfile); 
for($i=0;$i<count($data_array);$i++ ) 
  $parts_array[$i]=explode(";",$data_array[$i]); 
for($i=0;$i<count($parts_array);$i++)
{ $instruction[$i]=trim($parts_array[$i][0]);
  $object[$i]=trim($parts_array[$i][1]);
  $cat[$i]=trim($parts_array[$i][2]);
  $catindex_data[$i]=trim($parts_array[$i][3]);
	if(array_key_exists(4, $parts_array[$i]))
    $data4[$i] = trim($parts_array[$i][4]);
	if(array_key_exists(5, $parts_array[$i]))
    $data5[$i] = trim($parts_array[$i][5]);
	if(array_key_exists(6, $parts_array[$i]))
    $data6[$i] = trim($parts_array[$i][6]);
	if(array_key_exists(7, $parts_array[$i]))
    $data7[$i] = trim($parts_array[$i][7]);
	if(array_key_exists(8, $parts_array[$i]))
    $data8[$i] = trim($parts_array[$i][8]);
	if(array_key_exists(9, $parts_array[$i]))
    $data9[$i] = trim($parts_array[$i][9]);
	if(array_key_exists(10, $parts_array[$i]))
    $data10[$i] = trim($parts_array[$i][10]);
	if(array_key_exists(11, $parts_array[$i]))
    $data11[$i] = trim($parts_array[$i][11]);
	if(array_key_exists(12, $parts_array[$i]))
    $data12[$i] = trim($parts_array[$i][12]);
	if(array_key_exists(13, $parts_array[$i]))
    $data13[$i] = trim($parts_array[$i][13]);
}
if(!is_array($object))
  throw new Exception(LangInvalidCSVfile);
else
{ $object=array_values($object);
	$objectsMissing = array();
  $j=0;                                                                         // Test if the objects, locations and instruments are available in the database
  for($i=0;$i<count($parts_array);$i++)
  { if ($instruction[$i] == "NO")
  	  $objObject->addDSObject($object[$i], $cat[$i], $catindex_data[$i], "", "", 0, 0, "99.9", "99.9", "0", "0", "999", "", "ADMIN");
    elseif ($instruction[$i] == "NOC")
  	  $objObject->addDSObject($object[$i], $cat[$i], $catindex_data[$i], $data4[$i], $data5[$i], $data6[$i], $data7[$i], $data8[$i], $data9[$i], $data10[$i], $data11[$i], $data12[$i], $data13[$i], "ADMIN");
  	elseif ($instruction[$i] == "AN")
  		$objObject->newAltName($object[$i], $cat[$i], $catindex_data[$i]);
  	elseif ($instruction[$i] == "NN")
  	{ $objObject->newName($object[$i], $cat[$i],$catindex_data[$i]);
  		$_GET['object'] = trim($cat[$i] . " " . ucwords(trim($catindex_data[$i])));
    }	
  	elseif ($instruction[$i] == "RAN")
  	  $objObject->removeAltName($object[$i], $cat[$i], $catindex_data[$i]);
  	elseif ($instruction[$i] == "PO")
  	  $objObject->newPartOf($object[$i], $cat[$i], $catindex_data[$i]);
  	elseif ($instruction[$i] == "RPO")
  	  $objObject->removePartOf($object[$i], $cat[$i], $catindex_data[$i]);
  	elseif ($instruction[$i] == "RRO")
    { $objObject->removeAndReplaceObjectBy($object[$i], $cat[$i], $catindex_data[$i]);
  		$_GET['object'] = trim($cat[$i] . " " . ucwords(trim($catindex_data[$i])));
  	}			
  	elseif ($instruction[$i] == "RA")
  		$objObject->setRA($object[$i], $catindex_data[$i]);
  	elseif ($instruction[$i] == "DE")
  	  $objObject->setDeclination($object[$i], $catindex_data[$i]);
  	elseif ($instruction[$i] == "CON")
  	  $objObject->setConstellation($object[$i], $catindex_data[$i]);
  	elseif ($instruction[$i] == "TYP")
  	  $objObject->setDsObjectType($object[$i], $catindex_data[$i]);
  	elseif ($instruction[$i] == "MG")
  	  $objObject->setMagnitude($object[$i], $catindex_data[$i]);
  	elseif ($instruction[$i] == "SB")
  	  $objObject->setSurfaceBrightness($object[$i], $catindex_data[$i]);
  	elseif ($instruction[$i] == "D1")
  		$objObject->setDiam1($object[$i], $catindex_data[$i]);
  	elseif ($instruction[$i] == "D2")
  		$objObject->setDiam2($object[$i], $catindex_data[$i]);
  	elseif ($instruction[$i] == "PA")
  		$objObject->setPositionAngle($object[$i], $catindex_data[$i]);
    $_GET['indexAction']="detail_object";
		$_GET['object']=$object[0];
  }
}
?>
