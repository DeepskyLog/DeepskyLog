<?php // change_filter.php - form which allows the filter owner to change a filter
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
elseif(!($filterid=$objUtil->checkGetKey('filter'))) throw new Exception(LangException005);
elseif(!($objUtil->checkUserID($objFilter->getFilterPropertyFromId($filterid,'observer','')))) throw new Exception(LangExcpetion006);
//elseif(!($objFilter->getFilterPropertyFromId($filterid,'name')))  throw new Exception("Filter not found in change_filter.php, please contact the developers with this message:".$filterid);
else
{
$filter=$objFilter->getFilterPropertiesFromId($filterid);
echo "<div id=\"main\">";
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_filter\" />";
echo "<input type=\"hidden\" name=\"id\"          value=\"".$filterid."\" />";
$objPresentations->line(array("<h4>".stripslashes($filter['name'])."</h4>","<input type=\"submit\" name=\"change\" value=\"".LangChangeFilterButton."\" />&nbsp;"),"LR",array(80,20),30);
echo "<hr />";
$line[]=array(LangAddFilterField1,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"filtername\" size=\"30\" value=\"".stripslashes($filter['name'])."\" />",LangAddFilterField1Expl);
$line[]=array(LangAddFilterField2,$objFilter->getEchoListType($filter['type']));
$line[]=array(LangAddFilterField3,$objFilter->getEchoListColor($filter['color']));
$line[]=array(LangAddFilterField4,"<input type=\"text\" class=\"inputfield centered\" maxlength=\"5\" name=\"wratten\" size=\"5\" value=\"".stripslashes($filter['wratten'])."\" />");
$line[]=array(LangAddFilterField5,"<input type=\"text\" class=\"inputfield centered\" maxlength=\"5\" name=\"schott\" size=\"5\" value=\"".stripslashes($filter['schott'])."\" />");
for($i=0;$i<count($line);$i++)
  $objPresentations->line($line[$i],"RLL",array(20,40,40),'',array("fieldname","fieldvalue","fieldexplanation"));
echo "<hr />";
echo "</form>";
echo "</div>";
}
?>
