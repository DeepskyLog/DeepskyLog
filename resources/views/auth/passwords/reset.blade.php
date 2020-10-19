@extends('layout.master')

@section('name')
{{  _i('Reset Password') }}
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ _i('Reset Password') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <livewire:user.reset-password />

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
