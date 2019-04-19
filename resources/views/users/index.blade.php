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
                <th>{{ _i('User Role') }}</th>
                <th>{{ _i('Delete') }}</th>
                <th>{{ _i('Observations') }}</th>
                <th>{{ _i('Instruments') }}</th>
                <th>{{ _i('Lists') }}</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($users as $user)
            <tr>

                <td>
                    <a href="{{ route('users.edit', $user->id) }}">
                        {{ $user->name }}
                    </a>
                </td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->created_at->format('F d, Y h:ia') }}</td>
                <td>{{ $user->type }}</td>
                <td>
                    <form method="POST" action="{{ route('users.destroy', $user->id) }}">
                        @method('DELETE')
                        @csrf
                        <button type="button" class="btn btn-sm btn-link" onClick="this.form.submit()">
                            <i class="far fa-trash-alt"></i>
                        </button>
                    </form>
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
</script>
@endpush
