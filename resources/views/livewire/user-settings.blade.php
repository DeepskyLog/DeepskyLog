<div>
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
            class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" maxlength="64" name="email" size="30"
            value="{{ $user->email }}" />
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
