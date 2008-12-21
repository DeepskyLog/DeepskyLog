<?php
// The filters class collects all functions needed to enter, retrieve and
// adapt filters data from the database.

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
 function getFilterId($name, $observer)
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
 function getFiltersId($name)
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
 function getFilterType($id)
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

 // getFilterName returns the name of the given filter
 function getFilterName($id)
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
 function setFilterType($id, $type)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE filters SET type = \"$type\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setColor sets a new color for the given filter
 function setFilterColor($id, $color)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE filters SET color = \"$color\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // setName sets the name for the given filter
 function setFilterName($id, $name)
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
 function setFilterObserver($id, $observer)
 {
  $db = new database;
  $db->login();

  $sql = "UPDATE filters SET observer = \"$observer\" WHERE id = \"$id\"";
  $run = mysql_query($sql) or die(mysql_error());

  $db->logout();
 }

 // getObserver returns the observerid for this filter
 function getObserverFromFilter($id)
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

   $name = $this->getFilterName($value);
   $type = $this->getFilterType($value);
   $color = $this->getColor($value);
   $wratten = $this->getWratten($value);
   $schott = $this->getSchott($value);

   echo "<tr $class><td> $value </td><td> $name </td><td> $type </td><td> $color </td><td> $wratten </td><td> $schott </td>";

   echo "</tr>\n";

   $count++;
  }
  echo "</table>";
 }
 public function getEchoType($type)
 { if($type == FilterOther)      return FiltersOther;
	 if($type == FilterBroadBand)  return FiltersBroadBand;
	 if($type == FilterNarrowBand) return FiltersNarrowBand;
	 if($type == FilterOIII)       return FiltersOIII;
	 if($type == FilterHBeta)      return FiltersHBeta;
	 if($type == FilterHAlpha)     return FiltersHAlpha;
	 if($type == FilterColor)      return FiltersColor;
	 if($type == FilterNeutral)    return FiltersNeutral;
	 if($type == FilterCorrective) return FiltersCorrective;
	 return "Unkown type";
 }
 public function getEchoColor($color)
 { if($color == FilterColorLightRed)    return FiltersColorLightRed;
   if($color == FilterColorRed)         return FiltersColorRed;
   if($color == FilterColorDeepRed)     return FiltersColorDeepRed;
   if($color == FilterColorOrange)      return FiltersColorOrange;
   if($color == FilterColorLightYellow) return FiltersColorLightYellow;
   if($color == FilterColorDeepYellow)  return FiltersColorDeepYellow;
   if($color == FilterColorYellow)      return FiltersColorYellow;
   if($color == FilterColorYellowGreen) return FiltersColorYellowGreen;
   if($color == FilterColorLightGreen)  return FiltersColorLightGreen;
   if($color == FilterColorGreen)       return FiltersColorGreen;
   if($color == FilterColorMediumBlue)  return FiltersColorMediumBlue;
   if($color == FilterColorPaleBlue)    return FiltersColorPaleBlue;
   if($color == FilterColorBlue)        return FiltersColorBlue;
   if($color == FilterColorDeepBlue)    return FiltersColorDeepBlue;
   if($color == FilterColorDeepViolet)  return FiltersColorDeepViolet;
   return "Unknown color";
 }
 public function getEchoListColor($color)
 { $tempColorList="<select name=\"color\" class=\"inputfield\">";
   $tempColorList.="<option value=\"\">&nbsp;</option>";
 	 $tempColorList.="<option ".(($color==FilterColorLightRed)?   "selected=\"selected\" ":"")."value=\"".FilterColorLightRed.   "\">".FiltersColorLightRed."</option>";
	 $tempColorList.="<option ".(($color==FilterColorRed)?        "selected=\"selected\" ":"")."value=\"".FilterColorRed.        "\">".FiltersColorRed."</option>";
	 $tempColorList.="<option ".(($color==FilterColorDeepRed)?    "selected=\"selected\" ":"")."value=\"".FilterColorDeepRed.    "\">".FiltersColorDeepRed."</option>";
	 $tempColorList.="<option ".(($color==FilterColorOrange)?     "selected=\"selected\" ":"")."value=\"".FilterColorOrange.     "\">".FiltersColorOrange."</option>";
	 $tempColorList.="<option ".(($color==FilterColorLightYellow)?"selected=\"selected\" ":"")."value=\"".FilterColorLightYellow."\">".FiltersColorLightYellow."</option>";
	 $tempColorList.="<option ".(($color==FilterColorDeepYellow)? "selected=\"selected\" ":"")."value=\"".FilterColorDeepYellow. "\">".FiltersColorDeepYellow."</option>";
	 $tempColorList.="<option ".(($color==FilterColorYellow)?     "selected=\"selected\" ":"")."value=\"".FilterColorYellow.     "\">".FiltersColorYellow."</option>";
	 $tempColorList.="<option ".(($color==FilterColorYellowGreen)?"selected=\"selected\" ":"")."value=\"".FilterColorYellowGreen."\">".FiltersColorYellowGreen."</option>";
	 $tempColorList.="<option ".(($color==FilterColorLightGreen)? "selected=\"selected\" ":"")."value=\"".FilterColorLightGreen. "\">".FiltersColorLightGreen."</option>";
	 $tempColorList.="<option ".(($color==FilterColorGreen)?      "selected=\"selected\" ":"")."value=\"".FilterColorGreen.      "\">".FiltersColorGreen."</option>";
	 $tempColorList.="<option ".(($color==FilterColorMediumBlue)? "selected=\"selected\" ":"")."value=\"".FilterColorMediumBlue. "\">".FiltersColorMediumBlue."</option>";
	 $tempColorList.="<option ".(($color==FilterColorPaleBlue)?   "selected=\"selected\" ":"")."value=\"".FilterColorPaleBlue.   "\">".FiltersColorPaleBlue."</option>";
	 $tempColorList.="<option ".(($color==FilterColorBlue)?       "selected=\"selected\" ":"")."value=\"".FilterColorBlue.       "\">".FiltersColorBlue."</option>";
	 $tempColorList.="<option ".(($color==FilterColorDeepBlue)?   "selected=\"selected\" ":"")."value=\"".FilterColorDeepBlue.   "\">".FiltersColorDeepBlue."</option>";
	 $tempColorList.="<option ".(($color==FilterColorDeepViolet)? "selected=\"selected\" ":"")."value=\"".FilterColorDeepViolet. "\">".FiltersColorDeepViolet."</option>";
	 $tempColorList.="</select>";
 	 return $tempColorList;
 }
 public function getEchoListType($type)
 { $tempTypeList="<select name=\"type\" class=\"inputfield\">";
   $tempTypeList.= "<option ".(($type==FilterOther)?     " option selected=\"selected\" ":"")." value=\"".FilterOther.     "\">".FiltersOther."</option>";
   $tempTypeList.= "<option ".(($type==FilterBroadBand)? " option selected=\"selected\" ":"")." value=\"".FilterBroadBand. "\">".FiltersBroadBand."</option>";
   $tempTypeList.= "<option ".(($type==FilterNarrowBand)?" option selected=\"selected\" ":"")." value=\"".FilterNarrowBand."\">".FiltersNarrowBand."</option>";
   $tempTypeList.= "<option ".(($type==FilterOIII)?      " option selected=\"selected\" ":"")." value=\"".FilterOIII.      "\">".FiltersOIII."</option>";
   $tempTypeList.= "<option ".(($type==FilterHAlpha)?    " option selected=\"selected\" ":"")." value=\"".FilterHAlpha.    "\">".FiltersHAlpha."</option>";
   $tempTypeList.= "<option ".(($type==FilterColor)?     " option selected=\"selected\" ":"")." value=\"".FilterColor.     "\">".FiltersColor."</option>";
   $tempTypeList.= "<option ".(($type==FilterCorrective)?" option selected=\"selected\" ":"")." value=\"".FilterCorrective."\">".FiltersCorrective."</option>";
   $tempTypeList.= "</select>";
   return $tempTypeList;
 }
}

$objFilter=new Filters;
?>
