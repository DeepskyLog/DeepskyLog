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
  echo "<h4>"._("Reports Layout for ").constant($reportTitle)."</h4>";
  echo "<hr />";
  echo "<div class=\"form-inline\">";
  $defaults=$objReportLayout->getLayoutListDefault($reportName);
  echo _("Known layouts");
  echo " ("._('show all')." "."<input id=\"showallcheckbox\" type=\"checkbox\" onchange=\"showSelectOptions('".$reportName."');\" />"."):&nbsp;";
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
  echo "<input type=\"button\" class=\"btn btn-primary\" onclick=\"saveAndGeneratePdf('".$baseURL."report.pdf.php','".$reportName."','".$objUtil->checkRequestKey('pdfTtile',"DeepskyLog")."','".$_GET['SID']."');\" value=\""
    . _("Save and Generate pdf") ."\"/>";
  echo "&nbsp;<input type=\"button\" class=\"btn btn-primary\" onclick=\"saveAsLayoutPage('".$reportName."');\" value=\""
    . _("Save as...") ."\"/>";
  echo "&nbsp;<input type=\"button\" class=\"btn btn-primary hidden\" id=\"deletelayout\" onclick=\"deleteLayoutPage('".$reportName."');\" value=\""
    . _("Delete") ."\"/>";
  echo "<input type=\"hidden\" id=\"tempname\" value=\"\" />";
  echo "<input type=\"hidden\" id=\"tempobserver\" value=\"".$loggedUserName."\" />";
  echo "</div>";
  echo "<div id=\"reportlayout\">";
  echo "</div>";
	echo "<script type=\"text/javascript\">
	      /* <![CDATA[ */
	      var titles=new Array();
        titles['ReportFieldname']='"._("Field name")."';
	      titles['ReportFieldlineposition']='"._("Field is on line ... (0=first)")."';
	      titles['ReportFieldxposition']='"._("Field is on x-position ... in column")."';
	      titles['ReportFieldwidth']='"._("Display width of the field")."';
	      titles['ReportFieldStyle']='"._("Font style: combination of<br />i (italic), b (bold) and l, r or c (alignment)")."';
	      titles['ReportFieldTextBefore']='"._("Text to be shown in front of the field")."';
	      titles['ReportFieldTextAfter']='"._("Text to be shown after the field")."';
	      titles['ReportFieldLegend']='"._("Legend text")."';
	      titles['pagesize']='"._("Page size (A4/A3/LETTER/...)")."';
	      titles['pageorientation']='"._("Page orientation (landscape/portrait)")."';
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
        titles['objectmaxalt']='"._("Highest altitude during the year")."';
        titles['objectmaxaltstarttext']='"._("Highest from the month")."';
        titles['objectmaxaltmidtext']='"._("Highest around the month")."';
        titles['objectmaxaltendtext']='"._("Highest till the month")."';

        thereport='".$reportName."';
	      setLayoutPage();
	      /* ]]> */
	      </script>";
	echo "</div>";
}
?>
