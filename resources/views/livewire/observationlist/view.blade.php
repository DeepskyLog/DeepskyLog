<div>
    <div>
        @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
        @endif
    </div>

    <!-- We have to check if admin is part of request, because we can not
        add a variable to the view, because we are using YarJa dataTables. -->
    @if (strpos(Request::url(), 'admin') !== false) {
    <h4>
        {{ _i("All observation lists") }}
    </h4>
    @else
    @if ($selected_list_slug)
    <h4>
        {{ _i('Active list') }}
    </h4>

    {!! '<a href="observationList/' . $selected_list_slug . '">'
        . \App\Models\ObservationList::where('slug', $selected_list_slug)->first()->name . '</a>' !!}
    {{ _i(' is your active list. You can search for objects and add them to this list.') }}
    <br /><br />
    @endif
    <h4>
        {{ _i("Observation lists of %s", Auth::user()->name) }}
    </h4>
    <hr />
    <a class="btn btn-success float-right" href="/observationList/create">
        {{ _i("Add observation list") }}
    </a>
    <br /><br />
    @endif
    <hr />
    <livewire:observation-list-table hideable="select" exportable />
</div>
