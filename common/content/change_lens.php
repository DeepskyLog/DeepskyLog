<?php
// change_lens.php
// form which allows the administrator to change a lens
echo "<div id=\"main\">";
echo "<h2>";
echo stripslashes($lenses->getLensName($_GET['lens']));
echo "</h2>"; 
echo "<form action=\"".$baseURL."index.php?indexAction=validate_lens\" method=\"post\">";
echo "<table>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangAddLensField1;
echo "</td>";
echo "<td><input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"lensname\" size=\"30\" value=\"".stripslashes($lenses->getLensName($_GET['lens']))."\ /></td>";
echo "<td class=\"explanation\">".LangAddLensField1Expl."</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddLensField2."</td>";
echo "<td><input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"factor\" size=\"5\" value=\"".stripslashes($lenses->getFactor($_GET['lens']))."\ /></td>"; 
echo "<td class=\"explanation\">".LangAddLensField2Expl."</td>";
echo "</tr>";
echo "<tr>";
echo "<td></td>";
echo "<td><input type=\"submit\" name=\"change\" value=\"".LangChangeLensButton."\" /></td>";
echo "<input type=\"hidden\" name=\"id\" value=\"".urlencode($_GET['lens'])."\" />";
echo "<td></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";
?>