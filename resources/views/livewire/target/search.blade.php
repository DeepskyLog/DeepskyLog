<div>
    <form method="POST" action="/target">
        @csrf
        <div class="form-group row">
            <label for="catalog" class="col-sm-2 col-form-label">{{ _('Object name') }}</label>
            <div class="col-sm-5">
                <div x-data=''>
                    <x-input.select-live-wire wire:model="catalog" prettyname="mycatalog" :options="$allCatalogs"
                        selected="('catalog')" name="catalogname" />
                </div>
            </div>
            <div class="col-sm-2">
                <input type="text" placeholder="{{ _i('Enter number in catalog') }}" class="form-control"
                    name="catalog">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-10">
                <button type="submit" class="btn btn-primary">{{ _i('Search') }}</button>
                <button type="" class="btn btn-danger">{{ _i('Clear fields') }}</button>
            </div>
        </div>
    </form>
</div>
