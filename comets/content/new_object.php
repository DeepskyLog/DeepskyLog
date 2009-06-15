<?php // new_object.php - allows the user to add a comet to the database 
echo "<div id=\"main\">";
echo "<form action=\"".$baseURL."index.php?indexAction=comets_validate_object\" method=\"post\">";
$content="<input type=\"submit\" name=\"newobject\" value=\"" . LangNewObjectButton1 . "\" />";
$objPresentations->line(array("<h4>".LangNewObjectTitle."</h4>",$content),"LR",array(60,40),30);
echo "<hr />";
$content="<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"40\" name=\"name\" size=\"40\" value=\"\" />";
$objPresentations->line(array(LangViewObjectField1."&nbsp;*",$content),"RL",array(20,80),30,array("fieldname"));
$content="<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"40\" name=\"icqname\" size=\"40\" value=\"\" />";
$objPresentations->line(array(LangNewObjectIcqname."&nbsp;*",$content),"RL",array(20,80),30,array("fieldname"));
echo "<hr />";
echo "</form>";
echo "</div>";
?>
