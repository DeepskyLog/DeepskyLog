<div>
    <br />
    <form wire:submit.prevent="save" role="form" action="/users/{{ $user->id }}/settings">

        {{-- Standard object field of views --}}
        {{ _i('Atlas standard object FoVs:') }}
        <div class="form-group">
            <div class="row">
                <div class="col">
                    <label>{{ _i('Overview') }} ('')</label>
                    <input wire:model='overviewFov' type="number" min="1" max="3600"
                        class="inputfield centered form-control" name="overviewFoV" value="{{ $overviewFov }}" />
                    @error('overviewFov') <span class="small text-error">{{ $message }}</span> @enderror
                </div>
                <div class="col">
                    <label>{{ _i('Lookup') }} ('')</label>
                    <input wire:model='lookupFov' type="number" min="1" max="3600"
                        class="inputfield centered form-control" name="lookupFoV" value="{{ $lookupFov }}" />
                    @error('lookupFov') <span class="small text-error">{{ $message }}</span> @enderror
                </div>
                <div class="col">
                    <label>{{ _i('Detail') }} ('')</label>
                    <input wire:model='detailFov' type="number" min="1" max="3600"
                        class="inputfield centered form-control" name="detailFoV" value="{{ $detailFov }}" />
                    @error('detailFov') <span class="small text-error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Standard object magnitudes --}}
        {{ _i('Atlas standard object magnitudes:') }}
        <div class="form-group">
            <div class="row">
                <div class="col">
                    <label>{{ _i('Overview') }}</label>
                    <input wire:model='overviewObjectMagnitude' type="number" min="1.0" max="20.0" step="0.1"
                        class="inputfield centered form-control" name="overviewdsos"
                        value="{{ $overviewObjectMagnitude }}" />
                    @error('overviewObjectMagnitude') <span class="small text-error">{{ $message }}</span> @enderror
                </div>
                <div class="col">
                    <label>{{ _i('Lookup') }}</label>
                    <input wire:model='lookupObjectMagnitude' type="number" min="1.0" max="20.0" step="0.1"
                        class="inputfield centered form-control" name="lookupdsos"
                        value="{{ $lookupObjectMagnitude }}" />
                    @error('lookupObjectMagnitude') <span class="small text-error">{{ $message }}</span> @enderror
                </div>
                <div class="col">
                    <label>{{ _i('Detail') }}</label>
                    <input wire:model='detailObjectMagnitude' type="number" min="1.0" max="20.0" step="0.1"
                        class="inputfield centered form-control" name="detaildsos"
                        value="{{ $detailObjectMagnitude }}" />
                    @error('detailObjectMagnitude') <span class="small text-error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Standard star magnitudes --}}
        {{ _i('Atlas standard star magnitudes:') }}
        <div class="form-group">
            <div class="row">
                <div class="col">
                    <label>{{ _i('Overview') }}</label>
                    <input wire:model='overviewStarMagnitude' type="number" min="1.0" max="20.0" step="0.1"
                        class="inputfield centered form-control" name="overviewstars"
                        value="{{ $overviewStarMagnitude }}" />
                    @error('overviewStarMagnitude') <span class="small text-error">{{ $message }}</span> @enderror
                </div>
                <div class="col">
                    <label>{{ _i('Lookup') }}</label>
                    <input wire:model='lookupStarMagnitude' type="number" min="1.0" max="20.0" step="0.1"
                        class="inputfield centered form-control" name="lookupstars"
                        value="{{ $lookupStarMagnitude }}" />
                    @error('lookupStarMagnitude') <span class="small text-error">{{ $message }}</span> @enderror
                </div>
                <div class="col">
                    <label>{{ _i('Detail') }}</label>
                    <input wire:model='detailStarMagnitude' type="number" min="1.0" max="20.0" step="0.1"
                        class="inputfield centered form-control" name="detailstars"
                        value="{{ $detailStarMagnitude }}" />
                    @error('detailStarMagnitude') <span class="small text-error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Standard photo sizes --}}
        {{ _i('Standard size of photos:') }}
        <div class="form-group">
            <div class="row">
                <div class="col">
                    <label>{{ _i('Photo 1') }}</label>
                    <input wire:model='photosize1' type="number" min="1" max="3600"
                        class="inputfield centered form-control" name="photosize1" value="{{ $photosize1 }}" />
                    @error('photosize1') <span class="small text-error">{{ $message }}</span> @enderror
                </div>
                <div class="col">
                    <label>{{ _i('Photo 2') }}</label>
                    <input wire:model='photosize2' type="number" min="1" max="3600"
                        class="inputfield centered form-control" name="photosize2" value="{{ $photosize2 }}" />
                    @error('photosize2') <span class="small text-error">{{ $message }}</span> @enderror
                </div>
                <div class="col">
                </div>
            </div>
        </div>

        {{ _i('Font size printed atlas pages (6..9)') }}
        <div class="form-group">
            <div class="row">
                <div class='col'>
                    <input wire:model='atlaspagefont' type="number" min="6" max="9"
                        class="inputfield centered form-control" maxlength="1" name="atlaspagefont" size="5"
                        value="{{ $atlaspagefont }}" />
                    @error('atlaspagefont') <span class="small text-error">{{ $message }}</span> @enderror
                </div>
                <div class="col">
                </div>
                <div class="col">
                </div>
            </div>
        </div>

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

    </form>
</div>
