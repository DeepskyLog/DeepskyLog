<?php
global $baseURL;

echo "<script type=\"text/javascript\" src=\"" . $baseURL . "deepsky/content/view_catalogs.js\"></script>";
echo '<div id="catalogs" class="catalogs">';

// Show a drop-down with all catalogs
include_once $baseURL . "/lib/catalogs.php";
$objCatalog = new catalogs();
$catalogs = $objCatalog->getCatalogs();

print '<select onchange="view_catalog(this.value);">';
foreach ($catalogs as $key => $value) {
//  print '<option><a href="#" onchange="view_catalog(\'' . $value . '\');return false;">' . $value . '</a></option>';
  print '<option><value="' . $value . '">' . $value . '</a></option>';
}
print '</select>';

// function show_catalogs(thecatalogs)
// { var thetext='';
// //  $thetext='<list>';
//   for(i=0;i<thecatalogs.length;i++)
// 	thetext+='<a href="#" onclick="view_catalog(\''+thecatalogs[i]+'\');return false;";>'+thecatalogs[i]+'</a><br />';
// //  $thetext+='</list>';
//   document.getElementById('view_catalogs_left').innerHTML=thetext;
// }


// echo '<div id="view_catalogs_left" class="view_catalogs_left">';
// echo LangBuildingCatalogList;
// echo '</div>';
echo '<div id="view_catalogs_right" class="view_catalogs_right">';
echo LangClickToViewCatalogDetails;
echo '</div>';
echo '</div>';
?>
