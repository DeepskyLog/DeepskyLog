<div>
    <div wire:ignore>
        <select id="catalog" class="form-control" name="state">
            <option value=""></option>
            @foreach (\App\TargetName::getCatalogs() as $catalog)
                <option value="{{ $catalog->catalog }}">{{ $catalog->catalog }}</option>
            @endforeach
        </select>
    </div>

    @if ($selected_catalog)
        <br>
        <div wire:loading.remove>
            <h3>{{ $selected_catalog }}</h3>
            @php $data = \App\Target::getCatalogData($selected_catalog); @endphp
            {{ _i("Number of objects") . ": " . $data[0]->count() }}

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover">
                    @php $counter = 0; @endphp
                    @foreach ($data[1] as $const=>$count)
                        @if ($counter % 3 == 0)
                            <tr>
                        @endif
                        <td>{{ $const . ': ' . $count}}</td>
                        @if ($counter % 3 == 2)
                            </tr>
                        @endif
                        @php $counter++; @endphp
                    @endforeach
                </table>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover">
                    @php $counter = 0; @endphp
                    @foreach ($data[2] as $type=>$count)
                        @if ($counter % 3 == 0)
                            <tr>
                        @endif
                        <td>{{ $type . ': ' . $count}}</td>
                        @if ($counter % 3 == 2)
                            </tr>
                        @endif
                        @php $counter++; @endphp
                    @endforeach
                </table>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover">
                    @php $counter = 0; @endphp
                    @foreach ($data[0] as $target)
                        @if ($counter % 3 == 0)
                            <tr>
                        @endif
                        <td>
                            <a href="/target/{{ $target->altname }}">{{ $target->altname }}
                            @if ($target['altname'] != $target->target->target_name)
                                ({{ $target->target->target_name }})
                            @endif
                        </td>
                        @if ($counter % 3 == 2)
                            </tr>
                        @endif
                        @php $counter++; @endphp
                    @endforeach
                </table>
            </div>
        </div>

        <div wire:loading>
            {{ _i('Loading catalog info...') }}
        </div>
    @else
        <br />
        <div wire:loading.remove>
            {{ _i('Select a list to view its details') }}
        </div>

        <div wire:loading>
            {{ _i('Loading catalog info...') }}
        </div>
    @endif
</div>


@push('scripts')
<script>
    $(document).ready(function() {
        $('#catalog').select2();
        $('#catalog').on('change', function (e) {
            @this.set('selected_catalog', e.target.value);
        });
    });
</script>
@endpush
