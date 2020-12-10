<div>
    <h4>
        {{ $target->target_name }}

        @php
        $targets = $target->getNearbyObjects($zoomDiameter);
        $targetid = $target->id;
        @endphp
        {{ count($targets->get()) > 2
        ? _i(' and ') . (count($targets->get()) - 1) . _i(' nearby objects')
        : (count($targets->get()) > 1
        ? _i(' and ') . '1' . _i(' nearby object') :
        _i(" - there are no other objects within the specified distance"))
    }}
        <span class="float-right">
            <form class="form-inline" method="get">
                <div>
                    {{ _i("up to about ") }}
                    <select wire:model="zoom" wire:prevent class="form-control" name="zoom">
                        <option value="180">3x3&deg;</option>
                        <option value="120">2x2&deg;</option>
                        <option value="60">1x1&deg;</option>
                        <option value="30">30x30'</option>
                        <option value="15">15x15'</option>
                        <option value="10">10x10'</option>
                        <option value="5">5x5'</option>
                    </select>
                </div>
            </form>
        </span>
    </h4>
    <br />
    <livewire:nearby-table hideable="select" exportable :zoom='$zoomDiameter' :slug='$target->slug' />
</div>
