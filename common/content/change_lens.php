<?php
// change_lens.php
// form which allows the owner to change a lens

echo "<div id=\"main\">";
echo "<h2>".stripslashes($objLens->getLensName($_GET['lens']))."</h2>"; 
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_lens\" />";
echo "<input type=\"hidden\" name=\"id\"          value=\"".urlencode($_GET['lens'])."\" />";
echo "<table>";
tableFieldnameFieldExplanation(LangAddLensField1,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"lensname\" size=\"30\" value=\"".stripslashes($objLens->getLensName($_GET['lens']))."\" />",
                               LangAddLensField1Expl);
tableFieldnameFieldExplanation(LangAddLensField2,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"5\" name=\"factor\" size=\"5\" value=\"".stripslashes($objLens->getFactor($_GET['lens']))."\" />",
                               LangAddLensField2Expl);
echo "<tr>";
echo "<td><input type=\"submit\" name=\"change\" value=\"".LangChangeLensButton."\" /></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";
?>