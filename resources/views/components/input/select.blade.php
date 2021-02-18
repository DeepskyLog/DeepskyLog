<div x-data x-init="() => {
	var choices = new Choices($refs.{{ $attributes['id'] }}, {
		itemSelectText: '',
        shouldSort: false
	});
	choices.passedElement.element.addEventListener(
	  'change',
	  function(event) {
			values = event.detail.value;
	  },
	  false,
	);
	let selected = parseInt(@this.get{!! $attributes['selected'] !!}).toString();
	choices.setChoiceByValue(selected);
    }">
    <select class="form-control form-control-sm" id="{{ $attributes['id'] }}" name="{{ $attributes['id'] }}"
        x-ref="{{ $attributes['id'] }}">
        {!! htmlspecialchars_decode($attributes['options']) !!}
    </select>
</div>
