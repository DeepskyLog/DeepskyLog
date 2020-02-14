<?php

/**
 * User DataTable.
 *
 * PHP Version 7
 *
 * @category Authentication
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\DataTables;

use App\User;
use Yajra\DataTables\Services\DataTable;

/**
 * User DataTable.
 *
 * PHP Version 7
 *
 * @category Authentication
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class UserDataTable extends DataTable
{
    /**
     * Make the correct ajax call.
     *
     * @return datatables the Correct ajax call
     */
    public function ajax()
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn(
                'username',
                '<a href="/users/{{ $id }}/edit">{{ $username }}</a>'
            )->editColumn(
                'name',
                '<a href="/users/{{ $id }}/edit">{{ $name }}</a>'
            )->editColumn(
                'email',
                '<a href="mailto:{{ $email }}">{{ $email }}</a>'
            )->editColumn(
                'created_at',
                function ($user) {
                    return date('F d, Y H:i', strtotime($user->created_at));
                }
            )->addColumn(
                'delete',
                '<form method="POST" action="/users/{{ $id }}">
                            @method("DELETE")
                            @csrf
                            <button type="button" class="btn btn-sm btn-link" onClick="this.form.submit()">
                            <i class="far fa-trash-alt"></i>
                        </button>
                        </form>'
            )->addColumn(
                // TODO: Add the correct number of observations
                // TODO: Make sortable
                'observations',
                function ($user) {
                    return count($user->lenses);
                }
            )->addColumn(
                // TODO: Add the correct number of instruments
                // TODO: Make sortable
                'instruments',
                function ($user) {
                    return count($user->lenses);
                }
            )->addColumn(
                // TODO: Add the correct number of lists
                // TODO: Make sortable
                'lists',
                function ($user) {
                    return count($user->lenses);
                }
            )->rawColumns(
                ['username', 'name', 'email', 'delete']
            )->make(true);
    }

    /**
     * Get query source of dataTable.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $users = User::select();

        return $this->applyScopes($users);
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
        return [
            ['username' => 'username',
                'title' => _i('Username'),
                'data' => 'username',
            ],
            ['name' => 'name',
                'title' => _i('Name'),
                'data' => 'name',
            ],
            ['name' => 'email',
                'title' => _i('Email'),
                'data' => 'email',
            ],
            ['name' => 'created_at',
                'title' => _i('Date/Time Added'),
                'data' => 'created_at',
            ],
            ['name' => 'type',
                'title' => _i('User Role'),
                'data' => 'type',
            ],
            ['name' => 'delete',
                'title' => _i('Delete'),
                'data' => 'delete',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'observations',
                'title' => _i('Observations'),
                'data' => 'observations',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'instruments',
                'title' => _i('Instruments'),
                'data' => 'instruments',
                'orderable' => false,
                'searchable' => false,
            ],
            ['name' => 'lists',
                'title' => _i('Lists'),
                'data' => 'lists',
                'orderable' => false,
                'searchable' => false,
            ],
        ];
    }
}
