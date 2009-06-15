<?php // setup_objects_query.php - interface to query comets
$_SESSION['result'] = "";
echo "<div id=\"main\">";
echo "<form action=\"".$baseURL."index.php\" method=\"get\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"comets_result_query_objects\" />";
$objPresentations->line(array("<h4>".LangQueryObjectsTitle."</h4>","<input type=\"submit\" name=\"query\" value=\"" . LangQueryObjectsButton1 . "\" />"),"LR",array(70,30),30);
echo "<hr />";
// OBJECT NAME 
$content="<input type=\"text\" class=\"inputfield\" maxlength=\"40\" name=\"name\" size=\"40\" value=\"\" />";
$objPresentations->line(array(LangQueryObjectsField1,$content),"RL",array(20,80),30);
$content="<input type=\"text\" class=\"inputfield\" maxlength=\"40\" name=\"icqname\" size=\"40\" value=\"\" />";
$objPresentations->line(array(LangNewObjectIcqname,$content),"RL",array(20,80),30);
echo "<hr />";
echo "</form>";
echo "</div>";
?>
