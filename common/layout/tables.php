<?php
// function tableFieldnameFieldExplanation($name,$field,$explanation)                   // 3-item field line, containing the name of the field, the field value and the explanation
// function tableFieldnameField($name,$field)                                           // 2-item field line, containing the name of the field and the field value
// function tableFieldnameField2($name1,$field1,$name2,$field2)             // 4-item filed line, containing the line type, the 2 names and values of the dields, each 25% wide
// function tableFieldnameField3($name1,$field1,$name2,$field2,$name3,$field3, $type="")
// function tableMenuItem($link, $menuItem)                                             // Item for the left colums menus
// function tablePageTitle($title, $link, &$list, &$min, &$max)                         // TITLE in h2 and List Header 
// function tableSortHeader($header0, $link0)                                           // sorting header on table
// function tableSortInverseHeader($header0, $link0)                                    // inverse sorting header on table
// function tableTypeFieldnameField($type,$name,$field)                                 // 2-item type line, containing the name and the field, and formatted to the type


function tableFieldnameFieldExplanation($name,$field,$explanation, $type="")
{ echo "<tr ".$type.">";
  echo "<td class=\"fieldname\">".$name."</td>";
  echo "<td class=\"fieldvalue\">".$field."</td>";
  echo "<td class=\"fieldexplanation\">".$explanation."</td>";
  echo "</tr>";
}
function tableFieldnameField($name,$field, $type="")
{ echo "<tr ".$type.">";
  echo "<td class=\"fieldname\">".$name."</td>";
  echo "<td class=\"fieldvalue\">".$field."</td>";
  echo "</tr>";
}
function tableFieldnameField2($name1,$field1,$name2,$field2, $type="")
{ echo "<tr ".$type.">";
  echo "<td class=\"fieldname\" align=\"right\" width=\"25%\">".$name1."</td>";                                                                                                         // ALTERNATIVE NAME
  echo "<td width=\"25%\">".$field1."</td>";
  echo "<td class=\"fieldname\" align=\"right\" width=\"25%\">".$name2."</td>";
  echo "<td width=\"25%\">".$field2."</td>";
  echo "</tr>";
}
function tableFieldnameField3($name1,$field1,$name2,$field2,$name3,$field3, $type="")
{ echo "<tr ".$type.">";
  echo "<td class=\"fieldname\" align=\"right\" width=\"16%\">".$name1."</td>";                                                                                                         // ALTERNATIVE NAME
  echo "<td width=\"17%\">".$field1."</td>";
  echo "<td class=\"fieldname\" align=\"right\" width=\"16%\">".$name2."</td>";
  echo "<td width=\"17%\">".$field2."</td>";
  echo "<td class=\"fieldname\" align=\"right\" width=\"17%\">".$name3."</td>";
  echo "<td width=\"17%\">".$field3."</td>";
  echo "</tr>";
}
function tableMenuItem($link, $menuItem)
{ echo "<tr align=\"center\" height=\"25px\">";
  echo "<td>";
  echo "<a href=\"".$link."\" class=\"mainlevel\">".$menuItem."</a>";
  echo "</td>";
  echo "</tr>";
}
function tablePageTitle($title, $link, &$list, &$min, &$max)
{ global $objUtil;
  echo "<table width=\"100%\">";
	echo "<tr>";
	echo "<td>";
	echo "<h2>";
  echo $title;
	echo "</h2>";
	echo "</td>";
	echo "<td align=\"right\">";
  list($min,$max)=$objUtil->printNewListHeader($list,$link,$min,25,'');	
	echo "</td>";
	echo "</table>";
}
function tableSortHeader($header0, $link0)
{ global $baseURL;
  echo "<td style=\"vertical-align:top;\">";         
  echo "<table width=\"100%\">";   
  echo "<tr>";          
  echo "<td>";         
  echo "<a href=\"".$link0."&amp;sortdirection=asc\"  title=\"".LangSortOnAsc. "\"><img src=\"".$baseURL."styles/images/up10.gif\" border=\"0\"></a>";
  echo "</td>";        
  echo "<td align=\"right\">";         
  echo "<a href=\"".$link0."&amp;sortdirection=desc\" title=\"".LangSortOnDesc."\"><img src=\"".$baseURL."styles/images/down10.gif\" border=\"0\"></a>";
  echo "</td>";        
  echo "</tr>";        
  echo "<tr>";
	echo "<td colspan=\"2\" style=\"text-align: center\">";           
  echo "<a href=\"".$link0."&amp;sortdirection=asc\"  title=\"".LangSortOnAsc."\">".trim($header0)."</a>";;
  echo "</td>";        
  echo "<tr>";
  echo "</table>";
  echo "</td>";        
}
function tableSortInverseHeader($header0, $link0)
{ global $baseURL;
  echo "<td style=\"vertical-align:top;\">";         
  echo "<table width=\"100%\">";   
  echo "<tr>";          
  echo "<td>";         
  echo "<a href=\"".$link0."&amp;sortdirection=desc\" title=\"".LangSortOnDesc."\"><img src=\"".$baseURL."styles/images/up10.gif\" border=\"0\"></a>";
  echo "</td>";        
  echo "<td align=\"right\">";         
  echo "<a href=\"".$link0."&amp;sortdirection=asc\" title=\"".LangSortOnAsc."\"><img src=\"".$baseURL."styles/images/down10.gif\" border=\"0\"></a>";
  echo "</td>";        
  echo "</tr>";        
  echo "<tr >";
  echo "<td colspan=\"2\" style=\"text-align: center\">";           
  echo "<a href=\"".$link0."&amp;sortdirection=desc\" title=\"".LangSortOnDesc."\">".trim($header0)."</a>";;
  echo "</td>";        
  echo "</tr>";
  echo "</table>";
  echo "</td>";        
}
function tableTypeFieldnameField($type,$name,$field)
{ echo "<tr class=\"".$type."\">";
  echo "<td class=\"fieldname\">".$name."</td>";
  echo "<td class=\"fieldvalue\">".$field."</td>";
  echo "</tr>";
}
?>
