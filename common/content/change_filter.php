<?php
// change_filter.php
// allows the filter owner or an admin to change a filter
// or another user to view the filter details

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($filterid=$objUtil->checkGetKey('filter'))) throw new Exception(LangException005b);
elseif(!($objFilter->getFilterPropertyFromId($filterid,'name'))) throw new Exception("Filter not found in change_filter.php, please contact the developers with this message:".$filterid);
else change_filter();

function change_filter()
{ global $baseURL,$filterid,$loggedUser,
         $objFilter,$objPresentations,$objUtil;
  $disabled=" disabled=\"disabled\"";
	if(($loggedUser) &&
	   ($objUtil->checkAdminOrUserID($objFilter->getFilterPropertyFromId($filterid,'observer',''))))
	  $disabled="";
	$content=($disabled?"":"<input type=\"submit\" class=\"btn btn-primary pull-right\" name=\"change\" value=\"".LangChangeFilterButton."\" />&nbsp;");
	$filter=$objFilter->getFilterPropertiesFromId($filterid);
	echo "<div id=\"main\">";
	echo "<form role=\"form\" action=\"".$baseURL."index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_filter\" />";
	echo "<input type=\"hidden\" name=\"id\"          value=\"".$filterid."\" />";
	echo "<h4>".stripslashes($filter['name'])."</h4>";
	echo "<hr />";
	echo $content;

	echo "<div class=\"form-group\">
	       <label for=\"filtername\">". LangAddFilterField1."</label>";
	echo "<input type=\"text\" required class=\"form-control\" maxlength=\"64\" name=\"filtername\" size=\"30\" value=\"".stripslashes($filter['name'])."\" ".$disabled." />";
	echo "<span class=\"help-block\">" . LangAddFilterField1Expl . "</span>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
	       <label for=\"type\">". LangAddFilterField2."</label>";
	echo $objFilter->getEchoListType($filter['type'],$disabled);
	echo "</div>";
	
	echo "<div class=\"form-group\">
	       <label for=\"color\">". LangAddFilterField3."</label>";
	echo $objFilter->getEchoListColor($filter['color'],$disabled);
	echo "</div>";
	
	echo "<div class=\"form-group\">
	       <label for=\"wratten\">". LangAddFilterField4."</label>";
	echo "<input type=\"text\" class=\"inputfield form-control\" maxlength=\"5\" name=\"wratten\" size=\"5\" value=\"".stripslashes($filter['wratten'])."\" ".$disabled." />";
	echo "</div>";
	
	echo "<div class=\"form-group\">
	       <label for=\"schott\">". LangAddFilterField5."</label>";
	echo "<input type=\"text\" class=\"inputfield form-control\" maxlength=\"5\" name=\"schott\" size=\"5\" value=\"".stripslashes($filter['schott'])."\" ".$disabled." />";
	echo "</div>";
	
	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
