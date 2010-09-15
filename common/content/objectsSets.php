<?php
function objectsSets()
{ global $objObserver, $loggedUser, $baseURL, $loggedUserName, $objReportLayout, $objUtil;
  echo "<script type=\"text/javascript\">";
  echo "var Langpdfseriesclickok='".Langpdfseriesclickok."';";
  echo "var Langpdfserieswhenfinished='".Langpdfserieswhenfinished."';";
  echo "var LangpdfseriesGenerating='".LangpdfseriesGenerating."';";
  echo "var Langpdfserieschoselayout='".Langpdfserieschoselayout."';";
  echo "</script>";
  $fovo=$objObserver->getObserverProperty($loggedUser,'overviewFoV','');
  $fovl=$objObserver->getObserverProperty($loggedUser,'lookupFoV','');
  $fovd=$objObserver->getObserverProperty($loggedUser,'detailFoV','');
  $dsoso=$objObserver->getObserverProperty($loggedUser,'overviewdsos','');
  $dsosl=$objObserver->getObserverProperty($loggedUser,'lookupdsos','');
  $dsosd=$objObserver->getObserverProperty($loggedUser,'detaildsos','');
  $starso=$objObserver->getObserverProperty($loggedUser,'overviewstars','');
  $starsl=$objObserver->getObserverProperty($loggedUser,'lookupstars','');
  $starsd=$objObserver->getObserverProperty($loggedUser,'detailstars','');
  $foto1=$objObserver->getObserverProperty($loggedUser,'photosize1','');
  $foto2=$objObserver->getObserverProperty($loggedUser,'photosize2','');
  $k=count($_SESSION['Qobj']);
	echo "<script type=\"text/javascript\" src=\"".$baseURL."common/content/objectsSets.js\"></script>";
	echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/phpjs.js\"></script>";
	echo LangpdfseriesExplain1.'<br />';
	echo LangpdfseriesExplain2.'<br />';
	echo LangpdfseriesExplain3.'<br />';
	echo LangpdfseriesExplain4.'<br />'.'<br />';		
	echo LangpdfseriesExplain6.'<br />'.'<br />';		
	echo LangpdfseriesExplain7.'<br />'.'<br />';		
	echo LangpdfseriesExplain5.'<br />'.'<br />';		
	echo "<input type=\"button\" value=\"".LangpdfseriesButton."\" onclick=\"generate();\"/>".'<br />';	
  if($objUtil->checkGetKey('generateallonepass',''))
    echo "<input type=\"button\" value=\""."one pass"."\" onclick=\"generateallonepass(0);\"/>".'<br />';	
  echo LangpdfseriesAddDataPage."<input id=\"datapage\" type=\"checkbox\" value=\"\" />";
  if($loggedUser)
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".LangpdfseriesWithEphemerides."<input id=\"ephemerides\" type=\"checkbox\" />";
  else
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"."<input style=\"visibility:hidden;\" id=\"ephemerides\" type=\"checkbox\" />";
  if($loggedUser)
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".LangpdfseriesWithYearEphemerides."<input id=\"yearephemerides\" type=\"checkbox\" />";
  else
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"."<input style=\"visibility:hidden;\" id=\"yearephemerides\" type=\"checkbox\" />";
  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".LangpdfseriesAddIndexPage."<input id=\"indexpage\" type=\"checkbox\" onclick=\"if(document.getElementById('indexpage').checked==true) {document.getElementById('reportlayoutselect').style.visibility='visible'; alert('".Langpdfserieschoselayout."');} else document.getElementById('reportlayoutselect').style.visibility='hidden';\" value=\"\" />";
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
  echo "<td class=\"bold\">".Langpdfseriesphotos."</td>";
  
  echo "</tr>";
  for($i=0;$i<$k;$i++)
  { echo "<td id=\"T".$i."\">"."<input id=\"R".$i."\" type=\"button\" value=\"".$_SESSION['Qobj'][$i]['showname']."\" title=\"".$_SESSION['Qobj'][$i]['objectname']."\" onclick=\"generateOne(".$i.");\"/>"."</td>";
    echo "<td id=\"R".$i."Dsize\">".$_SESSION['Qobj'][$i]['objectsize']."</td>";
    echo "<td>"."<input type=\"text\" ".((($_SESSION['Qobj'][$i]['objectdiam1']/60)>$fovd)?"class=\"textred\"":"")." id=\"R".$i."D"."fov"."\" value=\"".$fovo." ".$fovl." ".$fovd."\" />"."</td>";
    echo "<td>"."<input type=\"text\" id=\"R".$i."D"."dsos"."\" value=\"".$dsoso." ".$dsosl." ".$dsosd."\"/>"."</td>";
    echo "<td>"."<input type=\"text\" id=\"R".$i."D"."stars"."\" value=\"".$starso." ".$starsl." ".$starsd."\"/>"."</td>";
    echo "<td>"."<input type=\"text\" ".((($_SESSION['Qobj'][$i]['objectdiam1']/60)>15)?"class=\"textred\"":"")."id=\"R".$i."D"."photos"."\" value=\"".$foto1." ".$foto2."\"/>"."</td>";
    echo "</tr>";
  }
  echo "</table>";
}
objectsSets();
?>
