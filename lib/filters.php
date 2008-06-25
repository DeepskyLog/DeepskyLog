<?php

// The filters class collects all functions needed to enter, retrieve and
// adapt filters data from the database.
//
// Version 3.2, WDM 20/01/2007

include_once "database.php";
include_once "setup/language.php";

class Filters
{
 // addFilter adds a new filter to the database. The name, type, color, wratten
 // and schott should be given as parameters. 
 function addFilter($name, $type, $color, $wratten, $schott)
 {
  $db = new database;
  $db->login();

  if (!$_SESSION['lang'])
  {
   $_SESSION['lang'] = "English";
  }

  $sql = "INSERT INTO filters (name, type, color, wratten, schott) VALUES (\"$name\", \"$type\", \"$color\", \"$wratten\", \"$schott\")";

  mysql_query($sql) or die(mysql_error());

  $query = "SELECT id FROM filters ORDER BY id DESC LIMIT 1";
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
 
 // getId returns the id for this filter
 function getId($name, $observer)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM filters where name=\"$name\" and observer=\"$observer\"";
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

 // deleteFilter removes the filter with id = $id 
 function deleteFilter($id)
 {
  $db = new database;
  $db->login();

  $sql = "DELETE FROM filters WHERE id=\"$id\"";
  mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // getAllIds returns a list with all id's which have the same name as the name of the given id
 function getAllFiltersIds($id)
 {
  $sql = "SELECT name FROM filters WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $sql = "SELECT id FROM filters WHERE name = \"$get->name\"";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $ids[] = $get->id;
  }

  return $ids;
 }

 // getFiltersId returns the id of the given name of the filter
 function getFilterId($name)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM filters where name=\"$name\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);


	if ($get)
	{
		$filterid = $get->id;
	}
	else
	{
		$filterid = 0;
	}

  $db->logout();

  return $filterid;
 }

 // getFilters returns an array with all filters
 function getFilters()
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM filters";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $filters[] = $get->id;
  }

  $db->logout();

  return $filters;
 }

 // getFiltersName returns an array with all filters and names
 function getFiltersName()
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM filters";
  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $fils[$get->id] = $get->name;
  }

  $db->logout();

  return $fils;
 }

 // getType returns the type of the given filter
 function getType($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM filters WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  if ($get)
	{
	  $type = $get->type;
  }
	else
  {
		$type = -1.0;
	}
		
  $db->logout();

  return $type;
 }

 // getName returns the name of the given filter
 function getName($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM filters WHERE id = \"$id\"";
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

 // getColor returns the color of the given filter
 function getColor($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM filters WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);
  if ($get)
	{
    $color = $get->color;
	}
	else
	{
		$color = "";
	}

  $db->logout();

  return $color;
 }

 // getWratten returns the wratten trype of the given filter
 function getWratten($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM filters WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);
  if ($get)
	{
    $wratten = $get->wratten;
	}
	else
	{
		$wratten = "";
	}

  $db->logout();

  return $wratten;
 }

 // getSchott returns the schott type of the given filter
 function getSchott($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM filters WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);
  if ($get)
	{
    $schott = $get->schott;
	}
	else
	{
		$schott = "";
	}

  $db->logout();

  return $schott;
 }

 // getSortedFilters returns an array with the ids of all filters, 
 // sorted by the column specified in $sort
 function getSortedFilters($sort, $observer = "", $unique = false)
 {
  $fils = array();
  $db = new database;
  $db->login();

  if ($unique == false)
  {
   if ($observer == "")
   {
    $sql = "SELECT * FROM filters ORDER BY $sort";
   } 
   else
   {
    $sql = "SELECT * FROM filters where observer = \"$observer\" ORDER BY $sort";
   }
  }
  else
  {
   if ($observer == "")
   {
    $sql = "SELECT id, name FROM filters GROUP BY name";
   } 
   else
   {
    $sql = "SELECT id, name FROM filters where observer = \"$observer\" GROUP BY name ORDER BY $sort";
   }
  } 

  $run = mysql_query($sql) or die(mysql_error());

  while($get = mysql_fetch_object($run))
  {
   $fils[] = $get->id;
  }
  $db->logout();

  return $fils;
 }

 // getSortedFiltersList returns an array with the ids of all filters,
 // sorted by the column specified in $sort.
 function getSortedFiltersList($sort, $observer = "")
 {
   $filters = $this->getSortedFilters($sort, $observer);

	 return $filters;
 }


 // setType sets a new type for the given filter
 function setType($id, $type)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE filters SET type = \"$type\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setColor sets a new color for the given filter
 function setColor($id, $color)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE filters SET color = \"$color\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setName sets the name for the given filter
 function setName($id, $name)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE filters SET name = \"$name\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setWratten sets a new wratten type for the given filter
 function setWratten($id, $wratten)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE filters SET wratten = \"$wratten\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setSchott sets a new schott type for the given filter
 function setSchott($id, $schott)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE filters SET schott = \"$schott\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setObserver sets the observer for the filter with id = $id
 function setObserver($id, $observer)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE filters SET observer = \"$observer\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // getObserver returns the observerid for this filter
 function getObserver($id)
 {
  $db = new database;
  $db->login();

  $sql = "SELECT * FROM filters WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $get = mysql_fetch_object($run);

  $observer = $get->observer;

  $db->logout();

  return $observer;
 }


 // showFilters prints a table showing all eyepieces. For testing 
 // purposes only.
 function showFilters()
 {
  $filters = $this->getFilters();

  $count = 0;

  echo "<table width=\"100%\">
         <tr class=\"type3\">
          <td>id</td>
          <td>name</td>
          <td>type</td>
          <td>color</td>
          <td>wratten</td>
          <td>schott</td>
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

   $name = $this->getName($value);
   $type = $this->getType($value);
   $color = $this->getColor($value);
   $wratten = $this->getWratten($value);
   $schott = $this->getSchott($value);

   echo "<tr $class><td> $value </td><td> $name </td><td> $type </td><td> $color </td><td> $wratten </td><td> $schott </td>";

   echo "</tr>\n";

   $count++;
  }
  echo "</table>";
 }
}
?>
