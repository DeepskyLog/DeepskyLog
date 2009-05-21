<?php // new_filter.php  allows the user to add a new filter
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
else
{
echo "<div id=\"main\">";

$objFilter->showFiltersObserver();
$filts=$objFilter->getSortedFilters('name', "");
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_filter\" />";
$content1b="<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
while(list($key, $value) = each($filts))
  $content1b.= "<option value=\"".$baseURL."index.php?indexAction=add_filter&amp;filterid=".urlencode($value)."\" ".(($value==$objUtil->checkGetKey('filterid'))?' selected=\"selected\" ':'').">" . $objFilter->getFilterPropertyFromId($value,'name') . "</option>";
$content1b.= "</select>";
$objPresentations->line(array("<h5>".LangAddFilterTitle."</h5>"),"L",array(),50);
echo "<hr />";
$objPresentations->line(array(LangAddFilterExisting,
                              $content1b,
                              "<input type=\"submit\" name=\"add\" value=\"".LangAddFilterButton."\" />&nbsp;"),
                              "RLR",array(25,40,35),'',array("fieldname"));
$objPresentations->line(array(LangAddSiteFieldOr." ".LangAddFilterFieldManually),"R",array(25),'',array("fieldname"));
$objPresentations->line(array(LangAddFilterField1,
                              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"filtername\" size=\"30\" value=\"".stripslashes($objUtil->checkGetKey('filtername','')).stripslashes($objFilter->getFilterPropertyFromId($objUtil->checkGetKey('filterid'),'name'))."\">",
                              LangAddFilterField1Expl),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddFilterField2,
                              $objFilter->getEchoListType($objFilter->getFilterPropertyFromId($objUtil->checkGetKey('filterid'),'type')),
                              LangAddFilterField2),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation")); 
$objPresentations->line(array(LangAddFilterField3,
                              $objFilter->getEchoListColor($objFilter->getFilterPropertyFromId($objUtil->checkGetKey('filterid'),'color')),
                              LangAddFilterField3),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation")); 
$objPresentations->line(array(LangAddFilterField4,
                              "<input type=\"text\" class=\"inputfield centered\" maxlength=\"5\" name=\"wratten\" size=\"5\" value=\"".stripslashes($objUtil->checkGetKey('wratten')).stripslashes($objFilter->getFilterPropertyFromId($objUtil->checkGetKey('filterid'),'wratten'))."\" />",
                              LangAddFilterField4),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddFilterField5,
                              "<input type=\"text\" class=\"inputfield centered\" maxlength=\"5\" name=\"schott\" size=\"5\" value=\"".stripslashes($objUtil->checkGetKey('schott')).stripslashes($objFilter->getFilterPropertyFromId($objUtil->checkGetKey('filterid'),'schott'))."\" />",
                              LangAddFilterField5),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
echo "<hr />";
echo "</form>";
echo "</div>";
}
?>
