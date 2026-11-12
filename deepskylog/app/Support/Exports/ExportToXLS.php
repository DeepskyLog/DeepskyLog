<?php

namespace App\Support\Exports;

use PowerComponents\LivewirePowerGrid\Plugins\Export\OpenSpout\v5\ExportToXLS as PowerGridExportToXLS;

class ExportToXLS extends PowerGridExportToXLS
{
    use EscapesFormulas;
    use WritesOwnFile;

    protected function extension(): string
    {
        return 'xlsx';
    }
}
