<?php
function reportsLayout()
{ global $baseURL,$objPresentations,$objReportLayout,$objUtil,$loggedUserName;
  echo   "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/reportlayouts.js\"></script>";
  $reportName=$objUtil->checkGetKey('reportname');
  $reportTitle=$objUtil->checkGetKey('reporttitle');
  echo "<div id=\"main\">";
  $objPresentations->line(array("<h4>".ReportTitle.constant($reportTitle)."</h4>"),"L",array(100),40);
  $objPresentations->line(array("<hr />"),"L",array(100));
  $defaults=$objReportLayout->getLayoutListDefault($reportName);
  echo ReportKnownLayouts;
  echo "<select id=\"reportlayoutselect\" name=\"reportlayoutselect\" onchange=\"setLayoutPage('".$loggedUserName."');\">";
  while(list($key, $value) = each($defaults))
    if($value['observerid']=="Deepskylog")
      echo "<option value=\"".$value['observerid'].': '.$value['reportlayout']."\">".$value['observerid'].': '.$value['reportlayout']."</option>";
  echo "<option value=\"\" selected=\"selected\" >"."-----"."</option>";
  reset($defaults);
  while(list($key, $value) = each($defaults))
    if($value['observerid']==$loggedUserName)
      echo "<option value=\"".$value['observerid'].': '.$value['reportlayout']."\">".$value['observerid'].': '.$value['reportlayout']."</option>";
  echo "<option value=\"\" selected=\"selected\" >"."-----"."</option>";
  reset($defaults);
  while(list($key, $value) = each($defaults))
    if(($value['observerid']!="Deepskylog")&&($value['observerid']!=$loggedUserName))
      echo "<option value=\"".$value['observerid'].': '.$value['reportlayout']."\">".$value['observerid'].': '.$value['reportlayout']."</option>";
  echo "</select>";
  echo "&nbsp;";
  echo "<input type=\"button\" onclick=\"saveAndGeneratePdf('".$baseURL."report.pdf','".$reportName."','".$objUtil->checkRequestKey('pdfTtile',"DeepskyLog")."','".$_GET['SID']."','".$_GET['sort']."');\" value=\"".ReportSaveAndGeneratePdf."\"/>";
  echo "<input type=\"button\" onclick=\"saveAsLayoutPage('".$reportName."');\" value=\"".ReportSaveAs."\"/>";
  echo "<input type=\"button\" id=\"deletelayout\" class=\"hidden\" onclick=\"deleteLayoutPage('".$reportName."');\" value=\"".ReportDelete."\"/>";
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
        
        thereport='".$reportName."';
	      setLayoutPage();
	      /* ]]> */ 
	      </script>";
}
reportsLayout();
?>