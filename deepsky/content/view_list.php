<?php
global $loggedUser, $loggedUserName, $objList;

// Show the list that is currently activated
echo "<h2>" . _("Active list") . "</h2>";
if ($_SESSION ['listname'] != '' && $_SESSION ['listname'] != '----------') {
	echo "<a href=\"" . $baseURL . "index.php?indexAction=listaction&amp;activateList=true&amp;listname=" . $_SESSION ['listname'] . "\">";
	echo $_SESSION ['listname'];
	echo "</a>";
	
	echo _(" is your active list. You can search for objects and add these objects to this list.");
} else {
	echo _("There is no active list. Select one of the lists to make that list active. You can search for objects and add these objects to your active lists.");
}

// First, we show the public lists of the observer.
echo "<h2>" . sprintf(_("Public lists of %s"), $loggedUserName);

// Add a button to add a new list.
echo " <a class=\"btn btn-success pull-right\" data-toggle=\"modal\" data-target=\"#addList\">
  		 <span class=\"glyphicon glyphicon-plus\"></span>&nbsp;" . _("Create a new observing list") . "</a>" . "</h2>";

// Show all public lists
$objList->showLists ( true );

// We show the private lists of the observer.
echo "<h2>" . sprintf(_("Private lists of %s"), $loggedUserName);

// Add a button to add a new list.
echo " <a class=\"btn btn-success pull-right\" data-toggle=\"modal\" data-target=\"#addList\">
  		 <span class=\"glyphicon glyphicon-plus\"></span>&nbsp;" . _("Create a new observing list") . "</a>" . "</h2>";

// Show all personal lists
$objList->showLists ( false );
?>