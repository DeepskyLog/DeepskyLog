<?php

/**
 * Instrument DataTable.
 *
 * PHP Version 7
 *
 * @category Instruments
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\DataTables;

use App\Models\Instrument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Services\DataTable;

/**
 * Instrument DataTable.
 *
 * PHP Version 7
 *
 * @category Instruments
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
        if ($this->user === 'admin') {
            $model = Instrument::with('user')->select('instruments.*');
        } else {
            $model = Instrument::where(
                'user_id',
                auth()->user()->id
            )->with('user')->select('instruments.*');
        }

        $model->when(
            Auth::user()->showInches,
            function ($model) {
                return $model->select()->addSelect(
                    DB::raw('round(diameter * fd / 25.4, 2) as focalLength')
                );
            },
            function ($model) {
                return $model->select()->addSelect(
                    DB::raw('round(diameter * fd)  as focalLength')
                );
            }
        );

        return datatables()
            ->eloquent($model)
            ->editColumn(
                'name',
                function ($instrument) {
                    return '<a href="/instrument/' .
                        $instrument->id . '">' .
                        $instrument->name . '</a>';
                }
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
                'focalLength',
                function ($instrument) {
                    if ($instrument->focalLength) {
                        if (Auth::user()->showInches) {
                            return $instrument->focalLength . ' ' . _i('inch');
                        } else {
                            return $instrument->focalLength . ' ' . _i('mm');
                        }
                    }
                }
            )->editColumn(
                'observations',
                '<a href="/observations/instrument/{{ $id }}">TODO</a>'
            )->editColumn(
                'active',
                '<form method="POST" action="/instrument/{{ $id }}">
                    @method("PATCH")
                    @csrf
                    <input type="checkbox" name="active" onChange="this.form.submit()" {{ $active ? "checked" : "" }}>
                 </form>'
            )->editColumn(
                'user.name',
                function ($instrument) {
                    return '<a href="/users/' . $instrument->user->slug . '">'
                        . $instrument->user->name . '</a>';
                }
            )->addColumn(
                'standard',
                function ($instrument) {
                    if ($instrument->id == Auth::user()->stdtelescope) {
                        return '<input type="radio" name="stdinstrument" value="'
                            . $instrument->id
                            . '" checked="checked" onclick="submit();" />';
                    } else {
                        if ($instrument->active) {
                            return '<input type="radio" name="stdinstrument" value="'
                            . $instrument->id
                            . '" onclick="submit();" />';
                        } else {
                            return '<input type="radio" name="stdinstrument" value="'
                            . $instrument->id
                            . '" disabled />';
                        }
                    }
                }
            )->addColumn(
                'delete',
                '<form method="POST" action="/instrument/{{ $id }}">
                            @method("DELETE")
                            @csrf
                            <button type="button" class="btn btn-sm btn-link" onClick="this.form.submit()">
                            <svg width="1.3em" height="1.3em" viewBox="0 0 16 16" class="bi bi-trash icon" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                            </svg>
                        </button>
                        </form>'
            )->rawColumns(
                ['name', 'observations', 'active', 'delete',
                    'user.name', 'standard', ]
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
        $language = ['url' => 'http://cdn.datatables.net/plug-ins/1.10.20/i18n/'
            . \PeterColes\Languages\LanguagesFacade::lookup(
                [\deepskylog\LaravelGettext\Facades\LaravelGettext::getLocaleLanguage()],
                'en'
            )->first()
            . '.json', ];
        $mypars             = $this->getBuilderParameters();
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
                ['name'     => 'name',
                    'title' => _i('Name'),
                    'data'  => 'name',
                ],
                ['name'          => 'type',
                    'title'      => _i('Type'),
                    'data'       => 'type',
                    'width'      => '10%',
                    'searchable' => false,
                ],
                ['name'          => 'diameter',
                    'title'      => _i('Diameter'),
                    'data'       => 'diameter',
                    'width'      => '10%',
                    'searchable' => false,
                ],
                ['name'          => 'fd',
                    'title'      => _i('F/D'),
                    'data'       => 'fd',
                    'width'      => '10%',
                    'searchable' => false,
                ],
                ['name'          => 'focalLength',
                    'title'      => _i('Focal Length'),
                    'data'       => 'focalLength',
                    'searchable' => false,
                    'width'      => '10%',
                ],
                ['name'          => 'fixedMagnification',
                    'title'      => _i('Fixed Magnification'),
                    'data'       => 'fixedMagnification',
                    'width'      => '10%',
                    'searchable' => false,
                ],
                ['name'          => 'observations',
                    'title'      => _i('Observations'),
                    'data'       => 'observations',
                    'width'      => '10%',
                    'searchable' => false,
                ],
                ['name'          => 'delete',
                    'title'      => _i('Delete'),
                    'data'       => 'delete',
                    'orderable'  => false,
                    'searchable' => false,
                    'width'      => '10%',
                ],
                ['name'          => 'user.name',
                    'title'      => _i('Observer'),
                    'data'       => 'user.name',
                    'orderable'  => true,
                    'searchable' => true,
                ],
            ];
        } else {
            return [
                ['name'     => 'name',
                    'title' => _i('Name'),
                    'data'  => 'name',
                ],
                ['name'          => 'type',
                    'title'      => _i('Type'),
                    'data'       => 'type',
                    'width'      => '10%',
                    'searchable' => false,
                ],
                ['name'          => 'diameter',
                    'title'      => _i('Diameter'),
                    'data'       => 'diameter',
                    'width'      => '10%',
                    'searchable' => false,
                ],
                ['name'          => 'fd',
                    'title'      => _i('F/D'),
                    'data'       => 'fd',
                    'width'      => '10%',
                    'searchable' => false,
                ],
                ['name'          => 'focalLength',
                    'title'      => _i('Focal Length'),
                    'data'       => 'focalLength',
                    'width'      => '10%',
                    'searchable' => false,
                ],
                ['name'          => 'fixedMagnification',
                    'title'      => _i('Fixed Magnification'),
                    'data'       => 'fixedMagnification',
                    'width'      => '10%',
                    'searchable' => false,
                ],
                ['name'          => 'observations',
                    'title'      => _i('Observations'),
                    'data'       => 'observations',
                    'width'      => '10%',
                    'searchable' => false,
                ],
                ['name'     => 'active',
                    'title' => _i('Active'),
                    'data'  => 'active',
                ],
                ['name'          => 'standard',
                    'title'      => _i('Default Instrument'),
                    'data'       => 'standard',
                    'orderable'  => false,
                    'searchable' => false,
                ],
                ['name'          => 'delete',
                    'title'      => _i('Delete'),
                    'data'       => 'delete',
                    'orderable'  => false,
                    'searchable' => false,
                    'width'      => '10%',
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
