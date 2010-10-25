<?php 
// setup_objects_query.php
// interface to query objects

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else setup_objects_query();

function setup_objects_query()
{ global $baseURL,$loggedUser,$objPresentations,$objUtil,$objObject,$objList,$objAtlas,
         $catalog,$catNumber,$atlas,$atlasPageNumber,$entryMessage,$DSOcatalogs,
         $pageError,$minDeclDegreesError,$minDeclMinutesError,$minDeclSecondsError,$maxDeclDegreesError,$maxDeclMinutesError,$maxDeclSecondsError,
         $minRAHoursError,$minRAMinutesError,$minRASecondsError,$maxRAHoursError,$maxRAMinutesError,$maxRASecondsError,$maxMagError,$minMagError,
         $maxSBError,$minSBError,$minSizeError,$maxSizeError,$minContrastError,$maxContrastError,$listError;
	$QobjParamsCount=26;
	if($objUtil->checkGetKey('object'))
	  $entryMessage.=LangInstructionsNoObjectFound.$_GET['object'];
	$link=$baseURL."index.php?indexAction=query_objects";
	reset($_GET);
	while(list($key,$value)=each($_GET))
		if(($key!='indexAction')&&($key!='multiplepagenr')&&($key!='sort')&&($key!='sortdirection')&&($key!='showPartOfs'))
	    $link.='&amp;'.$key.'='.$value;
	echo "<div id=\"main\">";
	echo "<form action=\"".$baseURL."index.php\" method=\"get\">";
	echo "<div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"query_objects\" />";
	echo "<input type=\"hidden\" name=\"title\" value=\"".LangSelectedObjectsTitle."\" />";
	echo "<input type=\"hidden\" name=\"source\" value=\"setup_objects_query\" />";
	echo "<input type=\"hidden\" name=\"sort\" value=\"showname\" />";
	echo "<input type=\"hidden\" name=\"sortdirection\" value=\"asc\" />";
	echo "<input type=\"hidden\" name=\"showPartOfs\" value=\"0\" />";
	$content1=LangSeen.":";
	$seen=$objUtil->checkGetKey('seen');
	if($seen=='')
	  if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	    $seen=$_SESSION['QobjParams']['seen'];
	$content2="<select name=\"seen\" id=\"seen\" class=\"inputfield\">";
	$content2.="<option value=\"A\"  ".($seen=="A" ?"selected=\"selected\"":"").">".LangSeenDontCare."</option>";
	$content2.="<option value=\"XY\" ".($seen=="XY"?"selected=\"selected\"":"").">".LangSeenByMeOrSomeoneElse."</option>";
	$content2.="<option value=\"SD\" ".($seen=="SD"?"selected=\"selected\"":"").">".LangDrawn."</option>";
	$content2.="<option value=\"-\"  ".($seen=="-"?"selected=\"selected\"":"") .">".LangNotSeen."</option>";
	$content2.="<option value=\"-Z\" ".($seen=="-Z"?"selected=\"selected\"":"").">".LangNotDrawn."</option>";
	if($loggedUser)
	{ $content2.="<option value=\"Y\"   ".($seen=="Y"?"selected=\"selected\"":"")  .">".LangSeenByMe."</option>";
		$content2.="<option value=\"D\"   ".($seen=="D"?"selected=\"selected\"":"")  .">".LangDrawnByMe."</option>";
	  $content2.="<option value=\"-X\"  ".($seen=="-X"?"selected=\"selected\"":"") .">".LangNotSeenByMeOrNotSeenAtAll."</option>";
	  $content2.="<option value=\"-SZ\" ".($seen=="-SZ"?"selected=\"selected\"":"").">".LangNotDrawnByMe."</option>";
	  $content2.="<option value=\"X\"   ".($seen=="X"?"selected=\"selected\"":"")  .">".LangSeenSomeoneElse."</option>";
	}
	$content2.="</select>";
	$content3="<input type=\"submit\" name=\"query\" value=\"" . LangQueryObjectsButton1 . "\" />";
	echo "<script type=\"text/javascript\" src=\"".$baseURL."deepsky/content/setup_objects_query.js\"></script>";
	$content3.='<input type="button" onclick="clearFields();" value="'.LangQueryObjectsButton2.'"/>';
	$objPresentations->line(array("<h4>".LangQueryObjectsTitle."</h4>",$content1,$content2,$content3),"LRLR",array(20,20,40,20),30);
	echo "<hr />";
	echo "<table>";
	// OBJECT NAME 
	  echo "<tr>";
	  if($catalog=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	      $catalog=$_SESSION['QobjParams']['catalog'];
	  echo "<td class=\"fieldname\">".LangQueryObjectsField1."</td>";
	  echo "<td>";
	  echo "<select id=\"catalog\" name=\"catalog\" class=\"inputfield\">";
	  echo "<option value=\"\">-----</option>";
	  while(list($key, $value) = each($DSOcatalogs))
	    echo "<option".(($value==$catalog)?" selected=\"selected\"":"")." value=\"".$value."\">$value</option>";
	  echo "</select>";
	  if($catNumber=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	      $catNumber=$_SESSION['QobjParams']['catNumber'];
	  echo "<input id=\"catNumber\" name=\"catNumber\" type=\"text\" class=\"inputfield\" maxlength=\"255\" size=\"30\" value=\"".$catNumber .  "\" />";
	  echo "</td>";
	// ATLAS PAGE NUMBER
	  echo "<td class=\"fieldname".(($pageError)?" errorclass":"")."\">".LangQueryObjectsField12."</td>";
	  echo "<td>";
	  if($atlas=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	      $atlas=$_SESSION['QobjParams']['atlas'];
	  echo "<select id=\"atlas\" name=\"atlas\" class=\"inputfield\">";
	  echo "<option value=\"\">-----</option>";
	  while(list($key,$value)=each($objAtlas->atlasCodes))
	     echo"<option ".(($key==$atlas)?" selected=\"selected\"":"")." value=\"".$key."\">".$value."</option>"; 
	  echo "</select>";
	  if($atlasPageNumber=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	      $atlasPageNumber=$_SESSION['QobjParams']['atlasPageNumber'];
	  echo "<input id=\"atlasPageNumber\" name=\"atlasPageNumber\" type=\"text\" class=\"inputfield centered\" maxlength=\"4\" size=\"4\" value=\"" . $atlasPageNumber .  "\" />"; 
	  echo "</td>";
	  echo "</tr>";  
	// CONSTELLATION
	  echo"<tr>";
	  echo "<td class=\"fieldname\" >";
	  echo LangQueryObjectsField2;
	  echo "</td>";
	  echo "<td>";
	  $constellations = $objObject->getConstellations(); // should be sorted
	  while(list($key, $value) = each($constellations))
	    $cons[$value] = $GLOBALS[$value];
	  asort($cons);
	  reset($cons);
	  $con=$objUtil->checkGetKey('con');
	  if($con=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	      $con=$_SESSION['QobjParams']['con'];
	  echo "<select id=\"con\" name=\"con\" class=\"inputfield\">";
	  echo "<option value=\"\">-----</option>"; // empty field
	  while(list($key, $value) = each($cons))
	      echo "<option".(($key==$con)?" selected=\"selected\"":"")." value=\"$key\">".$value."</option>";
	  echo "</select>";
	  echo LangTo; 
	  $conto=$objUtil->checkGetKey('conto','');
	  if($conto=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	      $conto=$_SESSION['QobjParams']['conto'];
	  if($conto=='')
	    $conto=$con;
	  echo "<select id=\"conto\" name=\"conto\" class=\"inputfield\">";
	  echo "<option value=\"\">-----</option>"; // empty field
	  if(array_key_exists('conto',$_GET)) $con=$_GET['conto']; else $con='';
	  reset($cons);
	  while(list($key, $value) = each($cons))
	      echo "<option ".(($key==$conto)?"selected=\"selected\"":"")." value=\"$key\">".$value."</option>";
	  echo "</select>";
	  echo "</td>";
	// TYPE
	  echo "<td class=\"fieldname\" >";
	  echo LangQueryObjectsField11;
	  echo "</td>";
	  echo "<td>";
	  $types = $objObject->getDsObjectTypes(); 
	  while(list($key, $value) = each($types))
	    $stypes[$value] = $GLOBALS[$value];
	  asort($stypes);
	  $type=$objUtil->checkGetKey('type');
	  if($type=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	      $type=$_SESSION['QobjParams']['type'];
	  echo "<select id=\"type\" name=\"type\" class=\"inputfield\">";
	  echo "<option value=\"\">-----</option>";
	  while(list($key, $value) = each($stypes))
		    echo "<option ".(($key==$type)?"selected=\"selected\" ":"")."value=\"$key\">".$value."</option>";
	  echo "</select>";
	  echo "</td>"; 
	  echo "</tr>";  
	// MINIMUM DECLINATION
	  echo "<tr>";
	  echo "<td class=\"fieldname".(($minDeclDegreesError || $minDeclMinutesError || $minDeclSecondsError)?" errorclass":"")."\">".LangQueryObjectsField9."</td>";
	  echo "<td>";
	  if(($minDeclDegrees=$objUtil->checkGetKey('minDeclDegrees'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['mindecl']!==''))
	      $minDeclDegrees=(int)($_SESSION['QobjParams']['mindecl']);
	  if(($minDeclMinutes=$objUtil->checkGetKey('minDeclMinutes'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['mindecl']!==''))
	      $minDeclMinutes=(int)(abs($_SESSION['QobjParams']['mindecl']*60) % 60);
	  if(($minDeclSeconds=$objUtil->checkGetKey('minDeclSeconds'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['mindecl']!==''))
	      $minDeclSeconds=round(abs($_SESSION['QobjParams']['mindecl']*3600)) % 60;
	  echo "<input id=\"minDeclDegrees\" name=\"minDeclDegrees\" type=\"text\" class=\"inputfield centered\" maxlength=\"3\" size=\"3\" value=\"" . $minDeclDegrees .  "\" />&nbsp;&deg;&nbsp;";
	  echo "<input id=\"minDeclMinutes\" name=\"minDeclMinutes\" type=\"text\" class=\"inputfield centered\" maxlength=\"2\" size=\"2\" value=\"" . $minDeclMinutes .  "\" />&nbsp;&#39;&nbsp;";
	  echo "<input id=\"minDeclSeconds\" name=\"minDeclSeconds\" type=\"text\" class=\"inputfield centered\" maxlength=\"2\" size=\"2\" value=\"" . $minDeclSeconds .  "\" />&nbsp;&quot;&nbsp;"; 
	  echo "</td>";
	// MAXIMUM DECLINATION
	  $errorclass=($maxDeclDegreesError || $maxDeclMinutesError || $maxDeclSecondsError);
	  echo "<td class=\"fieldname".($errorclass?" errorclass":"")."\">".LangQueryObjectsField10."</td>";
	  echo "<td>";
	  if(($maxDeclDegrees=$objUtil->checkGetKey('maxDeclDegrees'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['maxdecl']!==''))
	      $maxDeclDegrees=(int)($_SESSION['QobjParams']['maxdecl']);
	  if(($maxDeclMinutes=$objUtil->checkGetKey('maxDeclMinutes'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['maxdecl']!==''))
	      $maxDeclMinutes=(int)(abs($_SESSION['QobjParams']['maxdecl']*60) % 60);
	  if(($maxDeclSeconds=$objUtil->checkGetKey('maxDeclSeconds'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['maxdecl']!==''))
	      $maxDeclSeconds=round(abs($_SESSION['QobjParams']['maxdecl']*3600)) % 60;
	  echo "<input id=\"maxDeclDegrees\" name=\"maxDeclDegrees\" type=\"text\" class=\"inputfield centered\" maxlength=\"3\" size=\"3\" value=\"" . $maxDeclDegrees .  "\" />&nbsp;&deg;&nbsp;";
	  echo "<input id=\"maxDeclMinutes\" name=\"maxDeclMinutes\" type=\"text\" class=\"inputfield centered\" maxlength=\"2\" size=\"2\" value=\"" . $maxDeclMinutes .  "\" />&nbsp;&#39;&nbsp;";
	  echo "<input id=\"maxDeclSeconds\" name=\"maxDeclSeconds\" type=\"text\" class=\"inputfield centered\" maxlength=\"2\" size=\"2\" value=\"" . $maxDeclSeconds .  "\" />&nbsp;&quot;&nbsp;";
	  echo "</td>";
	  echo "</tr>";
	// MINIMUM RIGHT ASCENSION
	  echo "<tr>";
	  $errorclass=($minRAHoursError || $minRAMinutesError || $minRASecondsError);
	  echo "<td class=\"fieldname".($errorclass?" errorclass":"")."\">".LangQueryObjectsField7."</td>";
	  echo "<td>";
	  if(($minRAHours=$objUtil->checkGetKey('minRAHours'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['minra']!==''))
	      $minRAHours=(int)($_SESSION['QobjParams']['minra']);
	  if(($minRAMinutes=$objUtil->checkGetKey('minRAMinutes'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['minra']!==''))
	      $minRAMinutes=(int)(abs($_SESSION['QobjParams']['minra']*60) % 60);
	  if(($minRASeconds=$objUtil->checkGetKey('minRASeconds'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['minra']!==''))
	      $minRASeconds=round(abs($_SESSION['QobjParams']['minra']*3600)) % 60;
	  echo "<input id=\"minRAHours\"   name=\"minRAHours\"   type=\"text\" class=\"inputfield centered\" maxlength=\"2\" size=\"2\" value=\"" . $minRAHours .  "\" />&nbsp;h&nbsp;";
	  echo "<input id=\"minRAMinutes\" name=\"minRAMinutes\" type=\"text\" class=\"inputfield centered\" maxlength=\"2\" size=\"2\" value=\"" . $minRAMinutes .  "\" />&nbsp;m&nbsp;";
	  echo "<input id=\"minRASeconds\" name=\"minRASeconds\" type=\"text\" class=\"inputfield centered\" maxlength=\"2\" size=\"2\" value=\"" . $minRASeconds .  "\" />&nbsp;s&nbsp;";
	  echo "</td>";
	// MAXIMUM RIGHT ASCENSION
	  $errorclass=($maxRAHoursError || $maxRAMinutesError || $maxRASecondsError);
	  echo "<td class=\"fieldname".($errorclass?" errorclass":"")."\">".LangQueryObjectsField8."</td>";
	  echo "<td>";
	  if(($maxRAHours=$objUtil->checkGetKey('maxRAHours'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['maxra']!==''))
	      $maxRAHours=(int)($_SESSION['QobjParams']['maxra']);
	  if(($maxRAMinutes=$objUtil->checkGetKey('maxRAMinutes'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['maxra']!==''))
	      $maxRAMinutes=(int)(abs($_SESSION['QobjParams']['maxra']*60) % 60);
	  if(($maxRASeconds=$objUtil->checkGetKey('maxRASeconds'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['maxra']!==''))
	      $maxRASeconds=round(abs($_SESSION['QobjParams']['maxra']*3600)) % 60;
	  echo "<input id=\"maxRAHours\"   name=\"maxRAHours\"   type=\"text\" class=\"inputfield centered\" maxlength=\"2\" size=\"2\" value=\"" . $maxRAHours .  "\" />&nbsp;h&nbsp;";
	  echo "<input id=\"maxRAMinutes\" name=\"maxRAMinutes\" type=\"text\" class=\"inputfield centered\" maxlength=\"2\" size=\"2\" value=\"" . $maxRAMinutes .  "\" />&nbsp;m&nbsp;";
	  echo "<input id=\"maxRASeconds\" name=\"maxRASeconds\" type=\"text\" class=\"inputfield centered\" maxlength=\"2\" size=\"2\" value=\"" . $maxRASeconds .  "\" />&nbsp;s&nbsp;";
	  echo "</td>";
	  echo "</tr>";
	  echo("<tr>");
	// MAGNITUDE BRIGHTER THAN
	  echo "<td  class=\"fieldname".(($maxMagError)?" errorclass":"")."\">".LangQueryObjectsField4."</td>";
	  echo "<td>";
	  if(($maxMag=$objUtil->checkGetKey('maxMag'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	      $maxMag=$_SESSION['QobjParams']['maxmag'];
	  echo "<input id=\"maxMag\" name=\"maxMag\" type=\"text\" class=\"inputfield centered\" maxlength=\"4\" size=\"4\" value=\"" . $maxMag .  "\" />"; 
	  echo "</td>";
	// MAGNITUDE LESSER THAN
	  echo "<td class=\"fieldname".(($minMagError)?" errorclass":"")."\" >".LangQueryObjectsField3."</td>";
	  echo "<td>";
	  if(($minMag=$objUtil->checkGetKey('minMag'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	      $minMag=$_SESSION['QobjParams']['minmag'];
	  echo "<input id=\"minMag\" name=\"minMag\" type=\"text\" class=\"inputfield centered\" maxlength=\"4\" size=\"4\" value=\"" . $minMag .  "\" />";
	  echo "</td>";
	  echo "</tr>";
	  echo "<tr>";
	// SURFACE BRIGHTNESS BRIGHTER THAN
	  echo "<td class=\"fieldname".(($maxSBError)?" errorclass":"")."\">".LangQueryObjectsField6."</td>";
	  echo "<td>";
	  if(($maxSB=$objUtil->checkGetKey('maxSB'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	      $maxSB=$_SESSION['QobjParams']['maxsubr'];
	  echo "<input id=\"maxSB\" name=\"maxSB\" type=\"text\" class=\"inputfield centered\" maxlength=\"4\" size=\"4\" value=\"" . $maxSB .  "\" />";
	  echo "</td>";
	// SURFACE BRIGHTNESS LESSER THAN
	  echo "<td class=\"fieldname".(($minSBError)?" errorclass":"")."\">".LangQueryObjectsField5."</td>";
	  echo "<td>";
	  if(($minSB=$objUtil->checkGetKey('minSB'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	      $minSB=$_SESSION['QobjParams']['minsubr'];
	  echo "<input id=\"minSB\" name=\"minSB\" type=\"text\" class=\"inputfield centered\" maxlength=\"4\" size=\"4\" value=\"" . $minSB .  "\" />";
	  echo "</td>";
	  echo "</tr>";
	  echo "<tr>";
	// MINIMIM SIZE
	  echo "<td class=\"fieldname".(($minSizeError)?" errorclass":"")."\">".LangQueryObjectsField13."</td>";
	  echo "<td>";
	  $size_min_units=$objUtil->checkGetKey('size_min_units');
	  if(($minSize=$objUtil->checkGetKey('minSize'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	    { $minSize=$_SESSION['QobjParams']['mindiam1'];
	      $size_min_units="sec";
	    }
	  echo "<input id=\"minSize\" name=\"minSize\" type=\"text\" class=\"inputfield centered\" maxlength=\"4\" size=\"4\" value=\"".$minSize."\" />";
	  echo "&nbsp;&nbsp;";
	  echo "<select id=\"size_min_units\" name=\"size_min_units\" class=\"inputfield\">";
	  echo "<option".(($size_min_units=="min")?" selected=\"selected\"":"")." value=\"min\">".LangNewObjectSizeUnits1."</option>";
	  echo "<option".(($size_min_units=="sec")?" selected=\"selected\"":"")." value=\"sec\">".LangNewObjectSizeUnits2."</option>";
	  echo "</select>";
	  echo "</td>";
	// MAXIMUM SIZE
	  echo "<td class=\"fieldname".(($maxSizeError)?" errorclass":"")."\">".LangQueryObjectsField14."</td>";
	  echo "<td>";
	  $size_max_units=$objUtil->checkGetKey('size_max_units');
	  if(($maxSize=$objUtil->checkGetKey('maxSize'))=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	    { $maxSize=$_SESSION['QobjParams']['maxdiam1'];
	      $size_max_units="sec";
	    }
	  echo"<input id=\"maxSize\" name=\"maxSize\" type=\"text\" class=\"inputfield centered\" maxlength=\"4\" size=\"4\" value=\"" . $maxSize . "\" />";
	  echo "&nbsp;&nbsp;";
	  echo "<select id=\"size_max_units\" name=\"size_max_units\" class=\"inputfield\">";
	  echo "<option".(($size_max_units=="min")?" selected=\"selected\"":"")." value=\"min\">".LangNewObjectSizeUnits1."</option>";
	  echo "<option".(($size_max_units=="sec")?" selected=\"selected\"":"")." value=\"sec\">".LangNewObjectSizeUnits2."</option>";
	  echo "</select>";
	  echo "</td>";
	  echo "</tr>";
	if($loggedUser)
	{ echo "<tr>";
	  // MINIMUM CONTRAST RESERVE
	    echo "<td class=\"fieldname".(($minContrastError)?" errorclass":"")."\">".LangQueryObjectsField18."</td>";
	    echo "<td>";
	    if(($minContrast=$objUtil->checkGetKey('minContrast'))=='')
	      if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	        $minContrast=$_SESSION['QobjParams']['minContrast'];
	    echo "<input id=\"minContrast\" name=\"minContrast\" type=\"text\" class=\"inputfield centered\" maxlength=\"4\" size=\"4\" value=\"".$minContrast."\" />";
	    echo "</td>";
	  // MAXIMUM CONTRAST RESERVE
	    echo "<td class=\"fieldname".(($maxContrastError)?" errorclass":"")."\">".LangQueryObjectsField17."</td>";
	    echo "<td>";
	    if(($maxContrast=$objUtil->checkGetKey('maxContrast'))=='')
	      if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	        $maxContrast=$_SESSION['QobjParams']['maxContrast'];
	    echo "<input id=\"maxContrast\" name=\"maxContrast\" type=\"text\" class=\"inputfield centered\" maxlength=\"4\" size=\"4\" value=\"".$maxContrast."\" />";
	    echo "</td>";
	    echo "</tr>";
	    echo "<tr>";
	  // IN LIST
	    echo "<td class=\"fieldname".(($listError)?" errorclass":"")."\">".LangQueryObjectsField19."</td>";
	    echo "<td>";
	    $lists = $objList->getLists(); 
	    echo "<select id=\"inList\" name=\"inList\" class=\"inputfield\">";
	    echo "<option value=\"\">-----</option>";
	    if(($inList=$objUtil->checkGetKey('inList'))=='')
	      if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	        $inList=$_SESSION['QobjParams']['inList'];
	    while(list($key, $value) = each($lists))
	      echo("<option".(($value==$inList)?" selected=\"selected\"":"")." value=\"".$value."\">".$value."</option>");
	    echo "</select>";
	    echo "</td>";
	  // DESCRIPTION CONTAINS
	    $descriptioncontains=$objUtil->checkGetKey('descriptioncontains');
		  if(($descriptioncontains=$objUtil->checkGetKey('descriptioncontains'))=='')
	      if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	        $descriptioncontains=$_SESSION['QobjParams']['descriptioncontains'];
	    echo "<td class=\"fieldname\">".LangDescriptioncontains."</td>";
	    echo "<td>";
	    echo "<input id=\"descriptioncontains\" name=\"descriptioncontains\" type=\"text\" class=\"inputfield\" maxlength=\"50\" size=\"30\" value=\"".$descriptioncontains."\" />";
	    echo "</td>";
	    echo "</tr>";
	  // NOT IN LIST
	    echo "<tr>";
	    echo "<td class=\"fieldname".(($listError)?" errorclass":"")."\">".LangQueryObjectsField20."</td>";
	    echo "<td>";
	    reset($lists);
	    echo "<select id=\"notInList\" name=\"notInList\" class=\"inputfield\">";
	    echo "<option value=\"\">-----</option>";
	    if(($notInList=$objUtil->checkGetKey('notInList'))=='')
	      if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	        $notInList=$_SESSION['QobjParams']['notInList'];
	    while(list($key, $value) = each($lists))
	      echo("<option".(($value==$notInList)?" selected=\"selected\"":"")." value=\"".$value."\">".$value."</option>");
	    echo "</select>";
	    echo "</td>";
	    echo "</tr>";
	}
	// EXCLUDE LARGE CATALOGS
	  echo "<tr>";
	  echo "<td class=\"fieldname\" align=\"right\">".LangExclude."</td>";
	  $j=1;
	  reset($DSOcatalogs);
	  $temp="";
	  while(list($key,$value)=each($DSOcatalogs))
	  { if(($nmb=$objObject->getNumberOfObjectsInCatalog($value))>1000)
	    { $checked='';
	  	  if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	        $checked=in_array($value,$_SESSION['QobjParams']['excl']);
	    	echo "<td><input id=\"excl_".$value."\" name=\"excl_".$value."\" type=\"checkbox\" ".($checked?"checked=\"checked\"":"")."/>".$value." (".$nmb." objects".")</td>";
	      $temp=$temp."excl_".$value."/";
	    	if(!($j++%3))
	        echo "</tr><tr><td></td>";
	    } 
	  }
	  echo "</tr>";
	// NO EXCLUDE IF SEEN
	  echo "<tr>";
	  echo "<td class=\"fieldname\" align=\"right\" style=\"width:25%\">";
	  echo LangNoExcludeIfSeen;
	  echo "</td>";
	  $excludeexceptseen=$objUtil->checkGetKey('exclexceptseen');
	  if($excludeexceptseen=='')
	    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
	      $excludeexceptseen=$_SESSION['QobjParams']['exclexceptseen'];
	  echo "<td><input id=\"excludeexceptseen\" name=\"excludeexceptseen\" type=\"checkbox\" ".($excludeexceptseen=="on"?"checked=\"checked\" ":'')." /></td>";
	  echo "<td></td>";
	  echo "</tr>";
	echo "</table>";
	echo "<input id=\"temp\" type=\"hidden\" value=\"".$temp."\" />";
	echo "</div>";
	echo "</form>";
	echo "<hr />";
	if($loggedUser)
	{ $content=LangStoredQueries."&nbsp;";
	  $content.='<select id="observerqueries" onchange="restoreQuery();"><option value="-----">-----</option></select>'.'&nbsp;';
	  $content.='<input id="savequeryas" type="button" value="'.LangSaveAs.'" onclick="saveObserverQueryAs();"/>'.'&nbsp;';
	  $content.='<input id="deletequery" type="button" value="'.LangRemoveQuery.'" class="hidden" onclick="removeQuery();"/>'.'&nbsp;';
	  $objPresentations->line(array($content),"L",array(100));
	}
	echo "</div>";
	echo '<script type="text/javascript">setobserverqueries();</script>';
}
?>
