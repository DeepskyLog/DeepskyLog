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
                        </div>
                    @endif

                    {{ _i('Before proceeding, please check your email for a verification link.') }}
                    @php
                        $route = route('verification.resend');
                        $string = '<a href="' . $route . '">';
                        echo sprintf(_i('If you did not receive the email, %sclick here to request another%s.'), $string, '</a>');
                    @endphp

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
