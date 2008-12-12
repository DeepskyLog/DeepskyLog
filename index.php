<?php
// index.php
// main entrance to DeepskyLog

try
{	if(!array_key_exists('indexAction',$_GET)&&array_key_exists('indexAction',$_POST)) 
    $_GET['indexAction']=$_POST['indexAction'];
  include 'common/entryexit/preludes.php';                                          // Includes of all classes and assistance files
  include 'common/entryexit/instructions.php';                                      // Execution of all non-layout related instructions (login, add objects to lists, etc.)
  include 'common/menu/head.php';                                                   // HTML head
  include 'common/menu/headmenu.php';                                               // Page Title and welcome line - modules choices
  
                                                                                    // Page Center Content 
  echo "<table width=\"100%\" height=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
  echo "<tr>";
  echo "<td width=\"153px\" align=\"left\" valign=\"top\" style=\"background:url(".$baseURL."vvs/images/left_bg.jpg) repeat-x top left; background-color:#5C7D9D\">";
  echo "<br />";
  include 'common/entryexit/menu.php';                                              // Left Menu Section
  if(isset($entryMessage)&&$entryMessage) 
    echo $entryMessage.'<hr />';
  echo "</td><td style=\"background-color:#FFFFFF\">";
  include $objUtil->utiltiesDispatchIndexAction();                                  // Center content section	
  echo "</td>";
  echo "<td>&nbsp;</td>";                                                           // Right blue bar
  echo "</tr>"; 
  echo "</table>";
  include 'common/menu/tail.php';                                                   // Page bottom
}
catch (Exception $e)
{ echo 'Report problem: ' . $e->getMessage();
}

?>
