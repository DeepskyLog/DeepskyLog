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
    @if (! empty($panel['double']))
        <br />
        {{ $panel['double']['label'] }} · {{ __(':separation″ against a limit of :limit″', ['separation' => number_format($panel['double']['separation'], 1), 'limit' => number_format($panel['double']['limit'], 2)]) }}
        @if ($panel['double']['verdict'] !== 'not_split' && $panel['double']['magnification_too_low'])
            <br />{{ __('At :magnification the stars still look like one: use at least :minimum.', ['magnification' => $panel['double']['magnification'] . 'x', 'minimum' => $panel['double']['minimum_magnification'] . 'x']) }}
        @endif
    @endif
@endisset
