<?php // new_filter.php - allows the user to add a new filter
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
else
{
echo "<div id=\"main\">";
$objPresentations->line(array("<h4>".LangOverviewLensTitle." ".$loggedUserName."</h4>"),"L",array(),30);
echo "<hr />"; 
$objLens->showLensesObserver();
$lns=$objLens->getSortedLenses('name');
echo "<form action=\"".$baseURL."index.php\" method=\"post\"><div>";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_lens\" />";
$content1b= "<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
while(list($key, $value) = each($lns))
  $content1b.= "<option value=\"".$baseURL."index.php?indexAction=add_lens&amp;lensid=".urlencode($value)."\" ".(($value==$objUtil->checkRequestKey('lensid'))?" selected=\"selected\" ":'').">".$objLens->getLensPropertyFromId($value,'name')."</option>";
$content1b.= "</select>&nbsp;";
$objPresentations->line(array("<h4>".LangAddLensTitle."&nbsp;<span class=\"requiredField\">".LangRequiredFields."</span>"."</h4>"),"L",array(),30);
echo "<hr />";
$objPresentations->line(array(LangAddLensExisting,
                              $content1b,
                              "<input type=\"submit\" name=\"add\" value=\"".LangAddLensButton."\" />&nbsp;"),
                        "RLR",array(25,40,35),'',array("fieldname"));                              
$objPresentations->line(array(LangAddSiteFieldOr." ".LangAddLensFieldManually),"R",array(25),'',array("fieldname"));

$objPresentations->line(array(LangAddLensField1,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"lensname\" size=\"30\" value=\"".stripslashes($objUtil->checkRequestKey('lensname','')).stripslashes($objLens->getLensPropertyFromId($objUtil->checkRequestKey('lensid'),'name'))."\" />",
                               LangAddLensField1Expl),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddLensField2,
                               "<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"5\" name=\"factor\" size=\"5\" value=\"".stripslashes($objUtil->checkRequestKey('factor','')).stripslashes($objLens->getLensPropertyFromId($objUtil->checkRequestKey('lensid'),'factor'))."\" />",
                               LangAddLensField2Expl),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
echo "<hr />";
echo "</div></form>";
echo "</div>";
}
?>