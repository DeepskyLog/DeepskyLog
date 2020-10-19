@extends('layout.master')

{!! NoCaptcha::renderJs(LaravelGettext::getLocaleLanguage()) !!}

@section('title')
{{  _i("Register") }}
@endsection
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ _i('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <livewire:user.register />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
