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

        {{-- Tags --}}
        <div class="form-group type">
            <label
                for="type">{{ _i("Add tags to make your observation list easier to discover by other observers") }}</label>

            @php
            $allTags = \Spatie\Tags\Tag::where('type', 'ObservationList')->get()->pluck('name', 'name')->toArray();
            $list = \App\Models\ObservationList::where('slug', $slug)->first();
            if ($list) {
            $tags = $list->tags()->get()->pluck('name')->flatten();
            $selected = (json_encode($tags));
            } else {
            $selected = json_encode(['']);
            }
            @endphp

            <div class="form">
                <div x-data wire:ignore>
                    <x-input.select-live-wire-multiple wire:model="tags" prettyname="tags" :options="$allTags"
                        :selected="$selected" />
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="tagInput">{{ _i("You can also manually add a new tag") }}</label>
            <input type="text" wire:model="newTag" placeholder="{{ _i("Add a new tag") }}"
                class="form-control @error('newTag') is-invalid @enderror" name="newTag" size="30" />
            @error('newTag') <span class="small text-error">{{ $message }}<br /></span> @enderror
        </div>

        {{-- Discoverable --}}
        <div class="form-group form-check discoverable">
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
