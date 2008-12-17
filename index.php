<?php
// index.php
// main entrance to DeepskyLog

try
{	if(!array_key_exists('indexAction',$_GET)&&array_key_exists('indexAction',$_POST)) 
    $_GET['indexAction']=$_POST['indexAction'];
  include 'common/entryexit/preludes.php';                                          // Includes of all classes and assistance files
  include 'common/entryexit/instructions.php';                                      // Execution of all non-layout related instructions (login, add objects to lists, etc.)
  include 'common/entryexit/data.php';                                              // Get data for the form, object data, observation data, etc.
  include 'common/menu/head.php';                                                   // HTML head
  include 'common/menu/headmenu.php';                                               // Page Title and welcome line - modules choices
  
                                                                                    // Page Center Content 
  echo "<table width=\"100%\" height=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
  
  echo "<tr>";
  echo "<td width=\"153px\" align=\"left\" valign=\"top\" style=\"background-color:#5C7D9D\">";
  include 'common/entryexit/menu.php';                                              // Left Menu Section
  echo "</td>";
  echo "<td style=\"background:url(".$baseURL."vvs/images/lu.gif) no-repeat top left; background-color:#FFFFFF;\">";
  echo "&nbsp;&nbsp;</td>";                                                         // Left white bar
  echo "<td height=\"100%\" valign=\"top\" style=\"background-color:#FFFFFF;\">"; 
  if(isset($entryMessage)&&$entryMessage)                                           // Entry Message if any
    echo "<h3 align=\"center\">".$entryMessage.'</h3><hr />';
  include $objUtil->utiltiesDispatchIndexAction();                                  // Center content section	<<<===============================================================
  echo "</td>";
  echo "<td>&nbsp;&nbsp;</td>";                                                     // Right blue bar
  echo "</tr>"; 
  
  echo "<tr>";                                                                      // Footer
  echo "<td width=\"153px\" style=\"background-color:#5C7D9D; line-height:20px\">&nbsp;</td>";
  echo "<td style=\"background:url(".$baseURL."vvs/images/lo.gif) no-repeat bottom left; background-color:#FFFFFF\">&nbsp;&nbsp;</td>";
  echo "<td style=\"background-color:#FFFFFF;text-align:center\" >";
  echo "<p>Copyright 2004 - 2008&nbsp;";
  echo "<a href=\"http://www.vvs.be\">Vereniging voor Sterrenkunde</a> - Powered by&nbsp;";
  echo "<a href=\"http://www.deepskylog.org\">DeepskyLog</a>&nbsp;".$versionInfo;   //defined in databaseInfo.php
  echo "</td>";
  echo "</tr>";
  
  echo "</table>";
}
catch (Exception $e)
{ echo 'Report problem: ' . $e->getMessage();
}

?>
