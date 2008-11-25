<?php
// new_listdatacsv.php
// add new list data viacsv file

echo "<div id=\"main\">";
echo "<h2>";
echo LangCSVListTitle;
echo "</h2>";
echo "<p>";

if($list->checkList($_SESSION['listname'])==2)
{ echo LangCSVListMessage1;
  echo "<br /><br />" . LangCSVListMessage2;
  echo "<br /><br />" . LangCSVListMessage3;
  echo "<br /><br />" . LangCSVListMessage7;
  echo "<br /><br />" . LangCSVListMessage5;
  echo "<br /><br />" . LangCSVListMessage6;
  echo "<form action=\""$baseURL."deepsky/index.php?indexAction=add_csv_listdata\" enctype=\"multipart/form-data\" method=\"post\">";
  echo "<input type=\"file\" name=\"csv\"><br />"; 
  echo "<input type=\"submit\" name=\"change\" value=\"".LangCSVListButton."\" />";
	echo "</form>";
}
else 
  throw new Exception("List is not yours to edit");
?>
