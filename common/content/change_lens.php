<?php 
// change_lens.php
// allows the lens owner or an administrator to change a lens
// or another user to view the lens details

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($lensid=$objUtil->checkGetKey('lens'))) throw new Exception(LangException009b);
elseif(!($objLens->getLensPropertyFromId($lensid,'name'))) throw new Exception("Lens not found in change_lens.php, please contact the developers with this message:".$eyepieceid);
else change_lens();

function change_lens()
{ global $baseURL,$lensid,$loggedUser,
         $objLens,$objPresentations,$objUtil;
  $disabled=" disabled=\"disabled\"";
	if(($loggedUser) &&
	   ($objUtil->checkAdminOrUserID($objLens->getLensPropertyFromId($lensid,'observer',''))))
	  $disabled="";
	$content=($disabled?"":"<input type=\"submit\" name=\"change\" class=\"btn btn-primary pull-right\" value=\"".LangChangeLensButton."\" />&nbsp;");
	echo "<div id=\"main\">";
	echo "<form role=\"form\" action=\"".$baseURL."index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_lens\" />";
	echo "<input type=\"hidden\" name=\"id\"          value=\"".$lensid."\" />";
	echo "<h4>".stripslashes($objLens->getLensPropertyFromId($lensid,'name'))."</h4>";
	echo "<hr />";
	echo $content; 

	echo "<div class=\"form-group\">
	       <label for=\"lensname\">". LangAddLensField1."</label>";
	echo "<input type=\"text\" required class=\"form-control\" maxlength=\"64\" name=\"lensname\" size=\"30\" value=\"".stripslashes($objUtil->checkRequestKey('lensname','')).stripslashes($objLens->getLensPropertyFromId($objUtil->checkRequestKey('lensid'),'name'))."\" />";
	echo "<span class=\"help-block\">" . LangAddLensField1Expl . "</span>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
	       <label for=\"factor\">". LangAddLensField2."</label>";
	echo "<input type=\"number\" min=\"0.01\" max=\"99.99\" required step=\"0.01\" class=\"form-control\" maxlength=\"5\" name=\"factor\" size=\"5\" value=\"".stripslashes($objUtil->checkRequestKey('factor','')).stripslashes($objLens->getLensPropertyFromId($objUtil->checkRequestKey('lensid'),'factor'))."\" />";
	echo "<span class=\"help-block\">" . LangAddLensField2Expl . "</span>";
	echo "</div>";
	
	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>