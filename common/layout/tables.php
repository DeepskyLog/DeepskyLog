<?php

function tableSortHeader($header0, $link0)
{
echo "<td valign=\"top\">";         
echo "<table width=\"100%\">";   
echo "<tr valign=\top\">";          
echo "<td>";         
echo "<a href=\"" . $link0 . "&amp;sortdirection=asc\"  title=\"" . LangSortOnAsc . "\"><img src=\"../styles/images/up10.gif\" border=\"0\"></a>";
echo "<a href=\"" . $link0 . "&amp;sortdirection=desc\"  title=\"" . LangSortOnDesc . "\"><img src=\"../styles/images/down10.gif\" border=\"0\"></a>";
echo "</td>";        
echo "</tr>";        
echo "<tr>";		     
echo "<td width=\"100%\" align=\"center\">";           
echo trim($header0);
echo "</td>";        
echo "</tr>";        
echo "</table>";
echo "</td>";        
}


// Older designs
function tableSortHeader1($header0, $link0)
{
echo "<td>";         
echo "<table>";      
echo "<tr>";         
echo "<td>";          
echo "<table>";
echo "<tr height=\"10px\">";          
echo "<td width=\10px\" height=\"10px\">";         
echo "<a href=\"" . $link0 . "&amp;sortdirection=asc\"  title=\"" . LangSortOnAsc . "\"><img src=\"../styles/images/up10.gif\"></a>";
echo "</td>";        
echo "</tr>";        
echo "<tr height=\"10px\">";         
echo "<td width=\10px\" height=\"10px\">";         
echo "<a href=\"" . $link0 . "&amp;sortdirection=desc\"  title=\"" . LangSortOnDesc . "\"><img src=\"../styles/images/down10.gif\"></a>";
echo "</td>";        
echo "</tr>";        
echo "</table>";
echo "</td>";		     
echo "<td>";           
echo $header0;
echo "</td>";        
echo "</tr>";        
echo "</table>";     
echo "</td>";        
}

?>
