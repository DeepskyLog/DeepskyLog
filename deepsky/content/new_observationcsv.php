<?php
// new_observationcsv.php
// add a new observation list via csv to the database - entry page
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception(_("You need to be logged in to change your locations or equipment."));
else
	new_observationcsv ();
function new_observationcsv() {
	global $baseURL, $objPresentations;
	echo "<div id=\"main\">";
	echo "<h4>" . _("Import observations from a CSV file") . "</h4>"; 
	echo "<hr />";
    echo _("This form allows you to submit multiple observations at once by importing them directly from a CSV file (comma separated value file).
This will facilitate and speed up the number of observations you can submit at once.
It also allows you to easily add former observations already kept in some sort of database program. For your interest: only observations with your name (in full) will be inserted.");
	echo "<br /><br />" . _("The CSV file uses the following format:");
    echo "<br /><br /><strong><i>" 
        . _("1-Object; 2-Observer; 3-Date; 4-UT; 5-Location; 6-Instrument; 7-Eyepiece; 8-Filter; 9-Lens; 10-Seeing; 11-LimMag or SQM as you wish; 12-Visibility; 13-Language; 14-Description") 
        . "</i></strong>";
	echo "<br /><br />" . _("The file does not contain a header line,
the first line immediately contains the actual observations in the format mentioned above, e.g.: ");
    echo "<br /><br />" . _("NGC 2392;John Smith;21-01-2005;20:45;Aalst;Obsession 15\";31mm Nagler;Lumicon O-III filter;Televue 2x Barlow;2;4.0;3;en;Nice planetary nebula with a very bright central star!
M 35;John Smith;21-01-2005;20:53;Aalst;Obsession 15\";;;;2;4.0;1;en;About thirty members with several curved chains of stars.");
    echo "<br />...<br /><br />";
    echo _("Seeing should be given as a number between 1 and 5
(1=excellent, 2=good, 3=moderate, 4=poor, 5=bad).");
    echo "<br /> <br />" . _("Visibility should be given as a number between 1 and 7
(1=Very simple, prominent object, 2=Object easily percepted with direct vision, 3=Object perceptable with direct vision, 4=Averted vision required to percept object, 5=Object barely perceptable with averted vision, 6=Perception of object is very questionable, 7=Object definitely not seen).");
    echo "<br /> <br />" . _("If an observation has been done by naked eye, 'Naked Eye' should be given as instrument.");
    echo "<br />" . _("Language should be the short name for the language of the description (en for English)");
    echo "<br /><br />" . _("Caution!");
    echo "<br />" . _("The instruments, the locations, eyepieces, filters and the objects in the CSV file should already be known by DeepskyLog otherwise an error message will be shown and those observations will not be added.");
    echo "<br />" . _("Insert or adapt the missing or wrong data manually until there are no error messages left.");
    echo "<br /> <br />" . _("If everything went well, your observations will be shown in the \"All observations\" overview.");
	echo "<br /><br />" . _("CSV file to import");
	echo "<form action=\"" . $baseURL . "index.php?indexAction=add_csv_observations\" enctype=\"multipart/form-data\" method=\"post\"><div>";
	echo "<input type=\"file\" name=\"csv\" /><br />";
	echo "<input class=\"btn btn-success\" type=\"submit\" name=\"change\" value=\"" . _("Import!") . "\" />";
	echo "<br />";
	echo "<br />";
	echo "</div></form>";
	echo "</div>";
}
?>
