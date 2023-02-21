<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-400 active:bg-gray-600 focus:outline-none focus:border-gray-800 focus:ring focus:ring-gray-200 disabled:opacity-25 transition']) }}>
    {{ $slot }}
</button>
