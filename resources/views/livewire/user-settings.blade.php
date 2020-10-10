<div>
    {{-- <form wire:submit.prevent="save">
        --}}

        {{-- username --}}
        <div class="form-group username">
            <label for="name">{{ _i('Username') }}</label>
            <input wire:model="username" readonly type="text" required
                class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}" maxlength="64" name="username"
                size="30" value="{{ $user->username }}" />
        </div>

        {{-- Email address --}}
        <div class="form-group email">
            <label for="name">{{ _i('Email') }}</label>
            <input wire:model="email" type="text" required
                class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" maxlength="64" name="email"
                size="30" value="{{ $user->email }}" />
            @error('email') <span class="small text-error">{{ $message }}</span> @enderror
        </div>

        {{-- Name of the observer --}}
        <div class="form-group" name="name" id="name">
            <label for="name">{{ _i('Name') }}</label>
            <input wire:model="name" type="text" required
                class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" maxlength="64" name="name" size="30"
                value="{{ $user->name }}" />
            @error('name') <span class="small text-error">{{ $message }}</span> @enderror
        </div>

        {{-- Country of residence --}}
        <div class="form-group">
            <label for="country">{{ _i('Country of residence') }}</label>
            <div wire:ignore>
                <select class="form-control countrySel" id="countrySel" name="country">
                    <option value="">&nbsp;</option>
                    @foreach (Countries::getList(LaravelGettext::getLocaleLanguage()) as $code => $country)
                        <option @if ($code == $user->country) selected="selected"
                    @endif value="{{ $code }}">{{ $country }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- about the observer --}}
        <div class="form-group" name="about" id="about">
            <label for="about">{{ _i('Let other people know what are your astronomical interests') }}</label>
            <textarea wire:model="about" required class="form-control {{ $errors->has('about') ? 'is-invalid' : '' }}"
                rows="5" maxlength="500" name="about">{{ $user->about }}</textarea>

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
                    @error('photo')
                    <img class="card-img-top" style="border-radius: 20%" src="/users/{{ $user->id }}/getImage">
                @else
                    @if ($photo)
                        <img class="card-img-top" style="border-radius: 20%" src="{{ $photo->temporaryUrl() }}">
                    @else
                        <img class="card-img-top" style="border-radius: 20%" src="/users/{{ $user->id }}/getImage">
                    @endif
                    @enderror
                </div>
                <div class="col-10" id="card-bg">
                    <div class="card-body">
                        <h5 class="card-title">{{ _i('Change profile picture') . ' (max 10 Mb)' }}</h5>

                        <div class="custom-file">
                            <input type="file" class="custom-file-input {{ $errors->has('photo') ? 'is-invalid' : '' }}"
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

        <div class="form-group form-check sendMail">
            <input type="checkbox" wire:model="sendMail"
                class="form-check-input {{ $errors->has('sendMail') ? 'is-invalid' : '' }}" name="sendMail" @if ($user->sendMail)
            checked
            @endif />
            <label class="form-check-label" for="name">{{ _i('Send emails') }}</label>
        </div>

        <div class="form-group fstOffset">
            <label for="fstOffset">{{ _i('fstOffset') }}</label>
            <input wire:model="fstOffset" type="number" min="-5.0" max="5.0" step="0.01"
                class="form-control {{ $errors->has('fstOffset') ? 'is-invalid' : '' }}" maxlength="4" name="fstOffset"
                size="4" value="{{ $fstOffset }}" />
            <span class="help-block">{{ _i('Offset between measured SQM value and the faintest visible star.') }}</span>
        </div>

        {{ $fstOffset }}
        {{-- <button type="submit"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save
            Photo</button>
    </form> --}}
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.countrySel').select2();
            $('.countrySel').on('change', function(e) {
                @this.set('selected_country', e.target.value);
            });
        });

    </script>
@endpush
