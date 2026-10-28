<?php

namespace App\Support\Exports;

use PowerComponents\LivewirePowerGrid\Plugins\Export\OpenSpout\v5\ExportToCsv as PowerGridExportToCsv;

class ExportToCsv extends PowerGridExportToCsv
{
    use EscapesFormulas;
}
