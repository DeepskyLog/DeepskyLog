<div>
    <form role="form" wire:submit.prevent="save">
        {{-- The name of the observation list --}}
        <div class="form-group name">
            <label for="name">{{ _i("Name") }}</label>
            <input wire:model="name" type="text" required class="form-control @error('name') is-invalid @enderror"
                maxlength="100" name="name" size="30" placeholder='{{ _i("Personal observation list") }}'
                value="@if ($observationList->name){{ $observationList->name }}@else{{ old('name') }}@endif" />
            @error('name') <span class="small text-error">{{ $message }}<br /></span> @enderror
            <p class="text-center {{ strlen($name) >= 85 ? 'text-danger' : '' }}">
                <small>
                    {{ strlen($name) . '/100' }}
                </small>
            </p>
        </div>

        {{-- about the observation list --}}
        <div class="mb-4" wire:model.debounce.365ms="description.body">
            <div wire:ignore>
                <label class="block" for="description">
                    {{ _i('Describe your observation list') }}
                </label>
                <input id="body" value="{{ $origDescription }}" type="hidden" name="content">
                <trix-editor class="trix-content" input="body"></trix-editor>
            </div>
            @error('description')
            <p class="text-red-700 font-semibold mt-2">
                {{$message}}
            </p>
            @enderror

            @php
            if ($description) {
            $size = strlen(html_entity_decode(strip_tags($description['body'])));
            } else {
            $size = 0;
            }
            @endphp
            <p class="text-center {{ $size >= 485 ? 'text-danger' : '' }}">
                <small>
                    {{ $size . '/500' }}
                </small>
            </p>
        </div>

        {{-- Discoverable --}}
        <div class=" form-group form-check discoverable">
            <input type="checkbox" wire:model="discoverable" @if ($observationList->discoverable) checked @endif
            class="form-check-input @error('discoverable') is-invalid @enderror" name="discoverable"
            />
            <label class="form-check-label" for="name">{{ _i('Make list discoverable by other observers') }}</label>
        </div>

        <br />
        <input type="submit" class="btn btn-success" name="add"
            value="@if ($update){{ _i("Change observation list") }}@else{{ _i("Add observation list") }}@endif" />

    </form>
</div>
