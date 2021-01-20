@extends("layout.master")

@section('title')
{{ $set->name }}
@endsection

@section('content')

<livewire:set.show :set="$set" />

@endsection