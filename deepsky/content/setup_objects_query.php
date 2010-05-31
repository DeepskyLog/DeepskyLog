<?php // setup_objects_query.php - interface to query objects
echo "<script type=\"text/javascript\" src=\"".$baseURL."deepsky/content/setup_objects_query.js\"></script>";
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
$content2.="<option selected=\"selected\" value=\"D\">".LangSeenDontCare."</option>";
$content2.="<option value=\"-\">".LangNotSeen."</option>";
if($loggedUser)
{ $content2.="<option value=\"X\" ".($seen=="X"?"selected=\"selected\"":"").">".LangSeenSomeoneElse."</option>";
  $content2.="<option value=\"-X\" ".($seen=="-X"?"selected=\"selected\"":"").">".LangNotSeenByMeOrNotSeenAtAll."</option>";
  $content2.="<option value=\"XY\" ".($seen=="XY"?"selected=\"selected\"":"").">".LangSeenByMeOrSomeoneElse."</option>";
  $content2.="<option value=\"Y\" ".($seen=="Y"?"selected=\"selected\"":"").">".LangSeenByMe."</option>";
}
$content2.="</select>";
$content3="<input type=\"submit\" name=\"query\" value=\"" . LangQueryObjectsButton1 . "\" />";
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
  $catalogs = $objObject->getCatalogs();
  while(list($key, $value) = each($catalogs))
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
  echo "<input id=\"atlasPageNumber\" name=\"atlasPageNumber\" type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" value=\"" . $atlasPageNumber .  "\" />"; 
  echo "</td>";
  echo "</tr>";  
// CONSTELLATION
  echo"<tr>";
  echo "<td class=\"fieldname\" >";
  echo LangQueryObjectsField2;
  echo "</td>";
  echo "<td>";
  $con=$objUtil->checkGetKey('con');
  $constellations = $objObject->getConstellations(); // should be sorted
  while(list($key, $value) = each($constellations))
    $cons[$value] = $$value;
  asort($cons);
  reset($cons);
  if($con=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
      $con=$_SESSION['QobjParams']['con'];
  echo "<select id=\"con\" name=\"con\" class=\"inputfield\">";
  echo "<option value=\"\">-----</option>"; // empty field
  while(list($key, $value) = each($cons))
      echo "<option".(($key==$con)?" selected=\"selected\"":"")." value=\"$key\">".$value."</option>";
  echo "</select>";
  echo LangTo; 
  $conto=$objUtil->checkGetKey('conto',$con);
  if($conto=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
      $conto=$_SESSION['QobjParams']['conto'];
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
    $stypes[$value] = $$value;
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
  echo "<td class=\"fieldname".(($minDeclDegreesError || $minDeclMinutesError || $minDeclSecondsError)?" errorclass":"")."\">";
  echo LangQueryObjectsField9;
  echo("</td><td>");
  if($minDeclDegrees=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['mindecl']!==''))
      $minDeclDegrees=(int)($_SESSION['QobjParams']['mindecl']);
  if($minDeclMinutes=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['mindecl']!==''))
      $minDeclMinutes=(int)(abs($_SESSION['QobjParams']['mindecl']*60) % 60);
  if($minDeclSeconds=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['mindecl']!==''))
      $minDeclSeconds=round(abs($_SESSION['QobjParams']['mindecl']*3600)) % 60;
  echo "<input id=\"minDeclDegrees\" name=\"minDeclDegrees\" type=\"text\" class=\"inputfield\" maxlength=\"3\" size=\"3\" value=\"" . $minDeclDegrees .  "\" />&nbsp;&deg;&nbsp;";
  echo "<input id=\"minDeclMinutes\" name=\"minDeclMinutes\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"" . $minDeclMinutes .  "\" />&nbsp;&#39;&nbsp;";
  echo "<input id=\"minDeclSeconds\" name=\"minDeclSeconds\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"" . $minDeclSeconds .  "\" />&nbsp;&quot;&nbsp;"; 
  echo "</td>";
// MAXIMUM DECLINATION
  $errorclass=($maxDeclDegreesError || $maxDeclMinutesError || $maxDeclSecondsError);
  echo "<td class=\"fieldname".($errorclass?" errorclass":"")."\">".LangQueryObjectsField10."</td>";
  echo "<td>";
  if($maxDeclDegrees=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['maxdecl']!==''))
      $maxDeclDegrees=(int)($_SESSION['QobjParams']['maxdecl']);
  if($maxDeclMinutes=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['maxdecl']!==''))
      $maxDeclMinutes=(int)(abs($_SESSION['QobjParams']['maxdecl']*60) % 60);
  if($maxDeclSeconds=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['maxdecl']!==''))
      $maxDeclSeconds=round(abs($_SESSION['QobjParams']['maxdecl']*3600)) % 60;
  echo "<input id=\"maxDeclDegrees\" name=\"maxDeclDegrees\" type=\"text\" class=\"inputfield\" maxlength=\"3\" size=\"3\" value=\"" . $maxDeclDegrees .  "\" />&nbsp;&deg;&nbsp;";
  echo "<input id=\"maxDeclMinutes\" name=\"maxDeclMinutes\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"" . $maxDeclMinutes .  "\" />&nbsp;&#39;&nbsp;";
  echo "<input id=\"maxDeclSeconds\" name=\"maxDeclSeconds\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"" . $maxDeclSeconds .  "\" />&nbsp;&quot;&nbsp;";
  echo "</td>";
  echo "</tr>";
// MINIMUM RIGHT ASCENSION
  echo "<tr>";
  $errorclass=($minRAHoursError || $minRAMinutesError || $minRASecondsError);
  echo "<td class=\"fieldname".($errorclass?" errorclass":"")."\">".LangQueryObjectsField7."</td>";
  echo "<td>";
  if($minRAHours=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['minra']!==''))
      $minRAHours=(int)($_SESSION['QobjParams']['minra']);
  if($minRAMinutes=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['minra']!==''))
      $minRAMinutes=(int)(abs($_SESSION['QobjParams']['minra']*60) % 60);
  if($minRASeconds=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['minra']!==''))
      $minRASeconds=round(abs($_SESSION['QobjParams']['minra']*3600)) % 60;
  echo "<input id=\"minRAHours\" name=\"minRAHours\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"" . $minRAHours .  "\" />&nbsp;h&nbsp;";
  echo "<input id=\"minRAMinutes\" name=\"minRAMinutes\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"" . $minRAMinutes .  "\" />&nbsp;m&nbsp;";
  echo "<input id=\"minRASeconds\" name=\"minRASeconds\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"" . $minRASeconds .  "\" />&nbsp;s&nbsp;";
  echo "</td>";
// MAXIMUM RIGHT ASCENSION
  $errorclass=($maxRAHoursError || $maxRAMinutesError || $maxRASecondsError);
  echo "<td class=\"fieldname".($errorclass?" errorclass":"")."\">".LangQueryObjectsField8."</td>";
  echo "<td>";
  if($maxRAHours=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['maxra']!==''))
      $maxRAHours=(int)($_SESSION['QobjParams']['maxra']);
  if($maxRAMinutes=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['maxra']!==''))
      $maxRAMinutes=(int)(abs($_SESSION['QobjParams']['maxra']*60) % 60);
  if($maxRASeconds=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount)&&($_SESSION['QobjParams']['maxra']!==''))
      $maxRASeconds=round(abs($_SESSION['QobjParams']['maxra']*3600)) % 60;
  echo "<input id=\"maxRAHours\" name=\"maxRAHours\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"" . $maxRAHours .  "\" />&nbsp;h&nbsp;";
  echo "<input id=\"maxRAMinutes\" name=\"maxRAMinutes\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"" . $maxRAMinutes .  "\" />&nbsp;m&nbsp;";
  echo "<input id=\"maxRASeconds\" name=\"maxRASeconds\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"" . $maxRASeconds .  "\" />&nbsp;s&nbsp;";
  echo "</td>";
  echo "</tr>";

  
  
  
  
  
  
  
  
  
  
  echo("<tr>");
  // MAGNITUDE BRIGHTER THAN
  echo("<td  class=\"fieldname".(($maxMagError)?" errorclass":"")."\">");
  echo LangQueryObjectsField4;
  echo("</td><td>");
  if($maxMag=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
      $maxMag=$_SESSION['QobjParams']['maxmag'];
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxMag\" size=\"4\" value=\"" . $maxMag .  "\" />"); 
  echo("</td>");
  // MAGNITUDE LESSER THAN
  echo("<td class=\"fieldname".(($minMagError)?" errorclass":"")."\" >");
  echo LangQueryObjectsField3;
  echo("</td><td>");
  if($minMag=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
      $minMag=$_SESSION['QobjParams']['minmag'];
  echo("<input type=\"text\" class=\"inputfield centered\" maxlength=\"4\" name=\"minMag\" size=\"4\" value=\"" . $minMag .  "\" />");
  echo("</td>");
  echo("</tr>");
  
  echo("<tr>");
  // SURFACE BRIGHTNESS BRIGHTER THAN
  echo("<td class=\"fieldname".(($maxSBError)?" errorclass":"")."\">");
  echo LangQueryObjectsField6;
  echo("</td><td>");
    if($maxSB=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
      $maxSB=$_SESSION['QobjParams']['maxsubr'];
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxSB\" size=\"4\" value=\"" . $maxSB .  "\" />");
  echo("</td>");
  // SURFACE BRIGHTNESS LESSER THAN
  echo("<td class=\"fieldname".(($minSBError)?" errorclass":"")."\">");
  echo LangQueryObjectsField5;
  echo("</td><td>");
  if($minSB=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
      $minSB=$_SESSION['QobjParams']['minsubr'];
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"minSB\" size=\"4\" value=\"" . $minSB .  "\" />");
  echo("</td>");
  echo("</tr>");
  
  echo("<tr>");
  // MINIMIM SIZE
  echo("<td class=\"fieldname".(($minSizeError)?" errorclass":"")."\">");
  echo LangQueryObjectsField13;
  echo("</td><td>");
  if($minSize=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
    { $minSize=$_SESSION['QobjParams']['minsubr'];
      if(($size_min_units!="sec")&&$minSize)
        $minSize=$minSize/60;
    }
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"minSize\" size=\"4\" value=\"" . $minSize . "\" />");
  echo("&nbsp;&nbsp;<select name=\"size_min_units\" class=\"inputfield\">");
  if($size_min_units=="min") echo("<option selected=\"selected\" value=\"min\">" . LangNewObjectSizeUnits1 . "</option>"); else echo("<option value=\"min\">" . LangNewObjectSizeUnits1 . "</option>");
  if($size_min_units=="sec") echo("<option selected=\"selected\" value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>"); else echo("<option value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>");
  echo("</select></td>");
  // MAXIMUM SIZE
  echo("<td class=\"fieldname".(($maxSizeError)?" errorclass":"")."\">");
  echo LangQueryObjectsField14;
  echo("</td><td>");
  if($maxSize=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
    { $maxSize=$_SESSION['QobjParams']['minsubr'];
      if(($size_max_units!="sec")&&$maxSize)
        $maxSize=$maxSize/60;
    }
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxSize\" size=\"4\" value=\"" . $maxSize . "\" />");
  echo("&nbsp;&nbsp;<select name=\"size_max_units\" class=\"inputfield\">");
  if($size_max_units=="min") echo("<option selected=\"selected\" value=\"min\">" . LangNewObjectSizeUnits1 . "</option>"); else echo("<option value=\"min\">" . LangNewObjectSizeUnits1 . "</option>");
  if($size_max_units=="sec") echo("<option selected=\"selected\" value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>"); else echo("<option value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>");
  echo("</select></td>");
  echo("</tr>");

if($loggedUser)
{ echo("<tr>");
  // MINIMUM CONTRAST RESERVE
  echo("<td class=\"fieldname".(($minContrastError)?" errorclass":"")."\">");
  echo LangQueryObjectsField18;
  echo("</td>");
  echo("<td>");
  if($minContrast=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
      $minContrast=$_SESSION['QobjParams']['minContrast'];
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"minContrast\" size=\"4\" value=\"" . $minContrast .  "\" />");
  echo("</td>");
  // MAXIMUM CONTRAST RESERVE
  echo("<td"); 
  echo(" class=\"fieldname".(($maxContrastError)?" errorclass":"")."\">");
  echo LangQueryObjectsField17;
  echo("</td><td>");
  if($maxContrast=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
      $maxContrast=$_SESSION['QobjParams']['maxContrast'];
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxContrast\" size=\"4\" value=\"" . $maxContrast .  "\" />");
  echo("</td>");
  echo("</tr>");
  echo("<tr>");
  // IN LIST
  echo("<td"); 
  echo(" class=\"fieldname".(($listError)?" errorclass":"")."\">");
  echo LangQueryObjectsField19;
  echo("</td><td>");
  echo("<select name=\"inList\" class=\"inputfield\">");
  echo("<option value=\"\">-----</option>"); // empty field
  $lists = $objList->getLists(); 
  if($inList=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
      $inList=$_SESSION['QobjParams']['inList'];
  while(list($key, $value) = each($lists))
    if($value==$inList)
      echo("<option selected=\"selected\" value=\"".$value."\">".$value."</option>");
    else
      echo("<option value=\"".$value."\">".$value."</option>");
  echo("</select>");
  echo("</td>");
  $descriptioncontains=$objUtil->checkGetKey('descriptioncontains');
	if($descriptioncontains=='')
    if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
      $descriptioncontains=$_SESSION['QobjParams']['descriptioncontains'];
  echo "<td class=\"fieldname\">";
  echo LangDescriptioncontains;
  echo "</td>";
  echo "<td>";
  echo "<input id=\"descriptioncontains\" name=\"descriptioncontains\"  type=\"text\" class=\"inputfield\" maxlength=\"50\" size=\"30\" value=\"".$descriptioncontains."\" />";
  echo "</td>";
  // NOT IN LIST
  /*
    echo("<td"); 
    echo(" class=\"fieldname".(($listError)?" errorclass":"")."\">");
    echo LangQueryObjectsField20;
    echo("</td><td>");
    echo("<select name=\"notInList\">");
    echo("<option value=\"\">&nbsp;</option>"); // empty field
    $lists = $objList->getLists(); 
    while(list($key, $value) = each($lists))
      if($value==$notInList)
        echo("<option selected=\"selected\" value=\"$value\">$value</option>");
    else
        echo("<option value=\"$value\">$value</option>");
    echo("</select>");
    echo("</td>");
  */
  echo("</tr>");
}
echo "<tr>";
echo "<td class=\"fieldname\" align=\"right\">".LangExclude."</td>";
$j=1;
reset($catalogs);
while(list($key,$value)=each($catalogs))
{ if(($nmb=$objObject->getNumberOfObjectsInCatalog($value))>1000)
  { $checked='';
  	if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
      $checked=in_array($value,$_SESSION['QobjParams']['excl']);
  	echo "<td><input type=\"checkbox\" name=\"excl_".$value."\" ".($checked?"checked=\"checked\"":"")."/>".$value." (".$nmb." objects".")</td>";
    if(!($j++%3))
      echo "</tr><tr><td></td>";
  } 
} 
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\" align=\"right\" style=\"width:25%\">";
echo LangNoExcludeIfSeen;
echo "</td>";
$excludeexceptseen=$objUtil->checkGetKey('exclexceptseen');
if($excludeexceptseen=='')
  if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])==$QobjParamsCount))
    $excludeexceptseen=$_SESSION['QobjParams']['exclexceptseen'];
echo "<td><input type=\"checkbox\" name=\"excludeexceptseen\" ".($excludeexceptseen=="on"?"checked=\"checked\" ":'')." /></td>";
echo "<td></td>";
echo "</tr>";
echo "</table>";
echo "</div>";
echo "</form>";
echo "<hr />";
echo '<input type="button" onclick="clearFields();" value="Clear fields"/>';
echo "</div>";
?>
