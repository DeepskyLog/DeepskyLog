<?php // index.php - main entrance to DeepskyLog
try
{ $inIndex=true;
  if(!array_key_exists('indexAction',$_GET)&&array_key_exists('indexAction',$_POST)) 
    $_GET['indexAction']=$_POST['indexAction'];
  include 'common/entryexit/preludes.php';                                          // Includes of all classes and assistance files
  include 'common/entryexit/instructions.php';                                      // Execution of all non-layout related instructions (login, add objects to lists, etc.)
  include 'common/layout/presentation.php';
  include 'common/menu/head.php';                                                   // HTML head
  include 'common/menu/headmenu.php';                                               // Page Title and welcome line - modules choices
  // Page Center Content 
  echo "<div id=\"maintable\" style=\"static;width:100%;height:100%;border:0px;padding:0px\">";  
  echo "<div id=\"leftsection\" style=\"position:absolute;left:0px;width:173px;>";
  echo "<div id=\"leftmenus\" style=\"position:absolute;left:0px;width:153px;background-color:#5C7D9D\">";
  include 'common/entryexit/menu.php';                                              // Left Menu Section
  echo "</div>";
  echo "<div id=\"leftbodycorners\" style=\"float:left;width:20px;height:100%;text-align-vertical:bottom;background:url(".$baseURL."styles/images/lu.gif) no-repeat top left; background-color:#FFFFFF;\">";
  echo "<img src=\"".$baseURL."styles/images/lo.gif\">";
  echo "</div>";                       // Left white bar
  echo "</div>";
  echo "<div id=\"rightsection\" style=\"float:right\">";
  echo "<div id=\"maincell\" style=\"float:left;height:100%;background-color:#FFFFFF;\">"; 
  $includeFile=$objUtil->utilitiesDispatchIndexAction();                             // Determine the page to show
  include 'common/entryexit/data.php';                                              // Get data for the form, object data, observation data, etc.
  include $includeFile;                                                             // Center content section	<<<===============================================================
  echo "</div>";
  echo "<div id=\"rightbodycorners\" style=\"float:right;height:100%;width:20px;vertical-align:bottom;background:url(".$baseURL."styles/images/ru.gif) no-repeat top right; background-color:#FFFFFF;\">";
  echo "<img src=\"".$baseURL."styles/images/ro.gif\"></td>";                       // Right white bar
  echo "&nbsp;&nbsp;";                                                     // Right blue bar
  echo "</div>";  
  echo "</div>";	
  echo "</div>";
  echo "<div id=\"footer\" style=\"position:absolute;bottom:0px;height:30px;text-align:center;margin:0px;border:0px;padding:0px;\">";
  echo "Copyright 2004 - 2008&nbsp;";                           // bottom line
  echo "<a href=\"http://www.vvs.be\">Vereniging voor Sterrenkunde</a> - Powered by&nbsp;";
  echo "<a href=\"http://www.deepskylog.org\">DeepskyLog</a>&nbsp;".$versionInfo;   //defined in databaseInfo.php
  echo "</div>";
  if(isset($entryMessage)&&$entryMessage)                                           // Entry Message if any
    echo "<script>alert('".addslashes(html_entity_decode($entryMessage))."');</script>";
}
catch (Exception $e)
{ echo "<p>DeepskyLog encounterd a problem. Could you please report it to the Developers?</p>";
  echo "<p>Report problem with error message: " . $e->getMessage()."</p>";
  echo "<p>You can report the problem by sending an email to developers@deepskylog.be.</p>";
  echo "<p>Thank you.</p>";
  // EMAIL developers with error codes
}

?>
