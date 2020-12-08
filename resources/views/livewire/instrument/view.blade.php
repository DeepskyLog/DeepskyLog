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
        echo _i("All instruments");
        } else {
        echo _i("Instruments of %s", Auth::user()->name);
        }
        @endphp
    </h4>
    <hr />
    <a class="btn btn-success float-right" href="/instrument/create">
        {{ _i("Add instrument") }}
    </a>
    <br /><br />

    @if (strpos(Request::url(), 'admin') === false)
    <livewire:instrument-table hideable="select" exportable />
    @else
    <livewire:instrument-table hideable="select" hide="active" exportable />
    @endif
</div>
