<?php // change_instrument.php - form which allows the administrator to change an instrument 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($instrumentid=$objUtil->checkGetKey('instrument'))) throw new Exception(LangException007b);
else
{ $disabled=" disabled=\"disabled\"";
	if(($loggedUser) &&
	   ($objUtil->checkAdminOrUserID($objInstrument->getInstrumentPropertyFromId($instrumentid,'observer',''))))
	  $disabled="";
	$content=($disabled?"":"<input type=\"submit\" name=\"change\" value=\"".LangChangeInstrumentButton."\" />&nbsp;");
	$name=$objInstrument->getInstrumentPropertyFromId($instrumentid,'name');
	echo "<div id=\"main\">";
	echo "<form action=\"".$baseURL."index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_instrument\" />";
	echo "<input type=\"hidden\" name=\"id\" value=\"".$instrumentid."\" />";
	$objPresentations->line(array("<h4>".(($name=="Naked eye")?InstrumentsNakedEye:$name)."</h4>",$content),"LR",array(80,20),30);
	echo "<hr />";
	$line[]=array(LangAddInstrumentField1,
	              "<input value=\"".$name."\" type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"instrumentname\" size=\"30\" ".$disabled." />");
	$content ="<input value=\"".round($objInstrument->getInstrumentPropertyFromId($instrumentid,'diameter'), 0)."\" type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"64\" name=\"diameter\" size=\"10\" ".$disabled." />";
	$content.="<select name=\"diameterunits\"".$disabled." >";
	$content.="<option>inch</option>";
	$content.="<option selected=\"selected\">mm</option>";
	$content.="</select>";
	$line[]=array(LangAddInstrumentField2,$content);
	$line[]=array(LangAddInstrumentField5,$objInstrument->getInstrumentEchoListType($objInstrument->getInstrumentPropertyFromId($instrumentid,'type'),$disabled));
	$line[]=array("&nbsp;","&nbsp;");
	$content ="<input value=\"".(($fl=round($objInstrument->getInstrumentPropertyFromId($instrumentid,'fd')*$objInstrument->getInstrumentPropertyFromId($instrumentid,'diameter'), 0))?$fl:"")."\" type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"64\" name=\"focallength\" size=\"10\" ".$disabled." />";
	$content.="<select name=\"focallengthunits\" ".$disabled." >";
	$content.="<option>inch</option>";
	$content.="<option selected=\"selected\">mm</option>";
	$content.="</select>";
	$content.=' '.LangAddInstrumentOr.' '.LangAddInstrumentField3;
	$content.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"64\" name=\"fd\" size=\"10\"  ".$disabled." />";
	$line[]=array(LangAddInstrumentField4,$content);
	$line[]=array(LangAddInstrumentField6,"<input value=\"".(($fm=$objInstrument->getInstrumentPropertyFromId($instrumentid,'fixedMagnification'))?$fm:"")."\" type=\"text\" class=\"inputfield centered\" maxlength=\"64\" name=\"fixedMagnification\" size=\"10\" ".$disabled." />");
	for($i=0;$i<count($line);$i++)
	  $objPresentations->line($line[$i],"RLL",array(20,40,40));
	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
