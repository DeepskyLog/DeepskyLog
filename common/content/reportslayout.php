<?php
function reportsLayout()
{ global $baseURL,$objPresentations,$objReportLayout,$objUtil;
  echo   "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/reportlayouts.js\"></script>";
  $reportName=$objUtil->checkGetKey('reportname');
  $reportTitle=$objUtil->checkGetKey('reporttitle');
  echo "<div id=\"main\">";
  $objPresentations->line(array("<h4>Reports Layout for ".$reportTitle."</h4>"),"L",array(100),40);
  $objPresentations->line(array("<hr />"),"L",array(100));
  $defaults=$objReportLayout->getLayoutListDefault($reportName);
  echo "Known layouts: ";
  echo "<select id=\"reportlayoutselect\" name=\"reportlayoutselect\" onchange=\"setLayoutPage();\">";
  echo "<option value=\"\">"."-----"."</option>";
  while(list($key, $value) = each($defaults))
    echo "<option value=\"".$value."\">".$value."</option>";
  echo "</select>";
  echo "&nbsp;";
  echo "<input type=\"button\" onclick=\"saveLayoutPage('".$baseURL."report.pdf','".$reportName."','".$objUtil->checkRequestKey('pdfTtile',"DeepskyLog")."','".$_GET['SID']."','".$_GET['sort']."');\" value=\"Generate pdf\"/>";
  echo "<input type=\"button\" onclick=\"savereportlayout();\" value=\"Save as...\"/>";
  echo "</div>";
  echo "<div id=\"reportlayout\">";
  echo "</div>";
	echo "<script type=\"text/javascript\">
	      /* <![CDATA[ */ 
        thereport='".$reportName."';
	      /* ]]> */ 
	      </script>";
}
reportsLayout();
?>