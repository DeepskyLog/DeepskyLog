<?php

/**
 * Target DataTable.
 *
 * PHP Version 7
 *
 * @category Targets
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\DataTables;

use Yajra\DataTables\Services\DataTable;
use App\Target;

/**
 * Target DataTable.
 *
 * PHP Version 7
 *
 * @category Targets
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class TargetDataTable extends DataTable
{
    /**
     * Make the correct ajax call.
     *
     * @return datatables the Correct ajax call
     */
    public function ajax()
    {
        $model = $this->query();

        return datatables()
            ->eloquent($model)
            ->editColumn(
                'name',
                function ($target) {
                    return '<a href="/target/' . $target->name . '">'
                        . $target->name . '</a>';
                }
            )
            ->editColumn(
                'constellation',
                function ($target) {
                    return $target->constellation()->first()['name'];
                }
            )
            ->editColumn(
                'realType',
                function ($target) {
                    return _i($target->type()->first()['type']);
                }
            )
            ->editColumn(
                'size',
                function ($target) {
                    if ($target->pa != 999) {
                        return $target->size() . '/' . $target->pa . '°';
                    } else {
                        return $target->size();
                    }
                }
            )
            ->editColumn(
                'ra',
                function ($target) {
                    return $target->ra();
                }
            )
            ->editColumn(
                'decl',
                function ($target) {
                    return $target->declination();
                }
            )
            ->editColumn(
                'atlas',
                function ($target) {
                    return $target->atlasPage(auth()->user()->standardAtlasCode);
                }
            )
            ->editColumn(
                'contrast',
                function ($target) {
                    $contrast = new \App\Contrast($target);

                    return '<span class="' . $contrast->contype
                        . '" data-toggle="tooltip" data-placement="bottom" title="'
                        . $contrast->popup . '">' . $contrast->contrast . '</span>';
                }
            )
            ->rawColumns(
                ['name', 'contrast']
            )->make(true);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())->minifiedAjax()
            ->parameters($this->getMyParameters());
    }

    /**
     * Get query source of dataTable.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $zoom = 30;
        if (isset($_GET['zoom'])) {
            $zoom = $_GET['zoom'];
        }
        $target = $this->target;

        $targets = $target->getNearbyObjects($zoom)->select();

        return $this->applyScopes($targets);
    }

    /**
     * Returns the parameters and also add the correct translation to the datatables.
     *
     * @return array The parameters
     */
    protected function getMyParameters()
    {
        $language = ['url' => 'http://cdn.datatables.net/plug-ins/1.10.19/i18n/'
        . \PeterColes\Languages\LanguagesFacade::lookup(
            [\Xinax\LaravelGettext\Facades\LaravelGettext::getLocaleLanguage()],
            'en'
        )->first()
            . '.json'];
        $mypars = $this->getBuilderParameters();
        $mypars['language'] = $language;

        return $mypars;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            ['name' => 'name',
                'title' => _i('Name'),
                'data' => 'name',
            ],
            ['name' => 'con',
                'title' => _i('Constellation'),
                'data' => 'constellation',
            ],
            ['name' => 'con',
                'title' => _i('Const.'),
                'data' => 'con',
                'width' => '10%',
            ],
            ['name' => 'Mag',
                'title' => _i('Mag'),
                'data' => 'mag',
                'width' => '10%',
                'searchable' => false,
            ],
            ['name' => 'subr',
                'title' => _i('SB'),
                'data' => 'subr',
                'width' => '10%',
                'searchable' => false,
            ],
            ['name' => 'Type',
                'title' => _i('Type'),
                'data' => 'realType',
            ],
            ['name' => 'Type',
                'title' => _i('Typ'),
                'data' => 'type',
                'searchable' => false,
            ],
            ['name' => 'Size',
                'title' => _i('Size'),
                'data' => 'size',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'RA',
                'title' => _i('RA'),
                'data' => 'ra',
                'searchable' => false,
            ],
            ['name' => 'Decl',
                'title' => _i('Decl'),
                'data' => 'decl',
                'searchable' => false,
            ],
            ['name' => auth()->user()->standardAtlasCode,
                'title' => _i(
                    \App\Atlases::where(
                        'code', auth()->user()->standardAtlasCode
                    )->first()->name
                ),
                'data' => 'atlas',
                'searchable' => false,
            ],
            ['name' => 'contrast',
                'title' => _i('Contrast Reserve'),
                'data' => 'contrast',
                'searchable' => false,
            ],
/*            ['name' => 'Best',
                'title' => _i('Best'),
                'data' => 'best',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'Rise',
                'title' => _i('Rise'),
                'data' => 'rise',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'Transit',
                'title' => _i('Transit'),
                'data' => 'transit',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'Set',
                'title' => _i('Set'),
                'data' => 'set',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'BestTime',
                'title' => _i('Best Time'),
                'data' => 'bestTime',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'MaxAlt',
                'title' => _i('Max Alt'),
                'data' => 'maxAlt',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'Seen',
                'title' => _i('Seen'),
                'data' => 'seen',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'LastSeen',
                'title' => _i('Last Seen'),
                'data' => 'lastSeen',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'HighestAlt',
                'title' => _i('Highest Alt.'),
                'data' => 'highestAlt',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'HighestFrom',
                'title' => _i('Highest from'),
                'data' => 'highestFrom',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'HighestTo',
                'title' => _i('Highest to'),
                'data' => 'highestTo',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'HighestAround',
                'title' => _i('Highest around'),
                'data' => 'highestAround',
                'orderable' => false,
                'searchable' => false,
            ],
*/
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Targets_' . date('YmdHis');
    }
}
