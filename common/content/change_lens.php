<?php // change_lens.php - form which allows the owner to change a lens
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
elseif(!($lensid=$objUtil->checkGetKey('lens'))) throw new Exception(LangException009);
elseif(!($objLens->checkUserID($objLens->getLensPropertyFromId($lensid,'observer','')))) throw new Exception(LangExcpetion010);
else
{
echo "<div id=\"main\">";
echo "<h2>".stripslashes($objLens->getLensPropertyFromId($lensid,'name'))."</h2>"; 
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_lens\" />";
echo "<input type=\"hidden\" name=\"id\"          value=\"".urlencode($lensid)."\" />";
echo "<table>";
tableFieldnameFieldExplanation(LangAddLensField1,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"lensname\" size=\"30\" value=\"".stripslashes($objLens->getLensPropertyFromId($lensid,'name'))."\" />",
                               LangAddLensField1Expl);
tableFieldnameFieldExplanation(LangAddLensField2,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"5\" name=\"factor\" size=\"5\" value=\"".stripslashes($objLens->getLensPropertyFromId($lensid,'factor'))."\" />",
                               LangAddLensField2Expl);
echo "<tr>";
echo "<td><input type=\"submit\" name=\"change\" value=\"".LangChangeLensButton."\" /></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";
}
?>