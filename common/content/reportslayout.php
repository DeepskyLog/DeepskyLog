<?php
function reportsLayout()
{ global   $objPresentations, $objReportLayout,$objUtil;
  $reportName=$objUtil->checkGetKey('reportname');
  $reportTitle=$objUtil->checkGetKey('reporttitle');
  echo "<div id=\"main\">";
  $objPresentations->line(array("<h4>Reports Layout for ".$reportTitle."</h4>"),"L",array(100),40);
  $objPresentations->line(array("<hr />"),"L",array(100));
  $defaults=$objReportLayout->getLayoutListDefault($reportName);
  echo "Known layouts: ";
  echo "<select name=\"layouts\">";
  echo "<option value=\"\">"."-----"."</option>";
  while(list($key, $value) = each($defaults))
    echo "<option value=\"".$value."\">".$value."</option>";
  echo "</select>";
  echo "</div>";
}
reportsLayout();
?>