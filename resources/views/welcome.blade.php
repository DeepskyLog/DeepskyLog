@extends("layout.master")

@section('content')
<h1>Test</h1>

{!! LaravelGettext::getSelector([
    'en' => 'English',
    'es' => 'Español',
    'de' => 'Deutsch',
    'nl' => 'Nederlands',
    'sv' => 'Svenska',
    'fr' => 'Français'
])->render(); !!}

@endsection
