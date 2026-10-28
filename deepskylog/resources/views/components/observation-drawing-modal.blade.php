{{-- Lightbox for the drawings of the observation search table: its drawing icons open it. --}}
<div id="obs-drawing-modal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80"
     onclick="if(event.target===this){this.classList.add('hidden');document.getElementById('obs-drawing-img').src='';}">
    <div class="relative max-w-3xl w-full mx-4">
        <button type="button"
                onclick="document.getElementById('obs-drawing-modal').classList.add('hidden');document.getElementById('obs-drawing-img').src='';"
                class="absolute -top-8 right-0 text-white text-2xl leading-none hover:text-gray-300 focus:outline-none"
                aria-label="{{ __('Close') }}">&#x2715;</button>
        <img id="obs-drawing-img" src="" alt="{{ __('Drawing') }}"
             class="w-auto mx-auto rounded shadow-lg" style="max-height: 80vh" />
    </div>
</div>
