<?php
// change_eyepiece.php
// allows the administrator to change eyepiece details 

echo "<div id=\"main\">";
echo "<h2>".stripslashes($objEyepiece->getEyepieceName($_GET['eyepiece']))."</h2>";
echo "<form action=\"".$baseURL."index.php\" method=\"post\" />";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_eyepiece\">";
echo "<table>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddEyepieceField1."</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"eyepiecename\" size=\"30\" value=\"".stripslashes($objEyepiece->getEyepieceName($_GET['eyepiece']))."\" />";
echo "</td>";
echo "<td class=\"explanation\">" . LangAddEyepieceField1Expl . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddEyepieceField2."</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"focalLength\" size=\"5\" value=\"".stripslashes($objEyepiece->getFocalLength($_GET['eyepiece']))."\" />";
echo "</td>";
echo "<td class=\"explanation\">".LangAddEyepieceField2Expl."</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddEyepieceField4."</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"maxFocalLength\" size=\"5\" value=\"".((($mfl=stripslashes($objEyepiece->getMaxFocalLength($_GET['eyepiece']))) < 0)?"":$mfl)."\" />";
echo "</td>";
echo "<td class=\"explanation\">".LangAddEyepieceField4Expl."</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddEyepieceField3."</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"apparentFOV\" size=\"5\" value=\"".$objEyepiece->getApparentFOV($_GET['eyepiece'])."\" />";
echo "</td>";
echo "<td class=\"explanation\">".LangAddEyepieceField3Expl."</td>";
echo "</tr>";
echo "<tr>";
echo "<td></td>";
echo "<td>";
echo "<input type=\"submit\" name=\"change\" value=\"".LangAddEyepieceButton2."\" />";
echo "<input type=\"hidden\" name=\"id\" value=\"".$_GET['eyepiece']."\" />";
echo "</td>";
echo "<td></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";
?>
