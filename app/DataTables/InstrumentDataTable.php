<?php

/**
 * Instrument DataTable.
 *
 * PHP Version 7
 *
 * @category Instruments
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\DataTables;

use Yajra\DataTables\Services\DataTable;
use App\Instrument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Instrument DataTable.
 *
 * PHP Version 7
 *
 * @category Instruments
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class InstrumentDataTable extends DataTable
{
    /**
     * Make the correct ajax call.
     *
     * @return datatables the Correct ajax call
     */
    public function ajax()
    {
        $query = $this->query();

        $query->when(
            Auth::user()->showInches, function ($query) {
                return $query->select()->addSelect(
                    DB::raw('round(diameter * fd / 25.4, 2) as focalLength')
                );
            }, function ($query) {
                return $query->select()->addSelect(
                    DB::raw('round(diameter * fd)  as focalLength')
                );
            }
        );

        return datatables()
            ->eloquent($query)
            ->addColumn(
                'observername',
                function ($instrument) {
                    return '<a href="/observer/' . $instrument->observer_id . '">'
                        . $instrument->observer->name . '</a>';
                }
            )->editColumn(
                'name',
                '<a href="/instrument/{{ $id }}/edit">{{ $name }}</a>'
            )->editColumn(
                'type',
                function ($instrument) {
                    return $instrument->typeName();
                }
            )->editColumn(
                'diameter',
                function ($instrument) {
                    if (Auth::user()->showInches) {
                        return round($instrument->diameter / 25.4, 2) . ' ' . _i('inch');
                    } else {
                        return $instrument->diameter . ' ' . _i('mm');
                    }
                }
            )->editColumn(
                'observations',
                '<a href="/observations/instrument/{{ $id }}">{{ $observations }}</a>'
            )->editColumn(
                'active',
                '<form method="POST" action="/instrument/{{ $id }}">
                    @method("PATCH")
                    @csrf
                    <input type="checkbox" name="active" onChange="this.form.submit()" {{ $active ? "checked" : "" }}>
                 </form>'
            )->addColumn(
                'standard',
                function ($instrument) {
                    if ($instrument->id == Auth::user()->stdtelescope) {
                        return '<input type="radio" name="stdtelescope" value="'
                            . $instrument->id
                            . '" checked="checked" onclick="submit();" />';
                    } else {
                        return '<input type="radio" name="stdtelescope" value="'
                            . $instrument->id
                            . '" onclick="submit();" />';
                    }
                }
            )->addColumn(
                'delete',
                '<form method="POST" action="/instrument/{{ $id }}">
                            @method("DELETE")
                            @csrf
                            <button type="button" class="btn btn-sm btn-link" onClick="this.form.submit()">
                            <i class="far fa-trash-alt"></i>
                        </button>
                        </form>'
            )->rawColumns(
                ['name', 'observations', 'active', 'delete',
                'observername', 'standard']
            )->make(true);
    }

    /**
     * Get query source of dataTable.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        if ($this->user === 'admin') {
            $instruments = Instrument::select();
        } else {
            $instruments = auth()->user()->instruments();
        }

        return $this->applyScopes($instruments);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        if ($this->user === 'admin') {
            return $this->builder()
                ->columns($this->getColumns())->minifiedAjax()
                ->addColumn(
                    ['data' => 'observername', 'title' => _i('Name'),
                        'name' => 'observername',
                        'orderable' => false,
                        'searchable' => false,
                    ]
                )->parameters($this->getMyParameters());
        } else {
            return $this->builder()
                ->columns($this->getColumns())->minifiedAjax()
                ->parameters($this->getMyParameters());
        }
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
        if ($this->user === 'admin') {
            return [
                ['name' => 'name',
                    'title' => _i('Name'),
                    'data' => 'name',
                ],
                ['name' => 'type',
                    'title' => _i('Type'),
                    'data' => 'type',
                    'width' => '10%',
                ],
                ['name' => 'diameter',
                    'title' => _i('Diameter'),
                    'data' => 'diameter',
                    'width' => '10%',
                ],
                ['name' => 'fd',
                    'title' => _i('F/D'),
                    'data' => 'fd',
                    'width' => '10%',
                ],
                ['name' => 'focalLength',
                    'title' => _i('Focal Length'),
                    'data' => 'focalLength',
                    'width' => '10%',
                ],
                ['name' => 'fixedMagnification',
                    'title' => _i('Fixed Magnification'),
                    'data' => 'fixedMagnification',
                    'width' => '10%',
                ],
                ['name' => 'observations',
                    'title' => _i('Observations'),
                    'data' => 'observations',
                    'width' => '10%',
                ],
                ['name' => 'delete',
                    'title' => _i('Delete'),
                    'data' => 'delete',
                    'orderable' => false,
                    'searchable' => false,
                    'width' => '10%',
                ],
            ];
        } else {
            return [
                ['name' => 'name',
                    'title' => _i('Name'),
                    'data' => 'name',
                ],
                ['name' => 'type',
                    'title' => _i('Type'),
                    'data' => 'type',
                    'width' => '10%',
                ],
                ['name' => 'diameter',
                    'title' => _i('Diameter'),
                    'data' => 'diameter',
                    'width' => '10%',
                ],
                ['name' => 'fd',
                    'title' => _i('F/D'),
                    'data' => 'fd',
                    'width' => '10%',
                ],
                ['name' => 'focalLength',
                    'title' => _i('Focal Length'),
                    'data' => 'focalLength',
                    'width' => '10%',
                ],
                ['name' => 'fixedMagnification',
                    'title' => _i('Fixed Magnification'),
                    'data' => 'fixedMagnification',
                    'width' => '10%',
                ],
                ['name' => 'observations',
                    'title' => _i('Observations'),
                    'data' => 'observations',
                    'width' => '10%',
                ],
                ['name' => 'active',
                    'title' => _i('Active'),
                    'data' => 'active',
                ],
                ['name' => 'standard',
                    'title' => _i('Default Instrument'),
                    'data' => 'standard',
                    'orderable' => false,
                    'searchable' => false,
                ],
                ['name' => 'delete',
                    'title' => _i('Delete'),
                    'data' => 'delete',
                    'orderable' => false,
                    'searchable' => false,
                    'width' => '10%',
                ],
            ];
        }
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Instrument_' . date('YmdHis');
    }
}
