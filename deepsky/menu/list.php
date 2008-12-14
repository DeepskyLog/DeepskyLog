<?php

if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
{ echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";

  echo "<tr>";
  echo "<th valign=\"top\">".LangListsTitle."&nbsp;-&nbsp;"."<a href=\"".$baseURL."index.php?indexAction=listaction\">".LangManage."</a>"."</th>";
  echo "</tr>";

  echo "<tr>";
  echo "<td>";
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
	{ echo("<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"activatelist\">\n");
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
  echo "</td>";
  echo "</tr>";
  echo "</table>";
}
?>