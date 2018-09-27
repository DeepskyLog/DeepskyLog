<?php
// admin.php
// menu which allows the adminstrator to perform administrator tasks

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(_("You need to be logged in to change your locations or equipment."));
elseif($_SESSION['admin']!="yes") throw new Exception(_("You need to be logged in as an administrator to execute these operations."));
else admin();

function admin()
{ global $baseURL,$menuAdmin,
         $objUtil;
	  echo "<ul class=\"nav navbar-nav\">
		  	  <li class=\"dropdown\">
	         <a href=\"http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] ."#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">" . _("Administration")."<b class=\"caret\"></b></a>";
  	echo " <ul class=\"dropdown-menu\">";
    echo "  <li><a href=\"".$baseURL."index.php?indexAction=new_message&amp;receiver=all\">"._('Send message to all')."</a></li>";
    echo "  <li class=\"disabled\">─────────────────</li>";
	  echo "  <li><a href=\"".$baseURL."index.php?indexAction=view_observers\">"._("Observers")."</a></li>";
    echo "  <li class=\"disabled\">─────────────────</li>";
    echo "  <li><a href=\"".$baseURL."index.php?indexAction=admin_check_objects\">"._('Check Objects')."</a></li>";
    echo "  <li class=\"disabled\">─────────────────</li>";
	  echo "  <li><a href=\"".$baseURL."index.php?indexAction=overview_locations\">"._("Locations")."</a></li>";
	  echo "  <li><a href=\"".$baseURL."index.php?indexAction=overview_instruments\">"._("Instruments")."</a></li>";
	  echo "  <li><a href=\"".$baseURL."index.php?indexAction=overview_eyepieces\">"._("Eyepieces")."</a></li>";
	  echo "  <li><a href=\"".$baseURL."index.php?indexAction=overview_filters\">"._("Filters")."</a></li>";
	  echo "  <li><a href=\"".$baseURL."index.php?indexAction=overview_lenses\">"._("Lenses")."</a></li>";
	  echo " </ul>";
	  echo "</li>
		  	  </ul>";
}
?>
