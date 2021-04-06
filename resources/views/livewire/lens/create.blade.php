<div>
    <form role="form" action="/lens" wire:submit.prevent="save">
        @if(!$update)
        {{-- The selection of an existing lens --}}
        <div class="form-group">
            <label for="catalog">{{ _i("Select an existing lens") }}</label>

            @php
            $allLenses = [0 => ''] + \App\Models\Lens::all()->unique('name')->pluck('name', 'id')->toArray();
            @endphp
            <div class="form">
                <div x-data='' wire:ignore>
                    <x-input.select-live-wire-collection wire:model="sel_lens" prettyname="mylens" :options="$allLenses"
                        selected="('sel_lens')" />
                </div>
            </div>
        </div>

        {{ _i("or specify your lens details manually") }}
        <br /><br />
        @endif

        <div class="form-group">
            <label for="name">{{ _i("Name") }}</label>
            <input wire:model="name" type="text" required class="form-control @error('name') is-invalid @enderror"
                maxlength="64" name="name" size="30"
                value="@if ($lens->name){{ $lens->name }}@else{{ old('name') }}@endif" />
            @error('name') <span class="small text-error">{{ $message }}<br /></span> @enderror
            <span class="help-block">{{ _i("e.g. Televue 2x Barlow") }}</span>
        </div>

        <div class="form-group">
            <label for="factor">{{ _i("Factor") }}</label>
            <input wire:model="factor" type="number" min="0.01" max="9.99" required step="0.01"
                class="form-control @error('factor') is-invalid @enderror" maxlength="5" name="factor" size="5"
                value="@if ($lens->factor > 0){{ $lens->factor }}@else{{ old('factor') }}@endif" />
            @error('factor') <span class="small text-error">{{ $message }}<br /></span> @enderror
            <span class="help-block">{{ _i("> 1.0 for Barlow lenses, < 1.0 for shapley lenses.") }}</span>
        </div>

        {{-- Filter picture --}}
        {{ _i('Upload a picture of your lens.') . ' (max 10 Mb)' }}

        <x-media-library-attachment rules="max:10240" name="media" />

        <br />
        <input type="submit" class="btn btn-success" name="add"
            value="@if ($update){{ _i("Change lens") }}@else{{ _i("Add lens") }}@endif" />

    </form>
</div>
