<?php
// objectSets.php
// allows the user to generate a pdf series with object data, DSS photos, DSL charts an index pages

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(_("You need to be logged in to change your locations or equipment."));
else objectsSets();


function objectsSets()
{ global $objObserver, $loggedUser, $baseURL, $loggedUserName, $objReportLayout, $objUtil, $MSIE;
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
	echo _("Generate a set of charts for each object.").'<br />';
	echo _("Each set contains a number of charts specified by the FoVs asked below.").'<br />';
	echo _("Each chart specified by the FoVs, shows stars and objects up to the specified magnitudes.").'<br />';
	echo _("Each magnitude field should contain as many magnitudes as there are FoVs.").'<br />'.'<br />';		
	echo _("Before each object, you can add a data section. This contains the elementary data, an object description when available, and 2 photos by the size indicated by you (15, 30 or 60 arc minutes, or nothing).").'<br />'.'<br />';		
	echo _("You can add an index after each section. This index contains an overview of all the objects on each of the maps.").'<br />'.'<br />';		
	echo _("You can save each set and use a pdf merger to make one large atlas-catalogue if you wish.").'<br />'.'<br />';		
	echo _("If you choose to make all objects in one pass, please remember that each object can take up to 30 seconds or more to generate. Attention: this option is available in all browsers except for Microsoft Internet Explorer.").'<br />'.'<br />';		
	if(!($MSIE))
	  echo "<input type=\"button\" class=\"btn btn-primary\" value=\""._("Generate all")."\" onclick=\"generateallonepass(0,".($MSIE?'true':'false').");\"/>";
	echo "&nbsp;"."<div id='thecounter'> &nbsp; </div>";
	echo '<br />';	
  echo _("Add a data page")."<input id=\"datapage\" type=\"checkbox\" value=\"\" />";
  if($loggedUser)
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"._("with ephemerides")."<input id=\"ephemerides\" type=\"checkbox\" />";
  else
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"."<input style=\"visibility:hidden;\" id=\"ephemerides\" type=\"checkbox\" />";
  if($loggedUser)
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"._("with yearephemerides")."<input id=\"yearephemerides\" type=\"checkbox\" />";
  else
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"."<input style=\"visibility:hidden;\" id=\"yearephemerides\" type=\"checkbox\" />";
  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"._("Add an index page")."<input id=\"indexpage\" type=\"checkbox\" onclick=\"if(document.getElementById('indexpage').checked==true) {document.getElementById('reportlayoutselect').style.visibility='visible'; alert('"._("Please select a layout for the index page.")."');} else document.getElementById('reportlayoutselect').style.visibility='hidden';\" value=\"\" />";
  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"."<select id=\"reportlayoutselect\" name=\"reportlayoutselect\" class=\"form-control\" style=\"visibility:hidden;\" >";
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
  echo "<table class=\"table table-responsive table-condensed\">";
  echo "<tr>";
  echo "<td> &nbsp; </td>";
  echo "<td>"."<input id=\"RCB"."all"."\" class=\"btn btn-primary btn-sm\" type=\"button\" value=\"V\" onclick=\"setCheckedValues('fovs',".$k.",document.getElementById('RDfovs').value,'all');
                                                                                setCheckedValues('dsos',".$k.",document.getElementById('RDdsos').value,'all');
                                                                                setCheckedValues('stars',".$k.",document.getElementById('RDstars').value,'all');
                                                                                setCheckedValues('photos',".$k.",document.getElementById('RDphotos').value,'all');\" />"."</td>";
  echo "<td> &nbsp; </td>";
  echo "<td>"."<span class=\"form-inline\"><input type=\"text\" class=\"form-control\" id=\"RD"."fovs"."\" value=\"".$fovo." ".$fovl." ".$fovd."\" /><input id=\"RCB"."fovs"."\" class=\"btn btn-primary btn-sm\" type=\"button\" value=\"V\" onclick=\"setCheckedValues('fovs',".$k.",document.getElementById('RDfovs').value);\" />"."</span></td>";
  echo "<td>"."<span class=\"form-inline\"><input type=\"text\" class=\"form-control\" id=\"RD"."dsos"."\" value=\"".$dsoso." ".$dsosl." ".$dsosd."\"/><input id=\"RCB"."dsos"."\" class=\"btn btn-primary btn-sm\" type=\"button\" value=\"V\" onclick=\"setCheckedValues('dsos',".$k.",document.getElementById('RDdsos').value);\" />"."</span></td>";
  echo "<td>"."<span class=\"form-inline\"><input type=\"text\" class=\"form-control\" id=\"RD"."stars"."\" value=\"".$starso." ".$starsl." ".$starsd."\"/><input id=\"RCB"."stars"."\" class=\"btn btn-primary btn-sm\" type=\"button\" value=\"V\" onclick=\"setCheckedValues('stars',".$k.",document.getElementById('RDstars').value);\" />"."</span></td>";
  echo "<td>"."<span class=\"form-inline\"><input type=\"text\" class=\"form-control\" id=\"RD"."photos"."\" value=\"".$foto1." ".$foto2."\"/><input id=\"RCB"."photos"."\" class=\"btn btn-primary btn-sm\" type=\"button\" value=\"V\" onclick=\"setCheckedValues('photos',".$k.",document.getElementById('RDphotos').value);\" />"."</span></td>";
  echo "</tr>";  
  echo "<tr>";
  echo "<th><strong>"._("Object")."</strong></th>";
  echo "<th> &nbsp; </th>";
  echo "<th><strong>"._("Size")."</strong></th>";
  echo "<th><strong>"._("FoVs: shown field of views")."</strong></th>";
  echo "<th><strong>"._("Object magnitudes")."</strong></th>";
  echo "<th><strong>"._("Stellar magnitudes")."</strong></th>";
  echo "<th><strong>"._("Photos (arc minutes)")."</strong></th>";
  
  echo "</tr>";
  for($i=0;$i<$k;$i++)
  { echo "<tr>";
    echo "<td id=\"T".$i."\">"."<input id=\"R".$i."\" class=\"btn btn-success btn-sm\" type=\"button\" value=\"".$_SESSION['Qobj'][$i]['showname']."\" title=\"".$_SESSION['Qobj'][$i]['objectname']."\" onclick=\"generateOne(".$i.",".($MSIE?'true':'false').");\"/>"."</td>";
  	echo "<td>"."<input id=\"R".$i."CB"."all"."\" type=\"checkbox\" />"."</td>";
    echo "<td id=\"R".$i."Dsize\">".$_SESSION['Qobj'][$i]['objectsize']."</td>";
    echo "<td>".((($_SESSION['Qobj'][$i]['objectdiam1']/60)>$fovd)?"<div class=\"form-group has-error has-feedback form-inline\">":"<div class=\"form-group form-inline\">") . "<input type=\"text\" class=\"form-control\" id=\"R".$i."D"."fovs"."\" value=\"".$fovo." ".$fovl." ".$fovd."\" /><input id=\"R".$i."CB"."fovs"."\" type=\"checkbox\" />"."</div></td>";
    echo "<td>"."<span class=\"form-inline\"><input class=\"form-control\" type=\"text\" id=\"R".$i."D"."dsos"."\" value=\"".$dsoso." ".$dsosl." ".$dsosd."\"/><input id=\"R".$i."CB"."dsos"."\" type=\"checkbox\" />"."</span></td>";
    echo "<td>"."<span class=\"form-inline\"><input class=\"form-control\" type=\"text\" id=\"R".$i."D"."stars"."\" value=\"".$starso." ".$starsl." ".$starsd."\"/><input id=\"R".$i."CB"."stars"."\" type=\"checkbox\" />"."</span></td>";
    echo "<td>".((($_SESSION['Qobj'][$i]['objectdiam1']/60)>15)?"<div class=\"form-group has-error has-feedback form-inline\">":"<div class=\"form-group form-inline\">") . "<input class=\"form-control\" type=\"text\" id=\"R".$i."D"."photos"."\" value=\"".$foto1." ".$foto2."\"/><input id=\"R".$i."CB"."photos"."\" type=\"checkbox\" />"."</div></td>";
    echo "</tr>";
  }
  echo "<tr>";
  echo "<td> &nbsp; </td>";
  echo "<td> &nbsp; </td>";
  echo "<td> &nbsp; </td>";
  echo "<td>"."Select all: "."<input id=\"RCBA"."fovs"."\" type=\"checkbox\" onclick=\"setAllCheckboxes('CBfovs',".$i.",document.getElementById('RCBAfovs').checked);\"/>"."</td>";
  echo "<td>"."Select all: "."<input id=\"RCBA"."dsos"."\" type=\"checkbox\" onclick=\"setAllCheckboxes('CBdsos',".$i.",document.getElementById('RCBAdsos').checked);\" />"."</td>";
  echo "<td>"."Select all: "."<input id=\"RCBA"."stars"."\" type=\"checkbox\" onclick=\"setAllCheckboxes('CBstars',".$i.",document.getElementById('RCBAstars').checked);\" />"."</td>";
  echo "<td>"."Select all: "."<input id=\"RCBA"."photos"."\" type=\"checkbox\" onclick=\"setAllCheckboxes('CBphotos',".$i.",document.getElementById('RCBAphotos').checked);\" />"."</td>";
  echo "</tr>";  
  echo "</table>";
}
?>
