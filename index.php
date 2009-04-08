<?php // index.php - main entrance to DeepskyLog
try
{ $inIndex=true;
  if(!array_key_exists('indexAction',$_GET)&&array_key_exists('indexAction',$_POST)) 
    $_GET['indexAction']=$_POST['indexAction'];
  include 'common/entryexit/preludes.php';                                                                // Includes of all classes and assistance files
  include 'common/entryexit/instructions.php';                                                            // Execution of all non-layout related instructions (login, add objects to lists, etc.)
  include 'common/layout/presentation.php';                                                               // functions etc. concerning layout
  //echo    "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
  echo    "<html xmlns=\"http://www.w3.org/1999/xhtml\">";
  include 'common/menu/head.php';                                                                         // HTML head
  echo    "<body id=\"dslbody\" onresize=\"resizeForm();\" \">";
  echo    "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/wz_tooltip.js\"></script>";
  echo    "<div id=\"div4\">";                                                                            // div3 = left menu section
  echo    "<p style=\"position:absolute;left:20px;top:20px;\">".LangIndexPleaseWait."</p>";
  echo    "<img id=\"div4a\" src=\"".$baseURL."styles/images/lu.gif\" alt=\"\"/>";
  echo    "<img id=\"div4b\" src=\"".$baseURL."styles/images/lo.gif\" alt=\"\" />";
  echo    "<img id=\"div4c\" src=\"".$baseURL."styles/images/ru.gif\" alt=\"\" />";                       
  echo    "<img id=\"div4d\" src=\"".$baseURL."styles/images/ro.gif\" alt=\"\" />";                       
  echo    "</div>";
  include 'common/menu/headmenu.php';                                                                     // div1&2 = Page Title and welcome line - modules choices
  echo    "<div id=\"div3\">";                                                                            // div3 = left menu section
  include 'common/entryexit/menu.php';
  echo    "</div>";
  echo    "<div id=\"div6\">";	
  echo    $copyrightInfo.$vvsInfo.$dslInfo.$versionInfo;                      // defined in databaseInfo.php
  echo    "</div>";
  echo    "<div id=\"div5\">";                                                                            // div 5 = page contents
  $includeFile=$objUtil->utilitiesDispatchIndexAction();                                                  // Determine the page to show
  include 'common/entryexit/data.php';                                                                    // Get data for the form, object data, observation data, etc.
  include $includeFile;                                                                                   // Center content section	
  echo    "</div>";
}
catch (Exception $e)
{ echo    "<p>DeepskyLog encounterd a problem. Could you please report it to the Developers?</p>";
  echo    "<p>Report problem with error message: " . $e->getMessage()."</p>";
  echo    "<p>You can report the problem by sending an email to developers@deepskylog.be.</p>";
  echo    "<p>Thank you.</p>";
  // EMAIL developers with error codes
}
echo "<script type=\"text/javascript\">";
echo "resizeForm();";
echo "</script>";
if(isset($entryMessage)&&$entryMessage)                                                                 // dispays $entryMessage if any
  $objPresentations->alertMessage($entryMessage);
echo "</body>";
echo "</html>";
?>