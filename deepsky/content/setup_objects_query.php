<?php

// setup_objects_query.php
// interface to query objects
// version 0.4: 2005/06/28, JV 
// version 3.1, DE 20061119

include_once "../lib/objects.php";
$objects = new Objects; 

include_once "../lib/util.php";
$util = new Util();
$util->checkUserInput();

include_once "../lib/lists.php";
$list = new Lists;

include_once "../lib/observers.php";
$observer = new Observers;

$_SID='';
$min=0;
$previous = '';
$prev = '';		

$pageError = false;       
$minDeclDegreesError = false;    $minDeclMinutesError = false;    $minDeclSecondsError = false;
$maxDeclDegreesError = false;    $maxDeclMinutesError = false;    $maxDeclSecondsError = false;
$minRAHoursError = false;        $minRAMinutesError = false;      $minRASecondsError = false;
$maxRAHoursError = false;        $maxRAMinutesError = false;      $maxRASecondsError = false;
$minMagError = false;            $maxMagError = false;               
$minSBError = false;             $maxSBError = false;
$minSizeError = false;           $maxSizeError = false;
$minContrastError = false;       $maxContrastError = false; 
$listError = false;

$name = '';                                 $atlas = '';          $atlasPageNumber = '';
$catalog = '';        $catNumber = '';
$type = '';                                 $con = '';		
$minDecl = '';        $minDeclDegrees = ''; $minDeclMinutes = ''; $minDeclSeconds = '';
$maxDecl = '';        $maxDeclDegrees = ''; $maxDeclMinutes = ''; $maxDeclSeconds = '';
$minRA = '';          $minRAHours = '';     $minRAMinutes = '';   $minRASeconds = '';
$maxRA = '';          $maxRAHours = '';     $maxRAMinutes = '';   $maxRASeconds = '';
$maxMag = '';       	                      $minMag = '';
$maxSB = '';                                $minSB = '';
$minSize = '';        $minSizeC = '';       $size_min_units = ''; 
$maxSize = '';        $maxSizeC = '';       $size_max_units = ''; 
$minContrast = '';                          $maxContrast = '';    
$inList = '';                               $notInList = '';


if(array_key_exists('SID', $_GET) && $_GET['SID']) $_SID=$_GET['SID']; else $_SID='';
$min=0;   if(array_key_exists('min',$_GET) && $_GET['min'])  $min = $_GET['min'];
// CATALOG AND / OR NUMBER
$exact = 0;
if(array_key_exists('catalog',$_GET) && $_GET['catalog']) $name = $_GET['catalog'];
if(array_key_exists('catalog',$_GET)) $catalog = $_GET['catalog'];
if(array_key_exists('catNumber',$_GET)) $catNumber = $_GET['catNumber'];
if(array_key_exists('atlas',$_GET) && $_GET['atlas'])
  $atlas=$_GET['atlas'];
elseif(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'])
  $atlas=$atlassesCodes[$observer->getStandardAtlas($_SESSION['deepskylog_id'])][0];
if(array_key_exists('atlasPageNumber',$_GET)) $atlasPageNumber = $_GET['atlasPageNumber']; else $atlasPageNumber='';
if(array_key_exists('inList', $_GET)) $inList = $_GET['inList']; else $inList = '';
if(array_key_exists('notInList', $_GET)) $notInList = $_GET['notInList']; else $notInList = '';
if(array_key_exists('size_min_units',$_GET)) $size_min_units=$_GET['size_min_units']; else $size_min_units='';
if(array_key_exists('size_max_units',$_GET)) $size_max_units=$_GET['size_max_units']; else $size_max_units='';
if(array_key_exists('catNumber',$_GET) && $_GET['catNumber'])
{
  $name = ucwords(trim($name . " " . trim($_GET['catNumber'])));
  $exact = "1";
}
// ATLAS PAGE
if(array_key_exists('atlasPageNumber',$_GET) && $_GET['atlasPageNumber'])
{
  if(!is_numeric($_GET['atlasPageNumber']) || ($_GET['atlasPageNumber']<1) || ($_GET['atlasPageNumber']>5000))
    $pageError = true;
  else
    $atlasPageNumber = $_GET['atlasPageNumber'];
}
// CONSTELLATION
if(array_key_exists('con',$_GET) && $_GET['con'])     $con = $_GET['con'];
// TYPE
if(array_key_exists('type',$_GET) && $_GET['type'])   $type = $_GET['type'];
// MINIMUM DECLINATION
if(array_key_exists('minDeclDegrees',$_GET) && $_GET['minDeclDegrees']!='') 
{
  $minDeclDegrees = $_GET['minDeclDegrees'];
  if((!is_numeric($_GET['minDeclDegrees'])) || ($_GET['minDeclDegrees']<=-90) || ($_GET['minDeclDegrees']>=90))
    $minDeclDegreesError = True;
  if(array_key_exists('minDeclMinutes',$_GET) && $_GET['minDeclMinutes']!='') 
  {  
    $minDeclMinutes = $_GET['minDeclMinutes']; 
    if((!is_numeric($_GET['minDeclMinutes'])) || ($_GET['minDeclMinutes']<0) || ($_GET['minDeclMinutes']>=60))
      $minDeclMinutesError = true;
  }
  else
  {
    $minDeclMinutes = 0; 
    $_GET['minDeclMinutes']=0; 
  }
  if(array_key_exists('minDeclSeconds',$_GET) && $_GET['minDeclSeconds']!='') 
  {
    $minDeclSeconds = $_GET['minDeclSeconds']; 
    if((!is_numeric($_GET['minDeclSeconds'])) || ($_GET['minDeclSeconds']<0) || ($_GET['minDeclSeconds']>=60))
      $minDeclSecondsError = true;
  }
  else
  {
    $minDeclSeconds = 0;
    $_GET['minDeclSeconds'] = 0;
  }
  if($minDeclDegreesError || $minDeclMinutesError || $minDeclSecondsError)
    $errorQuery = true;
  else
    if(substr(trim($_GET['minDeclDegrees']),1,1)=="-")
      $minDecl = $minDeclDegrees - ($_GET['minDeclMinutes'] / 60) - ($_GET['minDeclSeconds'] / 3600);
    else 
      $minDecl = $minDeclDegrees + ($_GET['minDeclMinutes'] / 60) + ($_GET['minDeclSeconds'] / 3600);
}
// MAXIMUM DECLINATION 
if(array_key_exists('maxDeclDegrees',$_GET) && $_GET['maxDeclDegrees']!='') 
{
  $maxDeclDegrees = $_GET['maxDeclDegrees'];
  if((!is_numeric($_GET['maxDeclDegrees'])) || ($_GET['maxDeclDegrees']<=-90) || ($_GET['maxDeclDegrees']>=90))
    $maxDeclDegreesError = true;
  if(array_key_exists('maxDeclMinutes',$_GET) && $_GET['maxDeclMinutes']!='') 
  {  
    $maxDeclMinutes = $_GET['maxDeclMinutes']; 
    if((!is_numeric($_GET['maxDeclMinutes'])) || ($_GET['maxDeclMinutes']<0) || ($_GET['maxDeclMinutes']>=60))
      $maxDeclMinutesError = true;
  }
  else
  {
    $maxDeclMinutes = 0; 
    $_GET['maxDeclMinutes']=0; 
  }
  if(array_key_exists('maxDeclseconds',$_GET) && $_GET['maxDeclseconds']!='') 
  {
    $maxDeclSeconds = $_GET['maxDeclSeconds']; 
    if((!is_numeric($_GET['maxDeclSeconds'])) || ($_GET['maxDeclSeconds']<0) || ($_GET['maxDeclSeconds']>=60))
      $maxDeclSecondsError = true;
  }
  else
  {
    $maxDeclseconds = 0;
    $_GET['maxDeclSeconds'] = 0;
  }
  if($maxDeclDegreesError || $maxDeclMinutesError || $maxDeclSecondsError)
    $errorQuery = true;
  else
    if(substr(trim($_GET['maxDeclDegrees']),1,1)=="-")
      $maxDecl = $maxDeclDegrees - ($_GET['maxDeclMinutes'] / 60) - ($_GET['maxDeclSeconds'] / 3600);
    else 
      $maxDecl = $maxDeclDegrees + ($_GET['maxDeclMinutes'] / 60) + ($_GET['maxDeclSeconds'] / 3600);
} 
// MIN RA
if(array_key_exists('minRAHours',$_GET) && $_GET['minRAHours']!='') 
{
  $minRAHours = $_GET['minRAHours'];
  if((!is_numeric($_GET['minRAHours'])) || ($_GET['minRAHours']<0) || ($_GET['minRAHours']>24))
  {  $minRAHoursError = True;
echo 'MinRAHours: ' .$_GET['minRAHours'];
  
	}if(array_key_exists('minRAMinutes',$_GET) && $_GET['minRAMinutes']!='') 
  {  
    $minRAMinutes = $_GET['minRAMinutes']; 
    if((!is_numeric($_GET['minRAMinutes'])) || ($_GET['minRAMinutes']<0) || ($_GET['minRAMinutes']>=60))
      $minRAMinutesError = true;
  }
  else
  {
    $minRAMinutes = 0; 
    $_GET['minRAMinutes']=0; 
  }
  if(array_key_exists('minRASeconds',$_GET) && $_GET['minRASeconds']!='') 
  {
    if((!is_numeric($_GET['minRASeconds'])) || ($_GET['minRASeconds']<0) || ($_GET['minRASeconds']>=60))
      $minRASecondsError = true;
    else
      $minRASeconds = $_GET['minRASeconds']; 
  }
  else
  {
    $minRASeconds = 0;
    $_GET['minRASeconds'] = 0;
  }
  if(!($minRAHoursError || $minRAMinutesError || $minRASecondsError))
    $minRA = $minRAHours + ($_GET['minRAMinutes'] / 60) + ($_GET['minRASeconds'] / 3600);
}
// MAX RA
if(array_key_exists('maxRAHours',$_GET) && $_GET['maxRAHours']!='') 
{
  $maxRAHours = $_GET['maxRAHours'];
  if((!is_numeric($_GET['maxRAHours'])) || ($_GET['maxRAHours']<0) || ($_GET['maxRAHours']>24))
    $maxRAHoursError = True;
  if(array_key_exists('maxRAMinutes',$_GET) && $_GET['maxRAMinutes']!='') 
  {  
    $maxRAMinutes = $_GET['maxRAMinutes']; 
    if((!is_numeric($_GET['maxRAMinutes'])) || ($_GET['maxRAMinutes']<0) || ($_GET['maxRAMinutes']>=60))
      $maxRAMinutesError = true;
  }
  else
  {
    $maxRAMinutes = 0; 
    $_GET['maxRAMinutes']=0; 
  }
  if(array_key_exists('maxRASeconds',$_GET) && $_GET['maxRASeconds']!='') 
  {
    $maxRASeconds = $_GET['maxRASeconds']; 
    if((!is_numeric($_GET['maxRASeconds'])) || ($_GET['maxRASeconds']<0) || ($_GET['maxRASeconds']>=60))
      $maxRASecondsError = true;
  }
  else
  {
    $maxRASeconds = 0;
    $_GET['maxRASeconds'] = 0;
  }
  if(!($maxRAHoursError || $maxRAMinutesError || $maxRASecondsError))
    $maxRA = $maxRAHours + ($_GET['maxRAMinutes'] / 60) + ($_GET['maxRASeconds'] / 3600);
}
// MAGNITUDE BRIGHTER THAN
if(array_key_exists('maxMag',$_GET) && $_GET['maxMag']!='') 
{ $maxMag = $_GET['maxMag'];
  if((!is_numeric($_GET['maxMag'])) || ($_GET['maxMag']<=-2) || ($_GET['maxMag']>=30))
    $maxMagError=true;
}
// MAGNITUDE LESSER THAN
if(array_key_exists('minMag',$_GET) && $_GET['minMag']!='')   
{ $minMag = $_GET['minMag'];
  if((!is_numeric($_GET['minMag'])) || ($_GET['minMag']<=-2) || ($_GET['minMag']>=30))
    $minMagError=true;
}
// SB BRIGHTER THAN
if(array_key_exists('maxSB',$_GET) && $_GET['maxSB']!='')  
{ $maxSB = $_GET['maxSB'];
  if((!is_numeric($_GET['maxSB'])) || ($_GET['maxSB']<=-2) || ($_GET['maxSB']>=30))
    $maxSBError=true;
}
// SB LESSER THAN
if(array_key_exists('minSB',$_GET) && $_GET['minSB']!='')
{ $minSB = $_GET['minSB'];
  if((!is_numeric($_GET['minSB'])) || ($_GET['minSB']<=-2) || ($_GET['minSB']>=30))
    $minSBError=true;
}
// MINIMUM SIZE
if(array_key_exists('minSize',$_GET) && ($_GET['minSize']!=''))
{ if((!is_numeric($_GET['minSize'])) || ($_GET['minSize']<0))
    $minSizeError=True; 
  if(array_key_exists('size_min_units', $_GET) && ($_GET['size_min_units'] == "sec"))
  {
    $size_min_units = 'sec';
    $minSize = $_GET['minSize'];
    $minSizeC = $_GET['minSize'];
  }
  else
  {
    $size_min_units = 'min';
    $minSize = $_GET['minSize'];
    $minSizeC = $_GET['minSize'] * 60;
  }
}
// MAXIMUM SIZE
if(array_key_exists('maxSize',$_GET) && $_GET['maxSize']!='')
{ if((!is_numeric($_GET['maxSize'])) || ($_GET['maxSize']<0))
    $maxSizeError=True; 
  if(array_key_exists('size_max_units', $_GET) && ($_GET['size_max_units'] == "sec"))
  {
    $size_max_units = 'sec';
    $maxSize = $_GET['maxSize'];
    $maxSizeC = $_GET['maxSize'];
  }
  else
  {
    $size_max_units = 'min';
    $maxSize = $_GET['maxSize'];
    $maxSizeC = $_GET['maxSize'] * 60;
  }
}
// MIN CONTRAST
if(array_key_exists('minContrast',$_GET) && $_GET['minContrast']!='')	   
{ $minContrast = $_GET['minContrast'];
  if(!is_numeric($_GET['minContrast']))
    $minContrastError=True; 
}
// MAX CONTRAST
if(array_key_exists('maxContrast',$_GET) && $_GET['maxContrast']!='')	   
{ $maxContrast = $_GET['maxContrast'];
  if(!is_numeric($_GET['maxContrast']))
    $maxContrastError=True; 
}
if($minDecl && $maxDecl && ($minDecl<$MaxDecl))
{
  $minDeclError = True;
  $maxDeclError = True;
}
if($minRA && $maxRA && ($minRA<$maxRA))
{
  $minRAError = True;
  $maxRAError = True;
}
if($maxMag && $minMag && ($maxMag<$minMag))
{
  $maxMagError = True;
  $minMagError = True;
}    
if($minSB && $maxSB && ($maxSB<$minSB))
{
  $minSBError=True;
  $maxSBError=True;
}
if($minSizeC && $maxSizeC && ($minSizeC>$maxSizeC))
{
  $minSizeError=True;
  $maxSizeError=True;
}
if($minContrast && $maxContrast && ($minContrast > $maxContrast))
{
  $minContrastError=True;
  $maxContrastError=True;
}
if($inList && $notInList && ($inList==$notInList))
  $listError = True;
// Disable possibility to search for objects with a contrast reserve alone!!!!
if(
   (
    (int)!
    (
     (array_key_exists('con',$_GET) && ($_GET['con']!=""))                       ||
     (array_key_exists('type',$_GET) && ($_GET['type']!=""))                     || 
     (array_key_exists('catalog',$_GET) && ($_GET['catalog']!=""))               || 
     (array_key_exists('catPageNumber',$_GET) && ($_GET['catPageNumber']!=""))   || 
     (array_key_exists('minMag',$_GET) && ($_GET['minMag']!=""))                 || 
     (array_key_exists('maxMag',$_GET) && ($_GET['maxMag']!=""))                 || 
     (array_key_exists('maxSB',$_GET) && ($_GET['maxSB']!=""))                   || 
     (array_key_exists('minSB',$_GET) && ($_GET['minSB']!=""))                   || 
     (array_key_exists('minRAhours',$_GET) && ($_GET['minRAhours']!=""))         ||
     (array_key_exists('minDeclDegrees',$_GET) && ($_GET['minDeclDegrees']!="")) || 
     (array_key_exists('maxRAhours',$_GET) && ($_GET['maxRAhours']!=""))         || 
     (array_key_exists('maxDeclDegrees',$_GET) && ($_GET['maxDeclDegrees']!="")) || 
     (array_key_exists('minSize',$_GET) && ($_GET['minSize']!=""))               || 
     (array_key_exists('maxSize',$_GET) && ($_GET["maxSize"]!=""))
   )
	 )
  && 
   (
 	(array_key_exists('maxContrast',$_GET) && ($_GET['maxContrast']!=""))        ||
    (array_key_exists('minContrast',$_GET) && ($_GET['minContrast']!=""))
	 )
 )
  {
    $maxContrastError = True;
    $minContrastError = True;
  }
if($_SID)
{
  if(!($pageError || $minDeclDegreesError || $minDeclMinutesError || $minDeclSecondsError || 
         $maxDeclDegreesError || $maxDeclMinutesError || $maxDeclSecondsError || $minRAHoursError || 
         $minRAMinutesError || $minRASecondsError || $maxRAHoursError || $maxRAMinutesError || 
         $maxRASecondsError || $minMagError || $maxMagError || $minSBError || $maxSBError || 
         $minSizeError || $maxSizeError || $minContrastError || $maxContrastError ||$listError))
  {
      $query = array("name"          => $name,
                     "type"          => $type,
                     "constellation"   => $con,             
                     "minmag"          => $minMag,
                     "maxmag"          => $maxMag,
                     "minsubr"         => $minSB,             
                     "maxsubr"         => $maxSB,
                     "minra"           => $minRA,   
                     "maxra"           => $maxRA,
                     "mindecl"         => $minDecl,
                     "maxdecl"         => $maxDecl,
                     "mindiam1"        => $minSizeC,
                     "maxdiam1"        => $maxSizeC, 
                     "minContrast"     => $minContrast,
                     "maxContrast"     => $maxContrast,
                     "inList"          => $inList,
                     "notInList"       => $notInList,
                     "atlas"           => $atlas,
										 "atlasPageNumber" => $atlasPageNumber);
      if(array_key_exists('seen',$_GET) && $_GET['seen'])
        $seenPar = $_GET['seen'];
      else
        $seenPar = "D";
      $_SESSION['QO'] = $objects->getObjectFromQuery($query, $exact, $seenPar);
			unset($_SESSION['QOP']);
      $_GET['SO']="showname";
  }
  else
	  if(array_key_exists('QO',$_SESSION))
  		unset($_SESSION['QO']);
}

if($_SID && array_key_exists('QO',$_SESSION) && (count($_SESSION['QO']) > 0)) // valid result
  include("content/execute_query_objects.php"); 
else
{
  if($_SID)
	{
	  echo("<div id=\"main\">\n<h2>");
    echo LangSelectedObjectsTitle; // page title
    echo("</h2>\n");
    echo(LangExecuteQueryObjectsMessage2);		
    echo "<hr>";
  }
  else
	  $_SID=time();
  echo("<div id=\"main\">\n");
  echo("<h2>");
  
  echo LangQueryObjectsTitle;
  
  echo("</h2>\n");
  
  echo("<table width=\"100%\">\n");
  echo("<tr><td align=\"centre\" width=\"25%\">");
	echo("<form action=\"deepsky/index.php\">");
	echo("<input type=\"hidden\" name=\"indexAction\" value=\"query_objects\"></input>");
  echo("<input type=\"submit\" name=\"clear\" value=\"" . LangQueryObjectsButton2 . "\" />");
  echo("</form>");
  echo("</td>");
  echo("<td align=\"right\" width=\"25%\">" . LangSeen);
  echo("</td>");
  echo("<td>");
  echo("<form action=\"deepsky/index.php\" method=\"get\">\n");
  echo("<input type=\"hidden\" name=\"indexAction\" value=\"query_objects\"></input>");
  $_SID=(int) time();
  echo("<input type=\"hidden\" name=\"SID\" value=\"".$_SID."\"></input>");
  echo("<select name=\"seen\">");
  echo("<option selected value=\"D\">" . LangSeenDontCare . "</option>".
       "<option value=\"-\">" . LangNotSeen . "</option>");
  if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
  {
    echo("<option value=\"X\">" . LangSeenSomeoneElse . "</option>".
  		   "<option value=\"-X\">" . LangNotSeenByMeOrNotSeenAtAll . "</option>".
  		   "<option value=\"XY\">" . LangSeenByMeOrSomeoneElse . "</option>".
  		   "<option value=\"Y\">" . LangSeenByMe . "</option>");
  }
  echo("</select>");
  echo("</td>");
  echo("<td><input type=\"submit\" name=\"query\" value=\"" . LangQueryObjectsButton1 . "\" />\n</td>");
  echo("</tr>");
  
  echo("</table>");
  echo("<hr>");
  echo("<table>");
  
  echo("<tr>");
  // OBJECT NAME 
  echo("<td class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField1;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<select name=\"catalog\">\n");
  echo("<option value=\"\"></option>"); // empty field
  $catalogs = $objects->getCatalogues(); // should be sorted
  while(list($key, $value) = each($catalogs))
    if($value==$catalog)
      echo("<option selected value=\"$value\">$value</option>\n");
  	else
      echo("<option value=\"$value\">$value</option>\n");
  echo("</select>\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"255\" name=\"catNumber\" size=\"40\" value=\"" . $catNumber .  "\" />");
  echo("</td>");
  // ATLAS PAGE NUMBER
  echo("<td"); 
	if($pageError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField12; 
  echo("</td>\n<td width=\"25%\">\n");
  echo("<select name=\"atlas\">\n");
  while(list($key,$value)=each($atlassesCodes))
	  if($value[0]==$atlas) echo("<option selected value=\"" . $value[0] . "\">".$$value[1]."</option>\n"); 
		else echo("<option value=\"" . $value[0] . "\">".$$value[1]."</option>\n");
  echo("</select>\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"atlasPageNumber\" size=\"4\" value=\"" . $atlasPageNumber .  "\" />"); 
  echo("</td>");
  echo("</tr>");
  
  echo("<tr>");
  // CONSTELLATION
  echo("<td class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField2;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<select name=\"con\">\n");
  echo("<option value=\"\"></option>"); // empty field
  $constellations = $objects->getConstellations(); // should be sorted
  while(list($key, $value) = each($constellations))
    $cons[$value] = $$value;
  asort($cons);
  reset($cons);
	if(array_key_exists('con',$_GET)) $con=$_GET['con']; else $con='';
  while(list($key, $value) = each($cons))
    if($value==$con)
      echo("<option selected value=\"$key\">".$value."</option>\n");
		else
      echo("<option value=\"$key\">".$value."</option>\n");
  echo("</select>\n"); 
  echo("</td>");
  // TYPE
  echo("<td class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField11;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<select name=\"type\">\n");
  echo("<option value=\"\"></option>"); // empty field
  $types = $objects->getTypes(); 
  while(list($key, $value) = each($types))
    $stypes[$value] = $$value;
  asort($stypes);
	if(array_key_exists('type',$_GET)) $type=$_GET['type']; else $type='';
  while(list($key, $value) = each($stypes))
    if($value==$type)
		  echo("<option selected value=\"$key\">".$value."</option>\n");
		else
		  echo("<option value=\"$key\">".$value."</option>\n");
  echo("</select>\n");
  echo("</td>"); 
  echo("</tr>");
  
  echo("<tr>");
  // MINIMUM DECLINATION
  echo("<td"); 
	if($minDeclDegreesError || $minDeclMinutesError || $minDeclSecondsError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField9;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"minDeclDegrees\" size=\"3\" value=\"" . $minDeclDegrees .  "\" />&nbsp;&deg;&nbsp;");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minDeclMinutes\" size=\"2\" value=\"" . $minDeclMinutes .  "\" />&nbsp;&#39;&nbsp;");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minDeclSeconds\" size=\"2\" value=\"" . $minDeclSeconds .  "\" />&nbsp;&quot;&nbsp;"); 
  echo("</td>");
  // MAXIMUM DECLINATION
  echo("<td"); 
	if($maxDeclDegreesError || $maxDeclMinutesError || $maxDeclSecondsError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField10;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"maxDeclDegrees\" size=\"3\" value=\"" . $maxDeclDegrees .  "\" />&nbsp;&deg;&nbsp;");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxDeclMinutes\" size=\"2\" value=\"" . $maxDeclMinutes .  "\" />&nbsp;&#39;&nbsp;");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxDeclSeconds\" size=\"2\" value=\"" . $maxDeclSeconds .  "\" />&nbsp;&quot;&nbsp;");
  echo("</td>");
  echo("</tr>");
  
  echo("<tr>");
  // MINIMUM RIGHT ASCENSION
  echo("<td"); 
	if($minRAHoursError || $minRAMinutesError || $minRASecondsError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField7;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minRAHours\" size=\"2\" value=\"" . $minRAHours .  "\" />&nbsp;h&nbsp;");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minRAMinutes\" size=\"2\" value=\"" . $minRAMinutes .  "\" />&nbsp;m&nbsp;");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minRASeconds\" size=\"2\" value=\"" . $minRASeconds .  "\" />&nbsp;s&nbsp;");
  echo("</td>");
    // MAXIMUM RIGHT ASCENSION
  echo("<td"); 
	if($maxRAHoursError || $maxRAMinutesError || $maxRASecondsError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField8;  
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxRAHours\" size=\"2\" value=\"" . $maxRAHours .  "\" />&nbsp;h&nbsp;");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxRAMinutes\" size=\"2\" value=\"" . $maxRAMinutes .  "\" />&nbsp;m&nbsp;");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxRASeconds\" size=\"2\" value=\"" . $maxRASeconds .  "\" />&nbsp;s&nbsp;");
  echo("</td>");
  echo("</tr>");
  
	echo("<tr>");
  // MAGNITUDE BRIGHTER THAN
  echo("<td"); 
	if($maxMagError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField4;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxMag\" size=\"4\" value=\"" . $maxMag .  "\" />"); 
  echo("</td>");
  // MAGNITUDE LESSER THAN
  echo("<td"); 
	if($minMagError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField3;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" align=\"center\" maxlength=\"4\" name=\"minMag\" size=\"4\" value=\"" . $minMag .  "\" />");
  echo("</td>");
  echo("</tr>\n");
  
  echo("<tr>");
  // SURFACE BRIGHTNESS BRIGHTER THAN
  echo("<td"); 
	if($maxSBError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField6;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxSB\" size=\"4\" value=\"" . $maxSB .  "\" />");
  echo("</td>");
  // SURFACE BRIGHTNESS LESSER THAN
  echo("<td"); 
	if($minSBError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField5;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"minSB\" size=\"4\" value=\"" . $minSB .  "\" />");
  echo("</td>");
  echo("</tr>");
  
  echo("<tr>");
  // MINIMIM SIZE
  echo("<td"); 
  if($minSizeError) echo(" style=\"color:red\"");
  echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField13;
  echo("</td>\n<td>\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"minSize\" size=\"4\" value=\"" . $minSize . "\" />");
  echo("&nbsp;&nbsp;<select name=\"size_min_units\">");
  if($size_min_units=="min") echo("<option selected value=\"min\">" . LangNewObjectSizeUnits1 . "</option>"); else echo("<option value=\"min\">" . LangNewObjectSizeUnits1 . "</option>");
  if($size_min_units=="sec") echo("<option selected value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>"); else echo("<option value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>");
  echo("</select>\n</td>");
  // MAXIMUM SIZE
  echo("<td"); 
  if($maxSizeError) echo(" style=\"color:red\"");
  echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField14;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxSize\" size=\"4\" value=\"" . $maxSize . "\" />");
  echo("&nbsp;&nbsp;<select name=\"size_max_units\">");
  if($size_max_units=="min") echo("<option selected value=\"min\">" . LangNewObjectSizeUnits1 . "</option>"); else echo("<option value=\"min\">" . LangNewObjectSizeUnits1 . "</option>");
  if($size_max_units=="sec") echo("<option selected value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>"); else echo("<option value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>");
  echo("</select>\n</td>");
  echo("</tr>");

	if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
	{  
    echo("<tr>");
    // MINIMUM CONTRAST RESERVE
    echo("<td"); 
    if($minContrastError) echo(" style=\"color:red\"");
    echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
    echo LangQueryObjectsField18;
    echo("</td>\n");
    echo("<td width=\"25%\">\n");
    echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"minContrast\" size=\"4\" value=\"" . $minContrast .  "\" />");
    echo("</td>");
    // MAXIMUM CONTRAST RESERVE
    echo("<td"); 
    if($maxContrastError) echo(" style=\"color:red\"");
    echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
    echo LangQueryObjectsField17;
    echo("</td>\n<td>\n");
    echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxContrast\" size=\"4\" value=\"" . $maxContrast .  "\" />");
    echo("</td>");
    echo("</tr>");

    echo("<tr>");
    // IN LIST
    echo("<td"); 
    if($listError) echo(" style=\"color:red\"");
    echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
    echo LangQueryObjectsField19;
    echo("</td>\n<td width=\"25%\">\n");
    echo("<select name=\"inList\">\n");
    echo("<option value=\"\"></option>"); // empty field
    $lists = $list->getLists(); 
    while(list($key, $value) = each($lists))
      if($value==$inList)
        echo("<option selected value=\"$value\">$value</option>\n");
    	else
        echo("<option value=\"$value\">$value</option>\n");
    echo("</select>\n");
    echo("</td>");
    // NOT IN LIST
  /*
    echo("<td"); 
    if($listError) echo(" style=\"color:red\"");
    echo(" class=\"fieldname\" align=\"right\" width=\"25%\">");
    echo LangQueryObjectsField20;
    echo("</td>\n<td width=\"25%\">\n");
    echo("<select name=\"notInList\">\n");
    echo("<option value=\"\"></option>"); // empty field
    $lists = $list->getLists(); 
    while(list($key, $value) = each($lists))
      if($value==$notInList)
        echo("<option selected value=\"$value\">$value</option>\n");
    	else
        echo("<option value=\"$value\">$value</option>\n");
    echo("</select>\n");
    echo("</td>");
  */
    echo("</tr>");
  }
  echo("</table>");
}
echo("</div>\n</div>\n</body>\n</html>");
?>
