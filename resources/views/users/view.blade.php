@extends("layout.master")

@section('title', _i('Observer details'))

@section('content')

<table>
    <tr>
        <td><h3>{{ $user->name }}</h3></td>
        <td>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            @if ($media)
            <img style="border-radius: 20%" src="{{ $media->getUrl('thumb') }}" alt="{{ $user->name }}">
            @endif
        </td>
    </tr>
</table>
<hr>

<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
    <li class="active nav-item">
        <a class="nav-link active" href="#info" data-toggle="tab">
            {{ _i("Info") }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#observationsPerYear" data-toggle="tab">
            {{ _i("Observations per year")  }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#observationsPerMonth" data-toggle="tab">
            {{ _i("Observations per month")  }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#objectTypes" data-toggle="tab">
            {{ _i("Object types observed") }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#countries" data-toggle="tab">
            {{ _i("Observations per country") }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#stars" data-toggle="tab">
            {{ _i("DeepskyLog stars") }}
        </a>
    </li>
</ul>

<div id="my-tab-content" class="tab-content">
    <!-- Personal tab -->
    <div class="tab-pane active" id="info">
        <table class="table table-striped table-sm">
            <tr>
                <td> {{ _i("Name") }} </td>
                <td> {{ $user->name }} </td>
            </tr>

            <!-- Default location -->
            <tr>
                <td> {{ _i("Default observing site") }} </td>
                <td>
                    @if ($user->stdlocation !== 0)
                        <a href="/location/{{ $user->stdlocation }}">
                            {{ \App\Location::where(['id' => $user->stdlocation])->first()->name }}
                        </a>
                    @endif
                </td>
            </tr>

            <!-- Default instrument -->
            <tr>
                <td> {{ _i("Default instrument") }} </td>
                <td>
                    @if ($user->stdtelescope !== 0)
                        <a href="/instrument/{{ $user->stdtelescope }}">
                            {{ \App\Instrument::where(['id' => $user->stdtelescope])->first()->name }}
                        </a>
                    @endif
                </td>
            </tr>

            <!-- Number of locations -->
            <tr>
                <td> {{ _i("Number of locations") }} </td>
                <td>
                    @if ($user->id === Auth::user()->id)
                        <a href="/location">
                    @endif
                    {{ count($user->locations) }}
                    @if ($user->id === Auth::user()->id)
                        </a>
                    @endif
                </td>
            </tr>

            <!-- Number of instruments -->
            <tr>
                <td> {{ _i("Number of instruments") }} </td>
                <td>
                    @if ($user->id === Auth::user()->id)
                        <a href="/instrument">
                    @endif
                    {{ count($user->instruments) }}
                    @if ($user->id === Auth::user()->id)
                        </a>
                    @endif
                </td>
            </tr>

            <!-- Number of eyepieces -->
            <tr>
                <td> {{ _i("Number of eyepieces") }} </td>
                <td>
                    @if ($user->id === Auth::user()->id)
                        <a href="/eyepiece">
                    @endif
                    {{ count($user->eyepieces) }}
                    @if ($user->id === Auth::user()->id)
                        </a>
                    @endif
                </td>
            </tr>

            <!-- Number of filters -->
            <tr>
                <td> {{ _i("Number of filters") }} </td>
                <td>
                    @if ($user->id === Auth::user()->id)
                        <a href="/filter">
                    @endif
                    {{ count($user->filters) }}
                    @if ($user->id === Auth::user()->id)
                        </a>
                    @endif
                </td>
            </tr>

            <!-- Number of lenses -->
            <tr>
                <td> {{ _i("Number of lenses") }} </td>
                <td>
                    @if ($user->id === Auth::user()->id)
                        <a href="/lens">
                    @endif
                    {{ count($user->lenses) }}
                    @if ($user->id === Auth::user()->id)
                        </a>
                    @endif
                </td>
            </tr>

            <!-- Country of residence -->
            <tr>
                <td> {{ _i("Country of residence") }} </td>
                <td>
                    @if ($user->country != '')
                        {{ Countries::getOne($user->country, LaravelGettext::getLocaleLanguage()) }}
                    @endif
                </td>
            </tr>

            <!-- Copyright notice -->
            <tr>
                <td> {{ _i("Copyright notice") }} </td>
                <td> {!! $user->getCopyright() !!} </td>
            </tr>
        </table>

        <table class="table table-striped table-sm">
            <tr>
                <th></th>
                <th> {{ _i("Total") }} </th>
                @foreach ($observationTypes as $type)
                    <th>{{ _i($type->name) }}</th>
                @endforeach
            </tr>

            <tr>
                <td> {{ _i("Number of observations") }} </td>
                <td>36 / 6000 (0.06%)</td>
                @foreach ($observationTypes as $type)
                    <td>6 / 1000 (0.06%)</td>
                @endforeach
            </tr>

            <tr>
                <td> {{ _i("Observations last year") }} </td>
                <td>30 / 300 (10.0%)</td>
                @foreach ($observationTypes as $type)
                    <td>5 / 50 (10.0%)</td>
                @endforeach
            </tr>

            <tr>
                <td> {{ _i("Number of drawings") }} </td>
                <td>24 / 1200 (0.5%)</td>
                @foreach ($observationTypes as $type)
                    <td>4 / 2000 (0.5%)</td>
                @endforeach
            </tr>

            <tr>
                <td> {{ _i("Drawings last year") }} </td>
                <td>6 / 60 (1.0%)</td>
                @foreach ($observationTypes as $type)
                    <td>1 / 10 (1.0%)</td>
                @endforeach
            </tr>

            <tr>
                <td> {{ _i("Different objects") }} </td>
                <td>240 / {{ \App\Target::count() }} ({{ number_format(240.0 / \App\Target::count() * 100, 2)}}%)</td>
                @foreach ($observationTypes as $type)
                    <td>40 / {{ $numberOfObjects[$type->type] }}
                        ({{ number_format(40 / $numberOfObjects[$type->type] * 100, 2) }}%)</td>
                @endforeach
            </tr>

            <tr>
                <td> {{ _i("Messier objects") }} </td>
                <td></td>
                @foreach ($observationTypes as $type)
                    @if ($type->type == "ds")
                        <td>110 / 110 (100%)</td>
                    @else
                        <td></td>
                    @endif
                @endforeach
            </tr>

            <tr>
                <td> {{ _i("Caldwell objects") }} </td>
                <td></td>
                @foreach ($observationTypes as $type)
                    @if ($type->type == "ds")
                        <td>11 / 110 (10%)</td>
                    @else
                        <td></td>
                    @endif
                @endforeach
            </tr>

            <tr>
                <td> {{ _i("H400 objects") }} </td>
                <td></td>
                @foreach ($observationTypes as $type)
                    @if ($type->type == "ds")
                        <td>48 / 400 (1.2%)</td>
                    @else
                        <td></td>
                    @endif
                @endforeach
            </tr>

            <tr>
                <td> {{ _i("H400-II objects") }} </td>
                <td></td>
                @foreach ($observationTypes as $type)
                    @if ($type->type == "ds")
                        <td>24 / 400 (0.6%)</td>
                    @else
                        <td></td>
                    @endif
                @endforeach
            </tr>

            <tr>
                <td> {{ _i("Rank") }} </td>
                <td> 17 / 255</td>
                @foreach ($observationTypes as $type)
                    <td>12 / 123</td>
                @endforeach
            </tr>

        </table>

        <br />
        <a class="btn btn-success" href="/observations/user/{{ $user->id }}">
            <i class="far fa-eye"></i>&nbsp;{{ _i("All observations of ") . $user->name }}
        </a>

        <a class="btn btn-success" href="/observations/drawings/user/{{ $user->id }}">
            <i class="fas fa-pencil-alt"></i>&nbsp;{{ _i("All drawings of ") . $user->name }}
        </a>

        @if ($user->id != Auth::user()->id)
            <a class="btn btn-primary" href="/messages/create/{{ $user->id }}">
                <i class="fas fa-envelope-open"></i>&nbsp;{{ _i("Send message to ") . $user->name }}
            </a>
        @endif
    </div>

    <div class="tab-pane" id="observationsPerYear">

            <div id="observationsPerYear"></div>

            {!! $observationsPerYear !!}
    </div>

    <!-- The observations per month page -->
    <div class="tab-pane" id="observationsPerMonth">
            <div id="observationsPerMonth"></div>

            {!! $observationsPerMonth !!}
    </div>

</div>

@endsection

@push('scripts')

@endpush
