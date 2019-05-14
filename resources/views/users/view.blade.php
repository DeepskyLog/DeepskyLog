@extends("layout.master")

@section('title', _i('Observer details'))

@section('content')

<h3>{{ $user->name }}</h3>
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

        <br />

    </div>
</div>

@endsection

@push('scripts')

@endpush
