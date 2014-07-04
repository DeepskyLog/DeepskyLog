<?php  
// filters.php
// The filters class collects all functions needed to enter, retrieve and adapt filters data from the database.

global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";

class Filters
{public  function addFilter($name, $type, $color, $wratten, $schott)                    // addFilter adds a new filter to the database. The name, type, color, wratten and schott should be given as parameters. 
 { global $objDatabase;
   $objDatabase->execSQL("INSERT INTO filters (name, type, color, wratten, schott) VALUES (\"".$name."\", \"".$type."\", \"".$color."\", \"".$wratten."\", \"".$schott."\")");
	 return $objDatabase->selectSingleValue("SELECT id FROM filters ORDER BY id DESC LIMIT 1",'id','');
 }
 public  function getAllFiltersIds($id)                                                 // returns a list with all id's which have the same name as the name of the given id
 {global $objDatabase;
  return $objDatabase->selectSinleArray("SELECT id FROM filters WHERE name = \"".$objDatabase->selectSingleValue("SELECT name FROM filters WHERE id = \"".$id."\"")."\"");
 }
 public  function getEchoColor($color)
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
   return "-";
 }
 public  function getEchoListColor($color,$disabled="")
 { $tempColorList="<select name=\"color\" class=\"form-control inputfield\" ".$disabled." >";
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
 public  function getEchoListType($type,$disabled="")
 { $tempTypeList="<select name=\"type\" class=\"form-control inputfield\" ".$disabled." >";
   $tempTypeList.= "<option ".(($type==FilterOther)?     " selected=\"selected\" ":"")." value=\"".FilterOther.     "\">".FiltersOther."</option>";
   $tempTypeList.= "<option ".(($type==FilterBroadBand)? " selected=\"selected\" ":"")." value=\"".FilterBroadBand. "\">".FiltersBroadBand."</option>";
   $tempTypeList.= "<option ".(($type==FilterNarrowBand)?" selected=\"selected\" ":"")." value=\"".FilterNarrowBand."\">".FiltersNarrowBand."</option>";
   $tempTypeList.= "<option ".(($type==FilterOIII)?      " selected=\"selected\" ":"")." value=\"".FilterOIII.      "\">".FiltersOIII."</option>";
   $tempTypeList.= "<option ".(($type==FilterHAlpha)?    " selected=\"selected\" ":"")." value=\"".FilterHAlpha.    "\">".FiltersHAlpha."</option>";
   $tempTypeList.= "<option ".(($type==FilterColor)?     " selected=\"selected\" ":"")." value=\"".FilterColor.     "\">".FiltersColor."</option>";
   $tempTypeList.= "<option ".(($type==FilterCorrective)?" selected=\"selected\" ":"")." value=\"".FilterCorrective."\">".FiltersCorrective."</option>";
   $tempTypeList.= "</select>";
   return $tempTypeList;
 }
 public  function getEchoType($type)
 { if($type == FilterOther)      return FiltersOther;
	 if($type == FilterBroadBand)  return FiltersBroadBand;
	 if($type == FilterNarrowBand) return FiltersNarrowBand;
	 if($type == FilterOIII)       return FiltersOIII;
	 if($type == FilterHBeta)      return FiltersHBeta;
	 if($type == FilterHAlpha)     return FiltersHAlpha;
	 if($type == FilterColor)      return FiltersColor;
	 if($type == FilterNeutral)    return FiltersNeutral;
	 if($type == FilterCorrective) return FiltersCorrective;
	 return "-";
 }
 public  function getFilterId($name, $observer)                                         // returns the id for this instrument
 { global $objDatabase; return $objDatabase->selectSingleValue("SELECT id FROM filters where name=\"".$name."\" and observer=\"".$observer."\"",'id',-1);
 }
 public  function getFilterObserverPropertyFromName($name, $observer, $property)        // returns the property for the filter of the observer
 { global $objDatabase; 
   return $objDatabase->selectSingleValue("SELECT ".$property." FROM filters where name=\"".$name."\" and observer=\"".$observer."\"",$property);
 }
 public  function getFilterPropertiesFromId($id)                                        // returns the properties of the filters with id
 { global $objDatabase;
   return $objDatabase->selectRecordArray("SELECT * FROM filters WHERE id=\"".$id."\"");
 }
 public  function getFilterPropertyFromId($id,$property,$defaultValue='')               // returns the property of the given filter
 { global $objDatabase; 
   return $objDatabase->selectSingleValue("SELECT ".$property." FROM filters WHERE id = \"".$id."\"",$property,$defaultValue);
 }
 public  function getFilterUsedFromId($id)                                              // returns the number of times the eyepiece is used in observations
 { global $objDatabase; 
   return $objDatabase->selectSingleValue("SELECT count(id) as ObsCnt FROM observations WHERE filterid=\"".$id."\"",'ObsCnt',0);
 }
 public  function getSortedFilters($sort, $observer = "",$active='')                               // returns an array with the ids of all filters, sorted by the column specified in $sort
 { global $objDatabase; 
   return $objDatabase->selectSingleArray("SELECT id, name FROM filters ".($observer?"WHERE observer LIKE \"".$observer."\" ".($active?" AND filteractive=".$active:''):" GROUP BY name")." ORDER BY ".$sort.", name",'id');  
 } 
 public  function setFilterProperty($id,$property,$propertyValue)                       // sets the property to the specified value for the given filter
 { global $objDatabase;
   return $objDatabase->execSQL("UPDATE filters SET ".$property." = \"".$propertyValue."\" WHERE id = \"".$id."\"");
 }
 public  function showFiltersObserver()
 { global $baseURL,$loggedUser,$objUtil,$objFilter,$objPresentations,$loggedUserName;
   $filts=$objFilter->getSortedFilters('id', $loggedUser);
   if(count($filts)>0)
   { // Add the button to select which columns to show
	 $objUtil->addTableColumSelector();

	 echo "<table class=\"table table-condensed table-striped table-hover tablesorter custom-popup\">";
	 echo "<thead><tr>";
     echo "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\">".LangViewActive."</th>";
     echo "<th>".LangViewFilterName."</th>";
     echo "<th>".LangViewFilterType."</th>";
     echo "<th>".LangViewFilterColor."</th>";
     echo "<th>".LangViewFilterWratten."</th>";
     echo "<th>".LangViewFilterSchott."</th>";
     echo "<th>".LangTopObserversHeader3."</th>";
     echo "</tr></thead>";
     while(list($key, $value)=each($filts))
     { $filterProperties=$objFilter->getFilterPropertiesFromId($value);
       echo "<tr>";
       echo "<td>".
            "<input id=\"filteractive".$value."\" type=\"checkbox\" ".($filterProperties['filteractive']?" checked=\"checked\" ":"").
                    " onclick=\"setactivation('filter',".$value.");\" />".
            "</td>";
       echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_filter&amp;filter=".urlencode($value)."\">".stripslashes($filterProperties['name'])."</a></td>";
       echo "<td>".$objFilter->getEchoType($filterProperties['type'])."</td>";
       echo "<td>".$objFilter->getEchoColor($filterProperties['color'])."</td>";
       echo "<td>".($filterProperties['wratten']?$filterProperties['wratten']:"-")."</td>";
       echo "<td>".($filterProperties['schott']?$filterProperties['schott']:"-")."</td>";
       echo "<td>";
       if(!($obsCnt=$objFilter->getFilterUsedFromId($value))) {
         echo "<a href=\"".$baseURL."index.php?indexAction=validate_delete_filter&amp;filterid=" . urlencode($value) . "\">" . LangRemove . "</a>";
       } else {
         echo "<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;observer=".$loggedUser."&amp;filter=".$value."&amp;exactinstrumentlocation=true\">";
         if ($obsCnt > 1) {
	       echo $obsCnt.' '.LangGeneralObservations."</a>";
		 } else {
		   echo $obsCnt.' '.LangGeneralObservation."</a>";
		 }
       }
       echo "</td>";
       echo "</tr>";
	   }
     echo "</table>";
	 echo $objUtil->addTablePager();

	 echo $objUtil->addTableJavascript();
     echo "<hr />";
   }
 } 
 public  function validateDeleteFilter()                                                // validates and deletes a filter
 { global $objUtil, $objDatabase;
   if(($filterid=$objUtil->checkGetKey('filterid')) 
   && $objUtil->checkAdminOrUserID($this->getFilterPropertyFromId($filterid,'observer'))
   && (!($this->getFilterUsedFromId($filterid))))
   { $objDatabase->execSQL("DELETE FROM filters WHERE id=\"".$filterid."\"");
     return LangValidateFilterMessage6;
	 }
 }
 public  function validateSaveFilter()                                                  // validates and saves a filter and returns a message 
 { global $objUtil,$loggedUser;
   if($objUtil->checkPostKey('add')
   && $objUtil->checkSessionKey('deepskylog_id')
   && $objUtil->checkPostKey('filtername'))
   { $id=$this->addFilter($objUtil->checkPostKey('filtername'), $objUtil->checkPostKey('type'), $objUtil->checkPostKey('color',0), $objUtil->checkPostKey('wratten'), $objUtil->checkPostKey('schott'));
     $this->setFilterProperty($id, 'observer', $loggedUser);
     return LangValidateFilterMessage2;
   }
   elseif($objUtil->checkPostKey('change')
   && $objUtil->checkPostKey('id')
   && $objUtil->checkPostKey('filtername')
   && $objUtil->checkAdminOrUserID($this->getFilterPropertyFromId($_POST['id'],'observer')))
   { $this->setFilterProperty($_POST['id'], 'name', $objUtil->checkPostKey('filtername'));
     $this->setFilterProperty($_POST['id'], 'type', $objUtil->checkPostKey('type'));
     $this->setFilterProperty($_POST['id'], 'color', $objUtil->checkPostKey('color',0));
     $this->setFilterProperty($_POST['id'], 'wratten', $objUtil->checkPostKey('wratten'));
     $this->setFilterProperty($_POST['id'], 'schott', $objUtil->checkPostKey('schott'));
     //$this->setFilterProperty($_POST['id'], 'observer', $loggedUser);
     return LangValidateFilterMessage5;
   }
   else
     return LangValidateMessage1;
 }
}
?>
