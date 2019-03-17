@extends("layout.master")

@section('title', _i('User Administration'))

@section('content')

<div class="col-lg-10 col-lg-offset-1">
    <h3>
        <i class="fa fa-users"></i> {{ _i('User Administration') }}
    </h3>
    <hr>
    <table class="table table-sm table-striped table-hover" id="users_table">
        <thead>
            <tr>
                <th>{{ _i('Name') }}</th>
                <th>{{ _i('Email') }}</th>
                <th>{{ _i('Date/Time Added') }}</th>
                <th>{{ _i('User Roles') }}</th>
                <th>{{ _i('Delete') }}</th>
                <th>{{ _i('Edit') }}</th>
                <th>{{ _i('Observations') }}</th>
                <th>{{ _i('Instruments') }}</th>
                <th>{{ _i('Lists') }}</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($users as $user)
            <tr>

                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->created_at->format('F d, Y h:ia') }}</td>
                <td>{{  $user->roles()->pluck('name')->implode(', ') }}</td>{{-- Retrieve array of roles associated to a user and convert to string --}}
                <td>
                {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id] ]) !!}
                {!! Form::submit(_i('Delete'), ['class' => 'btn-small']) !!}
                {!! Form::close() !!}

                </td>
                <td>
                    <a href="{{ route('users.edit', $user->id) }}" class="fas fa-user-edit pull-left" style="margin-right: 3px;"></a>
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @endforeach
        </tbody>

    </table>

</div>

@endsection

@push('scripts')
<script>
$.getScript('{{ URL::asset('js/datatables.js') }}', function()
{
    datatable('#users_table', '{{ LaravelGettext::getLocale() }}');
});
</script>
@endpush
