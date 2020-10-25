<div>
    <br />
    <form wire:submit.prevent="save" role="form" action="/users/{{ $user->id }}/settings">

        @auth
        @php
        $allInstruments = \App\Models\Instrument::getInstrumentOptions();

        $allCountries = '<option value="">&nbsp;</option>';
        foreach (\Countries::getList(LaravelGettext::getLocaleLanguage()) as $code => $mycountry) {
        $allCountries .= '<option ';
            if ($code == $user->country) {
                $allCountries .= ' selected="selected" ';
            }
            $allCountries .= ' value="' . $code . '">' . $mycountry . '</option>';
        }
        @endphp

        {{-- username --}}
        <div class="form-group username">
            <label for="name">{{ _i('Username') }}</label>
            <input wire:model="username" readonly type="text" required
                class="form-control @error('username') is-invalid @enderror" maxlength="64" name="username" size="30"
                value="{{ $user->username }}" />
        </div>

        {{-- Change password button --}}
        <div class="form-group">
            <label class="btn btn-success">
                <input type="checkbox" wire:model="changePassword" autocomplete="off" checked>
                &nbsp;{{ _i('Change password') }}
            </label>
        </div>
        @if ($changePassword)
        {{-- Change password field--}}
        <div class="form-group">
            <label for="password">{{ _i('Password') }}</label>

            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                wire:model="password" autocomplete="off">

            <span class="help-block">
                {{ _i('The password should at least be 8 characters, and contain a least one uppercase character (A –
                Z), one lowercase character (a – z), one digit (0 – 9), and one Non-alphanumeric (!, @, #, $, %, ^, &,
                ?, or *) character') }}
            </span>
            @error('password')
            <br />
            <span class="small text-error">
                {{ $message }}
            </span>
            @enderror
        </div>

        {{-- Change password confirmation field --}}
        <div class="form-group">
            <label for="password-confirm">{{ _i('Confirm Password') }}</label>

            <input id="password-confirm" type="password" class="form-control" wire:model="password_confirmation"
                autocomplete="off">
        </div>
        @endif

        {{-- Email address --}}
        <div class="form-group email">
            <label for="name">{{ _i('Email') }}</label>
            <input wire:model="email" type="text" required class="form-control @error('email') is-invalid @enderror"
                maxlength="64" name="email" size="30" value="{{ $user->email }}" />
            @error('email') <span class="small text-error">{{ $message }}</span> @enderror
        </div>

        {{-- Name of the observer --}}
        <div class="form-group" name="name" id="name">
            <label for="name">{{ _i('Name') }}</label>
            <input wire:model="name" type="text" required class="form-control @error('name') is-invalid @enderror"
                maxlength="64" name="name" size="30" value="{{ $user->name }}" />
            @error('name') <span class="small text-error">{{ $message }}</span> @enderror
        </div>

        {{-- Country of residence --}}
        <div x-data=''>
            <label>{{ _i('Country of residence') }}</label>
            <x-input.select-live-wire wire:model="country" prettyname="mycountry" :options="$allCountries"
                selected="('country')" />
        </div>
        <p hidden>{{ $country }}</p>
        <p></p>
        {{-- about the observer --}}
        <div class="form-group" name="about" id="about">
            <label for="about">{{ _i('Let other people your astronomical interests') }}</label>
            <textarea wire:model="about" required class="form-control @error('about') is-invalid @enderror" rows="5"
                maxlength="500" name="about">{{ $user->about }}</textarea>
            <p class="text-center {{ strlen($about) >= 485 ? 'text-danger' : '' }}">
                <small>
                    {{ strlen($about) . '/500' }}
                </small>
            </p>
        </div>

        {{-- Profile picture --}}
        <div class="card mb-3">
            <div class="row no-gutters">
                <div class="col-2" id="card-bg">
                    @if ($photo)
                    <img class="card-img-top" style="border-radius: 20%" src="{{ $photo->temporaryUrl() }}">
                    @endif
                </div>
                <div class="col-10" id="card-bg">
                    <div class="card-body">
                        <h5 class="card-title">{{ _i('Change profile picture') . ' (max 10 Mb)' }}</h5>

                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('photo') is-invalid @enderror"
                                wire:model="photo">
                            <label class="custom-file-label">Choose file</label>
                        </div>
                        <div wire:loading wire:target="photo" class="text-sm text-gray-500 italic">
                            {{ _i('Uploading...') }}
                        </div>
                        @error('photo') <br /><span class="small text-error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Send mail --}}
        <div class="form-group form-check sendMail">
            <input type="checkbox" wire:model="sendMail" @if ($user->sendMail) checked @endif
            class="form-check-input @error('sendMail') is-invalid @enderror" name="sendMail"
            />
            <label class="form-check-label" for="name">{{ _i('Send emails') }}</label>
        </div>

        {{-- fst offset --}}
        <div class="form-group fstOffset">
            <label for="fstOffset">{{ _i('fstOffset') }}</label>
            <input wire:model="fstOffset" type="number" min="-5.0" max="5.0" step="0.01"
                class="form-control @error('fstOffset') is-invalid @enderror" maxlength="4" name="fstOffset" size="4"
                value="{{ $fstOffset }}" />
            <span class="help-block">{{ _i('Offset between measured SQM value and the faintest visible star.') }}</span>
            @error('fstOffset') <br /><span class="small text-error">{{ $message }}</span> @enderror
        </div>

        {{-- License --}}
        @php
        $allLicenses = '';
        foreach ($licenses as $license=>$number) {
        $allLicenses .= '<option value="' . $number . '"';
        if ($cclicense == $number) {
            $allLicenses .= ' selected="selected"';
        }
        $allLicenses .= '>' . $license . '</option>';
        }
        if ($cclicense == 6) {
        $allLicenses .= '<option value="6" selected="selected">' . _i('No license (Not recommended!)') . '</option>';
        $allLicenses .= '<option value="6">' . _i('No license (Not recommended!)') . '</option>';
        }
        if ($cclicense == 7) {
        $allLicenses .= '<option value="7" selected="selected">' . _i('Enter your own copyright text') . '</option>';
        } else {
        $allLicenses .= '<option value="7">' . _i('Enter your own copyright text') . '</option>';
        }
        @endphp
        <div class="form-group">
            <label for="cclicense">{{ _i('License for drawings') }}</label>
            <div x-data=''>
                <x-input.select-live-wire wire:model="cclicense" prettyname="mylicense" :options="$allLicenses"
                    selected="('cclicense')" />
            </div>

            <span class="help-block">
                @php
                // Use the correct language for the chooser tool
                echo _i('It is important to select the correct license for your drawings!
                For help, see the %sCreative Commons license chooser%s.',
                '<a href="http://creativecommons.org/choose/?lang=' . LaravelGettext::getLocale() . '">', '</a>');
                @endphp
            </span>
        </div>
        <p hidden>{{ $cclicense }}</p>

        {{-- The copyright notice --}}
        @if ($cclicense == 7)
        <div class="form-group">
            <label for="copyright">{{ _i('Copyright notice') }}</label>
            <input wire:model="copyright" id="copyright" type="text" class="form-control" maxlength="128"
                name="copyright" value="{{ $cclicense }}">
            <p class="text-center {{ strlen($copyright) >= 118 ? 'text-danger' : '' }}">
                <small>
                    {{ strlen($copyright) . '/128' }}
                </small>
            </p>
        </div>
        @endif

        {{-- Submit button --}}
        <div>
            @if (!$errors->isEmpty())
            <div class="alert alert-danger">
                {{  _i('Please fix the errors in the settings.') }}
            </div>
            @else
            <input type="submit" class="btn btn-success" name="add" value="{{ _i('Update') }}" />
            @endif
            @if (session()->has('message'))
            <br /><br />
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
            @endif
        </div>
        @endauth
    </form>
</div>
