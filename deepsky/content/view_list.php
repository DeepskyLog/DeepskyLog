<?php
global $loggedUser, $loggedUserName, $objList;

// Show the list that is currently activated
echo "<h2>" . LangActiveList . "</h2>";
if ($_SESSION ['listname'] != '' && $_SESSION ['listname'] != '----------') {
	echo "<a href=\"" . $baseURL . "index.php?indexAction=listaction&amp;activateList=true&amp;listname=" . $_SESSION ['listname'] . "\">";
	echo $_SESSION ['listname'];
	echo "</a>";
	
	echo LangActiveList1;
} else {
	echo LangActiveList2;
}

// First, we show the public lists of the observer.
echo "<h2>" . LangPublicLists . $loggedUserName;

// Add a button to add a new list.
echo " <a class=\"btn btn-success pull-right\" data-toggle=\"modal\" data-target=\"#addList\">
  		 <span class=\"glyphicon glyphicon-plus\"></span>&nbsp;" . LangAddObservingList . "</a>" . "</h2>";

// Show all public lists
$objList->showLists ( true );

// We show the private lists of the observer.
echo "<h2>" . LangPrivateLists . $loggedUserName;

// Add a button to add a new list.
echo " <a class=\"btn btn-success pull-right\" data-toggle=\"modal\" data-target=\"#addList\">
  		 <span class=\"glyphicon glyphicon-plus\"></span>&nbsp;" . LangAddObservingList . "</a>" . "</h2>";

// Show all personal lists
$objList->showLists ( false );
?>