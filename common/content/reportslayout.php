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
        titles['startpagenumber']='"._("First page number")."';
        titles['top']='"._("Top margin position for text (y-coordinate)")."';
        titles['header']='"._("Position of the header line (y-coordinate)")."';
        titles['xleft']='"._("Position of the 1st column (x-coordinate)")."';
        titles['bottom']='"._("Bottom margin position for the text (y-coordinate)")."';
        titles['footer']='"._("Position of the footer line (y-coordinate)")."';
        titles['xmid']='"._("Position of the 2nd column (x-coordinate)")."';
        titles['fontSizeText']='"._("Font size text")."';
        titles['fontSizeSection']='"._("Font size for section bar")."';
        titles['deltalineextra']='"._("Extra line separation")."';
        titles['sectionBarHeightextra']='"._("Extra section bar height")."';
        titles['deltalineSection']='"._("Extra space after a section bar")."';
        titles['deltaobjectline']='"._("Extra separation between the different objects")."';
        titles['SectionBarWidthbase']='"._("Width of the section title bars (e.g. constellation)")."';
        titles['sectionbarspace']='"._("Reverse indent of the section bar")."';
        titles['showelements']='"._("Show (t) title, (h) made up for, (e) efemerides, (p) page numbers, (l) legend, (s) separation lines, (i) index")."';
        titles['objectseen']='"._("Object seen by you or others")."';
        titles['objectlastseen']='"._("Object last seen date")."';
        titles['showname']='"._("Object name as in search (e.g. Caldwell number)")."';
        titles['objectname']='"._("Object principal name")."';
        titles['altname']='"._("Alternative object names")."';
        titles['objecttype']='"._("Object type abbreviation")."';
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
