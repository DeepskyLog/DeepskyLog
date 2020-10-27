<div>
    <form role="form" action="/filter" wire:submit.prevent="save">
        @if(!$update)
        {{-- The selection of an existing filter --}}
        <div class="form-group">
            <label for="catalog">{{ _i("Select an existing filter") }}</label>

            @php
            $allFilters = [0 => ''] + \App\Models\Filter::all()->unique('name')->pluck('name', 'id')->toArray();
            @endphp
            <div class="form">
                <div x-data=''>
                    <x-input.select-live-wire-collection wire:model="sel_filter" prettyname="myfilter"
                        :options="$allFilters" selected="('sel_filter')" />
                </div>
            </div>
        </div>

        {{ _i("or specify your filter details manually") }}
        <br /><br />

        @endif

        {{-- The name of the filter --}}
        <div class="form-group name">
            <label for="name">{{ _i("Name") }}</label>
            <input wire:model="name" type="text" required class="form-control @error('name') is-invalid @enderror"
                maxlength="64" name="name" size="30"
                value="@if ($filter->name){{ $filter->name }}@else{{ old('name') }}@endif" />
            @error('name') <span class="small text-error">{{ $message }}<br /></span> @enderror

            <span class="help-block">{{ _i("e.g. Baader O-III") }}</span>
        </div>

        {{-- The type of the filter --}}
        <div class="form-group type">
            <label for="type">{{ _i("Filter type") }}</label>

            @php
            $allFilterTypes = \App\Models\FilterType::all()->pluck('type', 'id')->toArray();
            @endphp
            <div class="form">
                <div x-data=''>
                    <x-input.select-live-wire-collection wire:model="type" prettyname="mytype"
                        :options="$allFilterTypes" selected="('type')" :first="$firsttype" />
                </div>
            </div>
        </div>
        <p hidden>{{ $type }}</p>

        {{-- The color of the filter, only visible if type is 0 or 6 --}}
        @if (!$disableColorFields)
        <div class="form-group">
            <label for="type">{{ _i("Color") }}</label>

            @php
            $allColors = [0 => ''] + \App\Models\FilterColor::all()->pluck('color', 'id')->toArray();
            @endphp

            <x-input.select-live-wire-collection wire:model="color" prettyname="mycolor" :options="$allColors"
                selected="('color')" :first="$firstcolor" />
        </div>

        {{-- wratten number --}}
        <div class="form-group">
            <label for="wratten">{{ _i("Wratten number") }}</label>
            <div class="form">
                <input wire:model="wratten" type="string" class="form-control @error('wratten') is-invalid @enderror"
                    maxlength="5" name="wratten" size="5"
                    value="@if ($filter->wratten > 0){{ $filter->wratten }}@else{{ old('wratten') }}@endif" />
                @error('wratten') <span class="small text-error">{{ $message }}<br /></span> @enderror
            </div>
        </div>

        {{-- Schott number --}}
        <div class="form-group">
            <label for="schott">{{ _i("Schott number") }}</label>
            <div class="form">
                <input wire:model="schott" type="string" class="form-control @error('schott') is-invalid @enderror"
                    maxlength=" 5" name="schott" size="5"
                    value="@if ($filter->schott > 0){{ $filter->schott }}@else{{ old('schott') }}@endif" />
                @error('schott') <span class="small text-error">{{ $message }}<br /></span> @enderror
            </div>
        </div>

        @endif

        {{-- Profile picture --}}
        <div class="form-group">

            <div class="card mb-3">
                <div class="row no-gutters">
                    <div class="col-2" id="card-bg">
                        @if ($photo)
                        <img class="card-img-top" style="border-radius: 20%" src="{{ $photo->temporaryUrl() }}">
                        @endif
                    </div>
                    <div class="col-10" id="card-bg">
                        <div class="card-body">
                            <h5 class="card-title">{{ _i('Upload a picture of your filter.') . ' (max 10 Mb)' }}</h5>

                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('photo') is-invalid @enderror"
                                    wire:model="photo">
                                <label class="custom-file-label">{{ _i('Choose file') }}</label>
                            </div>
                            <div wire:loading wire:target="photo" class="text-sm text-gray-500 italic">
                                {{ _i('Uploading...') }}
                            </div>
                            @error('photo') <br /><span class="small text-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <input type="submit" class="btn btn-success" name="add"
            value="@if ($update){{ _i("Change filter") }}@else{{ _i("Add filter") }}@endif" />
    </form>
</div>
