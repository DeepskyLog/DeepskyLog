<?php

namespace App\Support\Exports;

use PowerComponents\LivewirePowerGrid\Components\Exports\OpenSpout\v5\ExportToCsv as PowerGridExportToCsv;

class ExportToCsv extends PowerGridExportToCsv
{
    use EscapesFormulas;
}
