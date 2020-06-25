@extends("layout.master")

@section('title')
    {{ _i('Object catalogs') }}
@endsection

@section('content')
<h4>
    {{ _i('Object catalogs') }}
</h4>

<livewire:target.catalogs />
@endsection
