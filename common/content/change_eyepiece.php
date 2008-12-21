<?php
// change_eyepiece.php
// allows the administrator to change eyepiece details 

echo "<div id=\"main\">";
echo "<h2>".stripslashes($objEyepiece->getEyepieceName($_GET['eyepiece']))."</h2>";
echo "<hr>";
echo "<form action=\"".$baseURL."index.php\" method=\"post\" />";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_eyepiece\">";
echo "<input type=\"hidden\" name=\"id\"          value=\"".$_GET['eyepiece']."\" />";
echo "<table>";
tableFieldnameFieldExplanation(LangAddEyepieceField1,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"eyepiecename\" size=\"30\" value=\"".stripslashes($objEyepiece->getEyepieceName($_GET['eyepiece']))."\" />",
                               LangAddEyepieceField1Expl);
tableFieldnameFieldExplanation(LangAddEyepieceField2,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"5\" name=\"focalLength\" size=\"5\" value=\"".stripslashes($objEyepiece->getFocalLength($_GET['eyepiece']))."\" />",
                               LangAddEyepieceField2Expl);
tableFieldnameFieldExplanation(LangAddEyepieceField4,
                               "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"maxFocalLength\" size=\"5\" value=\"".((($mfl=stripslashes($objEyepiece->getMaxFocalLength($_GET['eyepiece']))) < 0)?"":$mfl)."\" />",
                               LangAddEyepieceField4Expl);
tableFieldnameFieldExplanation(LangAddEyepieceField3,
                               "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"apparentFOV\" size=\"5\" value=\"".$objEyepiece->getApparentFOV($_GET['eyepiece'])."\" />",
                               LangAddEyepieceField3Expl);
echo "</table>";
echo "<hr>";
echo "<p><input type=\"submit\" name=\"change\" value=\"".LangAddEyepieceButton2."\" /></p>";
echo "</form>";
echo "</div>";
?>
