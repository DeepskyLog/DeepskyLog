<?php
echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
echo "<tr>";
if ($_SESSION['module']=="deepsky")
  echo "<th valign=\"top\">".LangQuickPickTitle."</th>";
echo "</tr>";
echo "<tr>";
echo "<td valign=\"top\" >";
if ($_SESSION['module'] == "deepsky")
{ echo "<form action=\"".$baseURL."index.php\" method=\"get\">";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"quickpick\"></input>";
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"255\" name=\"object\" title=\"".LangQuickPickHelp."\" value=\"".urlencode((array_key_exists('object',$_GET) && ($_GET['object'] != '* '))?$_GET['object']:"")."\" >";
	echo "<input type=\"submit\" name=\"searchObject\" value=\"".LangQuickPickSearchObject."\" style=\"width: 147px\" >";
	echo "<input type=\"submit\" name=\"searchObservations\" value=\"".LangQuickPickSearchObservations."\" style=\"width: 147px\" >";
	if (array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
	  echo "<input type=\"submit\" name=\"newObservation\" value=\"".LangQuickPickNewObservation."\" style=\"width: 147px\" >";
	echo "</form>";
}
echo "</td>";
echo "</tr>";
echo "</table>";
?>