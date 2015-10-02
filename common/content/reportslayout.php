<?php
// reportslayout.php
// allows to specify a report layout

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(LangException001);
else reportsLayout();

function reportsLayout()
{ global $baseURL,$objPresentations,$objReportLayout,$objUtil,$loggedUserName;
  echo   "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/reportlayouts.js\"></script>";
  $reportName=$objUtil->checkGetKey('reportname');
  $reportTitle=$objUtil->checkGetKey('reporttitle');
  echo "<div id=\"main\">";
  echo "<h4>".ReportTitle.constant($reportTitle)."</h4>";
  echo "<hr />";
  echo "<div class=\"form-inline\">";
  $defaults=$objReportLayout->getLayoutListDefault($reportName);
  echo ReportKnownLayouts;
  echo " (".LangShowAll." "."<input id=\"showallcheckbox\" type=\"checkbox\" onchange=\"showSelectOptions('".$reportName."');\" />"."):&nbsp;";
  echo "<select id=\"reportlayoutselect\" name=\"reportlayoutselect\" class=\"form-control\" onchange=\"setLayoutPage('".$loggedUserName."');\">";
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
  echo "&nbsp;";
  echo "<input type=\"button\" class=\"btn btn-primary\" onclick=\"saveAndGeneratePdf('".$baseURL."report.pdf.php','".$reportName."','".$objUtil->checkRequestKey('pdfTtile',"DeepskyLog")."','".$_GET['SID']."');\" value=\"".ReportSaveAndGeneratePdf."\"/>";
  echo "&nbsp;<input type=\"button\" class=\"btn btn-primary\" onclick=\"saveAsLayoutPage('".$reportName."');\" value=\"".ReportSaveAs."\"/>";
  echo "&nbsp;<input type=\"button\" class=\"btn btn-primary hidden\" id=\"deletelayout\" onclick=\"deleteLayoutPage('".$reportName."');\" value=\"".ReportDelete."\"/>";
  echo "<input type=\"hidden\" id=\"tempname\" value=\"\" />";
  echo "<input type=\"hidden\" id=\"tempobserver\" value=\"".$loggedUserName."\" />";
  echo "</div>";
  echo "<div id=\"reportlayout\">";
  echo "</div>";
	echo "<script type=\"text/javascript\">
	      /* <![CDATA[ */
	      var titles=new Array();
        titles['ReportFieldname']='".ReportFieldname."';
	      titles['ReportFieldlineposition']='".ReportFieldlineposition."';
	      titles['ReportFieldxposition']='".ReportFieldxposition."';
	      titles['ReportFieldwidth']='".ReportFieldwidth."';
	      titles['ReportFieldStyle']='".ReportFieldStyle."';
	      titles['ReportFieldTextBefore']='".ReportFieldTextBefore."';
	      titles['ReportFieldTextAfter']='".ReportFieldTextAfter."';
	      titles['ReportFieldLegend']='".ReportFieldLegend."';
	      titles['pagesize']='".Reportpagesize."';
	      titles['pageorientation']='".Reportpageorientation."';
        titles['startpagenumber']='".Reportstartpagenumber."';
        titles['top']='".Reporttop."';
        titles['header']='".Reportheader."';
        titles['xleft']='".Reportxleft."';
        titles['bottom']='".Reportbottom."';
        titles['footer']='".Reportfooter."';
        titles['xmid']='".Reportxmid."';
        titles['fontSizeText']='".ReportfontSizeText."';
        titles['fontSizeSection']='".ReportfontSizeSection."';
        titles['deltalineextra']='".Reportdeltalineextra."';
        titles['sectionBarHeightextra']='".ReportsectionBarHeightextra."';
        titles['deltalineSection']='".ReportdeltalineSection."';
        titles['deltaobjectline']='".Reportdeltaobjectline."';
        titles['SectionBarWidthbase']='".ReportSectionBarWidthbase."';
        titles['sectionbarspace']='".ReportSectionBarSpace."';
        titles['showelements']='".ReportShowElements."';
        titles['objectseen']='".Reportobjectseen."';
        titles['objectlastseen']='".Reportobjectlastseen."';
        titles['showname']='".Reportshowname."';
        titles['objectname']='".Reportobjectname."';
        titles['altname']='".Reportaltname."';
        titles['objecttype']='".Reportobjecttype."';
        titles['objecttypefull']='".Reportobjecttypefull."';
        titles['objectconstellation']='".Reportobjectconstellation."';
        titles['objectconstellationfull']='".Reportobjectconstellationfull."';
        titles['objectmagnitude']='".Reportobjectmagnitude."';
        titles['objectsurfacebrightness']='".Reportobjectsurfacebrightness."';
        titles['objectradecl']='".Reportobjectradecl."';
        titles['objectra']='".Reportobjectra."';
        titles['objectdecl']='".Reportobjectdecl."';
        titles['objectsizepa']='".Reportobjectsizepa."';
        titles['objectdiam1']='".Reportobjectdiam1."';
        titles['objectdiam2']='".Reportobjectdiam2."';
        titles['objectsize']='".Reportobjectsize."';
        titles['objectpa']='".Reportobjectpa."';
        titles['objectuseratlaspage']='".Reportobjectuseratlaspage."';
        titles['objectdescription']='".Reportobjectdescription."';
        titles['objectcontrast']='".Reportobjectcontrast."';
        titles['objectcontrastpopup']='".Reportobjectcontrastpopup."';
        titles['objectcontrasttype']='".Reportobjectcontrasttype."';
        titles['objectoptimalmagnification']='".Reportobjectoptimalmagnification."';
        titles['objectoptimalmagnificationvalue']='".Reportobjectoptimalmagnificationvalue."';
        titles['objectrise']='".Reportobjectrise."';
        titles['objectrisepopup']='".Reportobjectrisepopup."';
        titles['objecttransit']='".Reportobjecttransit."';
        titles['objecttransitpopup']='".Reportobjecttransitpopup."';
        titles['objectset']='".Reportobjectset."';
        titles['objectsetpopup']='".Reportobjectsetpopup."';
        titles['objectbest']='".Reportobjectbest."';
        titles['objectbestpopup']='".Reportobjectbestpopup."';
        titles['objectmaxaltitude']='".Reportobjectmaxaltitude."';
        titles['objectmaxaltitudepopup']='".Reportobjectmaxaltitudepopup."';
        titles['objectmaxalt']='".LangReportObjectHighestAlt."';
        titles['objectmaxaltstarttext']='".LangReportObjectHighestFrom."';
        titles['objectmaxaltmidtext']='".LangReportObjectHighestAround."';
        titles['objectmaxaltendtext']='".LangReportObjectHighestTo."';

        thereport='".$reportName."';
	      setLayoutPage();
	      /* ]]> */
	      </script>";
	echo "</div>";
}
?>
