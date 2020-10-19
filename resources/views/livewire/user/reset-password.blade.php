<div>
    <div class="form-group row">
        <label for="email" class="col-md-4 col-form-label text-md-right">{{ _i('E-Mail Address') }}</label>

        <div class="col-md-6">
            <input wire:model="email" id="email" type="email"
                class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email"
                value="{{ $email ?? old('email') }}" required autofocus>

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

    {{-- Submit button --}}
    <div>
        @if (!$errors->isEmpty())
        <div class="alert alert-danger">
            {{  _i('Please fix the errors in the form.') }}
        </div>
        @else
        <div class="form-group row mb-0">
            <div class="col-md-6 offset-md-4">
                <button type="submit" class="btn btn-primary">
                    {{ _i('Reset Password') }}
                </button>
            </div>
        </div>
        @endif
    </div>
</div>
