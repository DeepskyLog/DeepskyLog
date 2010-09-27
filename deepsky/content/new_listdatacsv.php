<?php // new_listdatacsv.php - add new list data viacsv file
echo "<div id=\"main\">";
$objPresentations->line(array("<h4>".LangCSVListTitle."</h4>"),"L",array(),30);
echo "<hr />";
if($objList->checkList($_SESSION['listname'])==2)
{ $objPresentations->line(array(LangCSVListMessage1),"L");
  $objPresentations->line(array( LangCSVListMessage2),"L");
  $objPresentations->line(array(LangCSVListMessage3),"L");
  $objPresentations->line(array(LangCSVListMessage7),"L");
  $objPresentations->line(array(LangCSVListMessage5),"L");
  $objPresentations->line(array(LangCSVListMessage5a),"L");
  $objPresentations->line(array(LangCSVListMessage5b),"L");
  $objPresentations->line(array(LangCSVListMessage5c),"L");
  echo "<hr />";
  $objPresentations->line(array(LangCSVListMessage6.":"),"L",array(100),50);
  echo "<form action=\"".$baseURL."index.php?indexAction=add_csv_listdata\" enctype=\"multipart/form-data\" method=\"post\">";
  $objPresentations->line(array("<input type=\"file\" name=\"csv\" />"),"L",array(100),40); 
  $objPresentations->line(array("<input type=\"submit\" name=\"change\" value=\"".LangCSVListButton."\" />"),"L");
	echo "</form>";
}
else 
  throw new Exception("List is not yours to edit");
echo "</div>";
?>
