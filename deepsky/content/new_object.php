<?php // new_object.php  allows the user to add an object to the database 
$phase=$objUtil->checkRequestKey('phase',0);
echo "<div id=\"main\">";
echo "<form action=\"".$baseURL."index.php\" method=\"post\"><div>";
$content="";
$content2="";
$content3="";
$content4="";
$content5="";
if($phase==2)
{ $content="<input type=\"submit\" name=\"newobject\" value=\"".LangNewObjectButton1."\" />&nbsp;";
  echo "<input type=\"hidden\" name=\"indexAction\" id=\"indexAction\" value=\"validate_object\" />";
  $entryMessage.=LangNewObjectPhase2;
}
elseif($phase==1)
{ $content="<a href=\"".$baseURL."index.php?indexAction=defaultAction\">"."<input type=\"button\" name=\"cancelnewobject\" value=\"".LangCancelNewObjectButton1."\" />&nbsp;"."</a>";
  $content4="<input type=\"submit\" name=\"phase20\" id=\"phase20\" value=\"".LangCheckRA."\" />";
  $content3="<input type=\"submit\" name=\"phase2\" id=\"phase2\" value=\"".LangObjectNotFound."\" />";
  echo "<input type=\"hidden\" name=\"phase\" id=\"phase\" value=\"1\" />";
  echo "<input type=\"hidden\" name=\"indexAction\" id=\"indexAction\" value=\"add_object\" />";
  if($objUtil->checkRequestKey(('phase20')))
    $entryMessage.=LangNewObjectPhase20;
  else
    $entryMessage.=LangNewObjectPhase1;
}
else
{ $content="<a href=\"".$baseURL."index.php?indexAction=defaultAction\">"."<input type=\"button\" name=\"cancelnewobject\" value=\"".LangCancelNewObjectButton1."\" />&nbsp;"."</a>";
  $content2="<input type=\"submit\" name=\"phase10\" id=\"phase10\" value=\"".LangCheckName."\" />";
  $content3="<input type=\"submit\" name=\"phase1\" id=\"phase1\" value=\"".LangObjectNotFound."\" />";
  echo "<input type=\"hidden\" name=\"phase\" id=\"phase\" value=\"0\" />";
  echo "<input type=\"hidden\" name=\"indexAction\" id=\"indexAction\" value=\"add_object\" />";
  if($objUtil->checkRequestKey(('phase10')))
    $entryMessage.=LangNewObjectPhase10;
  else
    $entryMessage.=LangNewObjectPhase0;
}
$objPresentations->line(array("<h4>".LangNewObjectTitle."</h4>",$content),"LR",array(80,20),30);
echo "<hr />";
$disabled=" disabled=\"disabled\" ";



//NAME
if($phase==0)
  $objPresentations->line(array("&gt;&gt;&gt;&gt;&nbsp;".LangViewObjectField1 . "&nbsp;*",
                              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"20\" name=\"catalog\" size=\"20\" value=\"".$objUtil->checkRequestKey('catalog')."\" ".(($phase==0)?"":$disabled)."/>".
                              "&nbsp;&nbsp;".
                              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"20\" name=\"number\" size=\"20\" value=\"".$objUtil->checkRequestKey('number')."\" ".(($phase==0)?"":$disabled)."/>",
                              $content2),
                        "RLL",array(20,40,40),35,array("fieldname"));
else
{ $objPresentations->line(array(LangViewObjectField1 . "&nbsp;*",
                              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"20\" name=\"catalog0\" size=\"20\" value=\"".$objUtil->checkRequestKey('catalog')."\" ".$disabled."/>".
                              "&nbsp;&nbsp;".
                              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"20\" name=\"number0\" size=\"20\" value=\"".$objUtil->checkRequestKey('number')."\" ".$disabled."/>",
                              ""),
                        "RLL",array(20,40,40),35,array("fieldname"));
  echo "<input type=\"hidden\" name=\"catalog\" id=\"catalog\" value=\"".$objUtil->checkRequestKey('catalog')."\" />";
  echo "<input type=\"hidden\" name=\"number\" id=\"number\" value=\"".$objUtil->checkRequestKey('number')."\" />";
}
// RIGHT ASCENSION
// DECLINATION
if($phase==1)
{ $content ="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"RAhours\" size=\"3\" value=\"".$objUtil->checkRequestKey('RAhours')."\" ".(($phase==1)?"":$disabled)."/>&nbsp;h&nbsp;";
  $content.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"RAminutes\" size=\"3\" value=\"".$objUtil->checkRequestKey('RAminutes')."\" ".(($phase==1)?"":$disabled)."/>&nbsp;m&nbsp;"; 
  $content.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"RAseconds\" size=\"3\" value=\"".$objUtil->checkRequestKey('RAseconds')."\" ".(($phase==1)?"":$disabled)."/>&nbsp;s&nbsp;";
  $objPresentations->line(array("&gt;&gt;&gt;&gt;&nbsp;".LangViewObjectField3 . "&nbsp;*",
                              $content,
                              $content4),                              
                        "RLL",array(20,40,40),35,array("fieldname"));
	$content ="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"3\" name=\"DeclDegrees\" size=\"3\" value=\"".$objUtil->checkRequestKey('DeclDegrees')."\" ".(($phase==1)?"":$disabled)."/>&nbsp;d&nbsp;";
	$content.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"DeclMinutes\" size=\"3\" value=\"".$objUtil->checkRequestKey('DeclMinutes')."\" ".(($phase==1)?"":$disabled)."/>&nbsp;m&nbsp;";
	$content.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"DeclSeconds\" size=\"3\" value=\"".$objUtil->checkRequestKey('DeclSeconds')."\" ".(($phase==1)?"":$disabled)."/>&nbsp;s&nbsp;";
	$objPresentations->line(array("&gt;&gt;&gt;&gt;&nbsp;".LangViewObjectField4."&nbsp;*",
	                              $content),                              
	                        "RL",array(20,80),35,array("fieldname"));
}
else
{ $content ="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"RAhours1\"   size=\"3\" value=\"".$objUtil->checkRequestKey('RAhours')  ."\" ".(($phase==1)?"":$disabled)."/>&nbsp;h&nbsp;";
  $content.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"RAminutes1\" size=\"3\" value=\"".$objUtil->checkRequestKey('RAminutes')."\" ".(($phase==1)?"":$disabled)."/>&nbsp;m&nbsp;"; 
  $content.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"RAseconds1\" size=\"3\" value=\"".$objUtil->checkRequestKey('RAseconds')."\" ".(($phase==1)?"":$disabled)."/>&nbsp;s&nbsp;";
  $objPresentations->line(array(LangViewObjectField3 . "&nbsp;*",
                              $content,
                              $content4),                              
                        "RLL",array(20,40,40),35,array("fieldname"));
	$content ="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"3\" name=\"DeclDegrees1\" size=\"3\" value=\"".$objUtil->checkRequestKey('DeclDegrees')."\" ".(($phase==1)?"":$disabled)."/>&nbsp;d&nbsp;";
	$content.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"DeclMinutes1\" size=\"3\" value=\"".$objUtil->checkRequestKey('DeclMinutes')."\" ".(($phase==1)?"":$disabled)."/>&nbsp;m&nbsp;";
	$content.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"DeclSeconds1\" size=\"3\" value=\"".$objUtil->checkRequestKey('DeclSeconds')."\" ".(($phase==1)?"":$disabled)."/>&nbsp;s&nbsp;";
	$objPresentations->line(array(LangViewObjectField4."&nbsp;*",
	                              $content),                              
	                        "RL",array(20,80),35,array("fieldname"));
  echo "<input type=\"hidden\" name=\"RAhours\"     size=\"3\" value=\"".$objUtil->checkRequestKey('RAhours')    ."\"/>";
  echo "<input type=\"hidden\" name=\"RAminutes\"   size=\"3\" value=\"".$objUtil->checkRequestKey('RAminutes')  ."\"/>"; 
  echo "<input type=\"hidden\" name=\"RAseconds\"   size=\"3\" value=\"".$objUtil->checkRequestKey('RAseconds')  ."\"/>";
  echo "<input type=\"hidden\" name=\"DeclDegrees\" size=\"3\" value=\"".$objUtil->checkRequestKey('DeclDegrees')."\"/>";
  echo "<input type=\"hidden\" name=\"DeclMinutes\" size=\"3\" value=\"".$objUtil->checkRequestKey('DeclMinutes')."\"/>";
  echo "<input type=\"hidden\" name=\"DeclSeconds\" size=\"3\" value=\"".$objUtil->checkRequestKey('DeclSeconds')."\"/>";
}
// TYPE
$content ="<select name=\"type\" class=\"requiredField\"".(($phase==2)?"":$disabled).">";
$types=$objObject->getDsObjectTypes();
while(list($key,$value)=each($types))
  $stypes[$value] = $$value;
asort($stypes);
while(list($key, $value) = each($stypes))
  $content.="<option value=\"".$key."\"".(($key==$objUtil->checkRequestKey('type'))?" selected=\"selected\" ":"").">".$value."</option>";
$content.="</select>";
$objPresentations->line(array(LangViewObjectField6 . "&nbsp;*",
                              $content),                              
                        "RL",array(20,80),35,array("fieldname"));
// CONSTELLATION 
$content ="<select name=\"con\" class=\"requiredField\"".(($phase==2)?"":$disabled).">";
$constellations = $objObject->getConstellations();
while(list($key, $value)=each($constellations))
  $content.="<option value=\"".$value."\"".(($value==$objUtil->checkRequestKey('con'))?" selected=\"selected\" ":"").">".$GLOBALS[$value]."</option>";
$content.="</select>";
$objPresentations->line(array(LangViewObjectField5 . "&nbsp;*",
                              $content),                              
                        "RL",array(20,80),35,array("fieldname"));
// MAGNITUDE
$content ="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"magnitude\" size=\"4\" value=\"".$objUtil->checkRequestKey('magnitude')."\" ".(($phase==2)?"":$disabled)."/>";
$objPresentations->line(array(LangViewObjectField7,
                              $content),                              
                        "RL",array(20,80),35,array("fieldname"));
// SURFACE BRIGHTNESS
$content ="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"sb\" size=\"4\" value=\"".$objUtil->checkRequestKey('sb')."\" ".(($phase==2)?"":$disabled)."/>";
$objPresentations->line(array(LangViewObjectField8,
                              $content),                              
                        "RL",array(20,80),35,array("fieldname"));
// SIZE
$content ="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"size_x\" size=\"4\" value=\"".$objUtil->checkRequestKey('size_x')."\"".(($phase==2)?"":$disabled)."/>&nbsp;&nbsp;";
$content.="<select name=\"size_x_units\"".(($phase==2)?"":$disabled)."> <option value=\"min\"".(("min"==$objUtil->checkRequestKey('size_x_units'))?" selected=\"selected\" ":"").">" . LangNewObjectSizeUnits1 . "</option>
			                               <option value=\"sec\"".(("sec"==$objUtil->checkRequestKey('size_x_units'))?" selected=\"selected\" ":"").">" . LangNewObjectSizeUnits2 . "</option>";
$content.="</select>";
$content.="&nbsp;&nbsp;X&nbsp;&nbsp;";
$content.="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"size_y\" size=\"4\" value=\"".$objUtil->checkRequestKey('size_y')."\"".(($phase==2)?"":$disabled)."/>&nbsp;&nbsp;";
$content.="<select name=\"size_y_units\"".(($phase==2)?"":$disabled)."> <option value=\"min\"".(("min"==$objUtil->checkRequestKey('size_y_units'))?" selected=\"selected\" ":"").">" . LangNewObjectSizeUnits1 . "</option>
			                               <option value=\"sec\"".(("sec"==$objUtil->checkRequestKey('size_y_units'))?" selected=\"selected\" ":"").">" . LangNewObjectSizeUnits2 . "</option>";
$content.="</select>";
$objPresentations->line(array(LangViewObjectField9,
                              $content),                              
                        "RL",array(20,80),35,array("fieldname"));
// POSITION ANGLE 
$content ="<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"posangle\" size=\"3\" value=\"".$objUtil->checkRequestKey('posangle')."\" ".(($phase==2)?"":$disabled)."/>&deg;";
$objPresentations->line(array(LangViewObjectField12,
                              $content),                              
                        "RL",array(20,80),35,array("fieldname"));
echo "<hr />";
if($objUtil->checkRequestKey(('phase10')))
{ $objPresentations->line(array("<h4>".LangPossibleCandidateObjects."</h4>"),"L",array(),30);
  $objPresentations->line(array(LangPossibleCandidateObjectsExplanation."&nbsp;&gt;&gt;&gt;&gt;&nbsp;".$content3),"L",array(),30);
  echo "<hr />";
  $objObject->showObjects("",0,100);
	if($FF)
	{ echo "<script type=\"text/javascript\">";
    echo "theResizeElement='obj_list';";
    echo "theResizeSize=50;";
    echo "</script>";
	}
  echo "<hr />";
}
if($objUtil->checkRequestKey(('phase20')))
{ $objPresentations->line(array("<h4>".LangPossibleCandidateObjects."</h4>",$content3),"LR",array(),30);
  $objPresentations->line(array(LangPossibleCandidateObjectsExplanation."&nbsp;&gt;&gt;&gt;&gt;&nbsp;".$content3),"L",array(),30);
  echo "<hr />";
  $objObject->showObjects("",0,100);
	if($FF)
	{ echo "<script type=\"text/javascript\">";
    echo "theResizeElement='obj_list';";
    echo "theResizeSize=50;";
    echo "</script>";
	}
  echo "<hr />";
}
echo "</div></form>";
echo "</div>";
?>
