<div>
    <div>
        @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
        @endif
    </div>
    <h4>
        <!-- We have to check if admin is part of request. -->
        @php if (strpos(Request::url(), 'admin') !== false) {
        echo _i("All equipment sets");
        } else {
        echo _i("Equipment sets of %s", Auth::user()->name);
        }
        @endphp
    </h4>
    <hr />
    @if (strpos(Request::url(), 'admin') === false)
    @if (!$showAddSetField)
    <a wire:click="newSet" class="btn btn-success">
        {{ _i("New equipment set") }}
    </a>
    @endif
    @if ($showAddSetField)
    {{-- If the user clicks on the 'Add set' button, a field appears where the name of a new set can be set. --}}
    {{ _i("An equipment set is a combination of any number of instruments, eyepieces, filters, and lenses.") }}
    <br /><br />
    {{ _i("You can define an equipment set for anything you want.  You can for example create different instrument sets for the equipment you use in different locations.  Or you can create an equipment set for an old telescope that you don't use any longer.") }}
    <br /><br />
    {{ _i("When you add an observation, you will only see the equipment that is defined in the active equipment set.") }}
    <br /><br />

    {{-- Add the form to add a new equipment set --}}
    <form role="form" wire:submit.prevent="save">
        {{-- The name of the equipment set --}}
        <div class="form-group name">
            <label for="name">
                {{ _i("Name") }}
            </label>
            <input wire:model="name" type="text" required class="form-control @error('name') is-invalid @enderror"
                maxlength="100" name="name" size="30" value="{{ old('name') }}" />
            @error('name') <span class="small text-error">{{ $message }}<br /></span> @enderror
            <p class="text-center {{ strlen($name) >= 85 ? 'text-danger' : '' }}">
                <small>
                    {{ strlen($name) . '/100' }}
                </small>
            </p>
        </div>

        {{-- about the set --}}
        <div class="mb-4" wire:model.debounce.365ms="description.body">
            <div wire:ignore>
                <label class="block" for="description">
                    {{ _i('Describe your equipment set') }}
                </label>
                <input id="body" value="" type="hidden" name="content">
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

        {{-- Submit button --}}
        <div>
            @if (!$errors->isEmpty())
            <div class="alert alert-danger">
                {{  _i('Please fix the errors.') }}
            </div>
            @else
            <input type="submit" class="btn btn-success" name="add" value="{{ _i('Create new equipment set') }}" />
            @endif
            @if (session()->has('message'))
            <br /><br />
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
            @endif
        </div>

    </form>
    @endif
    <br /><br />
    <livewire:equipment-set-table hideable="select" exportable />

    {{-- Use trix rich text editor for the description
        - To show the formatted text: class="trix-content"
        - Use trix for adding the about field for the observer
        --}}
    {{-- When a set is clicked, add the possibility to add and remove new Eyepieces, Instruments, ... and to change the name and description of the set.--}}
    {{-- The table should show the number of filters, ... --}}
    {{-- Show sets in detail of eyepiece, filter, ... --}}
    {{-- Use sets in overview of observer, and in eyepiece view --}}
    {{-- Other users should not be able to see the sets --}}
    {{-- Show all sets for the administrator --}}
    @endif
</div>
