<div>
    <div>
        @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
        @endif
    </div>

    <h4>
        <!-- We have to check if admin is part of request, because we can not
                add a variable to the view, because we are using YarJa dataTables. -->
        @php if (strpos(Request::url(), 'admin') !== false) {
        echo _i("All locations");
        } else {
        echo _i("Locations of %s", Auth::user()->name);
        }
        @endphp
    </h4>
    <hr />
    <a class="btn btn-success float-right" href="/location/create">
        {{ _i("Add location") }}
    </a>
    <br /><br />

    @if (strpos(Request::url(), 'admin') === false)
    <livewire:location-table hideable="select" exportable />
    @else
    <livewire:location-table hideable="select" hide="active" exportable />
    @endif
</div>
