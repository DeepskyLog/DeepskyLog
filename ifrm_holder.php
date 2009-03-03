<?php
include 'common/entryexit/preludes.php';                                          // Includes of all classes and assistance files
include 'common/layout/presentation.php';
echo "<head>";
echo "<link href=\"".$baseURL."styles/style.css\" rel=\"stylesheet\" type=\"text/css\" />";
echo "</head>";
echo "<body style=\"background-color:#FFFFFF\">";
echo "<script type=\"text/javascript\" src=\"".$baseURL."common/menu/wz_tooltip.js\"></script>";
include $instDir.$_SESSION['ifrm'];
echo "</body>";
?>