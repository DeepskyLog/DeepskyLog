{{-- The lists to take to the telescope (AtTheEyepieceController::index,
     AtTheEyepiece::lists()), each opening its page at the eyepiece: the start
     page of the DeepskyLog GoTo app. Light and without the menu, like the page
     at the eyepiece, and kept by the service worker (public/sw.js). --}}
@php
    $name = fn ($list) => html_entity_decode((string) $list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $user = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="theme-color" content="#000000" />
    <meta name="robots" content="noindex" />
    <title>{{ __('At the eyepiece') }} · {{ config('app.name', 'DeepskyLog') }}</title>
    <link rel="manifest" href="/manifest.webmanifest" />
    <link rel="apple-touch-icon" href="/images/icons/icon-192.png" />
    @include('partials.night-mode', ['nightDefault' => true])
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #000; color: #d1d5db; font: 16px/1.4 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        a { color: #e5e7eb; }
        .bar { position: sticky; top: 0; z-index: 10; display: flex; align-items: center; gap: .75rem; padding: .5rem 1rem; background: #0b0f17; border-bottom: 1px solid #1f2937; }
        .bar .title { flex: 1; min-width: 0; font-weight: 700; color: #f3f4f6; }
        .button { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .55rem .9rem; border: 1px solid #374151; border-radius: .5rem; background: #111827; color: #e5e7eb; font: inherit; text-decoration: none; cursor: pointer; }
        .button[aria-pressed="true"] { border-color: #b91c1c; color: #fca5a5; }
        main { max-width: 56rem; margin: 0 auto; padding: 1rem; }
        h2 { margin: 1.25rem 0 .5rem; font-size: .8rem; text-transform: uppercase; letter-spacing: .08em; color: #9ca3af; }
        ul.lists { margin: 0; padding: 0; list-style: none; }
        ul.lists li + li { margin-top: .5rem; }
        ul.lists a { display: block; padding: .8rem 1rem; border: 1px solid #1f2937; border-radius: .75rem; background: #0b0f17; text-decoration: none; }
        ul.lists a:active { background: #1f2937; }
        .name { display: block; font-size: 1.2rem; font-weight: 700; color: #f3f4f6; }
        .meta { display: block; margin-top: .15rem; font-size: .9rem; color: #9ca3af; }
        .badge { display: inline-block; margin-left: .4rem; padding: 0 .4rem; border: 1px solid #b91c1c; border-radius: .3rem; font-size: .75rem; font-weight: 600; color: #fca5a5; vertical-align: middle; }
        .muted { color: #9ca3af; font-size: .9rem; }
        .row { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <header class="bar">
        <span class="title">{{ __('At the eyepiece') }}</span>
        <button type="button" class="button" data-dsl-night-toggle aria-pressed="false" onclick="window.dslToggleNightMode()">{{ __('Red') }}</button>
    </header>

    <main>
        <p class="muted">{{ __('Choose the observing list to take to the telescope.') }}</p>

        @foreach (['planned' => __('Planned'), 'other' => __('Other lists')] as $group => $heading)
            @if ($$group->isNotEmpty())
                <h2>{{ $heading }}</h2>
                <ul class="lists">
                    @foreach ($$group as $list)
                        <li>
                            <a href="{{ route('observing-list.eyepiece', $list) }}">
                                <span class="name">
                                    {{ $name($list) }}
                                    @if ($list->id === $activeId)
                                        <span class="badge">{{ __('Active') }}</span>
                                    @endif
                                </span>
                                <span class="meta">
                                    {{ implode(' · ', array_filter([
                                        trans_choice('{1} :count object|[2,*] :count objects', $list->items_count, ['count' => $list->items_count]),
                                        $list->timed_count > 0 && $list->planned_night
                                            ? ($list->planned_night->toDateString() === $tonight ? __('planned for tonight') : __('planned for the night of :night', ['night' => $list->planned_night->translatedFormat('j F Y')]))
                                            : null,
                                        $list->owner_user_id !== $user->id ? __('by :name', ['name' => html_entity_decode((string) $list->owner?->name)]) : null,
                                    ])) }}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        @endforeach

        @if ($planned->isEmpty() && $other->isEmpty())
            <p>{{ __('You have no observing lists with objects yet.') }}</p>
        @endif

        <div class="row">
            <a class="button" href="{{ route('observing-list.night') }}">{{ __('Make a list for a night') }}</a>
            <a class="button" href="{{ route('observing-lists.index') }}">{{ __('My observing lists') }}</a>
        </div>
    </main>

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(function () {});
        }
    </script>
</body>
</html>
