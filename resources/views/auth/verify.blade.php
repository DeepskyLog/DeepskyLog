@extends('layout.master')

@section('title')
    {{  _i('Verify Your Email Address') }}
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ _i('Verify Your Email Address') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ _i('A fresh verification link has been sent to your email address.') }}
                            {{ _i('If you did not receive the email') }},
                        </div>
                    @endif

                    {{ _i('Before proceeding, please check your email for a verification link.') }}
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ _i('click here to request another') }}</button>.
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
