<?php // new_filter.php  allows the user to add a new filter
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
else
{
echo "<div id=\"main\">";
$objPresentations->line(array("<h4>".LangOverviewFilterTitle." ".$loggedUserName."</h4>"),"L",array(),30);
echo "<hr />";
$objFilter->showFiltersObserver();
$filts=$objFilter->getSortedFilters('name', "");
echo "<form action=\"".$baseURL."index.php\" method=\"post\"><div>";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_filter\" />";
$content1b="<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
while(list($key, $value) = each($filts))
  $content1b.= "<option value=\"".$baseURL."index.php?indexAction=add_filter&amp;filterid=".urlencode($value)."\" ".(($value==$objUtil->checkRequestKey('filterid'))?" selected=\"selected\" ":'').">" . $objFilter->getFilterPropertyFromId($value,'name') . "</option>";
$content1b.= "</select>";
$objPresentations->line(array("<h4>".LangAddFilterTitle."&nbsp;<span class=\"requiredField\">".LangRequiredFields."</span>"."</h4>"),"L",array(),30);
echo "<hr />";
$objPresentations->line(array(LangAddFilterExisting,
                              $content1b,
                              "<input type=\"submit\" name=\"add\" value=\"".LangAddFilterButton."\" />&nbsp;"),
                              "RLR",array(25,40,35),'',array("fieldname"));
$objPresentations->line(array(LangAddSiteFieldOr." ".LangAddFilterFieldManually),"R",array(25),'',array("fieldname"));
$objPresentations->line(array(LangAddFilterField1,
                              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"filtername\" size=\"30\" value=\"".stripslashes($objUtil->checkRequestKey('filtername','')).stripslashes($objFilter->getFilterPropertyFromId($objUtil->checkRequestKey('filterid'),'name'))."\" />",
                              LangAddFilterField1Expl),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddFilterField2,
                              $objFilter->getEchoListType($objFilter->getFilterPropertyFromId($objUtil->checkRequestKey('filterid'),'type')),
                              LangAddFilterField2),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation")); 
$objPresentations->line(array(LangAddFilterField3,
                              $objFilter->getEchoListColor($objFilter->getFilterPropertyFromId($objUtil->checkRequestKey('filterid'),'color')),
                              LangAddFilterField3),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation")); 
$objPresentations->line(array(LangAddFilterField4,
                              "<input type=\"text\" class=\"inputfield centered\" maxlength=\"5\" name=\"wratten\" size=\"5\" value=\"".stripslashes($objUtil->checkRequestKey('wratten')).stripslashes($objFilter->getFilterPropertyFromId($objUtil->checkRequestKey('filterid'),'wratten'))."\" />",
                              LangAddFilterField4),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddFilterField5,
                              "<input type=\"text\" class=\"inputfield centered\" maxlength=\"5\" name=\"schott\" size=\"5\" value=\"".stripslashes($objUtil->checkRequestKey('schott')).stripslashes($objFilter->getFilterPropertyFromId($objUtil->checkRequestKey('filterid'),'schott'))."\" />",
                              LangAddFilterField5),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
echo "<hr />";
echo "</div></form>";
echo "</div>";
}
?>
