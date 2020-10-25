<div>
    @php
    $allCatalogs = '<option value=""></option>';
    foreach (\App\Models\TargetName::getCatalogs() as $catalog) {
    $allCatalogs .= '<option value="' . $catalog . '">' . $catalog . '</option>';
    }
    @endphp

    <div x-data=''>
        <x-input.select-live-wire wire:model="selected_catalog" prettyname="mycatalog" :options="$allCatalogs"
            selected="('selected_catalog')" />
    </div>

    @if ($selected_catalog)
    <br>
    <div wire:loading.remove>
        <h3>{{ $selected_catalog }}</h3>
        @php $data = \App\Models\Target::getCatalogData($selected_catalog); @endphp
        {{ _i('Number of objects') . ': ' . $data[0]->count() }}

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover">
                @php $counter = 0; @endphp
                @foreach ($data[1] as $const => $count)
                @if ($counter % 3 == 0)
                <tr>
                    @endif
                    <td>{{ $const . ': ' . $count }}</td>
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
                @foreach ($data[2] as $type => $count)
                @if ($counter % 3 == 0)
                <tr>
                    @endif
                    <td>{{ $type . ': ' . $count }}</td>
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
