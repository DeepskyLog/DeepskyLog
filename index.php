<?php 
// index.php
// main entrance to DeepskyLog

try
{ $inIndex=true;
  $language="nl";
  if(!array_key_exists('indexAction',$_GET)&&array_key_exists('indexAction',$_POST)) 
    $_GET['indexAction']=$_POST['indexAction'];
  require_once 'common/entryexit/globals.php';                                                                // Includes of all classes and assistance files
  require_once 'common/entryexit/preludes.php';                                                                // Includes of all classes and assistance files
  require_once 'common/entryexit/instructions.php';                                                            // Execution of all non-layout related instructions (login, add objects to lists, etc.)
  $includeFile=$objUtil->utilitiesDispatchIndexAction();                                                  // Determine the page to show
  require_once 'common/entryexit/data.php';                                                                    // Get data for the form, object data, observation data, etc.
  echo    "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" >";
  echo    "<html>";
  require_once 'common/menu/head.php';                                                                         // HTML head
  echo    "<body onkeydown=\"bodyOnKeyDown(event);\">"; 
  echo    "<script type=\"text/javascript\" src=\"".$baseURL."common/entryexit/globals.js\"></script>";
  echo    "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/jsenvironment.js\"></script>";
  echo    "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/wz_tooltip.js\"></script>";
  echo    "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/ajaxbase.js\"></script>";
  echo    "<script type=\"text/javascript\" 
              src=\"http://ajax.googleapis.com/ajax/libs/chrome-frame/1/CFInstall.min.js\"></script>";
  echo    "<div id=\"div4\">";                                                                            
  echo    "<p class=\"waitMessage\">".LangIndexPleaseWait."</p>";
  echo    "<img id=\"div4a\" src=\"".$baseURL."styles/images/lu.gif\" alt=\"\" />";
  echo    "<img id=\"div4b\" src=\"".$baseURL."styles/images/lo.gif\" alt=\"\" />";
  echo    "<img id=\"div4c\" src=\"".$baseURL."styles/images/ru.gif\" alt=\"\" />";                       
  echo    "<img id=\"div4d\" src=\"".$baseURL."styles/images/ro.gif\" alt=\"\" />";                       
  echo    "</div>";
  require_once 'common/menu/headmenu.php';                                                                     // div1&2 = Page Title and welcome line - modules choices
  echo    "<div id=\"div3\" onmouseover=\"resizeForm('show',theTopMenu);\">";                                                                            // div3 = left menu section
  require_once 'common/entryexit/menu.php';
  echo    "</div>";
  echo    "<footer>";
  echo    "<a class=\"footertooltip\" href=\"#\">" . $browsertitle . " " . $versionInfo . ", ";
  echo    "<small>" . $copyright . "</small>";
  echo    "<span class=\"classic\">".$copyrightInfo."<br/>".$dslInfo.$versionInfo."<br/>".$objectInfo."</span>";                                      // defined in databaseInfo.ph)
//  echo    "<span class=\"classic\">" . $copyrightInfo.$vvsInfo.$dslInfo.$versionInfo.$objectInfo . "</span>";                                      // defined in databaseInfo.ph)
  echo    "</a>";
  echo    "</footer>";
  echo    "<div id=\"div5\">";                                                                            // div 5 = page contents
  if(isset($entryMessage)&&$entryMessage)                                                                 // dispays $entryMessage if any
    echo "<p class=\"centered\">".$entryMessage."</p><hr />";
    require_once $includeFile;
  echo    "</div>";
}
catch (Exception $e)
{ $entryMessage.="<p>DeepskyLog encountered a problem. Could you please report it to the Developers?</p>";
  $entryMessage.="<p>Report problem with error message: " . $e->getMessage()."</p>";
  $entryMessage.="<p>You can report the problem by sending an email to developers@deepskylog.be.</p>";
  $entryMessage.="<p>Thank you.</p>";
  // EMAIL developers with error codes
}
echo "<script type=\"text/javascript\">";
echo "theLeftMenu='".$leftmenu."';";
echo "theTopMenu='".$topmenu."';";
echo "resizeForm('".$leftmenu."','".$topmenu."');";
if($includeFile=='deepsky/content/view_catalogs.php')
{ echo "view_catalogs('".$leftmenu."','".$topmenu."');";
}
if($loadAtlasPage)
{ echo "atlasFillPage();";
}
echo "</script>";
if(isset($entryMessage)&&$entryMessage)                                                                 // dispays $entryMessage if any
  $objPresentations->alertMessage($entryMessage);

// Scripts for pretty-photo
echo "<script type=\"text/javascript\" charset=\"utf-8\">
        $(document).ready(function(){
        $(\"a[rel^='prettyPhoto']\").prettyPhoto({
         theme: 'dark_rounded',
         social_tools: ''
        });
       });
      </script>";
echo "</body>";
echo "</html>";
?>
