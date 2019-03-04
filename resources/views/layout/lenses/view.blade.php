@extends("layout.master")

@section('title')
    {{ _i("Lenses of ") }}Name
@endsection

@section('content')
	<h4>
        <!-- TODO: Show real name -->
        {{ _i("Lenses of ") }}Name
    </h4>
	<hr />
    <a class="btn btn-success float-right" href="/lenses/create">
        {{ _i("Add lens") }}
    </a>
    <br /><br />
    <!-- TODO: Show administration overview page
         TODO: Show one lens (from other observer)
    -->

    <table class="table table-sm table-striped table-hover" id="lenses_table">
        <thead>
            <tr>
                <th>{{ _i("Name") }}</th>
                <th>{{ _i("Factor") }}</th>
                <th>{{ _i("Active") }}</th>
                <th>{{ _i("Delete") }}</th>
                <th>{{ _i("Observations") }}</th>
            </tr>
        </thead>
        <tbody>
            <!-- TODO: Only show the lenses for the correct user -->
            @foreach (\App\Lenses::all() as $lens)
                <tr>
                    <td>
                        <a href="/lenses/{{  $lens->id }}/edit">
                            {{ $lens->name }}
                        </a>
                    </td>
                    <td>{{ $lens->factor }}</td>
                    <td>
                        <form method="POST" action="/lenses/{{ $lens->id }}">
                            @method('PATCH')
                            @csrf
                            <input type="checkbox" name="active" onChange="this.form.submit()" {{ $lens->active ? 'checked' : '' }}>
                        </form>
                    </td>
                    <td>
                        <!-- TODO: Only show if there are no observations with this lens -->
                        <form method="POST" action="/lenses/{{ $lens->id }}">
                            @method('DELETE')
                            @csrf
                            <button type="button" class="btn btn-sm btn-link" onClick="this.form.submit()">
                                <i class="far fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                    <td>
                        <!-- TODO: Show the correct number of observations with this lens, and make the correct link -->
                        <a href="#">
                        @if ($lens->id != 6)
                            {{ $lens->id . " " . _i("observations") }}
                        @else
                            {{ $lens->id . " " . _i("observation") }}
                        @endif
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection

@push('scripts')
<script>
$.getScript('{{ URL::asset('js/datatables.js') }}', function()
{
    datatable('#lenses_table', '{{ LaravelGettext::getLocale() }}');
});
</script>
@endpush
