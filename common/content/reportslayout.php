<?php
function reportsLayout()
{ global $baseURL,$objPresentations,$objReportLayout,$objUtil;
  $reportName=$objUtil->checkGetKey('reportname');
  $reportTitle=$objUtil->checkGetKey('reporttitle');
  echo "<div id=\"main\">";
  $objPresentations->line(array("<h4>Reports Layout for ".$reportTitle."</h4>"),"L",array(100),40);
  $objPresentations->line(array("<hr />"),"L",array(100));
  $defaults=$objReportLayout->getLayoutListDefault($reportName);
  echo "Known layouts: ";
  echo "<select id=\"layouts\" name=\"layouts\">";
  echo "<option value=\"\">"."-----"."</option>";
  while(list($key, $value) = each($defaults))
    echo "<option value=\"reportuser=defaultuser&amp;reportlayout=".$value."\">".$value."</option>";
  echo "</select>";
  echo "&nbsp;";
  echo "<input type=\"button\" onclick=\"location.href=('".$baseURL."report.pdf?reportname=".$reportName."&amp;pdfTitle=".$objUtil->checkRequestKey('pdfTtile',"DeepskyLog")."&amp;SID=".$_GET['SID']."&amp;sort=".$_GET['sort']."&amp;'+document.getElementById('layouts').value);\" value=\"Generate pdf\"/>";
  echo "</div>";
}
reportsLayout();
?>