<?php

if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
{ echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
  echo "<tr>";
  echo "<th valign=\"top\">".LangListsTitle."</th>";
  echo "</tr>";
  echo "<tr>";
  echo "<td valign=\"top\">";
  echo "<form action=\"".$baseURL."index.php\" method=\"get\">";
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
	$result1[]='----------';
	$result=array_merge($result1,$result2);
	echo("<form name=\"listform\">");
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
  echo "</td>";
  echo "</tr>";
  echo "</table>";
}
?>