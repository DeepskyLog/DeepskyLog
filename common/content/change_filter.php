<?php // change_filter.php - form which allows the filter owner to change a filter
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($filterid=$objUtil->checkGetKey('filter'))) throw new Exception(LangException005b);
else
{ $disabled=" disabled=\"disabled\"";
	if(($loggedUser) &&
	   ($objUtil->checkAdminOrUserID($objFilter->getFilterPropertyFromId($filterid,'observer',''))))
	  $disabled="";
	$content=($disabled?"":"<input type=\"submit\" name=\"change\" value=\"".LangChangeFilterButton."\" />&nbsp;");
	$filter=$objFilter->getFilterPropertiesFromId($filterid);
	echo "<div id=\"main\">";
	echo "<form action=\"".$baseURL."index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_filter\" />";
	echo "<input type=\"hidden\" name=\"id\"          value=\"".$filterid."\" />";
	$objPresentations->line(array("<h4>".stripslashes($filter['name'])."</h4>",$content),"LR",array(80,20),30);
	echo "<hr />";
	$line[]=array(LangAddFilterField1,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"filtername\" size=\"30\" value=\"".stripslashes($filter['name'])."\" ".$disabled." />",LangAddFilterField1Expl);
	$line[]=array(LangAddFilterField2,$objFilter->getEchoListType($filter['type'],$disabled));
	$line[]=array(LangAddFilterField3,$objFilter->getEchoListColor($filter['color'],$disabled));
	$line[]=array(LangAddFilterField4,"<input type=\"text\" class=\"inputfield centered\" maxlength=\"5\" name=\"wratten\" size=\"5\" value=\"".stripslashes($filter['wratten'])."\" ".$disabled." />");
	$line[]=array(LangAddFilterField5,"<input type=\"text\" class=\"inputfield centered\" maxlength=\"5\" name=\"schott\" size=\"5\" value=\"".stripslashes($filter['schott'])."\" ".$disabled." />");
	for($i=0;$i<count($line);$i++)
	  $objPresentations->line($line[$i],"RLL",array(20,40,40),'',array("fieldname","fieldvalue","fieldexplanation"));
	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
