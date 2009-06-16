<?php // change_lens.php - form which allows the owner to change a lens
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($lensid=$objUtil->checkGetKey('lens'))) throw new Exception(LangException009b);
else
{ $disabled=" disabled=\"disabled\"";
	if(($loggedUser) &&
	   ($objUtil->checkAdminOrUserID($objLens->getLensPropertyFromId($lensid,'observer',''))))
	  $disabled="";
	$content=($disabled?"":"<input type=\"submit\" name=\"change\" value=\"".LangChangeLensButton."\" />&nbsp;");
	echo "<div id=\"main\">";
	echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_lens\" />";
	echo "<input type=\"hidden\" name=\"id\"          value=\"".$lensid."\" />";
	$objPresentations->line(array("<h4>".stripslashes($objLens->getLensPropertyFromId($lensid,'name'))."</h4>",$content),"LR",array(80,20),30); 
	echo "<hr />";
	$line[]=array(LangAddLensField1,
	              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"lensname\" size=\"30\" value=\"".stripslashes($objLens->getLensPropertyFromId($lensid,'name'))."\" ".$disabled." />",
	              LangAddLensField1Expl);
	$line[]=array(LangAddLensField2,
	              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"5\" name=\"factor\" size=\"5\" value=\"".stripslashes($objLens->getLensPropertyFromId($lensid,'factor'))."\" ".$disabled." />",
	              LangAddLensField2Expl);
	for($i=0;$i<count($line);$i++)
	  $objPresentations->line($line[$i],"RLL",array(20,40,40),'',array("fieldname","fieldvalue","fieldexplanation"));
	echo "<hr />";
	echo "</form>";
	echo "</div>";
}
?>