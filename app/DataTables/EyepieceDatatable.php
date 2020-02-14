<?php

/**
 * Eyepiece DataTable.
 *
 * PHP Version 7
 *
 * @category Eyepieces
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\DataTables;

use Yajra\DataTables\Services\DataTable;
use App\Eyepiece;

/**
 * Eyepiece DataTable.
 *
 * PHP Version 7
 *
 * @category Eyepieces
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class EyepieceDataTable extends DataTable
{
    /**
     * Make the correct ajax call.
     *
     * @return datatables the Correct ajax call
     */
    public function ajax()
    {
        if ($this->user === 'admin') {
            $model = Eyepiece::with('user')->select('eyepieces.*');
        } else {
            $model = Eyepiece::where(
                'user_id',
                auth()->user()->id
            )->with('user')->select('eyepieces.*');
        }

        return datatables()
            ->eloquent($model)
            ->editColumn(
                'name',
                '<a href="/eyepiece/{{ $id }}">{{ $name }}</a>'
            )
            ->editColumn(
                'observations',
                '<a href="/observations/eyepiece/{{ $id }}">{{ $observations }}</a>'
            )
            ->editColumn(
                'focalLength',
                function ($eyepiece) {
                    return $eyepiece->focalLength . ' mm';
                }
            )
            ->editColumn(
                'apparentFOV',
                function ($eyepiece) {
                    return $eyepiece->apparentFOV . ' º';
                }
            )
            ->editColumn(
                'maxFocalLength',
                function ($eyepiece) {
                    if ($eyepiece->maxFocalLength) {
                        return $eyepiece->maxFocalLength . ' mm';
                    } else {
                        return '';
                    }
                }
            )
            ->editColumn(
                'user.name',
                function ($eyepiece) {
                    return '<a href="/users/' . $eyepiece->user->id . '">'
                        . $eyepiece->user->name . '</a>';
                }
            )
            ->editColumn(
                'active',
                '<form method="POST" action="/eyepiece/{{ $id }}">
                @method("PATCH")
                @csrf
                <input type="checkbox" name="active" onChange="this.form.submit()" {{ $active ? "checked" : "" }}>
             </form>'
            )
            ->addColumn(
                'delete',
                '<form method="POST" action="/eyepiece/{{ $id }}">
                            @method("DELETE")
                            @csrf
                            <button type="button" class="btn btn-sm btn-link" onClick="this.form.submit()">
                            <i class="far fa-trash-alt"></i>
                        </button>
                        </form>'
            )
            ->rawColumns(
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
        $language = ['url' => 'http://cdn.datatables.net/plug-ins/1.10.20/i18n/'
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
                ['name' => 'focalLength',
                    'title' => _i('Focal Length'),
                    'data' => 'focalLength',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'brand',
                    'title' => _i('Brand'),
                    'data' => 'brand',
                    'width' => '10%',
                    'searchable' => true,
                ],
                ['name' => 'apparentFOV',
                    'title' => _i('Apparent Field of View'),
                    'data' => 'apparentFOV',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'type',
                    'title' => _i('Type'),
                    'data' => 'type',
                    'width' => '10%',
                    'searchable' => true,
                ],
                ['name' => 'maxFocalLength',
                    'title' => _i('Maximum Focal Length'),
                    'data' => 'maxFocalLength',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'observations',
                    'title' => _i('Observations'),
                    'data' => 'observations',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'delete',
                    'title' => _i('Delete'),
                    'data' => 'delete',
                    'orderable' => false,
                    'searchable' => false,
                    'width' => '10%',
                ],
                ['name' => 'user.name',
                    'title' => _i('Observer'),
                    'data' => 'user.name',
                    'orderable' => true,
                    'searchable' => true,
                ],
            ];
        } else {
            return [
                ['name' => 'name',
                    'title' => _i('Name'),
                    'data' => 'name',
                ],
                ['name' => 'focalLength',
                    'title' => _i('Focal Length'),
                    'data' => 'focalLength',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'brand',
                    'title' => _i('Brand'),
                    'data' => 'brand',
                    'width' => '10%',
                    'searchable' => true,
                ],
                ['name' => 'apparentFOV',
                    'title' => _i('Apparent Field of View'),
                    'data' => 'apparentFOV',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'type',
                    'title' => _i('Type'),
                    'data' => 'type',
                    'width' => '10%',
                    'searchable' => true,
                ],
                ['name' => 'maxFocalLength',
                    'title' => _i('Maximum Focal Length'),
                    'data' => 'maxFocalLength',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'observations',
                    'title' => _i('Observations'),
                    'data' => 'observations',
                    'searchable' => false,
                ],
                ['name' => 'active',
                    'title' => _i('Active'),
                    'data' => 'active',
                ],
                ['name' => 'delete',
                    'title' => _i('Delete'),
                    'data' => 'delete',
                    'orderable' => false,
                    'searchable' => false,
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
        return 'Eyepiece_' . date('YmdHis');
    }
}
