<h1>{{ __('Field views of :object', ['object' => $result['object']->name]) }}</h1>
<div class="meta">
    {{ $result['moment']->format('Y-m-d H:i') }} ({{ $result['timezone'] }})
    @if ($result['location']) · {{ html_entity_decode($result['location']->name) }} @endif
    · {{ __('Limiting magnitude of the sky: :nelm', ['nelm' => number_format($result['nelm'], 1)]) }}
</div>
