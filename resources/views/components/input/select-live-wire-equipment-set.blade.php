<div x-data x-init="() => {
	var choices = new Choices($refs.{{ $attributes['prettyname'] }}, {
        itemSelectText: '',
	});
	choices.passedElement.element.addEventListener(
	  'change',
	  function(event) {
			values = event.detail.value;
            @this.set('{{ $attributes['wire:model'] }}', values);
            Livewire.emit('equipmentChanged', values);
	  },
	  false,
	);
    let selected = @this.get{!! $attributes['selected'] !!};
	choices.setChoiceByValue(selected);
    }">
    <select class="form-control-sm" id="{{ $attributes['prettyname'] }}" wire-model="{{ $attributes['wire:model'] }}"
        wire:change="{{ $attributes['wire:change'] }}" x-ref="{{ $attributes['prettyname'] }}">
        {!! htmlspecialchars_decode($attributes['options']) !!}
    </select>
</div>
