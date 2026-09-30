<?php

namespace App\Support\Exports;

use PowerComponents\LivewirePowerGrid\Components\Exports\OpenSpout\v5\ExportToXLS as PowerGridExportToXLS;

class ExportToXLS extends PowerGridExportToXLS
{
    use EscapesFormulas;
}
