<div>
    <div class="form-group row">
        <label for="username" class="col-md-4 col-form-label text-md-right">{{ _i('Username') }}</label>

        <div class="col-md-6">
            <input wire:model="username" id="username" type="text"
                class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username" value=""
                required autofocus>

            @error('username')
            <br />
            <span class="small text-error">
                {{ $message }}
            </span>
            @enderror
        </div>
    </div>

    <div class="form-group row">
        <label for="name" class="col-md-4 col-form-label text-md-right">{{ _i('Full Name') }}</label>

        <div class="col-md-6">
            <input wire:model="name" id="name" type="text"
                class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="" required
                autofocus>

            @error('name')
            <span class="small text-error">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
    </div>

    <div class="form-group row">
        <label for="email" class="col-md-4 col-form-label text-md-right">{{ _i('E-Mail Address') }}</label>

        <div class="col-md-6">
            <input wire:model="email" id="email" type="email"
                class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="" required>

            @error('email')
            <span class="small text-error">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
    </div>

    <div class="form-group row">
        <label for="password" class="col-md-4 col-form-label text-md-right">{{ _i('Password') }}</label>

        <div class="col-md-6">
            <input wire:model="password" id="password" type="password"
                class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

            @error('password')
            <span class="small text-error">
                <strong>{{ $message }}</strong>
            </span>
            <br />
            @enderror
            <span class="help-block">
                {{ _i('The password should at least be 8 characters, and contain a least one uppercase character (A –
                    Z), one lowercase character (a – z), one digit (0 – 9), and one Non-alphanumeric (!, @, #, $, %, ^, &,
                    ?, or *) character') }}
            </span>
        </div>
    </div>

    <div class="form-group row">
        <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ _i('Confirm Password') }}</label>

        <div class="col-md-6">
            <input wire:model="password_confirmation" id="password-confirm" type="password" class="form-control"
                name="password_confirmation" required>
        </div>
    </div>

    <div class="form-group row">
        <label for="country" class="col-md-4 col-form-label text-md-right">{{ _i('Country of residence') }}</label>

        <div class="col-md-6">
            <div class="form" wire:ignore>
                <select wire:model="country" class="form-control countrySelection" id="country" name="country">
                    @foreach (Countries::getList(LaravelGettext::getLocaleLanguage()) as $code=>$country)
                    <option @if ($code==old('country')) selected="selected" @endif value="{{ $code }}">
                        {{ $country }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label for="observationlanguage"
            class="col-md-4 col-form-label text-md-right">{{ _i('Standard language for observations') }}</label>

        <div class="col-md-6">
            <div class="form" wire:ignore>
                <select wire:model="observationlanguage" class="form-control observationLanguageSelection"
                    id="observationlanguage" name="observationlanguage">
                    @foreach (Languages::lookup('major', LaravelGettext::getLocaleLanguage()) as $code=>$language)
                    <option value="{{ $code }}"> {{ ucfirst($language) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label for="language"
            class="col-md-4 col-form-label text-md-right">{{ _i('Language for user interface') }}</label>

        <div class="col-md-6">
            <div class="form" wire:ignore>
                <select wire:model="language" class="form-control languageSelection" id="language" name="language">
                    @foreach(Config::get('laravel-gettext.supported-locales') as $locale)
                    @php
                    $localeText = ucwords(Locale::getDisplayLanguage($locale,
                    LaravelGettext::getLocale()));
                    @endphp
                    <option value="{{ $locale }}">{{ $localeText }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>


    <div class="form-group row" name="license" id="license">
        <label for="cclicense" class="col-md-4 col-form-label text-md-right">{{ _i("License for drawings") }}</label>

        <div class="col-md-6">
            <div class="form" wire:ignore>
                <select wire:model="cclicense" name="cclicense" class="form-control license" style="width: 100%"
                    id="license">
                    @foreach ($licenses as $license=>$number)
                    <option value="{{ $number }}">{{ $license }}</option>
                    @endforeach
                    <option value="6">
                        {{ _i('No license (Not recommended!)') }}</option>
                    <option value="7">
                        {{ _i('Enter your own copyright text') }}</option>
                </select>
            </div>
            <span class="help-block">
                @php
                // Use the correct language for the chooser tool
                echo _i('It is important to select the correct license for your drawings!
                For help, see the %sCreative Commons license chooser%s.',
                '<a href="http://creativecommons.org/choose/?lang=' . LaravelGettext::getLocale() . '">',
                    '</a>');
                @endphp
            </span>
        </div>
    </div>

    @if ($cclicense == 7)
    <div class="form-group row">
        <label for="copyright" class="col-md-4 col-form-label text-md-right">{{ _i('Copyright notice') }}</label>

        <div class="col-md-6">
            <input wire:model="copyright" id="copyright" type="text" class="form-control" maxlength="128"
                name="copyright" value="{{ old('copyright') }}">
            <p class="text-center {{ strlen($copyright) >= 118 ? 'text-danger' : '' }}">
                <small>
                    {{ strlen($copyright) . '/128' }}
                </small>
            </p>
        </div>
    </div>
    @endif

    <div class="form-group row" wire:ignore>
        <label class="col-md-4 col-form-label text-md-right"></label>
        <div class="col-md-6">
            {!! NoCaptcha::display() !!}
        </div>
    </div>

    @php echo _i("Your personal information will be processed in accordance with the %sprivacy
    policy%s and shall be used only for user management and to keep you informed about our
    activities.", "<a href='/privacy'>", "</a>") . "<br /><br />";
    @endphp

    {{-- Submit button --}}
    <div>
        @if (!$errors->isEmpty())
        <div class="alert alert-danger">
            {{  _i('Please fix the errors in the form.') }}
        </div>
        @else
        <div class="form-group row mb-0">
            <div class="col-md-8 offset-md-5">
                <button type="submit" class="btn btn-primary">
                    {{ _i('Register') }}
                </button>
            </div>
        </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.license').select2();
        $('.observationLanguageSelection').select2();
        $('.languageSelection').select2();
        $('.countrySelection').select2();
        $('.license').on('change', function(e) {
            @this.set('cclicense', e.target.value);
        });
        $('.countrySelection').on('change', function(e) {
            @this.set('country', e.target.value);
        });
        $('.languageSelection').on('change', function(e) {
            @this.set('language', e.target.value);
        });
        $('.observationLanguageSelection').on('change', function(e) {
            @this.set('observationlanguage', e.target.value);
        });
    });
</script>
@endpush
