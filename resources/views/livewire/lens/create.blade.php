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
                <div x-data=''>
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
                            <h5 class="card-title">{{ _i('Upload a picture of your lens.') . ' (max 10 Mb)' }}</h5>

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
            value="@if ($update){{ _i("Change lens") }}@else{{ _i("Add lens") }}@endif" />

    </form>
</div>
