<?php
echo "</table>";
echo "</td>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</td>";
echo "<td colspan=\"3\" align=\"right\" valign=\"top\" style=\"background:url(vvs/images/toolbar_bg.jpg) no-repeat top left; background-color:#FFFFFF\">";
/*
echo "<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">"; 
echo "<tr width=\"100%\">";
echo "<td width=\"50%\">";
echo "<span class=\"pathway\"> &nbsp".LangYouAreHere;
echo "<span class=\"pathway\">";
echo "<a href=\"http://www.vvs.be/\" class=\"pathway\">".LangHome."</a>";
echo "<img src=\"vvs/images/arrow.png\" alt=\"arrow\" />";
echo "<a href=\"http://www.deepsky.be/\" class=\"pathway\">Deepsky</a>";
echo "<img src=\"vvs/images/arrow.png\" alt=\"arrow\" />   DeepskyLog";
echo "</span>";
echo "</span>";
echo "</td>";
echo "<td align=\"right\" width=\"50%\" nowrap=\"nowrap\"><span class=\"mainlevel\"> VVS: </span>";
echo "<span class=\"mainlevel\"> | </span>";
echo "<a href=\"http://www.vvs.be/component/option,com_frontpage/Itemid,1/\" class=\"mainlevel\" >".LangHome."</a>";
echo "<span class=\"mainlevel\"> | </span>";
echo "<a href=\"http://www.vvs.be/component/option,com_wrapper/Itemid,348/?\" class=\"mainlevel\" >".LangBecomeMember."</a>";
echo "<span class=\"mainlevel\"> | </span>";
echo "<a href=\"http://www.vvs.be/component/option,com_search/Itemid,81/\" class=\"mainlevel\" >".LangSearch."</a>";
echo "<span class=\"mainlevel\"> | </span>";
echo "<a href=\"http://www.vvs.be/component/option,com_contact/Itemid,80/\" class=\"mainlevel\" >".LangContact."</a>";
echo "<span class=\"mainlevel\"> | </span>";
echo "</td>";
echo "</tr>";
echo "</table>";
*/
echo "<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">"; 
echo "<tr width=\"100%\">";
echo "<td width=\"50%\">";
echo "<span class=\"mainlevel\">";
echo LangWelcome;
echo $objUtil->checkSessionKey('module');
echo LangWelcome1;
echo $baseURL;
echo "</span>";
echo "</td>";
echo "<td align=\"right\" width=\"50%\" nowrap=\"nowrap\">";
echo "<span class=\"mainlevel\">";
if($objUtil->checkSessionKey('deepskylog_id'))
  echo LangWelcome2.$objObserver->getFirstName($_SESSION['deepskylog_id'])."&nbsp;".$objObserver->getObserverName($_SESSION['deepskylog_id']);
else
  echo LangWelcome3;
echo "</span>";
echo "</td>";
echo "</tr>";
echo "</table>";

echo "</td>";
echo "<td height=\"28\" colspan=\"3\" align=\"right\" valign=\"top\" style=\"background:url(vvs/images/rb_bg.gif) no-repeat top right; background-color:#FFFFFF\">";
echo "<img src=\"vvs/images/rb.gif\" width=\"28\" height=\"28\" />";
echo "</td>";
echo "<td width=\"151\" rowspan=\"3\" align=\"left\" valign=\"top\" bgcolor=\"#5C7D9D\">";
echo "<table width=\"100%\"  border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
echo "<tr>";
echo "<td bgcolor=\"#003466\">&nbsp;<br />";
echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
echo "<tr>";
echo "<th valign=\"top\">".LangDeepskyLogModules."</th> ";
echo "</tr>";
echo "<tr>";
echo "<td height=\"30\" valign=\"top\">";
for ($i = 0; $i < count($modules);$i++)
{ $mod = $modules[$i];
  echo "<a href=\"".$baseURL."index.php?indexAction=module".$mod."\">".$GLOBALS[$mod]."</a><br />";
}
echo "</td>";
echo "</tr>";
echo "<tr>";
if ($_SESSION['module']=="deepsky")
  echo "<th valign=\"top\">".LangQuickPickTitle."</th>";
echo "</tr>";
echo "<tr>";
echo "<td valign=\"top\" height=\"175\">";
if ($_SESSION['module'] == "deepsky")
{ echo "<form action=\"".$baseURL."index.php\" method=\"get\">";
	echo LangQuickPickHelp;
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"quickpick\"></input>";
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"255\" name=\"object\"  value=\"".urlencode((array_key_exists('object',$_GET) && ($_GET['object'] != '* '))?$_GET['object']:"")."\" >";
	echo "<input type=\"submit\" name=\"searchObject\" value=\"".LangQuickPickSearchObject."\" style=\"width: 147px\" >";
	echo "<input type=\"submit\" name=\"searchObservations\" value=\"".LangQuickPickSearchObservations."\" style=\"width: 147px\" >";
	if (array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
	  echo "<input type=\"submit\" name=\"newObservation\" value=\"".LangQuickPickNewObservation."\" style=\"width: 147px\" >";
	echo "</form>";
}
echo "</td>";
echo "</tr>";
echo "<tr>";
if ($_SESSION['module'] == "deepsky")
  echo "<th valign=\"top\">".LangListsTitle."</th>";
echo "</tr>";
echo "<tr>";
echo "<td valign=\"top\" height=\"300\">";
if (($_SESSION['module'] == "deepsky") && (array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id']))
{ echo "<form action=\"".$baseURL."index.php\" method=\"get\">";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"listaction\"></input>";
	echo "<input type=\"submit\" name=\"manage\" value=\"".LangListManage."\" style=\"width: 147px\">";
  echo "</form>";
  if(array_key_exists('addList',$_GET) && array_key_exists('addlistname',$_GET))
  { if(array_key_exists('QOL',$_SESSION))
      unset($_SESSION['QOL']);
    $listnameToAdd = $_GET['addlistname'];
    if(array_key_exists("PublicList",$_GET))
      if(substr($listnameToAdd,0,7)!="Public:")
        $listnameToAdd = "Public: " . $listnameToAdd;  
    if($objList->checkList($_GET['addlistname'])!=0)
      $_GET['listnameMessage'] = LangToListList . stripslashes($listnameToAdd) . LangToListExists;
    else
    { $objList->addList($listnameToAdd);
      if(array_key_exists('QOL',$_SESSION))
				unset($_SESSION['QOL']);
      $_SESSION['listname'] = $listnameToAdd;
      $_GET['listnameMessage'] = LangToListList . stripslashes($_SESSION['listname']) . LangToListAdded;
    }                    	
  }
	if(array_key_exists('renameList',$_GET) && array_key_exists('addlistname',$_GET))
	{ if(array_key_exists('QOL',$_SESSION))
			unset($_SESSION['QOL']);
		$listnameFrom = $_SESSION['listname'];
		$listnameTo = $_GET['addlistname'];
		if(array_key_exists("PublicList",$_GET))
		  if(substr($listnameTo,0,7)!="Public:")
		    $listnameTo = "Public: " . $listnameTo;  
    if($objList->checkList($listnameTo)!=0)
      $_GET['listnameMessage'] =  LangToListList . stripslashes($listnameTo) . LangToListExists;
    else
    { $objList->renameList($listnameFrom, $listnameTo);
      $_SESSION['listname'] = $listnameTo;
      $_GET['listnameMessage'] = LangToListList . stripslashes($_SESSION['listname']) . LangToListAdded;
    }
  }
  if(array_key_exists('removeList',$_GET) && ($objList->checkList($_SESSION['listname'])==2))
  { if(array_key_exists('QOL',$_SESSION))
			unset($_SESSION['QOL']);
			$objList->removeList($_SESSION['listname']);
			$_GET['listnameMessage'] = LangToListRemoved . stripslashes($_SESSION['listname']) . ".";
			$_SESSION['listname']="----------";
			unset($_GET['removeList']);
  }
  if(array_key_exists('activateList',$_GET) && array_key_exists('listname',$_GET))
  { if(array_key_exists('QOL',$_SESSION))
      unset($_SESSION['QOL']);
    $_SESSION['listname'] = $_GET['listname'];
    if($_GET['listname']<>"----------")
      $_GET['listnameMessage'] = LangToListList . stripslashes($_SESSION['listname']) . LangToListActivation1 . LangBack . LangToListActivation2;
  }
  $result1=array();
	$result2=array();
  $db = new database;
  $db->login();
	$sql = "SELECT DISTINCT observerobjectlist.listname " .
				 "FROM observerobjectlist " .
				 "WHERE observerid = \"" . $_SESSION['deepskylog_id'] . "\" ORDER BY observerobjectlist.listname";
	$run = mysql_query($sql) or die(mysql_error());
	$get = mysql_fetch_object($run);
	while($get)
	{ $result1[]=$get->listname;
	  $get = mysql_fetch_object($run);
	}
	$sql = "SELECT DISTINCT observerobjectlist.listname " .
				 "FROM observerobjectlist " .
	       "WHERE observerid <> \"" . $_SESSION['deepskylog_id'] . "\"" . 
				 "AND listname LIKE \"Public: %\" ORDER BY observerobjectlist.listname";
	$run = mysql_query($sql) or die(mysql_error());
	$get = mysql_fetch_object($run);
	while($get)
	{ $result2[]=$get->listname; 
	  $get = mysql_fetch_object($run);
	}
	$db->logout();
	$result1[]='----------';
	$result=array_merge($result1,$result2);
	echo("<form name=\"listform\">\n ");
	if(count($result)>0)
	{ echo("<select style=\"width: 147px\" onchange=\"location = this.options[this.selectedIndex].value;\" name=\"activatelist\">\n");
		if((!array_key_exists('listname',$_SESSION)) || (!$_SESSION['listname']))
			$_SESSION['listname']="----------";
    while(list($key, $value) = each($result))
		{ if($value==$_SESSION['listname'])
				echo("<option selected value=\"".$baseURL."index.php?indexAction=listaction&amp;activateList=true&amp;listname=".$value."\">".$value."</option>");
       elseif (!(array_key_exists('removeList',$_GET) && ($_SESSION['listname']==$value)))
				 echo("<option value=\"".$baseURL."index.php?indexAction=listaction&amp;activateList=true&amp;listname=".$value."\">".$value."</option>");
    }
    echo("</select>\n");
	}
	echo("</form>");
}
else
  echo(LangListOnlyMembers);
echo "</td>";
echo "</tr>";
if ($_SESSION['lang'] == "nl")
{ echo"<tr>";
	echo "<th valign=\"top\">Help</th>";
	echo "</tr><tr>";
	echo "<td valign=\"top\" height=\"60\">";
	echo "<a href=\"http://www.deepskylog.org/wiki/bin/view/Main/DeepskyLogManualNL\" target=\"_blank\">Handleiding</a>";
	echo "</td>";
	echo "</tr>";
}
echo "<tr>";
if ($_SESSION['module'] == "deepsky")
  echo "<th valign=\"top\">".LangMailtoTitle."</th>"; 
echo "</tr>";
echo "<tr>";
echo "<td valign=\"top\" height=\"120\">";
if ($_SESSION['module'] == "deepsky")
  echo LangMailtoLink;
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td height=\"13\" align=\"left\" valign=\"bottom\" background=\"vvs/images/rightcolumn_2.gif\">";
echo "<img src=\"vvs/images/rightcolumn_1.gif\" width=\"10\" height=\"13\" />";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td height=\"12\" background=\"vvs/images/rightcolumn_4.gif\">";
echo "<img src=\"vvs/images/rightcolumn_3.gif\" width=\"12\" height=\"12\" />";
echo "<img src=\"vvs/images/rightcolumn_4.gif\" width=\"1\" height=\"12\" />";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td align=\"center\" bgcolor=\"#333333\">";
echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
echo "<tr>";
echo "<th valign=\"top\">Teller</th>";
echo "</tr>";
echo "</table>";
//    <!-- Start of StatCounter Code -->
echo "<a href=\"http://my6.statcounter.com/project/standard/stats.php?project_id=1347986&guest=1\">";
echo "<script type=\"text/javascript\" language=\"javascript\">";
echo "var sc_project=1347986;";
echo "var sc_invisible=0;";
echo "var sc_partition=12;";
echo "var sc_security=\"155f4e3f\";";
echo "var sc_remove_link=1;";
echo "</script>";
echo "<script type=\"text/javascript\" language=\"javascript\" src=\"http://www.statcounter.com/counter/counter.js\">";
echo "</script>";
echo "<noscript>";
echo "<img  src=\"http://c13.statcounter.com/counter.php?sc_project=1347986&amp;java=0&amp;security=155f4e3f&amp;invisible=0\" alt=\"free webpage counters\" border=\"0\">";
echo "</noscript>";
echo "</a>";
//    <!-- End of StatCounter Code -->
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td align=\"left\" valign=\"top\" background=\"vvs/images/rightcolumn_6.gif\">";
echo "<img src=\"vvs/images/rightcolumn_5.gif\" width=\"12\" height=\"12\" />";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td bgcolor=\"#FFFFFF\"></td>";
echo "<td colspan=\"3\" valign=\"top\" bgcolor=\"#FFFFFF\">";
echo "<table width=\"100%\"  border=\"0\" cellpadding=\"0\" cellspacing=\"0\" bgcolor=\"#FFFFFF\">";
echo "<tr>";
echo "<td>";
echo "<table class=\"blog2\" cellpadding=\"0\" cellspacing=\"0\">";
echo "<tr>";
echo "<td valign=\"top\">";
echo "<div>";
echo "<table class=\"contentpaneopen\">";
echo "<tr>";
echo "<td class=\"contentheading\" width=\"100%\">";
echo "http://www.deepskylog.org ";
echo "<div style=\"text-align:right\">";
$mod = $_SESSION['module'];
echo $mod;
echo "</div>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "<table class=\"contentpaneopen\">";
echo "<tr>";
echo "<td valign=\"top\" colspan=\"2\">";
?>
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            