<?php

function tableSortHeader($header0, $link0)
{ global $baseURL;
  echo "<td style=\"vertical-align:middle;\">";         
  echo "<table width=\"100%\">";   
  echo "<tr>";          
  echo "<td>";         
  echo "<a href=\"" . $link0 . "&amp;sortdirection=desc\" align=\"center\" title=\"" . LangSortOnDesc . "\"><img src=\"".$baseURL."/styles/images/down10.gif\" border=\"0\"></a>";
  echo "</td>";        
  echo "<td width=\"100%\" style=\"text-align: center\">";           
  echo "<a href=\"" . $link0 . "&amp;sortdirection=asc\"  title=\"" . LangSortOnAsc . "\">".trim($header0)."</a>";;
  echo "</td>";        
  echo "<td>";         
  echo "<a href=\"" . $link0 . "&amp;sortdirection=asc\" title=\"" . LangSortOnAsc . "\"><img src=\"".$baseURL."/styles/images/up10.gif\" border=\"0\"></a>";
  echo "</td>";        
  echo "</tr>";        
  echo "</table>";
  echo "</td>";        
}
function tableSortInverseHeader($header0, $link0)
{ global $baseURL;
  echo "<td style=\"vertical-align:middle;\">";         
  echo "<table width=\"100%\">";   
  echo "<tr>";          
  echo "<td>";         
  echo "<a href=\"" . $link0 . "&amp;sortdirection=asc\"  align=\"center\" title=\"" . LangSortOnAsc . "\"><img src=\"".$baseURL."/styles/images/down10.gif\" border=\"0\"></a>";
  echo "</td>";        
  echo "<td width=\"100%\" style=\"text-align: center\">";           
  echo "<a href=\"" . $link0 . "&amp;sortdirection=desc\"  title=\"" . LangSortOnDesc . "\">".trim($header0)."</a>";;
  echo "</td>";        
  echo "<td>";         
  echo "<a href=\"" . $link0 . "&amp;sortdirection=desc\"  align=\"center\"  title=\"" . LangSortOnDesc . "\"><img src=\"".$baseURL."/styles/images/up10.gif\" border=\"0\"></a>";
  echo "</td>";        
  echo "</tr>";        
  echo "</table>";
  echo "</td>";        
}
function tableMenuItem($link, $menuItem)
{ echo "<tr align=\"center\" height=\"25px\">";
  echo "<td>";
  echo "<a href=\"".$link."\" class=\"mainlevel\">".$menuItem."</a>";
  echo "</td>";
  echo "</tr>";
}
function tableCell($content)
{ echo "<td>".$content."</td>";
}
function tableFormatCell($format,$content)
{ echo "<td ".$format.">".$content."</td>";
}
function tableNextRow()
{ echo "</tr><tr>";
}
function tableNewRow()
{ echo "<tr>";
}
function tableEndRow()
{ echo "</tr>";
}
function tableNew($format='')
{ echo "<table ".$format.">";
}
function tableFieldnameFieldExplanation($name, $field, $explanation)
{ echo "<tr>";
  echo "<td class=\"fieldname\" align=\"right\">";
  echo $name;
  echo "</td>";
  echo "<td>";
  echo $field;
	echo "</td>";
  echo "<td class=\"explanation\">";
  echo $explanation;
  echo "</td>";
  echo "</tr>";
}

?>
