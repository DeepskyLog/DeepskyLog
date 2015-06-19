<?php
  global $loggedUser, $loggedUserName, $objList;
  
  // First, we show the public lists of the observer.
  echo "<h2>" . LangPublicLists . $loggedUserName;
  
  // Add a button to add a new list.
  echo " <a class=\"btn btn-success pull-right\" data-toggle=\"modal\" data-target=\"#addList\">
  		 <span class=\"glyphicon glyphicon-plus\"></span>&nbsp;" . LangAddObservingList . "</a>" . "</h2>";
  
  // Show all public lists
  $objList->showLists(true);

  // TODO: Show button to move from public to private list.

  // We show the private lists of the observer.
  echo "<h2>" . LangPrivateLists . $loggedUserName;
  
  // Add a button to add a new list.
  echo " <a class=\"btn btn-success pull-right\" data-toggle=\"modal\" data-target=\"#addList\">
  		 <span class=\"glyphicon glyphicon-plus\"></span>&nbsp;" . LangAddObservingList . "</a>" . "</h2>";

  // Show all personal lists
  $objList->showLists(false);
  
  // TODO: Show button to move from private to public list.
?>