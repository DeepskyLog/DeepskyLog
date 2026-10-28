<?php
// objectOutlines.php
// The objects class collects all functions needed to enter, retrieve and adapt object Outlines data from the database.
// The outlines are the object_contours of the new DB: per object one or more
// contours (contour_index) of points (point_index), RA in degrees.

global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";

class ObjectOutlines
{
  private static $objectNames = null;

  public  function getAllObjects()                  // returns a list of all objects which have outlines
  { global $objDatabase_new;
    if (self::$objectNames === null)                // asked once per object on an atlas page
      self::$objectNames=$objDatabase_new->selectSingleArray("select DISTINCT(object_name) as objectname from object_contours", "objectname");
    return self::$objectNames;
  }
  public  function getOutlines($name)                // returns a list of all coordinates for the outlines of an object
  { global $objDatabase_new;
	$points=$objDatabase_new->selectRecordSetArray("select object_name as objectname, contour_index, ra_degrees / 15 as ra, decl_degrees as decl from object_contours where object_name = \"".$name."\" order by contour_index, point_index");
    // type: start, vertex, end of each contour, as in the old objectOutlines table
    for ($i = 0; $i < count($points); $i++)
    { if (($i == 0) || ($points[$i - 1]['contour_index'] != $points[$i]['contour_index']))
        $points[$i]['type'] = 'start';
      elseif (($i == count($points) - 1) || ($points[$i + 1]['contour_index'] != $points[$i]['contour_index']))
        $points[$i]['type'] = 'end';
      else
        $points[$i]['type'] = 'vertex';
    }
    return $points;
  }
}
?>
