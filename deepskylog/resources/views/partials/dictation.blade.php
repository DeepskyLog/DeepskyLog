{{-- Dictation: speak the description instead of typing it (the Quick note of
     the page at the eyepiece, the observation form). Uses the browser's speech
     recognition (Chrome, Edge, Safari); where there is none the button stays
     hidden and the microphone of the keyboard still works. Chrome recognises
     on its servers, so without a connection it only works when the language
     can be recognised on the device (processLocally, Chrome 139 and later).

     window.dslDictation({ button, status, language, insert }) wires one
     button: language() gives the language of the text (an ISO code such as
     'nl'), insert(text) adds a recognised phrase, status is the element that
     shows what is being heard and why it stopped. --}}
@php
    $dictationTexts = [
        'dictate' => __('Dictate'),
        'stop' => __('Stop dictating'),
        'listening' => __('Listening… speak your description.'),
        'denied' => __('The microphone may not be used: allow it in the settings of the browser.'),
        'offline' => __('Dictation needs a connection on this device. Use the microphone of the keyboard instead.'),
        'language' => __('Dictation does not know this language on this device. Use the microphone of the keyboard instead.'),
        'failed' => __('Dictation stopped. Use the microphone of the keyboard instead.'),
    ];
@endphp
<script>
    (function () {
        if (window.dslDictation) return;
        var texts = @json($dictationTexts);
        var Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        // Stop listening after this long without a word
        var IDLE_MS = 60000;

        // The regional variant the browser prefers (nl-BE), else the language itself
        function locale(language) {
            language = (language || document.documentElement.lang || 'en').replace('_', '-');
            if (language.indexOf('-') > 0) return language;
            var preferred = (navigator.languages || []).filter(function (tag) {
                return tag.toLowerCase().indexOf(language.toLowerCase() + '-') === 0;
            });
            return preferred[0] || language;
        }

        window.dslDictation = function (options) {
            var button = options.button;
            var status = options.status;
            if (! Recognition || ! button) return;
            button.hidden = false;
            var label = button.querySelector('[data-dictation-label]') || button;

            var recognition = null;
            var wanted = false;
            var lastHeard = 0;
            var problem = null;

            function show(text) { if (status) status.textContent = text || ''; }
            function render() {
                button.setAttribute('aria-pressed', wanted ? 'true' : 'false');
                label.textContent = wanted ? texts.stop : texts.dictate;
            }
            function stop(message) {
                wanted = false;
                if (recognition) {
                    try { recognition.abort(); } catch (e) {}
                }
                recognition = null;
                render();
                show(message);
            }

            function listen(local) {
                recognition = new Recognition();
                recognition.lang = locale(options.language && options.language());
                recognition.continuous = true;
                recognition.interimResults = true;
                if (local) recognition.processLocally = true;
                recognition.onresult = function (event) {
                    var interim = '';
                    for (var i = event.resultIndex; i < event.results.length; i++) {
                        var phrase = event.results[i][0].transcript.trim();
                        if (! phrase) continue;
                        if (event.results[i].isFinal) options.insert(phrase);
                        else interim += ' ' + phrase;
                    }
                    lastHeard = Date.now();
                    show(interim ? '… ' + interim.trim() : texts.listening);
                };
                recognition.onerror = function (event) {
                    // Silence is no reason to stop: listening starts again below
                    if (event.error === 'no-speech' || event.error === 'aborted') return;
                    problem = {
                        'not-allowed': texts.denied,
                        'service-not-allowed': texts.denied,
                        'network': texts.offline,
                        'language-not-supported': texts.language,
                    }[event.error] || texts.failed;
                };
                // Phones stop after a pause: go on while the observer did not stop
                recognition.onend = function () {
                    if (problem) return stop(problem);
                    if (! wanted) return;
                    if (Date.now() - lastHeard > IDLE_MS) return stop('');
                    try { recognition.start(); } catch (e) { stop(texts.failed); }
                };
                try {
                    recognition.start();
                } catch (e) {
                    stop(texts.failed);
                }
            }

            function start() {
                wanted = true;
                problem = null;
                lastHeard = Date.now();
                render();
                show(texts.listening);
                if (navigator.onLine !== false) return listen(false);
                // Without a connection only recognition on the device can work
                if (typeof Recognition.available !== 'function') return stop(texts.offline);
                Recognition.available({ langs: [locale(options.language && options.language())], processLocally: true })
                    .then(function (availability) {
                        if (! wanted) return;
                        if (availability === 'available') listen(true);
                        else stop(texts.offline);
                    }, function () { stop(texts.offline); });
            }

            button.addEventListener('click', function () {
                if (wanted) stop('');
                else start();
            });
            render();
            return { stop: function () { if (wanted) stop(''); } };
        };

        // Adds a phrase to a textarea at the cursor, as a sentence of its own
        // when the text before it ends one
        window.dslDictation.join = function (before, phrase) {
            var text = before.replace(/\s+$/, '');
            if (text === '' || /[.!?]$/.test(text)) phrase = phrase.charAt(0).toLocaleUpperCase() + phrase.slice(1);
            return text === '' ? phrase : text + ' ' + phrase;
        };
    })();
</script>
