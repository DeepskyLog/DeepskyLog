<div class="form-group" name="about" id="about">
    <label for="about">{{ _i('Let other people know what are your astronomical interests') }}</label>
    <textarea wire:model="about" required class="form-control {{ $errors->has('about') ? 'is-invalid' : '' }}" rows="5" maxlength="500" name="about">
{{ $user->about }}</textarea>

<p class="text-center {{ strlen($about) >= 485 ? 'text-danger' : ''}}">
    <small>
        {{ strlen($about) . '/500' }}
    </small>
</p>
</div>
