<?php
// The lenses class collects all functions needed to enter, retrieve and
// adapt lenses data from the database.

class Lenses
{
 // addLens adds a new lens to the database. The name and the factor
 // should be given as parameters. 
 function addLens($name, $factor)
 {
  $db = new database;
  $db->login();

  if (!$_SESSION['lang'])
  {
   $_SESSION['lang'] = "English";
  }

  $sql = "INSERT INTO lenses (name, factor) VALUES (\"$name\", \"$factor\")";

  mysql_query($sql) or die(mysql_error());

  $query = "SELECT id FROM lenses ORDER BY id DESC LIMIT 1";
  $run = mysql_query($query) or die(mysql_error());

  $db->logout();
  $get = mysql_fetch_object($run);
  if($get) 
  {
   return $get->id; 
  }
  else
  {
   return '';
  }
 }
 
 // getId returns the id for this lens
 function getLensId($name, $observer)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM lenses where name=\"$name\" and observer=\"$observer\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get)
  {
    $id = $get->id;
  }
  else
  {
    $id = -1;
  }

  $db->logout();

  return $id;
 }

 // deleteLens removes the lens with id = $id 
 function deleteLens($id)
 {
  $db = new database;
  $db->login();

  $sql = "DELETE FROM lenses WHERE id=\"$id\"";
  mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // getAllIds returns a list with all id's which have the same name as the name of the given id
 function getAllLensesIds($id)
 {
  $sql = "SELECT name FROM lenses WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $sql = "SELECT id FROM lenses WHERE name = \"$get->name\"";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $ids[] = $get->id;
  }

  return $ids;
 }

 // getLensesId returns the id of the given name of the lens
 function getLensesId($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM lenses where name=\"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);


	if ($get)
	{
		$lensid = $get->id;
	}
	else
	{
		$lensid = 0;
	}

  $db->logout();

  return $lensid;
 }

 // getLenses returns an array with all lenses
 function getLenses()
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM lenses";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $lenses[] = $get->id;
  }

  $db->logout();

  return $lenses;
 }

 // getLensesName returns an array with all lenses and names
 function getLensesName()
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM lenses";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $lenses[$get->id] = $get->name;
  }

  $db->logout();

  return $lenses;
 }

 // getFactor returns the factor of the given lens
 function getFactor($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM lenses WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get)
	{
	  $factor = $get->factor;
  }
	else
  {
		$factor = -1.0;
	}
		
  $db->logout();

  return $factor;
 }

 // getLensName returns the name of the given lens
 function getLensName($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM lenses WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);
  if ($get)
	{
    $name = $get->name;
	}
	else
	{
		$name = "";
	}

  $db->logout();

  return $name;
 }

 // getSortedLenses returns an array with the ids of all lenses, 
 // sorted by the column specified in $sort
 function getSortedLenses($sort, $observer = "", $unique = false)
 {
  $fils = array();
  $db = new database;
  $db->login();
  $lns=array();
  if ($unique == false)
  {
   if ($observer == "")
   {
    $sql = "SELECT * FROM lenses ORDER BY $sort";
   } 
   else
   {
    $sql = "SELECT * FROM lenses where observer = \"$observer\" ORDER BY $sort";
   }
  }
  else
  {
   if ($observer == "")
   {
    $sql = "SELECT id, name FROM lenses GROUP BY name";
   } 
   else
   {
    $sql = "SELECT id, name FROM lenses where observer = \"$observer\" GROUP BY name ORDER BY $sort";
   }
  } 

  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $lns[] = $get->id;
  }
  $db->logout();

  return $lns;
 }

 // getSortedLensesList returns an array with the ids of all lenses,
 // sorted by the column specified in $sort.
 function getSortedLensesList($sort, $observer = "")
 {
   $lenses = $this->getSortedLenses($sort, $observer);

	 return $lenses;
 }


 // setType sets a new factor for the given lens
 function setFactor($id, $factor)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE lenses SET factor = \"$factor\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setLensName sets the name for the given lens
 function setLensName($id, $name)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE lenses SET name = \"$name\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setLensObserver sets the observer for the lens with id = $id
 function setLensObserver($id, $observer)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE lenses SET observer = \"$observer\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }
 public function getObserverFromLens($id) // getObserver returns the observerid for this lens
 { return $GLOBALS['objDatabase']->selectSingleValue("SELECT * FROM lenses WHERE id = \"$id\"",'observer');
 }
 function showLenses() // showLenses prints a table showing all lenses. For testing  purposes only.
 
 {
  $filters = $this->getLenses();

  $count = 0;

  echo "<table width=\"100%\">
         <tr class=\"type3\">
          <td>id</td>
          <td>name</td>
          <td>factor</td>
          <td>observer</td>
         </tr>";

  while(list ($key, $value) = each($eyepieces))
  {
   if ($count % 2)
   {
    $class = "class=\"type1\"";
   }
   else
   {
    $class = "class=\"type2\"";
   }

   $name = $this->getLensName($value);
   $factor = $this->getFactor($value);
   $observer = $this->getObserverFromLens($value);

   echo "<tr $class><td> $value </td><td> $name </td><td> $factor </td><td> $observer </td>";

   echo "</tr>\n";

   $count++;
  }
  echo "</table>";
 }
}
$objLens=new Lenses;
?>
