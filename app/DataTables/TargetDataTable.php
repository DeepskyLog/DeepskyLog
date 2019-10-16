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
        if ($this->user === 'admin') {
            $model = Target::with('user')->select('targets.*');
        } else {
            $model = Target::where(
                'user_id',
                auth()->user()->id
            )->with('user')->select('targets.*');
        }

        return datatables()
            ->eloquent($model)
            ->editColumn(
                'name',
                '<a href="/target/{{ $id }}">{{ $name }}</a>'
            )->editColumn(
                'type',
                function ($target) {
                    return $target->typeName();
                }
            )->editColumn(
                'color',
                function ($target) {
                    return $target->colorName();
                }
            )->editColumn(
                'observations',
                '<a href="/observations/target/{{ $id }}">{{ $observations }}</a>'
            )->editColumn(
                'user.name',
                function ($target) {
                    return '<a href="/users/' . $target->user->id . '">'
                        . $target->user->name . '</a>';
                }
            )->editColumn(
                'active',
                '<form method="POST" action="/target/{{ $id }}">
                    @method("PATCH")
                    @csrf
                    <input type="checkbox" name="active" onChange="this.form.submit()" {{ $active ? "checked" : "" }}>
                 </form>'
            )->addColumn(
                'delete',
                '<form method="POST" action="/target/{{ $id }}">
                            @method("DELETE")
                            @csrf
                            <button type="button" class="btn btn-sm btn-link" onClick="this.form.submit()">
                            <i class="far fa-trash-alt"></i>
                        </button>
                        </form>'
            )->rawColumns(
                ['name', 'observations', 'active', 'delete', 'user.name']
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
            ['name' => 'Constellation',
                'title' => _i('Constellation'),
                'data' => 'constellation',
            ],
            ['name' => 'Const.',
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
            ['name' => 'SB',
                'title' => _i('SB'),
                'data' => 'subr',
                'width' => '10%',
                'searchable' => false,
            ],
            ['name' => 'Type',
                'title' => _i('Type'),
                'data' => 'realType',
                'searchable' => false,
            ],
            ['name' => 'Typ',
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
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'Decl',
                'title' => _i('Decl'),
                'data' => 'decl',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'Atlas',
                'title' => _i('atlas'),
                'data' => 'atlas',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'ContrastReserve',
                'title' => _i('Contrast Reserve'),
                'data' => 'contrast',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'Best',
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
