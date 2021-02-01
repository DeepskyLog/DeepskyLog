@extends("layout.master")

@section('title')
{{ _i('Search objects') }}
@endsection

@section('content')
<h4>
    {{ _i('Search objects') }}
</h4>

<livewire:target.search />

@endsection
