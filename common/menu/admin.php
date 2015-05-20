<?php 
// admin.php
// menu which allows the adminstrator to perform administrator tasks

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
elseif($_SESSION['admin']!="yes") throw new Exception(LangException001);
else admin();

function admin()
{ global $baseURL,$menuAdmin,
         $objUtil;
	  echo "<ul class=\"nav navbar-nav\">
		  	  <li class=\"dropdown\">
	         <a href=\"http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] ."#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">" . LangAdminMenuTitle."<b class=\"caret\"></b></a>";
  	echo " <ul class=\"dropdown-menu\">";
	  echo "  <li><a href=\"".$baseURL."index.php?indexAction=view_observers\">".LangAdminMenuItem1."</a></li>";
	  echo "  <li><a href=\"".$baseURL."index.php?indexAction=overview_locations\">".LangAdminMenuItem2."</a></li>";
	  echo "  <li><a href=\"".$baseURL."index.php?indexAction=overview_instruments\">".LangAdminMenuItem3."</a></li>";
	  echo "  <li><a href=\"".$baseURL."index.php?indexAction=overview_eyepieces\">".LangAdminMenuItem4."</a></li>";
	  echo "  <li><a href=\"".$baseURL."index.php?indexAction=overview_filters\">".LangAdminMenuItem5."</a></li>";
	  echo "  <li><a href=\"".$baseURL."index.php?indexAction=overview_lenses\">".LangAdminMenuItem6."</a></li>";
	  echo "  <li><a href=\"".$baseURL."index.php?indexAction=admin_check_objects\">".LangAdminMenuItem7."</a></li>";
	  echo "  <li><a href=\"".$baseURL."index.php?indexAction=new_message&amp;receiver=all\">".LangAdminMenuItem8."</a></li>";
	  echo " </ul>";
	  echo "</li>
		  	  </ul>";
}
?>