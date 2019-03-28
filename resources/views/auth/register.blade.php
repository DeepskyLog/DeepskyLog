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

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ _i('Full Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" required autofocus>

                                @if ($errors->has('name'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ _i('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ _i('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ _i('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="country" class="col-md-4 col-form-label text-md-right">{{ _i('Country of residence') }}</label>

                            <div class="col-md-6">
                                <select class="form-control" id="country">
                                    <option value="">&nbsp;</option>
                                    @foreach (Countries::getList(LaravelGettext::getLocaleLanguage()) as $code=>$country)
                                        <option value="{{ $code }}">{{ $country }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="observationlanguage" class="col-md-4 col-form-label text-md-right">{{ _i('Standard language for observations') }}</label>

                            <div class="col-md-6">
                                <select class="form-control" id="observationlanguage">
                                    <option value="">&nbsp;</option>
                                    @foreach (Languages::lookup('major', LaravelGettext::getLocaleLanguage()) as $code=>$language)
                                        <option value="{{ $code }}"@if ($code == LaravelGettext::getLocaleLanguage()) selected="selected"@endif>{{ ucfirst($language) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="language" class="col-md-4 col-form-label text-md-right">{{ _i('Language for user interface') }}</label>

                            <div class="col-md-6">
                                <select class="form-control" id="language">
                                    <option value="">&nbsp;</option>
                                    @foreach(Config::get('laravel-gettext.supported-locales') as $locale)
                                        @php
                                            $localeText = ucwords(Locale::getDisplayLanguage($locale, LaravelGettext::getLocale()));
                                        @endphp
                                        <option value="{{ $locale }}"@if ($locale == LaravelGettext::getLocale()) selected="selected"@endif>{{ $localeText }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="cclicense" class="col-md-4 col-form-label text-md-right">{{ _i("License for drawings") }}</label>

                            <div class="col-md-6">
                                <select name="cclicense" id="cclicense" onchange="enableDisableCopyright();" class="form-control">
                                    <option value="0" selected>Attribution CC BY</option>
                                    <option value="1">Attribution-ShareAlike CC BY-SA</option>
                                    <option value="2">Attribution-NoDerivs CC BY-ND</option>
                                    <option value="3">Attribution-NonCommercial CC BY-NC</option>
                                    <option value="4">Attribution-NonCommercial-ShareAlike CC BY-NC-SA</option>
                                    <option value="5">Attribution-NonCommercial-NoDerivs CC BY-NC-ND</option>
                                    <option value="6">{{ _i("No license (Not recommended!)") }}</option>
                                    <option value="7">{{ _i("Enter your own copyright text") }}</option>
                                </select>
                                <span class="help-block">
                                    @php
                                        // TODO: Use the correct language for the chooser tool
                                        echo _i('It is important to select the correct license for your drawings!
                                            For help, see the %sCreative Commons license chooser%s.',
                                            '<a href="http://creativecommons.org/choose/?lang=' . LaravelGettext::getLocale() . '">', '</a>');
                                    @endphp
                                </span>
                                </div>
                        </div>



                        <div class="form-group row">
                            <label for="copyright" class="col-md-4 col-form-label text-md-right">{{ _i('Copyright notice') }}</label>

                            <div class="col-md-6">
                                <input id="copyright" disabled type="text" class="form-control" maxlength="128" name="copyright">

                                @if ($errors->has('name'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right"></label>
                            <div class="col-md-6">
                                {!! NoCaptcha::display() !!}
                            </div>
                        </div>

                        @php echo _i("Your personal information will be processed in accordance with the %sprivacy policy%s and shall be used only for user management and to keep you informed about our activities.", "<a href='/privacy'>", "</a>") . "<br /><br />";
                        @endphp

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ _i('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>

$('#password').password({
    shortPass: '<?php echo _i("The password is too short"); ?>',
    badPass: '<?php echo _i("Weak; try combining letters & numbers"); ?>',
    goodPass: '<?php echo _i("Medium; try using special characters"); ?>',
    strongPass: '<?php echo _i("Strong password"); ?>',
    containsUsername: '<?php echo _i("The password contains the username"); ?>',
    enterPass: '<?php echo _i("Type your password"); ?>',
    showText: true, // shows the text tips
    animate: true, // whether or not to animate the progress bar on input blur/focus
    animateSpeed: 'fast', // the above animation speed
    username: false, // select the username field (selector or jQuery instance) for better password checks
    usernamePartialMatch: true, // whether to check for username partials
    minimumLength: 6 // minimum password length (below this threshold, the score is 0)
  });

function enableDisableCopyright() {
    var selectBox = document.getElementById("cclicense");
    var selectedValue = selectBox.options[selectBox.selectedIndex].value;
    if (selectedValue == 7) {
        document.getElementById("copyright").disabled=false;
    } else {
        document.getElementById("copyright").disabled=true;
    }
}
</script>
@endpush
