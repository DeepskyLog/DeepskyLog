<?php //list.php - shows the lists available to the user
if($loggedUser)
{ echo "<div class=\"menuDivExtended\">";
  echo "<p   class=\"menuHead\">".LangListsTitle."&nbsp;-&nbsp;"."<a href=\"".$baseURL."index.php?indexAction=listaction\">".LangManage."</a>"."</p>";
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
  if(count($result)>0)
	{ echo "<select name=\"activatelist\" class=\"menuFieldExtended menuDropdown\" onchange=\"location=this.options[this.selectedIndex].value;\">";
		if((!array_key_exists('listname',$_SESSION)) || (!$_SESSION['listname']))
			$_SESSION['listname']="----------";
    while(list($key, $value) = each($result))
		{ if($value==$_SESSION['listname'])
				echo("<option selected value=\"".$baseURL."index.php?indexAction=listaction&amp;activateList=true&amp;listname=".$value."\">".$value."</option>");
       elseif (!(array_key_exists('removeList',$_GET) && ($_SESSION['listname']==$value)))
				 echo("<option value=\"".$baseURL."index.php?indexAction=listaction&amp;activateList=true&amp;listname=".$value."\">".$value."</option>");
    }
    echo "</select>";
	}
  echo "</div>";
}
?>