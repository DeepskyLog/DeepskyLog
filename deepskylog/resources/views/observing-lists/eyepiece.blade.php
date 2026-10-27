{{-- At the eyepiece (AtTheEyepieceController, App\Services\AtTheEyepiece): a page
     of its own, without the menu, the ephemeris aside or the Vite build, so
     it is light and works from the cache of the service worker (public/sw.js)
     when there is no connection. --}}
@php
    $listName = html_entity_decode($list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $baseUrl = route('observing-list.eyepiece', $list);
    $itemUrl = fn ($entry) => route('observing-list.eyepiece', ['list' => $list, 'item' => $entry['item']->id]);
    $time = fn ($moment) => $moment ? $moment->format('H:i') : null;
    $showViews = $views ? array_filter([
        'finder' => isset($views['views']['finder']['svg']) ? $views['views']['finder'] : null,
        'telescope' => isset($views['views']['telescope']['svg']) ? $views['views']['telescope'] : null,
        'naked_eye' => isset($views['views']['naked_eye']['svg']) ? $views['views']['naked_eye'] : null,
    ]) : [];
    $currentObject = $current['object'];
    $scale = \App\Services\AtTheEyepiece::visibilityScale($currentObject);
    $scaleSize = $scale === 'visibility_ds' ? 3 : 7;
    $user = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="theme-color" content="#000000" />
    <meta name="robots" content="noindex" />
    <title>{{ $current['name'] }} · {{ __('At the eyepiece') }} · {{ config('app.name', 'DeepskyLog') }}</title>
    <link rel="manifest" href="/manifest.webmanifest" />
    <link rel="apple-touch-icon" href="/images/icons/icon-192.png" />
    @include('partials.night-mode', ['nightDefault' => true])
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #000; color: #d1d5db; font: 16px/1.4 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        a { color: #e5e7eb; }
        .bar { position: sticky; top: 0; z-index: 10; display: flex; align-items: center; gap: .75rem; padding: .5rem 1rem; background: #0b0f17; border-bottom: 1px solid #1f2937; }
        .bar .back { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-decoration: none; color: #9ca3af; }
        .clock { font-variant-numeric: tabular-nums; font-size: 1.25rem; font-weight: 700; color: #f3f4f6; }
        .button { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .55rem .9rem; border: 1px solid #374151; border-radius: .5rem; background: #111827; color: #e5e7eb; font: inherit; text-decoration: none; cursor: pointer; }
        .button[aria-pressed="true"] { border-color: #b91c1c; color: #fca5a5; }
        .button:disabled { opacity: .5; cursor: default; }
        main { max-width: 56rem; margin: 0 auto; padding: 1rem; }
        .flash, .notice, .error { margin: 0 0 1rem; padding: .6rem .8rem; border-radius: .5rem; font-size: .9rem; }
        .flash { background: #052e16; border: 1px solid #166534; color: #bbf7d0; }
        .notice { background: #1c1917; border: 1px solid #44403c; color: #d6d3d1; }
        .error { background: #2a0a0a; border: 1px solid #7f1d1d; color: #fecaca; }
        .label { font-size: .8rem; text-transform: uppercase; letter-spacing: .08em; color: #9ca3af; }
        h1 { margin: .15rem 0 .25rem; font-size: clamp(2rem, 8vw, 3.25rem); line-height: 1.05; color: #fff; }
        .done { color: #4ade80; font-size: .6em; vertical-align: middle; }
        .meta { margin: 0; color: #d1d5db; }
        .note { margin: .5rem 0 0; color: #e5e7eb; }
        .muted { color: #9ca3af; font-size: .9rem; }
        .log { display: block; width: 100%; margin: 1rem 0 .5rem; padding: 1.1rem; border: 0; border-radius: .75rem; background: #b91c1c; color: #fff; font-size: 1.6rem; font-weight: 800; text-align: center; text-decoration: none; cursor: pointer; }
        .row { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }
        .row.spread { justify-content: space-between; }
        .tabs { display: flex; gap: .4rem; margin: 1rem 0 .5rem; }
        .tabs .button[aria-selected="true"] { background: #1f2937; border-color: #6b7280; color: #fff; }
        .view svg { display: block; width: 100%; max-width: 36rem; height: auto; margin: 0 auto; }
        .view[hidden] { display: none; }
        section { margin-bottom: 1.25rem; }
        .card { padding: 1rem; border: 1px solid #1f2937; border-radius: .75rem; background: #0b0f17; }
        .next h2 { margin: .1rem 0; font-size: 1.5rem; color: #f3f4f6; }
        ol.all { margin: .5rem 0 0; padding-left: 1.5rem; }
        ol.all li { padding: .2rem 0; }
        ol.all li.is-current { font-weight: 700; color: #fff; }
        ol.all .time { font-variant-numeric: tabular-nums; color: #9ca3af; margin-right: .4rem; }
        dialog { width: min(32rem, calc(100vw - 2rem)); padding: 1rem; border: 1px solid #374151; border-radius: .75rem; background: #0b0f17; color: #e5e7eb; }
        dialog::backdrop { background: rgba(0, 0, 0, .8); }
        dialog label { display: block; margin: .75rem 0 .25rem; font-size: .85rem; color: #9ca3af; }
        dialog select, dialog textarea { width: 100%; padding: .5rem; border: 1px solid #374151; border-radius: .5rem; background: #000; color: #f3f4f6; font: inherit; }
        .queue li { margin: .35rem 0; }
        .queue .problem { color: #fca5a5; }
        .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }
    </style>
</head>
<body>
    <header class="bar">
        <a class="back" href="{{ route('observing-list.show', $list) }}">&larr; {{ $listName }}</a>
        <span class="clock" id="clock" title="{{ __('Local time at :location', ['location' => html_entity_decode((string) ($location?->name ?? $timezone))]) }}">{{ $now->format('H:i') }}</span>
        <button type="button" class="button" data-dsl-night-toggle aria-pressed="false" onclick="window.dslToggleNightMode()">{{ __('Red') }}</button>
    </header>

    <main>
        @if (session('success'))
            <p class="flash" role="status">{{ session('success') }}</p>
        @endif
        @if (! $timed)
            <p class="notice">
                {{ __('This list has no times: the objects are shown in the order of the list. Plan the times on the page of the list to see the object to observe now and follow the clock.') }}
                <a href="{{ route('observing-list.show', $list) }}">{{ __('Plan the times') }}</a>
            </p>
        @elseif ($shift_days !== 0)
            <p class="notice">
                {{ __('The times were planned for the night of :night and are used for tonight; the objects stand about four minutes earlier for every day since. Plan the times again for tonight on the page of the list.', ['night' => \Carbon\Carbon::parse($planned_night)->translatedFormat('j F Y')]) }}
            </p>
        @endif

        {{-- The object to observe now --}}
        <section aria-labelledby="current-name">
            <div class="label">
                @if (! $current['at'])
                    {{ __('Object :number of :count', ['number' => $index + 1, 'count' => $entries->count()]) }}
                @elseif ($current['at']->isFuture())
                    {{ __('First object, at :time', ['time' => $time($current['at'])]) }}
                    · <span data-relative="{{ $current['at']->timestamp }}"></span>
                @else
                    {{ __('Now, planned at :time', ['time' => $time($current['at'])]) }}
                @endif
                @if ($current['at'])
                    · {{ $index + 1 }} / {{ $entries->count() }}
                @endif
            </div>
            <h1 id="current-name">
                {{ $current['name'] }}
                @if ($current['done'])
                    <span class="done" title="{{ __('Observed tonight') }}">✓ <span class="sr-only">{{ __('Observed tonight') }}</span></span>
                @endif
            </h1>
            <p class="meta">
                {{ implode(' · ', array_filter([
                    $details['type'],
                    $details['constellation'],
                    $details['magnitude'] !== null ? __('mag. :mag', ['mag' => number_format($details['magnitude'], 1)]) : null,
                    $details['size'],
                ])) }}
            </p>
            @if ($current['from'] && $current['until'])
                <p class="muted">{{ __('High enough from :from to :until', ['from' => $time($current['from']), 'until' => $time($current['until'])]) }}</p>
            @endif
            @if (!empty($views['views']['naked_eye']['subtitle']))
                <p class="muted">{{ $views['views']['naked_eye']['subtitle'] }}</p>
            @endif
            @if ($current['note'])
                <p class="note">{{ html_entity_decode(strip_tags((string) $current['note']), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</p>
            @endif

            <a class="log" id="log-it" href="{{ route('observing-list.eyepiece.log', ['list' => $list, 'item' => $current['item']->id]) }}">{{ __('Log it') }}</a>
            <div class="row spread">
                <button type="button" class="button" id="quick-log-open">{{ __('Quick note') }}</button>
                @if ($session)
                    <span class="muted">{{ __('Session: :name', ['name' => html_entity_decode((string) $session->name)]) }}</span>
                @endif
            </div>
            {{-- SkySafari for iOS centres the object of a skyobject:// link, its
                 GoTo then moves the telescope SkySafari is connected to; SkySafari
                 for Android ignores the link, so the button is shown on iOS only --}}
            <div class="row" style="margin-top:.5rem">
                <a class="button" id="skysafari" href="skyobject://{{ rawurlencode($current['name']) }}" hidden>{{ __('Show in SkySafari') }}</a>
                <button type="button" class="button" id="copy-name">{{ __('Copy name') }}</button>
                <span class="muted" id="copy-name-status" role="status"></span>
            </div>
        </section>

        {{-- Previous, next, following the clock --}}
        <nav class="row spread" aria-label="{{ __('Objects') }}">
            @if ($previous)
                <a class="button" data-pin href="{{ $itemUrl($previous) }}">&larr; {{ __('Previous') }}</a>
            @else
                <span></span>
            @endif
            @if ($timed)
                <a class="button" id="follow" href="{{ $baseUrl }}" @if ($following) aria-pressed="true" @endif>{{ __('Follow the clock') }}</a>
            @endif
            @if ($next)
                <a class="button" data-pin href="{{ $itemUrl($next) }}">{{ __('Next') }} &rarr;</a>
            @else
                <span></span>
            @endif
        </nav>

        {{-- Charts --}}
        <section>
            @if ($showViews !== [])
                <div class="tabs" role="tablist">
                    @foreach ($showViews as $key => $view)
                        <button type="button" class="button" role="tab" data-view="{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $view['title'] }}</button>
                    @endforeach
                </div>
                @foreach ($showViews as $key => $view)
                    <div class="view" id="view-{{ $key }}" @unless ($loop->first) hidden @endunless>
                        <p class="muted">
                            {{ implode(' · ', array_filter([
                                $view['subtitle'] ?? null,
                                __('Field of view: :fov', ['fov' => $view['fov']]),
                                __('Limiting magnitude: :mag', ['mag' => number_format($view['limit'], 1)]),
                            ])) }}
                        </p>
                        @if (! empty($view['contrast']['category']) && $view['contrast']['value'] !== null)
                            <p class="muted">{{ __('contrast.reserve.summary', ['value' => number_format($view['contrast']['value'], 2), 'category' => __('contrast.reserve.category.'.$view['contrast']['category'])]) }}</p>
                        @endif
                        @if (! empty($view['double']))
                            <p class="muted">{{ $view['double']['label'] }}</p>
                        @endif
                        {!! $view['svg'] !!}
                    </div>
                @endforeach
                @if (isset($views['views']['finder']['message']))
                    <p class="muted">{{ $views['views']['finder']['message'] }}</p>
                @endif
                @foreach ($views['warnings'] ?? [] as $warning)
                    <p class="muted">{{ $warning }}</p>
                @endforeach
                <p class="muted">
                    {{ __('Drawn for :time at :location.', ['time' => $time($views['moment']), 'location' => html_entity_decode((string) ($views['location']?->name ?? '—'))]) }}
                    <a href="{{ route('object.field-views', ['slug' => $current['slug'], 'datetime' => $views['moment']->format('Y-m-d\TH:i')]) }}">{{ __('Naked eye, finder and telescope') }}</a>
                </p>
            @else
                <p class="muted">{{ __('There is no finder chart for :object.', ['object' => $current['name']]) }}</p>
            @endif
        </section>

        {{-- The next object --}}
        <section class="card next">
            @if ($next)
                <div class="label">
                    @if ($next['at'])
                        {{ __('Next, at :time', ['time' => $time($next['at'])]) }} · <span data-relative="{{ $next['at']->timestamp }}"></span>
                    @else
                        {{ __('Next') }}
                    @endif
                </div>
                <h2>
                    <a href="{{ $itemUrl($next) }}" data-pin>{{ $next['name'] }}</a>
                    @if ($next['done'])
                        <span class="done" title="{{ __('Observed tonight') }}">✓</span>
                    @endif
                </h2>
                <p class="meta">
                    {{ implode(' · ', array_filter([
                        $nextDetails['type'],
                        $nextDetails['constellation'],
                        $nextDetails['magnitude'] !== null ? __('mag. :mag', ['mag' => number_format($nextDetails['magnitude'], 1)]) : null,
                        $nextDetails['size'],
                    ])) }}
                </p>
            @else
                <p class="muted">{{ __('This is the last object of the list.') }}</p>
            @endif
        </section>

        {{-- Observations kept on this device until they are sent --}}
        <section class="card" id="queue" hidden>
            <div class="label">{{ __('Waiting to be sent') }}</div>
            <ul class="queue" id="queue-list"></ul>
            <p class="muted" id="queue-status"></p>
            <button type="button" class="button" id="queue-send">{{ __('Send now') }}</button>
        </section>

        {{-- All objects of the night --}}
        <section class="card">
            <details>
                <summary>{{ $timed ? __('All objects with a time (:count)', ['count' => $entries->count()]) : __('All objects (:count)', ['count' => $entries->count()]) }}</summary>
                <ol class="all">
                    @foreach ($entries as $i => $entry)
                        <li @class(['is-current' => $i === $index])>
                            @if ($entry['at'])
                                <span class="time">{{ $time($entry['at']) }}</span>
                            @endif
                            <a href="{{ $itemUrl($entry) }}" data-pin>{{ $entry['name'] }}</a>
                            @if ($entry['done'])
                                <span class="done" title="{{ __('Observed tonight') }}">✓</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
                @if ($untimed > 0)
                    <p class="muted">{{ trans_choice('{1} :count object of the list has no time.|[2,*] :count objects of the list have no time.', $untimed, ['count' => $untimed]) }}</p>
                @endif
            </details>
        </section>

        {{-- Offline --}}
        <section class="card">
            <div class="label">{{ __('Without a connection') }}</div>
            <p class="muted">{{ __('Save the list on this device before you leave: the page of every object, with its charts, then also opens without a connection. Observations noted without a connection are kept here and sent when it returns.') }}</p>
            <div class="row">
                <button type="button" class="button" id="save-offline">{{ __('Save for offline use') }}</button>
                <span class="muted" id="save-offline-status" role="status"></span>
            </div>
            <p class="muted">{{ __('Or open the whole list in SkySafari, which also works without a connection, and use its GoTo there.') }}</p>
            <a class="button" href="{{ route('observing-list.export.skylist', $list) }}">{{ __('Export SkySafari (.skylist)') }}</a>
        </section>
    </main>

    {{-- Quick note: also without a connection --}}
    <dialog id="quick-log" aria-labelledby="quick-log-title">
        <form method="dialog" id="quick-log-form">
            <div class="label" id="quick-log-title">{{ __('Note an observation') }}</div>
            <h2 style="margin:.2rem 0">{{ $current['name'] }}</h2>
            <p class="muted" id="quick-log-time"></p>
            @if ($scale)
                <label for="quick-log-visibility">{{ __('Object visibility') }}</label>
                <select id="quick-log-visibility">
                    <option value="">{{ __('Select visibility') }}</option>
                    @for ($i = 1; $i <= $scaleSize; $i++)
                        <option value="{{ $i }}">{{ __('observations.' . $scale . '.' . $i) }}</option>
                    @endfor
                </select>
            @endif
            <label for="quick-log-description">{{ __('Description') }}</label>
            <textarea id="quick-log-description" rows="6" maxlength="{{ \App\Services\AtTheEyepiece::MAX_DESCRIPTION }}" required></textarea>
            <p class="muted">{{ __('Saved with the location and equipment of your last observation tonight, else your standard ones. Complete it later on the observation page.') }}</p>
            <p class="error" id="quick-log-error" hidden></p>
            <div class="row spread">
                <button type="button" class="button" id="quick-log-cancel">{{ __('Cancel') }}</button>
                <button type="submit" class="button" style="border-color:#b91c1c">{{ __('Save') }}</button>
            </div>
        </form>
    </dialog>

    @php
        $pageData = [
            'list' => $list->slug,
            'base' => $baseUrl,
            'store' => route('observing-list.eyepiece.store', $list),
            'listPage' => route('observing-list.show', $list),
            'user' => $user->id,
            'timezone' => $timezone,
            'locale' => str_replace('_', '-', app()->getLocale()),
            'item' => $current['item']->id,
            'object' => $current['name'],
            'following' => $following,
            // Only a list with times follows the clock
            'sequence' => $timed ? $entries->map(fn ($entry) => ['id' => $entry['item']->id, 'at' => $entry['at']->timestamp])->values() : [],
            'ids' => $entries->map(fn ($entry) => $entry['item']->id)->values(),
            'texts' => [
                'in' => __('in :minutes min'),
                'ago' => __(':minutes min ago'),
                'now' => __('now'),
                'saving' => __('Saving :done of :total…'),
                'saved' => __('Saved: :count pages. They open without a connection.'),
                'saveFailed' => __('Saving failed: :count pages could not be fetched.'),
                'noCache' => __('This browser cannot keep pages for offline use.'),
                'waiting' => __('Kept on this device: they are sent when the connection returns.'),
                'sent' => __('Sent: :object.'),
                'login' => __('Log in again to send the observations kept on this device.'),
                'remove' => __('Remove'),
                'removeConfirm' => __('Remove this observation from this device? It has not been sent.'),
                'empty' => __('Write what you saw.'),
                'at' => __('At :time'),
                'copied' => __('Copied: paste it in the search of SkySafari.'),
                'noSkySafari' => __('SkySafari did not open: copy the name and paste it in its search.'),
            ],
        ];
    @endphp
    <script type="application/json" id="eyepiece-data">@json($pageData)</script>
    <script>
        (function () {
            var data = JSON.parse(document.getElementById('eyepiece-data').textContent);
            var texts = data.texts;
            // PAGES of public/sw.js
            var CACHE = 'dsl-offline-v1';
            var QUEUE = 'dsl-eyepiece-queue';
            var FOLLOW = 'dsl-eyepiece-follow:' + data.list;
            var format = function (text, values) {
                return text.replace(/:(\w+)/g, function (match, key) { return key in values ? values[key] : match; });
            };
            var storage = function (store, key, value) {
                try {
                    if (value === undefined) return window[store].getItem(key);
                    window[store].setItem(key, value);
                } catch (e) { return null; }
            };

            // The clock and the minutes to or since each time, at the location
            var clock = document.getElementById('clock');
            var hm;
            try {
                hm = new Intl.DateTimeFormat(data.locale, { timeZone: data.timezone, hour: '2-digit', minute: '2-digit', hourCycle: 'h23' });
            } catch (e) {
                hm = new Intl.DateTimeFormat(undefined, { hour: '2-digit', minute: '2-digit' });
            }
            function tick() {
                var now = Date.now();
                clock.textContent = hm.format(new Date(now));
                document.querySelectorAll('[data-relative]').forEach(function (element) {
                    var minutes = Math.round((Number(element.getAttribute('data-relative')) * 1000 - now) / 60000);
                    element.textContent = minutes === 0 ? texts.now : format(minutes > 0 ? texts['in'] : texts.ago, { minutes: Math.abs(minutes) });
                });
            }

            // Following the clock: the page of the object whose time has come
            if (data.following) storage('sessionStorage', FOLLOW, '1');
            document.querySelectorAll('[data-pin]').forEach(function (link) {
                link.addEventListener('click', function () { storage('sessionStorage', FOLLOW, '0'); });
            });
            var followButton = document.getElementById('follow');
            if (followButton) followButton.addEventListener('click', function () { storage('sessionStorage', FOLLOW, '1'); });
            function follow() {
                if (storage('sessionStorage', FOLLOW) !== '1' || ! data.sequence.length) return;
                var now = Date.now() / 1000;
                var id = data.sequence[0].id;
                data.sequence.forEach(function (entry) { if (entry.at <= now) id = entry.id; });
                if (id !== data.item && ! document.querySelector('dialog[open]')) {
                    window.location.replace(data.base + '?item=' + id);
                }
            }
            tick();
            setInterval(function () { tick(); follow(); }, 15000);

            // Charts
            document.querySelectorAll('[data-view]').forEach(function (tab) {
                tab.addEventListener('click', function () {
                    document.querySelectorAll('[data-view]').forEach(function (other) {
                        other.setAttribute('aria-selected', other === tab ? 'true' : 'false');
                        document.getElementById('view-' + other.getAttribute('data-view')).hidden = other !== tab;
                    });
                });
            });

            // SkySafari: when the page is still in view a moment after the link,
            // no app took it
            var copyStatus = document.getElementById('copy-name-status');
            var skySafari = document.getElementById('skysafari');
            // An iPad presents itself as a Mac with a touch screen
            skySafari.hidden = ! (/iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1));
            skySafari.addEventListener('click', function () {
                copyStatus.textContent = '';
                setTimeout(function () {
                    if (document.visibilityState === 'visible' && document.hasFocus()) copyStatus.textContent = texts.noSkySafari;
                }, 2000);
            });
            document.getElementById('copy-name').addEventListener('click', function () {
                var done = function () { copyStatus.textContent = texts.copied; };
                var fallback = function () {
                    var field = document.createElement('textarea');
                    field.value = data.object;
                    field.setAttribute('readonly', '');
                    field.style.position = 'fixed';
                    field.style.opacity = '0';
                    document.body.appendChild(field);
                    field.select();
                    try { document.execCommand('copy'); done(); } catch (e) {}
                    document.body.removeChild(field);
                };
                if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(data.object).then(done, fallback);
                else fallback();
            });

            // Observations kept on this device until they are sent
            function queue() {
                try { return JSON.parse(storage('localStorage', QUEUE) || '[]'); } catch (e) { return []; }
            }
            function saveQueue(entries) { storage('localStorage', QUEUE, JSON.stringify(entries)); renderQueue(); }
            var queueStatus = document.getElementById('queue-status');
            function renderQueue(status) {
                var entries = queue();
                var section = document.getElementById('queue');
                var list = document.getElementById('queue-list');
                section.hidden = entries.length === 0;
                list.textContent = '';
                entries.forEach(function (entry) {
                    var li = document.createElement('li');
                    li.textContent = entry.object + ' · ' + hm.format(new Date(entry.observed_at)) + ' ';
                    if (entry.problem) {
                        var problem = document.createElement('div');
                        problem.className = 'problem';
                        problem.textContent = entry.problem;
                        li.appendChild(problem);
                    }
                    var remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'button';
                    remove.textContent = texts.remove;
                    remove.addEventListener('click', function () {
                        if (! window.confirm(texts.removeConfirm)) return;
                        saveQueue(queue().filter(function (other) { return other.key !== entry.key; }));
                    });
                    li.appendChild(remove);
                    list.appendChild(li);
                });
                if (status !== undefined) queueStatus.textContent = status;
                else if (entries.length) queueStatus.textContent = texts.waiting;
            }

            var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            // The token of a page from the cache can be older than the session
            function freshToken() {
                return fetch(data.base + '?item=' + data.item, { cache: 'no-store', credentials: 'same-origin' })
                    .then(function (response) { return response.text(); })
                    .then(function (html) {
                        var match = html.match(/<meta name="csrf-token" content="([^"]+)"/);
                        if (match) token = match[1];
                    });
            }
            function post(entry) {
                return fetch(entry.store, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ item: entry.item, observed_at: entry.observed_at, description: entry.description, visibility: entry.visibility, user: entry.user }),
                });
            }
            var sending = false;
            function send() {
                var entries = queue().filter(function (entry) { return ! entry.problem; });
                if (sending || ! entries.length || navigator.onLine === false) return Promise.resolve();
                sending = true;
                var retried = false;
                var sent = [];
                var next = function (index) {
                    if (index >= entries.length) return null;
                    var entry = entries[index];
                    return post(entry).then(function (response) {
                        if (response.status === 419 && ! retried) {
                            retried = true;
                            return freshToken().then(function () { return next(index); });
                        }
                        if (response.status === 401 || response.status === 419) {
                            renderQueue(texts.login);
                            return null;
                        }
                        if (response.ok) {
                            sent.push(entry.object);
                            saveQueue(queue().filter(function (other) { return other.key !== entry.key; }));
                            return next(index + 1);
                        }
                        return response.json().catch(function () { return {}; }).then(function (body) {
                            saveQueue(queue().map(function (other) {
                                if (other.key === entry.key) other.problem = body.message || ('HTTP ' + response.status);
                                return other;
                            }));
                            return next(index + 1);
                        });
                    });
                };
                return Promise.resolve(next(0))
                    .catch(function () { renderQueue(texts.waiting); })
                    .then(function () {
                        sending = false;
                        if (sent.length) renderQueue(format(texts.sent, { object: sent.join(', ') }));
                    });
            }
            document.getElementById('queue-send').addEventListener('click', function () {
                // A problem may be solved (logged in again): try those too
                saveQueue(queue().map(function (entry) { delete entry.problem; return entry; }));
                send();
            });
            window.addEventListener('online', send);
            setInterval(send, 60000);
            renderQueue();
            send();

            // Quick note, and "Log it" without a connection
            var dialog = document.getElementById('quick-log');
            var form = document.getElementById('quick-log-form');
            var description = document.getElementById('quick-log-description');
            var visibility = document.getElementById('quick-log-visibility');
            var error = document.getElementById('quick-log-error');
            var openedAt = null;
            function openQuickLog() {
                openedAt = new Date();
                document.getElementById('quick-log-time').textContent = format(texts.at, { time: hm.format(openedAt) });
                error.hidden = true;
                if (typeof dialog.showModal === 'function') dialog.showModal(); else dialog.setAttribute('open', '');
                description.focus();
            }
            document.getElementById('quick-log-open').addEventListener('click', openQuickLog);
            document.getElementById('quick-log-cancel').addEventListener('click', function () { dialog.close(); });
            document.getElementById('log-it').addEventListener('click', function (event) {
                if (navigator.onLine === false) {
                    event.preventDefault();
                    openQuickLog();
                }
            });
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                if (! description.value.trim()) {
                    error.textContent = texts.empty;
                    error.hidden = false;
                    return;
                }
                var entries = queue();
                entries.push({
                    key: Date.now() + '-' + Math.random().toString(36).slice(2),
                    store: data.store,
                    item: data.item,
                    object: data.object,
                    observed_at: (openedAt || new Date()).toISOString(),
                    description: description.value.trim(),
                    visibility: visibility && visibility.value ? Number(visibility.value) : null,
                    user: data.user,
                });
                saveQueue(entries);
                description.value = '';
                if (visibility) visibility.value = '';
                dialog.close();
                send();
            });
            // The service worker sends "Log it" here when the form cannot be reached
            if (window.location.hash === '#log') {
                history.replaceState(null, '', window.location.pathname + window.location.search);
                openQuickLog();
            }

            // Save every page of the list for use without a connection
            var saveButton = document.getElementById('save-offline');
            var saveStatus = document.getElementById('save-offline-status');
            saveButton.addEventListener('click', function () {
                if (! window.caches) {
                    saveStatus.textContent = texts.noCache;
                    return;
                }
                var urls = [data.listPage, data.base].concat(data.ids.map(function (id) { return data.base + '?item=' + id; }));
                var done = 0;
                var failed = 0;
                saveButton.disabled = true;
                caches.open(CACHE).then(function (cache) {
                    var step = function (index) {
                        if (index >= urls.length) return null;
                        saveStatus.textContent = format(texts.saving, { done: done + 1, total: urls.length });
                        return fetch(urls[index], { credentials: 'same-origin', cache: 'no-store' })
                            .then(function (response) {
                                if (! response.ok || response.redirected) throw new Error(response.status);
                                return cache.put(urls[index], response);
                            })
                            .catch(function () { failed++; })
                            .then(function () { done++; return step(index + 1); });
                    };
                    return step(0);
                }).then(function () {
                    saveStatus.textContent = failed ? format(texts.saveFailed, { count: failed }) : format(texts.saved, { count: done });
                    saveButton.disabled = false;
                });
            });

            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js').catch(function () {});
            }
        })();
    </script>
</body>
</html>
