{{-- The drawing laid over the DSS image (App\Services\Observations\SketchOverlay):
     centred on the object, the field of the eyepiece across the smaller side (field_fraction of it
     for a sketch of the sketch pad, which has N and W around its field)
     of the drawing, turned and mirrored back to north up, east left. The
     observer fine-tunes it (move by dragging, turn, scale, mirror) and fades
     or blinks it. The black of the drawing is screened away; a drawing on
     white paper (a light border) is inverted first, one of the sketch pad
     (white stars on black) is not. Aladin Lite (with jQuery, which it needs) is only loaded
     when the comparison is opened. --}}
@php
    $overlayTexts = [
        'up' => $sketchOverlay['up'] === 'zenith'
            ? __('Drawn zenith up at the moment of the observation (alt-azimuth mount), turned back to north up.')
            : __('Drawn north up.'),
        'field' => __('The width of the drawing is taken as the field of view, :field.', ['field' => \App\Services\FieldViews\FieldViewBuilder::formatAngle($sketchOverlay['field_deg'])]),
        'estimated' => __('The field of view is estimated: adjust the scale.'),
        'mirrored' => __('Mirrored as set for the instrument.'),
        'failed' => __('The photo could not be loaded.'),
    ];
@endphp
<div class="mt-4 text-center" id="sketch-overlay">
    <button type="button" id="sketch-overlay-open" class="rounded bg-gray-700 px-3 py-2 text-sm text-white hover:bg-gray-600">
        {{ __('Compare with the photo') }}
    </button>
    <div id="sketch-overlay-panel" class="mt-3 text-left" hidden>
        <div id="sketch-overlay-stage" style="position: relative; width: 100%; max-width: 40rem; aspect-ratio: 1; margin: 0 auto; overflow: hidden; background: #000; isolation: isolate;">
            <div id="sketch-overlay-aladin" style="position: absolute; inset: 0;"></div>
            <img id="sketch-overlay-image" src="{{ $sketchOverlay['image'] }}" alt="{{ __('Observation drawing') }}" draggable="false" hidden
                style="position: absolute; left: 0; top: 0; z-index: 20; max-width: none; transform-origin: 50% 50%; cursor: move; touch-action: none; user-select: none;" />
        </div>
        <p class="mt-1 text-center text-xs text-gray-400">{{ __('DSS2 colour image, from Aladin Lite (CDS).') }} {{ __('Drag the drawing to move it.') }}</p>

        <div class="mx-auto mt-3 grid max-w-2xl gap-3 text-sm text-gray-300 sm:grid-cols-2">
            <label class="block">
                {{ __('Opacity') }}
                <input type="range" id="sketch-overlay-opacity" min="0" max="100" value="70" class="w-full" />
            </label>
            <label class="block">
                {{ __('Turn') }} <span id="sketch-overlay-rotate-value">0°</span>
                <input type="range" id="sketch-overlay-rotate" min="-180" max="180" step="1" value="0" class="w-full" />
            </label>
            <label class="block">
                {{ __('Scale') }} <span id="sketch-overlay-scale-value">100%</span>
                <input type="range" id="sketch-overlay-scale" min="-100" max="100" step="1" value="0" class="w-full" />
            </label>
            <div class="flex flex-wrap items-center gap-3">
                <label><input type="checkbox" id="sketch-overlay-flop" @checked($sketchOverlay['flop']) /> {{ __('Mirror left-right') }}</label>
                <label><input type="checkbox" id="sketch-overlay-flip" @checked($sketchOverlay['flip']) /> {{ __('Mirror top-bottom') }}</label>
                <label><input type="checkbox" id="sketch-overlay-invert" /> {{ __('Invert the drawing') }}</label>
            </div>
        </div>
        <div class="mt-3 flex flex-wrap justify-center gap-2">
            <button type="button" id="sketch-overlay-blink" aria-pressed="false" class="rounded bg-gray-700 px-3 py-1 text-sm text-white hover:bg-gray-600">{{ __('Blink') }}</button>
            <button type="button" id="sketch-overlay-reset" class="rounded bg-gray-700 px-3 py-1 text-sm text-white hover:bg-gray-600">{{ __('Reset') }}</button>
        </div>
        <p id="sketch-overlay-info" class="mx-auto mt-2 max-w-2xl text-xs text-gray-400"></p>
    </div>
</div>
<script type="application/json" id="sketch-overlay-data">@json(['overlay' => $sketchOverlay, 'texts' => $overlayTexts])</script>
<script>
    (function () {
        var payload = JSON.parse(document.getElementById('sketch-overlay-data').textContent);
        var overlay = payload.overlay;
        var texts = payload.texts;
        var $ = function (id) { return document.getElementById('sketch-overlay-' + id); };
        var panel = $('panel');
        var stage = $('stage');
        var image = $('image');
        var info = $('info');
        var aladin = null;
        var state = null;
        var blinking = null;
        var placed = '';

        function reset() {
            state = { dx: 0, dy: 0, turn: 0, scale: 0 };
            $('rotate').value = 0;
            $('scale').value = 0;
            $('flop').checked = overlay.flop;
            $('flip').checked = overlay.flip;
            $('invert').checked = onPaper();
            update();
        }
        // Whether the drawing is dark on light: the mean brightness of its border
        function onPaper() {
            if (! image.naturalWidth) return false;
            try {
                var canvas = document.createElement('canvas');
                canvas.width = canvas.height = 32;
                var context = canvas.getContext('2d');
                context.drawImage(image, 0, 0, 32, 32);
                var pixels = context.getImageData(0, 0, 32, 32).data;
                var sum = 0, count = 0;
                for (var i = 0; i < 32; i++) {
                    [[i, 0], [i, 31], [0, i], [31, i]].forEach(function (p) {
                        var k = (p[1] * 32 + p[0]) * 4;
                        sum += pixels[k] + pixels[k + 1] + pixels[k + 2];
                        count += 3;
                    });
                }
                return sum / count > 128;
            } catch (e) {
                return true;
            }
        }
        image.addEventListener('load', function () { if (state) { $('invert').checked = onPaper(); update(); } });
        function scale() { return Math.pow(2, state.scale / 100); }
        function update() {
            $('rotate-value').textContent = state.turn + '°';
            $('scale-value').textContent = Math.round(scale() * 100) + '%';
            image.style.opacity = $('opacity').value / 100;
            // Screened, the black disappears and the strokes light up; white
            // paper is inverted first
            image.style.filter = $('invert').checked ? 'invert(1)' : 'none';
            image.style.mixBlendMode = 'screen';
            placed = '';
        }
        function pixelsPerDegree() {
            return stage.clientWidth / aladin.getFov()[0];
        }
        // Follows the view of Aladin (panning, zooming) every frame
        function place() {
            if (panel.hidden) return;
            window.requestAnimationFrame(place);
            if (! aladin || ! image.naturalWidth) return;
            var centre = aladin.world2pix(overlay.ra, overlay.dec);
            if (! centre) { image.hidden = true; return; }
            var perDegree = pixelsPerDegree();
            var field = overlay.field_deg * scale() * perDegree;
            var side = Math.min(image.naturalWidth, image.naturalHeight) * overlay.field_fraction;
            var width = field * image.naturalWidth / side;
            var height = field * image.naturalHeight / side;
            var x = centre[0] + state.dx * perDegree - width / 2;
            var y = centre[1] - state.dy * perDegree - height / 2;
            // The drawing was turned and mirrored from the sky: undone in reverse
            var transform = 'translate(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px) rotate(' + (overlay.rotation_deg + state.turn) + 'deg) scale('
                + ($('flop').checked ? -1 : 1) + ',' + ($('flip').checked ? -1 : 1) + ')';
            var key = transform + width.toFixed(1);
            if (key === placed) return;
            placed = key;
            image.hidden = false;
            image.style.width = width + 'px';
            image.style.height = height + 'px';
            image.style.transform = transform;
        }

        // Moving the drawing by dragging, in degrees so it stays when zooming
        var drag = null;
        image.addEventListener('pointerdown', function (event) {
            event.preventDefault();
            image.setPointerCapture(event.pointerId);
            drag = { x: event.clientX, y: event.clientY };
        });
        image.addEventListener('pointermove', function (event) {
            if (! drag || ! aladin) return;
            var perDegree = pixelsPerDegree();
            state.dx += (event.clientX - drag.x) / perDegree;
            state.dy -= (event.clientY - drag.y) / perDegree;
            drag = { x: event.clientX, y: event.clientY };
        });
        ['pointerup', 'pointercancel'].forEach(function (name) {
            image.addEventListener(name, function () { drag = null; });
        });

        $('rotate').addEventListener('input', function () { state.turn = Number(this.value); update(); });
        $('scale').addEventListener('input', function () { state.scale = Number(this.value); update(); });
        ['opacity', 'flop', 'flip', 'invert'].forEach(function (id) { $(id).addEventListener('input', update); $(id).addEventListener('change', update); });
        $('reset').addEventListener('click', reset);
        $('blink').addEventListener('click', function () {
            var button = this;
            if (blinking) {
                clearInterval(blinking);
                blinking = null;
                image.style.visibility = '';
            } else {
                blinking = setInterval(function () {
                    image.style.visibility = image.style.visibility === 'hidden' ? '' : 'hidden';
                }, 700);
            }
            button.setAttribute('aria-pressed', blinking ? 'true' : 'false');
        });

        function load(src) {
            return new Promise(function (resolve, reject) {
                var script = document.createElement('script');
                script.src = src;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }
        function aladinLoaded() {
            if (window.A && typeof window.A.aladin === 'function') return Promise.resolve();
            var base = 'https://aladin.u-strasbg.fr/AladinLite/api/v2/latest/';
            var css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = base + 'aladin.min.css';
            document.head.appendChild(css);
            return (window.jQuery ? Promise.resolve() : load('https://code.jquery.com/jquery-3.6.0.min.js'))
                .then(function () { return load(base + 'aladin.min.js'); });
        }

        $('open').addEventListener('click', function () {
            panel.hidden = ! panel.hidden;
            if (panel.hidden) return;
            var lines = [texts.field];
            if (overlay.field_estimated) lines.push(texts.estimated);
            lines.push(texts.up);
            if (overlay.flip || overlay.flop) lines.push(texts.mirrored);
            info.textContent = lines.join(' ');
            if (! state) reset();
            if (! aladin) {
                // CDS sometimes does not answer at all
                var slow = setTimeout(function () { if (! aladin) info.textContent = texts.failed; }, 20000);
                aladinLoaded().then(function () {
                    clearTimeout(slow);
                    aladin = window.A.aladin('#sketch-overlay-aladin', {
                        survey: 'P/DSS2/color',
                        fov: overlay.field_deg * 1.4,
                        target: overlay.ra.toFixed(6) + ' ' + overlay.dec.toFixed(6),
                        showReticle: false,
                        showGotoControl: false,
                        showFullscreenControl: false,
                    });
                }).catch(function () { info.textContent = texts.failed; });
            }
            window.requestAnimationFrame(place);
        });
    })();
</script>
