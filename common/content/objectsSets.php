<?php
function objectsSets()
{ global $objObserver, $loggedUser, $baseURL, $loggedUserName, $objReportLayout;
  $fovo=$objObserver->getObserverProperty($loggedUser,'overviewFoV',120);
  $fovl=$objObserver->getObserverProperty($loggedUser,'lookupFoV',60);
  $fovd=$objObserver->getObserverProperty($loggedUser,'detailFoV',15);
  $dsoso=$objObserver->getObserverProperty($loggedUser,'overviewdsos',10);
  $dsosl=$objObserver->getObserverProperty($loggedUser,'lookupdsos',11);
  $dsosd=$objObserver->getObserverProperty($loggedUser,'detaildsos',14);
  $starso=$objObserver->getObserverProperty($loggedUser,'overviewstars',10);
  $starsl=$objObserver->getObserverProperty($loggedUser,'lookupstars',11);
  $starsd=$objObserver->getObserverProperty($loggedUser,'detailstars',14);
  $k=count($_SESSION['Qobj']);
	echo "<script type=\"text/javascript\" src=\"".$baseURL."common/content/objectsSets.js\"></script>";
	echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/phpjs.js\"></script>";
	echo LangpdfseriesExplain1.'<br />';
	echo LangpdfseriesExplain2.'<br />';
	echo LangpdfseriesExplain3.'<br />';
	echo LangpdfseriesExplain4.'<br />'.'<br />';		
	echo LangpdfseriesExplain5.'<br />'.'<br />';		
	echo "<input type=\"button\" value=\"".LangpdfseriesButton."\" onclick=\"generate();\"/>".'<br />';
  echo "Add Data page"."<input id=\"datapage\" type=\"checkbox\" value=\"\" />";
  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"."Add index page"."<input id=\"indexpage\" type=\"checkbox\" onclick=\"if(document.getElementById('indexpage').checked==true) document.getElementById('reportlayoutselect').style.visibility='visible'; else document.getElementById('reportlayoutselect').style.visibility='hidden';\" value=\"\" />";
  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"."<select id=\"reportlayoutselect\" name=\"reportlayoutselect\"  style=\"visibility:hidden;\" >";
  $defaults=$objReportLayout->getLayoutListDefault("ReportQueryOfObjects");
  while(list($key, $value) = each($defaults))
    if($value['observerid']=="Deepskylog default")
      echo "<option value=\"".$value['observerid'].': '.$value['reportlayout']."\">".$value['observerid'].': '.$value['reportlayout']."</option>";
  echo "<option value=\"\" selected=\"selected\" >"."-----"."</option>";
  reset($defaults);
  while(list($key, $value) = each($defaults))
    if($value['observerid']==$loggedUserName)
      echo "<option value=\"".$value['observerid'].': '.$value['reportlayout']."\">".$value['observerid'].': '.$value['reportlayout']."</option>";
  /*
  echo "<option value=\"\" selected=\"selected\" >"."-----"."</option>";
  reset($defaults);
  while(list($key, $value) = each($defaults))
    if(($value['observerid']!="Deepskylog default")&&($value['observerid']!=$loggedUserName))
      echo "<option value=\"".$value['observerid'].': '.$value['reportlayout']."\">".$value['observerid'].': '.$value['reportlayout']."</option>";
  */
  echo "</select>";
  echo "<hr />";
  echo "<table>";
  echo "<tr>";
  echo "<td class=\"bold\">".LangpdfseriesObject."</td>";
  echo "<td class=\"bold\">".LangpdfseriesSize."</td>";
  echo "<td class=\"bold\">".LangpdfseriesFoVs."</td>";
  echo "<td class=\"bold\">".Langpdfseriesdsos."</td>";
  echo "<td class=\"bold\">".Langpdfseriesstars."</td>";
  
  echo "</tr>";
  for($i=0;$i<$k;$i++)
  { echo "<tr>";
    echo "<td id=\"T".$i."\">"."<input id=\"R".$i."\" type=\"button\" value=\"".$_SESSION['Qobj'][$i]['showname']."\" title=\"".$_SESSION['Qobj'][$i]['objectname']."\" onclick=\"generateOne(".$i.");\"/>"."</td>";
    echo "<td id=\"R".$i."Dsize\">".$_SESSION['Qobj'][$i]['objectsize']."</td>";
    echo "<td>"."<input type=\"text\" ".((($_SESSION['Qobj'][$i]['objectdiam1']/60)>$fovd)?"class=\"textred\"":"")." id=\"R".$i."D"."fov"."\" value=\"".$fovo." ".$fovl." ".$fovd."\" />"."</td>";
    echo "<td>"."<input type=\"text\" id=\"R".$i."D"."dsos"."\" value=\"".$dsoso." ".$dsosl." ".$dsosd."\"/>"."</td>";
    echo "<td>"."<input type=\"text\" id=\"R".$i."D"."stars"."\" value=\"".$starso." ".$starsl." ".$starsd."\"/>"."</td>";
  	echo "</tr>";
  }
  echo "</table>";
}
objectsSets();
?>
