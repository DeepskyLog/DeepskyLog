<?php
echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";

echo "<tr>";
//echo "<th valign=\"top\">"."&nbsp;"."</th>";
echo "</tr>";

echo "<form action=\"".$baseURL."index.php\" method=\"get\">";
echo "<tr>";
echo "<td>";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"quickpick\"></input>";
echo "<input type=\"hidden\" name=\"source\" value=\"quickpick\"></input>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>";
echo "<input type=\"text\" style=\"width: 143px\" class=\"inputfield\" maxlength=\"255\" name=\"object\" title=\"".LangQuickPickHelp."\" value=\"".((array_key_exists('object',$_GET) && ($_GET['object'] != '* '))?$_GET['object']:"")."\" >";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>";
echo "<input type=\"submit\" name=\"searchObject\" value=\"".LangQuickPickSearchObject."\" style=\"width: 147px\" accesskey=\"O\">";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>";
echo "<input type=\"submit\" name=\"searchObservations\" value=\"".LangQuickPickSearchObservations."\" style=\"width: 147px\" accesskey=\"v\">";
echo "</td>";
echo "</tr>";
if (array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
{	echo "<tr>";
  echo "<td>";
	echo "<input type=\"submit\" name=\"newObservation\" value=\"".LangQuickPickNewObservation."\" style=\"width: 147px\"  accesskey=\"N\">";
	echo "</td>";
  echo "</tr>";
}
echo "</form>";
echo "</table>";
?>