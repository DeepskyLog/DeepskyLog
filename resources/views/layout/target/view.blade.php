@extends("layout.master")

@section('title')
{{ _i('Overview selected objects') }}
@endsection

@section('content')
<h4>
    {{ _i('Overview selected objects') }}
</h4>

<livewire:target.view :targetsToShow="$targetsToShow" />

@endsection
