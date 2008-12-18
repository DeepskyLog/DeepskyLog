<?php
// head.php
// prints the html headers

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
echo "<html xmlns=\"http://www.w3.org/1999/xhtml\">";
echo "<head>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />";
echo "<meta name=\"revisit-after\" content=\"1 day\">";
echo "<META NAME=\"copyright\" CONTENT=\"Copyright &copy; 2005-2009 VVS. Alle Rechten Voorbehouden.\">";
echo "<META NAME=\"author\" CONTENT=\"DeepskyLog - VVS\">";
echo "<title>DeepskyLog ". $GLOBALS['objUtil']->checkGetKey('indexAction','')."</title>";  // 20081209 Here should come a better solution, see bug report 44
echo "<meta name=\"description\" content=\"Vereniging voor sterrenkunde\" />";
echo "<meta name=\"keywords\" content=\"VVS, Vereniging Voor Sterrenkunde, astronomie, sterrenkunde, JVS, Heelal, Astra, Hemelkalender, Sterrenkijkdag, Sterrenkijkdagen, sterr, Nieuws, Laatste nieuws\" />";
echo "<meta name=\"robots\" content=\"index, follow\" />";
echo "<base href=\"" . $baseURL . "\" />";
echo "<link rel=\"shortcut icon\" href=\"".$baseURL."/styles/images/favicon.ico\" />";
echo "<link href=\"".$baseURL."styles/style.css\" rel=\"stylesheet\" type=\"text/css\" />";
echo "</head>";

?>
