<?php // index.php - main entrance to DeepskyLog
try
{ $inIndex=true;
  if(!array_key_exists('indexAction',$_GET)&&array_key_exists('indexAction',$_POST)) 
    $_GET['indexAction']=$_POST['indexAction'];
  include 'common/entryexit/preludes.php';                                                                // Includes of all classes and assistance files
  include 'common/entryexit/globals.php';                                                                // Includes of all classes and assistance files
  include 'common/entryexit/instructions.php';                                                            // Execution of all non-layout related instructions (login, add objects to lists, etc.)
  $includeFile=$objUtil->utilitiesDispatchIndexAction();                                                  // Determine the page to show
  include 'common/entryexit/data.php';                                                                    // Get data for the form, object data, observation data, etc.
  echo    "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">";
  echo    "<html xmlns=\"http://www.w3.org/1999/xhtml\">";
  include 'common/menu/head.php';                                                                         // HTML head
  echo    "<body onkeydown=\"function() { bodyOnKeyDown(event); }\">"; 
  echo    "<script type=\"text/javascript\" src=\"".$baseURL."common/entryexit/globals.js\"></script>";
  echo    "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/jsenvironment.js\"></script>";
  echo    "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/wz_tooltip.js\"></script>";
  echo    "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/ajaxbase.js\"></script>";
  echo    "<div id=\"div4\">";                                                                            
  echo    "<p class=\"waitMessage\">".LangIndexPleaseWait."</p>";
  echo    "<img id=\"div4a\" src=\"".$baseURL."styles/images/lu.gif\" alt=\"\" />";
  echo    "<img id=\"div4b\" src=\"".$baseURL."styles/images/lo.gif\" alt=\"\" />";
  echo    "<img id=\"div4c\" src=\"".$baseURL."styles/images/ru.gif\" alt=\"\" />";                       
  echo    "<img id=\"div4d\" src=\"".$baseURL."styles/images/ro.gif\" alt=\"\" />";                       
  echo    "</div>";
  include 'common/menu/headmenu.php';                                                                     // div1&2 = Page Title and welcome line - modules choices
  echo    "<div id=\"div3\">";                                                                            // div3 = left menu section
  include 'common/entryexit/menu.php';
  echo    "</div>";
  echo    "<div id=\"div6\">";	
  $objPresentations->line(array($copyrightInfo.$vvsInfo.$dslInfo.$versionInfo.$objectInfo,$w3cInfo),"LR",array(90,10),18);                                      // defined in databaseInfo.ph)
  echo    "</div>";
  echo    "<div id=\"div5\">";                                                                            // div 5 = page contents
  if(isset($entryMessage)&&$entryMessage)                                                                 // dispays $entryMessage if any
    echo "<p class=\"centered\">".$entryMessage."</p><hr />";
  include $includeFile;     
  echo    "</div>";
}
catch (Exception $e)
{ $entryMessage.="<p>DeepskyLog encounterd a problem. Could you please report it to the Developers?</p>";
  $entryMessage.="<p>Report problem with error message: " . $e->getMessage()."</p>";
  $entryMessage.="<p>You can report the problem by sending an email to developers@deepskylog.be.</p>";
  $entryMessage.="<p>Thank you.</p>";
  // EMAIL developers with error codes
}
echo "<script type=\"text/javascript\">";
echo "resizeForm('".$leftmenu."','".$topmenu."');";
if($loadAtlasPage)
{ echo "atlasFillPage();";
}
echo "</script>";
if(isset($entryMessage)&&$entryMessage)                                                                 // dispays $entryMessage if any
  $objPresentations->alertMessage($entryMessage);
echo "</body>";
echo "</html>";
?>
