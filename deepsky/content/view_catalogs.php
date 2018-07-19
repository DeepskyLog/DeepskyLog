<?php
global $instDir;

echo "<script type=\"text/javascript\" src=\"" . $baseURL . "deepsky/content/view_catalogs.js\"></script>";
echo '<div id="catalogs" class="catalogs">';

// Show a drop-down with all catalogs
include_once $instDir . "/lib/catalogs.php";
$objCatalog = new catalogs();
$catalogs = $objCatalog->getCatalogs();

echo '<form>
<div class="form-group">
<select class="form-control" onchange="view_catalog(this.value);">';
foreach ($catalogs as $key => $value) {
  print '<option><value="' . $value . '">' . $value . '</a></option>';
}
echo '</select>';
echo '  </div>
       </form>';
echo '<br /><br />';
echo '<div id="view_catalogs_right" class="view_catalogs_right">';
echo _('Click on a list to view its details');
echo '</div>';
echo '</div>';
?>
