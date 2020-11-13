@extends("layout.master")

@section('title', _i('Observer details'))

@section('content')

<div class="row">
    <h3>{{ $user->name }}</h3>
</div>
<div class="row">
    @if ($user->about)
    <div class="col-8">
        <div class="card float-right">
            <div class="card-body">
                {{ $user->about }}
            </div>
        </div>
    </div>
    @endif
    @if ($media)
    <a href={{ $media->getUrl() }} data-lity>
        <img class="float-right" style="border-radius: 20%" src="{{ $media->getUrl('thumb') }}" alt="{{ $user->name }}">
    </a>
    @endif
</div>
<hr>

<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
    <li class="active nav-item">
        <a class="nav-link active" href="#info" data-toggle="tab">
            {{ _i('Info') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#observationsPerYear" data-toggle="tab">
            {{ _i('Observations per year') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#observationsPerMonth" data-toggle="tab">
            {{ _i('Observations per month') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#objectTypes" data-toggle="tab">
            {{ _i('Object types observed') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#countries" data-toggle="tab">
            {{ _i('Observations per country') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#stars" data-toggle="tab">
            {{ _i('DeepskyLog stars') }}
        </a>
    </li>
</ul>

<div id="my-tab-content" class="tab-content">
    <!-- Personal tab -->
    <div class="tab-pane active" id="info">
        <table class="table table-striped table-sm">
            <tr>
                <td> {{ _i('Name') }} </td>
                <td> {{ $user->name }} </td>
            </tr>

            <!-- Default location -->
            <tr>
                <td> {{ _i('Default observing site') }} </td>
                <td>
                    @if ($user->stdlocation)
                    <a href="{{ route('location.show', $user->stdlocation) }}">
                        {{ \App\Models\Location::where(['id' => $user->stdlocation])->first()->name }}
                    </a>
                    @endif
                </td>
            </tr>

            <!-- Default instrument -->
            <tr>
                <td> {{ _i('Default instrument') }} </td>
                <td>
                    @if ($user->stdtelescope)
                    <a href="{{ route('instrument.show', $user->stdtelescope) }}">
                        {{ \App\Models\Instrument::where(['id' => $user->stdtelescope])->first()->name }}
                    </a>
                    @endif
                </td>
            </tr>

            <!-- Number of locations -->
            <tr>
                <td> {{ _i('Number of locations') }} </td>
                <td>
                    @if ($user->id === Auth::user()->id)
                    <a href="{{ route('location.index') }}">
                        @endif
                        {{ count($user->locations) }}
                        @if ($user->id === Auth::user()->id)
                    </a>
                    @endif
                </td>
            </tr>

            <!-- Number of instruments -->
            <tr>
                <td> {{ _i('Number of instruments') }} </td>
                <td>
                    @if ($user->id === Auth::user()->id)
                    <a href="{{ route('instrument.index') }}">
                        @endif
                        {{ count($user->instruments) }}
                        @if ($user->id === Auth::user()->id)
                    </a>
                    @endif
                </td>
            </tr>

            <!-- Number of eyepieces -->
            <tr>
                <td> {{ _i('Number of eyepieces') }} </td>
                <td>
                    @if ($user->id === Auth::user()->id)
                    <a href="{{ route('eyepiece.index') }}">
                        @endif
                        {{ count($user->eyepieces) }}
                        @if ($user->id === Auth::user()->id)
                    </a>
                    @endif
                </td>
            </tr>

            <!-- Number of filters -->
            <tr>
                <td> {{ _i('Number of filters') }} </td>
                <td>
                    @if ($user->id === Auth::user()->id)
                    <a href="{{ route('filter.index') }}">
                        @endif
                        {{ count($user->filters) }}
                        @if ($user->id === Auth::user()->id)
                    </a>
                    @endif
                </td>
            </tr>

            <!-- Number of lenses -->
            <tr>
                <td> {{ _i('Number of lenses') }} </td>
                <td>
                    @if ($user->id === Auth::user()->id)
                    <a href="{{ route('lens.index') }}">
                        @endif
                        {{ count($user->lenses) }}
                        @if ($user->id === Auth::user()->id)
                    </a>
                    @endif
                </td>
            </tr>

            <!-- Country of residence -->
            <tr>
                <td> {{ _i('Country of residence') }} </td>
                <td>
                    @if ($user->country != '')
                    {{ Countries::getOne($user->country, LaravelGettext::getLocaleLanguage()) }}
                    @endif
                </td>
            </tr>

            <!-- Copyright notice -->
            <tr>
                <td> {{ _i('Copyright notice') }} </td>
                <td> {!! $user->getCopyright() !!} </td>
            </tr>
        </table>

        <table class="table table-striped table-sm">
            <tr>
                <th></th>
                <th> {{ _i('Total') }} </th>
                @foreach ($observationTypes as $type)
                <th>{{ _i($type->name) }}</th>
                @endforeach
            </tr>

            <tr>
                <td> {{ _i('Number of observations') }} </td>
                <td>36 / 6000 (0.06%)</td>
                @foreach ($observationTypes as $type)
                <td>6 / 1000 (0.06%)</td>
                @endforeach
            </tr>

            <tr>
                <td> {{ _i('Observations last year') }} </td>
                <td>30 / 300 (10.0%)</td>
                @foreach ($observationTypes as $type)
                <td>5 / 50 (10.0%)</td>
                @endforeach
            </tr>

            <tr>
                <td> {{ _i('Number of drawings') }} </td>
                <td>24 / 1200 (0.5%)</td>
                @foreach ($observationTypes as $type)
                <td>4 / 2000 (0.5%)</td>
                @endforeach
            </tr>

            <tr>
                <td> {{ _i('Drawings last year') }} </td>
                <td>6 / 60 (1.0%)</td>
                @foreach ($observationTypes as $type)
                <td>1 / 10 (1.0%)</td>
                @endforeach
            </tr>

            <tr>
                <td> {{ _i('Different objects') }} </td>
                <td>240 / {{ \App\Models\Target::count() }}
                    ({{ number_format((240.0 / \App\Models\Target::count()) * 100, 2) }}%)</td>
                @foreach ($observationTypes as $type)
                <td>40 / {{ $numberOfObjects[$type->type] }}
                    (@if ($numberOfObjects[$type->type] == 0) 0%) @else
                    {{ number_format((40 / $numberOfObjects[$type->type]) * 100, 2) }}%) </beautify end=" @endif">
                </td>
                @endforeach
            </tr>

            <tr>
                <td> {{ _i('Messier objects') }} </td>
                <td></td>
                @foreach ($observationTypes as $type)
                @if ($type->type == 'ds')
                <td>110 / 110 (100%)</td>
                @else
                <td></td>
                @endif
                @endforeach
            </tr>

            <tr>
                <td> {{ _i('Caldwell objects') }} </td>
                <td></td>
                @foreach ($observationTypes as $type)
                @if ($type->type == 'ds')
                <td>11 / 110 (10%)</td>
                @else
                <td></td>
                @endif
                @endforeach
            </tr>

            <tr>
                <td> {{ _i('H400 objects') }} </td>
                <td></td>
                @foreach ($observationTypes as $type)
                @if ($type->type == 'ds')
                <td>48 / 400 (1.2%)</td>
                @else
                <td></td>
                @endif
                @endforeach
            </tr>

            <tr>
                <td> {{ _i('H400-II objects') }} </td>
                <td></td>
                @foreach ($observationTypes as $type)
                @if ($type->type == 'ds')
                <td>24 / 400 (0.6%)</td>
                @else
                <td></td>
                @endif
                @endforeach
            </tr>

            <tr>
                <td> {{ _i('Rank') }} </td>
                <td> 17 / 255</td>
                @foreach ($observationTypes as $type)
                <td>12 / 123</td>
                @endforeach
            </tr>

        </table>

        <br />
        <a class="btn btn-success" href="/observations/user/{{ $user->slug }}">
            <svg width="1.1em" height="1.1em" viewBox="0 1 16 16" class="bi bi-eye-fill" fill="currentColor"
                xmlns="http://www.w3.org/2000/svg">
                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z" />
                <path fill-rule="evenodd"
                    d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z" />
            </svg>
            &nbsp;{{ _i('All observations of ') . $user->name }}
        </a>

        <a class="btn btn-success" href="/observations/drawings/user/{{ $user->slug }}">
            <svg width="1em" height="1em" viewBox="0 1 16 16" class="bi bi-pencil" fill="currentColor"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M11.293 1.293a1 1 0 0 1 1.414 0l2 2a1 1 0 0 1 0 1.414l-9 9a1 1 0 0 1-.39.242l-3 1a1 1 0 0 1-1.266-1.265l1-3a1 1 0 0 1 .242-.391l9-9zM12 2l2 2-9 9-3 1 1-3 9-9z" />
                <path fill-rule="evenodd"
                    d="M12.146 6.354l-2.5-2.5.708-.708 2.5 2.5-.707.708zM3 10v.5a.5.5 0 0 0 .5.5H4v.5a.5.5 0 0 0 .5.5H5v.5a.5.5 0 0 0 .5.5H6v-1.5a.5.5 0 0 0-.5-.5H5v-.5a.5.5 0 0 0-.5-.5H3z" />
            </svg>
            &nbsp;{{ _i('All drawings of ') . $user->name }}
        </a>

        @if ($user->id != Auth::user()->id)
        <a class="btn btn-primary" href="/messages/create/{{ $user->id }}">
            <svg width="1em" height="1em" viewBox="0 1 16 16" class="bi bi-envelope" fill="currentColor"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383l-4.758 2.855L15 11.114v-5.73zm-.034 6.878L9.271 8.82 8 9.583 6.728 8.82l-5.694 3.44A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.739zM1 11.114l4.758-2.876L1 5.383v5.73z" />
            </svg>
            &nbsp;{{ _i('Send message to ') . $user->name }}
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
