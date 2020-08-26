<?php

/**
 * Location DataTable.
 *
 * PHP Version 7
 *
 * @category Locations
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\DataTables;

use App\Location;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Services\DataTable;

/**
 * Location DataTable.
 *
 * PHP Version 7
 *
 * @category Locations
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class LocationDataTable extends DataTable
{
    /**
     * Make the correct ajax call.
     *
     * @return datatables the Correct ajax call
     */
    public function ajax()
    {
        if ($this->user === 'admin') {
            $model = Location::with('user')->select('locations.*');
        } else {
            $model = Location::where(
                'user_id',
                auth()->user()->id
            )->with('user')->select('locations.*');
        }

        return datatables()
            ->eloquent($model)
            ->addColumn(
                'weather',
                function ($location) {
                    return '<a href="http://clearoutside.com/forecast/'
                        . round($location->latitude, 2) . '/'
                        . round($location->longitude, 2) . '">
                        <img src="http://clearoutside.com/forecast_image_small/'
                        . round($location->latitude, 2) . '/'
                        . round($location->longitude, 2) . '/forecast.png" />
                        </a>';
                }
            )->editColumn(
                'name',
                function ($location) {
                    return '<a href="/location/' . $location->id . '">' .
                        $location->name . '</a>';
                }
            )->editColumn(
                'observations',
                '<a href="/observations/location/{{ $id }}">TODO</a>'
            )->editColumn(
                'elevation',
                '{{ $elevation }} m'
            )->editColumn(
                'country',
                '{{ Countries::getOne($country, LaravelGettext::getLocaleLanguage()) }}'
            )->editColumn(
                'limitingMagnitude',
                function ($location) {
                    if ($location->limitingMagnitude != null) {
                        return $location->limitingMagnitude - Auth::user()->fstOffset;
                    } else {
                        return '';
                    }
                }
            )->editColumn(
                'user.name',
                function ($location) {
                    return '<a href="/users/' . $location->user->id . '">'
                        . $location->user->name . '</a>';
                }
            )->editColumn(
                'active',
                '<form method="POST" action="/location/{{ $id }}">
                    @method("PATCH")
                    @csrf
                    <input type="checkbox" name="active" onChange="this.form.submit()" {{ $active ? "checked" : "" }}>
                 </form>'
            )->addColumn(
                'standard',
                function ($location) {
                    if ($location->id == Auth::user()->stdlocation) {
                        return '<input type="radio" name="stdlocation" value="'
                            . $location->id
                            . '" checked="checked" onclick="submit();" />';
                    } else {
                        if ($location->active) {
                            return '<input type="radio" name="stdlocation" value="'
                            . $location->id
                            . '" onclick="submit();" />';
                        } else {
                            return '<input type="radio" name="stdlocation" value="'
                            . $location->id
                            . '" disabled />';
                        }
                    }
                }
            )->addColumn(
                'delete',
                '<form method="POST" action="/location/{{ $id }}">
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
                    'user.name', 'standard', 'weather', ]
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
                ['name' => 'country',
                    'title' => _i('Country'),
                    'data' => 'country',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'elevation',
                    'title' => _i('Elevation'),
                    'data' => 'elevation',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'limitingMagnitude',
                    'title' => _i('NELM'),
                    'data' => 'limitingMagnitude',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'skyBackground',
                    'title' => _i('SQM'),
                    'data' => 'skyBackground',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'bortle',
                    'title' => _i('Bortle'),
                    'data' => 'bortle',
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
                ['name' => 'weather',
                    'title' => _i('Weather forecast'),
                    'data' => 'weather',
                    'orderable' => false,
                    'searchable' => false,
                ],
                ['name' => 'country',
                    'title' => _i('Country'),
                    'data' => 'country',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'elevation',
                    'title' => _i('Elevation'),
                    'data' => 'elevation',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'limitingMagnitude',
                    'title' => _i('NELM'),
                    'data' => 'limitingMagnitude',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'skyBackground',
                    'title' => _i('SQM'),
                    'data' => 'skyBackground',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'bortle',
                    'title' => _i('Bortle'),
                    'data' => 'bortle',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'observations',
                    'title' => _i('Observations'),
                    'data' => 'observations',
                    'width' => '10%',
                    'searchable' => false,
                ],
                ['name' => 'active',
                    'title' => _i('Active'),
                    'data' => 'active',
                ],
                ['name' => 'standard',
                    'title' => _i('Default Location'),
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
        return 'Location_' . date('YmdHis');
    }
}
