<?php 
//list.php
// shows the lists available to the user

global $inIndex,$loggedUser,$objUtil;

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(LangExcpetion001);
elseif(!($objUtil->checkAdminOrUserID($loggedUser))) throw new Exception(LangExcpetion012);
else menu_list();

function menu_list()
{ global 	$baseURL,$loggedUser,$myList,$objDatabase;
  echo "<ul class=\"nav navbar-nav\">";
	echo "<p class=\"navbar-text\">".LangListsTitle;
	if($loggedUser)
	  echo "&nbsp;-&nbsp;"."<a href=\"".$baseURL."index.php?indexAction=listaction\">".LangManage."</a>";
	echo "</p>
			  </ul>";
	$result1=array();
	$result2=array();
	$sql = "SELECT DISTINCT observerobjectlist.listname " .
				 "FROM observerobjectlist " .
				 "WHERE observerid = \"" . $loggedUser . "\" ORDER BY observerobjectlist.listname";
	$run = $objDatabase->selectRecordset($sql);
	$get = $run->fetch(PDO::FETCH_OBJ);
	while($get)
	{ $result1[]=$get->listname;
	  $get = $run->fetch(PDO::FETCH_OBJ);
	}
	$sql = "SELECT DISTINCT observerobjectlist.listname " .
				 "FROM observerobjectlist " .
	       "WHERE observerid <> \"" . $loggedUser . "\"" . 
				 "AND listname LIKE \"Public: %\" ORDER BY observerobjectlist.listname";
	$run = $objDatabase->selectRecordset($sql);
	$get = $run->fetch(PDO::FETCH_OBJ);
  echo "<ul class=\"nav navbar-nav\">";
	echo "<p class=\"navbar-text\">";
	while($get)
	{ $result2[]=$get->listname; 
	  $get = $run->fetch(PDO::FETCH_OBJ);
	}
	$result1[]='----------';
	$result=array_merge($result1,$result2);
	if(count($result)>0)
	{ echo "<select name=\"activatelist\" onchange=\"location=this.options[this.selectedIndex].value;\">";
	  if((!array_key_exists('listname',$_SESSION)) || (!$_SESSION['listname']))
			$_SESSION['listname']="----------";
	  while(list($key, $value) = each($result))
		{ if((($value==$_SESSION['listname'])&&$myList)||((!$myList)&&($value=="----------")))
				echo("<option selected=\"selected\" value=\"".$baseURL."index.php?indexAction=listaction&amp;activateList=true&amp;listname=".$value."\">".$value."</option>");
	    elseif (!(array_key_exists('removeList',$_GET) && ($_SESSION['listname']==$value)))
				echo("<option value=\"".$baseURL."index.php?indexAction=listaction&amp;activateList=true&amp;listname=".$value."\">".$value."</option>");
	  }
	  echo "</select>";
	}
	echo "</p></ul>";
}
?>