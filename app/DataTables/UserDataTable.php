<?php

/**
 * User DataTable.
 *
 * PHP Version 7
 *
 * @category Authentication
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\DataTables;

use App\Models\User;
use Yajra\DataTables\Services\DataTable;

/**
 * User DataTable.
 *
 * PHP Version 7
 *
 * @category Authentication
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
                '<a href="/users/{{ $slug }}/edit">{{ $username }}</a>'
            )->editColumn(
                'name',
                '<a href="/users/{{ $slug }}/edit">{{ $name }}</a>'
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
                '<form method="POST" action="/users/{{ $slug }}">
                            @method("DELETE")
                            @csrf
                            <button type="button" class="btn btn-sm btn-link" onClick="this.form.submit()">
                            <svg width="1.3em" height="1.3em" viewBox="0 0 16 16" class="bi bi-trash icon" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                            </svg>
                        </button>
                        </form>'
            )->addColumn(
                // TODO: Add the correct number of observations
                // TODO: Make sortable
                'observations',
                function ($user) {
                    return 'TODO';
                }
            )->addColumn(
                // TODO: Make sortable
                'instruments',
                function ($user) {
                    return count($user->instruments);
                }
            )->addColumn(
                // TODO: Make sortable
                'locations',
                function ($user) {
                    return count($user->locations);
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
        return [
            ['username' => 'username',
                'title' => _i('Username'),
                'data'  => 'username',
            ],
            ['name'     => 'name',
                'title' => _i('Name'),
                'data'  => 'name',
            ],
            ['name'     => 'email',
                'title' => _i('Email'),
                'data'  => 'email',
            ],
            ['name'     => 'created_at',
                'title' => _i('Date/Time Added'),
                'data'  => 'created_at',
            ],
            ['name'     => 'type',
                'title' => _i('User Role'),
                'data'  => 'type',
            ],
            ['name'          => 'delete',
                'title'      => _i('Delete'),
                'data'       => 'delete',
                'orderable'  => false,
                'searchable' => false,
            ],
            ['name'          => 'observations',
                'title'      => _i('Observations'),
                'data'       => 'observations',
                'orderable'  => false,
                'searchable' => false,
            ],
            ['name'          => 'instruments',
                'title'      => _i('Instruments'),
                'data'       => 'instruments',
                'orderable'  => false,
                'searchable' => false,
            ],
            ['name'          => 'locations',
                'title'      => _i('Locations'),
                'data'       => 'locations',
                'orderable'  => false,
                'searchable' => false,
            ],
            ['name'          => 'lists',
                'title'      => _i('Lists'),
                'data'       => 'lists',
                'orderable'  => false,
                'searchable' => false,
            ],
        ];
    }
}
