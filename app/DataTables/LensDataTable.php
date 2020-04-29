<?php

/**
 * Lens DataTable.
 *
 * PHP Version 7
 *
 * @category Lenses
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\DataTables;

use App\Lens;
use Yajra\DataTables\Services\DataTable;

/**
 * Lens DataTable.
 *
 * PHP Version 7
 *
 * @category Lenses
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class LensDataTable extends DataTable
{
    /**
     * Make the correct ajax call.
     *
     * @return datatables the Correct ajax call
     */
    public function ajax()
    {
        if ($this->user === 'admin') {
            $model = Lens::with('user')->select('lens.*');
        } else {
            $model = Lens::where(
                'user_id',
                auth()->user()->id
            )->with('user')->select('lens.*');
        }

        return datatables()
            ->eloquent($model)
            ->editColumn(
                'name',
                '<a href="/lens/{{ $id }}">{{ $name }}</a>'
            )->editColumn(
                'observations',
                '<a href="/observations/lens/{{ $id }}">TODO</a>'
            )->editColumn(
                'user.name',
                function ($lens) {
                    return '<a href="/users/'.$lens->user->id.'">'
                        .$lens->user->name.'</a>';
                }
            )->editColumn(
                'active',
                '<form method="POST" action="/lens/{{ $id }}">
                    @method("PATCH")
                    @csrf
                    <input type="checkbox" name="active" onChange="this.form.submit()" {{ $active ? "checked" : "" }}>
                 </form>'
            )->addColumn(
                'delete',
                '<form method="POST" action="/lens/{{ $id }}">
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
        $language = ['url' => 'http://cdn.datatables.net/plug-ins/1.10.20/i18n/'
            .\PeterColes\Languages\LanguagesFacade::lookup(
                [\deepskylog\LaravelGettext\Facades\LaravelGettext::getLocaleLanguage()],
                'en'
            )->first()
            .'.json', ];
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
                ['name' => 'factor',
                    'title' => _i('Factor'),
                    'data' => 'factor',
                    'width' => '10%',
                    'searchable' => true,
                ],
                ['name' => 'observations',
                    'title' => _i('Observations'),
                    'data' => 'observations',
                    'width' => '10%',
                    'searchable' => true,
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
                ['name' => 'factor',
                    'title' => _i('Factor'),
                    'data' => 'factor',
                    'searchable' => true,
                ],
                ['name' => 'observations',
                    'title' => _i('Observations'),
                    'data' => 'observations',
                    'searchable' => true,
                ],
                ['name' => 'active',
                    'title' => _i('Active'),
                    'data' => 'active',
                    'searchable' => true,
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
        return 'Lens_'.date('YmdHis');
    }
}
