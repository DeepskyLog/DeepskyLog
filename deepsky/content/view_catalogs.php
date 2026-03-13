<?php
global $instDir, $baseURL;

echo "<script type=\"text/javascript\" src=\"" . $baseURL . "deepsky/content/view_catalogs.js\"></script>";
echo '<div id="catalogs" class="catalogs">';

// Show a drop-down with all catalogs
if (!empty($instDir) && file_exists($instDir . "/lib/catalogs.php")) {
  include_once $instDir . "/lib/catalogs.php";
} else {
  // fallback to repository lib directory
  include_once __DIR__ . '/../../lib/catalogs.php';
}

$objCatalog = class_exists('catalogs') ? new catalogs() : null;
$catalogs = is_object($objCatalog) ? $objCatalog->getCatalogs() : array();

echo '<form>
<div class="form-group">
<select class="form-control" onchange="view_catalog(this.value);">';
foreach ($catalogs as $key => $value) {
  print '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</option>';
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
