@if (! empty($panel['subtitle'])){{ $panel['subtitle'] }}<br />@endif
@isset($panel['fov'])
    {{ __('Field of view: :fov', ['fov' => $panel['fov']]) }} · {{ __('Limiting magnitude: :mag', ['mag' => number_format($panel['limit'], 1)]) }}
    @if (! empty($panel['contrast']))
        <br />
        @if ($panel['contrast']['value'] === null)
            {{ __('Contrast reserve: :category', ['category' => __('contrast.reserve.category.not_visible')]) }}
        @else
            {{ __('contrast.reserve.summary', ['value' => number_format($panel['contrast']['value'], 2), 'category' => __('contrast.reserve.category.'.$panel['contrast']['category'])]) }}
        @endif
    @endif
@endisset
