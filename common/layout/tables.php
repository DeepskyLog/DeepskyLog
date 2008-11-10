<?php

function tableSortHeader($header0, $link0)
{
global $baseURL;
echo "<td valign=\"top\">";         
echo "<table width=\"100%\">";   
echo "<tr valign=\top\">";          
echo "<td>";         
echo "<a href=\"" . $link0 . "&amp;sortdirection=desc\"  title=\"" . LangSortOnDesc . "\"><img src=\"".$baseURL."/styles/images/down10.gif\" border=\"0\"></a>";
echo "</td>";        
echo "</tr>";        
echo "<tr>";		     
echo "<td width=\"100%\" align=\"center\">";           
echo "<a href=\"" . $link0 . "&amp;sortdirection=asc\"  title=\"" . LangSortOnAsc . "\">".trim($header0)."</a>";;
echo "</td>";        
echo "</tr>";        
echo "</table>";
echo "</td>";        
}
function tableSortInverseHeader($header0, $link0)
{
global $baseURL;
echo "<td valign=\"top\">";         
echo "<table width=\"100%\">";   
echo "<tr valign=\top\">";          
echo "<td>";         
echo "<a href=\"" . $link0 . "&amp;sortdirection=asc\"  title=\"" . LangSortOnAsc . "\"><img src=\"".$baseURL."/styles/images/down10.gif\" border=\"0\"></a>";
echo "</td>";        
echo "</tr>";        
echo "<tr>";		     
echo "<td width=\"100%\" align=\"center\">";           
echo "<a href=\"" . $link0 . "&amp;sortdirection=desc\"  title=\"" . LangSortOnDesc . "\">".trim($header0)."</a>";;
echo "</td>";        
echo "</tr>";        
echo "</table>";
echo "</td>";        
}
function tableMenuItem($link, $menuItem)
{
echo "<tr align=\"center\" height=\"25px\">";
echo "<td>";
echo "<a href=\"".$link."\" class=\"mainlevel\">".$menuItem."</a>";
echo "</td>";
echo "</tr>";
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
echo "<a href=\"" . $link0 . "&amp;sortdirection=asc\"  title=\"" . LangSortOnAsc . "\"><img src=\"".$baseURL."/styles/images/up10.gif\"></a>";
echo "</td>";        
echo "</tr>";        
echo "<tr height=\"10px\">";         
echo "<td width=\10px\" height=\"10px\">";         
echo "<a href=\"" . $link0 . "&amp;sortdirection=desc\"  title=\"" . LangSortOnDesc . "\"><img src=\"".$baseURL."/styles/images/down10.gif\"></a>";
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
