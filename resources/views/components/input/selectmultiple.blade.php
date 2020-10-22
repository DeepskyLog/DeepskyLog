<div x-data x-init="() => {
	var choices = new Choices($refs.{{ $attributes['prettyname'] }}, {
		itemSelectText: '',
		removeItems: true,
	    removeItemButton: true,
	});
	choices.passedElement.element.addEventListener(
	  'change',
	  function(event) {
	  		values = getSelectValues($refs.{{ $attributes['prettyname'] }});
	  },
	  false,
	);
	{{-- items = {!! $attributes['selected'] !!};
	if(Array.isArray(items)){
		items.forEach(function(select) {
			choices.setChoiceByValue((select).toString());
		});
	} --}}
	}
	function getSelectValues(select) {
	  var result = [];
	  var options = select && select.options;
	  var opt;
	  for (var i=0, iLen=options.length; i<iLen; i++) {
	    opt = options[i];
	    if (opt.selected) {
	      result.push(opt.value || opt.text);
	    }
	  }
	  return result;
	}
	">
    <select name="{{ $attributes['name'] }}" id="{{ $attributes['prettyname'] }}"
        x-ref="{{ $attributes['prettyname'] }}" multiple="multiple">
        @if(count($attributes['options'])>0)
        @foreach($attributes['options'] as $key=>$option)
        <option value="{{$key}}">{{$option}}</option>
        @endforeach
        @endif
    </select>
</div>
